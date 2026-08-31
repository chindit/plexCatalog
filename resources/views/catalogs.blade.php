@extends('base')

@section('content')
    <style>
        .loader {
            display: inline-block;
            animation: loader-spin 1s linear infinite;
        }

        @keyframes loader-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        window.reportChannelToken = @json($channelToken);
    </script>

    <h3>List of available catalogs</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        Only Movies and TV Show catalogs are currently supported
    </div>

    <p>Please check catalogs you want to include in your report</p>
    <div class="alert alert-secondary d-none" role="status">
        <span class="loader">⏳</span>
        <span id="report-status"></span>
    </div>

    <form id="report-form" method="POST" action="{{ route('report') }}">
            @foreach($catalogs as $id => $name)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{ $id }}" id="checkbox-{{ $id }}" name="ids[]">
                    <label class="form-check-label" for="checkbox-{{ $id }}">
                        {{ $name }}
                    </label>
                </div>
            @endforeach
        <div class="mb-3 mt-5">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="truncateDescription" value="true" id="truncateDescription">
                <label class="form-check-label" for="truncateDescription">
                    Truncate description if too big (max height allowed is thumbnail height)
                </label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="htmlOnly" value="true" id="htmlOnly">
                <label class="form-check-label" for="htmlOnly">
                    Only render a HTML version
                </label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="unwatchedOnly" value="true" id="unwatchedOnly">
                <label class="form-check-label" for="unwatchedOnly">
                    Only list unwatched movies/TV shows
                </label>
            </div>
        </div>
        <div class="mt-3 mb-3 alert alert-warning">
            Generation can take up to 5 minutes.  Just be patient.<br>
            If you do not want to wait, clone the project and run it on your own machine.
        </div>
        <div class="mb-3">
            <input id="report-submit" type="submit" class="btn btn-primary" value="Generate report" />
        </div>
        @csrf
    </form>

    <script>
        document.getElementById('report-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            const form = event.currentTarget;
            const submit = document.getElementById('report-submit');
            const status = document.getElementById('report-status');
            const banner = status.closest('.alert');

            submit.disabled = true;
            banner.className = 'alert alert-info';
            status.textContent = 'Report queued...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Request failed (${response.status})`);
                }

                status.textContent = 'Report queued. Waiting for updates...';
            } catch (error) {
                submit.disabled = false;
                banner.className = 'alert alert-danger';
                status.textContent = `Unable to queue report: ${error.message}`;
            }
        });
    </script>
@endsection
