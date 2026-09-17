/**
 * screen-share.js
 * -----------------------------------------------------------------------
 * Loads after webrtc.js. Owns the screen-share button, the permission
 * handshake with the host, the getDisplayMedia fallbacks, and the
 * mobile/desktop screen-share layout switching.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    Meeting.screenShare = {
        startScreenSharing: startScreenSharing,
        stopScreenSharing: stopScreenSharing,
        handleScreenSharePermission: handleScreenSharePermission,
        handleScreenSharePermissionResult: handleScreenSharePermissionResult
    };

    // notify the moderator for new screen share request
    function handleScreenSharePermission(data) {
        state.notificationTone.play();
        toastr.info(
            '<br><button type="button" class="btn btn-primary btn-sm clear approveScreenShare" data-from="' +
            data.fromSocketId +
            '">' + languages.approve + '</button><button type="button" class="btn btn-warning btn-sm clear ml-2 declineScreenShare" data-from="' +
            data.fromSocketId +
            '">' + languages.decline + '</button>',
            languages.request_screenshare + data.username, {
            tapToDismiss: false,
            timeOut: 0,
            extendedTimeOut: 0,
            newestOnTop: false,
        }
        );
    }

    $(document).on('click', '.approveScreenShare', function () {
        $(this).closest('.toast').remove();

        utils.sendMessage(state.socket, {
            type: 'screenSharePermissionResult',
            result: true,
            toSocketId: $(this).data('from'),
        });
    });

    $(document).on('click', '.declineScreenShare', function () {
        $(this).closest('.toast').remove();

        utils.sendMessage(state.socket, {
            type: 'screenSharePermissionResult',
            result: false,
            toSocketId: $(this).data('from'),
            message: languages.request_declined,
        });
    });

    function handleScreenSharePermissionResult(data) {
        $("#screenShare").attr('disabled', false);
        if (data.result) {
            console.log("Host approved screen sharing request");
            startScreenSharing();
        } else {
            console.log("Not supported at this screen");
            showInfo(languages.screenshare_request_declined);
        }
    }

    $(document).on('click', '#screenShare', function () {
        if (!utils.featureAvailable('screen_share')) return;

        if (state.screenShared) {
            stopScreenSharing();
        } else {
            if (state.isModerator || state.settings.authMode == "disabled" || state.settings.moderatorRights == "disabled") {
                startScreenSharing();
            } else {
                utils.sendMessage(state.socket, {
                    type: 'screenSharePermission',
                    username: userInfo.username,
                    meetingId: userInfo.meetingId
                });

                showInfo(languages.screen_share_request_sent);
            }
        }
    });

    async function startScreenSharing() {
        // Try to apply adapter polyfill if available (safely)
        if (typeof adapter !== 'undefined') {
            console.log("Adapter available:", adapter);
            if (typeof adapter.browserShim === 'function') {
                console.log("Applying adapter browser shim...");
                adapter.browserShim();
            }
        }

        console.log("Protocol:", location.protocol);
        console.log("Hostname:", location.hostname);
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            console.log("HTTPS check failed - showing error");
            showError("Screen sharing requires HTTPS. Please use HTTPS or localhost.");
            return;
        }
        console.log("HTTPS check passed");

        if (!navigator.mediaDevices) {
            if (navigator.getDisplayMedia) {
                navigator.mediaDevices = navigator.mediaDevices || {};
                navigator.mediaDevices.getDisplayMedia = navigator.getDisplayMedia;
            } else {
                showError("Screen sharing is not supported in this browser. Please use a modern browser.");
                return;
            }
        }

        if (typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
            if (typeof navigator.getDisplayMedia === 'function') {
                navigator.mediaDevices.getDisplayMedia = navigator.getDisplayMedia;
            } else {
                showError("Screen sharing is not supported in this browser. Please use a modern browser.");
                return;
            }
        }

        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        console.log("Is mobile device:", isMobile);

        state.screenSocket = io.connect(state.settings.signalingURL);
        console.log("Screen socket connected:", state.screenSocket.connected);

        let attempts = 0;
        const maxAttempts = 3;

        while (!state.screenStream && attempts < maxAttempts) {
            attempts++;
            console.log(`Screen sharing attempt ${attempts}/${maxAttempts}`);

            try {
                if (attempts === 1) {
                    state.screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: true,
                        audio: !isMobile
                    });
                } else if (attempts === 2) {
                    if (isMobile) {
                        state.screenStream = await navigator.mediaDevices.getDisplayMedia({
                            video: { displaySurface: 'browser' }
                        });
                    } else {
                        state.screenStream = await navigator.mediaDevices.getDisplayMedia({
                            video: { cursor: 'always' },
                            audio: true
                        });
                    }
                } else if (attempts === 3) {
                    state.screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                }

                if (state.screenStream) {
                    console.log(`Screen sharing attempt ${attempts} succeeded:`, state.screenStream);
                    break;
                }
            } catch (error) {
                console.error(`Screen sharing attempt ${attempts} failed:`, error);

                if (attempts === maxAttempts) {
                    if (error.name === 'NotAllowedError') {
                        showError("Screen sharing permission denied. Please allow screen sharing when prompted.");
                    } else if (error.name === 'NotFoundError') {
                        showError("No screen or window selected for sharing.");
                    } else if (error.name === 'NotSupportedError') {
                        showError("Screen sharing is not supported on this device/browser.");
                    } else {
                        showError("Screen sharing failed. Please try again or check your browser permissions.");
                    }
                    return;
                }
            }
        }

        console.log("Screen socket ID:", state.screenSocket.id);
        if (!state.screenSocket.id && state.screenStream) {
            console.log("Screen socket not connected, cleaning up...");
            state.screenStream.getVideoTracks().forEach((track) => track.stop());
            state.screenStream = null;
            state.screenSocket.disconnect();
            showError(languages.error_occurred);
            return;
        }

        if (state.screenStream) {
            state.screenShared = true;
            state.screenSocketId = state.screenSocket.id;

            showInfo(languages.screen_share_started);

            $('#screenShare').html('<i class="fa fa-stop"></i>').addClass('btn-danger').removeClass('btn-primary');

            setTimeout(() => {
                if (isMobile) {
                    applyMobileScreenShareLayout();
                } else {
                    applyDesktopScreenShareLayout();
                }
            }, 100);

            let videoScreen = document.createElement('video');
            videoScreen.id = 'video-' + state.screenSocketId;
            videoScreen.setAttribute('autoplay', '');
            videoScreen.setAttribute('playsinline', '');
            videoScreen.srcObject = state.screenStream;
            videoScreen.muted = true;

            if (isMobile) {
                videoScreen.style.width = '100%';
                videoScreen.style.height = '100%';
                videoScreen.style.objectFit = 'contain';
            }

            videoScreen.onloadedmetadata = function (e) {
                videoScreen.play();
            };

            let containerDiv = document.createElement('div');
            containerDiv.id = 'container-' + state.screenSocketId;
            containerDiv.className = 'videoContainer OT_big screen-share-container';

            let containerText = document.createElement('span');
            containerText.className = 'local-user-name';
            containerText.innerText = languages.your_screen;

            containerDiv.appendChild(videoScreen);
            containerDiv.appendChild(containerText);
            videos.appendChild(containerDiv);

            if (state.isRecording) state.mixer.appendStreams(state.screenStream);

            state.screenSocket.on('message', function (data) {
                data = JSON.parse(data);

                switch (data.type) {
                    case 'offer':
                        Meeting.webrtc.handleOffer(state.screenStream, state.screenSocket, data, true);
                        break;
                    case 'candidate':
                        // Needed so the screen-sharing peer can complete ICE negotiation
                        Meeting.webrtc.handleCandidate(data);
                        break;
                    case 'answer':
                        // Typically not needed on the answering side, but safe to handle
                        Meeting.webrtc.handleAnswer(data);
                        break;
                    case 'join':
                        Meeting.webrtc.handleJoin(state.screenStream, state.screenSocket, data, true);
                        break;
                    case 'leave':
                        Meeting.webrtc.handleLeave(data);
                        break;
                }
            });

            utils.sendMessage(state.screenSocket, {
                type: 'join',
                username: userInfo.username + '-screen',
                meetingId: userInfo.meetingId,
                moderator: false,
                screen: true,
                isModerator: state.isModerator
            });

            state.screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                stopScreenSharing();
            });
        } else {
            state.screenSocket.disconnect();
        }

        return state.screenStream;
    }

    // Helper function to handle screen share errors (kept for parity with
    // the original file; not currently called from anywhere, same as before)
    function handleScreenShareError(...errors) {
        console.error("Screen share errors:", errors);

        for (let error of errors) {
            if (error.name === 'NotAllowedError') {
                showError("Screen sharing permission denied. Please allow screen sharing when prompted.");
                return;
            } else if (error.name === 'NotFoundError') {
                showError("No screen or window selected for sharing.");
                return;
            } else if (error.name === 'NotSupportedError') {
                showError("Screen sharing is not supported on this device/browser.");
                return;
            }
        }

        showError("Screen sharing failed. Please try again or check your browser permissions.");
    }

    function applyMobileScreenShareLayout() {
        const videosContainer = document.getElementById('videos');
        if (!videosContainer) return;

        console.log('Applying mobile screen share layout...');

        videosContainer.classList.add('mobile-screen-sharing');

        const participantVideos = videosContainer.querySelectorAll('.videoContainer:not(.screen-share-container):not(#selfContainer)');
        participantVideos.forEach(video => {
            video.style.display = 'none';
        });

        const screenShareContainer = videosContainer.querySelector('.screen-share-container');
        if (screenShareContainer) {
            screenShareContainer.style.display = 'block';
            screenShareContainer.style.width = '100%';
            screenShareContainer.style.height = '70vh';
            screenShareContainer.style.maxWidth = '100%';
            screenShareContainer.style.maxHeight = '70vh';
            screenShareContainer.style.position = 'relative';
            screenShareContainer.style.margin = '0 auto';

            const video = screenShareContainer.querySelector('video');
            if (video) {
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'contain';
                video.style.backgroundColor = '#000';
            }
        }

        const selfContainer = videosContainer.querySelector('#selfContainer');
        if (selfContainer) {
            selfContainer.style.display = 'block';
            selfContainer.style.width = '100%';
            selfContainer.style.height = '25vh';
            selfContainer.style.maxWidth = '100%';
            selfContainer.style.maxHeight = '25vh';
            selfContainer.style.marginTop = '10px';
            selfContainer.style.position = 'relative';

            const video = selfContainer.querySelector('video');
            if (video) {
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
            }
        }

        console.log('Mobile screen share layout applied');
    }

    function applyDesktopScreenShareLayout() {
        const videosContainer = document.getElementById('videos');
        if (!videosContainer) return;

        console.log('Applying desktop screen share layout...');

        videosContainer.classList.add('screen-sharing');

        const participantVideos = videosContainer.querySelectorAll('.videoContainer:not(.screen-share-container):not(#selfContainer)');
        participantVideos.forEach(video => {
            video.style.display = 'none';
        });

        const screenShareContainer = videosContainer.querySelector('.screen-share-container');
        if (screenShareContainer) {
            screenShareContainer.style.display = 'block';
            screenShareContainer.style.width = '100%';
            screenShareContainer.style.height = '100%';
            screenShareContainer.style.maxWidth = 'none';
            screenShareContainer.style.maxHeight = 'none';
            screenShareContainer.style.position = 'relative';

            const video = screenShareContainer.querySelector('video');
            if (video) {
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'contain';
                video.style.backgroundColor = '#000';
            }
        }

        const selfContainer = videosContainer.querySelector('#selfContainer');
        if (selfContainer) {
            selfContainer.style.display = 'block';
            selfContainer.style.width = '200px';
            selfContainer.style.height = '150px';
            selfContainer.style.maxWidth = '200px';
            selfContainer.style.maxHeight = '150px';
            selfContainer.style.position = 'absolute';
            selfContainer.style.top = '20px';
            selfContainer.style.right = '20px';
            selfContainer.style.zIndex = '1000';
            selfContainer.style.borderRadius = '8px';
            selfContainer.style.overflow = 'hidden';
            selfContainer.style.boxShadow = '0 4px 20px rgba(0,0,0,0.3)';
            selfContainer.style.border = '2px solid #fff';

            const video = selfContainer.querySelector('video');
            if (video) {
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
            }
        }

        console.log('Desktop screen share layout applied');
    }

    function stopScreenSharing() {
        console.log('Stopping screen sharing...');

        if (state.screenStream) {
            state.screenStream.getVideoTracks().forEach((track) => track.stop());
            state.screenStream = null;
        }

        state.screenShared = false;

        const screenShareContainer = document.querySelector('.screen-share-container');
        if (screenShareContainer) {
            screenShareContainer.remove();
        }

        const videosContainer = document.getElementById('videos');
        if (videosContainer) {
            videosContainer.classList.remove('screen-sharing', 'mobile-screen-sharing');

            const participantVideos = videosContainer.querySelectorAll('.videoContainer');
            participantVideos.forEach(video => {
                video.style.display = 'block';
                video.style.width = '';
                video.style.height = '';
                video.style.maxWidth = '';
                video.style.maxHeight = '';
                video.style.position = '';
                video.style.top = '';
                video.style.right = '';
                video.style.zIndex = '';
                video.style.borderRadius = '';
                video.style.overflow = '';
                video.style.boxShadow = '';
                video.style.marginTop = '';
                video.style.border = '';

                const videoElement = video.querySelector('video');
                if (videoElement) {
                    videoElement.style.width = '';
                    videoElement.style.height = '';
                    videoElement.style.objectFit = '';
                    videoElement.style.backgroundColor = '';
                }
            });

            setTimeout(() => {
                state.layout();
            }, 100);
        }

        $('#screenShare').html('<i class="fa fa-desktop"></i>').removeClass('btn-danger').addClass('btn-primary');

        if (state.screenSocket) {
            state.screenSocket.disconnect();
            state.screenSocket = null;
        }

        state.screenSocketId = null;

        console.log('Screen sharing stopped, layout reset');
        showInfo(languages.screen_share_stopped);
    }
})();
