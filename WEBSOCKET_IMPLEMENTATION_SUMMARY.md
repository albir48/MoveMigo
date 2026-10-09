# WebSocket Real-time Messaging Implementation Summary

## ✅ Implementation Complete

A complete WebSocket-based real-time messaging system has been implemented for MoveMigo, enabling one-to-one communication between tenants and homeowners.

## 📁 Files Created

### Core WebSocket Server
- **`websocket/ChatServer.php`** - Main WebSocket server using Ratchet
  - Handles connections, message routing, and real-time delivery
  - Validates users and maintains in-memory user-to-socket mapping
  - Stores messages in MySQL database

### Backend API
- **`api/websocket-messaging.php`** - REST API endpoints
  - `GET ?action=chats` - Get all chats for user
  - `GET ?action=chat&chat_id=X` - Get chat details
  - `GET ?action=messages&chat_id=X` - Get messages (marks as read)
  - `POST {"action": "create_chat", "homeowner_id": X}` - Create new chat

### Frontend
- **`chat.php`** - Minimal chat interface
  - Single chat view with message list
  - WebSocket client for real-time messaging
  - Auto-reconnect on disconnect
  - Message alignment (left/right based on sender)

- **`chat-list.php`** - Chat list page
  - Shows all chats for logged-in user
  - Displays unread count
  - Links to individual chats

### Database
- **`websocket_messaging_setup.sql`** - Database migration
  - Creates `chats` table
  - Creates `messages` table with proper indexes

### Configuration & Setup
- **`composer.json`** - PHP dependencies (Ratchet, React)
- **`start-websocket-server.php`** - Server startup script
- **`WEBSOCKET_SETUP.md`** - Detailed setup documentation
- **`README_WEBSOCKET.md`** - Quick start guide

## 🔧 Setup Instructions

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
**Keep this running!**

### 4. Access Chat
- **Tenants:** Click "Message" button on property → Opens `chat.php?homeowner_id=X`
- **Homeowners:** Go to `chat-list.php` → Click any chat

## 🔐 Authentication Integration

### Current Implementation
- WebSocket connects with: `ws://localhost:8080?user_id=XX&role=tenant|homeowner`
- Server validates user exists and is active in database
- PHP sessions used for REST API authentication

### For Production
- Implement token-based WebSocket authentication
- Validate session tokens server-side
- Use WSS (secure WebSocket) with SSL certificates

## 📊 Database Schema

### `chats` Table
```sql
- id (INT, PRIMARY KEY)
- tenant_id (INT, FOREIGN KEY)
- homeowner_id (INT, FOREIGN KEY)
- created_at (TIMESTAMP)
```

### `messages` Table
```sql
- id (INT, PRIMARY KEY)
- chat_id (INT, FOREIGN KEY)
- sender_id (INT)
- sender_role (ENUM: 'tenant', 'homeowner')
- message (TEXT)
- is_read (BOOLEAN)
- created_at (TIMESTAMP)
```

## 🔄 Message Flow

1. **Sending:**
   - User types message → WebSocket sends to server
   - Server validates chat and stores in database
   - Server sends to receiver if online
   - If offline, message stored for later delivery

2. **Receiving:**
   - **Online:** Receives instantly via WebSocket
   - **Offline:** Fetches from database when opening chat

3. **Chat Creation:**
   - Only tenants can initiate chats
   - Homeowners can only reply to existing chats

## 🎨 UI Features

- **Minimal Design:** Focus on functionality, no animations/emojis
- **Message Alignment:** Sent messages right, received left
- **Connection Status:** Shows connected/disconnected status
- **Auto-scroll:** Messages auto-scroll to bottom
- **Unread Badges:** Shows unread message count

## 🔌 WebSocket Message Format

### Send Message
```json
{
  "type": "message",
  "chat_id": 1,
  "to_user_id": 5,
  "message": "Hello, is this home still available?"
}
```

### Receive Message
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

## ⚙️ Integration Points

### Tenant Dashboard
- Updated `messageHomeowner()` function to redirect to `chat.php?homeowner_id=X`
- Original `messages.php` still exists for backward compatibility

### Homeowner Dashboard
- Link to `messages.php` exists (can be updated to `chat-list.php`)
- Can view and reply to tenant messages

## 🚀 Production Deployment

1. **Process Manager:** Use Supervisor or PM2 to keep server running
2. **Reverse Proxy:** Configure nginx for WebSocket connections
3. **SSL:** Use WSS (secure WebSocket) with SSL certificates
4. **Authentication:** Implement token-based authentication
5. **Rate Limiting:** Add rate limiting to prevent spam
6. **Logging:** Add comprehensive logging and monitoring
7. **Message Queue:** Implement offline message queue for better delivery

## 📝 Notes

- WebSocket server must be running for real-time messaging
- Messages are stored in database even if receiver is offline
- Only one-to-one chats (tenant ↔ homeowner)
- Admin does not participate in chats
- Minimal UI focused on functionality

## 🐛 Troubleshooting

**Server won't start:**
- Check port 8080 is available
- Install PHP sockets extension
- Run `composer install`

**Messages not sending:**
- Verify WebSocket server is running
- Check browser console for errors
- Verify user_id and role are correct

**Can't connect:**
- Check server is running
- Verify port 8080 is accessible
- Check firewall settings

## ✨ Features Implemented

✅ Real-time message delivery via WebSocket
✅ Message persistence in MySQL
✅ Offline message storage and retrieval
✅ One-to-one chat between tenant and homeowner
✅ Chat creation (tenant-initiated)
✅ Unread message tracking
✅ Minimal, functional UI
✅ Auto-reconnect on disconnect
✅ Session-based authentication for API
✅ User validation in WebSocket server

## 🔮 Future Enhancements

- Token-based WebSocket authentication
- Message read receipts
- Typing indicators
- Message search
- File attachments
- Message notifications
- Online/offline status
- Message timestamps with relative time

