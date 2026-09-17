<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ getSelectedLanguage()->direction }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

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
@if(Auth::check())
<script>
    localStorage.setItem('pendingUsername', "{{ Auth::user()->username }}");
</script>
@endif

    <style>
        /* Hide ALL old PWA install elements (replaced by floating button) */
        #installationModal,
        #floatingDownloadBtn,
        .floating-download-btn,
        #cookie-consent .download-app-btn,
        a[id*="download"][id*="App"],
        a[class*="download"][class*="App"],
        .cookie-consent-download {
            display: none !important;
        }
    </style>

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

    @yield('style')

    <style>
        {!! getSetting('CUSTOM_CSS') !!}
    </style>
    
    <style>
        /* Meeting Modal Styles */
        .meeting-card-modal {
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        
        .meeting-card-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .meeting-info div {
            margin-bottom: 4px;
            color: #64748b;
        }
        
        .meeting-info i {
            width: 16px;
            color: #667eea;
        }
        
        .btn-group .btn {
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .dropdown-item.text-info:hover {
            background-color: #e0f2fe;
            color: #0284c7 !important;
        }
    </style>
    
    {!! getSetting('CUSTOM_JS') !!}
    <style>
        /* Navbar redesign - Corporate Premium */
        .navbar.navbar-expand-md {
            background: #ffffff !important;
            border-bottom: 1px solid #e4e4e7 !important;
            box-shadow: none !important;
            padding: 12px 32px !important;
            min-height: 64px;
        }
        .navbar-brand { padding: 0 !important; margin-right: 24px; }
        .navbar-brand .logo-inner { max-height: 32px; width: auto; }
        .navbar .nav-link {
            color: #3f3f46 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            padding: 8px 14px !important;
            border-radius: 6px;
            transition: all 0.15s;
            margin: 0 2px;
            display: inline-flex;
            align-items: center;
        }
        .navbar .nav-link:hover { color: #0a0a0a !important; background: #fafafa; }
        .navbar .nav-link i.fa { font-size: 13px; margin-right: 6px; color: #71717a; }
        .navbar-nav .nav-item .nav-link[href*="register"] {
            background: #0a0a0a !important;
            color: #ffffff !important;
            padding: 8px 18px !important;
            margin-left: 8px;
        }
        .navbar-nav .nav-item .nav-link[href*="register"]:hover {
            background: #27272a !important;
            color: #ffffff !important;
        }
        .navbar .dropdown-menu {
            border: 1px solid #e4e4e7 !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
            padding: 6px !important;
            margin-top: 8px !important;
            min-width: 200px;
        }
        .navbar .dropdown-item {
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            color: #3f3f46;
        }
        .navbar .dropdown-item:hover { background: #fafafa; color: #0a0a0a; }
        .navbar .dropdown-item.active, .navbar .dropdown-item:active {
            background: #0a0a0a !important;
            color: #ffffff !important;
        }
        .navbar .dropdown-item i.fa { color: #71717a; margin-right: 6px; width: 14px; }
        .navbar .dropdown-divider { border-top-color: #e4e4e7; margin: 4px 0; }
        .navbar .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0a0a0a;
            color: #ffffff !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
            text-transform: uppercase;
        }
        .navbar .set-profile { display: inline-flex !important; align-items: center; }
        .navbar-toggler {
            border: 1px solid #e4e4e7 !important;
            padding: 6px 10px !important;
            border-radius: 6px !important;
        }
        .navbar-toggler:focus { box-shadow: none !important; }
        body.np-auth-page .navbar.navbar-expand-md { display: none !important; }
        body.np-auth-page main.pt-4 { padding-top: 0 !important; margin-bottom: 0 !important; }
    </style>
    <style>
        /* ============================================
           Mobile responsive fixes
           ============================================ */
        @media (max-width: 768px) {
            /* Logo navbar — تصغير وضبط */
            .navbar-brand .logo-inner {
                max-height: 26px !important;
                width: auto !important;
            }
            .navbar.navbar-expand-md {
                padding: 10px 14px !important;
                min-height: 56px;
            }

            /* اخفاء الـ navbar toggler الإضافي على الموبايل (هنخلي واحد بس) */
            .navbar-toggler {
                display: none !important;
            }

            /* الـ navbar collapse تخفي على الموبايل، السايدبار هي اللي هتظهر */
            .navbar .navbar-collapse {
                display: none !important;
            }

            /* السايدبار toggle button فوق */
            #sidebarToggle, .sidebar-toggle {
                width: 40px !important;
                height: 40px !important;
                font-size: 18px !important;
            }

            /* تصغير الـ logo داخل الـ sidebar */
            .nav-sidebar .logo-icon {
                width: 32px !important;
                height: 32px !important;
                font-size: 16px !important;
            }
            .nav-sidebar .logo-text {
                font-size: 16px !important;
            }
            /* .nav-sidebar .sidebar-header {
                padding: 12px !important;
            } */

            /* تقليل الـ padding في الصفحات الداخلية */
            main.pt-4 {
                padding-top: 12px !important;
            }
            .container, .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            /* الكروت تاخد عرض كامل بـ padding أصغر */
            .card {
                margin-bottom: 12px !important;
            }
            .card-body {
                padding: 14px !important;
            }
            .card-header {
                padding: 12px 14px !important;
                font-size: 1.1rem !important;
            }

            /* H1 و H2 أصغر على الموبايل */
            h1, .h1 { font-size: 1.6rem !important; }
            h2, .h2 { font-size: 1.4rem !important; }
            h3, .h3 { font-size: 1.2rem !important; }

            /* Buttons تاخد padding أصغر */
            .btn {
                padding: 10px 18px !important;
                font-size: 14px !important;
            }

            /* Form inputs */
            .form-control {
                font-size: 14px !important;
                padding: 10px 12px !important;
            }

            /* Search button في my meetings */
            #search, button[id*="search"], .search-btn {
                width: auto !important;
                min-width: 110px;
            }

            /* Meeting cards زراير الـ 3 تحت تبقى أصغر */
            .meeting-card .btn-group .btn,
            .meeting-card-modal .btn {
                padding: 6px 10px !important;
                font-size: 13px !important;
            }
        }

        /* شاشات صغيرة جداً */
        @media (max-width: 480px) {
            .navbar-brand .logo-inner { max-height: 22px !important; }
            .navbar.navbar-expand-md { padding: 8px 10px !important; }
            .container, .container-fluid {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
            .card-body { padding: 12px !important; }
            h1, .h1 { font-size: 1.4rem !important; }
        }

        /* الـ auth pages — الـ left section يخفي على الموبايل أصلاً */
        @media (max-width: 991px) {
            body.np-auth-page .np-left { display: none !important; }
            body.np-auth-page .np-grid { grid-template-columns: 1fr !important; }
            body.np-auth-page .np-right { padding: 24px 18px !important; }
        }
    </style>
</head>


<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('storage/images/PRIMARY_LOGO.png') }}" alt="{{ getSetting('APPLICATION_NAME') }}"
                    class="logo-inner">
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
                        <li class="nav-item d-md-none">
                            <a class="nav-link np-install-btn" href="javascript:void(0)" onclick="npInstallApp()" style="background:linear-gradient(135deg,#00bef2 0%,#0099c8 100%);color:#fff !important;border-radius:8px;margin-top:4px">
                                📱 Download App
                            </a>
                        </li>
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
                        <a class="nav-link dropdown-toggle" href="#" id="myMeetingsDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-calendar-alt"></i> {{ __('My Meetings') }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="myMeetingsDropdown">
                     @if(isset($meetings) && $meetings->count())
                      @foreach ($meetings as $meeting)
                <a class="dropdown-item meeting-title-item" href="#" data-id="{{ $meeting->id }}">
                    {{ $meeting->title }}
                </a>
                   @endforeach
                   
                   @if(isset($hasMoreMeetings) && $hasMoreMeetings)
                   <div class="dropdown-divider"></div>
                   <a class="dropdown-item text-info font-weight-bold" href="#" 
                    data-toggle="modal" data-target="#allMeetingsModal">
                    <i class="fa fa-ellipsis-h mr-1"></i> {{ __('Others') }} ({{ $totalMeetings - 5 }})
                   </a>
                   @endif
            @else
            <span class="dropdown-item text-muted">{{ __('No meetings yet') }}</span>
            @endif
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-primary font-weight-bold" href="#" 
         data-toggle="modal" data-target="#createMeeting" id="createMeetingNav">
         <i class="fa fa-plus mr-1"></i> {{ __('Create Meeting') }}
</a>
    </div>
</li>

                        <li class="nav-item dropdown">
                            <a id="profileDropdown" class="nav-link dropdown-toggle set-profile" href="#"
                                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ getAuthUserInfo('username') }}
                                @if (getAuthUserInfo('avatar'))
                                    <img src="{{ asset('storage/avatars/' . getAuthUserInfo('avatar')) }}"
                                        class="user-avatar">
                                @else
                                    <span class="user-avatar">{{ ucfirst(getAuthUserInfo('username')[0]) }}</span>
                                @endif
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

        <footer class="app-footer">
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

           
       @if(isset($meetings))
document.querySelectorAll('.meeting-title-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();

        const meetingId = this.getAttribute('data-id');
        const meetings = @json($meetings);

        // Find selected meeting
        const meeting = meetings.find(m => String(m.id) === String(meetingId));
        if (!meeting) return;

        // Redirect to dashboard with meeting parameter
        window.location.href = '/dashboard?meeting=' + meeting.id;
    });
});

