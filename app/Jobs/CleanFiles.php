<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class CleanFiles implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $channelToken,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('local');

        // Remove the serialized media files created by FetchMedias and
        // ProcessImages, as well as the generated HTML and PDF.
        $disk->delete([
            "reports/{$this->channelToken}.serialized",
            "reports/{$this->channelToken}.processed.serialized",
            "reports/{$this->channelToken}.html",
            "reports/{$this->channelToken}.pdf",
        ]);

        // Processed thumbnails are stored in this report-specific directory.
        $disk->deleteDirectory("reports/{$this->channelToken}");

        // Thumbnailer uses the system temp directory while downloading and
        // resizing. Clean up leftovers from interrupted jobs as well.
        foreach (glob(sys_get_temp_dir() . "/plex_{$this->channelToken}_*") ?: [] as $temporaryFile) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }
}
