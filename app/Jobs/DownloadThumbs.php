<?php

namespace App\Jobs;

use App\Models\Catalog;
use App\Models\Media;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;

class DownloadThumbs implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800;

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
        $this->catalog->user->medias
            ->filter(function (Media $movie) {
                return in_array((string)$movie->library_id, $this->catalog->options['ids']);
            })
            ->each(function (Media $movie) {
                if (file_exists($tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $movie->id . '.jpg'))
                {
                    return;
                }
                try {
                    $response = Http::get($this->catalog->user->server_url . ':' . $this->catalog->user->server_port . $movie->thumb, [
                        'X-Plex-Token' => $this->catalog->user->server_token
                    ]);
                    $thumbContent = $response->successful() ? $response->body() : false;
                } catch (\Exception $e) {
                    $thumbContent = false;
                }

                if ($thumbContent !== false) {
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $movie->id . '.jpg';
                    try {

                        ImageManager::imagick()->read($thumbContent)->scale(150)->save($tmpPath);
                    }catch (\Exception $e) {
                        return;
                    }
                }
            });
    }
}
