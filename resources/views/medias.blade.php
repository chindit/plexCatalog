<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <h1>Medias</h1>

        <table class="table table-bordered table-hover">
            <thead>
            <th>@sortablelink('title', 'Title')</th>
            <th>@sortablelink('video_codec', 'Video')</th>
            <th>@sortablelink('audio_codec', 'Audio')</th>
            <th>@sortablelink('aspect_ratio', 'Aspect ratio')</th>
            <th>@sortablelink('bitrate', 'Bitrate')</th>
            <th>@sortablelink('framerate', 'Framerate')</th>
            <th>@sortablelink('resolution', 'Resolution')</th>
            <th>@sortablelink('container', 'Container')</th>
            <th>@sortablelink('duration', 'Duration')</th>
            </thead>
            <tbody>
            @if ($medias->count() == 0)
                <tr>
                    <td colspan="5">No Media to display.</td>
                </tr>
            @endif

            @foreach ($medias as $media)
                <tr>
                    <td>{{ $media->title }}</td>
                    <td>{{ $media->video_codec }}</td>
                    <td>{{ $media->audio_codec }}</td>
                    <td>{{ $media->aspect_ratio }}</td>
                    <td>{{ $media->bitrate }} kbps</td>
                    <td>{{ $media->framerate }}</td>
                    <td>{{ $media->resolution }}</td>
                    <td>{{ $media->container }}</td>
                    <td>{{ Carbon\CarbonInterval::seconds($media->duration)->cascade()->forHumans() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {!! $medias->appends(Request::except('page'))->render() !!}
<!--
        <p>
            Displaying {{$medias->count()}} of {{ $medias->total() }} product(s).
        </p>-->

    </div>
</x-app-layout>
