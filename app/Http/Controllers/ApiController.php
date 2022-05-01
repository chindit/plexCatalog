<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ApiController extends Controller
{
    public function getPlexToken(Request $request, XmlEncoder $serializer): JsonResponse
    {
        $url = http_build_query([
            'X-Plex-Version' => '1.26',
            'X-Plex-Platform' => 'Linux',
            'X-Plex-Platform-Version' => '1.26',
            'X-Plex-Provides' => 'controller',
            'X-Plex-Client-Identifier' => '7b8f37cc-114d-4757-867c-5a2f3cd8a67a',
            'X-Plex-Product' => 'Plex-Catalog',
            'X-Plex-Device' => 'Automated-Script',
            'X-Plex-Device-Name' => 'Automated-Script',
            'X-Plex-Username' => $request->get('email'),
        ]);
        $req = \Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($request->get('email') . ':' . $request->get('password')),
        ])->post('https://plex.tv/users/sign_in.xml?' . $url);

        if ($req->failed())
        {
            return response()->json(['error' => 'Invalid username or password'], Response::HTTP_BAD_REQUEST);
        }
        $decodedResponse = $serializer->decode($req->toPsrResponse()->getBody()->getContents(), 'array');

        /** @var User $user */
        $user = \Auth::user();
        $user->server_token = $decodedResponse['@authToken'];
        $user->save();

        return response()->json(['token' => $decodedResponse['@authToken']]);
    }
}
