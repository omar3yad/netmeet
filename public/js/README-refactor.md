# JupiterMeet `meeting.js` refactor

## What changed
`public/js/meeting.js` (one 3000+ line IIFE) is split into 10 focused files.
No behavior was changed. Two pieces of **inert, fully-commented-out dead
code** were removed (the unused alternate dark-themed `showWaitingForHost`,
and stray leftover comments) since they never executed — that's cleanup,
not a behavior change.

## Required `<script>` load order
Plain scripts, no bundler, no ES modules — exactly as before. Order matters
because later files call into `Meeting.<module>.*` APIs exposed by earlier
ones:

```html
<script src="/js/helpers.js"></script>
<script src="/js/meeting-core.js"></script>
<script src="/js/media.js"></script>
<script src="/js/webrtc.js"></script>
<script src="/js/screen-share.js"></script>
<script src="/js/pip.js"></script>
<script src="/js/chat.js"></script>
<script src="/js/whiteboard.js"></script>
<script src="/js/recording.js"></script>
<script src="/js/ui.js"></script>
```

Keep this block exactly where `<script src="/js/meeting.js"></script>` used
to sit in `meeting.blade.php`, after jQuery / socket.io / hark /
MultiStreamsMixer / easytimer / CanvasDesigner / toastr / emoji-picker /
`languages` / `features` / `userInfo` / etc. — all of those external globals
are still referenced as-is by the new files.

## The `Meeting` namespace
Every module attaches to one global object instead of adding more top-level
`var`/`let`s:

- `Meeting.state` — all shared mutable state (socket, localStream,
  connections, flags, timers, settings...). Owned/initialized by
  `meeting-core.js`; read/written directly by other modules
  (`Meeting.state.localStream`, etc.).
- `Meeting.utils` — pure helpers (`sendMessage`, `linkify`,
  `featureAvailable`, `getRandomColor`, `getCurrentTime`, `reload`).
- `Meeting.core`, `Meeting.media`, `Meeting.webrtc`, `Meeting.screenShare`,
  `Meeting.pip`, `Meeting.chat`, `Meeting.whiteboard`, `Meeting.recording`,
  `Meeting.ui` — each module's minimal public API (only the functions other
  modules actually call cross-module; everything else stays private to its
  file via the IIFE closure).

## Known pre-existing issue preserved as-is
The socket handler for `'recordingPermission'` calls
`Meeting.recording.handleRecordingPermission(data)`, but that function was
never implemented in the original `meeting.js` either (its body was fully
commented out in the source). This refactor keeps that gap rather than
silently "fixing" it — flagged with a `// NOTE` comment in `recording.js`.
If you want it working, mirror `handleScreenSharePermission` in
`screen-share.js` (host approval toast) — happy to add that as a follow-up
once you confirm it's wanted.

## Suggested next incremental steps (not done here, to keep this diff reviewable)
1. Swap the 40+ `Meeting.state.x` reads for a couple of local `const`
   destructures at the top of hot functions, for readability.
2. Consider moving the admin-control DOM string-building in
   `addModeratorButtons` into small template functions.
3. Once this is merged and verified in staging, do a second pass to extract
   the remaining `meeting-core.js` "waiting for host" DOM markup into
   `ui.js`, since `meeting-core.js` is still the largest file (724 lines,
   vs. the original 3000+, but still the biggest of the ten).
