// (function () {
//     'use strict';

//     const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
//     const isOnIOS =
//         navigator.userAgent.match(/iPad/i) ||
//         navigator.userAgent.match(/iPhone/i);
//     const eventName = isOnIOS ? "pagehide" : "beforeunload";

//     const audioInputSelect = document.querySelector('select#audioSource');
//     const videoInputSelect = document.querySelector('select#videoSource');
//     const selectors = [audioInputSelect, videoInputSelect];

//     let socket;
//     let screenSocket;
//     let screenSocketId;
//     let constraints;
//     let localStream;
//     let meetingType;
//     let currentMeetingTime;
//     let layoutContainer = document.getElementById('videos');
//     let layout = initLayoutContainer(layoutContainer).layout;

//     let mixer;
//     let recorder;
//     let screenStream;
//     let speechEvents;
//     let mouseMoveTimer;
//     let displayFileUrl;
//     let resizeTimeout;
//     let messageCount = 0;
//     let recordingData = [];
//     let isRecording = false;
//     let connections = [];
//     let screenConnections = [];
//     let usernames = [];
//     let avatars = [];
//     let settings = {};
//     let configuration = {};
//     let allMuted = false;
//     let audioMuted = false;
//     let videoMuted = false;
//     let initiated = false;
//     let screenShared = false;
//     let whiteboardAdded = false;
//     let whiteboardVisible = false;
//     let waitingForChatGPT = false;
//     let isModerator = moderator;
//     let timer = new easytimer.Timer();
//     let notificationTone = new Audio('/sounds/notification.mp3');

//     // Fixed meeting state variables
//     let isHost = moderator; // Use the existing moderator variable as primary host indicator
//     let passwordVerified = false; 
//     let meetingStarted = false;

//     let designer = new CanvasDesigner();
//     designer.widgetHtmlURL = '/widget';
//     designer.widgetJsURL = 'js/widget.min.js';

//     //get the details
//     (function () {
//         $.post({
//             url: "/get-details",
//         })
//             .done(function (data) {
//                 data = JSON.parse(data);

//                 if (data.success) {
//                     settings = data.data;

//                     initializeSocket(settings.signalingURL);

//                     configuration = {
//                         iceServers: [{
//                             urls: settings.stunUrl,
//                         },
//                         {
//                             urls: settings.turnUrl,
//                             username: settings.turnUsername,
//                             credential: settings.turnPassword,
//                         },
//                         ],
//                     };
//                 } else {
//                     showError(languages.no_session);
//                 }
//             })
//             .catch(function () {
//                 showError(languages.no_session);
//             });
//     })();

//     //connect to the signaling server and add listeners
//    function initializeSocket(signalingURL) {
//     socket = io.connect(signalingURL);

//     //handle socket file event
//     socket.on("file", function (data) {
//         if ($(".chat-panel").is(":hidden")) {
//             $("#openChat").addClass("notify").attr('data-content', ++messageCount);
//             showOptions();
//             notificationTone.play();
//         }
//         appendFile(data.file, data.username, false);
//     });

//     //show the error message and disable the join button
//     socket.on("connect_error", function () {
//         $('#joinMeeting').attr('disabled', true);
//         $("#error").show();
//     });

//     //hide the error message and enable the join button
//     socket.on("connect", function () {
//         $('#joinMeeting').attr('disabled', false);
//         $("#error").hide();
//     });

//     //listen for socket message event and handle it
//     socket.on('message', function (data) {
//         data = JSON.parse(data);

//         switch (data.type) {
//             case 'join':
//                 // Server should provide these values based on user authentication
//                 const serverIsHost = data.isHost || false;
//                 const serverMeetingStarted = data.meetingStarted || false;
//                 const serverPasswordVerified = data.passwordVerified || false;
                
//                 // Update local state
//                 isHost = serverIsHost || isModerator;
//                 meetingStarted = serverMeetingStarted;
//                 passwordVerified = serverPasswordVerified;
                
//                 console.log('Join response:', { isHost, meetingStarted, passwordVerified, isModerator });

//                 if (isHost || isModerator) {
//                     if (!meetingStarted) {
//                         // Host needs to start the meeting
//                         showStartMeetingUI();
//                         showInfo("You are the host. Click 'Start Meeting' when ready.");
//                     } else {
//                         // Meeting already started, join as host
//                         joinMeetingAutomatically();
//                     }
//                 } else {
//                     if (meetingStarted && passwordVerified) {
//                         // Meeting started and password is correct, join automatically
//                         joinMeetingAutomatically();
//                     } else if (!meetingStarted) {
//                         // Meeting not started, wait for host
//                         showWaitingForHostMessage();
//                     } else {
//                         // Need permission from host (rare case)
//                         sendMessage(socket, {
//                             type: 'requestPermission',
//                             username: userInfo.username
//                         });
//                     }
//                 }
//                 break;

//             case 'meetingStarted':
//             case 'startMeeting':
//                 meetingStarted = true;
//                 console.log('Meeting started notification received');
                
//                 // If user has correct password or is host, join automatically
//                 if (passwordVerified || isHost || isModerator) {
//                     joinMeetingAutomatically();
//                 }
//                 break;

//             case 'offer':
//                 handleOffer(localStream, socket, data, false);
//                 break;

//             case 'answer':
//                 handleAnswer(data);
//                 break;

//             case 'candidate':
//                 handleCandidate(data);
//                 break;

//             case 'leave':
//                 handleLeave(data);
//                 break;

//             case 'checkMeetingResult':
//             case 'permissionResult':
//                 checkMeetingResult(data);
//                 break;

//             case 'meetingMessage':
//                 handlemeetingMessage(data);
//                 break;

//             case "fileMessage":
//                 handleFileMessage(data);
//                 break;

//             case 'permission':
//                 handlePermission(data);
//                 break;

//             case 'info':
//                 toastr.info(languages[data.message], "", {
//                     timeOut: 0,
//                     extendedTimeOut: 0,
//                 });
//                 break;

//             case 'kick':
//                 showInfo(languages.kicked);
//                 reload(0);
//                 break;

//             case 'whiteboard':
//                 handleWhiteboard(data.data);
//                 break;

//             case 'clearWhiteboard':
//                 designer.clearCanvas();
//                 designer.sync();
//                 break;

//             case 'raiseHand':
//                 showInfo(languages.hand_raised + ': ' + data.username);
//                 break;

//             case 'sync':
//                 designer.sync();
//                 break;

//             case 'currentTime':
//                 timer.stop();
//                 timer.start({
//                     precision: 'seconds',
//                     startValues: {
//                         seconds: data.currentTime,
//                     },
//                     target: {
//                         seconds: timeLimit * 60 - 60,
//                     },
//                 });
//                 break;

//             case 'recordingPermission':
//                 handleRecordingPermission(data);
//                 break;

//             case 'recordongPermissionResult':
//                 handleRecordingPermissionResult(data);
//                 break;

//             case 'screenSharePermission':
//                 handleScreenSharePermission(data);
//                 break;

//             case 'screenSharePermissionResult':
//                 handleScreenSharePermissionResult(data);
//                 break;

//             case 'recordingStarted':
//                 notificationTone.play();
//                 showInfo(languages.recording_started + ": " + data.username);
//                 break;

//             case "speaking":
//                 handleSpeaking(data);
//                 break;

//             case "chatGPTResponse":
//                 handleChatGPTMessage(data);
//                 break;

//             case 'mic-admin':
//                 handleMicAdmin(data.value);
//                 break;

//             case 'camera-admin':
//                 handleCameraAdmin(data.value);
//                 break;

//             case 'micToggled':
//                 handleMicToggled(data.fromSocketId, data.audioMuted);
//                 break;

//             case 'cameraToggled':
//                 handleCameraToggled(data.fromSocketId, data.videoMuted);
//                 break;

//             case 'muteAll':
//                 handleMuteAll(data.value);
//                 break;

//             case 'moderatorAssignment':
//                 handleChangeModerator(data.value);
//                 break;

//             case 'moderatorUpdated':
//                 handleModeratorUpdated(data.username, data.socketId);
//                 break;

//             case 'moderatorButtons':
//                 handleModeRatorButtons(data);
//                 break;
//         }
//     });

//     //get item from localStorage and set to html
//     videoQualitySelect.value = localStorage.getItem('videoQuality') || 'VGA';
//     username.value = localStorage.getItem('username');

//     setVideoPreview();
// }

//     //get media stream and set video preview, show the error if any
//     async function setVideoPreview() {
//         $("#toggleCameraPreview").addClass('disabled');
//         document.getElementById("overlay").style.display = "block";

//         //the room has space, get the media and initiate the meeting
//         constraints = {
//             audio: getAudioConstraints(),
//             video: getVideoConstraints(),
//         };

//         try {
//             document.getElementById("overlayText").innerText = languages.checking_mic_cam_permission;
//             setTimeout(() => {
//                 document.getElementById("overlayText").innerHTML = languages.click_allow;
//             }, 1000);

//             //get user media
//             localStream = await navigator.mediaDevices.getUserMedia(constraints);
//         } catch (e) {
//             //show an error if the media device is not available
//             $(".text-show").text(languages.no_device + e);
//             showError(languages.no_device + e.name);
//             if (e.name == "OverconstrainedError") $("#videoQualitySelect").val('VGA').trigger('change');
//             $("#toggleCameraPreview").removeClass('disabled');
//         }

//         document.getElementById("overlay").style.display = "none";

