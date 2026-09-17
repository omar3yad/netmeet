@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('style')
    <style>
        .flex-fill {
            padding-bottom: 1.5rem;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
        }
    </style>
@endsection

@section('content')
    <div class="bg-base-1">
        @include('include/pricing')
    </div>
@endsection
