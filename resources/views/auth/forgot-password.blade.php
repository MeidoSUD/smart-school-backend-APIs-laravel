@extends('layouts.public')

@section('title', __('messages.forgot_password') . ' - ' . __('messages.app_name'))

@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="card">
            <div class="login-header">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28" height="28">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <h1>{{ __('messages.forgot_password_title') }}</h1>
                <p>{{ __('messages.forgot_password_subtitle') }}</p>
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

            <form method="POST" action="{{ route('web.forgot.password.post') }}" data-loading>
                @csrf
                <div class="form-group">
                    <label class="form-label" for="identifier">{{ __('messages.email_or_phone') }}</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        class="form-input @error('identifier') is-invalid @enderror"
                        value="{{ old('identifier') }}"
                        placeholder="{{ __('messages.email_or_phone_placeholder') }}"
                        required
                        autofocus
                    >
                    @error('identifier')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">{{ __('messages.forgot_password_hint') }}</div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <span class="spinner"></span>
                    <span class="btn-text">{{ __('messages.send_reset_link') }}</span>
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
