/**
 * meeting-core.js
 * -----------------------------------------------------------------------
 * Loads SECOND (after helpers.js). Owns:
 *   - Meeting.state: the    single shared state object (replaces the ~50
 *     `let` variables that used to live in meeting.js's closure).
 *   - App bootstrap (get-details -> socket connect).
 *   - The socket 'message' switch statement, which now just DELEGATES
 *     each case to the module that owns that behavior.
 *   - Password/join flow, waiting-for-host polling, init(), timer
 *     lifecycle, leave/pagehide cleanup.
 *
 * Other modules read/write shared data via `Meeting.state.*` and call
 * into each other via their public `Meeting.<module>.*` API - never by
 * reaching into another module's private functions.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    const isOnIOS = navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPhone/i);
    // Always use 'pagehide' so browsers don't show the native 'Leave site?' prompt
    const eventName = "pagehide";

    // ---------------------------------------------------------------
    // Shared state (formerly closure-local `let`s scattered across the
    // 3000-line file). Everything here is intentionally mutable and
    // shared across modules.
    // ---------------------------------------------------------------
    Meeting.state = {
        isMobile: isMobile,
        isOnIOS: isOnIOS,
        eventName: eventName,

        isRejoin: false,
        socket: null,
        screenSocket: null,
        screenSocketId: null,
        constraints: null,
        localStream: null,
        meetingType: null,
        currentMeetingTime: null,
        layoutContainer: document.getElementById('videos'),
        layout: null,

        mixer: null,
        recorder: null,
        screenStream: null,
        speechEvents: null,
        mouseMoveTimer: null,
        displayFileUrl: null,
        resizeTimeout: null,
        messageCount: 0,
        recordingData: [],
        isRecording: false,
        connections: [],
        screenConnections: [],
        usernames: [],
        avatars: [],
        settings: {},
        configuration: {},
        allMuted: false,
        audioMuted: false,
        videoMuted: false,
        initiated: false,
        screenShared: false,
        whiteboardAdded: false,
        whiteboardVisible: false,
        waitingForChatGPT: false,
        isModerator: moderator,
        timer: new easytimer.Timer(),
        notificationTone: new Audio('/sounds/notification.mp3'),
        intentionalLeave: false,

        // Join / waiting-for-host flow
        isHost: userInfo.role === 'moderator',
        passwordVerified: false,
        meetingStarted: false,
        isWaitingForHost: false,
        waitingPollInterval: null,
        pollingInterval: null,

        // Picture-in-Picture
        isPipActive: false,

        // Screen share (kept for parity with original declaration; the
        // active flag actually used at runtime is `screenShared`)
        isScreenSharing: false
    };

    Meeting.state.layout = initLayoutContainer(Meeting.state.layoutContainer).layout;

    const state = Meeting.state;
    const utils = Meeting.utils;

    // ---------------------------------------------------------------
    // Public API used by the other modules
    // ---------------------------------------------------------------
    Meeting.core = {
        layout: function () { state.layout(); },
        showWaitingForHost: showWaitingForHost,
        hideWaitingForHost: hideWaitingForHost,
        featureAvailable: utils.featureAvailable
    };

    // ---------------------------------------------------------------
    // Bootstrap: fetch signaling/session details, then connect
    // ---------------------------------------------------------------
    (function () {
        $.post({ url: "/get-details" })
            .done(function (data) {
                data = JSON.parse(data);

                if (data.success) {
                    state.settings = data.data;

                    initializeSocket(state.settings.signalingURL);

                    state.configuration = {
                        iceServers: [
                            { urls: state.settings.stunUrl },
                            {
                                urls: state.settings.turnUrl,
                                username: state.settings.turnUsername,
                                credential: state.settings.turnPassword,
                            },
                        ],
                    };
                } else {
                    showError(languages.no_session);
                }
            })
            .catch(function () {
                showError(languages.no_session);
            });
    })();

    // ---------------------------------------------------------------
    // Signaling socket + message dispatch
    // ---------------------------------------------------------------
    function initializeSocket(signalingURL) {
        state.socket = io.connect(signalingURL);
        const socket = state.socket;

        socket.on("file", function (data) {
            Meeting.chat.handleIncomingFile(data.file, data.username);
        });

        socket.on("connect_error", function () {
            $('#joinMeeting').attr('disabled', true);
            $("#error").show();
        });

        socket.on("connect", function () {
            $('#joinMeeting').attr('disabled', false);
            $("#error").hide();

            // Only auto rejoin if meeting was already started before disconnect
            if (state.isRejoin && state.initiated && state.localStream) {
                console.log("[Rejoin] Reconnecting to meeting...");
                setTimeout(() => {
                    utils.sendMessage(socket, {
                        type: 'join',
                        username: userInfo.username,
                        meetingId: userInfo.meetingId,
                        isModerator: state.isModerator,
                        screen: false,
                        avatar: userInfo.avatar,
                        mic: state.audioMuted,
                        camera: state.videoMuted
                    });
                }, 200);
                state.isRejoin = false; // reset after rejoin
            }
        });

        // On disconnect -> mark rejoin attempt
        socket.on("disconnect", function () {
            if (state.initiated) {
                console.warn("[Socket] Disconnected. Will rejoin on reconnect...");
                state.isRejoin = true;
            }
        });

        // Listen for socket message event and dispatch to the owning module
        socket.on('message', function (data) {
            data = JSON.parse(data);

            switch (data.type) {
                case 'join':
                    Meeting.webrtc.handleJoin(state.localStream, socket, data);
                    break;
                case 'offer':
                    Meeting.webrtc.handleOffer(state.localStream, socket, data, data.screen);
                    break;
                case 'answer':
                    Meeting.webrtc.handleAnswer(data);
                    break;
                case 'candidate':
                    Meeting.webrtc.handleCandidate(data);
                    break;
                case 'leave':
                    Meeting.webrtc.handleLeave(data);
                    break;
                case 'checkMeetingResult':
                case 'permissionResult':
                    checkMeetingResult(data);
                    break;
                case 'meetingMessage':
                    Meeting.chat.handleMeetingMessage(data);
                    break;
                case "fileMessage":
                    Meeting.chat.handleFileMessage(data);
                    break;
                case 'permission':
                    handlePermission(data);
                    break;
                case 'info':
                    // Suppress host-ending info for attendees; keep meeting alive for them
                    if (!state.isModerator && typeof data.message === 'string' && /end/i.test(data.message)) {
                        break;
                    }
                    toastr.info(languages[data.message], "", {
                        timeOut: 0,
                        extendedTimeOut: 0,
                    });
                    break;
                case 'kick':
                    showInfo(languages.kicked);
                    utils.reload(0);
                    break;
                case 'whiteboard':
                    Meeting.whiteboard.handleWhiteboard(data.data);
                    break;
                case 'hostDisconnected':
                    handleHostDisconnected();
                    break;
                case 'hostReconnected':
                    handleHostReconnected();
                    break;
                case 'clearWhiteboard':
                    Meeting.whiteboard.designer.clearCanvas();
                    Meeting.whiteboard.designer.sync();
                    break;
                case 'raiseHand':
                    showInfo(languages.hand_raised + ': ' + data.username);
                    break;
                case 'sync':
                    Meeting.whiteboard.designer.sync();
                    break;
                case 'currentTime':
                    // Update the timer if the user joins an existing room
                    state.timer.stop();
                    state.timer.start({
                        precision: 'seconds',
                        startValues: { seconds: data.currentTime },
                        target: { seconds: timeLimit * 60 - 60 },
                    });
                    break;
                case 'recordingPermission':
                    // NOTE (pre-existing behavior preserved): the original file
                    // called handleRecordingPermission(data) here, but that
                    // function's body was already commented out in the source
                    // meeting.js. Preserved as-is rather than silently "fixed".
                    Meeting.recording.handleRecordingPermission(data);
                    break;
                case 'recordongPermissionResult':
                    Meeting.recording.handleRecordingPermissionResult(data);
                    break;
                case 'screenSharePermission':
                    Meeting.screenShare.handleScreenSharePermission(data);
                    break;
                case 'screenSharePermissionResult':
                    Meeting.screenShare.handleScreenSharePermissionResult(data);
                    break;
                case 'recordingStarted':
                    state.notificationTone.play();
                    showInfo(languages.recording_started + ": " + data.username);
                    break;
                case "speaking":
                    Meeting.media.handleSpeaking(data);
                    break;
                case "chatGPTResponse":
                    Meeting.chat.handleChatGPTMessage(data);
                    break;
                case 'mic-admin':
                    Meeting.ui.handleMicAdmin(data.value);
                    break;
                case 'camera-admin':
                    Meeting.ui.handleCameraAdmin(data.value);
                    break;
                case 'micToggled':
                    Meeting.ui.handleMicToggled(data.fromSocketId, data.audioMuted);
                    break;
                case 'cameraToggled':
                    Meeting.ui.handleCameraToggled(data.fromSocketId, data.videoMuted);
                    break;
                case 'muteAll':
                    Meeting.ui.handleMuteAll(data.value);
                    break;
                case 'moderatorAssignment':
                    Meeting.ui.handleChangeModerator(data.value);
                    break;
                case 'moderatorUpdated':
                    Meeting.ui.handleModeratorUpdated(data.username, data.socketId);
                    break;
                case 'moderatorButtons':
                    Meeting.ui.handleModeRatorButtons(data);
                    break;
            }
        });

        // get item from localStorage and set to html
        videoQualitySelect.value = localStorage.getItem('videoQuality') || 'VGA';
        username.value = localStorage.getItem('username');

        Meeting.media.setVideoPreview();
    }

    // ---------------------------------------------------------------
    // Timer lifecycle
    // ---------------------------------------------------------------
    state.timer.addEventListener('secondsUpdated', function () {
        state.currentMeetingTime = state.timer.getTimeValues().minutes * 60 + state.timer.getTimeValues().seconds;
        $('#timer').text(utils.getCurrentTime());
    });

    state.timer.addEventListener('targetAchieved', function () {
        $('#timer').css('color', 'red');
        state.timer.stop();
        state.timer.start({
            precision: 'seconds',
            startValues: { seconds: state.currentMeetingTime },
        });

        // Only the moderator triggers the global end notice and redirect.
        // Attendees should not be forced to leave when time elapses or host leaves.
        if (state.isModerator) {
            Meeting.ui.showOptions();
            showInfo(languages.meeting_ending);
            setTimeout(function () {
                showInfo(languages.meeting_ended);
                utils.reload(1);
            }, 60 * 1000);
        }
    });

    // ---------------------------------------------------------------
    // Password check / join flow
    // ---------------------------------------------------------------
    $('#passwordCheck').on('submit', function (e) {
        e.preventDefault();

        if (!state.localStream) return;
        $('#joinMeeting').attr('disabled', true);

        if (!state.socket.connected) {
            showError(languages.cant_connect);
            $('#joinMeeting').attr('disabled', false);
            return;
        }

        if (passwordRequired) {
            $.ajax({
                url: '/check-meeting-password',
                data: $(this).serialize(),
                type: 'post',
            })
                .done(function (data) {
                    data = JSON.parse(data);
                    $('#joinMeeting').attr('disabled', false);

                    if (data.success) {
                        state.passwordVerified = true;
                        continueToMeeting();
                    } else {
                        showError(languages.invalid_password);
                    }
                })
                .catch(function () {
                    showError();
                    $('#joinMeeting').attr('disabled', false);
                });
        } else {
            if (state.isModerator) {
                state.passwordVerified = true;
                continueToMeeting();
            } else {
                sendJoinRequestToHost();
                showWaitingForHost();
                startWaitingForHostPolling();
                $('#joinMeeting').attr('disabled', false);
            }
        }
    });

    function sendJoinRequestToHost() {
        utils.sendMessage(state.socket, {
            type: 'permission',
            username: userInfo.username,
            meetingId: userInfo.meetingId,
            fromSocketId: state.socket.id
        });
    }

    function continueToMeeting() {
        userInfo.username = username.value || htmlEscape(state.settings.defaultUsername);
        localStorage.setItem('username', userInfo.username);

        utils.sendMessage(state.socket, {
            type: 'checkMeeting',
            username: userInfo.username,
            meetingId: userInfo.meetingId,
            moderator: state.isModerator,
            authMode: state.settings.authMode,
            moderatorRights: state.settings.moderatorRights,
            userLimit
        });

        if (!state.isModerator) {
            showWaitingForHost();
            startWaitingForHostPolling();
        }
    }

    async function checkMeetingResult(data) {
        console.log("[Polling Response]", data);
        toastr.clear();

        if (data.result && state.localStream) {
            console.log("[Meeting Start Triggered] Host started. Joining...");

            if (state.isModerator) $("#muteAll").show();

            hideWaitingForHost();
            stopWaitingForHostPolling();

            $('#waiting-container').remove();
            $('#waiting-overlay').remove();
            $('.meeting-details, .navbar, footer').hide();
            $('.meeting-section').show();

            setTimeout(() => {
                if (!document.getElementById("localVideo")) {
                    console.error("#localVideo not found - cannot init meeting.");
                    return;
                }
                init();
            }, 50);

            if (data.allMuted) Meeting.ui.handleMuteAll(true);
        } else if (!data.result) {
            if (!state.isModerator && !state.isWaitingForHost) {
                console.log("[Waiting] Host not started yet. Redirecting to waiting page...");

                localStorage.setItem("pendingMeetingId", userInfo.meetingId);
                localStorage.setItem("pendingUsername", userInfo.username);
                localStorage.setItem("pendingIsModerator", state.isModerator);

                state.isWaitingForHost = true;
                showWaitingForHost();
                startWaitingForHostPolling();
            }
        } else if (!data.result && data.isRejoin) {
            console.log("[Rejoin Detected] Skipping waiting - restoring meeting...");
            init();
        } else {
            showError(languages[data.message]);
            $('#joinMeeting').attr('disabled', false);
        }
    }

    function showWaitingForHost() {
        $('.meeting-details, .navbar, footer, .meeting-section').hide();
        $('#waiting-container').remove(); // avoid duplicates
        $('body').append(`
            <div id="waiting-container" style="position:fixed;top:0;left:0;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:Arial;background:#fff;z-index:99999;">
                <div class="spinner"></div>
                <h2 style="margin-top:20px;">The host has not started the meeting yet</h2>
                <p>You will be joined automatically once the meeting starts...</p>
            </div>
            <style>
                .spinner {
                    width: 60px;
                    height: 60px;
                    border: 6px solid #ccc;
                    border-top-color: #ff0000;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            </style>
        `);
    }

    function hideWaitingForHost() {
        state.isWaitingForHost = false;
        const overlay = document.getElementById('waiting-overlay');
        if (overlay) overlay.remove();
        const waiting = document.getElementById('waiting-container');
        if (waiting) waiting.remove();
        $('.meeting-details, .navbar, footer').show();
        $('.meeting-section').show();
    }

    function startWaitingForHostPolling() {
        stopWaitingForHostPolling(); // Prevent duplicate timers
        state.pollingInterval = setInterval(() => {
            utils.sendMessage(state.socket, {
                type: 'checkMeeting',
                username: userInfo.username,
                meetingId: userInfo.meetingId,
                moderator: state.isModerator,
                authMode: state.settings.authMode,
                moderatorRights: state.settings.moderatorRights,
                userLimit
            });
        }, 3000); // every 3s
    }

    function stopWaitingForHostPolling() {
        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
            state.pollingInterval = null;
        }
    }

    // notify the moderator for new join request
    function handlePermission(data) {
        if (!state.isModerator) return;

        if (!state.initiated) {
            showWaitingForHost();
            utils.sendMessage(state.socket, {
                type: 'permissionResult',
                result: false,
                toSocketId: data.fromSocketId,
                message: 'waiting_for_host'
            });
            return; // stop here so they aren't auto-approved yet
        }

        console.log("Host Started Successfully...")
        utils.sendMessage(state.socket, {
            type: 'permissionResult',
            result: true,
            toSocketId: data.fromSocketId,
            allMuted: state.allMuted
        });
    }

    $(document).on('click', '.approve', function () {
        $(this).closest('.toast').remove();
        utils.sendMessage(state.socket, {
            type: 'permissionResult',
            result: true,
            toSocketId: $(this).data('from'),
            allMuted: state.allMuted
        });
    });

    $(document).on('click', '.decline', function () {
        $(this).closest('.toast').remove();
        utils.sendMessage(state.socket, {
            type: 'permissionResult',
            result: false,
            toSocketId: $(this).data('from'),
            message: 'request_declined',
        });
    });

    // ---------------------------------------------------------------
    // Meeting init - fires once the host has actually started
    // ---------------------------------------------------------------
    function init() {
        const localVideoEl = document.getElementById("localVideo");
        const previewVideoEl = document.getElementById("previewVideo");

        if (!localVideoEl) {
            console.error("localVideo element not found");
            return;
        }
        if (state.localStream && state.localStream.getTracks().length > 0) {
            localVideoEl.srcObject = state.localStream;
        } else {
            localVideoEl.style.display = 'none';
            $('.user-initial')
                .text(userInfo.username[0])
                .css('background', utils.getRandomColor())
                .show();
        }
        if (previewVideoEl) previewVideoEl.srcObject = null;

        state.layout();
        if (!state.localStream || !state.localStream.getVideoTracks().length) {
            $('.user-initial')
                .text(userInfo.username[0])
                .css('background', utils.getRandomColor())
                .show();
        }

        const hasAudio = state.localStream && state.localStream.getAudioTracks().length > 0;
        const hasVideo = state.localStream && state.localStream.getVideoTracks().length > 0;

        utils.sendMessage(state.socket, {
            type: 'join',
            username: userInfo.username,
            meetingId: userInfo.meetingId,
            isModerator: state.isModerator,
            screen: false,
            avatar: userInfo.avatar,
            mic: !hasAudio || state.audioMuted,
            camera: !hasVideo || state.videoMuted
        });

        state.timer.start({
            precision: 'seconds',
            startValues: { seconds: 0 },
            target: { seconds: timeLimit * 60 - 60 }
        });

        Meeting.ui.manageOptions();

        if (state.isMobile && state.meetingType === 'video') $('#toggleCam').show();
        // Always show screen share button; permission flow will gate non-moderators
        $('#screenShare').show();

        // Show Picture-in-Picture button (both branches show it; preserved as-is)
        if (state.isModerator || state.settings.authMode == "disabled" || state.settings.moderatorRights == "disabled") {
            $('#pictureInPicture').show();
        } else {
            $('#pictureInPicture').show();
        }

        $('#toggleVideo').show();
        $('#toggleMic').show();

        Meeting.media.updateButtonStates();

        Meeting.ui.initKeyShortcuts();

        if (!localStorage.getItem('tripDone')) {
            setTimeout(() => {
                showInfo(languages.double_click);
                showInfo(languages.single_click);
                localStorage.setItem('tripDone', true);
            }, 3000);
        }

        $('#showParticipantList').addClass('number').attr('data-content', 1);

        if (!state.audioMuted) {
            Meeting.media.initHark();
        }

        Meeting.pip.initializePictureInPicture();

        state.initiated = true;
    }

    // When host disconnects unexpectedly, attendees should wait without leaving
    function handleHostDisconnected() {
        if (state.isModerator) return; // host doesn't need to show waiting for self
        if (document.getElementById('waiting-container')) return;
        showWaitingForHost();
    }

    function handleHostReconnected() {
        hideWaitingForHost();
    }

    // ---------------------------------------------------------------
    // Leave / cleanup
    // ---------------------------------------------------------------
    $(document).on('click', '#leave', function () {
        if (!confirm(languages.confirmation)) return;
        state.intentionalLeave = true;
        // Notify others to end only if moderator intentionally ends
        if (state.isModerator) {
            utils.sendMessage(state.socket, { type: 'hostEnded', meetingId: userInfo.meetingId });
        }
        showError(languages.meeting_ended);
        utils.reload(0);
    });

    // On page hide/close: clean up without triggering native confirm prompts
    window.addEventListener(eventName, function () {
        // Only run minimal cleanup for this client
        if (state.isModerator && state.initiated) {
            // Optional: save meeting state on the server if needed
            let form = new FormData();
            form.append("_token", $("[name=csrf-token]").attr("content"));
            form.append("meetingId", userInfo.meetingId);
            // navigator.sendBeacon("/save-meeting-state", form);
        }

        // Only send "leave" for non-host users - host should silently drop
        if (!state.isModerator) {
            try {
                console.log("Meeting Ended for you To rejoin enter Again");
                utils.sendMessage(state.socket, { type: 'leave', fromSocketId: state.socket.id, isModerator: state.isModerator });
            } catch (e) {}
        }

        // Clean up ONLY local video/audio
        let localVideoEl = document.getElementById('localVideo');
        if (localVideoEl) {
            localVideoEl.pause();
            localVideoEl.srcObject = null;
            localVideoEl.load();
        }

        if (state.isRecording) Meeting.recording.stopRecording();

        // Do NOT close the socket manually - allow natural disconnect for reconnection
        // Do NOT remove all remote participants' video elements
    });

    // Expose init() for other modules (core.checkMeetingResult path calls it internally already)
    Meeting.core.init = init;
})();
