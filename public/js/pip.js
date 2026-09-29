/**
 * pip.js
 * -----------------------------------------------------------------------
 * Loads after screen-share.js. Owns Picture-in-Picture (PiP) and Auto-PiP.
 * Handles:
 *  - Automatic Picture-in-Picture when page/tab is backgrounded (visibilitychange & native autoPiP)
 *  - Exit PiP when returning to foreground (if auto-entered)
 *  - Debounced manual PiP toggle via #pictureInPicture button or video click
 *  - Dynamic Canvas Video inside #selfContainer for camera-off / audio-only meetings
 *    (enables native Chrome Android Auto-PiP on Home button even with camera off)
 *  - MediaSession background audio carrier (Dynamic Island / lock screen notification)
 *    to prevent audio/mic cutoff on mobile
 *  - Syncing button state and PiP indicators
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    let isAutoPip = false;
    let wakeLock = null;
    let isInitialized = false;
    let isToggling = false;

    let canvasStreamVideo = null;
    let canvasAnimId = null;
    let backgroundCarrier = null;

    Meeting.pip = {
        initializePictureInPicture: initializePictureInPicture,
        togglePictureInPicture: togglePictureInPicture,
        enterPip: enterPip,
        exitPip: exitPip,
        findBestVideo: findBestVideo
    };

    // Expose global for compatibility
    window.toggleMeetingPiP = togglePictureInPicture;

    function initializePictureInPicture() {
        if (isInitialized) return;
        isInitialized = true;

        setupAutoPip();
        setupWakeLock();
        setupMediaSessionAndBackgroundCarrier();
        bindEvents();
        ensurePlaybackOnInteraction();
    }

    // Pre-initialize canvas fallback on interaction so it's ready instantly
    function ensurePlaybackOnInteraction() {
        function kickstart() {
            getCanvasVideo();
            const fallback = document.getElementById('fallbackVideo');
            if (fallback && fallback.paused) {
                fallback.play().catch(() => {});
            }
            if (backgroundCarrier && backgroundCarrier.paused) {
                backgroundCarrier.play().catch(() => {});
            }
        }
        document.addEventListener('click', kickstart, { passive: true, once: true });
        document.addEventListener('touchstart', kickstart, { passive: true, once: true });
    }

    // Request Screen Wake Lock so screen doesn't turn off unexpectedly during meetings
    async function setupWakeLock() {
        if ('wakeLock' in navigator) {
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                wakeLock.addEventListener('release', () => {
                    wakeLock = null;
                });
            } catch (err) {
                console.log('[PiP] WakeLock request error:', err);
            }
        }
    }

    let keepAliveAudioCtx = null;
    let keepAliveOsc = null;

    function startWebAudioKeepAlive() {
        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            if (!keepAliveAudioCtx) {
                keepAliveAudioCtx = new AudioContextClass();
            }
            if (keepAliveAudioCtx.state === 'suspended') {
                keepAliveAudioCtx.resume().catch(() => {});
            }
            if (!keepAliveOsc) {
                keepAliveOsc = keepAliveAudioCtx.createOscillator();
                const gain = keepAliveAudioCtx.createGain();
                keepAliveOsc.frequency.value = 440;
                gain.gain.value = 0.00001; // Inaudible
                keepAliveOsc.connect(gain);
                gain.connect(keepAliveAudioCtx.destination);
                keepAliveOsc.start();
            }
        } catch (e) {
            console.log('[WebAudio KeepAlive] Error:', e);
        }
    }

    // Dynamic Island / MediaSession background audio keeper
    function setupMediaSessionAndBackgroundCarrier() {
        if (!backgroundCarrier) {
            // Inaudible silent wav audio loop
            const silentWav = 'data:audio/wav;base64,UklGRjIAAABXQVZFZm10IBIAAAABAAEAQB8AAEAfAAABAAgAAABmYWN0BAAAAAAAAABkYXRhAAAAAA==';
            backgroundCarrier = new Audio(silentWav);
            backgroundCarrier.loop = true;
            backgroundCarrier.volume = 0.01;
        }

        const appName = (typeof features !== 'undefined' && features.appName) ? features.appName : 'NetMeet';
        const meetTitle = (typeof userInfo !== 'undefined' && userInfo.meetingTitle) ? userInfo.meetingTitle : 'Meeting in progress';

        if ('mediaSession' in navigator) {
            try {
                navigator.mediaSession.metadata = new MediaMetadata({
                    title: meetTitle,
                    artist: appName,
                    album: 'Online Meeting',
                    artwork: [
                        { src: '/storage/images/SECONDARY_LOGO.png', sizes: '96x96', type: 'image/png' },
                        { src: '/storage/images/SECONDARY_LOGO.png', sizes: '192x192', type: 'image/png' },
                        { src: '/storage/images/SECONDARY_LOGO.png', sizes: '512x512', type: 'image/png' }
                    ]
                });
                navigator.mediaSession.playbackState = 'playing';

                navigator.mediaSession.setActionHandler('play', () => {
                    startWebAudioKeepAlive();
                    if (backgroundCarrier) backgroundCarrier.play().catch(() => {});
                    navigator.mediaSession.playbackState = 'playing';
                });

                navigator.mediaSession.setActionHandler('pause', () => {
                    // Keep meeting audio alive
                    startWebAudioKeepAlive();
                    if (backgroundCarrier) backgroundCarrier.play().catch(() => {});
                    navigator.mediaSession.playbackState = 'playing';
                });

                navigator.mediaSession.setActionHandler('enterpictureinpicture', async () => {
                    await enterPip(true);
                });
            } catch (e) {
                console.log('[PiP] MediaSession setup error:', e);
            }
        }

        const kickstartAudio = () => {
            startWebAudioKeepAlive();
            if (backgroundCarrier && backgroundCarrier.paused) {
                backgroundCarrier.play().then(() => {
                    if ('mediaSession' in navigator) {
                        navigator.mediaSession.playbackState = 'playing';
                    }
                }).catch(() => {});
            }
        };

        document.addEventListener('click', kickstartAudio, { passive: true });
        document.addEventListener('touchstart', kickstartAudio, { passive: true });
        kickstartAudio();
    }

    // Check if an element has a real, active VIDEO track
    function hasVideoTrack(v) {
        if (!v) return false;
        if (v.id === 'previewVideo') return false;

        // MediaStream video check
        if (v.srcObject && typeof v.srcObject.getVideoTracks === 'function') {
            const tracks = v.srcObject.getVideoTracks();
            if (tracks.length === 0) return false;
            return tracks.some(t => t.enabled && t.readyState !== 'ended');
        }

        // Static video source check
        if (v.src && v.readyState >= 1) {
            return true;
        }

        return false;
    }

    // Create or retrieve canvas video element for camera-off / audio-only PiP
    function getCanvasVideo() {
        if (!canvasStreamVideo) {
            canvasStreamVideo = document.getElementById('pipCanvasVideo');
            if (!canvasStreamVideo) {
                canvasStreamVideo = document.createElement('video');
                canvasStreamVideo.id = 'pipCanvasVideo';
                canvasStreamVideo.autoplay = true;
                canvasStreamVideo.muted = true;
                canvasStreamVideo.playsInline = true;
                canvasStreamVideo.setAttribute('playsinline', '');
                canvasStreamVideo.setAttribute('webkit-playsinline', '');
                canvasStreamVideo.setAttribute('autopictureinpicture', '');
                canvasStreamVideo.autoPictureInPicture = true;

                // Mount inside #selfContainer so it is visible in the viewport when camera is off
                const selfContainer = document.getElementById('selfContainer');
                if (selfContainer) {
                    canvasStreamVideo.style.cssText = 'width:100%;height:100%;object-fit:contain;position:absolute;top:0;left:0;z-index:2;pointer-events:none;';
                    selfContainer.appendChild(canvasStreamVideo);
                } else {
                    canvasStreamVideo.style.cssText = 'position:fixed;width:240px;height:180px;bottom:10px;right:10px;z-index:9999;pointer-events:none;';
                    document.body.appendChild(canvasStreamVideo);
                }
            }
        }

        let canvas = document.getElementById('pipDynamicCanvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'pipDynamicCanvas';
            canvas.width = 400;
            canvas.height = 300;
            canvas.style.display = 'none';
            document.body.appendChild(canvas);
        }

        const ctx = canvas.getContext('2d');
        drawCanvasFrame(canvas, ctx);

        if (!canvasStreamVideo.srcObject && typeof canvas.captureStream === 'function') {
            try {
                canvasStreamVideo.srcObject = canvas.captureStream(10);
            } catch (e) {
                console.warn('[PiP] captureStream error:', e);
            }
        }

        if (!canvasAnimId) {
            canvasAnimId = setInterval(() => {
                if (state.isPipActive || document.pictureInPictureElement) {
                    drawCanvasFrame(canvas, ctx);
                }
            }, 1000);
        }

        return canvasStreamVideo;
    }

    function drawCanvasFrame(canvas, ctx) {
        // Dark modern gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, '#0f172a');
        gradient.addColorStop(1, '#1e293b');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Center circle avatar
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2 - 25;
        const radius = 45;

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fillStyle = '#3b82f6';
        ctx.fill();

        // Initial letter
        const name = (typeof userInfo !== 'undefined' && userInfo.username) ? userInfo.username : 'User';
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 36px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(name.charAt(0).toUpperCase(), centerX, centerY);

        // Name
        ctx.font = 'bold 18px Arial, sans-serif';
        ctx.fillText(name, centerX, centerY + radius + 25);

        // Mic Status Indicator
        ctx.font = '14px Arial, sans-serif';
        const isMuted = state.audioMuted;
        ctx.fillStyle = isMuted ? '#f87171' : '#4ade80';
        ctx.fillText(isMuted ? 'Mic Muted' : 'Mic Active', centerX, centerY + radius + 52);
    }

    function findBestVideo() {
        // 1. Screen share video (highest priority)
        const screenShareVideo = document.querySelector('.screen-share-container video, .videoContainer.OT_big video');
        if (screenShareVideo && hasVideoTrack(screenShareVideo)) {
            return screenShareVideo;
        }

        // 2. Active remote participant video with video tracks
        const remoteVideos = document.querySelectorAll('#videos .videoContainer:not(#selfContainer) video');
        for (const v of remoteVideos) {
            if (v.id !== 'fallbackVideo' && v.id !== 'pipCanvasVideo' && hasVideoTrack(v)) {
                return v;
            }
        }

        // 3. Local camera video with video tracks (when camera is on)
        const localVideo = document.getElementById('localVideo');
        if (localVideo && hasVideoTrack(localVideo)) {
            return localVideo;
        }

        // 4. Fallback video element (if valid and playing)
        const fallback = document.getElementById('fallbackVideo');
        if (fallback && hasVideoTrack(fallback)) {
            return fallback;
        }

        // 5. Canvas Video: GUARANTEED to have a video track (used when camera is off or audio-only)
        return getCanvasVideo();
    }

    function updateAutoPipAttribute() {
        const target = findBestVideo();
        const allVideos = document.querySelectorAll('video');
        allVideos.forEach(v => {
            if (v === target) {
                try {
                    v.autoPictureInPicture = true;
                    v.setAttribute('autopictureinpicture', '');
                    v.disablePictureInPicture = false;
                } catch (e) {}
            } else {
                try {
                    v.autoPictureInPicture = false;
                    v.removeAttribute('autopictureinpicture');
                } catch (e) {}
            }
        });
    }

    function setupAutoPip() {
        // Regularly refresh the autoPictureInPicture flag on the best video (for Chromium automatic PiP)
        setInterval(updateAutoPipAttribute, 1500);

        // Visibilitychange event listener: triggered as soon as user goes to Home or background
        document.addEventListener('visibilitychange', onVisibilityChange);
    }

    async function onVisibilityChange() {
        if (document.hidden || document.visibilityState === 'hidden') {
            await onBackground();
        } else {
            await onForeground();
        }
    }

    async function onBackground() {
        if (document.pictureInPictureElement || state.isPipActive) {
            return;
        }

        // Ensure background audio is playing so sound/mic never cut out
        if (backgroundCarrier && backgroundCarrier.paused) {
            backgroundCarrier.play().catch(() => {});
        }

        // Try to enter PiP automatically when user leaves / goes home
        await enterPip(true);
    }

    async function onForeground() {
        // Re-acquire wakeLock if needed
        setupWakeLock();

        // Exit PiP if it was opened automatically when leaving
        if (isAutoPip && document.pictureInPictureElement) {
            try {
                await document.exitPictureInPicture();
            } catch (e) {
                console.log('[PiP] exitPictureInPicture on foreground error:', e);
            }
            isAutoPip = false;
            state.isPipActive = false;
        }
    }

    async function enterPip(automatic = false) {
        if (document.pictureInPictureElement || state.isPipActive) return;

        const video = findBestVideo();
        if (!video) {
            if (!automatic && typeof showError === 'function') {
                showError(languages.no_video || 'No active video found for Picture-in-Picture');
            }
            return;
        }

        try {
            // Make sure video is playing
            if (video.paused) {
                await video.play().catch(() => {});
            }

            // Standard Web PiP on video element (works on Android Chrome, Desktop Chrome, Edge)
            if (typeof video.requestPictureInPicture === 'function') {
                await video.requestPictureInPicture();
                state.isPipActive = true;
                isAutoPip = automatic;
                return;
            }

            // iOS WebKit fallback: webkitSetPresentationMode
            if (typeof video.webkitSetPresentationMode === 'function') {
                video.webkitSetPresentationMode('picture-in-picture');
                state.isPipActive = true;
                isAutoPip = automatic;
                return;
            }
        } catch (err) {
            console.warn('[PiP] Primary video request failed:', err);

            // If primary video failed, try canvas video as guaranteed fallback
            if (video.id !== 'pipCanvasVideo') {
                try {
                    const canvasVid = getCanvasVideo();
                    if (canvasVid.paused) {
                        await canvasVid.play().catch(() => {});
                    }
                    if (typeof canvasVid.requestPictureInPicture === 'function') {
                        await canvasVid.requestPictureInPicture();
                        state.isPipActive = true;
                        isAutoPip = automatic;
                        return;
                    }
                } catch (fallbackErr) {
                    console.warn('[PiP] Fallback canvas PiP also failed:', fallbackErr);
                }
            }

            // Log to console only - avoid intrusive popup for user
            console.warn('[PiP] Picture-in-Picture request failed:', err);
        }
    }

    async function exitPip() {
        if (document.pictureInPictureElement) {
            try {
                await document.exitPictureInPicture();
            } catch (e) {
                console.log('[PiP] Exit PiP error:', e);
            }
        }
        state.isPipActive = false;
        isAutoPip = false;
    }

    async function togglePictureInPicture() {
        // Prevent rapid double-clicks
        if (isToggling) return;
        isToggling = true;

        try {
            // 1. If currently in PiP, exit
            if (document.pictureInPictureElement || state.isPipActive) {
                await exitPip();
                return;
            }

            // 2. iOS Safari handling
            const video = findBestVideo();
            if (video && typeof video.webkitSetPresentationMode === 'function' && typeof video.requestPictureInPicture !== 'function') {
                const currentMode = video.webkitPresentationMode;
                if (currentMode === 'picture-in-picture') {
                    video.webkitSetPresentationMode('inline');
                    state.isPipActive = false;
                } else {
                    video.webkitSetPresentationMode('picture-in-picture');
                    state.isPipActive = true;
                }
                return;
            }

            // 3. Enter PiP
            await enterPip(false);
        } finally {
            setTimeout(() => {
                isToggling = false;
            }, 500);
        }
    }

    function bindEvents() {
        // Toggle PiP on #pictureInPicture button click
        $(document).on('click', '#pictureInPicture', function (e) {
            e.preventDefault();
            togglePictureInPicture();
        });

        // Toggle PiP with click/tap on any meeting video
        $(document).on('click', '#videos video', function () {
            if (this.id === 'previewVideo') return;

            if (document.pictureInPictureElement || state.isPipActive) {
                exitPip();
            } else {
                if (hasVideoTrack(this) && typeof this.requestPictureInPicture === 'function') {
                    this.requestPictureInPicture().catch(() => {
                        enterPip(false);
                    });
                } else {
                    togglePictureInPicture();
                }
            }
        });

        // Native browser PiP events to keep UI in sync
        document.addEventListener('enterpictureinpicture', function () {
            state.isPipActive = true;
            $('#pictureInPicture').addClass('active').html('<i class="fa fa-arrow-left"></i>');
        });

        document.addEventListener('leavepictureinpicture', function () {
            state.isPipActive = false;
            isAutoPip = false;
            $('#pictureInPicture').removeClass('active').html('<i class="fa fa-external-link-alt"></i>');
            if (canvasAnimId) {
                clearInterval(canvasAnimId);
                canvasAnimId = null;
            }
        });
    }

    // Auto-initialize when DOM is ready in case meeting is already initiated
    $(function () {
        if (typeof Meeting !== 'undefined' && Meeting.state && Meeting.state.initiated) {
            initializePictureInPicture();
        }
    });
})();
