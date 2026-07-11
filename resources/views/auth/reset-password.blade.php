@extends('layouts.public')

@section('title', __('messages.reset_password') . ' - ' . __('messages.app_name'))

@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="card">
            <div class="login-header">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28" height="28">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>
                <h1>{{ __('messages.reset_password_title') }}</h1>
                <p>{{ __('messages.reset_password_subtitle') }}</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18" style="flex-shrink:0;margin-top:1px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('web.reset.password.post') }}" data-loading>
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? '' }}">

                <div class="form-group">
                    <label class="form-label" for="email">{{ __('messages.email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input @error('email') is-invalid @enderror"
                        value="{{ old('email', $email ?? '') }}"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">{{ __('messages.new_password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input @error('password') is-invalid @enderror"
                        placeholder="{{ __('messages.password_placeholder') }}"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">{{ __('messages.confirm_password') }}</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="{{ __('messages.confirm_password_placeholder') }}"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <span class="spinner"></span>
                    <span class="btn-text">{{ __('messages.reset_password') }}</span>
                </button>
            </form>

            <div class="divider"></div>

            <div class="text-center">
                <a href="{{ route('web.login') }}" class="text-sm">&larr; {{ __('messages.back_to_login') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
