/**
 * pip.js
 * -----------------------------------------------------------------------
 * Loads after screen-share.js. Owns Picture-in-Picture (PiP) and Auto-PiP.
 * Handles:
 *  - Automatic Picture-in-Picture when page/tab is backgrounded (visibilitychange)
 *  - Exit PiP when returning to foreground
 *  - Fallback and toast guidance for iOS / unsupported browsers
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

        // 3. Local video
        const localVideo = document.getElementById('localVideo');
        if (localVideo && isVideoActive(localVideo)) {
            return localVideo;
        }

        // 4. Any video with a valid srcObject
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
        if (!document.pictureInPictureEnabled) return;
        const target = findBestVideo();
        const allVideos = document.querySelectorAll('video');
        allVideos.forEach(v => {
            if (v === target) {
                if (!v.autoPictureInPicture) {
                    v.autoPictureInPicture = true;
                    v.disablePictureInPicture = false;
                }
            } else {
                if (v.autoPictureInPicture) {
                    v.autoPictureInPicture = false;
                }
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
            // Page is now hidden (user switched app or went to Home screen)
            await onBackground();
        } else {
            // Page is visible again (user returned to app)
            await onForeground();
        }
    }

    async function onBackground() {
        // If already in PiP, do nothing
        if (document.pictureInPictureElement) {
            return;
        }

        // iOS Safari handling
        if (state.isOnIOS || !document.pictureInPictureEnabled) {
            const video = findBestVideo();
            if (video && typeof video.webkitSetPresentationMode === 'function') {
                try {
                    video.webkitSetPresentationMode('picture-in-picture');
                    state.isPipActive = true;
                    isAutoPip = true;
                    return;
                } catch (e) {
                    console.warn('[PiP] iOS webkitSetPresentationMode failed:', e);
                }
            }
            return;
        }

        // Standard Web Picture-in-Picture
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

            // iOS WebKit
            if (typeof video.webkitSetPresentationMode === 'function' && !document.pictureInPictureEnabled) {
                video.webkitSetPresentationMode('picture-in-picture');
                state.isPipActive = true;
                isAutoPip = automatic;
                return;
            }

            // Standard Chromium / Firefox
            if (document.pictureInPictureEnabled) {
                await video.requestPictureInPicture();
                state.isPipActive = true;
                isAutoPip = automatic;
            }
        } catch (err) {
            console.warn('[PiP] Request PiP failed:', err);
            if (!automatic && typeof showError === 'function') {
                showError(languages.no_pip || 'Picture-in-Picture could not be started');
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
        if (state.isOnIOS && !document.pictureInPictureEnabled) {
            const video = findBestVideo();
            if (video && typeof video.webkitSetPresentationMode === 'function') {
                const currentMode = video.webkitPresentationMode;
                if (currentMode === 'picture-in-picture') {
                    video.webkitSetPresentationMode('inline');
                    state.isPipActive = false;
                } else {
                    video.webkitSetPresentationMode('picture-in-picture');
                    state.isPipActive = true;
                }
                return;
            } else {
                if (typeof showInfo === 'function') {
                    showInfo('للاستمرار في الميتنج أثناء الخروج، يمكنك تفعيل ميزة الصورة داخل صورة يدويًا عبر مشغل الفيديو');
                }
                return;
            }
        }

        if (!document.pictureInPictureEnabled) {
            if (typeof showError === 'function') {
                showError(languages.no_pip || 'Picture-in-Picture is not supported in this browser');
            }
            return;
        }

        if (document.pictureInPictureElement) {
            await exitPip();
        } else {
            await enterPip(false);
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

            if (document.pictureInPictureElement) {
                exitPip();
            } else {
                if (this.readyState >= 1 && this.srcObject && this.srcObject.getVideoTracks().length) {
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
