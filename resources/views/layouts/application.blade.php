<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ getSelectedLanguage()->direction }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <meta name="description" content="@yield('description')" />

    <!-- Styles -->
    <style type="text/css">
        :root {
            --secondary-color: #536d79;
            --primary-color: {{ getSetting('PRIMARY_COLOR') }};
        }
    </style>
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fa.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css?version=') . getVersion() }}" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('storage/images/FAVICON.png') }}">

    @if (getSetting('PWA') == 'enabled')
        <link rel="manifest" href="/manifest.json">
    @endif

    @yield('style')

    <style>
        {!! getSetting('CUSTOM_CSS') !!}
    </style>
    {!! getSetting('CUSTOM_JS') !!}

    {{-- PWA Configuration --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00bef2">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="NetMeet">
    <link rel="apple-touch-icon" href="/storage/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/storage/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/storage/images/icons/icon-192x192.png">
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm application-header">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('storage/images/PRIMARY_LOGO.png') }}" alt="{{ getSetting('APPLICATION_NAME') }}"
                    class="logo-home">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    @if (getLanguages()->count() > 1)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-globe"></i> {{ getSelectedLanguage()->name }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                @foreach (getLanguages() as $language)
                                    <a class="dropdown-item @if (getSelectedLanguage()->name == $language->name) active @endif"
                                        href="{{ route('language', ['locale' => $language->code]) }}">{{ $language->name }}</a>
                                @endforeach
                            </div>
                        </li>
                    @endif

                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('pricing') && count(paymentGateways()) != 0 && getSetting('PAYMENT_MODE') == 'enabled')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pricing') }}">{{ __('Pricing') }}</a>
                            </li>
                        @endif

                        @if (Route::has('login') && getSetting('AUTH_MODE') == 'enabled')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register') && getSetting('AUTH_MODE') == 'enabled')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        @if (getAuthUserInfo('role') == 'admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin') }}">{{ __('Admin') }}</a>
                            </li>
                        @endif

                        @if (getSetting('AUTH_MODE') == 'enabled')
                            @if (Route::has('pricing') && count(paymentGateways()) != 0 && getSetting('PAYMENT_MODE') == 'enabled')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('pricing') }}">{{ __('Pricing') }}</a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a id="profileDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ getAuthUserInfo('username') }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                                <a class="dropdown-item" href="{{ route('profile.profile') }}">
                                    {{ __('Profile') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>

        <main class="pt-4 mb-5 mb-md-0">
            @yield('content')
        </main>

        <footer class="application-footer">
            <div class="container-fluid">
                <div class="row d-flex align-items-top">
                    <div class="col-12 col-md-9 text-md-left text-center pad-res">
                        <ul class="footer-links">
                            @foreach (getPages() as $page)
                                <li>
                                    <a href="{{ '/pages/' . $page->slug }}">{{ __($page->title) }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <p>{{ __('Copyright') }} &copy; {{ date('Y') }}
                            {{ getSetting('APPLICATION_NAME') }}. {{ __('All rights reserved') }}</p>
                    </div>
                    <div class="col-12 col-md-3 text-md-right text-center pad-res">
                        <div class="social-data">
                            <p><strong>{{ __('Share with your friends') }}</strong></p>
                            <ul class="social-links">
                                <li>
                                    <a href="" target="_blank" id="fbShare" rel="noreferrer">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="" target="_blank" id="twitterShare" rel="noreferrer">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="" target="_blank" id="waShare" rel="noreferrer">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <div class="cookie">
            <p><i class="fa fa-cookie-bite"></i>
                {{ __('This website uses cookies to ensure you get the best experience on our website') }}
                <a href="/pages/privacy-policy"> {{ __('Learn more') }}</a>
            </p>
            <button class="btn btn-theme confirm-cookie">{{ __('Got it') }}</button>
        </div>

        @if (isDemoMode())
            <div id="buy-now">
                <a id="buy-now-link" href="https://codecanyon.net/cart/configure_before_adding/31388330"
                    target="_blank"><span>$</span>{{ config('app.script_price') }}</a>
                <button class="buy-now-button" onclick="document.getElementById('buy-now-link').click();">
                    {{ __('Buy Now') }}
                </button>
            </div>
        @endif
    </div>

    @if (getSetting('PWA') == 'enabled')
        @include('include.pwa-installation-modal')

        <script type="text/javascript">
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/serviceworker.js', {
                    scope: '.'
                }).then(function(registration) {}, function(err) {});
            }
        </script>
    @endif

    <script>
        const cookieConsent = "{{ getSetting('COOKIE_CONSENT') }}";
        const googleAnalyticsTrackingId = "{{ getSetting('GOOGLE_ANALYTICS_ID') }}";
        const socialInvitation = "{{ getSetting('SOCIAL_INVITATION') }}";
        const pwa = "{{ getSetting('PWA') }}";

        const languages = {
            error_occurred: "{{ __('An error occurred, please try again') }}",
            data_updated: "{{ __('Data updated successfully') }}",
            no_meeting: "{{ __('The meeting does not exist') }}"
        }
    </script>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/main.js?version=') . getVersion() }}"></script>
    @yield('script')

    {{-- PWA Service Worker + Install Prompt --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js')
                    .then(function(reg) { console.log('PWA: SW registered'); })
                    .catch(function(err) { console.log('PWA: SW failed', err); });
            });
        }

        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        async function npInstallApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                return;
            }
            const ua = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(ua);
            const isAndroid = /Android/i.test(ua);
            if (isIOS) {
                alert('To install on iPhone/iPad:\n\n1. Tap the Share button at the bottom of Safari\n2. Tap "Add to Home Screen"\n3. Tap Add');
            } else if (isAndroid) {
                alert('To install on Android:\n\n1. Tap the menu (⋮) at the top right\n2. Tap "Add to home screen" or "Install app"');
            } else {
                alert('To install:\n\n1. Click the install icon in the address bar\n2. Or browser menu > "Install"');
            }
        }

        window.addEventListener('appinstalled', () => {
            const btns = document.querySelectorAll('.np-install-btn');
            btns.forEach(b => b.style.display = 'none');
        });
    </script>

    {{-- Persistent floating Download App button --}}
    <a href="javascript:void(0)" onclick="npInstallApp()" id="np-floating-install" class="np-install-btn" style="position:fixed;bottom:20px;right:20px;z-index:99999;background:linear-gradient(135deg,#00bef2 0%,#0099c8 100%);color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(0,190,242,0.4);font-family:-apple-system,'Segoe UI',Inter,sans-serif">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        
    </a>
</body>

</html>
