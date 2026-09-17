/**
 * recording.js
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    Meeting.recording = {
        startRecording: startRecording,
        stopRecording: stopRecording,
        getVideoStreams: getVideoStreams,
        handleRecordingPermissionResult: handleRecordingPermissionResult
    };

    $(document).on('click', '.approveRecording', function () {
        $(this).closest('.toast').remove();
        utils.sendMessage(state.socket, {
            type: 'recordongPermissionResult',
            result: true,
            toSocketId: $(this).data('from'),
        });
    });

    $(document).on('click', '.declineRecording', function () {
        $(this).closest('.toast').remove();
        utils.sendMessage(state.socket, {
            type: 'recordongPermissionResult',
            result: false,
            toSocketId: $(this).data('from'),
            message: languages.request_declined,
        });
    });

    function handleRecordingPermissionResult(data) {
        $("#recording").attr('disabled', false);
        toastr.clear();

        if (data.result) {
            startRecording();
        } else {
            showInfo(languages.record_request_declined);
        }
    }

    // -------------------------------------------------------------------
    // Handling Modal Choices (Local vs Cloud Recording)
    // -------------------------------------------------------------------
    // استماع لضغطات أزرار المودال (حسّب الـ ID أو الـ Class المعتمد في الـ HTML)
    $(document).on('click', '#startLocalRecording, .btn-local-recording', function () {
        state.recordingType = 'local';
        $("#recordingTypeModal").hide();
        window.__skipRecordingModal = true;
        startRecording();
    });

    $(document).on('click', '#startCloudRecording, .btn-cloud-recording', function () {
        state.recordingType = 'cloud';
        $("#recordingTypeModal").hide();
        window.__skipRecordingModal = true;
        startRecording();
    });

    $(document).on('click', '#recording', function () {
        if (!utils.featureAvailable('recording')) return;

        if (state.isOnIOS) {
            showError(languages.feature_not_supported);
            return;
        }

        if (state.isRecording) {
            stopRecording();
        } else {
            if (state.isModerator || state.settings.authMode == "disabled" || state.settings.moderatorRights == "disabled") {
                var rtModal = document.getElementById("recordingTypeModal");
                if (rtModal && !window.__skipRecordingModal) {
                    rtModal.style.display = "flex";
                    return;
                }
                window.__skipRecordingModal = false;
                startRecording();
            } else {
                $(this).attr('disabled', true);

                utils.sendMessage(state.socket, {
                    type: 'recordingPermission',
                    username: userInfo.username,
                    meetingId: userInfo.meetingId
                });
            }
        }
    });

    function startRecording() {
        state.mixer = new MultiStreamsMixer(getVideoStreams());
        state.mixer.frameInterval = 1;
        state.mixer.startDrawingFrames();

        var __src;
        if (state.screenStream && state.screenStream.getVideoTracks().length > 0) {
            __src = new MediaStream();
            state.screenStream.getVideoTracks().forEach(function (t) {
                var s = t.getSettings();
                console.log("[REC] Screen track:", s.width + "x" + s.height + " @ " + s.frameRate + "fps");
                __src.addTrack(t);
            });
            if (state.localStream) {
                state.localStream.getAudioTracks().forEach(function (t) { __src.addTrack(t); });
            }
            state.screenStream.getAudioTracks().forEach(function (t) { __src.addTrack(t); });
        } else {
            __src = state.mixer.getMixedStream();
        }

        const usernameLS = localStorage.getItem('pendingUsername');
        state.recordingOriginalFileName = (usernameLS ? usernameLS.replace(/\s+/g, '_') : 'Guest')
            + "_" + meetingTitle.replace(/\s+/g, '_') + "_" + Date.now() + ".webm";

        state.isRecording = true;
        $("#recording").css('color', 'red');

        // Setup live chunk upload variables
        if (state.recordingType === 'cloud') {
            state.recordingSessionId = 'rec_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            state.recordingChunkIndex = 0;
            state.recordingUploadQueue = [];
            state.isUploadingChunks = false;

            // Inject pulse animation CSS if not present
            if (!document.getElementById('recording-pulse-style')) {
                const style = document.createElement('style');
                style.id = 'recording-pulse-style';
                style.textContent = `
                    @keyframes pulse-recording-dot {
                        0% { opacity: 0.3; }
                        50% { opacity: 1; }
                        100% { opacity: 0.3; }
                    }
                `;
                document.head.appendChild(style);
            }

            // Start MediaRecorder and request data every 5 seconds (5000ms)
            state.recorder = new MediaRecorder(__src, { videoBitsPerSecond: 8000000, audioBitsPerSecond: 192000 });
            state.recorder.start(5000);
            state.recorder.ondataavailable = function (e) {
                if (e.data && e.data.size > 0) {
                    state.recordingUploadQueue.push(e.data);
                    processUploadQueue();
                }
            };

            updateUploadProgressUI("Active", "Recording started. Synced up to part 0");
        } else {
            // Local Recording - request data every 1 second (1000ms) and accumulate in memory
            state.recorder = new MediaRecorder(__src, { videoBitsPerSecond: 8000000, audioBitsPerSecond: 192000 });
            state.recorder.start(1000);
            state.recorder.ondataavailable = function (e) {
                if (e.data && e.data.size > 0) {
                    state.recordingData.push(e.data);
                }
            };
        }

        utils.sendMessage(state.socket, {
            type: 'recordingStarted',
            username: userInfo.username,
            meetingId: userInfo.meetingId
        });
    }

    // Process chunk uploads sequentially
    function processUploadQueue() {
        if (state.isUploadingChunks) return;
        if (state.recordingUploadQueue.length === 0) return;

        state.isUploadingChunks = true;
        const chunk = state.recordingUploadQueue.shift();
        const chunkIndex = state.recordingChunkIndex++;

        updateUploadProgressUI("Uploading...", `Sending part ${chunkIndex + 1}...`);

        uploadChunk(chunk, chunkIndex)
            .then(() => {
                state.isUploadingChunks = false;
                updateUploadProgressUI("Active", `Synced up to part ${chunkIndex + 1}`);
                processUploadQueue();
            })
            .catch(error => {
                console.error("Failed to upload chunk " + chunkIndex + ", retrying...", error);
                // Return chunk to front of queue to retry
                state.recordingUploadQueue.unshift(chunk);
                state.recordingChunkIndex--; // decrement since it failed
                state.isUploadingChunks = false;

                updateUploadProgressUI("Retrying...", `Connection lost. Retrying part ${chunkIndex + 1} in 3s...`);

                setTimeout(processUploadQueue, 3000);
            });
    }

    // Send individual chunk to the server
    function uploadChunk(blob, index) {
        const formData = new FormData();
        formData.append("file", blob, `chunk_${index}.part`);
        formData.append("session_id", state.recordingSessionId);
        formData.append("chunk_index", index);
        formData.append("filename", state.recordingOriginalFileName);

        return fetch("/upload-video-chunk", {
            method: "POST",
            body: formData,
            credentials: "same-origin"
        })
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                throw new Error("Server error: " + text);
            }
            return response.json();
        });
    }

    // Show modern floating visual progress in UI
    function updateUploadProgressUI(status, message) {
        const container = document.getElementById('videoUploadMessageContainer');
        if (!container) return;

        // Apply compact pill styles for active recording status
        container.style.display = "flex";
        container.style.alignItems = "center";
        container.style.gap = "8px";
        container.style.padding = "6px 12px";
        container.style.borderRadius = "20px";
        container.style.minWidth = "auto";
        container.style.maxWidth = "auto";
        container.style.background = "rgba(15, 23, 42, 0.75)";
        container.style.backdropFilter = "blur(8px)";
        container.style.webkitBackdropFilter = "blur(8px)";
        container.style.border = "1px solid rgba(255, 255, 255, 0.1)";

        let badgeColor = "#10B981"; // Emerald
        if (status === "Uploading...") badgeColor = "#3B82F6"; // Blue
        if (status === "Retrying...") badgeColor = "#F59E0B"; // Amber
        if (status === "Finalizing...") badgeColor = "#8B5CF6"; // Purple

        container.innerHTML = `
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:${badgeColor}; animation: pulse-recording-dot 1.2s infinite ease-in-out;"></span>
            <span style="font-size:12px; font-weight:700; color:#fff; letter-spacing:0.5px;">REC</span>
        `;
        container.title = `Cloud Recording\nStatus: ${status}\n${message}`;
    }

    // Wait for the remaining chunks to upload, then call finalization route
    function finalizeCloudRecording() {
        updateUploadProgressUI("Finalizing...", "Processing final recording files. Please do not close the window.");

        function checkAndFinalize() {
            if (state.recordingUploadQueue.length > 0 || state.isUploadingChunks) {
                setTimeout(checkAndFinalize, 500);
                return;
            }

            const totalChunks = state.recordingChunkIndex;
            const usernameLS = localStorage.getItem('pendingUsername');

            const payload = {
                session_id: state.recordingSessionId,
                total_chunks: totalChunks,
                filename: state.recordingOriginalFileName,
                username: usernameLS || 'Guest'
            };

            fetch("/finalize-video-upload", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload),
                credentials: "same-origin"
            })
            .then(async response => {
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error("Finalize failed: " + text);
                }
                return response.json();
            })
            .then(data => {
                const videoUrl = window.location.origin + "/videos/" + data.file;

                const container = document.getElementById('videoUploadMessageContainer');
                if (container) {
                    // Reset to card layout for final confirmation and link copying
                    container.style.display = "block";
                    container.style.padding = "15px 20px";
                    container.style.borderRadius = "12px";
                    container.style.minWidth = "260px";
                    container.style.maxWidth = "320px";
                    container.style.background = "#1e1e1e";
                    container.style.border = "none";
                    container.title = "";

                    container.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #333; padding-bottom:8px; margin-bottom:8px;">
                            <div style="font-size:14px; font-weight:700; color:#10B981; display:flex; align-items:center; gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Recording Saved
                            </div>
                            <button id="closeMsgBtn"
                                style="background: transparent; border: none; color: #9CA3AF; font-size: 16px; cursor: pointer; font-weight: bold; padding: 0 4px;"
                                title="Close">×</button>
                        </div>
                        <div style="font-size:12px; color:#D1D5DB; margin-bottom:10px; line-height:1.4;">
                            Your video is stored safely and is ready for viewing.
                        </div>
                        <button id="copyVideoLink"
                            style="background: #10B981; color: white; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; width: 100%; transition: background 0.2s;"
                            onmouseover="this.style.background='#059669'"
                            onmouseout="this.style.background='#10B981'">
                            Copy Video Link
                        </button>
                        <div id="copyStatus" style="margin-top:8px; font-size:12px; text-align:center; min-height:16px; font-weight:500;"></div>
                    `;

                    document.getElementById('copyVideoLink').addEventListener('click', () => {
                        navigator.clipboard.writeText(videoUrl)
                            .then(() => {
                                document.getElementById('copyStatus').innerHTML =
                                    "<span style='color:#10B981;'>Copied successfully!</span>";
                            })
                            .catch(() => {
                                document.getElementById('copyStatus').innerHTML =
                                    "<span style='color:#EF4444;'>Failed to copy</span>";
                            });
                    });

                    document.getElementById('closeMsgBtn').addEventListener('click', () => {
                        container.style.display = "none";
                    });
                }

                state.isRecording = false;
                state.recordingData = [];
                state.recordingType = null;
                $("#recording").css('color', 'white');
            })
            .catch(error => {
                showError("Finalization failed: " + error.message);
                
                const container = document.getElementById('videoUploadMessageContainer');
                if (container) {
                    // Reset to card layout for error display
                    container.style.display = "block";
                    container.style.padding = "15px 20px";
                    container.style.borderRadius = "12px";
                    container.style.minWidth = "260px";
                    container.style.maxWidth = "320px";
                    container.style.background = "#1e1e1e";
                    container.style.border = "none";
                    container.title = "";

                    container.innerHTML = `
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #333; padding-bottom:8px; margin-bottom:8px;">
                            <div style="font-size:14px; font-weight:700; color:#EF4444;">Upload Failed</div>
                            <button id="closeMsgBtn"
                                style="background: transparent; border: none; color: #9CA3AF; font-size: 16px; cursor: pointer; font-weight: bold; padding: 0 4px;"
                                title="Close">×</button>
                        </div>
                        <div style="font-size:12px; color:#D1D5DB; line-height:1.4;">
                            Error: ${error.message}
                        </div>
                    `;
                    document.getElementById('closeMsgBtn').addEventListener('click', () => {
                        container.style.display = "none";
                    });
                }

                state.isRecording = false;
                state.recordingData = [];
                state.recordingType = null;
                $("#recording").css('color', 'white');
            });
        }

        checkAndFinalize();
    }

    // Stop recording, handle local download OR cloud upload finalization
    function stopRecording() {
        try {
            try { if (state.mixer && state.mixer.audioSources) state.mixer.releaseStreams(); } catch (e) { console.warn("mixer release failed:", e); }

            state.recorder.stop();
            state.recorder = state.recorder.ondataavailable = null;

            if (state.recordingType === 'local') {
                const videoBlob = new Blob(state.recordingData, { type: "video/webm" });
                const fileName = state.recordingOriginalFileName;

                const link = document.createElement('a');
                link.href = URL.createObjectURL(videoBlob);
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                if (typeof showSuccess === 'function') {
                    showSuccess("Recording saved to your device!");
                }

                state.isRecording = false;
                state.recordingData = [];
                state.recordingType = null;
                $("#recording").css('color', 'white');
            } else {
                finalizeCloudRecording();
            }
        } catch (err) {
            showError("Error while stopping the recording: " + err.message);
            state.isRecording = false;
            state.recordingData = [];
            state.recordingType = null;
            $("#recording").css('color', 'white');
        }
    }

    function getVideoStreams() {
        let hasVideoTrack = false;
        let videoStreams = [];

        $("#videos video").each((key, value) => {
            if (value.srcObject.getVideoTracks().length) {
                hasVideoTrack = true;
            }
            videoStreams.push(value.srcObject);
        });

        if (recordingPreference.value == 'with' && parseInt(features['whiteboard'])) {
            hasVideoTrack = true;
            if (state.whiteboardAdded) {
                videoStreams.push($("iframe").contents().find("#main-canvas")[0].captureStream());
            } else {
                Meeting.whiteboard.show();
                setTimeout(function () {
                    state.mixer.appendStreams($("iframe").contents().find("#main-canvas")[0].captureStream());
                }, 3000);
            }
        }

        if (!hasVideoTrack) {
            videoStreams.push(audioOnly.captureStream());
        }

        return videoStreams;
    }

    recordingPreference.onchange = function () {
        localStorage.setItem('recordingPreference', this.value);
    };

    recordingPreference.value = localStorage.getItem('recordingPreference') || 'with';
})();