<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
    <title>Plex catalog</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            @if($htmlOnly)
            margin: auto;
            max-width: 1024px;
            @endif
        }
        .summary {
            font-size: small;
        }
        .ellipsed {
            height: 118px;
            display: -webkit-box;
            -webkit-line-clamp: 6;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>
<div class="container-fluid ms-5 me-5">
    @foreach($movies as $movie)
        <div class="row mb-3" style="page-break-inside: avoid;">
            <div class="col-sm-3">
                <img src="{{ $server }}:{{ $port }}{{ $movie['thumb'] }}?X-Plex-Token={{ $token }}" width="150px" />
            </div>
            <div class="col-sm-7">
                <h3>{{ $movie['title'] }}</h3>
                <div class="clearfix">
                    <div class="float-start">
                        {{ $movie['duration'] }} min
                    </div>
                    <div class="float-end">
                        <em>{{ $movie['genres'] }}</em>
                    </div>
                </div>
                <div>
                    <em>{{ $movie['actors'] }}</em>
                </div>
                <div class="mt-3 summary @if($truncateDescription) ellipsed @endif" style="text-align: justify;">
                    {{ $movie['summary'] }}
                </div>
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
