<?php

namespace App\Jobs;

use App\Models\Catalog;
use App\Models\Media;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanThumbs implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Catalog $catalog)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->catalog->user->medias->each(function(Media $movie) {
            if (file_exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $movie->id . '.jpg'))
            {
                unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $movie->id . '.jpg');
            }
        });
    }
}
