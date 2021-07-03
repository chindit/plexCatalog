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
        <div class="mb-3">
            <input type="submit" class="btn btn-primary" value="Generate report" />
        </div>
        @csrf
    </form>
@endsection
