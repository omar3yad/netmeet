'use strict';

let dotenvLoaded = false;
try {
  require('dotenv').config();
  dotenvLoaded = true;
} catch (error) {
  console.warn('[Signaling] dotenv not found, proceeding without .env support');
}

const path = require('path');
const http = require('http');
const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const { Server } = require('socket.io');

const PORT = process.env.PORT || 8080;
const APP_NAME = process.env.APP_NAME || 'Networkat Meetings';
const HEARTBEAT_INTERVAL_MS = Number(process.env.HEARTBEAT_INTERVAL_MS) || 15000;

const ICE_SERVERS = [
  {
    urls: (process.env.STUN_URL && process.env.STUN_URL.split(',')) || ['stun:stun.l.google.com:19302']
  },
  ...(process.env.TURN_URL
    ? [
        {
          urls: process.env.TURN_URL.split(','),
          username: process.env.TURN_USERNAME,
          credential: process.env.TURN_PASSWORD
        }
      ]
    : [])
];

const app = express();
app.use(helmet());
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname)));

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: process.env.CORS_ORIGIN || '*',
    methods: ['GET', 'POST']
  },
  pingInterval: HEARTBEAT_INTERVAL_MS,
  pingTimeout: HEARTBEAT_INTERVAL_MS * 2
});

const rooms = new Map();

app.get('/healthz', (_req, res) => {
  res.json({ status: 'ok', app: APP_NAME, timestamp: Date.now(), dotenv: dotenvLoaded });
});

app.get('/config/ice', (_req, res) => {
  res.json({ iceServers: ICE_SERVERS });
});

app.post('/api/heartbeat', (req, res) => {
  res.json({ status: 'alive', meetingId: req.body?.meetingId || null, timestamp: Date.now() });
});

io.on('connection', (socket) => {
  console.log('[Signaling] client connected', socket.id);
  let activeMeetingId = null;

  const socketHeartbeat = setInterval(() => {
    socket.emit('serverPing', { ts: Date.now() });
  }, HEARTBEAT_INTERVAL_MS);

  socket.on('clientHeartbeat', (payload = {}) => {
    socket.emit('serverHeartbeat', { ts: Date.now(), message: 'ack', hidden: payload.hidden });
  });

  socket.on('serverPong', () => {
    // Reserved for future metrics
  });

  socket.on('message', (rawMessage) => {
    let data;
    try {
      data = typeof rawMessage === 'string' ? JSON.parse(rawMessage) : rawMessage;
    } catch (error) {
      console.error('[Signaling] Invalid JSON payload', error);
      return;
    }

    if (!data || !data.type) return;

    switch (data.type) {
      case 'checkMeeting': {
        handleCheckMeeting(socket, data);
        break;
      }
      case 'join': {
        activeMeetingId = data.meetingId;
        joinMeeting(socket, data);
        break;
      }
      case 'leave': {
        leaveMeeting(socket, data.meetingId || activeMeetingId, data);
        break;
      }
      default: {
        forwardSignal(socket, data);
      }
    }
  });

  socket.on('disconnect', (reason) => {
    console.log('[Signaling] disconnect', socket.id, reason);
    clearInterval(socketHeartbeat);
    if (activeMeetingId) {
      leaveMeeting(socket, activeMeetingId, { fromSocketId: socket.id, reason: 'disconnect' });
    }
  });
});

function handleCheckMeeting(socket, payload) {
  const { meetingId, moderator } = payload;
  if (!meetingId) {
    socket.emit(
      'message',
      JSON.stringify({
        type: 'checkMeetingResult',
        result: false,
        message: 'invalid_meeting'
      })
    );
    return;
  }

  const room = rooms.get(meetingId) || createRoom(meetingId);

  if (moderator) {
    room.started = true;
  }

  socket.emit(
    'message',
    JSON.stringify({
      type: 'checkMeetingResult',
      result: Boolean(room.started || moderator),
      currentTime: Math.floor((Date.now() - room.startedAt) / 1000),
      allMuted: room.allMuted || false
    })
  );
}

function joinMeeting(socket, payload) {
  const { meetingId } = payload;
  if (!meetingId) return;

  const room = rooms.get(meetingId) || createRoom(meetingId);
  room.participants.set(socket.id, {
    username: payload.username,
    isModerator: payload.isModerator,
    lastSeen: Date.now()
  });
  socket.join(meetingId);

  socket.to(meetingId).emit('message', JSON.stringify(payload));
  console.log('[Signaling] join', meetingId, 'size', room.participants.size);
}

function leaveMeeting(socket, meetingId, payload = {}) {
  if (!meetingId || !rooms.has(meetingId)) return;

  const room = rooms.get(meetingId);
  room.participants.delete(socket.id);
  socket.leave(meetingId);
  socket.to(meetingId).emit('message', JSON.stringify({ ...payload, type: 'leave' }));

  if (room.participants.size === 0) {
    rooms.delete(meetingId);
  }
}

function forwardSignal(socket, payload) {
  if (payload.toSocketId) {
    io.to(payload.toSocketId).emit('message', JSON.stringify(payload));
  } else if (payload.meetingId) {
    socket.to(payload.meetingId).emit('message', JSON.stringify(payload));
  } else {
    socket.broadcast.emit('message', JSON.stringify(payload));
  }
}

function createRoom(meetingId) {
  const room = {
    started: false,
    startedAt: Date.now(),
    allMuted: false,
    participants: new Map()
  };
  rooms.set(meetingId, room);
  return room;
}

server.listen(PORT, () => {
  console.log(`[Signaling] ${APP_NAME} listening on port ${PORT}`);
});

