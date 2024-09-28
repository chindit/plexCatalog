<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Jobs\GeneratePdf;
use App\Jobs\ProcessCatalog;
use App\Jobs\RenderTemplate;
use App\Models\CatalogJobs;
use App\Service\StringUtils;
use App\Service\Thumbnailer;
use Chindit\PlexApi\Enum\LibraryType;
use Chindit\PlexApi\Model\File;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\Model\Media;
use Chindit\PlexApi\Model\Show;
use Chindit\PlexApi\PlexServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
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
        $isCatalogOnly = ($request->get('htmlOnly', false) === "true");

        $jobModel = CatalogJobs::create([
            'status' => JobStatus::created,
            'server' => (string)$plexApi
        ]);

        $batchJobs = [];
        foreach($request->get('ids') as $id) {
            $batchJobs[] = new ProcessCatalog($jobModel->id, $id, $request->get('unwatchedOnly', false) === "true");
        }

        $jobChain = [Bus::batch($batchJobs), new RenderTemplate($jobModel->id)];
        if ($isCatalogOnly) {
            $jobChain[] = new GeneratePdf($jobModel->id);
        }

        Bus::chain($jobChain)->dispatch();





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
