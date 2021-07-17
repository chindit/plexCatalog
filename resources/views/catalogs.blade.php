@extends('base')

@section('content')
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

    <form method="POST" action="{{ route('report') }}">
        <table>
            <thead>
            <tr>
                <th>Use</th>
                <th>Name</th>
            </tr>
            </thead>
            <tbody>
            @foreach($catalogs as $id => $name)
                <tr>
                    <td>
                        <input type="checkbox" name="ids[]" value="{{ $id }}" />
                    </td>
                    <td>
                        {{ $name }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mb-3 mt-5">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="truncateDescription" value="true" id="truncateDescription">
                <label class="form-check-label" for="truncateDescription">
                    Truncate description if too big (max height allowed is thumbnail height)
                </label>
            </div>
        </div>
        <div class="mt-3 mb-3 alert alert-warning">
            Generation can take up to 5 minutes.  Just be patient.<br>
            If you do not want to wait, clone the project and run it on your own machine.
        </div>
        <div class="mb-3">
            <input type="submit" class="btn btn-primary" value="Generate report" />
        </div>
        @csrf
    </form>
@endsection
