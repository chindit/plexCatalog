<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function server()
    {
        return view('server');
    }

    public function createServer(Request $request)
    {
        /** @var User $user */
        $user = \Auth::user();

        $user->server_url = $request->get('serverUrl');
        $user->server_port = $request->get('serverPort');
        $user->server_token = $request->get('serverToken', $user->server_token);
        $user->save();

        return redirect('/dashboard');
    }
}
