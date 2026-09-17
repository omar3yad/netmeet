@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | Your Recordings')

@section('style')
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: 'Inter', sans-serif;
    }
    .recordings-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        padding: 24px;
    }
    .recording-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .recording-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .recording-video {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #000;
    }
    .recording-info {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .recording-info h5 {
        margin: 0 0 8px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #2d3748;
    }
    .recording-info p {
        font-size: 0.875rem;
        color: #718096;
        margin: 0 0 12px 0;
    }
    .btn-recording {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 500;
        transition: 0.2s;
    }
    .btn-recording:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102,126,234,0.3);
        color: white;
    }
    .empty-state {
        text-align: center;
        padding: 60px 24px;
        color: #718096;
    }
</style>
@endsection

@section('content')
<div class="recordings-container" id="recordingsContainer">
    <!-- Videos will be injected here -->
</div>
<div class="empty-state" id="emptyState" style="display:none;">
    <h6>No recordings found ??</h6>
    <p>You haven't recorded any videos yet.</p>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const username = localStorage.getItem('pendingUsername') || 'Guest';
    const container = document.getElementById('recordingsContainer');
    const emptyState = document.getElementById('emptyState');

    fetch(`/user-recordings?username=${encodeURIComponent(username)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok' && data.data.length > 0) {
                emptyState.style.display = 'none';
                data.data.forEach(recording => {
                    const card = document.createElement('div');
                    card.className = 'recording-card';
                    card.innerHTML = `
                        <video class="recording-video" controls>
                            <source src="/${recording.file_path}" type="video/webm">
                            Your browser does not support the video tag.
                        </video>
                        <div class="recording-info">
                            <h5>${recording.file_name}</h5>
                            <p>Recorded at: ${new Date(recording.recorded_at).toLocaleString()}</p>
                            <a href="/${recording.file_path}" target="_blank" class="btn-recording">Open / Download</a>
                        </div>
                    `;
                    container.appendChild(card);
                });
            } else {
                emptyState.style.display = 'block';
            }
        })
        .catch(err => {
            console.error('Failed to load recordings', err);
            emptyState.style.display = 'block';
        });
});
</script>
@endsection