//         if (localStream) {
//             previewVideo.srcObject = localStream;
//             previewVideo.style.zIndex = 5;
//             $(".text-show").text();
//             $("#toggleCameraPreview").html('<i class="fa fa-video"></i>').removeClass('disabled');
//             meetingType = 'video';
//         }
//     }

//     //toggle video preview
//     $("#toggleCameraPreview").on('click', function () {
//         if (localStream && localStream.getVideoTracks().length) {
//             localStream.getVideoTracks().forEach((track) => track.stop());
//             localStream.removeTrack(localStream.getVideoTracks()[0]);
//             previewVideo.srcObject = null;
//             previewVideo.style.zIndex = 0;
//             $("#toggleCameraPreview").html('<i class="fa fa-video-slash"></i>');
//             meetingType = 'audio';
//         } else {
//             meetingType = '';
//             setVideoPreview();
//         }
//     });

//     //listen for timer update event and display during the meeting
//     timer.addEventListener('secondsUpdated', function () {
//         currentMeetingTime = timer.getTimeValues().minutes * 60 + timer.getTimeValues().seconds;
//         $('#timer').text(getCurrentTime());
//     });

//     //start the timer for last one minute and end the meeting after that
//     timer.addEventListener('targetAchieved', function () {
//         $('#timer').css('color', 'red');
//         timer.stop();
//         timer.start({
//             precision: 'seconds',
//             startValues: {
//                 seconds: currentMeetingTime,
//             },
//         });
//         showOptions();
//         showInfo(languages.meeting_ending);

//         setTimeout(function () {
//             showInfo(languages.meeting_ended);
//             reload(1);
//         }, 60 * 1000);
//     });

//     //ajax call to check password, continue to meeting if valid
//     $('#passwordCheck').on('submit', function (e) {
//         e.preventDefault();

//         if (!localStream) return;
//         $('#joinMeeting').attr('disabled', true);

//         //show an error if the signaling server is not connected
//         if (!socket.connected) {
//             showError(languages.cant_connect);
//             $('#joinMeeting').attr('disabled', false);
//             return;
//         }

//         if (passwordRequired) {
//             $.ajax({
//                 url: '/check-meeting-password',
//                 data: $(this).serialize(),
//                 type: 'post',
//             })
//                 .done(function (data) {
//                     data = JSON.parse(data);
//                     $('#joinMeeting').attr('disabled', false);

//                     if (data.success) {
//                         passwordVerified = true; // Set password as verified
//                         continueToMeeting();
//                     } else {
//                         showError(languages.invalid_password);
//                     }
//                 })
//                 .catch(function () {
//                     showError();
//                     $('#joinMeeting').attr('disabled', false);
//                 });
//         } else {
//             passwordVerified = true; // No password required
//             continueToMeeting();
//         }
//     });

//     //set details into localStorage and notify server to check meeting status
//     function continueToMeeting() {
//         //set username
//         userInfo.username = username.value || htmlEscape(settings.defaultUsername);
//         localStorage.setItem('username', userInfo.username);

//         //send join request with proper user identification
//         sendMessage(socket, {
//             type: 'join',
//             username: userInfo.username,
//             meetingId: userInfo.meetingId,
//             moderator: isModerator,
//             authMode: settings.authMode,
//             moderatorRights: settings.moderatorRights,
//             userLimit: userLimit,
//             passwordProvided: passwordRequired ? document.querySelector('#meetingPassword').value : null,
//             passwordVerified: passwordVerified
//         });
//     }

//     //stringify the data and send it to opponent
//     function sendMessage(socket, data) {
//         socket.emit('message', JSON.stringify(data));
//     }

//     //get current meeting time in readable format
//     function getCurrentTime() {
//         return timer.getTimeValues().toString(['hours', 'minutes', 'seconds']);
//     }

//     //reload after a specific seconds
//     function reload(seconds) {
//         setTimeout(function () {
//             if (settings.endURL == 'null') {
//                 window.location.reload();
//             } else {
//                 window.location.href = settings.endURL;
//             }
//         }, seconds * 1000);
//     }

//     //check meeting request
//     async function checkMeetingResult(data) {

//         //clear the info toastr
//         toastr.clear();

//         //init the meeting if result is true and media is available
//         if (data.result && localStream) {
//             if (isModerator) $("#muteAll").show();

//             init();

//             if (data.chatBotName == 'DeepSeek') {
//                 $('.chatgpt-header').html(`
//                     <img src="/images/deepseek-logo.png" width="30" alt="DeepSeek" />
//                     DeepSeek
//                     <i class="fas fa-times close-chatgpt-panel"></i>
//                 `);

//                 $('#chatGPTmessageInput').attr("placeholder", "Message DeepSeek");
//             }

//             if (data.allMuted) handleMuteAll(true);
//         } else {
//             //there is an error, show it to the user
//             showError(languages[data.message]);
//             $('#joinMeeting').attr('disabled', false);
//         }
//     }

//     // Function to show start meeting UI for host
//     function showStartMeetingUI() {
//         // Add a start meeting button to the UI
//         if (!document.querySelector('#startMeetingBtn')) {
//             const startBtn = document.createElement('button');
//             startBtn.id = 'startMeetingBtn';
//             startBtn.className = 'btn btn-success btn-lg';
//             startBtn.innerHTML = '<i class="fas fa-play"></i> Start Meeting';
//             startBtn.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; padding: 15px 30px; font-size: 18px;';
            
//             startBtn.onclick = function() {
//                 meetingStarted = true;
//                 this.remove();
                
//                 // Notify all participants that meeting has started
//                 sendMessage(socket, {
//                     type: 'startMeeting',
//                     fromSocketId: socket.id
//                 });
                
//                 // Join the meeting as host
//                 joinMeetingAutomatically();
//             };
            
//             document.body.appendChild(startBtn);
//         }
//     }

//     // Enhanced waiting screen
//     function showWaitingForHostMessage() {
//         // Create overlay div
//         const overlay = document.createElement('div');
//         overlay.id = 'waitingOverlay';
//         overlay.innerHTML = `
//             <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;font-family:Arial;background:#f5f5f5;">
//                 <div class="spinner"></div>
//                 <h2 style="margin-top:20px;color:#333;">Waiting for Host</h2>
//                 <p style="color:#666;text-align:center;">The meeting host hasn't started the session yet.<br>You'll be joined automatically once it begins.</p>
//                 <div style="margin-top:20px;">
//                     <small style="color:#999;">Please keep this tab open</small>
//                 </div>
//             </div>
//             <style>
//                 .spinner {
//                     width: 60px;
//                     height: 60px;
//                     border: 6px solid #e3e3e3;
//                     border-top: 6px solid #007bff;
//                     border-radius: 50%;
//                     animation: spin 1s linear infinite;
//                 }
//                 @keyframes spin {
//                     to { transform: rotate(360deg); }
//                 }
//                 #waitingOverlay {
//                     position: fixed;
//                     top: 0;
//                     left: 0;
//                     width: 100%;
//                     height: 100%;
//                     background: #f5f5f5;
//                     z-index: 9999;
//                 }
//             </style>
//         `;
        
//         document.body.appendChild(overlay);

//         // Listen for meeting start notification
//         const onMeetingStart = function(dataRaw) {
//             let dataMsg = JSON.parse(dataRaw);
            
//             if (dataMsg.type === 'meetingStarted' || dataMsg.type === 'startMeeting') {
//                 meetingStarted = true;
//                 console.log('Meeting started notification received');

//                 // Remove this listener after receiving the message
//                 socket.off('message', onMeetingStart);

//                 // Remove waiting overlay
//                 const waitingOverlay = document.getElementById('waitingOverlay');
//                 if (waitingOverlay) waitingOverlay.remove();

//                 // Join the meeting automatically
//                 joinMeetingAutomatically();
//             }
//         };
        
//         socket.on('message', onMeetingStart);
//     }

//     // Enhanced auto-join function
//     function joinMeetingAutomatically() {
//         console.log('Auto-joining meeting...');
        
//         // Clear any waiting UI
//         const startBtn = document.querySelector('#startMeetingBtn');
//         if (startBtn) startBtn.remove();
        
//         const waitingOverlay = document.getElementById('waitingOverlay');
//         if (waitingOverlay) waitingOverlay.remove();
        
//         // Continue with normal join process
//         sendMessage(socket, {
//             type: 'joinMeeting',
//             username: userInfo.username,
//             avatar: userInfo.avatar
//         });
//     }

//     // Modified handlePermission function with proper host detection
//     function handlePermission(data) {
//         console.log('Permission request received:', { isHost, meetingStarted, passwordVerified, isModerator });
        
//         // If the user is the actual host (moderator), always approve and start meeting if needed
//         if (isModerator || isHost) {
//             console.log('User is host/moderator - auto approving');
            
//             // If meeting hasn't started yet, start it now
//             if (!meetingStarted) {
//                 meetingStarted = true;
//                 // Notify all participants that meeting has started
//                 sendMessage(socket, {
//                     type: 'startMeeting',
//                     fromSocketId: socket.id
//                 });
//             }
            
//             // Auto-approve the join request
//             sendMessage(socket, {
//                 type: 'permissionResult',
//                 result: true,
//                 toSocketId: data.fromSocketId,
//                 allMuted: allMuted
//             });
//             return;
//         }
        
