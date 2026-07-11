@extends('layouts.public')

@section('title', __('messages.privacy_policy') . ' - ' . __('messages.app_name'))

@section('content')
<div class="container">
    <div class="card privacy-content">
        <h1>{{ __('messages.privacy_policy_title') }}</h1>
        <p class="last-updated">{{ __('messages.last_updated') }}: {{ date('F d, Y') }}</p>

        <h2>{{ __('messages.pp_introduction_title') }}</h2>
        <p>{{ __('messages.pp_introduction') }}</p>

        <h2>{{ __('messages.pp_collection_title') }}</h2>
        <p>{{ __('messages.pp_collection_intro') }}</p>
        <ul>
            <li>{{ __('messages.pp_collection_personal') }}</li>
            <li>{{ __('messages.pp_collection_academic') }}</li>
            <li>{{ __('messages.pp_collection_usage') }}</li>
            <li>{{ __('messages.pp_collection_device') }}</li>
        </ul>

        <h2>{{ __('messages.pp_use_title') }}</h2>
        <p>{{ __('messages.pp_use_intro') }}</p>
        <ul>
            <li>{{ __('messages.pp_use_manage') }}</li>
            <li>{{ __('messages.pp_use_communicate') }}</li>
            <li>{{ __('messages.pp_use_improve') }}</li>
            <li>{{ __('messages.pp_use_comply') }}</li>
            <li>{{ __('messages.pp_use_analytics') }}</li>
        </ul>

        <h2>{{ __('messages.pp_sharing_title') }}</h2>
        <p>{{ __('messages.pp_sharing_intro') }}</p>
        <ul>
            <li>{{ __('messages.pp_sharing_authorized') }}</li>
            <li>{{ __('messages.pp_sharing_legal') }}</li>
            <li>{{ __('messages.pp_sharing_service') }}</li>
        </ul>

        <h2>{{ __('messages.pp_security_title') }}</h2>
        <p>{{ __('messages.pp_security') }}</p>

        <h2>{{ __('messages.pp_retention_title') }}</h2>
        <p>{{ __('messages.pp_retention') }}</p>

        <h2>{{ __('messages.pp_rights_title') }}</h2>
        <p>{{ __('messages.pp_rights_intro') }}</p>
        <ul>
            <li>{{ __('messages.pp_rights_access') }}</li>
            <li>{{ __('messages.pp_rights_correction') }}</li>
            <li>{{ __('messages.pp_rights_deletion') }}</li>
            <li>{{ __('messages.pp_rights_portability') }}</li>
            <li>{{ __('messages.pp_rights_objection') }}</li>
        </ul>

        <h2>{{ __('messages.pp_children_title') }}</h2>
        <p>{{ __('messages.pp_children') }}</p>

        <h2>{{ __('messages.pp_cookies_title') }}</h2>
        <p>{{ __('messages.pp_cookies') }}</p>

        <h2>{{ __('messages.pp_changes_title') }}</h2>
        <p>{{ __('messages.pp_changes') }}</p>

        <h2>{{ __('messages.pp_contact_title') }}</h2>
        <p>{{ __('messages.pp_contact_intro') }}</p>
        <p>
            {{ __('messages.app_name') }}<br>
            {{ __('messages.pp_contact_email') }}: <a href="mailto:{{ $settings['email'] ?? 'info@school.com' }}">{{ $settings['email'] ?? 'info@school.com' }}</a><br>
            {{ __('messages.pp_contact_phone') }}: <a href="tel:{{ $settings['phone'] ?? '+000000000' }}">{{ $settings['phone'] ?? '+000000000' }}</a>
            @if(!empty($settings['address']))
                <br>{{ __('messages.pp_contact_address') }}: {{ $settings['address'] }}
            @endif
        </p>
    </div>
</div>
@endsection
