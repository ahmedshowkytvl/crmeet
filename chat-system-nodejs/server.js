require('dotenv').config();
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const morgan = require('morgan');
const rateLimit = require('express-rate-limit');
const path = require('path');
const mysql = require('mysql2/promise');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: process.env.SOCKET_CORS_ORIGIN || "http://localhost:8000",
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Middleware
app.use(helmet());
app.use(compression());
app.use(morgan('combined'));
app.use(cors({
    origin: process.env.SOCKET_CORS_ORIGIN || "http://localhost:8000",
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Rate limiting
const limiter = rateLimit({
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW) * 60 * 1000 || 15 * 60 * 1000,
    max: parseInt(process.env.RATE_LIMIT_MAX) || 100
});
app.use('/api/', limiter);

// Database connection
let dbConnection;
async function connectDB() {
    try {
        dbConnection = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            port: process.env.DB_PORT || 3306,
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'crm_stafftobia'
        });
        console.log('✅ Connected to MySQL database');
    } catch (error) {
        console.error('❌ Database connection error:', error.message);
        process.exit(1);
    }
}

// Serve UI files
app.use(express.static(path.join(__dirname, 'UI')));

// API Routes
app.get('/api/health', (req, res) => {
    res.json({ status: 'ok', message: 'Chat system is running' });
});

// Get conversations
app.get('/api/conversations', async (req, res) => {
    try {
        const userId = req.query.userId || req.headers['user-id'];
        if (!userId) {
            return res.status(401).json({ error: 'User ID required' });
        }

        const [conversations] = await dbConnection.execute(`
            SELECT DISTINCT
                cr.id,
                cr.name,
                cr.type,
                cr.avatar,
                cr.last_message_at,
                cm.content as last_message,
                cm.created_at as last_message_time,
                (SELECT COUNT(*) FROM chat_messages 
                 WHERE chat_room_id = cr.id 
                 AND receiver_id = ? 
                 AND read_at IS NULL) as unread_count
            FROM chat_rooms cr
            LEFT JOIN chat_participants cp ON cp.chat_room_id = cr.id
            LEFT JOIN chat_messages cm ON cm.id = (
                SELECT id FROM chat_messages 
                WHERE chat_room_id = cr.id 
                ORDER BY created_at DESC 
                LIMIT 1
            )
            WHERE cp.user_id = ?
            AND cr.is_active = 1
            ORDER BY cr.last_message_at DESC
        `, [userId, userId]);

        res.json({ conversations });
    } catch (error) {
        console.error('Error fetching conversations:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Get messages for a conversation
app.get('/api/messages/:roomId', async (req, res) => {
    try {
        const { roomId } = req.params;
        const userId = req.query.userId || req.headers['user-id'];

        const [messages] = await dbConnection.execute(`
            SELECT 
                cm.*,
                u.name as sender_name,
                u.avatar as sender_avatar
            FROM chat_messages cm
            LEFT JOIN users u ON u.id = cm.sender_id
            WHERE cm.chat_room_id = ?
            AND cm.is_deleted = 0
            ORDER BY cm.created_at ASC
            LIMIT 100
        `, [roomId]);

        // Mark messages as read
        if (userId) {
            await dbConnection.execute(`
                UPDATE chat_messages 
                SET read_at = NOW(), status = 'read'
                WHERE chat_room_id = ? 
                AND receiver_id = ? 
                AND read_at IS NULL
            `, [roomId, userId]);
        }

        res.json({ messages });
    } catch (error) {
        console.error('Error fetching messages:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    console.log('✅ User connected:', socket.id);

    socket.on('join-room', (roomId) => {
        socket.join(`room-${roomId}`);
        console.log(`User ${socket.id} joined room ${roomId}`);
    });

    socket.on('leave-room', (roomId) => {
        socket.leave(`room-${roomId}`);
        console.log(`User ${socket.id} left room ${roomId}`);
    });

    socket.on('send-message', async (data) => {
        try {
            const { roomId, senderId, receiverId, content, messageType = 'text' } = data;

            // Save message to database
            const [result] = await dbConnection.execute(`
                INSERT INTO chat_messages 
                (chat_room_id, sender_id, receiver_id, message_type, content, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'sent', NOW())
            `, [roomId, senderId, receiverId, messageType, content]);

            // Update last message time
            await dbConnection.execute(`
                UPDATE chat_rooms 
                SET last_message_at = NOW() 
                WHERE id = ?
            `, [roomId]);

            // Emit to room
            io.to(`room-${roomId}`).emit('new-message', {
                id: result.insertId,
                chat_room_id: roomId,
                sender_id: senderId,
                receiver_id: receiverId,
                message_type: messageType,
                content: content,
                status: 'sent',
                created_at: new Date()
            });

            // Emit conversation update
            io.emit('conversation-updated', { roomId });
        } catch (error) {
            console.error('Error sending message:', error);
            socket.emit('error', { message: 'Failed to send message' });
        }
    });

    socket.on('typing', (data) => {
        socket.to(`room-${data.roomId}`).emit('user-typing', {
            userId: data.userId,
            roomId: data.roomId
        });
    });

    socket.on('stop-typing', (data) => {
        socket.to(`room-${data.roomId}`).emit('user-stopped-typing', {
            userId: data.userId,
            roomId: data.roomId
        });
    });

    socket.on('disconnect', () => {
        console.log('❌ User disconnected:', socket.id);
    });
});

// Start server
const PORT = process.env.PORT || 3001;
connectDB().then(() => {
    server.listen(PORT, () => {
        console.log(`🚀 Chat system server running on http://localhost:${PORT}`);
        console.log(`📡 Socket.IO ready for connections`);
    });
}).catch(error => {
    console.error('Failed to start server:', error);
    process.exit(1);
});

