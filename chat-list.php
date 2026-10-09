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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - MoveMigo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 5px;
            padding: 20px;
        }
        
        h1 {
            margin-bottom: 20px;
        }
        
        .chat-list {
            list-style: none;
        }
        
        .chat-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-item:hover {
            background: #f9f9f9;
        }
        
        .chat-item:last-child {
            border-bottom: none;
        }
        
        .chat-info {
            flex: 1;
        }
        
        .chat-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .chat-last-message {
            font-size: 12px;
            color: #666;
        }
        
        .unread-badge {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            margin-left: 10px;
        }
        
        .no-chats {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo $user['user_type'] === 'tenant' ? 'tenant-dashboard.php' : 'homeowner-dashboard.php'; ?>" class="back-link">← Back to Dashboard</a>
        <h1>My Chats</h1>
        <ul class="chat-list" id="chatList">
            <li class="no-chats">Loading chats...</li>
        </ul>
    </div>

    <script>
        async function loadChats() {
            try {
                const response = await fetch('api/websocket-messaging.php?action=chats');
                const data = await response.json();
                
                const chatList = document.getElementById('chatList');
                
                if (!data.success || data.chats.length === 0) {
                    chatList.innerHTML = '<li class="no-chats">No chats yet. Start a conversation!</li>';
                    return;
                }
                
                chatList.innerHTML = '';
                
                data.chats.forEach(chat => {
                    const li = document.createElement('li');
                    li.className = 'chat-item';
                    li.onclick = () => {
                        window.location.href = `chat.php?chat_id=${chat.id}`;
                    };
                    
                    const unreadBadge = chat.unread_count > 0 
                        ? `<span class="unread-badge">${chat.unread_count}</span>` 
                        : '';
                    
                    const lastMessage = chat.last_message 
                        ? (chat.last_message.length > 50 ? chat.last_message.substring(0, 50) + '...' : chat.last_message)
                        : 'No messages yet';
                    
                    li.innerHTML = `
                        <div class="chat-info">
                            <div class="chat-name">
                                ${chat.other_user_name}${unreadBadge}
                            </div>
                            <div class="chat-last-message">${lastMessage}</div>
                        </div>
                    `;
                    
                    chatList.appendChild(li);
                });
            } catch (error) {
                console.error('Error loading chats:', error);
                document.getElementById('chatList').innerHTML = 
                    '<li class="no-chats">Error loading chats. Please try again.</li>';
            }
        }
        
        loadChats();
    </script>
</body>
</html>