// Handle Others button click to load all meetings
$(document).on('click', '[data-target="#allMeetingsModal"]', function(e) {
    e.preventDefault();
    loadAllMeetings();
});

// Function to load all meetings in the modal
function loadAllMeetings() {
    console.log('loadAllMeetings function called - meetings are pre-loaded in the view');
    
    // Debug: Check how many meetings are available
    const meetingsCount = {{ isset($allMeetings) ? $allMeetings->count() : 0 }};
    console.log('Available meetings count:', meetingsCount);
    
    if (meetingsCount > 0) {
        console.log('Meetings data:', @json(isset($allMeetings) ? $allMeetings : []));
    }
    
    // Meetings are now loaded directly in the modal HTML, so no AJAX call needed
    // Just show the modal
    $('#allMeetingsModal').modal('show');
}

// Function to redirect to meeting edit on dashboard
function redirectToMeeting(meetingId) {
    console.log('redirectToMeeting called with:', meetingId);
    $('#allMeetingsModal').modal('hide');
    
    // If it's a numeric ID, redirect to dashboard with meeting parameter
    // If it's a meeting_id (string), redirect directly to the meeting
    if (!isNaN(meetingId) && meetingId > 0) {
        // It's a numeric ID, redirect to dashboard to highlight the meeting
        window.location.href = '/dashboard?meeting=' + meetingId;
    } else {
        // It's a meeting_id string, redirect directly to the meeting
        window.location.href = '/meeting/' + meetingId;
    }
}
@endif
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
            no_meeting: "{{ __('The meeting does not exist') }}",
            meeting_created: "{{ __('The meeting has been created') }}",
            confirmation: "{{ __('Are you sure') }}",
            meeting_deleted: "{{ __('The meeting has been deleted') }}",
            link_copied: "{{ __('Meeting link has been copied to the clipboard') }}",
            meeting_updated: "{{ __('The meeting has been updated') }}",
            sending_invite: "{{ __('Sending the invitation') }}",
            invite_sent: "{{ __('Invitation has been sent') }}",
            inviteMessage: "{{ __('Hey there! Join me for a meeting at this link') }}",
            no_session: "{{ __('Could not get the session details') }}",
            kicked: "{{ __('You have been kicked out of the meeting') }}",
            uploading: "{{ __('Uploading the file') }}",
            meeting_ended: "{{ __('Meeting ended') }}",
            cant_connect: "{{ __('Could not connect to the server, please try again later') }}",
            invalid_password: "{{ __('The password is invalid') }}",
            no_device: "{{ __('Could not get the devices, please check the permissions and try again. Error') }}",
            approve: "{{ __('Approve') }}",
            decline: "{{ __('Decline') }}",
            request_join_meeting: "{{ __('Request to join the meeting') }}",
            request_declined: "{{ __('Your request has been declined by the moderator') }}",
            double_click: "{{ __('Double click on the video to make it fullscreen') }}",
            single_click: "{{ __('Single click on the video to turn picture-in-picture mode on') }}",
            error_message: "{{ __('An error occurred') }}",
            kick_user: "{{ __('Kick this user') }}",
            participant_joined: "{{ __('A participant has joined the meeting') }}",
            confirmation_kick: "{{ __('Are you sure you want to kick this user') }}",
            participant_left: "{{ __('A participant has left the meeting') }}",
            camera_on: "{{ __('Camera has been turned on') }}",
            camera_off: "{{ __('Camera has been turned off') }}",
            mic_unmute: "{{ __('Mic has been unmute') }}",
            mic_mute: "{{ __('Mic has been muted') }}",
            no_video: "{{ __('The video is not playing or has no video track') }}",
            no_pip: "{{ __('Picture-in-picture mode is not supported in this browser') }}",
            link_copied: "{{ __('The meeting invitation link has been copied to the clipboard') }}",
            cant_share_screen: "{{ __('Could not share the screen, please check the permissions and try again') }}",
            max_file_size: "{{ __('Maximum file size allowed (MB)') }}",
            view_file: "{{ __('View File') }}",
            hand_raised: "{{ __('Hand raised') }}",
            hand_raised_self: "{{ __('You raised hand') }}",
            your_screen: "{{ __('Your screen') }}",
            not_started: "{{ __('The meeting has not been started yet') }}",
            meeting_full: "{{ __('The meeting is full') }}",
            please_wait: "{{ __('Please wait while the moderator check your request') }}",
            request_record_meeting: "{{ __('Request to record the meeting') }}",
            request_screenshare: "{{ __('Request to start screen sharing') }}",
            record_request_declined: "{{ __('Your recording request was not approved') }}",
            screenshare_request_declined: "{{ __('Your screen share request was not approved') }}",
            feature_not_supported: "{{ __('This feature is not yet supported in your browser') }}",
            feature_not_available: "{{ __('This feature is not available in the current meeting plan') }}",
            password: "{{ __('Password: ') }}",
            calendar_check: "{{ __('Please set a date and time') }}",
            recording_started: "{{ __('The recording has been started') }}",
            token_copied: "{{ __('API Token has been copied to the clipboard') }}",
            screen: "{{ __('Screen-') }}",
            checking_mic_cam_permission: "{{ __('Checking microphone and camera permission') }}",
            click_allow: "{{ __('Click \"Allow\"') }}",
            personal_link_copied: "{{ __('Your personal meeting link has been copied to the clipboard') }}",
            toggleMic: "{{ __('Mute/Unmute Mic') }}",
            toggleCamera: "{{ __('On/Off Camera') }}",
            you_muted: "{{ __('You muted all the participants') }}",
            you_unmuted: "{{ __('You unmuted all the participants') }}",
            mic_muted_moderator: "{{ __('Mic has been muted by the moderator') }}",
            mic_unmuted_moderator: "{{ __('Mic has been unmuted by the moderator') }}",
            camera_off_moderator: "{{ __('Camera has been turned off by the moderator') }}",
            camera_on_moderator: "{{ __('Camera has been turned on by the moderator') }}",
            moderator: "{{ __('Moderator') }}",
            moderator_updated: "{{ __('The moderator has been updated. New moderator: ') }}",
            make_moderator: "{{ __('Make Moderator') }}",
            you_moderator: "{{ __('You are now the moderator') }}",
            moderator_confirm: "{{ __('Are you sure you want to switch the moderator right? This action can not be undone') }}",
            meeting_ending: "{{ __('The meeting will end in one minute') }}",
            picture_in_picture: "{{ __('Picture in Picture') }}",
            back_to_tab: "{{ __('Back to Tab') }}",
            pip_not_supported: "{{ __('Picture-in-Picture is not supported in this browser') }}",
            pip_host_only: "{{ __('Only the host can use Picture-in-Picture mode') }}",
            pip_started: "{{ __('Picture-in-Picture mode started') }}",
            pip_stopped: "{{ __('Picture-in-Picture mode stopped') }}",
            screen_share_started: "{{ __('Screen sharing started successfully') }}",
            screen_share_stopped: "{{ __('Screen sharing stopped') }}",
            screen_share_request_sent: "{{ __('Screen share request sent to host. Please wait for approval') }}",
            back_to_tab: "{{ __('Back to Tab') }}",
            camera_turned_on: "{{ __('Camera turned on') }}",
            camera_turned_off: "{{ __('Camera turned off') }}",
            microphone_turned_on: "{{ __('Microphone turned on') }}",
            microphone_turned_off: "{{ __('Microphone turned off') }}",
        }
    </script>



    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/main.js?version=') . getVersion() }}"></script>

    @yield('script')
    
