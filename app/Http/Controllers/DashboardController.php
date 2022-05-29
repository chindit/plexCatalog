<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = User::find(Auth::id());

        $needSync = ($user->last_sync ?: new \DateTime('last month')) < new \DateTime('last week');
        return view('dashboard', ['hasServer' => $user->server_token, 'needSync' => $needSync]);
    }
}
