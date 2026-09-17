/**
 * whiteboard.js
 * -----------------------------------------------------------------------
 * Loads after chat.js. Owns the CanvasDesigner instance and whiteboard
 * show/hide/sync behavior.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    const state = Meeting.state;
    const utils = Meeting.utils;

    const designer = new CanvasDesigner();
    designer.widgetHtmlURL = '/widget';
    designer.widgetJsURL = 'js/widget.min.js';

    designer.addSyncListener(function (data) {
        utils.sendMessage(state.socket, {
            type: 'whiteboard',
            data: data
        });
    });

    designer.setTools({
        line: true,
        arrow: true,
        pencil: true,
        marker: true,
        dragSingle: false,
        dragMultiple: false,
        eraser: true,
        rectangle: true,
        arc: false,
        bezier: false,
        quadratic: true,
        text: true,
        image: true,
        pdf: false,
        zoom: false,
        lineWidth: false,
        colorsPicker: false,
        extraOptions: false,
        code: false,
        undo: true,
        snap: true,
        clear: true,
        close: true
    });

    designer.icons = {
        pencil: '/images/pencil.png',
        marker: '/images/marker.png',
        eraser: '/images/eraser.png',
        text: '/images/text.png',
        image: '/images/image.png',
        pdf: '/images/pdf.png',
        line: '/images/line.png',
        arrow: '/images/arrow.png',
        rectangle: '/images/rectangle.png',
        quadratic: '/images/curve.png',
        undo: '/images/undo.png',
        colorsPicker: '/images/color.png',
        snap: '/images/camera.png',
        clear: '/images/clear.png',
        close: '/images/close.png',
    };

    Meeting.whiteboard = {
        designer: designer,
        handleWhiteboard: handleWhiteboard,
        show: showWhiteboard,
        hide: hideWhiteboard
    };

    $(document).on("click", "#whiteboard", function () {
        if (!utils.featureAvailable('whiteboard')) return;

        if (state.whiteboardVisible) {
            hideWhiteboard();
        } else {
            showWhiteboard();
        }
    });

    function hideWhiteboard() {
        $("#videos").removeClass('set-videos');
        $("#whiteboardSection").removeClass('set-whiteboard');
        state.whiteboardVisible = false;
        state.layout();
    }

    function showWhiteboard() {
        $("#videos").addClass('set-videos');
        $("#whiteboardSection").addClass('set-whiteboard');
        state.whiteboardVisible = true;
        state.layout();

        appendWhiteboard();
    }

    function appendWhiteboard() {
        if (state.whiteboardAdded) return;
        designer.appendTo(whiteboardSection);
        state.whiteboardAdded = true;

        $('iframe').on("load", function () {
            $("iframe").contents().on('click', '#clear', function () {
                utils.sendMessage(state.socket, { type: 'clearWhiteboard' });
            });

            $("iframe").contents().on('click', '#close', function () {
                hideWhiteboard();
            });
        });
    }

    function handleWhiteboard(data) {
        if (state.whiteboardAdded) {
            designer.syncData(data);
        } else {
            showWhiteboard();

            setTimeout(function () {
                designer.syncData(data);
            }, 3000);
        }
    }
})();
