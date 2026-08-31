<?php

namespace App\Jobs;

use App\Events\ReportUpdated;
use App\Service\StringUtils;
use App\Service\Thumbnailer;
use Chindit\PlexApi\Model\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessImages implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $channelToken,
        private readonly bool $htmlOnly = false,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Thumbnailer $thumbnailer): void
    {
        $path = "reports/{$this->channelToken}.serialized";

        if (!Storage::disk('local')->exists($path)) {
            throw new \RuntimeException("Media file not found: {$path}");
        }

        /** @var array<int, array{movie: object, server: array{s: string, t: string, p: int}}> $items */
        $items = unserialize(Storage::disk('local')->get($path));

        if (!is_array($items)) {
            throw new \RuntimeException("Invalid media file: {$path}");
        }

        ReportUpdated::dispatch($this->channelToken, 'Image processing started');

        $disk = Storage::disk('local');
        $processed = [];

        foreach ($items as $index => $item) {
            if ($index % 5 === 0) {
                ReportUpdated::dispatch($this->channelToken, 'Image processing: ' . ($index + 1) . '/' . count($items));
            }

            $movie = $item['movie'];
            $server = $item['server'];

            $thumbnail = '';
            if ($movie->getThumb()) {
                $thumbnail = $server['s']
                    . (parse_url($server['s'], PHP_URL_PORT) === null ? ':' . $server['p'] : '')
                    . $movie->getThumb()
                    . '?X-Plex-Token=' . $server['t'];

                if (!$this->htmlOnly) {
                    $imagePath = "reports/{$this->channelToken}/images/{$index}.jpg";

                    if ($disk->exists($imagePath)) {
                        // On a retry, an existing image is already complete.
                        $thumbnail = $disk->path($imagePath);
                    } else {
                        $temporaryPath = $thumbnailer->thumbnail($thumbnail);

                        if ($temporaryPath !== '') {
                            $disk->put($imagePath, file_get_contents($temporaryPath));
                            unlink($temporaryPath);
                            $thumbnail = $disk->path($imagePath);
                        } else {
                            $thumbnail = '';
                        }
                    }
                }
            }

            $processed[] = [
                'title' => ucfirst(StringUtils::stripPrefix($movie->getTitle())),
                'summary' => $movie->getSummary(),
                'thumb' => $thumbnail,
                'duration' => round($movie->getDuration() / 60),
                'year' => $movie->getYear(),
                'quality' => in_array(File::class, class_uses_recursive($movie), true)
                    ? ($movie->getResolution() > 10 ? $movie->getResolution() . 'p' : ($movie->getResolution() === 4 ? '4k' : ''))
                    : '',
                'actors' => implode(', ', $movie->getActors()),
                'genres' => implode(', ', $movie->getGenres()),
            ];

        }

        $movies = collect($processed)->sortBy('title')->values()->all();

        $disk->put(
            "reports/{$this->channelToken}.processed.serialized",
            serialize($movies),
        );

        ReportUpdated::dispatch(
            $this->channelToken,
            sprintf('Image processing completed. %d medias processed', count($movies)),
        );
    }
}
