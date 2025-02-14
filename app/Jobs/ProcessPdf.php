<?php

namespace App\Jobs;

use App\Models\Catalog;
use App\Models\Media;
use App\Models\User;
use App\Service\StringUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class ProcessPdf implements ShouldQueue
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
        /** @var Collection $movies */
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

        $pageLimit = 10;
        $pages = ceil($movies->count()/4);
        $volumes = ceil($pages/$pageLimit);

        $moviesByVolumes = $movies->chunk($pageLimit * 4);

        if ($volumes > 1 && $pages % $pageLimit < ($pageLimit / 4)) {
            $lastVolume = $moviesByVolumes->pop();
            $previousVolume = $moviesByVolumes->pop();
            $moviesByVolumes->push($previousVolume->merge($lastVolume));
        }

        $moviesByVolumes->each(function(Collection $moviesByVolumes) {
            $catalog = view('templates/catalog', [
                'movies' => $moviesByVolumes,
                'truncateDescription' => true,
                'htmlOnly' => false,
            ])->render();

            $fileName = tempnam(sys_get_temp_dir(), 'plex_') . '.pdf';
            Browsershot::html($catalog)
                ->noSandbox()
                ->format('A4')
                ->timeout(30_000)
                ->protocolTimeout(30_000)
                ->margins(25, 0, 15, 0)
                ->showBrowserHeaderAndFooter()
                ->hideHeader()
                ->footerHtml('<style>
           body { margin: 0; font-size: 12px; }
           .footer {
               font-size: 12px;
               width: 90%;
               text-align: right;
               margin-top: 5px;
           }
           .pageNumber:before {
               content: counter(page, decimal);
           }
           .totalPages:before {
               content: counter(pages, decimal);
           }
       </style>
       <div class="footer">
           <span class="pageNumber"></span>/<span class="totalPages"></span>
       </div>
')
                ->save($fileName);
        });
    }
}
