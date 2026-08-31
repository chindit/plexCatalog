<?php

namespace App\Jobs;

use App\Events\ReportUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class GenerateReport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $channelToken,
        private readonly bool $htmlOnly = false,
        private readonly bool $truncateDescription = false,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('local');
        $path = "reports/{$this->channelToken}.processed.serialized";

        if (!$disk->exists($path)) {
            throw new \RuntimeException("Processed media file not found: {$path}");
        }

        $movies = unserialize($disk->get($path));

        if (!is_array($movies)) {
            throw new \RuntimeException("Invalid processed media file: {$path}");
        }

        // Make the generated HTML self-contained. Browser clients cannot read
        // the worker's local filesystem after downloading the HTML file.
        $movies = array_map(function (array $movie): array {
            if ($movie['thumb'] !== '' && is_file($movie['thumb'])) {
                $content = file_get_contents($movie['thumb']);
                if ($content !== false) {
                    $movie['thumb'] = 'data:image/jpeg;base64,' . base64_encode($content);
                }
            }

            return $movie;
        }, $movies);

        ReportUpdated::dispatch($this->channelToken, 'Report generation started');

        $catalog = view('templates/catalog', [
            'movies' => $movies,
            'truncateDescription' => $this->truncateDescription,
            'htmlOnly' => $this->htmlOnly,
        ])->render();

        if ($this->htmlOnly) {
            $disk->put("reports/{$this->channelToken}.html", $catalog);
        } else {
            $pdfPath = $disk->path("reports/{$this->channelToken}.pdf");

            Browsershot::html($catalog)
                ->noSandbox()
                ->newHeadless()
                ->format('A4')
                ->timeout(3000)
                ->margins(25, 0, 15, 0)
                ->showBrowserHeaderAndFooter()
                ->hideHeader()
                ->footerHtml('<div style="text-align: right;width: 297mm;font-size: 8px;"><span style="margin-right: 1cm"><span class="pageNumber"></span>/<span class="totalPages"></span></span></div>')
                ->save($pdfPath);
        }

        // Keep the current host and port when the app is running locally.
        $reportUrl = route('report.download', ['token' => $this->channelToken], false);

        ReportUpdated::dispatch(
            $this->channelToken,
            'Report generation completed',
            $reportUrl,
        );
    }
}
