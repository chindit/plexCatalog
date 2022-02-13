<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="logo" />
            </a>Register
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-3 row">
                <label for="name" class="col-sm-3 col-form-label">{{ __('Name') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
            </div>

            <!-- Email Address -->
            <div class="mb-3 row">
                <label for="email" class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3 row">
                <label for="password" class="col-sm-3 col-form-label">{{ __('Password') }}</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3 row">
                <label for="password_confirmation" class="col-sm-3 col-form-label">{{ __('Confirm Password') }}</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <div class="d-float">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="float-end">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
