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
                    Do you want to (re)sync your collection ?
                    <a class="btn btn-info" href="#">Yes</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in!
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
