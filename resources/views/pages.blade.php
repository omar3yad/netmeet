@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page->title)

@section('style')
    <style>
        .card-body {
            min-height: 68vh;
        }
        .card-body a {
            color:rgb(79, 7, 89) !important;
        }

    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-10 mt-3 offset-md-1 text-justify">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="mb-0 text-bold">{{ $page->title }}</h4>
                    </div>
                    <div class="card-body">
                        {!! $page->content !!}
                        <div class="text-center mt-4 mb-2">
                            <a href="/" class="btn" style="background:linear-gradient(135deg,#00bef2 0%,#0099c8 100%);color:#fff;padding:12px 32px;border-radius:8px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(0,190,242,0.3)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                {{ __('Back to Home') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

