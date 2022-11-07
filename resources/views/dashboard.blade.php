<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!$hasServer)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    You haven't activated any server yet.<br>
                    Do you want to activate a server now ?<br>
                    <a class="btn btn-info" href="{{ route('add_server') }}">Yes</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif($needSync)
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    Do you want to (re)sync your collection ?  @if($user->last_sync)Last sync was {{ $user->last_sync->diffForHumans() }}@endif
                    <a class="btn btn-info" href="{{ route('sync') }}">Yes</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg ">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>You have {{ $user->medias()->count()  }} medias (including show episodes)</p>
                </div>
            </div>
            <div class="flex mb-4">
                <div class="w-1/2">
                    <div id="chart_video_div"></div>
                </div>
                <div class="w-1/2">
                    <div id="chart_audio_div"></div>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Load the Visualization API and the corechart package.
        google.charts.load('current', {'packages': ['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawVideoCodecChart);
        google.charts.setOnLoadCallback(drawAudioCodecChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawVideoCodecChart() {

            // Create the data table.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Codec');
            data.addColumn('number', 'Count');
            data.addRows([
                    @foreach($medias['formats'] as $format)
                ['{{ $format['video_codec'] }}', {{ $format['total'] }}],
                @endforeach
            ]);
            // Set chart options
            var options = {
                'title': 'Video codec',
                'width': 600,
                'height': 500
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.PieChart(document.getElementById('chart_video_div'));
            chart.draw(data, options);
        }

        function drawAudioCodecChart() {

            // Create the data table.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Codec');
            data.addColumn('number', 'Count');
            data.addRows([
                    @foreach($medias['audio'] as $format)
                ['{{ $format['audio_codec'] }}', {{ $format['total'] }}],
                @endforeach
            ]);
            // Set chart options
            var options = {
                'title': 'Audio codec',
                'width': 600,
                'height': 500
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.PieChart(document.getElementById('chart_audio_div'));
            chart.draw(data, options);
        }
    </script>
</x-app-layout>
