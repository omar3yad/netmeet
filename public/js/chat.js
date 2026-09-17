/**
 * chat.js
 * -----------------------------------------------------------------------
 * Loads after pip.js. Owns the text chat panel, the ChatGPT panel, file
 * sharing inside chat, and the emoji picker.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    Meeting.chat = {
        handleMeetingMessage: handlemeetingMessage,
        handleChatGPTMessage: handleChatGPTMessage,
        handleFileMessage: handleFileMessage,
        handleIncomingFile: function (file, username) {
            if ($(".chat-panel").is(":hidden")) {
                $("#openChat").addClass("notify").attr('data-content', ++state.messageCount);
                Meeting.ui.showOptions();
                state.notificationTone.play();
            }
            appendFile(file, username, false);
        }
    };

    // ---------------------------------------------------------------
    // Chat panel
    // ---------------------------------------------------------------
    $(document).on('click', '#openChat', function () {
        $('.chat-panel').animate({ width: 'toggle' });

        if ($(this).hasClass('notify')) {
            $(this).removeClass('notify');
            state.messageCount = 0;
        }
    });

    $(document).on('click', '.close-panel', function () {
        $('.chat-panel').animate({ width: 'toggle' });
    });

    // copy/share the meeting invitation
    $(document).on('click', '.add', function () {
        let link = location.protocol + '//' + location.host + location.pathname;

        if (navigator.share) {
            try {
                navigator.share({
                    title: htmlEscape(state.settings.appName),
                    url: link,
                    text: languages.inviteMessage,
                });
            } catch (e) {
                showError(e);
            }
        } else {
            let inp = document.createElement('textarea');
            inp.style.display = 'hidden';
            document.body.appendChild(inp);
            inp.value = languages.inviteMessage + link;
            inp.select();
            document.execCommand('copy', false);
            inp.remove();
            showSuccess(languages.link_copied);
        }
    });

    $(document).on('submit', '#chatForm', function (e) {
        e.preventDefault();

        if (!utils.featureAvailable('text_chat')) return;

        let message = htmlEscape($('#messageInput').val().trim());

        if (message) {
            $('#messageInput').val('');
            appendMessage(message, null, true);

            utils.sendMessage(state.socket, {
                type: 'meetingMessage',
                message: message,
                username: userInfo.username,
            });
        }
    });

    function handlemeetingMessage(data) {
        if ($('.chat-panel').is(':hidden')) {
            $('#openChat').addClass('notify').attr('data-content', ++state.messageCount);
            Meeting.ui.showOptions();
            state.notificationTone.play();
        }
        appendMessage(data.message, data.username, false);
    }

    function appendMessage(message, username, self) {
        if ($('.empty-chat-body')) {
            $('.empty-chat-body').remove();
        }

        let className = self ? 'local-chat' : 'remote-chat',
            messageDiv = '<div class="' + className + '">' + '<div>' + (username ? '<span class="remote-chat-name">' + username + ': </span>' : '') + utils.linkify(message) + '</div>' + '</div>';

        $('.chat-body').append(messageDiv);
        $('.chat-body').animate({ scrollTop: $('.chat-body').prop('scrollHeight') }, 1000);
    }

    // ---------------------------------------------------------------
    // ChatGPT panel
    // ---------------------------------------------------------------
    $(document).on('submit', '#chatGPTchatForm', function (e) {
        e.preventDefault();

        if (!utils.featureAvailable('chatgpt')) return;

        let message = htmlEscape($('#chatGPTmessageInput').val().trim());

        // prevent other messages while ChatGPT is already in process
        if (state.waitingForChatGPT) return;

        if (message) {
            $('#chatGPTmessageInput').val('');
            appendChatGPTMessage(message, null, true);
            appendChatGPTMessage('<p class="typing-dots"></p>', false, false);

            utils.sendMessage(state.socket, {
                type: 'chatGPTMessage',
                message: message
            });

            state.waitingForChatGPT = true;
        }
    });

    function appendChatGPTMessage(message, username, self) {
        if ($('.empty-chatgpt-body')) {
            $('.empty-chatgpt-body').remove();
        }

        let className = self ? 'local-chatgpt' : 'remote-chatgpt',
            messageDiv = '<div class="' + className + '">' + '<div>' + (username ? '<span class="remote-chatgpt-name">' + username + ': </span>' : '') + utils.linkify(message) + '</div>' + '</div>';

        $('.chatgpt-body').append(messageDiv);
        $('.chatgpt-body').animate({ scrollTop: $('.chatgpt-body').prop('scrollHeight') }, 1000);
    }

    function handleChatGPTMessage(data) {
        if ($('.chatgpt-panel').is(':hidden')) {
            $('#openChatGPT').addClass('notify').attr('data-content', ++state.messageCount);
            Meeting.ui.showOptions();
            state.notificationTone.play();
        }

        $(".typing-dots").parent().parent().remove();

        appendChatGPTMessage(data.message, data.chatBotName, false);

        state.waitingForChatGPT = false;
    }

    $(document).on('click', '#openChatGPT', function () {
        $('.chatgpt-panel').animate({ width: 'toggle' });

        if ($(this).hasClass('notify')) {
            $(this).removeClass('notify');
        }
    });

    $(document).on('click', '.close-chatgpt-panel', function () {
        $('.chatgpt-panel').animate({ width: 'toggle' });
    });

    // ---------------------------------------------------------------
    // File sharing (inside chat)
    // ---------------------------------------------------------------
    $('#file').on('change', function () {
        let inputFile = this.files;
        let maxFilesize = $(this).data('max');

        if (inputFile && inputFile[0]) {
            if (inputFile[0].size > maxFilesize * 1024 * 1024) {
                showError(languages.max_file_size + maxFilesize);
                return;
            }

            $('#previewImage').attr('src', 'images/loader.gif');
            $('#previewFilename').text(inputFile[0].name);
            $('#filePreviewModal').modal('show');

            if (inputFile[0].type.includes('image')) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#previewImage').attr('src', e.target.result);
                };
                reader.readAsDataURL(inputFile[0]);
            } else {
                $('#previewImage').attr('src', '/images/file.png');
            }
        } else {
            showError();
        }
    });

    $('#filePreviewModal').on('hidden.bs.modal', function () {
        $('#file').val('');
    });

    $(document).on('click', '#sendFile', function () {
        $('#filePreviewModal').modal('hide');

        const fileInput = $("#file")[0].files[0];
        const meetingId = userInfo.meetingId;

        const formData = new FormData();
        formData.append("file", fileInput);
        formData.append("meetingId", meetingId);

        $.ajax({
            url: "/meeting-files",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                const filename = response.file_name;

                utils.sendMessage(state.socket, {
                    type: "fileMessage",
                    filename,
                    username: userInfo.username,
                });

                appendFile(filename, userInfo.username, false);
            },
            error: function (xhr, status, error) {
                console.log("Error:", xhr.responseText);
            },
        });
    });

    function handleFileMessage(data) {
        if ($(".chat-panel").is(":hidden")) {
            $("#openChat").addClass("notify").attr("data-content", ++state.messageCount);
            Meeting.ui.showOptions();
            state.notificationTone.play();
        }
        appendFile(data.filename, data.username, false);
    }

    function appendFile(filename, username, self) {
        if ($(".empty-chat-body")) {
            $(".empty-chat-body").remove();
        }

        const remoteUsername = username ? "<span>" + username + ": </span>" : "";

        const className = self ? "local-chat" : "remote-chat",
            fileDiv =
                "<div class='" + className + "'>" +
                "<button class='btn btn-primary fileMessage' title='" + languages.view_file + "' data-file='" + filename + "'>" +
                remoteUsername + "<i class='fa fa-file'></i> " + filename + "</button>";

        $(".chat-body").append(fileDiv);
        $(".chat-body").animate({ scrollTop: $(".chat-body").prop("scrollHeight") }, 1000);
    }

    $(document).on("click", ".fileMessage", function () {
        const filename = $(this).data("file");
        const extension = filename.split(".").splice(-1)[0];

        $("#displayImage").attr("src", "/images/loader.gif");
        $("#displayFilename").text(filename);
        $("#displayModal").modal("show");

        fetch("/storage/file_uploads/" + userInfo.meetingId + "/" + filename)
            .then((res) => res.blob())
            .then((blob) => {
                state.displayFileUrl = window.URL.createObjectURL(blob);
                if (["png", "jpg", "jpeg", "gif"].includes(extension)) {
                    $("#displayImage").attr("src", state.displayFileUrl);
                } else {
                    $("#displayImage").attr("src", "/images/file.png");
                }
            })
            .catch(() => showError());
    });

    $(document).on('click', '#downloadFile', function () {
        const link = document.createElement('a');
        link.style.display = 'none';
        link.href = state.displayFileUrl;
        link.download = $('#displayFilename').text();
        document.body.appendChild(link);
        link.click();
        $('#displayModal').modal('hide');
        window.URL.revokeObjectURL(state.displayFileUrl);
    });

    $(document).on('click', '#selectFile', function () {
        if (!utils.featureAvailable('file_share')) return;

        $('#file').trigger('click');
    });

    // ---------------------------------------------------------------
    // Emoji picker
    // ---------------------------------------------------------------
    const trigger = document.querySelector('#emojiPicker');
    window.picker.on('emoji', selection => {
        messageInput.value = messageInput.value + selection.emoji;
    });

    trigger.addEventListener('click', () => window.picker.togglePicker(trigger));
})();
