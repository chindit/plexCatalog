<?php

namespace App\Http\Controllers;

use App\Service\StringUtils;
use App\Service\Thumbnailer;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Exceptions\UnreachableServerException;
use Chindit\PlexApi\Model\File;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\Model\Media;
use Chindit\PlexApi\Model\Server;
use Chindit\PlexApi\Model\Show;
use Chindit\PlexApi\PlexServer;
use Chindit\PlexApi\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Cookie as SfCookie;

class PlexController extends Controller
{
    public function listcatalogs(Request $request)
    {
        $request->validate([
            'serverAddress' => 'nullable|string',
            'serverToken' => 'required|string',
            'serverPort' => 'int',
        ]);

        $account = new Account(
            $request->string('serverToken'),
            $request->string('serverAddress'),
            $request->integer('serverPort'),
        );

        // Save token into session
        $request->session()->put('plexServer', [
            's' => $request->string('serverAddress'),
            't' => $request->string('serverToken'),
            'p' => $request->integer('serverPort', 32400),
        ]);

        $libraries = new Collection();

        foreach ($account->getServerList() as $server) {
            /** @var Server $server */
            try {
                $serverLibraries = $account
                    ->getServer($server->identifier)
                    ->libraries();

                foreach ($serverLibraries as $library) {
                    $libraries->push([
                        'serverId' => $server->identifier,
                        'serverName' => $server->name,
                        'library' => $library,
                    ]);
                }
            } catch (UnreachableServerException|ClientException $throwable) {
                continue;
            }
        }

        try {
            $catalogs = $libraries
                ->filter(fn(array $item) => $item['library']->getType() === LibraryType::Movie || $item['library']->getType() === LibraryType::Show)
                ->mapWithKeys(fn(array $item) => [
                    $item['serverId'] . ':' . $item['library']->getId() => $item['serverName'] . ' - ' . $item['library']->getTitle(),
                ]);
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }

        return response()
            ->view('catalogs', ['catalogs' => $catalogs]);
    }

    public function generateReport(Request $request, Thumbnailer $thumbnailer)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string'
        ]);

        $server = $request->session()->get('plexServer');
        $account = new Account($server['t'], $server['s'], $server['p']);
        /** @var Collection<Server> $servers */
        $servers = collect($account->getServerList())->keyBy('identifier');

        $movies = collect();

        foreach ($request->array('ids') as $selection) {
            try {
                [$serverId, $libraryId] = explode(':', $selection, 2);
                $plexServer = $account->getServer($serverId);
                $serverModel = $servers->firstWhere(fn($server) => $server->identifier === $serverId);
                $connection = $serverModel?->getConnections()->filter(fn($connection) => !$connection->isLocal)->first();

                if (!$connection || !ctype_digit($libraryId)) {
                    throw new \InvalidArgumentException('Invalid server or library selection');
                }

                $serverUrl = rtrim($connection->host, '/');
                if (parse_url($serverUrl, PHP_URL_SCHEME) === null || parse_url($serverUrl, PHP_URL_PORT) === null) {
                    $serverUrl .= ':' . $connection->port;
                }

                foreach ($plexServer->library((int) $libraryId, ($request->get('unwatchedOnly', false) === "true")) as $movie) {
                    $movies->push([
                        'movie' => $movie,
                        'server' => [
                            's' => $serverUrl,
                            't' => $serverModel->token,
                            'p' => $connection->port,
                        ],
                    ]);
                }
            } catch (\Throwable $throwable) {
                return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
            }
        }

        $isCatalogOnly = ($request->get('htmlOnly', false) === "true");

        $movies = $movies->map(function (array $item) use ($thumbnailer, $isCatalogOnly) {
            /** @var Media|Show $movie */
            $movie = $item['movie'];
            $server = $item['server'];

            // Download thumb & resize it but only if PDF rendering is required
            if ($movie->getThumb()) {
                $thumbnail = $server['s']
                    . (parse_url($server['s'], PHP_URL_PORT) === null ? ':' . $server['p'] : '')
                    . $movie->getThumb()
                    . '?X-Plex-Token=' . $server['t'];
                if (!$isCatalogOnly) {
                    $thumbnail = $thumbnailer->thumbnail($thumbnail);
                }
            } else {
                $thumbnail = '';
            }

            return [
                // Title should start with an uppercase for better sorting
                'title' => ucfirst(StringUtils::stripPrefix($movie->getTitle())),
                'summary' => $movie->getSummary(),
                'thumb' => $thumbnail,
                'duration' => round($movie->getDuration() / 60),
                'year' => $movie->getYear(),
                'quality' => in_array(File::class, class_uses_recursive($movie), true) ? ($movie->getResolution() > 10 ? $movie->getResolution() . 'p' : ($movie->getResolution() === 4 ? '4k' : '')) : '',
                'actors' => implode(', ', $movie->getActors()),
                'genres' => implode(', ', $movie->getGenres()),
            ];
        });

        $movies = $movies->sortBy(function (array $movie) {
            return Str::ascii($movie['title']);
        });

        $catalog = view('templates/catalog', [
            'server' => $server['s'],
            'token' => $server['t'],
            'port' => $server['p'],
            'movies' => $movies,
            'truncateDescription' => $request->get('truncateDescription', false) === "true",
            'htmlOnly' => $request->get('htmlOnly', false) === "true",
        ])->render();

        if ($isCatalogOnly)
        {
            return $catalog;
        }

        try {
            $fileName = tempnam(sys_get_temp_dir(), 'plex_') . '.pdf';
            Browsershot::html($catalog)
                ->noSandbox()
                ->newHeadless()
                ->format('A4')
                ->timeout(3000)
                ->margins(25, 0, 15, 0)
                ->showBrowserHeaderAndFooter()
                ->hideHeader()
                ->footerHtml('<div style="text-align: right;width: 297mm;font-size: 8px;"><span style="margin-right: 1cm"><span class="pageNumber"></span>/<span class="totalPages"></span></span></div>')
                ->save($fileName);

            return response()->download($fileName, 'catalog.pdf');
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }
    }
}
