<?php

namespace App\Http\Controllers;

use App\Jobs\FetchMedias;
use App\Jobs\ProcessImages;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Exceptions\UnreachableServerException;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\Model\Server;
use Chindit\PlexApi\Account;
use App\Jobs\GenerateReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Symfony\Component\HttpClient\Exception\ClientException;

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

        $channelToken = Str::random(40);
        $request->session()->put('report_channel_token', $channelToken);

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
            ->view('catalogs', [
                'catalogs' => $catalogs,
                'channelToken' => $channelToken,
            ]);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string'
        ]);

        $server = $request->session()->get('plexServer');

        $channelToken = $request->session()->get('report_channel_token');
        $htmlOnly = $request->boolean('htmlOnly');
        $truncateDescription = $request->boolean('truncateDescription');

        $fetchMediasJob = new FetchMedias(
            $request->session()->get('report_channel_token'),
            $request->array('ids'),
            $server['t'],
            $server['s'],
            $server['p'],
            $request->boolean('unwatchedOnly')
        );

        $processImagesJob = new ProcessImages(
            $channelToken,
            $htmlOnly,
        );

        $generateReportJob = new GenerateReport($channelToken, $htmlOnly, $truncateDescription);

        Bus::chain([
            $fetchMediasJob,
            $processImagesJob,
            $generateReportJob
        ])->dispatch();

        return response()->json([
            'status' => 'queued',
            'channelToken' => $channelToken,
        ], 202);
    }

    public function downloadReport(Request $request, string $token)
    {
        abort_unless(
            hash_equals((string) $request->session()->get('report_channel_token', ''), $token),
            403,
        );

        $disk = Storage::disk('local');
        $pdfPath = "reports/{$token}.pdf";
        $htmlPath = "reports/{$token}.html";

        if ($disk->exists($pdfPath)) {
            return response()->download($disk->path($pdfPath), 'catalog.pdf');
        }

        if ($disk->exists($htmlPath)) {
            return response()->download(
                $disk->path($htmlPath),
                'catalog.html',
                ['Content-Type' => 'text/html; charset=UTF-8'],
            );
        }

        abort(404);
    }
}
