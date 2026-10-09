# WebSocket Real-time Messaging System

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Setup Database
```bash
mysql -u root -p movemigo_db < websocket_messaging_setup.sql
```

### 3. Start WebSocket Server
```bash
php start-websocket-server.php
```

Keep this running in a terminal.

### 4. Use the Chat System

**For Tenants:**
- Go to tenant dashboard
- Click "Message" button on any property
- This opens `chat.php?homeowner_id=X`
- Start chatting!

**For Homeowners:**
- Go to `chat-list.php` to see all chats
- Click on any chat to open it
- Reply to tenant messages

## File Structure

```
movemigo/
├── websocket/
│   └── ChatServer.php          # WebSocket server implementation
├── api/
│   └── websocket-messaging.php # REST API for chat operations
├── chat.php                     # Chat interface (single chat view)
├── chat-list.php               # List of all chats
├── start-websocket-server.php  # Server startup script
├── websocket_messaging_setup.sql # Database migration
├── composer.json               # PHP dependencies
└── WEBSOCKET_SETUP.md         # Detailed documentation
```

## How Authentication Works

The WebSocket server authenticates users via query parameters:
```
ws://localhost:8080?user_id=123&role=tenant
```

**Current Implementation:**
- User ID and role are passed directly in the WebSocket URL
- Server validates user exists and is active in database
- No session token validation (simplified for this implementation)

**For Production:**
- Implement token-based authentication
- Validate session tokens server-side
- Use WSS (secure WebSocket) with SSL

## Message Flow

1. **User sends message:**
   - Frontend → WebSocket Server
   - Server stores in database
   - Server sends to receiver if online
   - If offline, message stored for later delivery

2. **User receives message:**
   - If online: Receives instantly via WebSocket
   - If offline: Fetches from database when opening chat

## API Endpoints

All endpoints require valid PHP session.

### GET `api/websocket-messaging.php?action=chats`
Returns all chats for logged-in user.

### GET `api/websocket-messaging.php?action=chat&chat_id=X`
Returns chat details.

### GET `api/websocket-messaging.php?action=messages&chat_id=X`
Returns messages for a chat (marks as read).

### POST `api/websocket-messaging.php`
Body: `{"action": "create_chat", "homeowner_id": 123}`
Creates a new chat (tenant only).

## WebSocket Message Format

**Send:**
```json
{
  "type": "message",
  "chat_id": 1,
  "to_user_id": 5,
  "message": "Hello!"
}
```

**Receive:**
```json
{
  "type": "message",
  "id": 123,
  "chat_id": 1,
  "sender_id": 3,
  "sender_role": "tenant",
  "message": "Hello!",
  "created_at": "2024-01-01 12:00:00"
}
```

## Troubleshooting

**Server won't start:**
- Check port 8080 is free: `netstat -an | grep 8080`
- Install PHP sockets extension
- Run `composer install`

**Messages not sending:**
- Ensure WebSocket server is running
- Check browser console for errors
- Verify user_id and role are correct

**Can't connect to WebSocket:**
- Check server is running
- Verify port 8080 is accessible
- Check firewall settings

## Production Notes

1. Use process manager (Supervisor/PM2) to keep server running
2. Implement proper authentication with tokens
3. Use WSS (secure WebSocket) with SSL
4. Add rate limiting
5. Implement message queuing for offline users
6. Add logging and monitoring

