<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CatalogRequest;
use App\Jobs\CleanThumbs;
use App\Jobs\DownloadThumbs;
use App\Jobs\ProcessCollection;
use App\Jobs\ProcessPdf;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use Chindit\PlexApi\Model\Library;
use Chindit\PlexApi\PlexServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $needSync = $user->last_sync?->lt(Carbon::now()->subMonth()) ?? true;

        return view(
            'dashboard',
            [
                'hasServer' => $user->server_token,
                'needSync' => $needSync,
                'user' => $user,
                'medias' => [
                    'formats' => Media::select(['video_codec', \DB::raw('COUNT(*) as total')])->groupBy('video_codec')->get(),
                    'audio' => Media::select(['audio_codec', \DB::raw('COUNT(*) as total')])->groupBy('audio_codec')->get(),
                ]
            ]
        );
    }

    public function sync()
    {
        if (Auth::user()) {
            $this->dispatch(new ProcessCollection(Auth::user()));
            $user = User::find(Auth::id());
            $user->last_sync = Carbon::now();
            $user->save();
        }

        return redirect()->route('dashboard');
    }

    public function catalog()
    {
        /** @var User $user */
        $user = Auth::user();
        $server = new PlexServer($user->server_url, $user->server_token, (int)$user->server_port);

        return view('catalogs', [
            'catalogs' => collect($server->libraries())->mapWithKeys(function (Library $library) {
                return [$library->getId() => $library->getTitle()];
            })
        ]);
    }

    public function createCatalog(CatalogRequest $request)
    {

        /** @var User $user */
        $user = Auth::user();

        $catalog = new \App\Models\Catalog();
        $catalog->user_id = $user->id;
        $catalog->options = $request->validated();
        $catalog->save();

        $this->dispatch(
            (new DownloadThumbs($catalog))
                ->chain([
                    new ProcessPdf($catalog),
                    //new CleanThumbs($catalog),
                ])
        );

        return redirect()->route('catalog');
    }
}
