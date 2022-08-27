<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessCollection;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = User::find(Auth::id());

        $needSync = ($user->last_sync ?: new \DateTime('last month')) < new \DateTime('last week');
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
}
