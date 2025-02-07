<?php

namespace App\Jobs;

use App\Models\Catalog;
use App\Models\Media;
use App\Models\User;
use App\Service\StringUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class ProcessPdf implements ShouldQueue
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
        $movies = $this->catalog->user->medias
            ->filter(function(Media $movie) {
                return in_array((string)$movie->library_id, $this->catalog->options['ids']);
            })
            ->map(function(Media $movie) {
            return [
                'id' => $movie->id,
                // Title should start with an uppercase for better sorting
                'title' => Str::ucfirst(StringUtils::stripPrefix($movie->title)),
                'summary' => $movie->summary,
                'thumb' => $movie->thumb,
                'duration' => round($movie->duration / 60),
                'year' => $movie->year,
                'actors' => implode(', ', $movie->actors),
                'genres' => implode(', ', $movie->genres),
            ];
        });

        $collator = new \Collator('fr_FR');
        $movies = $movies->sortBy(function (array $movie) use ($collator)
        {
            return $collator->getSortKey($movie['title']);
        });

        $catalog = view('templates/catalog', [
            'movies' => $movies,
            'truncateDescription' => true,
            'htmlOnly' => false,
            'server' => $this->catalog->user->server_url,
            'port' => $this->catalog->user->server_port,
            'token' => $this->catalog->user->server_token
        ])->render();

        $fileName = tempnam(sys_get_temp_dir(), 'plex_') . '.pdf';
        Browsershot::html($catalog)
            ->noSandbox()
            ->format('A4')
            ->timeout(30_000)
            ->protocolTimeout(30_000)
            ->margins(25, 0, 15, 0)
            ->footerHtml('<div class="pageNumber"></div>')
            ->save($fileName);
    }
}
