@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('style')
<style>
    :root {
        --np-black: #00bef2;
        --np-black-hover: #0099c8;
        --np-black-dark: #0099c8;
        --np-text: #18181b;
        --np-muted: #71717a;
        --np-soft: #a1a1aa;
        --np-border: #e4e4e7;
        --np-bg-soft: #fafafa;
        --np-blue: #00bef2;
        --np-blue-soft: #e3f7ff;
        --np-blue-text: #006d8c;
        --np-success: #22c55e;
    }

    body { background: linear-gradient(135deg, #f5f7fa 0%, #e3f7ff 100%); }

    .np-auth { min-height: 100vh; background: transparent; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, sans-serif; color: var(--np-text); }
    .np-grid { display: grid; grid-template-columns: 1.15fr 1fr; min-height: 100vh; max-width: 1280px; margin: 0 auto; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 190, 242, 0.1); overflow: hidden; }

    /* LEFT — showcase */
    .np-left { background: #ffffff; padding: 36px 48px; display: flex; flex-direction: column; justify-content: space-between; border-right: 1px solid #f4f4f5; }
    .np-brand { display: flex; align-items: center; gap: 10px; }
    .np-logo-mark { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 14px; }
    .np-brand-name { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; color: #0099c8; }

    .np-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--np-blue-soft); border-radius: 20px; margin-bottom: 20px; }
    .np-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--np-blue); }
    .np-pill-text { font-size: 11px; color: var(--np-blue-text); font-weight: 600; letter-spacing: 0.02em; }

    .np-headline { font-size: 36px; font-weight: 600; letter-spacing: -0.025em; color: var(--np-black); margin: 0 0 14px; line-height: 1.12; }
    .np-sub { font-size: 14px; color: var(--np-muted); margin: 0 0 28px; line-height: 1.6; max-width: 400px; }

    .np-meeting-card { background: #f8fdff; border: 1px solid #b3ecfa; border-radius: 12px; padding: 14px; display: flex; align-items: center; gap: 12px; }
    .np-avatars { display: flex; }
    .np-avatar { width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; }
    .np-avatar + .np-avatar { margin-left: -8px; }
    .np-meeting-title { font-size: 13px; color: var(--np-black); font-weight: 600; margin-bottom: 2px; }
    .np-meeting-meta { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--np-muted); }
    .np-meeting-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--np-success); }

    .np-stats { display: flex; gap: 8px; margin-top: 14px; }
    .np-stat { flex: 1; background: #f8fdff; border: 1px solid #b3ecfa; border-radius: 10px; padding: 10px 12px; }
    .np-stat-label { font-size: 11px; color: var(--np-muted); margin-bottom: 3px; }
    .np-stat-value { font-size: 13px; color: var(--np-black); font-weight: 600; }

    .np-footnote { font-size: 11px; color: var(--np-soft); letter-spacing: 0.04em; }

    /* RIGHT — form */
    .np-right { background: #ffffff; padding: 48px 56px; display: flex; flex-direction: column; justify-content: center; }
    .np-top-link { display: flex; justify-content: flex-end; margin-bottom: 32px; font-size: 13px; color: var(--np-muted); }
    .np-top-link a { color: #00bef2; text-decoration: none; font-weight: 600; border-bottom: 1px solid #b3ecfa; padding-bottom: 1px; margin-left: 4px; }

    .np-form-wrap { max-width: 380px; width: 100%; margin: 0 auto; }
    .np-h2 { font-size: 26px; font-weight: 700; letter-spacing: -0.02em; color: #1a3a47; margin: 0 0 6px; }
    .np-h2-sub { font-size: 13px; color: var(--np-muted); margin: 0 0 24px; }

    .np-social { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 18px; }
    .np-social.full { grid-template-columns: 1fr; }
    .np-social-btn { height: 42px; border: 1px solid var(--np-border); background: #fff; border-radius: 10px; font-size: 13px; color: var(--np-black); display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; font-weight: 500; transition: all 0.15s; text-decoration: none; }
    .np-social-btn:hover { background: var(--np-bg-soft); border-color: #d4d4d8; color: var(--np-black); text-decoration: none; }
    .np-social-btn svg { width: 16px; height: 16px; }

    .np-divider { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .np-divider-line { flex: 1; height: 1px; background: var(--np-border); }
    .np-divider-text { font-size: 11px; color: var(--np-soft); letter-spacing: 0.04em; font-weight: 500; }

    .np-field { margin-bottom: 14px; }
    .np-label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .np-label { font-size: 12px; font-weight: 600; color: #3f3f46; display: block; margin-bottom: 6px; }
    .np-forgot { font-size: 12px; color: #00bef2; text-decoration: none; font-weight: 500; }
    .np-forgot:hover { text-decoration: underline; color: #0099c8; }
    .np-input { width: 100%; border-radius: 10px !important; height: 44px; padding: 0 14px; border: 1px solid var(--np-border); border-radius: 7px; font-size: 14px; color: var(--np-black); background: #fff; box-sizing: border-box; outline: none; transition: all 0.15s; }
    .np-input:focus { border-color: #00bef2; box-shadow: 0 0 0 3px rgba(0, 190, 242, 0.15); }
    .np-input.is-invalid { border-color: #ef4444; }

    .np-check { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; }
    .np-check input { width: 14px; height: 14px; accent-color: #00bef2; margin: 0; }
    .np-check label { font-size: 12px; color: #52525b; cursor: pointer; }

    .np-submit { width: 100%; height: 46px; background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; letter-spacing: -0.01em; display: flex; align-items: center; justify-content: center; gap: 7px; transition: all 0.2s; box-shadow: 0 4px 14px rgba(0, 190, 242, 0.3); }
    .np-submit:hover { background: linear-gradient(135deg, #0099c8 0%, #007a9e 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    .np-legal { font-size: 11px; color: var(--np-soft); text-align: center; margin: 22px 0 0; line-height: 1.6; }
    .np-legal a { color: #52525b; text-decoration: underline; }

    .np-alert { padding: 12px 14px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 7px; color: #92400e; font-size: 13px; margin-bottom: 16px; }
    .invalid-feedback { display: block; color: #ef4444; font-size: 12px; margin-top: 5px; }

    #autoLogin { margin-top: 14px; height: 40px; padding: 0 12px; border: 1px solid var(--np-border); border-radius: 7px; font-size: 13px; width: 100%; }

    /* Responsive */
    @media (max-width: 991px) {
        .np-grid { grid-template-columns: 1fr; }
        .np-left { display: none; }
        .np-right { padding: 32px 24px; }
    }
    @media (max-width: 480px) {
        .np-right { padding: 24px 18px; }
        .np-form-wrap { max-width: 100%; }
    }
</style>
@endsection

@section('content')
<section class="np-auth">
    <div class="np-grid">

        {{-- LEFT: Showcase --}}
        <div class="np-left">
            <div class="np-brand">
                <div class="np-logo-mark">{{ strtoupper(substr(getSetting('APPLICATION_NAME'), 0, 1)) }}</div>
                <span class="np-brand-name">{{ getSetting('APPLICATION_NAME') }}</span>
            </div>

            <div>
                <div class="np-pill">
                    <div class="np-pill-dot"></div>
                    <span class="np-pill-text">{{ __('Secure HD video meetings') }}</span>
                </div>
                <h1 class="np-headline">{{ __('Meetings that') }}<br>{{ __('actually work.') }}</h1>
                <p class="np-sub">{{ __('HD video, screen sharing, and recording — all in your browser, no downloads required.') }}</p>

                <div class="np-meeting-card">
                    <div class="np-avatars">
                        <div class="np-avatar" style="background:#fbbf24;color:#78350f">SK</div>
                        <div class="np-avatar" style="background:#a78bfa;color:#4c1d95">MR</div>
                        <div class="np-avatar" style="background:#34d399;color:#064e3b">AH</div>
                        <div class="np-avatar" style="background:#f4f4f5;color:#52525b">+5</div>
                    </div>
                    <div>
                        <div class="np-meeting-title">{{ __('Weekly product sync') }}</div>
                        <div class="np-meeting-meta">
                            <div class="np-meeting-dot"></div>
                            <span>{{ __('8 active · started 4m ago') }}</span>
                        </div>
                    </div>
                </div>

                <div class="np-stats">
                    <div class="np-stat">
                        <div class="np-stat-label">{{ __('HD video') }}</div>
                        <div class="np-stat-value">1080p · 60fps</div>
                    </div>
                    <div class="np-stat">
                        <div class="np-stat-label">{{ __('Encrypted') }}</div>
                        <div class="np-stat-value">{{ __('End-to-end') }}</div>
                    </div>
                    <div class="np-stat">
                        <div class="np-stat-label">{{ __('Latency') }}</div>
                        <div class="np-stat-value">&lt;50ms</div>
                    </div>
                </div>
            </div>

            <div class="np-footnote">© {{ date('Y') }} {{ getSetting('APPLICATION_NAME') }}. {{ __('All rights reserved.') }}</div>
        </div>

        {{-- RIGHT: Form --}}
        <div class="np-right">
            @if (Route::has('register'))
                <div class="np-top-link">
                    {{ __('New here?') }}
                    <a href="{{ route('register') }}">{{ __('Create account') }}</a>
                </div>
            @endif

            <div class="np-form-wrap">
                <h2 class="np-h2">{{ __('Sign in') }}</h2>
                <p class="np-h2-sub">{{ __('Use your work email to get started') }}</p>

                @if (session('verify'))
                    <div class="np-alert">{{ session('verify') }}</div>
                @endif

                <form id="login" method="POST" action="{{ route('login') }}">
                    @csrf

                    @php
                        $hasGoogle = getSetting('GOOGLE_SOCIAL_LOGIN') == 'enabled';
                        $hasFb = getSetting('FACEBOOK_SOCIAL_LOGIN') == 'enabled';
                        $hasLi = getSetting('LINKEDIN_SOCIAL_LOGIN') == 'enabled';
                        $hasTw = getSetting('TWITTER_SOCIAL_LOGIN') == 'enabled';
                        $socialCount = ($hasGoogle?1:0) + ($hasFb?1:0) + ($hasLi?1:0) + ($hasTw?1:0);
                    @endphp

                    @if ($socialCount > 0)
                        <div class="np-social {{ $socialCount === 1 ? 'full' : '' }}">
                            @if ($hasGoogle)
                                <a href="{{ route('login.google') }}" class="np-social-btn">
                                    <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    Google
                                </a>
                            @endif
                            @if ($hasFb)
                                <a href="{{ route('login.facebook') }}" class="np-social-btn">
                                    <svg viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook
                                </a>
                            @endif
                            @if ($hasLi)
                                <a href="{{ route('login.linkedin') }}" class="np-social-btn">
                                    <svg viewBox="0 0 24 24" fill="#0077B5"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                    LinkedIn
                                </a>
                            @endif
                            @if ($hasTw)
                                <a href="{{ route('login.twitter') }}" class="np-social-btn">
                                    <svg viewBox="0 0 24 24" fill="#000000"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    X
                                </a>
                            @endif
                        </div>

                        <div class="np-divider">
                            <div class="np-divider-line"></div>
                            <span class="np-divider-text">{{ __('OR WITH EMAIL') }}</span>
                            <div class="np-divider-line"></div>
                        </div>
                    @endif

                    <div class="np-field">
                        <label class="np-label" for="email">{{ __('Email or username') }}</label>
                        <input id="email" type="text"
                            class="np-input @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}"
                            placeholder="you@company.com" maxlength="50"
                            required autocomplete="email" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="np-field">
                        <div class="np-label-row">
                            <label class="np-label" for="password" style="margin-bottom:0">{{ __('Password') }}</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="np-forgot">{{ __('Forgot?') }}</a>
                            @endif
                        </div>
                        <input id="password" type="password"
                            class="np-input @error('password') is-invalid @enderror"
                            placeholder="••••••••••" name="password" maxlength="50"
                            required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (getSetting('CAPTCHA_LOGIN_PAGE') == 'enabled')
                        <div class="np-field">
                            <div class="g-recaptcha" id="recaptcha-div" data-sitekey="{{ getSetting('GOOGLE_RECAPTCHA_KEY') }}"></div>
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="invalid-feedback">{{ $errors->first('g-recaptcha-response') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="np-check">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">{{ __('Keep me signed in for 30 days') }}</label>
                    </div>

                    <button id="loginButton" type="submit" class="np-submit">
                        {{ __('Continue') }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </button>
                </form>

                @if (isDemoMode())
                    <select id="autoLogin">
                        <option value="">Auto login as (demo only)</option>
                        <option value="admin">Admin</option>
                        <option value="user_1">User 1</option>
                        <option value="user_2">User 2</option>
                    </select>
                @endif

                <p class="np-legal">
                    {{ __('By continuing you agree to the') }}
                    <a href="#">{{ __('Terms') }}</a> {{ __('and') }}
                    <a href="#">{{ __('Privacy policy') }}</a>
                </p>
            </div>
        </div>

    </div>
</section>
@endsection

@section('script')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>document.body.classList.add('np-auth-page');</script>
@endsection
