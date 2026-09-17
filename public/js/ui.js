/**
 * ui.js
 * -----------------------------------------------------------------------
 * Loads LAST. This is the "glue" layer: moderator admin controls
 * (kick/mic-admin/camera-admin/mute-all/make-moderator), the
 * show/hide-options bar, double-click "big video" mode, keyboard
 * shortcuts, raise-hand, and the copy-meeting-link button.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    Meeting.ui = {
        showOptions: showOptions,
        hideOptions: hideOptions,
        manageOptions: manageOptions,
        initKeyShortcuts: initKeyShortcuts,
        addModeratorButtons: addModeratorButtons,
        handleMicAdmin: handleMicAdmin,
        handleCameraAdmin: handleCameraAdmin,
        handleMicToggled: handleMicToggled,
        handleCameraToggled: handleCameraToggled,
        handleMuteAll: handleMuteAll,
        handleChangeModerator: handleChangeModerator,
        handleModeratorUpdated: handleModeratorUpdated,
        handleModeRatorButtons: handleModeRatorButtons
    };

    // ---------------------------------------------------------------
    // Options bar (meeting ID / control bar auto-hide)
    // ---------------------------------------------------------------
    function manageOptions() {
        $('.meeting-options').show();
        $('#meetingIdInfo').text(meetingTitle);
        localStorage.setItem('videoQuality', videoQualitySelect.value);

        if (state.meetingType === 'video') {
            $('#toggleVideo').show();
        }

        setTimeout(function () {
            hideOptions();
            $('.local-user-name, .remote-user-name, .kick').hide();
        }, 3000);

        $('body').on('mousemove', function () {
            showOptions();
        });
    }

    function hideOptions() {
        $('.meeting-options, .meeting-info').hide();
    }

    function showOptions() {
        $('.meeting-options, .meeting-info').show();

        if (state.mouseMoveTimer) {
            clearTimeout(state.mouseMoveTimer);
        }

        state.mouseMoveTimer = setTimeout(function () {
            hideOptions();
        }, 3000);
    }

    $(document).on('mouseover', '.videoContainer', function () {
        $(this).find('span, button').show();
    });

    $(document).on('mouseout', '.videoContainer', function () {
        $(this).find('span, button').hide();
    });

    // enter into bigger video mode with double click on video
    $(document).on('dblclick', 'video', function () {
        if (this.id == "previewVideo") return;

        let parentElement = $(this).parent();
        if (parentElement.hasClass('OT_big')) {
            parentElement.removeClass('OT_big');
        } else {
            parentElement.addClass('OT_big');
        }

        state.layout();
    });

    // ---------------------------------------------------------------
    // Moderator admin controls
    // ---------------------------------------------------------------
    $(document).on('click', '.kick', function () {
        if (confirm(languages.confirmation_kick)) {
            $(this).attr('disabled', true);
            utils.sendMessage(state.socket, {
                type: 'kick',
                toSocketId: $(this).data('id'),
            });
        }
    });

    $(document).on('click', '.mic-admin', function () {
        if ($(this).data('muted')) {
            $(this).html('<i class="fa fa-microphone"></i>').data('muted', false);
            utils.sendMessage(state.socket, { type: 'mic-admin', toSocketId: $(this).data('id'), value: false });
        } else {
            $(this).html('<i class="fa fa-microphone-slash"></i>').data('muted', true);
            utils.sendMessage(state.socket, { type: 'mic-admin', toSocketId: $(this).data('id'), value: true });
        }
    });

    $(document).on('click', '.camera-admin', function () {
        if ($(this).data('muted')) {
            $(this).html('<i class="fa fa-video"></i>').data('muted', false);
            utils.sendMessage(state.socket, { type: 'camera-admin', toSocketId: $(this).data('id'), value: false });
        } else {
            $(this).html('<i class="fa fa-video-slash"></i>').data('muted', true);
            utils.sendMessage(state.socket, { type: 'camera-admin', toSocketId: $(this).data('id'), value: true });
        }
    });

    function handleMicAdmin(value) {
        state.audioMuted = !value;
        Meeting.media.manageMic(true);
    }

    function handleCameraAdmin(value) {
        state.videoMuted = !value;
        Meeting.media.manageCamera(true);
    }

    function handleMicToggled(socketId, value) {
        if (value) {
            $('#container-' + socketId + ' .mic-admin').html('<i class="fa fa-microphone-slash"></i>').data('muted', true);
        } else {
            $('#container-' + socketId + ' .mic-admin').html('<i class="fa fa-microphone"></i>').data('muted', false);
        }
    }

    function handleCameraToggled(socketId, value) {
        if (value) {
            $('#container-' + socketId + ' .camera-admin').html('<i class="fa fa-video-slash"></i>').data('muted', true);
        } else {
            $('#container-' + socketId + ' .camera-admin').html('<i class="fa fa-video"></i>').data('muted', false);
        }
    }

    $(document).on('click', '.make-moderator', function () {
        if (!confirm(languages.moderator_confirm)) return;

        utils.sendMessage(state.socket, {
            type: 'moderatorAssignment',
            toSocketId: $(this).data('id'),
            meetingId: userInfo.meetingId,
        });
    });

    function handleChangeModerator() {
        state.isModerator = true;
        showSuccess(languages.you_moderator);

        utils.sendMessage(state.socket, {
            type: 'moderatorUpdated',
            meetingId: userInfo.meetingId,
            username: userInfo.username,
            socketId: state.socket.id
        });

        $("#muteAll").html(state.allMuted ? '<i class="fa fa-users-slash"></i>' : '<i class="fa fa-users"></i>').show();

        $("#toggleMic, #toggleVideo").attr('disabled', false);
        $(".moderator-icon").hide();
        $(".local-user-name .moderator-icon, #participantListBody    td:first .moderator-icon").show();
    }

    function handleModeratorUpdated(username, socketId) {
        showSuccess(languages.moderator_updated + username);
        if (state.isModerator) {
            state.isModerator = false;
            $('[id^=container-]').find('.meeting-option').remove(); // remove moderator action buttons
            $("#muteAll").hide();
        }

        utils.sendMessage(state.socket, {
            type: 'moderatorButtons',
            meetingId: userInfo.meetingId,
            toSocketId: socketId,
            fromSocketId: state.socket.id,
            audioMuted: state.audioMuted,
            videoMuted: state.videoMuted,
            meetingType: state.meetingType
        });

        $(".moderator-icon").hide();
        $("#container-" + socketId + " .moderator-icon, #listId-" + socketId + " .moderator-icon").show();
    }

    function handleModeRatorButtons(data) {
        const containerDiv = document.getElementById("container-" + data.fromSocketId);
        addModeratorButtons(containerDiv, data.audioMuted, data.videoMuted, data.fromSocketId, data.meetingType == 'video');
    }

    function addModeratorButtons(containerDiv, mic, camera, socketId, isVideoMeeting) {
        let kickButton = document.createElement('button');
        kickButton.className = 'btn meeting-option kick';
        kickButton.innerHTML = '<i class="fa fa-ban"></i>';
        kickButton.setAttribute('data-id', socketId);
        kickButton.setAttribute('title', languages.kick_user);
        containerDiv.appendChild(kickButton);

        let micButton = document.createElement('button');
        micButton.className = 'btn meeting-option mic-admin';
        micButton.innerHTML = (mic || state.allMuted) ? '<i class="fa fa-microphone-slash"></i>' : '<i class="fa fa-microphone"></i>';
        micButton.setAttribute('data-id', socketId);
        micButton.setAttribute('data-muted', mic || state.allMuted);
        micButton.setAttribute('title', languages.toggleMic);
        containerDiv.appendChild(micButton);

        if (isVideoMeeting) {
            let cameraButton = document.createElement('button');
            cameraButton.className = 'btn meeting-option camera-admin';
            cameraButton.innerHTML = camera ? '<i class="fa fa-video-slash"></i>' : '<i class="fa fa-video"></i>';
            cameraButton.setAttribute('data-id', socketId);
            cameraButton.setAttribute('data-muted', camera);
            cameraButton.setAttribute('title', languages.toggleCamera);
            containerDiv.appendChild(cameraButton);
        }

        let moderatorButton = document.createElement('button');
        moderatorButton.className = 'btn meeting-option make-moderator';
        moderatorButton.innerHTML = '<i class="fas fa-crown"></i>';
        moderatorButton.setAttribute('data-id', socketId);
        moderatorButton.setAttribute('title', languages.make_moderator);
        containerDiv.appendChild(moderatorButton);
    }

    // ---------------------------------------------------------------
    // Mute all
    // ---------------------------------------------------------------
    async function handleMuteAll(value) {
        if (value) {
            $("#toggleMic").html('<i class="fa fa-microphone-slash"></i>').attr('disabled', true);
            showSuccess(languages.mic_muted_moderator);
            if (state.audioMuted) return;

            state.localStream.getAudioTracks().forEach((track) => (track.enabled = false));

            localAudio.srcObject = null;

            state.audioMuted = state.allMuted = true;
            if (state.isRecording) state.mixer.resetVideoStreams(Meeting.recording.getVideoStreams());

            Meeting.media.stopHark();
        } else {
            state.localStream.getAudioTracks().forEach((track) => (track.enabled = true));

            $("#toggleMic").html('<i class="fa fa-microphone"></i>').attr('disabled', false);
            state.audioMuted = state.allMuted = false;
            showSuccess(languages.mic_unmuted_moderator);

            if (state.isRecording) state.mixer.appendStreams(new MediaStream([audioParams.track]));

            Meeting.media.initHark();
        }
    }

    $(document).on('click', '#muteAll', function () {
        if (!state.isModerator) return;

        if (state.allMuted) {
            utils.sendMessage(state.socket, { type: 'muteAll', value: false, meetingId: userInfo.meetingId });
            state.allMuted = false;
            $("#muteAll").html('<i class="fa fa-users"></i>');
            showInfo(languages.you_unmuted);
        } else {
            utils.sendMessage(state.socket, { type: 'muteAll', value: true, meetingId: userInfo.meetingId });
            state.allMuted = true;
            $("#muteAll").html('<i class="fa fa-users-slash"></i>');
            showInfo(languages.you_muted);
        }
    });

    // ---------------------------------------------------------------
    // Raise hand
    // ---------------------------------------------------------------
    $(document).on('click', '#raiseHand', function () {
        if (!utils.featureAvailable('hand_raise')) return;

        showInfo(languages.hand_raised_self);

        utils.sendMessage(state.socket, {
            type: 'raiseHand',
            username: userInfo.username,
        });
    });

    // ---------------------------------------------------------------
    // Copy meeting link
    // ---------------------------------------------------------------
    $(document).on('click', '#copyMeetingLink', function () {
        const meetingLink = window.location.href;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(meetingLink).then(function () {
                showSuccess('Meeting link copied to clipboard!');
            }).catch(function (err) {
                console.error('Could not copy text: ', err);
                fallbackCopyTextToClipboard(meetingLink);
            });
        } else {
            fallbackCopyTextToClipboard(meetingLink);
        }
    });

    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showSuccess('Meeting link copied to clipboard!');
            } else {
                showError('Failed to copy meeting link');
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            showError('Failed to copy meeting link');
        }

        document.body.removeChild(textArea);
    }

    // ---------------------------------------------------------------
    // Video object-fit preference
    // ---------------------------------------------------------------
    $(document).on('change', '#videoObjectFit', function () {
        $(".cam").css('object-fit', this.value);
        localStorage.setItem('objectFit', this.value);
    });

    // ---------------------------------------------------------------
    // Keyboard shortcuts
    // ---------------------------------------------------------------
    function initKeyShortcuts() {
        $(document).on('keydown', function (e) {
            if ($('#messageInput, #chatGPTmessageInput').is(':focus') || window.picker.isPickerVisible()) return;

            switch (e.key) {
                case 'C':
                case 'c':
                    $('.chat-panel').animate({ width: 'toggle' });

                    if ($('#openChat').hasClass('notify')) {
                        $('#openChat').removeClass('notify');
                        state.messageCount = 0;
                    }
                    break;
                case 'F':
                case 'f':
                    if ($('.chat-panel').is(':hidden')) {
                        $('.chat-panel').animate({ width: 'toggle' });
                    }
                    $('#selectFile').trigger('click');
                    break;
                case 'A':
                case 'a':
                    $('#toggleMic').trigger('click');
                    break;
                case 'L':
                case 'l':
                    $('#leave').trigger('click');
                    break;
                case 'V':
                case 'v':
                    if (state.meetingType === 'video') $('#toggleVideo').trigger('click');
                    break;
                case 'S':
                case 's':
                    $('#screenShare').trigger('click');
                    break;
            }
        });
    }
})();