<!-- Meeting Details Modal -->
<div class="modal fade" id="meetingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="meetingDetailsLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="meetingDetailsLabel">{{ __('Meeting Details') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="meetingDetailsBody">
        <!-- Details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- All Meetings Modal -->
<div class="modal fade" id="allMeetingsModal" tabindex="-1" role="dialog" aria-labelledby="allMeetingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allMeetingsModalLabel">{{ __('All My Meetings') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row" id="allMeetingsList">
          @if(isset($allMeetings) && $allMeetings->count() > 0)
            @foreach($allMeetings as $meeting)
              <div class="col-md-6 mb-3">
                <div class="card meeting-card-modal h-100">
                  <div class="card-body">
                    <h6 class="card-title text-primary">{{ $meeting->title ?: 'Untitled Meeting' }}</h6>
                    <p class="card-text text-muted small">{{ $meeting->description ?: 'No description' }}</p>
                    <div class="meeting-info small mb-3">
                      <div><i class="fa fa-calendar mr-1"></i> {{ $meeting->date ? \Carbon\Carbon::parse($meeting->date)->format('M d, Y') : '-' }}</div>
                      <div><i class="fa fa-clock mr-1"></i> {{ $meeting->time ?: '-' }}</div>
                      <div><i class="fa fa-id-badge mr-1"></i> {{ $meeting->meeting_id ?: 'N/A' }}</div>
                    </div>
                    <div class="btn-group btn-group-sm w-100">
                      <button class="btn btn-outline-primary" onclick="redirectToMeeting('{{ $meeting->id }}')">
                        <i class="fa fa-edit mr-1"></i> Edit
                      </button>
                      <button class="btn btn-outline-success" onclick="redirectToMeeting('{{ $meeting->meeting_id }}')">
                        <i class="fa fa-play mr-1"></i> Start
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="col-12 text-center text-muted">
              <i class="fa fa-calendar-times fa-2x mb-3"></i>
              <p>{{ __('No meetings found') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

    {{-- PWA Service Worker + Install Prompt --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/serviceworker.js')
                    .then(function(reg) { console.log('PWA: Service Worker registered'); })
                    .catch(function(err) { console.log('PWA: Service Worker registration failed', err); });
            });
        }

        // Install button handler — works on click
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
            // Fallback: show instructions if no native prompt
            const ua = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(ua);
            const isAndroid = /Android/i.test(ua);
            if (isIOS) {
                alert('To install on iPhone/iPad:\n\n1. Tap the Share button at the bottom of Safari\n2. Scroll down and tap "Add to Home Screen"\n3. Tap Add');
            } else if (isAndroid) {
                alert('To install on Android:\n\n1. Tap the menu (⋮) at the top right of your browser\n2. Tap "Install app" or "Add to Home Screen"\n\nIf you do not see this option, try Chrome browser.');
            } else {
                alert('To install this app:\n\n1. Click the install icon in your browser address bar\n2. Or open browser menu and click "Install"');
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
