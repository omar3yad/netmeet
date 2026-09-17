/**
 * pip.js
 * -----------------------------------------------------------------------
 * Loads after screen-share.js. Owns Picture-in-Picture toggling.
 * Native browser PiP UI/wiring itself lives in meeting.blade.php, as in
 * the original file - this module just owns the click handler and the
 * (currently no-op) init hook that meeting-core.init() calls.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;

    Meeting.pip = {
        initializePictureInPicture: initializePictureInPicture
    };

    // Native Picture-in-Picture is implemented in meeting.blade.php
    function initializePictureInPicture() {}

    // toggle picture-in-picture mode with click on video
    $(document).on('click', 'video', function () {
        if (state.isMobile || this.id == "previewVideo") return;

        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else {
            if (this.readyState === 4 && this.srcObject.getVideoTracks().length) {
                try {
                    this.requestPictureInPicture();
                } catch (e) {
                    showError(languages.no_pip);
                }
            } else {
                showError(languages.no_video);
            }
        }
    });
})();
