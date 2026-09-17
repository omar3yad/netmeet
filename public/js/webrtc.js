/**
 * webrtc.js
 * -----------------------------------------------------------------------
 * Loads after media.js. Owns peer-connection setup, SDP offer/answer,
 * ICE candidates, and rendering remote video elements as tracks arrive.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    Meeting.webrtc = {
        handleJoin: handleJoin,
        handleOffer: handleOffer,
        handleAnswer: handleAnswer,
        handleCandidate: handleCandidate,
        setupListeners: setupListeners,
        handleLeave: handleLeave
    };

    // Resize handling lives here since it's tightly coupled to `layout()`
    // being re-run whenever the video grid needs to reflow.
    window.onresize = function () {
        clearTimeout(state.resizeTimeout);
        state.resizeTimeout = setTimeout(function () {
            state.layout();
        }, 20);
    };

    // create and send an offer for newly joined user
    function handleJoin(stream, currentSocket, data, isScreenShareInitiator) {
        if (state.screenSocket && data.socketId == state.screenSocketId) return;

        state.usernames[data.socketId] = data.username;
        state.avatars[data.socketId] = data.avatar;

        // stop screen sharing
        if (data.screen && state.settings.limitedScreenShare == 'enabled' && state.screenShared) {
            Meeting.screenShare.stopScreenSharing();
        }

        let isScreen = isScreenShareInitiator || data.screen;

        let connection = new RTCPeerConnection(state.configuration);
        isScreen ? (state.screenConnections[data.socketId] = connection) : (state.connections[data.socketId] = connection);

        setupListeners(stream, currentSocket, connection, data.socketId, isScreen, data.isModerator);

        connection
            .createOffer({ offerToReceiveVideo: true })
            .then(function (offer) {
                return connection.setLocalDescription(offer);
            })
            .then(function () {
                utils.sendMessage(currentSocket, {
                    type: 'offer',
                    sdp: connection.localDescription,
                    username: userInfo.username + (isScreen ? '-screen' : ''),
                    fromSocketId: currentSocket.id,
                    toSocketId: data.socketId,
                    isModerator: state.isModerator,
                    avatar: userInfo.avatar,
                    screen: isScreen
                });
            })
            .catch(function (e) {
                console.log(languages.error_message, e);
            });
    }

    // handle offer from initiator, create and send an answer
    function handleOffer(stream, currentSocket, data, isScreen) {
        state.usernames[data.fromSocketId] = data.username;
        state.avatars[data.fromSocketId] = data.avatar;

        let connection = new RTCPeerConnection(state.configuration);
        isScreen ? (state.screenConnections[data.fromSocketId] = connection) : (state.connections[data.fromSocketId] = connection);

        connection.setRemoteDescription(data.sdp);
        setupListeners(stream, currentSocket, connection, data.fromSocketId, isScreen, data.isModerator);

        connection
            .createAnswer()
            .then(function (answer) {
                setDescriptionAndSendAnswer(currentSocket, answer, data.fromSocketId, isScreen);
            })
            .catch(function (e) {
                console.log(e);
            });
    }

    function setDescriptionAndSendAnswer(currentSocket, answer, fromSocketId, isScreen) {
        let currentConnection = isScreen ? state.screenConnections[fromSocketId] : state.connections[fromSocketId];

        currentConnection.setLocalDescription(answer);
        utils.sendMessage(currentSocket, {
            type: 'answer',
            answer: answer,
            fromSocketId: currentSocket.id,
            toSocketId: fromSocketId,
            screen: isScreen
        });
    }

    function handleAnswer(data) {
        let currentConnection = data.screen ? state.screenConnections[data.fromSocketId] : state.connections[data.fromSocketId];
        currentConnection.setRemoteDescription(data.answer);
    }

    function handleCandidate(data) {
        let currentConnection = data.screen ? state.screenConnections[data.fromSocketId] : state.connections[data.fromSocketId];

        if (data.candidate && currentConnection) {
            currentConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
        }
    }

    // add local track to the connection, manage remote track, ice candidate
    // and connection state change events.
    function setupListeners(stream, currentSocket, connection, socketId, isScreen, userIsModerator) {
        if (stream && (!isScreen || (isScreen && currentSocket === state.screenSocket))) {
            stream.getTracks().forEach((track) => connection.addTrack(track, stream));
        }

        connection.onicecandidate = (event) => {
            if (event.candidate) {
                utils.sendMessage(currentSocket, {
                    type: 'candidate',
                    candidate: event.candidate,
                    fromSocketId: currentSocket.id,
                    toSocketId: socketId,
                    screen: isScreen
                });
            }
        };

        connection.ontrack = (event) => {
            if (document.getElementById('video-' + socketId)) {
                return;
            }

            if (state.isRecording) state.mixer.appendStreams(event.streams[0]);

            let videoRemote = document.createElement('video');
            videoRemote.id = 'video-' + socketId;
            videoRemote.setAttribute('autoplay', '');
            videoRemote.setAttribute('playsinline', '');
            videoRemote.srcObject = event.streams[0];

            videoRemote.onloadedmetadata = function (e) {
                videoRemote.play().catch(function () {});
            };

            let containerDiv = document.createElement('div');
            containerDiv.id = 'container-' + socketId;

            if (isScreen) {
                // Remote screen share: use the same classes as local screen share
                containerDiv.className = 'videoContainer OT_big screen-share-container';
                videoRemote.style.width = '100%';
                videoRemote.style.height = '100%';
                videoRemote.style.objectFit = 'contain';
                videoRemote.style.backgroundColor = '#000';
                videoRemote.style.position = 'relative';
                videoRemote.style.zIndex = '2';
            } else {
                containerDiv.className = 'videoContainer';
            }

            let containerText = document.createElement('span');
            containerText.className = isScreen ? 'local-user-name' : 'remote-user-name';
            containerText.innerHTML = state.usernames[socketId] + " <i class='fas fa-crown moderator-icon' title='" + languages.moderator + "' " + (!userIsModerator ? "style='display: none'" : "") + "></i>";

            // Only add avatar/initial for non-screen-share connections
            if (!isScreen) {
                if (state.avatars[socketId]) {
                    let containerAvatar = document.createElement('img');
                    containerAvatar.className = 'user-initial';
                    containerAvatar.src = '/storage/avatars/' + state.avatars[socketId];
                    containerDiv.appendChild(containerAvatar);
                } else {
                    let containerInitial = document.createElement('p');
                    containerInitial.className = 'user-initial';
                    containerInitial.innerText = state.usernames[socketId][0];
                    containerInitial.style.background = utils.getRandomColor();
                    containerDiv.appendChild(containerInitial);
                }
            }

            if (!isScreen && state.isModerator && state.settings.moderatorRights == "enabled") {
                const isVideoMeeting = event.streams[0].getVideoTracks().length;
                Meeting.ui.addModeratorButtons(containerDiv, false, !isVideoMeeting, socketId, isVideoMeeting);
            }

            const moderatorIcon = " <i class='fas fa-crown moderator-icon' title='" + languages.moderator + "' " + (!userIsModerator ? "style='display: none'" : "") + " ></i>";

            $("#participantListBody").append("<tr id='list-" + socketId + "'><th scope='row'></th><td id='listId-" + socketId + "'>" + state.usernames[socketId] + moderatorIcon + "</td></tr>");
            $('#showParticipantList').addClass('number').attr('data-content', Object.keys(state.usernames).length + 1);

            containerDiv.appendChild(videoRemote);
            containerDiv.appendChild(containerText);
            videos.appendChild(containerDiv);

            if (!isScreen) {
                // to fix the mirror image
                videoRemote.classList.add("cam");

                if (localStorage.getItem('objectFit')) {
                    $(".cam").css('object-fit', localStorage.getItem('objectFit'));
                }
            }

            // Apply screen share layout on the receiving side
            if (isScreen) {
                const videosContainer = document.getElementById('videos');
                if (videosContainer) {
                    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                    if (isMobile) {
                        videosContainer.classList.add('mobile-screen-sharing');
                    } else {
                        videosContainer.classList.add('screen-sharing');
                    }

                    // Hide other participant videos so screen share is prominent
                    const participantVideos = videosContainer.querySelectorAll('.videoContainer:not(.screen-share-container):not(#selfContainer)');
                    participantVideos.forEach(v => {
                        v.style.display = 'none';
                    });

                    // Position self container as overlay
                    const selfContainer = videosContainer.querySelector('#selfContainer');
                    if (selfContainer) {
                        if (isMobile) {
                            selfContainer.style.display = 'block';
                            selfContainer.style.width = '100%';
                            selfContainer.style.height = '25vh';
                            selfContainer.style.maxWidth = '100%';
                            selfContainer.style.maxHeight = '25vh';
                            selfContainer.style.marginTop = '10px';
                            selfContainer.style.position = 'relative';
                        } else {
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
                        }
                    }
                }
            }

            state.layout();
        };

        connection.addEventListener('connectionstatechange', () => {
            if (connection.connectionState === 'connected') {
                if (Meeting.whiteboard.designer.pointsLength <= 0) {
                    setTimeout(function () {
                        utils.sendMessage(state.socket, { type: 'sync' });
                    }, 1000);
                }

                if (state.isModerator) {
                    utils.sendMessage(state.socket, {
                        type: 'currentTime',
                        currentTime: state.timer.getTimeValues().minutes * 60 + state.timer.getTimeValues().seconds,
                        fromSocketId: state.socket.id,
                        toSocketId: socketId,
                    });
                }
            }
        });
    }

    // handle when opponent leaves the meeting
    function handleLeave(data) {
        // If host leaves (network drop, refresh, cancel), keep meeting running.
        if (data.isModerator) {
            console.log("[Host Disconnected] Keeping meeting alive until host returns...");
            // Do nothing special here - just clean up their video.
        }

        let video = document.getElementById('video-' + data.fromSocketId);
        let container = document.getElementById('container-' + data.fromSocketId);
        if (video && container) {
            // Check if this was a screen share container before removing
            let wasScreenShare = container.classList.contains('screen-share-container');

            video.pause();
            video.srcObject = null;
            video.load();
            container.removeChild(video);
            videos.removeChild(container);

            // If a screen share just left, reset layout for all participants
            if (wasScreenShare || data.screen) {
                const videosContainer = document.getElementById('videos');
                if (videosContainer) {
                    videosContainer.classList.remove('screen-sharing', 'mobile-screen-sharing');

                    const participantVideos = videosContainer.querySelectorAll('.videoContainer');
                    participantVideos.forEach(v => {
                        v.style.display = 'block';
                        v.style.width = '';
                        v.style.height = '';
                        v.style.maxWidth = '';
                        v.style.maxHeight = '';
                        v.style.position = '';
                        v.style.top = '';
                        v.style.right = '';
                        v.style.zIndex = '';
                        v.style.borderRadius = '';
                        v.style.overflow = '';
                        v.style.boxShadow = '';
                        v.style.marginTop = '';
                        v.style.border = '';

                        const videoElement = v.querySelector('video');
                        if (videoElement) {
                            videoElement.style.width = '';
                            videoElement.style.height = '';
                            videoElement.style.objectFit = '';
                            videoElement.style.backgroundColor = '';
                        }
                    });
                }
            }

            state.layout();
        }

        let conn = state.connections[data.fromSocketId];
        if (conn) {
            conn.close();
            conn.onicecandidate = null;
            conn.ontrack = null;
            delete state.connections[data.fromSocketId];
        }

        let screenConn = state.screenConnections[data.fromSocketId];
        if (screenConn) {
            screenConn.close();
            screenConn.onicecandidate = null;
            screenConn.ontrack = null;
            delete state.screenConnections[data.fromSocketId];
        }

        if (state.isRecording) state.mixer.resetVideoStreams(Meeting.recording.getVideoStreams());

        delete state.usernames[data.fromSocketId];
        delete state.avatars[data.fromSocketId];
        $("#list-" + data.fromSocketId).remove();
        $('#showParticipantList')
            .addClass('number')
            .attr('data-content', Object.keys(state.usernames).length + 1);
    }
})();