//         // If user has verified password and meeting is started → join automatically
//         if (passwordVerified && meetingStarted) {
//             console.log('Password verified and meeting started - auto approving');
//             sendMessage(socket, {
//                 type: 'permissionResult',
//                 result: true,
//                 toSocketId: data.fromSocketId,
//                 allMuted: allMuted
//             });
//             return;
//         }

//         // If the meeting hasn't started yet → show waiting page
//         if (!meetingStarted) {
//             console.log('Meeting not started - showing waiting message');
//             showWaitingForHostMessage();
//             return;
//         }

//         // Otherwise → ask host for approval (this should rarely happen with proper password flow)
//         console.log('Requesting host approval');
//         notificationTone.play();
//         toastr.info(
//             '<br><button type="button" class="btn btn-primary btn-sm clear approve" data-from="' +
//             data.fromSocketId +
//             '">' + languages.approve + '</button><button type="button" class="btn btn-warning btn-sm clear ml-2 decline" data-from="' +
//             data.fromSocketId +
//             '">' + languages.decline + '</button>',
//             languages.request_join_meeting + data.username, {
//             tapToDismiss: false,
//             timeOut: 0,
//             extendedTimeOut: 0,
//             newestOnTop: false,
//         });
//     }

//     // Notify participant about the request approval
//     $(document).on('click', '.approve', function () {
//         $(this).closest('.toast').remove();
//         sendMessage(socket, {
//             type: 'permissionResult',
//             result: true,
//             toSocketId: $(this).data('from'),
//             allMuted: allMuted
//         });
//     });

//     // Notify participant about the request rejection
//     $(document).on('click', '.decline', function () {
//         $(this).closest('.toast').remove();
//         sendMessage(socket, {
//             type: 'permissionResult',
//             result: false,
//             toSocketId: $(this).data('from'),
//             message: 'request_declined',
//         });
//     });


//     //notify the moderator for new recording request
//     function handleRecordingPermission(data) {
//         notificationTone.play();
//         toastr.info(
//             '<br><button type="button" class="btn btn-primary btn-sm clear approveRecording" data-from="' +
//             data.fromSocketId +
//             '">' + languages.approve + '</button><button type="button" class="btn btn-warning btn-sm clear ml-2 declineRecording" data-from="' +
//             data.fromSocketId +
//             '">' + languages.decline + '</button>',
//             languages.request_record_meeting + data.username, {
//             tapToDismiss: false,
//             timeOut: 0,
//             extendedTimeOut: 0,
//             newestOnTop: false,
//         }
//         );
//     }

//     //notify participant about the recording request approval
//     $(document).on('click', '.approveRecording', function () {
//         $(this).closest('.toast').remove();
//         sendMessage(socket, {
//             type: 'recordongPermissionResult',
//             result: true,
//             toSocketId: $(this).data('from'),
//         });
//     });

//     //notify participant about the recording request rejection
//     $(document).on('click', '.declineRecording', function () {
//         $(this).closest('.toast').remove();
//         sendMessage(socket, {
//             type: 'recordongPermissionResult',
//             result: false,
//             toSocketId: $(this).data('from'),
//             message: languages.request_declined,
//         });
//     });

//     //start the recording or notify the user about the rejection
//     function handleRecordingPermissionResult(data) {
//         $("#recording").attr('disabled', false);
//         toastr.clear();

//         if (data.result) {
//             startRecording();
//         } else {
//             showInfo(languages.record_request_declined);
//         }
//     }

//     //notify the moderator for new screen share request
//     function handleScreenSharePermission(data) {
//         notificationTone.play();
//         toastr.info(
//             '<br><button type="button" class="btn btn-primary btn-sm clear approveScreenShare" data-from="' +
//             data.fromSocketId +
//             '">' + languages.approve + '</button><button type="button" class="btn btn-warning btn-sm clear ml-2 declineScreenShare" data-from="' +
//             data.fromSocketId +
//             '">' + languages.decline + '</button>',
//             languages.request_screenshare + data.username, {
//             tapToDismiss: false,
//             timeOut: 0,
//             extendedTimeOut: 0,
//             newestOnTop: false,
//         }
//         );
//     }

//     //notify participant about the recording request approval
//     $(document).on('click', '.approveScreenShare', function () {
//         $(this).closest('.toast').remove();

//         sendMessage(socket, {
//             type: 'screenSharePermissionResult',
//             result: true,
//             toSocketId: $(this).data('from'),
//         });
//     });

//     //notify participant about the recording request rejection
//     $(document).on('click', '.declineScreenShare', function () {
//         $(this).closest('.toast').remove();

//         sendMessage(socket, {
//             type: 'screenSharePermissionResult',
//             result: false,
//             toSocketId: $(this).data('from'),
//             message: languages.request_declined,
//         });
//     });

//     //start the screen share or notify the user about the rejection
//     function handleScreenSharePermissionResult(data) {
//         $("#screenShare").attr('disabled', false);

//         if (data.result) {
//             startScreenSharing();
//         } else {
//             showInfo(languages.screenshare_request_declined);
//         }
//     }

//     //initiate meeting
//     function init() {
//         $('.meeting-details, .navbar, footer').hide();
//         $('.meeting-section').show();

//         //set object fit property from local storage if available
//         if (localStorage.getItem('objectFit')) {
//             $(".cam").css('object-fit', localStorage.getItem('objectFit'));
//             $("#videoObjectFit").val(localStorage.getItem('objectFit'))
//         }

//         localVideo.srcObject = localStream;
//         previewVideo.srcObject = null;
//         layout();
//         if (!localStream.getVideoTracks().length) $('.user-initial').text(userInfo.username[0]).css('background', getRandomColor());
//         sendMessage(socket, {
//             type: 'join',
//             username: userInfo.username,
//             meetingId: userInfo.meetingId,
//             isModerator,
//             screen: false,
//             avatar: userInfo.avatar,
//             mic: audioMuted,
//             camera: videoMuted
//         });

//         //start with a time limit for limited time meeting
//         timer.start({ precision: 'seconds', startValues: { seconds: 0 }, target: { seconds: timeLimit * 60 - 60 } });
//         manageOptions();
//         if (isMobile && meetingType === 'video') $('#toggleCam').show();
//         if (!isMobile) $('#screenShare').show();
//         initKeyShortcuts();
//         if (!localStorage.getItem('tripDone')) {
//             setTimeout(function () {
//                 showInfo(languages.double_click);
//                 showInfo(languages.single_click);
//                 localStorage.setItem('tripDone', true);
//             }, 3000);
//         }
//         $('#showParticipantList').addClass('number').attr('data-content', 1);

//         if (!audioMuted) {
//             initHark();
//         }

//         initiated = true;

//     }

//     //active speaker: speech detection - hark
//     function initHark() {
//         speechEvents = hark(localStream, {});
//         speechEvents.on("speaking", sendSpeakingIndication);
//         speechEvents.on("stopped_speaking", sendSpeakingIndication);
//     }

//     //send speaking indication to the server
//     function sendSpeakingIndication() {
//         document.getElementById("selfContainer").classList.toggle('speaking-shadow');

//         sendMessage(socket, {
//             type: "speaking",
//             fromSocketId: socket.id,
//         });
//     }

//     //stop hark
//     function stopHark() {
//         if (speechEvents) {
//             speechEvents.stop();
//             // sendSpeakingIndication(); //uncomment if needed later
//         }
//     }

//     //handle speaking
//     function handleSpeaking(data) {
//         document.getElementById("container-" + data.fromSocketId).classList.toggle('speaking-shadow');
//     }

//     //hide/show certain meeting related details
//     function manageOptions() {
//         $('.meeting-options').show();
//         $('#meetingIdInfo').text(meetingTitle);
//         localStorage.setItem('videoQuality', videoQualitySelect.value);

//         if (meetingType === 'video') {
//             $('#toggleVideo').show();
//         }

//         setTimeout(function () {
//             hideOptions();
//             $('.local-user-name, .remote-user-name, .kick').hide();
//         }, 3000);

//         $('body').on('mousemove', function () {
//             showOptions();
//         });
//     }

//     //hide meeting ID and options
//     function hideOptions() {
//         $('.meeting-options, .meeting-info').hide();
//     }

//     //show meeting ID and options
//     function showOptions() {
//         $('.meeting-options, .meeting-info').show();

//         if (mouseMoveTimer) {
//             clearTimeout(mouseMoveTimer);
//         }

//         mouseMoveTimer = setTimeout(function () {
//             hideOptions();
//         }, 3000);
//     }

//     $(document).on('mouseover', '.videoContainer', function () {
//         $(this).find('span, button').show();
//     });

//     $(document).on('mouseout', '.videoContainer', function () {
//         $(this).find('span, button').hide();
//     });

//     //create and send an offer for newly joined user
//     function handleJoin(stream, currentSocket, data) {
//         if (screenSocket && data.socketId == screenSocketId) return;

//         usernames[data.socketId] = data.username;
//         avatars[data.socketId] = data.avatar;

//         //stop screen sharing
//         if (data.screen && settings.limitedScreenShare == 'enabled' && screenShared) stopScreenSharing();

//         //initialize a new connection
//         let connection = new RTCPeerConnection(configuration);
//         data.screen ? screenConnections[data.socketId] = connection : connections[data.socketId] = connection;

//         setupListeners(stream, currentSocket, connection, data.socketId, data.screen, data.isModerator);

