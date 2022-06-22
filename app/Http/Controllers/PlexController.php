<?php

namespace App\Http\Controllers;

use App\Service\StringUtils;
use App\Service\Thumbnailer;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\Model\Media;
use Chindit\PlexApi\PlexServer;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Cookie as SfCookie;

class PlexController extends Controller
{
    public function listcatalogs(Request $request)
    {
        $request->validate([
            'serverAddress' => 'required|string',
            'serverToken' => 'required|string',
            'serverPort' => 'int',
        ]);

        $plexApi = new PlexServer($request->get('serverAddress'), $request->get('serverToken'), $request->get('serverPort', 32400));

        try {
            $catalogs = collect($plexApi->libraries())
                ->filter(fn(Library $library) => $library->getType() === LibraryType::Movie || $library->getType() === LibraryType::Show)
                ->keyBy(fn(Library $library) => $library->getId())
                ->map(fn(Library $library) => $library->getTitle());
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }

        return response()
            ->view('catalogs', ['catalogs' => $catalogs])
            ->withCookie(new SfCookie('plex', json_encode(
                [
                    's' => $request->get('serverAddress'),
                    't' => $request->get('serverToken'),
                    'p' => $request->get('serverPort', 32400),
                ],
                JSON_THROW_ON_ERROR
            )
            ));
    }

    public function generateReport(Request $request, Thumbnailer $thumbnailer)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $server = json_decode($request->cookie('plex'), true, 10, JSON_THROW_ON_ERROR);

        $plexApi = new PlexServer($server['s'], $server['t'], $server['p']);

        $movies = collect();

        foreach ($request->get('ids') as $id) {
            try {
                $movies = $movies->merge($plexApi->library($id, ($request->get('unwatchedOnly', false) === "true")));
            } catch (\Throwable $throwable) {
                return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
            }
        }

        $isCatalogOnly = ($request->get('htmlOnly', false) === "true");

        $movies = $movies->map(function(Media $movie) use ($thumbnailer, $server, $isCatalogOnly) {
            // Download thumb & resize it but only if PDF rendering is required
            if ($movie->getThumb()) {
                $thumbnail = $server['s'] . ':' . $server['p'] . $movie->getThumb() . '?X-Plex-Token=' . $server['t'];
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
                'quality' => $movie->getResolution(),
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
                ->format('A4')
                ->timeout(3000)
                ->margins(25, 0, 15, 0)
                ->footerHtml('<div class="pageNumber"></div>')
                ->save($fileName);

            return response()->download($fileName, 'catalog.pdf');
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }
    }
}
