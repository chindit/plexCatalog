<?php

namespace App\Jobs;

use App\Models\CatalogJobs;
use App\Service\StringUtils;
use App\Service\Thumbnailer;
use Chindit\PlexApi\Model\File;
use Chindit\PlexApi\Model\Media;
use Chindit\PlexApi\Model\Show;
use Chindit\PlexApi\PlexServer;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCatalog implements ShouldQueue
{
    use Batchable, Queueable;

    private PlexServer $server;
    /**
     * Create a new job instance.
     */
    public function __construct(private int $jobId, private readonly string $libraryId, private readonly bool $unwatched = false)
    {
        $this->server = PlexServer::fromString(CatalogJobs::findOrFail($jobId)->server);
    }

    /**
     * Execute the job.
     */
    public function handle(Thumbnailer $thumbnailer): void
    {
        $movies = collect();

        $movies = $movies
            ->merge($this->server->library($this->libraryId, $this->unwatched))
            ->map(function(Media|Show $movie) use ($thumbnailer) {
            // Download thumb & resize it but only if PDF rendering is required
            if ($movie->getThumb()) {
                $thumbnail = $thumbnailer->thumbnail($this->server->getThumb($movie->getThumb()));
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

        /** @var CatalogJobs $job */
        $job = CatalogJobs::findOrFail($this->jobId);
        $job->catalogData()->create(['data' => $movies]);
    }
}
