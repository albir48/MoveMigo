# WebSocket Real-time Messaging System Setup Guide

## Overview
This WebSocket-based messaging system enables real-time one-to-one communication between tenants and homeowners in MoveMigo.

## Architecture

### Components
1. **WebSocket Server** (`websocket/ChatServer.php`) - Handles real-time message delivery
2. **REST API** (`api/websocket-messaging.php`) - Handles chat creation, message retrieval, offline messages
3. **Frontend** (`chat.php`) - Minimal chat interface with WebSocket client
4. **Database Tables** - `chats` and `messages` tables

## Installation Steps

### 1. Install Dependencies
```bash
composer install
```

This will install:
- `cboden/ratchet` - WebSocket server library
- `react/socket` - ReactPHP socket components

### 2. Database Setup
Run the SQL migration script to create the required tables:

```bash
mysql -u root -p movemigo_db < websocket_messaging_setup.sql
```

Or manually execute the SQL in `websocket_messaging_setup.sql` in your MySQL client.

### 3. Start WebSocket Server
Start the WebSocket server on port 8080:

```bash
php start-websocket-server.php
```

Or directly:
```bash
php websocket/ChatServer.php
```

The server will run on `ws://localhost:8080`

**Important:** Keep this server running while users are using the chat feature.

### 4. Access the Chat Interface
Navigate to `chat.php` with appropriate parameters:

**For tenants (initiating chat):**
```
chat.php?homeowner_id=123
```

**For existing chats:**
```
chat.php?chat_id=456
```

## How It Works

### Authentication Integration

The WebSocket server authenticates users via query parameters:
```
ws://localhost:8080?user_id=XX&role=tenant|homeowner
```

**Note:** In a production environment, you should:
1. Use session tokens instead of passing user_id directly
2. Validate tokens server-side before accepting connections
3. Use WSS (secure WebSocket) over HTTPS

For this implementation, the user_id and role are passed directly for simplicity. The server validates that:
- User exists in the database
- User is active
- Role matches (tenant or homeowner)

### Message Flow

1. **Sending a Message:**
   - Frontend sends message via WebSocket to server
   - Server validates chat and stores message in database
   - Server sends message to receiver if online
   - If receiver is offline, message is stored and delivered when they connect

2. **Receiving Messages:**
   - Online users receive messages instantly via WebSocket
   - Offline users receive messages when they:
     - Load the chat page (fetches from database)
     - Connect to WebSocket (can implement offline message queue)

3. **Chat Creation:**
   - Only tenants can initiate chats
   - Homeowners can only reply to existing chats
   - Chat is created via REST API (`api/websocket-messaging.php`)

### Database Schema

**chats table:**
- `id` - Primary key
- `tenant_id` - Foreign key to tenants table
- `homeowner_id` - Foreign key to homeowners table
- `created_at` - Timestamp

**messages table:**
- `id` - Primary key
- `chat_id` - Foreign key to chats table
- `sender_id` - ID of sender (tenant or homeowner)
- `sender_role` - 'tenant' or 'homeowner'
- `message` - Message text
- `is_read` - Boolean flag
- `created_at` - Timestamp

### API Endpoints

**GET `api/websocket-messaging.php?action=chats`**
- Returns all chats for the logged-in user

**GET `api/websocket-messaging.php?action=chat&chat_id=X`**
- Returns chat details and other user info

**GET `api/websocket-messaging.php?action=messages&chat_id=X`**
- Returns messages for a chat (marks as read)

**POST `api/websocket-messaging.php`**
- `action=create_chat` - Creates a new chat (tenant only)
- `action=mark_read` - Marks messages as read

### WebSocket Message Format

**Sending:**
```json
{
  "type": "message",
  "chat_id": 1,
  "to_user_id": 5,
  "message": "Hello, is this home still available?"
}
```

**Receiving:**
```json
{
  "type": "message",
  "id": 123,
  "chat_id": 1,
  "sender_id": 3,
  "sender_role": "tenant",
  "message": "Hello, is this home still available?",
  "created_at": "2024-01-01 12:00:00"
}
```

## Security Considerations

1. **Session Validation:** Currently, user_id is passed in query string. In production, implement token-based authentication.

2. **Input Sanitization:** Messages are stored as-is. Consider adding HTML escaping in the frontend.

3. **Rate Limiting:** Implement rate limiting to prevent spam.

4. **CORS:** Configure CORS properly if frontend is on different domain.

5. **WSS:** Use secure WebSocket (WSS) in production with SSL certificates.

## Troubleshooting

### Server won't start
- Check if port 8080 is available: `netstat -an | grep 8080`
- Ensure PHP extensions are installed: `php -m | grep sockets`
- Check file permissions

### Messages not sending
- Verify WebSocket server is running
- Check browser console for WebSocket errors
- Verify user_id and role are correct

### Messages not appearing
- Check database connection in ChatServer.php
- Verify chat_id exists and user has access
- Check browser console for errors

## Production Deployment

1. Use a process manager like Supervisor or PM2 to keep the server running
2. Set up reverse proxy (nginx) for WebSocket connections
3. Use WSS (secure WebSocket) with SSL
4. Implement proper session/token validation
5. Add logging and monitoring
6. Set up database connection pooling

## Example Supervisor Configuration

```ini
[program:movemigo-websocket]
command=php /path/to/movemigo/start-websocket-server.php
directory=/path/to/movemigo
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/movemigo-websocket.log
```