//         connection
//             .createOffer({
//                 offerToReceiveVideo: true,
//             })
//             .then(function (offer) {
//                 return connection.setLocalDescription(offer);
//             })
//             .then(function () {
//                 sendMessage(currentSocket, {
//                     type: 'offer',
//                     sdp: connection.localDescription,
//                     username: userInfo.username + (data.screen ? '-screen' : ''),
//                     fromSocketId: currentSocket.id,
//                     toSocketId: data.socketId,
//                     isModerator,
//                     avatar: userInfo.avatar
//                 });
//             })
//             .catch(function (e) {
//                 console.log(languages.error_message, e);
//             });
//     }

//     //handle offer from initiator, create and send an answer
//     function handleOffer(stream, currentSocket, data, isScreen) {
//         usernames[data.fromSocketId] = data.username;
//         avatars[data.fromSocketId] = data.avatar;

//         //initialize a new connection
//         let connection = new RTCPeerConnection(configuration);
//         isScreen ? screenConnections[data.fromSocketId] = connection : connections[data.fromSocketId] = connection;

//         connection.setRemoteDescription(data.sdp);
//         setupListeners(stream, currentSocket, connection, data.fromSocketId, isScreen, data.isModerator);

//         connection
//             .createAnswer()
//             .then(function (answer) {
//                 setDescriptionAndSendAnswer(currentSocket, answer, data.fromSocketId, isScreen);
//             })
//             .catch(function (e) {
//                 console.log(e);
//             });
//     }

//     //set local description and send the answer
//     function setDescriptionAndSendAnswer(currentSocket, answer, fromSocketId, isScreen) {
//         let currentConnection = isScreen ? screenConnections[fromSocketId] : connections[fromSocketId];

//         currentConnection.setLocalDescription(answer);
//         sendMessage(currentSocket, {
//             type: 'answer',
//             answer: answer,
//             fromSocketId: currentSocket.id,
//             toSocketId: fromSocketId,
//             screen: isScreen
//         });
//     }

//     //handle answer and set remote description
//     function handleAnswer(data) {
//         let currentConnection = data.screen ? screenConnections[data.fromSocketId] : connections[data.fromSocketId];
//         currentConnection.setRemoteDescription(data.answer);
//     }

//     //handle candidate and add ice candidate
//     function handleCandidate(data) {
//         let currentConnection = data.screen ? screenConnections[data.fromSocketId] : connections[data.fromSocketId];

//         if (data.candidate && currentConnection) {
//             currentConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
//         }
//     }

//     //change the video size on window resize
//     window.onresize = function () {
//         clearTimeout(resizeTimeout);
//         resizeTimeout = setTimeout(function () {
//             layout();
//         }, 20);
//     };

//     //add local track to the connection,
//     //manage remote track,
//     //ice candidate and state change event
//     function setupListeners(stream, currentSocket, connection, socketId, isScreen, userIsModerator) {
//         stream.getTracks().forEach((track) => connection.addTrack(track, localStream));

//         connection.onicecandidate = (event) => {
//             if (event.candidate) {
//                 sendMessage(currentSocket, {
//                     type: 'candidate',
//                     candidate: event.candidate,
//                     fromSocketId: currentSocket.id,
//                     toSocketId: socketId,
//                     screen: isScreen
//                 });
//             }
//         };

//         connection.ontrack = (event) => {
//             if (document.getElementById('video-' + socketId)) {
//                 return;
//             }

//             if (isRecording) mixer.appendStreams(event.streams[0]);

//             let videoRemote = document.createElement('video');
//             videoRemote.id = 'video-' + socketId;
//             videoRemote.setAttribute('autoplay', '');
//             videoRemote.setAttribute('playsinline', '');
//             videoRemote.srcObject = event.streams[0];

//             videoRemote.onloadedmetadata = function (e) {
//                 videoRemote.play();
//             };

//             let containerDiv = document.createElement('div');
//             containerDiv.id = 'container-' + socketId;
//             containerDiv.className = 'videoContainer' + (isScreen ? ' OT_big' : '');

//             let containerText = document.createElement('span');
//             containerText.className = 'remote-user-name';
//             containerText.innerHTML = usernames[socketId] + " <i class='fas fa-crown moderator-icon' title='" + languages.moderator + "' " + (!userIsModerator ? "style='display: none'" : "") + "></i>";

//             if (avatars[socketId]) {
//                 let containerAvatar = document.createElement('img');
//                 containerAvatar.className = 'user-initial';
//                 containerAvatar.src = '/storage/avatars/' + avatars[socketId];
//                 containerDiv.appendChild(containerAvatar);
//             } else {
//                 let containerInitial = document.createElement('p');
//                 containerInitial.className = 'user-initial';
//                 containerInitial.innerText = usernames[socketId][0];
//                 containerInitial.style.background = getRandomColor();
//                 containerDiv.appendChild(containerInitial);
//             }

//             if (!isScreen && isModerator && settings.moderatorRights == "enabled") {
//                 const isVideoMeeting = event.streams[0].getVideoTracks().length;
//                 addModeratorButtons(containerDiv, false, !isVideoMeeting, socketId, isVideoMeeting); //mic is set to false because it will never be muted initially
//             }

//             const moderatorIcon = " <i class='fas fa-crown moderator-icon' title='" + languages.moderator + "' " + (!userIsModerator ? "style='display: none'" : "") + " ></i>";

//             $("#participantListBody").append("<tr id='list-" + socketId + "'><th scope='row'></th><td id='listId-" + socketId + "'>" + usernames[socketId] + moderatorIcon + "</td></tr>");
//             $('#showParticipantList').addClass('number').attr('data-content', Object.keys(usernames).length + 1);

//             containerDiv.appendChild(videoRemote);
//             containerDiv.appendChild(containerText);
//             videos.appendChild(containerDiv);

//             if (!isScreen) {
//                 //to fix the mirror image
//                 videoRemote.classList.add("cam");

//                 //set object fit property from local storage if available
//                 if (localStorage.getItem('objectFit')) {
//                     $(".cam").css('object-fit', localStorage.getItem('objectFit'));
//                 }
//             }

//             layout();
//         };

//         connection.addEventListener('connectionstatechange', () => {
//             if (connection.connectionState === 'connected') {
//                 if (designer.pointsLength <= 0) {
//                     setTimeout(function () {
//                         sendMessage(socket, {
//                             type: 'sync'
//                         });
//                     }, 1000);
//                 }

//                 if (isModerator) {
//                     sendMessage(socket, {
//                         type: 'currentTime',
//                         currentTime: timer.getTimeValues().minutes * 60 + timer.getTimeValues().seconds,
//                         fromSocketId: socket.id,
//                         toSocketId: socketId,
//                     });
//                 }
//             }
//         });
//     }

//     //kick the participant out of the meeting
//     $(document).on('click', '.kick', function () {
//         if (confirm(languages.confirmation_kick)) {
//             $(this).attr('disabled', true);
//             sendMessage(socket, {
//                 type: 'kick',
//                 toSocketId: $(this).data('id'),
//             });
//         }
//     });

//     //manage users mic
//     $(document).on('click', '.mic-admin', function () {
//         if ($(this).data('muted')) {
//             //unmute mic
//             $(this).html('<i class="fa fa-microphone"></i>').data('muted', false);

//             sendMessage(socket, {
//                 type: 'mic-admin',
//                 toSocketId: $(this).data('id'),
//                 value: false
//             });
//         } else {
//             //mute mic
//             $(this).html('<i class="fa fa-microphone-slash"></i>').data('muted', true);

//             sendMessage(socket, {
//                 type: 'mic-admin',
//                 toSocketId: $(this).data('id'),
//                 value: true
//             });
//         }
//     });

//     //manage users camera
//     $(document).on('click', '.camera-admin', function () {
//         if ($(this).data('muted')) {
//             //unmute camera
//             $(this).html('<i class="fa fa-video"></i>').data('muted', false);

//             sendMessage(socket, {
//                 type: 'camera-admin',
//                 toSocketId: $(this).data('id'),
//                 value: false
//             });
//         } else {
//             //mute camera
//             $(this).html('<i class="fa fa-video-slash"></i>').data('muted', true);

//             sendMessage(socket, {
//                 type: 'camera-admin',
//                 toSocketId: $(this).data('id'),
//                 value: true
//             });
//         }
//     });

//     //handle mic admin event
//     function handleMicAdmin(value) {
//         audioMuted = !value;
//         manageMic(true);
//     }

//     //handle camera admin event
//     function handleCameraAdmin(value) {
//         videoMuted = !value;
//         manageCamera(true);
//     }

//     //handle mic toggle event
//     function handleMicToggled(socketId, value) {
//         if (value) {
//             $('#container-' + socketId + ' .mic-admin').html('<i class="fa fa-microphone-slash"></i>').data('muted', true);
//         } else {
//             $('#container-' + socketId + ' .mic-admin').html('<i class="fa fa-microphone"></i>').data('muted', false);
//         }
//     }

//     //handle camera toggle event
//     function handleCameraToggled(socketId, value) {
//         if (value) {
//             $('#container-' + socketId + ' .camera-admin').html('<i class="fa fa-video-slash"></i>').data('muted', true);
//         } else {
//             $('#container-' + socketId + ' .camera-admin').html('<i class="fa fa-video"></i>').data('muted', false);
//         }
//     }

//     //manage make moderator
//     $(document).on('click', '.make-moderator', function () {
//         if (!confirm(languages.moderator_confirm)) return;

//         sendMessage(socket, {
//             type: 'moderatorAssignment',
//             toSocketId: $(this).data('id'),
//             meetingId: userInfo.meetingId,
//         });
//     });

