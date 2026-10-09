<?php
/**
 * WebSocket Server Startup Script
 * Run this file to start the WebSocket server on port 8080
 * 
 * Usage: php start-websocket-server.php
 * 
 * Make sure to:
 * 1. Install dependencies: composer install
 * 2. Run database migration: websocket_messaging_setup.sql
 * 3. Start this server before using the chat feature
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';

use MoveMigo\ChatServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

echo "Starting MoveMigo WebSocket Server...\n";
echo "Server will run on ws://localhost:8080\n";
echo "Press Ctrl+C to stop the server\n\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();

