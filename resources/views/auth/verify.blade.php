@extends('layouts.app')

@section('content')
<style>
    .np-verify-wrap {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: #f5f9fc;
    }
    .np-verify-card {
        display: flex;
        max-width: 900px;
        width: 100%;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 190, 242, 0.15);
    }
    .np-verify-left {
        flex: 1;
        background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 40px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .np-verify-left::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        opacity: 0.3;
    }
    .np-verify-left-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    .np-verify-icon {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        backdrop-filter: blur(10px);
    }
    .np-verify-icon svg {
        width: 50px;
        height: 50px;
        stroke: #fff;
    }
    .np-verify-left h3 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #fff;
    }
    .np-verify-left p {
        font-size: 15px;
        opacity: 0.95;
        line-height: 1.6;
        margin: 0;
    }
    .np-verify-right {
        flex: 1;
        padding: 60px 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .np-verify-right h2 {
        font-size: 26px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 8px;
    }
    .np-verify-right .np-subtitle {
        font-size: 14px;
        color: #888;
        margin-bottom: 30px;
    }
    .np-verify-alert {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }
    .np-verify-message {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        font-size: 14px;
        color: #555;
        line-height: 1.7;
        margin-bottom: 24px;
    }
    .np-verify-message strong {
        color: #1a1a2e;
    }
    .np-resend-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .np-resend-btn {
        background: linear-gradient(135deg, #00bef2 0%, #0099c8 100%);
        color: #fff;
        border: none;
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(0, 190, 242, 0.3);
    }
    .np-resend-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 190, 242, 0.4);
        color: #fff;
    }
    .np-back-link {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }
    .np-back-link a {
        color: #00bef2;
        font-weight: 600;
        text-decoration: none;
    }
    .np-back-link a:hover {
        text-decoration: underline;
    }
    @media (max-width: 768px) {
        .np-verify-card {
            flex-direction: column;
        }
        .np-verify-left {
            padding: 40px 20px;
        }
        .np-verify-right {
            padding: 40px 30px;
        }
    }
</style>

<div class="np-verify-wrap">
    <div class="np-verify-card">
        <div class="np-verify-left">
            <div class="np-verify-left-content">
                <div class="np-verify-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <h3>{{ __('You are just a click away!') }}</h3>
                <p>{{ __('Check your inbox to activate your account and start using NetMeet') }}</p>
            </div>
        </div>

        <div class="np-verify-right">
            <h2>{{ __('Verify Your Email Address') }}</h2>
            <p class="np-subtitle">{{ __('Almost there! One last step.') }}</p>

            @if (session('resent'))
                <div class="np-verify-alert">
                    ✓ {{ __('A fresh verification link has been sent to your email address') }}
                </div>
            @endif

            <div class="np-verify-message">
                {{ __('Before proceeding, please check your email for a verification link.') }}
                <br><br>
                <strong>{{ __('Did not receive the email?') }}</strong>
                {{ __('Click the button below to request a new one.') }}
            </div>

            <form method="POST" action="{{ route('verification.resend') }}" class="np-resend-form">
                @csrf
                <button type="submit" class="np-resend-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    {{ __('Resend Verification Email') }}
                </button>
            </form>

            <div class="np-back-link">
                <a href="{{ url('/') }}">← {{ __('Back to Home') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