//     //handle change moderator
//     function handleChangeModerator() {
//         isModerator = true;
//         showSuccess(languages.you_moderator);

//         sendMessage(socket, {
//             type: 'moderatorUpdated',
//             meetingId: userInfo.meetingId,
//             username: userInfo.username,
//             socketId: socket.id
//         });

//         $("#muteAll").html(allMuted ? '<i class="fa fa-users-slash"></i>' : '<i class="fa fa-users"></i>').show();

//         $("#toggleMic, #toggleVideo").attr('disabled', false);
//         $(".moderator-icon").hide();
//         $(".local-user-name .moderator-icon, #participantListBody td:first .moderator-icon").show();
//     }

//     //handle moderator updated
//     function handleModeratorUpdated(username, socketId) {
//         showSuccess(languages.moderator_updated + username);
//         if (isModerator) {
//             isModerator = false;
//             $('[id^=container-]').find('.meeting-option').remove(); //remove moderator action buttons
//             $("#muteAll").hide();
//         }

//         sendMessage(socket, {
//             type: 'moderatorButtons',
//             meetingId: userInfo.meetingId,
//             toSocketId: socketId,
//             fromSocketId: socket.id,
//             audioMuted,
//             videoMuted,
//             meetingType
//         });

//         $(".moderator-icon").hide();
//         $("#container-" + socketId + " .moderator-icon, #listId-" + socketId + " .moderator-icon").show();
//     }

//     //handle moderator buttons
//     function handleModeRatorButtons(data) {
//         const containerDiv = document.getElementById("container-" + data.fromSocketId);
//         addModeratorButtons(containerDiv, data.audioMuted, data.videoMuted, data.fromSocketId, data.meetingType == 'video');
//     }

//     //add moderator buttons
//     function addModeratorButtons(containerDiv, mic, camera, socketId, isVideoMeeting) {
//         //append kick button
//         let kickButton = document.createElement('button');
//         kickButton.className = 'btn meeting-option kick';
//         kickButton.innerHTML = '<i class="fa fa-ban"></i>';
//         kickButton.setAttribute('data-id', socketId);
//         kickButton.setAttribute('title', languages.kick_user);
//         containerDiv.appendChild(kickButton);

//         //append mic button
//         let micButton = document.createElement('button');
//         micButton.className = 'btn meeting-option mic-admin';
//         micButton.innerHTML = (mic || allMuted) ? '<i class="fa fa-microphone-slash"></i>' : '<i class="fa fa-microphone"></i>';
//         micButton.setAttribute('data-id', socketId);
//         micButton.setAttribute('data-muted', mic || allMuted);
//         micButton.setAttribute('title', languages.toggleMic);
//         containerDiv.appendChild(micButton);

//         if (isVideoMeeting) {
//             //append camera button
//             let cameraButton = document.createElement('button');
//             cameraButton.className = 'btn meeting-option camera-admin';
//             cameraButton.innerHTML = camera ? '<i class="fa fa-video-slash"></i>' : '<i class="fa fa-video"></i>';
//             cameraButton.setAttribute('data-id', socketId);
//             cameraButton.setAttribute('data-muted', camera);
//             cameraButton.setAttribute('title', languages.toggleCamera);
//             containerDiv.appendChild(cameraButton);
//         }

//         //append moderator button
//         let moderatorButton = document.createElement('button');
//         moderatorButton.className = 'btn meeting-option make-moderator';
//         moderatorButton.innerHTML = '<i class="fas fa-crown"></i>';
//         moderatorButton.setAttribute('data-id', socketId);
//         moderatorButton.setAttribute('title', languages.make_moderator);
//         containerDiv.appendChild(moderatorButton);
//     }

//     //handle when opponent leaves the meeting
//     function handleLeave(data) {
//         if (!data.screen && data.isModerator) {
//             reload(1);
//         }

//         let video = document.getElementById('video-' + data.fromSocketId);
//         let container = document.getElementById('container-' + data.fromSocketId);

//         if (video && container) {
//             video.pause();
//             video.srcObject = null;
//             video.load();
//             container.removeChild(video);
//             videos.removeChild(container);
//             layout();
//         }

//         let currentConnection = connections[data.fromSocketId] || screenConnections[data.fromSocketId];

//         if (currentConnection) {
//             currentConnection.close();
//             currentConnection.onicecandidate = null;
//             currentConnection.ontrack = null;
//             delete connections[data.fromSocketId];
//         }

//         if (isRecording) mixer.resetVideoStreams(getVideoStreams());
//         delete usernames[data.fromSocketId];
//         delete avatars[data.fromSocketId];
//         $("#list-" + data.fromSocketId).remove();
//         $('#showParticipantList').addClass('number').attr('data-content', Object.keys(usernames).length + 1);
//     }

//     //mute/unmute local video
//     $(document).on('click', '#toggleVideo', function () {
//         console.log("========== TOGGLE VIDEO CLICK A ==========");
// console.log("videoMuted =", videoMuted);
// console.log("localStream =", localStream);

// if (localStream) {
//     console.log("Video tracks countA =", localStream.getVideoTracks().length);
//     console.log("Audio tracks countA =", localStream.getAudioTracks().length);

//     if (localStream.getVideoTracks()[0]) {
//         console.log("TrackA =", localStream.getVideoTracks()[0]);
//         console.log("TrackA enabled =", localStream.getVideoTracks()[0].enabled);
//         console.log("TrackA readyState =", localStream.getVideoTracks()[0].readyState);
//     }
// }
//         //notify the moderator regarding the change
//         if (!isModerator && settings.moderatorRights == "enabled") {
//             sendMessage(socket, {
//                 type: 'cameraToggled',
//                 videoMuted: !videoMuted,
//                 meetingId: userInfo.meetingId
//             })
//         }

//         manageCamera(false);
//     });

//     //manage camera
//     async function manageCamera(byModerator) {
//         if (isModerator) $("#toggleVideo").attr('disabled', true);

//         if (videoMuted) {
//             localStream.getVideoTracks().forEach((track) => (track.enabled = true));
//             $("#toggleVideo").html('<i class="fa fa-video"></i>');
//             videoMuted = false;
//             showSuccess(byModerator ? languages.camera_on_moderator : languages.camera_on);
//         } else {
//             localStream.getVideoTracks().forEach((track) => (track.enabled = false));
//             $("#toggleVideo").html('<i class="fa fa-video-slash"></i>');
//             videoMuted = true;
//             showSuccess(byModerator ? languages.camera_off_moderator : languages.camera_off);
//         }

//         if (isModerator) $("#toggleVideo").attr('disabled', false);
//     }

//     //mute/unmute local audio
//     $(document).on('click', '#toggleMic', function () {
//         //notify the moderator regarding the change
//         if (!isModerator && settings.moderatorRights == "enabled") {
//             sendMessage(socket, {
//                 type: 'micToggled',
//                 audioMuted: !audioMuted,
//                 meetingId: userInfo.meetingId
//             })
//         }

//         manageMic(false);
//     });

//     //manage mic
//     async function manageMic(byModerator) {
//         if (isModerator) $("#toggleMic").attr('disabled', true);

//         if (audioMuted) {
//             localStream.getAudioTracks().forEach((track) => (track.enabled = true));
//             $('#toggleMic').html('<i class="fa fa-microphone"></i>');
//             audioMuted = false;
//             showSuccess(byModerator ? languages.mic_unmuted_moderator : languages.mic_unmute);

//             initHark();
//         } else {
//             localStream.getAudioTracks().forEach((track) => (track.enabled = false));
//             $('#toggleMic').html('<i class="fa fa-microphone-slash"></i>');
//             audioMuted = true;
//             showSuccess(byModerator ? languages.mic_muted_moderator : languages.mic_mute);

//             stopHark();
//         }

//         if (isModerator) $("#toggleMic").attr('disabled', false);
//     }

//     //leave the meeting
//     $(document).on('click', '#leave', function () {
//         if (!confirm(languages.confirmation)) return;

//         showError(languages.meeting_ended);
//         reload(0);
//     });

//     //warn the user if he tries to leave the page during the meeting
//     window.addEventListener(eventName, function () {

//         if (isModerator && initiated) {
//             //call delete folder API
//             let form = new FormData();
//             form.append("_token", $("[name=csrf-token]").attr("content"));
//             form.append("meetingId", userInfo.meetingId);
//             navigator.sendBeacon("/delete-meeting-files", form);
//         }

//         socket.close();
//         Object.keys(connections).forEach((key) => {
//             connections[key].close();
//             let video = document.getElementById('video-' + key);
//             video.pause();
//             video.srcObject = null;
//             video.load();
//             video.parentNode.removeChild(video);
//         });

//         if (isRecording) stopRecording();
//     });

//     //enter into bigger video mode with double click on video
//     $(document).on('dblclick', 'video', function () {
//         if (this.id == "previewVideo") return;

//         let parentElement = $(this).parent();
//         if (parentElement.hasClass('OT_big')) {
//             parentElement.removeClass('OT_big');
//         } else {
//             parentElement.addClass('OT_big');
//         }

//         layout();
//     });

//     //toggle picture-in-picture mode with click on video
//     $(document).on('click', 'video', function () {
//         if (isMobile || this.id == "previewVideo") return;

//         if (document.pictureInPictureElement) {
//             document.exitPictureInPicture();
//         } else {
//             if (this.readyState === 4 && this.srcObject.getVideoTracks().length) {
//                 try {
//                     this.requestPictureInPicture();
//                 } catch (e) {
//                     showError(languages.no_pip);
//                 }
//             } else {
//                 showError(languages.no_video);
//             }
//         }
//     });

