/**
 * pip.js
 * -----------------------------------------------------------------------
 * Loads after screen-share.js. Owns Picture-in-Picture (PiP) and Auto-PiP.
 * Handles:
 *  - Automatic Picture-in-Picture when page/tab is backgrounded (visibilitychange)
 *  - Exit PiP when returning to foreground (if auto-entered)
 *  - Manual PiP toggle via #pictureInPicture button or video click
 *  - Dynamic Canvas Video fallback for audio-only / camera-off meetings
 *    (prevents "The video element has no video track" error)
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

    let canvasStreamVideo = null;
    let canvasAnimId = null;

    Meeting.pip = {
        initializePictureInPicture: initializePictureInPicture,
        togglePictureInPicture: togglePictureInPicture,
        enterPip: enterPip,
        exitPip: exitPip,
        findBestVideo: findBestVideo
    };

    // Expose global for inline onclick="toggleMeetingPiP()" in Blade template
    window.toggleMeetingPiP = togglePictureInPicture;

    function initializePictureInPicture() {
        if (isInitialized) return;
        isInitialized = true;

        setupAutoPip();
        setupWakeLock();
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
                canvasStreamVideo.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0.001;pointer-events:none;bottom:0;right:0;z-index:-1;';
                document.body.appendChild(canvasStreamVideo);
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
        // Background
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
            if (v.id !== 'fallbackVideo' && hasVideoTrack(v)) {
                return v;
            }
        }

        // 3. Local camera video with video tracks
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

        // Media Session handler for browsers supporting automatic/media PiP
        if ('mediaSession' in navigator) {
            try {
                navigator.mediaSession.setActionHandler('enterpictureinpicture', async () => {
                    await enterPip(true);
                });
            } catch (e) {
                console.log('[PiP] mediaSession enterpictureinpicture handler error:', e);
            }
        }

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
        if (document.pictureInPictureElement) {
            return;
        }

        // Try to enter PiP automatically immediately when user leaves / goes home
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
        if (document.pictureInPictureElement) return;

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

            if (!automatic && typeof showError === 'function') {
                showError(languages.no_pip || 'Picture-in-Picture is not supported in this browser');
            }
        } catch (err) {
            console.warn('[PiP] Primary video request failed:', err);

            // If primary video failed (e.g. track error), try canvas video as guaranteed fallback
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

            if (!automatic && typeof showError === 'function') {
                showError('Picture-in-Picture failed: ' + (err.message || err));
            }
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
        // 1. If currently in PiP, exit
        if (document.pictureInPictureElement) {
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

            if (document.pictureInPictureElement) {
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
