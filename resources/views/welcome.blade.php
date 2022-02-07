@extends('base')

@section('content')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col">
                    <label for="serverAddress" class="form-label">Plex server address</label>
                    <input type="url" class="form-control" name="serverAddress" id="serverAddress" value="{{ old('serverAddress') }}">
                </div>
                <div class="col">
                    <label for="serverPort" class="form-label">Plex port</label>
                    <input type="number" class="form-control" name="serverPort" id="serverPort" value="32400">
                </div>
            </div>
            <div class="mb-3">
                <label for="serverToken" class="form-label">Plex server token</label>
                <input type="text" class="form-control" name="serverToken" id="serverToken" value="{{ old('serverToken') }}">
            </div>
            <div class="mb-3">
                <input type="submit" class="btn btn-primary" value="Connect" />
            </div>
            @csrf
        </form>
@endsection