//     //toggle chat panel
//     $(document).on('click', '#openChat', function () {
//         $('.chat-panel').animate({
//             width: 'toggle',
//         });

//         if ($(this).hasClass('notify')) {
//             $(this).removeClass('notify');
//             messageCount = 0;
//         }
//     });

//     //close chat panel
//     $(document).on('click', '.close-panel', function () {
//         $('.chat-panel').animate({
//             width: 'toggle',
//         });
//     });

//     //copy/share the meeting invitation
//     $(document).on('click', '.add', function () {
//         let link = location.protocol + '//' + location.host + location.pathname;

//         if (navigator.share) {
//             try {
//                 navigator.share({
//                     title: htmlEscape(settings.appName),
//                     url: link,
//                     text: languages.inviteMessage,
//                 });
//             } catch (e) {
//                 showError(e);
//             }
//         } else {
//             let inp = document.createElement('textarea');
//             inp.style.display = 'hidden';
//             document.body.appendChild(inp);
//             inp.value = languages.inviteMessage + link;
//             inp.select();
//             document.execCommand('copy', false);
//             inp.remove();
//             showSuccess(languages.link_copied);
//         }
//     });

//     //listen for message form submit event and send message
//     $(document).on('submit', '#chatForm', function (e) {
//         e.preventDefault();

//         if (!featureAvailable('text_chat')) return;

//         let message = htmlEscape($('#messageInput').val().trim());

//         if (message) {
//             $('#messageInput').val('');
//             appendMessage(message, null, true);

//             sendMessage(socket, {
//                 type: 'meetingMessage',
//                 message: message,
//                 username: userInfo.username,
//             });
//         }
//     });

//     //listen for ChatGPT form submit event and send message
//     $(document).on('submit', '#chatGPTchatForm', function (e) {
//         e.preventDefault();

//         if (!featureAvailable('chatgpt')) return;

//         let message = htmlEscape($('#chatGPTmessageInput').val().trim());

//         //prevent other messages while ChatGPT is already in process
//         if (waitingForChatGPT) return;

//         if (message) {
//             $('#chatGPTmessageInput').val('');
//             appendChatGPTMessage(message, null, true);
//             appendChatGPTMessage('<p class="typing-dots"></p>', false, false);

//             sendMessage(socket, {
//                 type: 'chatGPTMessage',
//                 message: message
//             });

//             waitingForChatGPT = true;
//         }
//     });

//     //handle message and append it
//     function handlemeetingMessage(data) {
//         if ($('.chat-panel').is(':hidden')) {
//             $('#openChat').addClass('notify').attr('data-content', ++messageCount);
//             showOptions();
//             notificationTone.play();
//         }
//         appendMessage(data.message, data.username, false);
//     }

//     //append message to chat body
//     function appendMessage(message, username, self) {
//         if ($('.empty-chat-body')) {
//             $('.empty-chat-body').remove();
//         }

//         let className = self ? 'local-chat' : 'remote-chat',
//             messageDiv = '<div class="' + className + '">' + '<div>' + (username ? '<span class="remote-chat-name">' + username + ': </span>' : '') + linkify(message) + '</div>' + '</div>';

//         $('.chat-body').append(messageDiv);
//         $('.chat-body').animate({
//             scrollTop: $('.chat-body').prop('scrollHeight'),
//         },
//             1000
//         );
//     }

//     //append message to chat body
//     function appendChatGPTMessage(message, username, self) {
//         if ($('.empty-chatgpt-body')) {
//             $('.empty-chatgpt-body').remove();
//         }

//         let className = self ? 'local-chatgpt' : 'remote-chatgpt',
//             messageDiv = '<div class="' + className + '">' + '<div>' + (username ? '<span class="remote-chatgpt-name">' + username + ': </span>' : '') + linkify(message) + '</div>' + '</div>';

//         $('.chatgpt-body').append(messageDiv);
//         $('.chatgpt-body').animate({
//             scrollTop: $('.chatgpt-body').prop('scrollHeight'),
//         },
//             1000
//         );
//     }

//     //handle ChatGPT message and append it
//     function handleChatGPTMessage(data) {
//         if ($('.chatgpt-panel').is(':hidden')) {
//             $('#openChatGPT').addClass('notify').attr('data-content', ++messageCount);
//             showOptions();
//             notificationTone.play();
//         }

//         $(".typing-dots").parent().parent().remove();

//         appendChatGPTMessage(data.message, data.chatBotName, false);

//         waitingForChatGPT = false;
//     }

//     $(document).on('click', '#openChatGPT', function () {
//         $('.chatgpt-panel').animate({
//             width: 'toggle',
//         });

//         if ($(this).hasClass('notify')) {
//             $(this).removeClass('notify');
//         }
//     });

//     //close ChatGPT panel
//     $(document).on('click', '.close-chatgpt-panel', function () {
//         $('.chatgpt-panel').animate({
//             width: 'toggle',
//         });
//     });

//     //toggle screen share
//     $(document).on('click', '#screenShare', function () {
//         if (!featureAvailable('screen_share')) return;

//         if (screenShared) {
//             stopScreenSharing();
//         } else {
//             if (isModerator || settings.authMode == "disabled" || settings.moderatorRights == "disabled") {
//                 startScreenSharing();
//             } else {
//                 $(this).attr('disabled', true);

//                 //ask moderator for permission
//                 sendMessage(socket, {
//                     type: 'screenSharePermission',
//                     username: userInfo.username,
//                     meetingId: userInfo.meetingId
//                 });

//                 showInfo(languages.please_wait);
//             }
//         }
//     });

//     //stop screen share
//     function stopScreenSharing() {
//         screenStream.getVideoTracks().forEach((track) => track.stop());
//         screenStream = null;
//         screenShared = false;
//         $("#video-" + screenSocketId).parent().remove();
//         layout();
//         screenSocket.disconnect();
//         screenConnections.forEach((connection) => connection.stop());
//         screenConnections = [];
//         if (isRecording) mixer.resetVideoStreams(getVideoStreams());
//     }

//     //start screensharing
//     async function startScreenSharing() {
//         screenSocket = io.connect(settings.signalingURL);

//         let displayMediaOptions = {
//             video: {
//                 cursor: 'always',
//             },
//             audio: true
//         };

//         try {
//             screenStream = await navigator.mediaDevices.getDisplayMedia(displayMediaOptions);
//         } catch (e) {
//             showError(languages.cant_share_screen + ' ' + e);
//         }

//         //prevent undefined screenSocket
//         if (!screenSocket.id && screenStream) {
//             screenStream.getVideoTracks().forEach((track) => track.stop());
//             screenStream = null;
//             screenSocket.disconnect();
//             showError(languages.error_occurred);
//             return;
//         }

//         //check if the screen stream is available
//         if (screenStream) {
//             screenShared = true;
//             screenSocketId = screenSocket.id;

//             let videoScreen = document.createElement('video');
//             videoScreen.id = 'video-' + screenSocketId;
//             videoScreen.setAttribute('autoplay', '');
//             videoScreen.setAttribute('playsinline', '');
//             videoScreen.srcObject = screenStream;
//             videoScreen.muted = true;

//             videoScreen.onloadedmetadata = function (e) {
//                 videoScreen.play();
//             };

//             let containerDiv = document.createElement('div');
//             containerDiv.id = 'container-' + screenSocketId;
//             containerDiv.className = 'videoContainer OT_big';

//             let containerText = document.createElement('span');
//             containerText.className = 'local-user-name';
//             containerText.innerText = languages.your_screen;

//             containerDiv.appendChild(videoScreen);
//             containerDiv.appendChild(containerText);
//             videos.appendChild(containerDiv);

//             layout();

//             if (isRecording) mixer.appendStreams(screenStream);

//             screenSocket.on('message', function (data) {
//                 data = JSON.parse(data);

//                 switch (data.type) {
//                     case 'offer':
//                         handleOffer(screenStream, screenSocket, data, true);
//                         break;
//                 }
//             });

//             sendMessage(screenSocket, {
//                 type: 'join',
//                 username: userInfo.username + '-screen',
//                 meetingId: userInfo.meetingId,
//                 moderator: false,
//                 screen: true,
//                 isModerator
//             });

//             screenStream.getVideoTracks()[0].addEventListener('ended', () => {
//                 stopScreenSharing();
//             });
//         } else {
//             screenSocket.disconnect();
//         }
//     }

//     //listen on file input change
//     $('#file').on('change', function () {
//         let inputFile = this.files;
//         let maxFilesize = $(this).data('max');

//         if (inputFile && inputFile[0]) {
//             if (inputFile[0].size > maxFilesize * 1024 * 1024) {
//                 showError(languages.max_file_size + maxFilesize);
//                 return;
//             }

//             $('#previewImage').attr('src', 'images/loader.gif');
//             $('#previewFilename').text(inputFile[0].name);
//             $('#filePreviewModal').modal('show');

//             if (inputFile[0].type.includes('image')) {
//                 let reader = new FileReader();
//                 reader.onload = function (e) {
//                     $('#previewImage').attr('src', e.target.result);
//                 };
//                 reader.readAsDataURL(inputFile[0]);
//             } else {
//                 $('#previewImage').attr('src', '/images/file.png');
//             }
//         } else {
//             showError();
//         }
//     });

