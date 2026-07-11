@extends('layouts.public')

@section('title', __('messages.login') . ' - ' . __('messages.app_name'))

@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="card">
            <div class="login-header">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28" height="28">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                    </svg>
                </div>
                <h1>{{ __('messages.login_title') }}</h1>
                <p>{{ __('messages.login_subtitle') }}</p>
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

            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('web.login.post') }}" data-loading>
                @csrf
                <div class="form-group">
                    <label class="form-label" for="username">{{ __('messages.username') }}</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input @error('username') is-invalid @enderror"
                        value="{{ old('username') }}"
                        placeholder="{{ __('messages.username_placeholder') }}"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    @error('username')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">{{ __('messages.password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input @error('password') is-invalid @enderror"
                        placeholder="{{ __('messages.password_placeholder') }}"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="text-sm text-muted">{{ __('messages.remember_me') }}</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <span class="spinner"></span>
                    <span class="btn-text">{{ __('messages.login') }}</span>
                </button>
            </form>

            <div class="divider"></div>

            <div class="text-center">
                <a href="{{ route('web.forgot.password') }}" class="text-sm">{{ __('messages.forgot_password') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
