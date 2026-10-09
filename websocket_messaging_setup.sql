-- WebSocket Messaging System Database Setup
-- Run this to add the chats and messages tables for WebSocket messaging

USE movemigo_db;

-- Create chats table (one-to-one chat between tenant and homeowner)
CREATE TABLE IF NOT EXISTS chats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  homeowner_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE,
  UNIQUE KEY unique_chat (tenant_id, homeowner_id),
  INDEX idx_chats_tenant (tenant_id),
  INDEX idx_chats_homeowner (homeowner_id)
);

-- Create messages table for WebSocket messaging
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chat_id INT NOT NULL,
  sender_id INT NOT NULL,
  sender_role ENUM('tenant','homeowner') NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
  INDEX idx_messages_chat (chat_id),
  INDEX idx_messages_sender (sender_id, sender_role),
  INDEX idx_messages_unread (is_read),
  INDEX idx_messages_created (created_at)
);