//     //empty file value on modal close
//     $('#filePreviewModal').on('hidden.bs.modal', function () {
//         $('#file').val('');
//     });

//     //hide modal on file send button click
//     $(document).on('click', '#sendFile', function () {
//         $('#filePreviewModal').modal('hide');

//         const fileInput = $("#file")[0].files[0];
//         const meetingId = userInfo.meetingId;

//         const formData = new FormData();
//         formData.append("file", fileInput);
//         formData.append("meetingId", meetingId);

//         $.ajax({
//             url: "/meeting-files",
//             type: "POST",
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function (response) {
//                 const filename = response.file_name;

//                 sendMessage(socket, {
//                     type: "fileMessage",
//                     filename,
//                     username: userInfo.username,
//                 });

//                 appendFile(filename, userInfo.username, false);
//             },
//             error: function (xhr, status, error) {
//                 console.log("Error:", xhr.responseText);
//             },
//         });
//     });

//     //handle file message
//     function handleFileMessage(data) {
//         if ($(".chat-panel").is(":hidden")) {
//             $("#openChat")
//                 .addClass("notify")
//                 .attr("data-content", ++messageCount);
//             showOptions();
//             notificationTone.play();
//         }
//         appendFile(data.filename, data.username, false);
//     }


//     //append file to the chat panel
//     function appendFile(filename, username, self) {
//         if ($(".empty-chat-body")) {
//             $(".empty-chat-body").remove();
//         }

//         const remoteUsername = username
//             ? "<span>" + username + ": </span>"
//             : "";

//         const className = self ? "local-chat" : "remote-chat",
//             fileDiv =
//                 "<div class='" +
//                 className +
//                 "'>" +
//                 "<button class='btn btn-primary fileMessage' title='" +
//                 languages.view_file +
//                 "' data-file='" +
//                 filename +
//                 "'>" +
//                 remoteUsername +
//                 "<i class='fa fa-file'></i> " +
//                 filename +
//                 "</button>";

//         $(".chat-body").append(fileDiv);
//         $(".chat-body").animate(
//             {
//                 scrollTop: $(".chat-body").prop("scrollHeight"),
//             },
//             1000
//         );
//     }

//     //dispay file on button click
//     $(document).on("click", ".fileMessage", function () {
//         const filename = $(this).data("file");
//         const extension = filename.split(".").splice(-1)[0];

//         $("#displayImage").attr("src", "/images/loader.gif");
//         $("#displayFilename").text(filename);
//         $("#displayModal").modal("show");

//         fetch("/storage/file_uploads/" + userInfo.meetingId + "/" + filename)
//             .then((res) => res.blob())
//             .then((blob) => {
//                 displayFileUrl = window.URL.createObjectURL(blob);
//                 if (["png", "jpg", "jpeg", "gif"].includes(extension)) {
//                     $("#displayImage").attr("src", displayFileUrl);
//                 } else {
//                     $("#displayImage").attr("src", "/images/file.png");
//                 }
//             })
//             .catch(() => showError());
//     });

//     //download file on button click
//     $(document).on('click', '#downloadFile', function () {
//         const link = document.createElement('a');
//         link.style.display = 'none';
//         link.href = displayFileUrl;
//         link.download = $('#displayFilename').text();
//         document.body.appendChild(link);
//         link.click();
//         $('#displayModal').modal('hide');
//         window.URL.revokeObjectURL(displayFileUrl);
//     });

//     //open file exploler
//     $(document).on('click', '#selectFile', function () {
//         if (!featureAvailable('file_share')) return;

//         $('#file').trigger('click');
//     });

//     //open device settings modal
//     $('.openSettings').on('click', async function () {
//         $('#settings').modal('show');
//         let devices = await navigator.mediaDevices.enumerateDevices();
//         gotDevices(devices);
//     });

//     //set devices in select input
//     function gotDevices(deviceInfos) {
//         const values = selectors.map((select) => select.value);
//         selectors.forEach((select) => {
//             while (select.firstChild) {
//                 select.removeChild(select.firstChild);
//             }
//         });
//         for (let i = 0; i !== deviceInfos.length; ++i) {
//             const deviceInfo = deviceInfos[i];
//             const option = document.createElement('option');
//             option.value = deviceInfo.deviceId;
//             if (deviceInfo.kind === 'audioinput') {
//                 option.text = deviceInfo.label || `microphone ${audioInputSelect.length + 1}`;
//                 audioInputSelect.appendChild(option);
//             } else if (deviceInfo.kind === 'videoinput') {
//                 option.text = deviceInfo.label || `camera ${videoInputSelect.length + 1}`;
//                 videoInputSelect.appendChild(option);
//             }
//         }
//         selectors.forEach((select, selectorIndex) => {
//             if (Array.prototype.slice.call(select.childNodes).some((n) => n.value === values[selectorIndex])) {
//                 select.value = values[selectorIndex];
//             }
//         });
//     }

//     //get audio constraints
//     function getAudioConstraints() {
//         const audioSource = audioInputSelect.value;

//         return {
//             deviceId: audioSource ? { exact: audioSource } : undefined,
//         };
//     }

//     //get video constraints
//     function getVideoConstraints() {
//         if (meetingType == 'audio') {
//             return false;
//         } else {
//             return {
//                 deviceId: videoInputSelect.value,
//                 width: { exact: $('#' + videoQualitySelect.value).data('width') },
//                 height: { exact: $('#' + videoQualitySelect.value).data('height') },
//             };
//         }
//     }

//     //video input change handler
//     videoQualitySelect.onchange = videoInputSelect.onchange = async function () {
//         const option =
//             videoQualitySelect.options[videoQualitySelect.selectedIndex];

//         if (
//             (features["video_quality"] == "VGA" &&
//                 option.getAttribute("data-width") > 640) ||
//             (features["video_quality"] == "HD" &&
//                 option.getAttribute("data-width") > 1280) ||
//             (features["video_quality"] == "FHD" &&
//                 option.getAttribute("data-width") > 1920)
//         ) {
//             videoQualitySelect.value = "VGA";
//             if (isModerator) {
//                 showError(languages.feature_not_available);
//             } else {
//                 showError(languages.premiumFeature);
//             }
//             return;
//         }

//         if (!(localStream && localStream.getVideoTracks() && localStream.getVideoTracks().length)) return;

//         constraints = {
//             video: getVideoConstraints(),
//         };

//         try {
//             localStream.getVideoTracks().forEach((track) => track.stop());
//             let videoStream = await navigator.mediaDevices.getUserMedia(constraints);
//             localStream.removeTrack(localStream.getVideoTracks()[0]);
//             replaceMediaTrack(videoStream.getVideoTracks()[0]);
//             videoSource.value = localStream.getVideoTracks()[0].getSettings().deviceId;
//             localStorage.setItem('videoQuality', videoQualitySelect.value);
//         } catch (e) {
//             showError(e.name);
//             $("#videoQualitySelect").val('VGA').trigger('change');
//         }
//     };

//     //checks and audio input change handler
//     audioSource.onchange = async function () {
//         if (!localStream) return;

//         constraints = {
//             audio: getAudioConstraints(),
//         };

//         try {
//             localStream.getAudioTracks().forEach((track) => track.stop());
//             let audioStream = await navigator.mediaDevices.getUserMedia(constraints);
//             localStream.removeTrack(localStream.getAudioTracks()[0]);
//             replaceMediaTrack(audioStream.getAudioTracks()[0]);
//         } catch (e) {
//             console.log(languages.no_device + e.name);
//         }
//     };

//     //replace video track and add track to the localStream
//     function replaceMediaTrack(track) {
//         if (localStream) localStream.addTrack(track);

//         Object.values(connections).forEach((connection) => {
//             let sender = connection.getSenders().find(function (s) {
//                 return s.track.kind === track.kind;
//             });

//             sender.replaceTrack(track);
//         });
//     }

//     //detect and replace text with url
//     function linkify(text) {
//         var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
//         return text.replace(urlRegex, function (url) {
//             return '<a href="' + url + '" target="_blank">' + url + '</a>';
//         });
//     }

//     //initiate keyboard shortcuts
//     function initKeyShortcuts() {
//         $(document).on('keydown', function (e) {
//             if ($('#messageInput, #chatGPTmessageInput').is(':focus') || window.picker.isPickerVisible()) return;

//             switch (e.key) {
//                 case 'C':
//                 case 'c':
//                     $('.chat-panel').animate({
//                         width: 'toggle',
//                     });

//                     if ($('#openChat').hasClass('notify')) {
//                         $('#openChat').removeClass('notify');
//                         messageCount = 0;
//                     }
//                     break;
//                 case 'F':
//                 case 'f':
//                     if ($('.chat-panel').is(':hidden')) {
//                         $('.chat-panel').animate({
//                             width: 'toggle',
//                         });
//                     }
//                     $('#selectFile').trigger('click');
//                     break;
//                 case 'A':
//                 case 'a':
//                     $('#toggleMic').trigger('click');
//                     break;
//                 case 'L':
//                 case 'l':
//                     $('#leave').trigger('click');
//                     break;
//                 case 'V':
//                 case 'v':
//                     if (meetingType === 'video') $('#toggleVideo').trigger('click');
//                     break;
//                 case 'S':
//                 case 's':
//                     $('#screenShare').trigger('click');
//                     break;
//             }
//         });
//     }

//     //add listner to whiteboard
//     designer.addSyncListener(function (data) {
//         sendMessage(socket, {
//             type: 'whiteboard',
//             data: data
//         });
//     });

//     //set whiteboard tools
//     designer.setTools({
//         line: true,
//         arrow: true,
//         pencil: true,
//         marker: true,
//         dragSingle: false,
//         dragMultiple: false,
//         eraser: true,
//         rectangle: true,
//         arc: false,
//         bezier: false,
//         quadratic: true,
//         text: true,
//         image: true,
//         pdf: false,
//         zoom: false,
//         lineWidth: false,
//         colorsPicker: false,
//         extraOptions: false,
//         code: false,
//         undo: true,
//         snap: true,
//         clear: true,
//         close: true
//     });

//     designer.icons = {
//         pencil: '/images/pencil.png',
//         marker: '/images/marker.png',
//         eraser: '/images/eraser.png',
//         text: '/images/text.png',
//         image: '/images/image.png',
//         pdf: '/images/pdf.png',
//         line: '/images/line.png',
//         arrow: '/images/arrow.png',
//         rectangle: '/images/rectangle.png',
//         quadratic: '/images/curve.png',
//         undo: '/images/undo.png',
//         colorsPicker: '/images/color.png',
//         snap: '/images/camera.png',
//         clear: '/images/clear.png',
//         close: '/images/close.png',
//     };

//     //check if the feature is available in the current meeting plan
//     function featureAvailable(feature) {
//         let result = parseInt(features[feature]);
//         if (!result) showError(languages.feature_not_available);
//         return result;
//     }

//     //toggle whiteboard
//     $(document).on("click", "#whiteboard", function () {
//         if (!featureAvailable('whiteboard')) return;

//         if (whiteboardVisible) {
//             hideWhiteboard();
//         } else {
//             showWhiteboard();
//         }
//     });

//     //hide whiteboard
//     function hideWhiteboard() {
//         $("#videos").removeClass('set-videos');
//         $("#whiteboardSection").removeClass('set-whiteboard');
//         whiteboardVisible = false;
//         layout();
//     }

//     //show whiteboard
//     function showWhiteboard() {
//         $("#videos").addClass('set-videos');
//         $("#whiteboardSection").addClass('set-whiteboard');
//         whiteboardVisible = true;
//         layout();

//         appendWhiteboard();
//     }

//     //append whiteboard
//     function appendWhiteboard() {
//         if (whiteboardAdded) return;
//         designer.appendTo(whiteboardSection);
//         whiteboardAdded = true;

//         //set onload event on iframe
//         $('iframe').on("load", function () {
//             $("iframe").contents().on('click', '#clear', function () {
//                 sendMessage(socket, {
//                     type: 'clearWhiteboard'
//                 });
//             });

//             $("iframe").contents().on('click', '#close', function () {
//                 hideWhiteboard();
//             });
//         });
//     }

//     //handle new event on whiteboard
//     function handleWhiteboard(data) {
//         if (whiteboardAdded) {
//             designer.syncData(data);
//         } else {
//             showWhiteboard();

//             setTimeout(function () {
//                 designer.syncData(data);
//             }, 3000);
//         }
//     }

//     //notify participants about hand raise
//     $(document).on('click', '#raiseHand', function () {
//         if (!featureAvailable('hand_raise')) return;

//         showInfo(languages.hand_raised_self);

//         sendMessage(socket, {
//             type: 'raiseHand',
//             username: userInfo.username,
//         });
//     });

//     //toggle screen share
//     $(document).on('click', '#recording', function () {
//         if (!featureAvailable('recording')) return;

//         if (isOnIOS) {
//             showError(languages.feature_not_supported);
//             return;
//         }

//         if (isRecording) {
//             stopRecording();
//         } else {
//             if (isModerator || settings.authMode == "disabled" || settings.moderatorRights == "disabled") {
//                 startRecording();
//             } else {
//                 $(this).attr('disabled', true);

//                 //ask moderator for permission
//                 sendMessage(socket, {
//                     type: 'recordingPermission',
//                     username: userInfo.username,
//                     meetingId: userInfo.meetingId
//                 });
//             }
//         }
//     });

//     //start the recording
//     function startRecording() {
//         mixer = new MultiStreamsMixer(getVideoStreams());
//         mixer.frameInterval = 1;
//         mixer.startDrawingFrames();

//         recorder = new MediaRecorder(mixer.getMixedStream());
//         recorder.start(1000);
//         recorder.ondataavailable = function (e) {
//             if (e.data && e.data.size > 0) {
//                 recordingData.push(e.data);
//             }
//         }
//         isRecording = true;
//         $("#recording").css('color', 'red');
//         sendMessage(socket, {
//             type: 'recordingStarted',
//             username: userInfo.username,
//             meetingId: userInfo.meetingId
//         })
//     }

//     //stop recording and download
//     function stopRecording() {
//         mixer.releaseStreams();
//         recorder.stop();
//         recorder = recorder.ondataavailable = null;
//         let link = document.createElement('a');
//         link.href = URL.createObjectURL(new Blob(recordingData, { type: "video/webm" }));
//         link.download = meetingTitle;
//         link.click();
//         isRecording = false;
//         recordingData = [];
//         $("#recording").css('color', 'white');
//     }

//     //get all the audio and video streams
//     function getVideoStreams() {
//         let hasVideoTrack = false;
//         let videoStreams = [];

//         $("#videos video").each((key, value) => {
//             if (value.srcObject.getVideoTracks().length) {
//                 hasVideoTrack = true;
//             }
//             videoStreams.push(value.srcObject);
//         });

//         if (recordingPreference.value == 'with' && parseInt(features['whiteboard'])) {
//             hasVideoTrack = true;
//             if (whiteboardAdded) {
//                 videoStreams.push($("iframe").contents().find("#main-canvas")[0].captureStream());
//             } else {
//                 showWhiteboard();
//                 setTimeout(function () {
//                     mixer.appendStreams($("iframe").contents().find("#main-canvas")[0].captureStream());
//                 }, 3000);
//             }
//         }

//         //add a fake video stream from the canvas if no video track is available
//         if (!hasVideoTrack) {
//             videoStreams.push(audioOnly.captureStream());
//         }

//         return videoStreams;
//     }

//     //store recordingPreference in localStorage
//     recordingPreference.onchange = function () {
//         localStorage.setItem('recordingPreference', this.value);
//     };

//     //update recordingPreference value from localStorage
//     recordingPreference.value = localStorage.getItem('recordingPreference') || 'with';

//     //get random color
//     function getRandomColor() {
//         let letters = '0123456789ABCDEF';
//         let color = '#';
//         for (let i = 0; i < 6; i++) {
//             color += letters[Math.floor(Math.random() * 16)];
//         }
//         return color;
//     }

//     //iPhone fix - while clicking on kick button, video got paused
//     localVideo.addEventListener("pause", (event) => {
//         localVideo.play();
//     });

//     //handle video object fit change
//     $(document).on('change', '#videoObjectFit', function () {
//         $(".cam").css('object-fit', this.value);
//         localStorage.setItem('objectFit', this.value);
//     });

//     //handle emojis
//     const trigger = document.querySelector('#emojiPicker');
//     window.picker.on('emoji', selection => {
//         messageInput.value = messageInput.value + selection.emoji;
//     });

//     trigger.addEventListener('click', () => window.picker.togglePicker(trigger));

//     //handle mute all
//     async function handleMuteAll(value) {
//         if (value) {
//             $("#toggleMic").html('<i class="fa fa-microphone-slash"></i>').attr('disabled', true);
//             showSuccess(languages.mic_muted_moderator);
//             if (audioMuted) return;

//             localStream.getAudioTracks().forEach((track) => (track.enabled = false));

//             // $(this).html('<i class="fa fa-microphone"></i>');
//             // audioParams.track.stop();
//             // sendMessage(socket, { type: 'producerClose', id: audioParams.id });
//             localAudio.srcObject = null;

//             audioMuted = allMuted = true;
//             if (isRecording) mixer.resetVideoStreams(getMediaStreams());

//             stopHark();
//         } else {
//             localStream.getAudioTracks().forEach((track) => (track.enabled = true));
//             // $(this).html('<i class="fa fa-microphone-slash"></i>');

//             // localAudioStream = await getUserMedia(true, false);
//             // audioParams.track = localAudioStream.getTracks()[0];
//             // producerTransport.produce(audioParams);
//             // localAudio.srcObject = new MediaStream([audioParams.track]);

//             $("#toggleMic").html('<i class="fa fa-microphone"></i>').attr('disabled', false);
//             audioMuted = allMuted = false;
//             showSuccess(languages.mic_unmuted_moderator);

//             if (isRecording) mixer.appendStreams(new MediaStream([audioParams.track]));

//             initHark();
//         }
//     }

//     //listen on mute all click
//     $(document).on('click', '#muteAll', function () {
//         if (!isModerator) return;

//         if (allMuted) {
//             sendMessage(socket, {
//                 type: 'muteAll',
//                 value: false,
//                 meetingId: userInfo.meetingId,
//             });
//             allMuted = false;
//             $("#muteAll").html('<i class="fa fa-users"></i>');
//             showInfo(languages.you_unmuted);
//         } else {
//             sendMessage(socket, {
//                 type: 'muteAll',
//                 value: true,
//                 meetingId: userInfo.meetingId,
//             });
//             allMuted = true;
//             $("#muteAll").html('<i class="fa fa-users-slash"></i>');
//             showInfo(languages.you_muted);
//         }
//     });
// })();
