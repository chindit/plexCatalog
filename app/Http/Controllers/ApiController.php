<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getPlexToken(Request $request): JsonResponse
    {
        $req = \Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($request->get('email') . ':' . $request->get('password')),
        ])->post('https://plex.tv/users/sign_in.xml');

        dd($req->toPsrResponse()->getBody()->getContents());
        //return response()->json();
    }
}
