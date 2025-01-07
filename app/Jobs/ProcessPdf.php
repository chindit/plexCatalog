<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\User;
use App\Service\StringUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Browsershot\Browsershot;

class ProcessPdf implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $movies = $this->user->medias->map(function(Media $movie) {
            return [
                // Title should start with an uppercase for better sorting
                'title' => ucfirst(StringUtils::stripPrefix($movie->title)),
                'summary' => $movie->summary,
                'thumb' => $movie->thumb,
                'duration' => round($movie->duration / 60),
                'year' => $movie->year,
                'actors' => implode(', ', $movie->),
                'genres' => implode(', ', $movie->getGenres()),
            ];
        });

        $movies = $movies->sortBy('title');

        $catalog = view('templates/catalog', [
            'movies' => $movies,
            'truncateDescription' => true,
            'htmlOnly' => false,
        ])->render();

        $fileName = tempnam(sys_get_temp_dir(), 'plex_') . '.pdf';
        Browsershot::html($catalog)
            ->noSandbox()
            ->format('A4')
            ->timeout(3000)
            ->margins(25, 0, 15, 0)
            ->footerHtml('<div class="pageNumber"></div>')
            ->save($fileName);
    }
}
