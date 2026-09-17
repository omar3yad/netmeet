@extends('layouts.app')

@section('title', 'My Recordings')

@section('content')

<style>
    .recordings-title {
        color: #fff;
        font-size: 26px;
        margin-bottom: 25px;
        font-weight: bold;
    }

    .recordings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .recording-card {
        background: #1e1e2f;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 0 12px rgba(0,0,0,0.25);
        transition: 0.3s;
        border: 1px solid #333;
    }

    .recording-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0 18px rgba(0,0,0,0.35);
    }

    .recording-card video {
        width: 100%;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .recording-info {
        color: #ddd;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .copy-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        font-weight: bold;
        transition: 0.2s;
    }

    .copy-btn:hover {
        background: #43a047;
    }

    .copy-success {
        margin-top: 6px;
        font-size: 13px;
        color: #4CAF50;
        display: none;
    }
    .delete-btn {
        background: #dc2626;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        font-weight: bold;
        transition: 0.2s;
        margin-top: 8px;
    }
    .delete-btn:hover {
        background: #b91c1c;
    }
</style>

<h2 class="recordings-title">Recordings for {{ $username }}</h2>

@if($recordings->isEmpty())
    <p style="color:#bbb; font-size:16px;">No recordings found.</p>
@else
    <div class="recordings-grid">
        @foreach($recordings as $rec)
            <div class="recording-card">

                <video controls src="{{ asset($rec->file_path) }}"></video>

                <div class="recording-info">
                    <strong>Name:</strong> {{ $rec->file_name }} <br>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($rec->recorded_at)->format('Y-m-d H:i') }}
                </div>

                <button class="copy-btn" onclick="copyLink('{{ asset($rec->file_path) }}', '{{ $rec->id }}')">
                    Copy Video Link
                </button>

                <div id="copy-status-{{ $rec->id }}" class="copy-success">
                    Link copied!
                </div>

            </div>
        @endforeach
    </div>
@endif

<script>
    function copyLink(url, id) {
        navigator.clipboard.writeText(url).then(() => {
            let msg = document.getElementById("copy-status-" + id);
            msg.style.display = "block";

            setTimeout(() => {
                msg.style.display = "none";
            }, 2000);
        });
    }
</script>

@endsection
