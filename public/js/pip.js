/**
 * pip.js
 * -----------------------------------------------------------------------
 * Loads after screen-share.js. Owns Picture-in-Picture (PiP) and Auto-PiP.
 * Handles:
 *  - Automatic Picture-in-Picture when page/tab is backgrounded (visibilitychange)
 *  - Exit PiP when returning to foreground (if auto-entered)
 *  - Manual PiP toggle via #pictureInPicture button or video click
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

    // Ensure fallback video plays on any user interaction so it's always ready for PiP
    function ensurePlaybackOnInteraction() {
        function kickstart() {
            const fallback = document.getElementById('fallbackVideo');
            if (fallback && fallback.paused) {
                fallback.play().catch(() => {});
            }
        }
        document.addEventListener('click', kickstart, { passive: true });
        document.addEventListener('touchstart', kickstart, { passive: true });
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

    function isVideoActive(v) {
        if (!v) return false;
        if (v.id === 'previewVideo') return false;
        if (v.id === 'fallbackVideo') {
            return v.readyState >= 1;
        }
        if (!v.srcObject) return false;
        const tracks = v.srcObject.getVideoTracks();
        if (!tracks || tracks.length === 0) return false;
        return tracks.some(t => t.enabled && t.readyState !== 'ended');
    }

    function findBestVideo() {
        // 1. Screen share video (highest priority)
        const screenShareVideo = document.querySelector('.screen-share-container video, .videoContainer.OT_big video');
        if (screenShareVideo && isVideoActive(screenShareVideo)) {
            return screenShareVideo;
        }

        // 2. Active remote participant video
        const remoteVideos = document.querySelectorAll('#videos .videoContainer:not(#selfContainer) video');
        for (const v of remoteVideos) {
            if (v.id !== 'fallbackVideo' && isVideoActive(v)) {
                return v;
            }
        }

        // 3. Local camera video
        const localVideo = document.getElementById('localVideo');
        if (localVideo && isVideoActive(localVideo)) {
            return localVideo;
        }

        // 4. Any participant video with srcObject
        const anyVideo = document.querySelector('#videos video:not(#previewVideo)');
        if (anyVideo && anyVideo.srcObject) {
            return anyVideo;
        }

        // 5. Fallback video element (used when all cameras are off or audio-only)
        const fallback = document.getElementById('fallbackVideo');
        if (fallback) {
            return fallback;
        }

        return null;
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
        // If already in PiP, do nothing
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
            // If using fallback video, make sure it's playing
            if (video.id === 'fallbackVideo' && video.paused) {
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
            console.warn('[PiP] Request PiP failed:', err);
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
                if (typeof this.requestPictureInPicture === 'function') {
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
        });
    }

    // Auto-initialize when DOM is ready in case meeting is already initiated
    $(function () {
        if (typeof Meeting !== 'undefined' && Meeting.state && Meeting.state.initiated) {
            initializePictureInPicture();
        }
    });
})();
