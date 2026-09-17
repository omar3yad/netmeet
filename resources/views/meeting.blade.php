@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page . ' | ' . $meeting->title)

@section('style')
    <link href="{{ asset('css/meeting.css?version=') . getVersion() }}" rel="stylesheet">
    <style>
        /* Screen sharing improvements */
        .videoContainer.OT_big {
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
        }
        
        .videoContainer.OT_big video {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
        }
        
        /* Hide participant videos during screen share */
        .screen-sharing .videoContainer:not(.screen-share-container) {
            display: none !important;
        }

        /* Ensure screen share takes full space */
        .screen-sharing .screen-share-container {
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
        }

        /* Mobile screen sharing layout */
        .mobile-screen-sharing .screen-share-container {
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
        }

        /* Picture-in-Picture button styles */
        #pictureInPicture {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        #pictureInPicture:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        #pictureInPicture.active {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        /* Enhanced screen sharing layout */
        .screen-share-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            border: 2px solid #e0e0e0;
        }

        .screen-share-container video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }

        .screen-share-container .local-user-name {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* PiP Window Styles */
        #pip-video {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            width: 320px !important;
            height: 240px !important;
            background: #000 !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3) !important;
            z-index: 9999 !important;
            border: 2px solid #e0e0e0 !important;
            cursor: pointer !important;
        }

        #pip-controls {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            width: 320px !important;
            height: 240px !important;
            z-index: 10000 !important;
            pointer-events: none !important;
        }

        #pip-controls button {
            pointer-events: auto !important;
            background: rgba(0,0,0,0.8) !important;
            color: white !important;
            border: none !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }

        #pip-controls button:hover {
            background: rgba(0,0,0,0.9) !important;
            transform: scale(1.05) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container meeting-details">
        <canvas id="audioOnly" hidden></canvas>
        <video id="fallbackVideo" src="{{ asset('videos/fallback.mp4') }}" loop muted playsinline style="display: none;"></video>
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-lg-7 video-detail">
                <div class="video-Section">
                    <video id="previewVideo" class="cam" autoplay playsinline muted></video>
                    <div class="cameraText">{{ __('Camera is off') }}</div>
                    <div class="video-controls">
                        <ul>
                            <li id="toggleCameraPreview" class="disabled" data-toggle="tooltip" data-placement="top"
                                title="{{ __('On/Off Camera') }}">
                                <em class="fa fa-video-slash"></em>
                            </li>
                            <li data-toggle="tooltip" data-placement="top" title="Settings" class="openSettings"
                                title="{{ __('Settings') }}">
                                <em class="fa fa-cog"></em>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="text-show" style="color: red;"></div>
            </div>
            <div class="col-lg-5 mb-3 mt-3">
                <div class="card mb-0">
                    <div class="card-header">
                        <h5>{{ $meeting->title }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($meeting->timeLimit == -1)
                            <div class="ribbon-wrapper ribbon-xl">
                                <div class="ribbon bg-primary" title="{{ __('Time Limit') }}">
                                    {{ __('Unlimited') . ' ' . __('Minutes') }}
                                </div>
                            </div>
                        @else
                            <div class="ribbon-wrapper ribbon-lg">
                                <div class="ribbon bg-primary" title="{{ __('Time Limit') }}">
                                    {{ $meeting->timeLimit . ' ' . __('Minutes') }}
                                </div>
                            </div>
                        @endif
                        <form id="passwordCheck">
                            <div class="form-group">
                                <h6><i class="fa fa-id-badge mr-1"></i> {{ $meeting->meeting_id }}</h6>
                            </div>
                            @if (getSetting('AUTH_MODE') == 'enabled')
                                <div class="form-group">
                                    <h6><i class="fa fa-calendar mr-1"></i>
                                        {{ $meeting->date ? formatDate($meeting->date) : '-' }}</h6>
                                </div>
                                <div class="form-group">
                                    <h6><i class="fa fa-clock mr-1"></i>
                                        {{ $meeting->time ? formatTime($meeting->time) : '-' }}</h6>
                                </div>
                                <div class="form-group">
                                    <h6><i class="fa fa-globe mr-1"></i>
                                        {{ $meeting->timezone ? $meeting->timezone : '-' }}</h6>
                                </div>
                            @endif
                            <div class="form-group">
                                <p class="mb-1 meetDesc">{{ $meeting->description ? $meeting->description : '-' }}</p>
                            </div>

                            <div class="form-group row" @if (Auth::check()) hidden @endif>
                                <div class="col-12 col-md-10 offset-md-1">
                                    <input type="text" id="username" class="form-control"
                                        value="{{ $meeting->username }}" placeholder="{{ __('Enter your name') }}"
                                        maxlength="25" />
                                </div>
                            </div>

                            @if ($meeting->password)
                                <div class="form-group row">
                                    <div class="col-12 col-md-10 offset-md-1">
                                        <input id="password" type="text" class="form-control" name="password"
                                            placeholder="{{ __('Enter meeting password') }}" maxlength="8" required />
                                        <input type="hidden" name="id" value="{{ $meeting->id }}" />
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row mb-0">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" id="joinMeeting" data-toggle="tooltip"
                                        data-placement="top" title="{{ __('Join Meeting') }}" type="submit"
                                        disabled>{{ __('Join') }}</button>
                                    <button class="btn btn-info" type="button" data-toggle="modal"
                                        data-target="#shortcutInfo" data-toggle="tooltip" data-placement="top"
                                        title="{{ __('Shortcut Keys information') }}"><i class="fa fa-info"></i></button>
                                    <button class="btn btn-warning add" type="button" data-toggle="tooltip"
                                        data-placement="top" title="{{ __('Share Link') }}"><i
                                            class="fa fa-share-alt"></i></button>
                                </div>
                                <div id="error">
                                    <p>{{ __('Could not connect to the server, please try refreshing the page') }}</p>

                                    @if ($meeting->isAdmin)
                                        <a href="{{ route('signaling') }}" target="_blank"><span class="badge badge-warning p-2"><i
                                                    class="fa fa-exclamation-triangle"></i>
                                                {{ __('Troubleshooting steps (Visible to the admin only)') }}</span></a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid meeting-section">
        <div class="row">
            <div id="videos">
                <div id="selfContainer" class="videoContainer">
                    <img src="{{ asset('storage/images/SECONDARY_LOGO.png') }}" class="meeting-logo"
                        alt="{{ getSetting('APPLICATION_NAME') }}" />
                    <video id="localVideo" class="cam" autoplay playsinline muted></video>
                    <span class="local-user-name">{{ __('You') }}
                        <i class='fas fa-crown moderator-icon' title='{{ __("Moderator") }}'
                            @if (!$meeting->isModerator) style="display: none" @endif></i>
                    </span>
                    @if (getAuthUserInfo('avatar'))
                        <img class="user-initial" src="{{ asset('storage/avatars/' . getAuthUserInfo('avatar')) }}" />
                    @else
                        <p class="user-initial"></p>
                    @endif
                </div>
            </div>
            <div id="whiteboardSection"></div>
        </div>

        <div class="meeting-info text-center">
            <span id="meetingIdInfo" class="text-center"></span>
            <br>
            <span id="timer" class="text-center"></span>
        </div>
        <script>
            window.addEventListener("beforeunload", (event) => {
              // Customizing the alert message is no longer supported in most modern browsers,
              // but this still works as a warning dialog.
              event.preventDefault();
              event.returnValue = "The meeting is still running. Are you sure you want to leave?";
              return "The meeting is still running. Are you sure you want to leave?";
            });
        </script>

        <div class="chat-panel">
            <div class="chat-box">
                <div class="chat-header">
                    {{ __('Group Chat') }}
                    <i class="fas fa-times close-panel"></i>
                </div>
                <div class="chat-body">
                    <div class="empty-chat-body">
                        <i class="fa fa-comments chat-icon"></i>
                    </div>
                </div>
                <div class="chat-footer">
                    <form id="chatForm">
                        <div class="input-group">
                            <input type="text" id="messageInput" class="form-control note-input"
                                placeholder="{{ __('Type a message') }}" autocomplete="off" maxlength="250" />
                            <div class="input-group-append">
                                <button id="sendMessage" class="btn btn-outline-secondary" type="submit"
                                    title="{{ __('Send') }}">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                                <button id="selectFile" class="btn btn-outline-secondary"
                                    title="{{ __('Attach File') }}" type="button">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button id="emojiPicker" class="btn btn-outline-secondary" title="{{ __('Emoji') }}"
                                    type="button">
                                    <i class="fa fa-smile"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <input type="file" name="file" id="file" data-max="50" hidden />
                </div>
            </div>
        </div>

        <div class="chatgpt-panel">
            <div class="chatgpt-box">
                <div class="chatgpt-header">
                    <img src="/images/chatgpt-logo.png" width="30" alt="{{ __('ChatGPT') }}" />
                    {{ __('ChatGPT') }}
                    <i class="fas fa-times close-chatgpt-panel"></i>
                </div>
                <div class="chatgpt-body">
                    <div class="empty-chatgpt-body">
                        <i class="fa fa-magic chat-icon"></i>
                    </div>
                </div>
                <div class="chatgpt-footer">
                    <form id="chatGPTchatForm">
                        <div class="input-group">
                            <input type="text" id="chatGPTmessageInput" class="form-control note-input"
                                placeholder="{{ __('Message ChatGPT') }}" autocomplete="off" maxlength="250" />
                            <div class="input-group-append">
                                <button id="chatGPTSendMessage" class="btn btn-outline-secondary" type="submit"
                                    title="{{ __('Send') }}">
                                    <i class="far fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="meeting-options">
            <button class="btn meeting-option" title="{{ __('Group Chat') }}" id="openChat">
                <i class="fa fa-comments"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('ChatGPT') }}" id="openChatGPT">
                <i class="fa fa-magic"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Participants') }}" data-toggle="modal"
                data-target="#participantList" id="showParticipantList">
                <i class="fas fa-users"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Whiteboard') }}" id="whiteboard">
                <i class="fa fa-chalkboard"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Mute/Unmute Mic') }}" id="toggleMic">
                <i class="fa fa-microphone"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('On/Off Camera') }}" id="toggleVideo">
                <i class="fa fa-video"></i>
            </button>
            <button class="btn btn-danger" title="{{ __('Leave Meeting') }}" id="leave">
                <i class="fas fa-phone"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Start/Stop ScreenShare') }}" id="screenShare">
                <i class="fa fa-desktop"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Picture in Picture') }}" id="pictureInPicture" onclick="toggleMeetingPiP()" style="">
                <i class="fa fa-external-link-alt"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Raise Hand') }}" id="raiseHand">
                <i class="fa fa-hand-paper"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Start/Stop Recording') }}" id="recording">
                <i class="fa fa-record-vinyl"></i>
            </button>
            <button class="btn meeting-option openSettings" title="{{ __('Open Settings') }}">
                <i class="fa fa-cog"></i>
            </button>
            <button class="btn meeting-option" title="{{ __('Mute/Unmute All') }}" id="muteAll">
                <i class="fas fa-users"></i>
            </button>
        </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">{{ __('File Preview') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="previewImage" src="" />
                    <p id="previewFilename"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" id="sendFile" class="btn btn-primary">{{ __('Send') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="displayModal" tabindex="-1" role="dialog" aria-labelledby="displayModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="displayModalLabel">{{ __('File Display') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="displayImage" src="" />
                    <p id="displayFilename"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" id="downloadFile" class="btn btn-primary">{{ __('Download') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="settings" tabindex="-1" role="dialog" aria-labelledby="settingsLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsLabel">{{ __('Settings') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-lg-3 col-md-4 text-left">
                            <label for="videoQualitySelect">{{ __('Video quality') }} </label>
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <select id="videoQualitySelect" class="form-control">
                                <option id="QVGA" value="QVGA" data-width="320" data-height="240">{{ __('QVGA') }}</option>
                                <option id="VGA" value="VGA" data-width="640" data-height="480" selected>{{ __('VGA') }}</option>
                                <option id="HD" value="HD" data-width="1280" data-height="720">{{ __('HD') }}</option>
                                <option id="FHD" value="FHD" data-width="1920" data-height="1080">{{ __('FHD') }}</option>
                                <option id="4K" value="4K" data-width="3840" data-height="2160">{{ __('4K') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-3 col-md-4 text-left">
                            <label for="audioSource">{{ __('Audio input source') }} </label>
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <select id="audioSource" class="form-control"></select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-3 col-md-4 text-left">
                            <label for="videoSource">{{ __('Video source') }} </label>
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <select id="videoSource" class="form-control"></select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-3 col-md-4 text-left">
                            <label for="recordingPreference">{{ __('Recording preference') }} </label>
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <select id="recordingPreference" class="form-control">
                                <option value="with">{{ __('With whiteboard') }}</option>
                                <option value="without">{{ __('Without whiteboard') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-lg-3 col-md-4 text-left">
                            <label for="videoObjectFit">{{ __('Video object-fit') }} </label>
                        </div>
                        <div class="col-lg-9 col-md-8">
                            <select id="videoObjectFit" class="form-control">
                                <option value="contain">{{ __('Contain') }}</option>
                                <option value="cover">{{ __('Cover') }}</option>
                                <option value="fill">{{ __('Fill') }}</option>
                                <option value="none">{{ __('None') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shortcutInfo" tabindex="-1" role="dialog" aria-labelledby="shortcutInfoLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shortcutInfoLabel">{{ __('Settings') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Shortcut Key') }}</th>
                                <th scope="col">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">C</th>
                                <td>{{ __('Chat') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">F</th>
                                <td>{{ __('Attach File') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">A</th>
                                <td>{{ __('Mute/Unmute Audio') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">L</th>
                                <td>{{ __('Leave Meeting') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">V</th>
                                <td>{{ __('On/Off Video') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">S</th>
                                <td>{{ __('Screen Share') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="participantList" tabindex="-1" role="dialog" aria-labelledby="participantListLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="participantListLabel">{{ __('Participants') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('#') }}</th>
                                <th scope="col">{{ __('Name') }}</th>
                            </tr>
                        </thead>
                        <tbody id="participantListBody">
                            <tr>
                                <th scope="row"></th>
                                <td>
                                    {{ __('You') }}
                                    <i class='fas fa-crown moderator-icon' title='{{ __("Moderator") }}'
                                        @if (!$meeting->isModerator) style="display: none" @endif></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    @if($meeting->isModerator)
                        <button type="button" class="btn btn-success" id="copyMeetingLink" title="{{ __('Copy Meeting Link') }}">
                            <i class="fa fa-copy mr-1"></i>{{ __('Copy Link') }}
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary add">{{ __('Invite') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay">
        <div class="overlay-wrapper">
            <p id="overlayText"></p>
            <img src="/images/allow.png" alt="{{ __('Allow Camera') }}" />
        </div>
    </div>
<div id="videoUploadMessageContainer" 
     style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(15, 23, 42, 0.75);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 99999;
        font-family: system-ui, -apple-system, sans-serif;
        display: none;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.1);
        align-items: center;
        gap: 8px;
        cursor: pointer;
     ">
</div>

@endsection

@section('script')
    <script type="text/javascript">
        const userInfo = {
            username: htmlEscape(username.value),
            meetingId: "{{ $meeting->meeting_id }}",
            avatar: "{{ getAuthUserInfo('avatar') }}"
        };

        const passwordRequired = "{{ !!$meeting->password }}";
        const moderator = "{{ $meeting->isModerator }}";
        const meetingTitle = "{{ $meeting->title }}";
        const timeLimit = "{{ $meeting->timeLimit == -1 ? 9999 : $meeting->timeLimit }}";
        const userLimit = "{{ $meeting->userLimit == -1 ? 9999 : $meeting->userLimit }}";
        const features = JSON.parse("{{ json_encode($meeting->features) }}".replace(/&quot;/g, '"'));
        Object.freeze(features);
        // Prevention of back navigation without interrupting permission prompts
        (function() {
            function preventBack() {
                window.history.pushState(null, "", window.location.href);
            }
            window.history.pushState(null, "", window.location.href);
            window.addEventListener('popstate', function() {
                window.history.pushState(null, "", window.location.href);
            });
        })();
    </script>
    <script src="{{ asset('js/socket.io.min.js') }}"></script>
    <script src="{{ asset('js/easytimer.min.js') }}"></script>
    <script src="{{ asset('js/adapter.min.js') }}"></script>
    <script src="{{ asset('js/siofu.min.js') }}"></script>
    <script src="{{ asset('js/MultiStreamsMixer.min.js') }}"></script>
    <script src="{{ asset('js/opentok-layout.min.js') }}"></script>
    <script src="{{ asset('js/canvas-designer-widget.js') }}"></script>
    <script src="{{ asset('js/meeting2.js') }}"></script>
    <script src="{{ asset('js/emoji.js') }}"></script>
    <!-- <script src="{{ asset('js/meeting.js?version=26') . getVersion() }}"></script> -->
     <script src="/js/helpers.js"></script>
    <script src="/js/meeting-core.js"></script>
    <script src="/js/media.js"></script>
    <script src="/js/webrtc.js"></script>
    <script src="/js/screen-share.js"></script>
    <script src="/js/pip.js"></script>
    <script src="/js/chat.js"></script>
    <script src="/js/whiteboard.js"></script>
    <script src="/js/recording.js?v=chunk_v1"></script>
    <script src="/js/ui.js"></script>

{{-- Picture-in-Picture + Wake Lock for mobile background --}}
<script>
(function() {
    let wakeLock = null;
    let pipVideo = null;
    let wasInPiP = false;

    // Request wake lock to keep screen on during meeting
    async function requestWakeLock() {
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('Wake Lock active');
                wakeLock.addEventListener('release', () => console.log('Wake Lock released'));
            }
        } catch (err) { console.log('Wake Lock failed:', err); }
    }

    // Helper to check if a video has an active stream/content
    function isVideoActive(v) {
        if (!v) return false;
        if (v.id === 'fallbackVideo') {
            return v.readyState >= 1; // Metadata loaded, ready for PiP
        }
        if (v.id === 'previewVideo') return false;
        if (!v.srcObject) return false;
        
        const videoTracks = v.srcObject.getVideoTracks();
        if (videoTracks.length === 0) return false;
        
        // Return true if track is enabled and active
        return videoTracks.some(track => track.enabled && track.readyState !== 'ended');
    }

    // Find the best active video based on priority:
    // 1. Available screen share (local or remote)
    // 2. Remote participant videos
    // 3. Local camera
    // 4. Fallback video
    function findActiveVideo() {
        // 1. Screen Share (look for container with screen-share-container class or OT_big class)
        const screenShareVideo = document.querySelector('.screen-share-container video, .videoContainer.OT_big video');
        if (screenShareVideo && isVideoActive(screenShareVideo)) {
            return screenShareVideo;
        }

        // 2. Remote participant videos
        const remoteVideos = document.querySelectorAll('.videoContainer:not(#selfContainer) video');
        for (const v of remoteVideos) {
            if (v.id !== 'fallbackVideo' && isVideoActive(v)) {
                return v;
            }
        }

        // 3. Local camera
        const localVideo = document.getElementById('localVideo');
        if (localVideo && isVideoActive(localVideo)) {
            return localVideo;
        }

        // 4. Fallback video
        const fallbackVideo = document.getElementById('fallbackVideo');
        if (fallbackVideo && isVideoActive(fallbackVideo)) {
            return fallbackVideo;
        }

        return null;
    }

    // Periodically update autoPictureInPicture attribute on the active video
    function updateAutoPip() {
        if (!document.pictureInPictureEnabled) return;
        const bestVideo = findActiveVideo();
        const allVideos = document.querySelectorAll('video');
        allVideos.forEach(v => {
            if (v === bestVideo) {
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
    // Update auto-PiP state every second
    setInterval(updateAutoPip, 1000);

    // Register Media Session handler for automatic PiP
    if ('mediaSession' in navigator) {
        try {
            navigator.mediaSession.setActionHandler('enterpictureinpicture', async () => {
                const video = findActiveVideo();
                if (video) {
                    try {
                        if (video.id === 'fallbackVideo' && video.paused) {
                            await video.play();
                        }
                        await video.requestPictureInPicture();
                        wasInPiP = true;
                    } catch (e) {
                        console.log('Media Session PiP failed:', e);
                    }
                }
            });
        } catch (err) {
            console.log('Media Session action handler registration failed:', err);
        }
    }

    let wasAutoPiP = false;

    // Auto-PiP when user minimizes / switches apps
    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'hidden') {
            // Only try auto-PiP if browser supports it and we are not already in PiP
            if (!document.pictureInPictureEnabled) return;
            if (document.pictureInPictureElement) return;

            const video = findActiveVideo();
            if (!video) return;

            try {
                if (video.id === 'fallbackVideo' && video.paused) {
                    await video.play();
                }
                pipVideo = video;
                await video.requestPictureInPicture();
                wasInPiP = true;
                wasAutoPiP = true;
                console.log('Entered PiP automatically');
            } catch (err) {
                console.log('Auto-PiP failed:', err);
            }
        } else if (document.visibilityState === 'visible') {
            // Reacquire wake lock
            requestWakeLock();

            // Only exit PiP if it was entered automatically via backgrounding
            if (wasAutoPiP && document.pictureInPictureElement) {
                try {
                    await document.exitPictureInPicture();
                    wasInPiP = false;
                    wasAutoPiP = false;
                } catch (err) { console.log('Exit Auto-PiP failed:', err); }
            }
        }
    });

    // Reacquire wake lock when visible again
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && wakeLock === null) {
            requestWakeLock();
        }
    });

    // Initial activation when meeting starts (any user interaction)
    function initOnInteraction() {
        requestWakeLock();
        const fallback = document.getElementById('fallbackVideo');
        if (fallback) {
            fallback.play().catch(e => console.log('Fallback video autoplay failed:', e));
        }
        document.removeEventListener('click', initOnInteraction);
        document.removeEventListener('touchstart', initOnInteraction);
    }
    document.addEventListener('click', initOnInteraction, { once: true });
    document.addEventListener('touchstart', initOnInteraction, { once: true });

    // Debug: log what's in the DOM when PiP is pressed
    function debugPipState() {
        const allVideos = document.querySelectorAll('video');
        let report = '=== PiP Debug ===\n';
        report += 'Total videos: ' + allVideos.length + '\n';
        allVideos.forEach((v, i) => {
            report += `Video ${i}: id=${v.id || '(none)'}, ` +
                      `class=${v.className}, ` +
                      `srcObject=${!!v.srcObject}, ` +
                      `videoWidth=${v.videoWidth}, ` +
                      `readyState=${v.readyState}, ` +
                      `paused=${v.paused}\n`;
        });
        report += 'PiP enabled: ' + document.pictureInPictureEnabled + '\n';
        report += 'In PiP: ' + !!document.pictureInPictureElement + '\n';
        console.log(report);
        return report;
    }

    // Manual PiP button trigger
    window.toggleMeetingPiP = async function() {
        if (document.pictureInPictureElement) {
            try {
                await document.exitPictureInPicture();
                wasInPiP = false;
                wasAutoPiP = false;
            } catch (e) {
                console.log('Exit PiP error:', e);
            }
        } else {
            const video = findActiveVideo();
            if (video) {
                try {
                    if (video.id === 'fallbackVideo' && video.paused) {
                        await video.play();
                    }
                    await video.requestPictureInPicture();
                    wasInPiP = true;
                    wasAutoPiP = false;
                } catch (e) {
                    console.log('PiP failed:', e);
                    if (typeof showError === 'function') {
                        showError('Picture-in-Picture failed: ' + (e.message || e));
                    } else {
                        alert('Picture-in-Picture failed.');
                    }
                }
            } else {
                const debug = debugPipState();
                if (typeof showError === 'function') {
                    showError('No active video found for Picture-in-Picture');
                } else {
                    alert('PiP failed. Debug info:\n\n' + debug);
                }
            }
        }
    };

    // Reset state when user leaves PiP manually from native browser control
    document.addEventListener('leavepictureinpicture', () => {
        wasInPiP = false;
        wasAutoPiP = false;
    });
})();
</script>


{{-- Hide Download App button inside meeting --}}
<style>
    #np-floating-install, .np-install-btn {
        display: none !important;
    }
</style>


{{-- Auto-reconnect media tracks when returning from background --}}
<script>
(function() {
    let lastHiddenTime = 0;

    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'hidden') {
            lastHiddenTime = Date.now();
            return;
        }

        // Returned to foreground
        const hiddenDuration = Date.now() - lastHiddenTime;
        if (hiddenDuration < 1500) return; // Ignore short hides

        // Check if we're in a meeting and localStream exists
        if (typeof localStream === 'undefined' || !localStream) return;

        try {
            const videoTracks = localStream.getVideoTracks();
            const audioTracks = localStream.getAudioTracks();

            // Check if any track is dead/ended
            const needsReconnect = [...videoTracks, ...audioTracks].some(t =>
                t.readyState === 'ended' || t.muted
            );

            if (!needsReconnect) return;

            console.log('[NetMeet] Reconnecting media tracks after background...');

            // Get fresh media stream
            const constraints = {
                video: videoTracks.length > 0,
                audio: audioTracks.length > 0
            };
            const newStream = await navigator.mediaDevices.getUserMedia(constraints);

            // Replace video track in all peer connections
            if (typeof peerConnections !== 'undefined' && peerConnections) {
                const newVideoTrack = newStream.getVideoTracks()[0];
                const newAudioTrack = newStream.getAudioTracks()[0];

                Object.values(peerConnections).forEach(pc => {
                    if (!pc || !pc.getSenders) return;
                    pc.getSenders().forEach(sender => {
                        if (sender.track && sender.track.kind === 'video' && newVideoTrack) {
                            sender.replaceTrack(newVideoTrack).catch(e => console.log('Video replace failed:', e));
                        }
                        if (sender.track && sender.track.kind === 'audio' && newAudioTrack) {
                            sender.replaceTrack(newAudioTrack).catch(e => console.log('Audio replace failed:', e));
                        }
                    });
                });
            }

            // Update localStream
            videoTracks.forEach(t => { t.stop(); localStream.removeTrack(t); });
            audioTracks.forEach(t => { t.stop(); localStream.removeTrack(t); });
            newStream.getTracks().forEach(t => localStream.addTrack(t));

            // Update local video element
            const localVideoEl = document.getElementById('localVideo');
            if (localVideoEl) localVideoEl.srcObject = localStream;

            console.log('[NetMeet] Media tracks reconnected');
        } catch (e) {
            console.log('[NetMeet] Reconnect failed:', e);
        }
    });
})();
</script>




{{-- Recording Type Modal --}}
<div id="recordingTypeModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:999999;align-items:center;justify-content:center;font-family:-apple-system,'Segoe UI',sans-serif">
    <div style="background:#fff;border-radius:16px;padding:28px 24px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.4)">
        <h3 style="margin:0 0 8px;font-size:20px;color:#222;text-align:center">Choose Recording Type</h3>
        <p style="margin:0 0 24px;color:#666;font-size:14px;text-align:center">Where do you want to save the recording?</p>

        <div onclick="selectRecordingType('local')" style="display:flex;align-items:center;gap:14px;padding:16px;border:2px solid #e5e5e5;border-radius:12px;cursor:pointer;margin-bottom:12px;transition:all 0.2s" onmouseover="this.style.borderColor='#00bef2';this.style.background='#f0fbff'" onmouseout="this.style.borderColor='#e5e5e5';this.style.background='#fff'">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#00bef2 0%,#0099c8 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </div>
            <div style="flex:1">
                <div style="font-weight:600;color:#222;font-size:15px;margin-bottom:2px">Local Recording</div>
                <div style="color:#666;font-size:13px">Save to your device</div>
            </div>
        </div>

        <div onclick="selectRecordingType('cloud')" style="display:flex;align-items:center;gap:14px;padding:16px;border:2px solid #e5e5e5;border-radius:12px;cursor:pointer;margin-bottom:16px;transition:all 0.2s" onmouseover="this.style.borderColor='#00bef2';this.style.background='#f0fbff'" onmouseout="this.style.borderColor='#e5e5e5';this.style.background='#fff'">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#7c3aed 0%,#5b21b6 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
            </div>
            <div style="flex:1">
                <div style="font-weight:600;color:#222;font-size:15px;margin-bottom:2px">Cloud Recording</div>
                <div style="color:#666;font-size:13px">Save to server, view in My Recordings</div>
            </div>
        </div>

        <button onclick="closeRecordingTypeModal()" style="width:100%;padding:12px;background:#f5f5f5;color:#666;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer">Cancel</button>
    </div>
</div>

<script>
window.selectedRecordingType = 'cloud';
function selectRecordingType(type) {
    window.selectedRecordingType = type;
    
    // ربط الخيار بـ Meeting.state لضمان وصول القيمة للموديول
    if (window.Meeting && window.Meeting.state) {
        window.Meeting.state.recordingType = type;
    }

    document.getElementById('recordingTypeModal').style.display = 'none';
    
    var rp = document.getElementById('recordingPreference');
    if (rp) { rp.value = 'without'; localStorage.setItem('recordingPreference', 'without'); }
    
    window.__skipRecordingModal = true;
    document.getElementById('recording').click();
}

function closeRecordingTypeModal() {
    document.getElementById('recordingTypeModal').style.display = 'none';
}
</script>


{{-- Recording active button style --}}
<style>
    #recording.np-rec-active {
        background: #dc2626 !important;
        animation: np-rec-pulse 1.5s infinite;
    }
    #recording.np-rec-active i {
        color: #fff !important;
    }
    @keyframes np-rec-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.7); }
        50% { box-shadow: 0 0 0 8px rgba(220,38,38,0); }
    }
</style>

@endsection
