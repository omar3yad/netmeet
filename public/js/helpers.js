/**
 * helpers.js
 * -----------------------------------------------------------------------
 * Loads FIRST. Creates the single `Meeting` namespace that every other
 * module attaches to, plus small, dependency-free utility functions that
 * used to be scattered as loose top-level functions in meeting.js.
 *
 * No DOM/session state lives here - just the namespace shell and pure
 * helpers. This keeps the file safely reusable and easy to unit test.
 * -----------------------------------------------------------------------
 */
(function () {
    'use strict';

    // Single global namespace. Every module does `window.Meeting.xxx = ...`
    // instead of declaring more top-level globals.
    window.Meeting = window.Meeting || {};

    Meeting.utils = {
        /**
         * Stringify and emit a message over the given socket.
         * Used by every module that talks to the signaling server.
         */
        sendMessage: function (socket, data) {
            socket.emit('message', JSON.stringify(data));
        },

        /**
         * Current meeting timer value in "hh:mm:ss" (delegates to the
         * easytimer instance stored on Meeting.state).
         */
        getCurrentTime: function () {
            return Meeting.state.timer.getTimeValues().toString(['hours', 'minutes', 'seconds']);
        },

        /**
         * Reload the page (or redirect to settings.endURL) after N seconds.
         */
        reload: function (seconds) {
            setTimeout(function () {
                if (Meeting.state.settings.endURL == 'null') {
                    window.location.reload();
                } else {
                    window.location.href = Meeting.state.settings.endURL;
                }
            }, seconds * 1000);
        },

        /**
         * Turn bare URLs inside a chat message into clickable links.
         */
        linkify: function (text) {
            var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
            return text.replace(urlRegex, function (url) {
                return '<a href="' + url + '" target="_blank">' + url + '</a>';
            });
        },

        /**
         * Check whether a plan feature flag is enabled; shows the
         * "feature not available" toast and returns falsy if not.
         */
        featureAvailable: function (feature) {
            let result = parseInt(features[feature]);
            if (!result) showError(languages.feature_not_available);
            return result;
        },

        /**
         * Random hex color, used for user-initial avatar backgrounds.
         */
        getRandomColor: function () {
            let letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }
    };
})();
