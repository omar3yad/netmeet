/**
 * media.js
 * -----------------------------------------------------------------------
 * Loads after meeting-core.js. Owns everything about the local camera/mic:
 * getUserMedia fallbacks, preview, mute/unmute, device switching, and
 * active-speaker detection (hark).
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    const audioInputSelect = document.querySelector('select#audioSource');
    const videoInputSelect = document.querySelector('select#videoSource');
    const selectors = [audioInputSelect, videoInputSelect];

    Meeting.media = {
        setVideoPreview: setVideoPreview,
        initHark: initHark,
        stopHark: stopHark,
        handleSpeaking: handleSpeaking,
        updateButtonStates: updateButtonStates,
        getAudioConstraints: getAudioConstraints,
        getVideoConstraints: getVideoConstraints,
        replaceMediaTrack: replaceMediaTrack
    };

    // ---------------------------------------------------------------
    // getUserMedia with graceful fallbacks (mic-only / cam-only / none)
    // ---------------------------------------------------------------
    async function setVideoPreview() {
        if (state.previewLoading) {
            console.log("setVideoPreview is already running. Skipping concurrent call.");
            return;
        }
        state.previewLoading = true;

        try {
            $("#toggleCameraPreview").addClass('disabled');
            document.getElementById("overlay").style.display = "block";

            document.getElementById("overlayText").innerText = languages.checking_mic_cam_permission;
            setTimeout(() => {
                document.getElementById("overlayText").innerHTML = languages.click_allow;
            }, 1000);

            let hasMic = false;
            let hasCam = false;

            // Detect hardware first
            try {
                if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    hasMic = devices.some(d => d.kind === 'audioinput');
                    hasCam = devices.some(d => d.kind === 'videoinput');
                } else {
                    hasMic = true;
                    hasCam = true;
                }
            } catch (e) {
                console.warn("enumerateDevices failed, assuming devices exist:", e);
                hasMic = true;
                hasCam = true;
            }

            console.log(`Detected hardware - Mic: ${hasMic}, Camera: ${hasCam}`);

            let success = false;

            if (hasMic || hasCam) {
                state.constraints = {
                    audio: hasMic ? getAudioConstraints() : false,
                    video: hasCam ? getVideoConstraints() : false
                };

                try {
                    state.localStream = await navigator.mediaDevices.getUserMedia(state.constraints);
                    success = true;
                } catch (e) {
                    console.error("getUserMedia failed for detected devices:", e);
                    // Fallback in case permission is denied or device is busy
                    if (hasMic && hasCam) {
                        console.log("Retrying with audio-only fallback...");
                        try {
                            state.constraints = { audio: getAudioConstraints(), video: false };
                            state.localStream = await navigator.mediaDevices.getUserMedia(state.constraints);
                            success = true;
                        } catch (err2) {
                            console.log("Audio-only fallback failed, trying video-only...");
                            try {
                                state.constraints = { audio: false, video: getVideoConstraints() };
                                state.localStream = await navigator.mediaDevices.getUserMedia(state.constraints);
                                success = true;
                            } catch (err3) {
                                console.log("All media fallbacks failed.");
                            }
                        }
                    }
                }
            }

            if (!success) {
                console.log("No devices found or permission denied, initializing empty stream.");
                state.localStream = new MediaStream();
            }

            document.getElementById("overlay").style.display = "none";

            if (state.localStream) {
                if (state.localStream.getVideoTracks().length > 0) {
                    previewVideo.srcObject = state.localStream;
                    previewVideo.style.zIndex = 5;
                    $(".text-show").text('');
                    $("#toggleCameraPreview").html('<i class="fa fa-video"></i>').removeClass('disabled');
                    state.meetingType = 'video';
                } else {
                    previewVideo.srcObject = null;
                    previewVideo.style.zIndex = 0;
                    $(".text-show").text(languages.audio_only || "Audio-only meeting");
                    $("#toggleCameraPreview").html('<i class="fa fa-video-slash"></i>').removeClass('disabled');
                    state.meetingType = 'audio';
                }
            }
        } catch (e) {
            console.warn("Error in setVideoPreview:", e);
            state.localStream = new MediaStream();
            document.getElementById("overlay").style.display = "none";
        } finally {
            state.previewLoading = false;
        }
    }

    // toggle video preview (pre-join screen)
    $("#toggleCameraPreview").on('click', function () {
        if (state.localStream && state.localStream.getVideoTracks().length) {
            state.localStream.getVideoTracks().forEach((track) => track.stop());
            state.localStream.removeTrack(state.localStream.getVideoTracks()[0]);
            previewVideo.srcObject = null;
            previewVideo.style.zIndex = 0;
            $("#toggleCameraPreview").html('<i class="fa fa-video-slash"></i>');
            state.meetingType = 'audio';
        } else {
            state.meetingType = '';
            setVideoPreview();
        }
    });

    // ---------------------------------------------------------------
    // In-meeting mic/camera toggle (first-registered handlers)
    // ---------------------------------------------------------------
    $(document).on('click', '#toggleVideo', function () {
        if (!state.isModerator && state.settings.moderatorRights == "enabled") {
            utils.sendMessage(state.socket, {
                type: 'cameraToggled',
                videoMuted: !state.videoMuted,
                meetingId: userInfo.meetingId
            });
        }

        manageCamera(false);
    });

async function manageCamera(byModerator) {
    if (state.isModerator) $("#toggleVideo").attr('disabled', true);

    // إذا لم يكن هناك Video Track، أنشئ واحدًا جديدًا
    if (state.localStream.getVideoTracks().length === 0) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
    audio: false
            });

            const videoTrack = stream.getVideoTracks()[0];

            state.localStream.addTrack(videoTrack);
            state.meetingType = "video";
            replaceMediaTrack(videoTrack);

            if (localVideo) {
                localVideo.srcObject = state.localStream;
            }

            $("#toggleVideo").html('<i class="fa fa-video"></i>');
            state.videoMuted = false;
            state.meetingType = 'video';

            showSuccess(byModerator ? languages.camera_on_moderator : languages.camera_on);

        } catch (e) {
            console.error(e);
            showError(languages.no_video || "Unable to access camera");
        }

        if (state.isModerator) $("#toggleVideo").attr('disabled', false);
        return;
    }

    // السلوك الحالي إذا كان الـ Video Track موجودًا
    if (state.videoMuted) {
        state.localStream.getVideoTracks().forEach(track => track.enabled = true);
        $("#toggleVideo").html('<i class="fa fa-video"></i>');
        state.videoMuted = false;
        showSuccess(byModerator ? languages.camera_on_moderator : languages.camera_on);
    } else {
        state.localStream.getVideoTracks().forEach(track => track.enabled = false);
        $("#toggleVideo").html('<i class="fa fa-video-slash"></i>');
        state.videoMuted = true;
        showSuccess(byModerator ? languages.camera_off_moderator : languages.camera_off);
    }

    if (state.isModerator) $("#toggleVideo").attr('disabled', false);
}

    $(document).on('click', '#toggleMic', function () {
        if (!state.isModerator && state.settings.moderatorRights == "enabled") {
            utils.sendMessage(state.socket, {
                type: 'micToggled',
                audioMuted: !state.audioMuted,
                meetingId: userInfo.meetingId
            });
        }

        manageMic(false);
    });

    async function manageMic(byModerator) {
        if (state.isModerator) $("#toggleMic").attr('disabled', true);

        if (state.audioMuted) {
            state.localStream.getAudioTracks().forEach((track) => (track.enabled = true));
            $('#toggleMic').html('<i class="fa fa-microphone"></i>');
            state.audioMuted = false;
            showSuccess(byModerator ? languages.mic_unmuted_moderator : languages.mic_unmute);

            initHark();
        } else {
            state.localStream.getAudioTracks().forEach((track) => (track.enabled = false));
            $('#toggleMic').html('<i class="fa fa-microphone-slash"></i>');
            state.audioMuted = true;
            showSuccess(byModerator ? languages.mic_muted_moderator : languages.mic_mute);

            stopHark();
        }

        if (state.isModerator) $("#toggleMic").attr('disabled', false);
    }

    // Expose manageCamera/manageMic for ui.js (mic-admin / camera-admin controls)
    Meeting.media.manageCamera = manageCamera;
    Meeting.media.manageMic = manageMic;

    // ---------------------------------------------------------------
    // Active-speaker detection (hark)
    // ---------------------------------------------------------------
    function initHark() {
        state.speechEvents = hark(state.localStream, {});
        state.speechEvents.on("speaking", sendSpeakingIndication);
        state.speechEvents.on("stopped_speaking", sendSpeakingIndication);
    }

    function sendSpeakingIndication() {
        document.getElementById("selfContainer").classList.toggle('speaking-shadow');

        utils.sendMessage(state.socket, {
            type: "speaking",
            fromSocketId: state.socket.id,
        });
    }

    function stopHark() {
        if (state.speechEvents) {
            state.speechEvents.stop();
        }
    }

    function handleSpeaking(data) {
        document.getElementById("container-" + data.fromSocketId).classList.toggle('speaking-shadow');
    }

    // ---------------------------------------------------------------
    // Device settings modal
    // ---------------------------------------------------------------
    $('.openSettings').on('click', async function () {
        $('#settings').modal('show');
        let devices = await navigator.mediaDevices.enumerateDevices();
        gotDevices(devices);
    });

    function gotDevices(deviceInfos) {
        const values = selectors.map((select) => select.value);
        selectors.forEach((select) => {
            while (select.firstChild) {
                select.removeChild(select.firstChild);
            }
        });
        for (let i = 0; i !== deviceInfos.length; ++i) {
            const deviceInfo = deviceInfos[i];
            const option = document.createElement('option');
            option.value = deviceInfo.deviceId;
            if (deviceInfo.kind === 'audioinput') {
                option.text = deviceInfo.label || `microphone ${audioInputSelect.length + 1}`;
                audioInputSelect.appendChild(option);
            } else if (deviceInfo.kind === 'videoinput') {
                option.text = deviceInfo.label || `camera ${videoInputSelect.length + 1}`;
                videoInputSelect.appendChild(option);
            }
        }
        selectors.forEach((select, selectorIndex) => {
            if (Array.prototype.slice.call(select.childNodes).some((n) => n.value === values[selectorIndex])) {
                select.value = values[selectorIndex];
            }
        });
    }

    function getAudioConstraints() {
        try {
            const audioSource = audioInputSelect.value;
            return {
                deviceId: audioSource ? { exact: audioSource } : undefined,
            };
        } catch (e) {
            console.warn("Error getting audio constraints:", e);
            return false;
        }
    }

    function getVideoConstraints() {
        if (state.meetingType == 'audio') {
            return false;
        } else {
            try {
                const videoSource = videoInputSelect.value;
                return {
                    deviceId: videoSource ? { exact: videoSource } : undefined,
                    width: { ideal: $('#' + videoQualitySelect.value).data('width') || 640 },
                    height: { ideal: $('#' + videoQualitySelect.value).data('height') || 480 },
                };
            } catch (e) {
                console.warn("Error getting video constraints:", e);
                return false;
            }
        }
    }

    videoQualitySelect.onchange = videoInputSelect.onchange = async function () {
        const option = videoQualitySelect.options[videoQualitySelect.selectedIndex];

        if (
            (features["video_quality"] == "VGA" && option.getAttribute("data-width") > 640) ||
            (features["video_quality"] == "HD" && option.getAttribute("data-width") > 1280) ||
            (features["video_quality"] == "FHD" && option.getAttribute("data-width") > 1920)
        ) {
            videoQualitySelect.value = "VGA";
            if (state.isModerator) {
                showError(languages.feature_not_available);
            } else {
                showError(languages.premiumFeature);
            }
            return;
        }

        if (!(state.localStream && state.localStream.getVideoTracks() && state.localStream.getVideoTracks().length)) return;

        state.constraints = {
            video: getVideoConstraints(),
        };

        try {
            state.localStream.getVideoTracks().forEach((track) => track.stop());
            let videoStream = await navigator.mediaDevices.getUserMedia(state.constraints);
            state.localStream.removeTrack(state.localStream.getVideoTracks()[0]);
            replaceMediaTrack(videoStream.getVideoTracks()[0]);
            videoSource.value = state.localStream.getVideoTracks()[0].getSettings().deviceId;
            localStorage.setItem('videoQuality', videoQualitySelect.value);
        } catch (e) {
            showError(e.name);
            $("#videoQualitySelect").val('VGA').trigger('change');
        }
    };

    audioSource.onchange = async function () {
        if (!state.localStream) return;

        state.constraints = {
            audio: getAudioConstraints(),
        };

        try {
            state.localStream.getAudioTracks().forEach((track) => track.stop());
            let audioStream = await navigator.mediaDevices.getUserMedia(state.constraints);
            state.localStream.removeTrack(state.localStream.getAudioTracks()[0]);
            replaceMediaTrack(audioStream.getAudioTracks()[0]);
        } catch (e) {
            console.log(languages.no_device + e.name);
        }
    };

    function replaceMediaTrack(track) {
        if (state.localStream) state.localStream.addTrack(track);

        Object.values(state.connections).forEach((connection) => {
            let sender = connection.getSenders().find(function (s) {
                return s.track.kind === track.kind;
            });

            sender.replaceTrack(track);
        });
    }

    // iPhone fix - while clicking on kick button, video got paused
    localVideo.addEventListener("pause", (event) => {
        localVideo.play();
    });

    // ---------------------------------------------------------------
    // Second mic/cam toggle handlers (both fire on click, same as the
    // original file, which bound '#toggleMic'/'#toggleVideo' twice)
    // ---------------------------------------------------------------
    $(document).on('click', '#toggleMic', function () {
        if (!utils.featureAvailable('audio')) return;

        if (state.audioMuted) {
            unmuteAudio();
        } else {
            muteAudio();
        }
    });

    function muteAudio() {
        if (state.localStream) {
            const audioTrack = state.localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = false;
                state.audioMuted = true;
                $('#toggleMic').html('<i class="fa fa-microphone-slash"></i>').addClass('btn-danger').removeClass('btn-primary');
                showInfo(languages.mic_mute);
            }
        }
    }

    function unmuteAudio() {
        if (state.localStream) {
            const audioTrack = state.localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = true;
                state.audioMuted = false;
                $('#toggleMic').html('<i class="fa fa-microphone"></i>').removeClass('btn-danger').addClass('btn-primary');
                showInfo(languages.mic_unmute);
            }
        }
    }

    function muteVideo() {
        if (state.localStream) {
            const videoTrack = state.localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = false;
                state.videoMuted = true;
                $('#toggleVideo').html('<i class="fa fa-video-slash"></i>').addClass('btn-danger').removeClass('btn-primary');
                showInfo(languages.camera_off);
            }
        }
    }

    function unmuteVideo() {
        if (state.localStream) {
            const videoTrack = state.localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = true;
                state.videoMuted = false;
                $('#toggleVideo').html('<i class="fa fa-video"></i>').removeClass('btn-danger').addClass('btn-primary');
                showInfo(languages.camera_on);
            }
        }
    }

    function updateButtonStates() {
        console.log('Button states updated');
    }
})();
