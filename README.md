# MoveMigo - Rental and Relocation Platform

A web-based rental and relocation platform built with PHP, MySQL, HTML/CSS/JavaScript, featuring real-time messaging via WebSockets.

## Features

- **User Management**: Separate dashboards for tenants and homeowners
- **Property Listings**: Browse and search available rental properties
- **Real-time Messaging**: WebSocket-based chat system between tenants and homeowners
- **Property Management**: Homeowners can add, edit, and manage property listings
- **Admin Dashboard**: User management and reporting system
- **Authentication**: Secure session-based authentication

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **WebSocket**: Ratchet (PHP WebSocket library)
- **Server**: XAMPP (Apache + MySQL)

## Project Structure

```
movemigo/
├── api/                    # REST API endpoints
├── config/                 # Configuration files
├── includes/               # PHP classes (Auth, Messaging, etc.)
├── websocket/             # WebSocket server
├── admin-dashboard.php    # Admin interface
├── tenant-dashboard.php   # Tenant dashboard
├── homeowner-dashboard.php # Homeowner dashboard
├── chat.php               # WebSocket chat interface
├── messages.php           # Traditional messaging interface
├── signin.php             # Login page
├── signup-tenant.php      # Tenant registration
├── signup-homeowner.php   # Homeowner registration
└── database_setup.sql     # Database schema
```

## Installation

### Prerequisites

- XAMPP (PHP 7.4+, MySQL, Apache)
- Composer (for WebSocket dependencies)
- Git

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd movemigo
   ```

2. **Database Setup**
   - Start XAMPP MySQL
   - Open phpMyAdmin
   - Create database: `movemigo_db`
   - Import `database_setup.sql`
   - Import `websocket_messaging_setup.sql` (for WebSocket messaging)

3. **Configure Database**
   - Edit `config/database.php`
   - Update database credentials if needed (default: root, no password)

4. **Install Dependencies**
   ```bash
   composer install
   ```

5. **Start WebSocket Server**
   ```bash
   php start-websocket-server.php
   ```
   Keep this running in a separate terminal.

6. **Access the Application**
   - Start XAMPP Apache
   - Navigate to `http://localhost/movemigo`

## Usage

### For Tenants
- Sign up at `signup-tenant.php`
- Browse properties on tenant dashboard
- Message homeowners about properties
- Save favorite properties

### For Homeowners
- Sign up at `signup-homeowner.php`
- Add property listings
- Manage properties
- Respond to tenant messages

### For Admins
- Login at `admin_login.php`
- Manage users
- View reports
- Block/unblock users

## WebSocket Messaging

The platform includes a real-time messaging system using WebSockets:

- **Server**: Runs on port 8080
- **Start Server**: `php start-websocket-server.php`
- **Documentation**: See `WEBSOCKET_SETUP.md`

## Database Schema

Main tables:
- `tenants` - Tenant user accounts
- `homeowners` - Homeowner user accounts
- `home` - Property listings
- `home_details` - Property details
- `chats` - WebSocket chat sessions
- `messages` - Chat messages
- `conversations` - Traditional messaging
- `user_sessions` - Active user sessions

## Security Notes

- Change default passwords in production
- Update database credentials
- Use HTTPS in production
- Implement proper session management
- Add rate limiting
- Sanitize all user inputs

## Development

### Adding New Features
- Follow existing code structure
- Use PDO for database queries
- Maintain session-based authentication
- Test WebSocket connections

### Testing
- Use test files: `test-*.php`
- Check browser console for errors
- Verify database connections

## License

[Your License Here]

## Contributors

[Your Name/Team]

## Support

For issues and questions, please open an issue on GitHub.
