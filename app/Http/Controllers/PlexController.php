<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Cookie as SfCookie;

class PlexController extends Controller
{
    public function listcatalogs(Request $request)
    {
        $request->validate([
            'serverAddress' => 'required|string',
            'serverToken' => 'required|string',
            'serverPort' => 'int',
        ]);

        try {
            $catalogRequest = Http::get($request->get('serverAddress') . ':' . $request->get('serverPort', 32400) . '/library/sections?X-Plex-Token=' . $request->get('serverToken'));

            if ($catalogRequest->failed()) {
                throw new \Exception('Unable to reach specific url');
            }
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }

        $xmlResponse = simplexml_load_string($catalogRequest->toPsrResponse()->getBody()->getContents());

        $catalogs = collect();
        foreach($xmlResponse->Directory as $t) {
            $catalogs->put((string)$t->attributes()['key'], (string)$t->attributes()['title']);
        }

        return response()
            ->view('catalogs', ['catalogs' => $catalogs])
            ->withCookie(new SfCookie('plex', json_encode(
                [
                    's' => $request->get('serverAddress'),
                    't' => $request->get('serverToken'),
                    'p' => $request->get('serverPort', 32400),
                ],
                JSON_THROW_ON_ERROR
            )
            ));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $movies = collect();

        $server = json_decode($request->cookie('plex'), true);

        foreach ($request->get('ids') as $id) {
            $catalogRequest = Http::get($server['s'] . ':' . $server['p'] . '/library/sections/' . $id . '/all?X-Plex-Token=' . $server['t']);

            $xmlResponse = simplexml_load_string($catalogRequest->toPsrResponse()->getBody()->getContents());

            foreach ($xmlResponse->Video as $movie) {
                $genres = collect();
                $actors = collect();
                foreach ($movie->Genre as $genre) {
                    $genres->push((string)$genre->attributes()['tag']);
                }
                foreach ($movie->Role as $actor) {
                    $actors->push((string)$actor->attributes()['tag']);
                }
                $movies->push([
                    'title' => (string)$movie->attributes()['title'],
                    'summary' => (string)$movie->attributes()['summary'],
                    'thumb' => (string)$movie->attributes()['thumb'],
                    'duration' => round((int)$movie->attributes()['duration']/60_000, 0),
                    'year' => (int)$movie->attributes()['year'],
                    'actors' => $actors->implode(', '),
                    'genres' => $genres->implode(', '),
                ]);
            }
        }

        $movies = $movies->map(function(array $movie) {
           $title = $movie['title'];
           if (str_starts_with($title, 'Le ')) {
               $movie['title'] = substr($title, 3) . ' (Le)';
           }
           else if (str_starts_with($title, 'La ')) {
               $movie['title'] = substr($title, 3) . ' (La)';
           }
           else if (str_starts_with($title, 'L')) {
               $movie['title'] = substr($title, 2) . ' (L)';
           }

           return $movie;
        });

        $movies = $movies->sortBy('title');

        $catalog = view('templates/catalog', [
            'server' => $server['s'],
            'token' => $server['t'],
            'port' => $server['p'],
            'movies' => $movies,
            'truncateDescription' => $request->get('truncateDescription', false) === "true",
        ])->render();

        try {
            $fileName = tmpfile() . '.pdf';
            Browsershot::html($catalog)
                ->noSandbox()
                ->format('A4')
                ->timeout(3000)
                ->margins(25, 0, 15, 0)
                ->footerHtml('<div class="pageNumber"></div>')
                ->save($fileName);

            return response()->download($fileName, 'catalog.pdf');
        } catch (\Throwable $throwable) {
            return response()->redirectTo('/')->withErrors(new MessageBag(['serverAddress' => $throwable->getMessage()]));
        }
    }
}
