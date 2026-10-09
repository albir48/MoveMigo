<?php
session_start();
require_once 'includes/Auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: signin.php');
    exit();
}

$auth = new Auth();
$user = $auth->validateSession($_SESSION['session_token'] ?? '');

if (!$user) {
    session_destroy();
    header('Location: signin.php');
    exit();
}

// Get chat_id from query parameter
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;
$homeowner_id = isset($_GET['homeowner_id']) ? (int)$_GET['homeowner_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - MoveMigo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            background: #fff;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .message {
            margin-bottom: 10px;
            display: flex;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message.received {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 10px;
            word-wrap: break-word;
        }
        
        .message.sent .message-content {
            background: #007bff;
            color: white;
        }
        
        .message.received .message-content {
            background: #e9ecef;
            color: #333;
        }
        
        .message-time {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .chat-input-container {
            padding: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
            background: white;
        }
        
        .message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }
        
        .send-btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .send-btn:hover {
            background: #0056b3;
        }
        
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .status {
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .status.connected {
            color: #28a745;
        }
        
        .status.disconnected {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2 id="chat-title">Chat</h2>
            <div class="status disconnected" id="status">Disconnected</div>
        </div>
        <div class="chat-messages" id="messages"></div>
        <div class="chat-input-container">
            <textarea class="message-input" id="messageInput" placeholder="Type your message..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn" disabled>Send</button>
        </div>
    </div>

    <script>
        const userId = <?php echo $user['user_id']; ?>;
        const userRole = '<?php echo $user['user_type']; ?>';
        const chatId = <?php echo $chat_id ?: 'null'; ?>;
        const homeownerId = <?php echo $homeowner_id ?: 'null'; ?>;
        
        let ws = null;
        let currentChatId = chatId;
        let otherUserId = null;
        
        // Initialize chat
        async function initChat() {
            try {
                if (currentChatId) {
                    // Load existing chat
                    await loadChat(currentChatId);
                    // Load messages for existing chat
                    await loadMessages(currentChatId);
                } else if (homeownerId && userRole === 'tenant') {
                    // Create new chat automatically
                    await createChat(homeownerId);
                } else if (homeownerId && userRole === 'homeowner') {
                    // Homeowner trying to access chat - find existing chat
                    await findChatByHomeowner(homeownerId);
                } else {
                    document.getElementById('messages').innerHTML = '<p>No chat selected</p>';
                    return;
                }
                
                // Connect to WebSocket after chat is ready
                connectWebSocket();
            } catch (error) {
                console.error('Error initializing chat:', error);
                document.getElementById('messages').innerHTML = '<p>Error loading chat. Please try again.</p>';
            }
        }
        
        async function createChat(homeownerId) {
            try {
                document.getElementById('chat-title').textContent = 'Creating chat...';
                
                const response = await fetch('api/websocket-messaging.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'create_chat',
                        homeowner_id: homeownerId
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    currentChatId = data.chat_id;
                    otherUserId = homeownerId;
                    
                    // Load chat details and messages
                    await loadChat(currentChatId);
                    await loadMessages(currentChatId);
                } else {
                    document.getElementById('messages').innerHTML = 
                        '<p style="color: red;">Error creating chat: ' + (data.error || 'Unknown error') + '</p>';
                }
            } catch (error) {
                console.error('Error creating chat:', error);
                document.getElementById('messages').innerHTML = 
                    '<p style="color: red;">Error creating chat. Please check your connection and try again.</p>';
            }
        }
        
        async function findChatByHomeowner(homeownerId) {
            // For homeowners, try to find existing chat with a tenant
            // This is a fallback - normally homeowners access via chat_id
            try {
                const response = await fetch('api/websocket-messaging.php?action=chats');
                const data = await response.json();
                
                if (data.success && data.chats.length > 0) {
                    // Find chat with matching homeowner (though this shouldn't happen)
                    const chat = data.chats.find(c => c.homeowner_id == homeownerId);
                    if (chat) {
                        currentChatId = chat.id;
                        await loadChat(currentChatId);
                        await loadMessages(currentChatId);
                    } else {
                        document.getElementById('messages').innerHTML = 
                            '<p>No chat found. Please access via chat list.</p>';
                    }
                } else {
                    document.getElementById('messages').innerHTML = 
                        '<p>No chats available. Tenants can start conversations with you.</p>';
                }
            } catch (error) {
                console.error('Error finding chat:', error);
            }
        }
        
        async function loadChat(chatId) {
            try {
                const response = await fetch(`api/websocket-messaging.php?action=chat&chat_id=${chatId}`);
                const data = await response.json();
                
                if (data.success) {
                    const chat = data.chat;
                    otherUserId = chat.other_user_id;
                    const otherUser = chat.other_user;
                    document.getElementById('chat-title').textContent = 
                        `Chat with ${otherUser.first_name} ${otherUser.last_name}`;
                } else {
                    document.getElementById('messages').innerHTML = 
                        '<p style="color: red;">Error loading chat: ' + (data.error || 'Unknown error') + '</p>';
                }
            } catch (error) {
                console.error('Error loading chat:', error);
                document.getElementById('messages').innerHTML = 
                    '<p style="color: red;">Error loading chat details.</p>';
            }
        }
        
        function connectWebSocket() {
            const wsUrl = `ws://localhost:8080?user_id=${userId}&role=${userRole}`;
            
            ws = new WebSocket(wsUrl);
            
            ws.onopen = function() {
                document.getElementById('status').textContent = 'Connected';
                document.getElementById('status').className = 'status connected';
                document.getElementById('sendBtn').disabled = false;
            };
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'message') {
                    displayMessage(data);
                } else if (data.type === 'error') {
                    alert('Error: ' + data.message);
                } else if (data.type === 'connected') {
                    console.log('WebSocket connected');
                }
            };
            
            ws.onclose = function() {
                document.getElementById('status').textContent = 'Disconnected';
                document.getElementById('status').className = 'status disconnected';
                document.getElementById('sendBtn').disabled = true;
                
                // Reconnect after 3 seconds
                setTimeout(connectWebSocket, 3000);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
        }
        
        async function loadMessages(chatId) {
            try {
                const response = await fetch(`api/websocket-messaging.php?action=messages&chat_id=${chatId}`);
                const data = await response.json();
                
                if (data.success) {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            displayMessage(msg);
                        });
                        scrollToBottom();
                    } else {
                        messagesDiv.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No messages yet. Start the conversation!</p>';
                    }
                } else {
                    document.getElementById('messages').innerHTML = 
                        '<p style="color: red;">Error loading messages: ' + (data.error || 'Unknown error') + '</p>';
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                document.getElementById('messages').innerHTML = 
                    '<p style="color: red;">Error loading messages. Please refresh the page.</p>';
            }
        }
        
        function displayMessage(msg) {
            const messagesDiv = document.getElementById('messages');
            const isSent = msg.sender_id == userId && msg.sender_role === userRole;
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = msg.message;
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            const time = new Date(msg.created_at);
            timeDiv.textContent = time.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
            
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);
            messagesDiv.appendChild(messageDiv);
            
            scrollToBottom();
        }
        
        function scrollToBottom() {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !currentChatId || !otherUserId) {
                return;
            }
            
            if (ws && ws.readyState === WebSocket.OPEN) {
                const messageData = {
                    type: 'message',
                    chat_id: currentChatId,
                    to_user_id: otherUserId,
                    message: message
                };
                
                ws.send(JSON.stringify(messageData));
                input.value = '';
            } else {
                alert('WebSocket not connected. Please wait...');
            }
        }
        
        document.getElementById('sendBtn').addEventListener('click', sendMessage);
        
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Auto-resize textarea
        document.getElementById('messageInput').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        // Initialize on page load
        initChat();
    </script>
</body>
</html>

