@extends('layouts.app')

@section('page', __('Reset Password'))
@section('title', getSetting('APPLICATION_NAME') . ' | ' . __('Reset Password'))

@section('content')
<style>
    .np-auth-wrap { min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: #f5f9fc; }
    .np-auth-card { display: flex; max-width: 900px; width: 100%; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 190, 242, 0.15); }
    .np-auth-left { flex: 1; background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%); display: flex; align-items: center; justify-content: center; padding: 60px 40px; color: #fff; position: relative; overflow: hidden; }
    .np-auth-left::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.3; }
    .np-auth-left-content { position: relative; z-index: 1; text-align: center; }
    .np-auth-icon { width: 100px; height: 100px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; backdrop-filter: blur(10px); }
    .np-auth-icon svg { width: 50px; height: 50px; stroke: #fff; }
    .np-auth-left h3 { font-size: 28px; font-weight: 700; margin-bottom: 12px; color: #fff; }
    .np-auth-left p { font-size: 15px; opacity: 0.95; line-height: 1.6; margin: 0 0 16px 0; }
    .np-auth-left a { color: #fff; font-weight: 700; text-decoration: underline; }
    .np-auth-right { flex: 1; padding: 60px 50px; display: flex; flex-direction: column; justify-content: center; }
    .np-auth-right h2 { font-size: 26px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
    .np-auth-right .np-subtitle { font-size: 14px; color: #888; margin-bottom: 30px; }
    .np-auth-alert { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; padding: 14px 18px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; border-left: 4px solid #28a745; }
    .np-auth-input { width: 100%; padding: 14px 18px; border: 2px solid #e5e5e5; border-radius: 10px; font-size: 15px; transition: all 0.2s; margin-bottom: 16px; }
    .np-auth-input:focus { outline: none; border-color: #00bef2; box-shadow: 0 0 0 3px rgba(0,190,242,0.1); }
    .np-auth-input.is-invalid { border-color: #dc3545; }
    .np-error { color: #dc3545; font-size: 13px; margin-top: -10px; margin-bottom: 16px; }
    .np-auth-btn { width: 100%; background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%); color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(0, 190, 242, 0.3); }
    .np-auth-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 190, 242, 0.4); }
    .np-back-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
    .np-back-link a { color: #00bef2; font-weight: 600; text-decoration: none; }
    .np-back-link a:hover { text-decoration: underline; }
    @media (max-width: 768px) { .np-auth-card { flex-direction: column; } .np-auth-left { padding: 40px 20px; } .np-auth-right { padding: 40px 30px; } }
</style>

<div class="np-auth-wrap">
    <div class="np-auth-card">
        <div class="np-auth-left">
            <div class="np-auth-left-content">
                <div class="np-auth-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h3>{{ __('Forgot your password?') }}</h3>
                <p>{{ __('No worries, we will send you reset instructions') }}</p>
                <p>{{ __('Already have the password?') }} <a href="{{ route('login') }}">{{ __('Login') }}</a></p>
            </div>
        </div>

        <div class="np-auth-right">
            <h2>{{ __('Reset Password') }}</h2>
            <p class="np-subtitle">{{ __('Enter your email to receive reset link') }}</p>

            @if (session('status'))
                <div class="np-auth-alert">✓ {{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="np-auth-input @error('email') is-invalid @enderror"
                    placeholder="{{ __('E-Mail Address') }}" maxlength="50" required autocomplete="email" autofocus>
                @error('email')<div class="np-error">{{ $message }}</div>@enderror

                <button type="submit" class="np-auth-btn">{{ __('Send Password Reset Link') }}</button>
            </form>

            <div class="np-back-link">
                <a href="{{ url('/') }}">← {{ __('Back to Home') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
