<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.app_name'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|IBM+Plex+Sans+Arabic:wght@400,500,600,700&display=swap" rel="stylesheet"/>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --success: #16a34a;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --radius: 0.5rem;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        html { font-family: 'Figtree', 'IBM Plex Sans Arabic', sans-serif; line-height: 1.5; -webkit-text-size-adjust: 100%; }
        body { background: var(--gray-50); color: var(--gray-800); min-height: 100vh; display: flex; flex-direction: column; }

        a { color: var(--primary); text-decoration: none; transition: color 0.15s; }
        a:hover { color: var(--primary-hover); }

        .container { max-width: 72rem; margin: 0 auto; padding: 0 1rem; width: 100%; }

        .navbar {
            background: #fff;
            border-bottom: 1px solid var(--gray-200);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow);
        }
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--gray-900);
        }
        .navbar-brand svg { width: 2rem; height: 2rem; color: var(--primary); }
        .navbar-links { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .navbar-links a {
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            transition: all 0.15s;
        }
        .navbar-links a:hover { background: var(--gray-100); color: var(--gray-900); }
        .navbar-links a.active { background: var(--primary-light); color: var(--primary); }

        .lang-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            background: #fff;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.15s;
        }
        .lang-toggle:hover { background: var(--gray-50); border-color: var(--gray-400); }

        .main-content { flex: 1; padding: 2rem 0; }

        .card {
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            line-height: 1.25;
            white-space: nowrap;
        }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover:not(:disabled) { background: var(--primary-hover); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover:not(:disabled) { background: var(--danger-hover); }
        .btn-outline {
            background: #fff;
            border-color: var(--gray-300);
            color: var(--gray-700);
        }
        .btn-outline:hover:not(:disabled) { background: var(--gray-50); border-color: var(--gray-400); }
        .btn-block { width: 100%; }

        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
        }
        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            color: var(--gray-900);
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        .form-input.is-invalid { border-color: var(--danger); }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15); }
        .form-error { color: var(--danger); font-size: 0.8125rem; margin-top: 0.25rem; }
        .form-hint { color: var(--gray-500); font-size: 0.8125rem; margin-top: 0.25rem; }
        .form-check { display: flex; align-items: center; gap: 0.5rem; }
        .form-check input[type="checkbox"] { width: 1rem; height: 1rem; accent-color: var(--primary); }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        .footer {
            background: #fff;
            border-top: 1px solid var(--gray-200);
            padding: 1.5rem 0;
            margin-top: auto;
        }
        .footer .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        .spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }
        .btn.loading .spinner { display: inline-block; }
        .btn.loading .btn-text { opacity: 0.6; }

        @keyframes spin { to { transform: rotate(360deg); } }

        [dir="rtl"] { text-align: right; }
        [dir="ltr"] { text-align: left; }

        .privacy-content h1 { font-size: 1.75rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; }
        .privacy-content h2 { font-size: 1.25rem; font-weight: 600; color: var(--gray-800); margin: 2rem 0 0.75rem; padding-top: 1.25rem; border-top: 1px solid var(--gray-200); }
        .privacy-content p { color: var(--gray-600); line-height: 1.75; margin-bottom: 1rem; }
        .privacy-content ul { color: var(--gray-600); margin: 0.5rem 0 1rem; padding-[dir-margin]: 1.5rem; }
        .privacy-content ul li { margin-bottom: 0.5rem; line-height: 1.6; }
        .privacy-content .last-updated { color: var(--gray-400); font-size: 0.875rem; margin-bottom: 2rem; }

        [dir="rtl"] .privacy-content ul { padding-right: 1.5rem; padding-left: 0; }
        [dir="ltr"] .privacy-content ul { padding-left: 1.5rem; padding-right: 0; }

        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 10rem);
            padding: 2rem 1rem;
        }
        .login-card { width: 100%; max-width: 28rem; }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-header .icon {
            width: 3.5rem;
            height: 3.5rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .login-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--gray-900); }
        .login-header p { color: var(--gray-500); font-size: 0.875rem; margin-top: 0.375rem; }

        .divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; color: var(--gray-400); font-size: 0.8125rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--gray-200); }

        .text-center { text-align: center; }
        .text-sm { font-size: 0.875rem; }
        .text-muted { color: var(--gray-500); }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .hidden { display: none !important; }

        @media (max-width: 640px) {
            .navbar .container { flex-wrap: wrap; }
            .card { padding: 1.25rem; }
            .privacy-content h1 { font-size: 1.375rem; }
            .footer .container { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="{{ route('privacy.policy') }}" class="navbar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
                {{ __('messages.app_name') }}
            </a>
            <div class="navbar-links">
                <a href="{{ route('privacy.policy') }}" class="{{ request()->routeIs('privacy.policy') ? 'active' : '' }}">{{ __('messages.privacy_policy') }}</a>
                <a href="{{ route('web.login') }}" class="{{ request()->routeIs('web.login') ? 'active' : '' }}">{{ __('messages.login') }}</a>
                <button class="lang-toggle" onclick="toggleLocale()" title="{{ __('messages.switch_lang') }}">
                    {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                </button>
            </div>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <span>{{ __('messages.app_name') }} &copy; {{ date('Y') }}</span>
            <a href="{{ route('privacy.policy') }}">{{ __('messages.privacy_policy') }}</a>
        </div>
    </footer>

    <script>
        function toggleLocale() {
            const current = '{{ app()->getLocale() }}';
            const next = current === 'ar' ? 'en' : 'ar';
            const url = new URL(window.location.href);
            url.searchParams.set('lang', next);
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[data-loading]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const btn = form.querySelector('[type="submit"]');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
