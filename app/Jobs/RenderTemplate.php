<?php

namespace App\Jobs;

use App\Models\CatalogJobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class RenderTemplate implements ShouldQueue
{
    use Queueable;

    private CatalogJobs $catalogJob;
    /**
     * Create a new job instance.
     */
    public function __construct(private int $jobId)
    {
        $this->catalogJob = CatalogJobs::findOrFail($this->jobId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $movies = collect($this->catalogJob->catalogData()->all())->flatten();
        $movies = $movies->sortBy(function (array $movie) {
            return Str::ascii($movie['title']);
        });

        $catalog = view('templates/catalog', [
            'movies' => $movies,
            'truncateDescription' => true,//$request->get('truncateDescription', false) === "true",
            'htmlOnly' => false,//$request->get('htmlOnly', false) === "true",
        ])->render();
    }
}
