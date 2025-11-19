-- Zoho Tickets Microservice Database Schema
-- Schema for storing Zoho Desk tickets with full details

CREATE DATABASE IF NOT EXISTS zoho_tickets_db;
USE zoho_tickets_db;

-- Main tickets table
CREATE TABLE IF NOT EXISTS tickets (
    id BIGINT PRIMARY KEY,
    ticket_number VARCHAR(50) NOT NULL,
    subject TEXT,
    status VARCHAR(50),
    created_time DATETIME,
    closed_time DATETIME,
    email VARCHAR(255),
    phone VARCHAR(50),
    department_id VARCHAR(50),
    assignee_id VARCHAR(50),
    priority VARCHAR(50),
    category VARCHAR(100),
    sub_category VARCHAR(100),
    channel VARCHAR(50),
    thread_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    attachment_count INT DEFAULT 0,
    task_count INT DEFAULT 0,
    follower_count INT DEFAULT 0,
    layout_id VARCHAR(50),
    contact_id VARCHAR(50),
    relationship_type VARCHAR(50),
    language VARCHAR(10),
    status_type VARCHAR(50),
    is_spam BOOLEAN DEFAULT FALSE,
    is_archived BOOLEAN DEFAULT FALSE,
    onhold_time DATETIME,
    classification VARCHAR(100),
    resolution TEXT,
    created_by VARCHAR(100),
    modified_by VARCHAR(100),
    cf_closed_by VARCHAR(100),
    processing_time VARCHAR(50),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_status (status),
    INDEX idx_created_time (created_time),
    INDEX idx_closed_time (closed_time),
    INDEX idx_cf_closed_by (cf_closed_by),
    INDEX idx_department_id (department_id),
    INDEX idx_assignee_id (assignee_id),
    INDEX idx_last_updated (last_updated)
);

-- Custom fields table
CREATE TABLE IF NOT EXISTS ticket_custom_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_value TEXT,
    field_type VARCHAR(50) DEFAULT 'cf',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_field_name (field_name),
    INDEX idx_field_type (field_type)
);

-- Threads table
CREATE TABLE IF NOT EXISTS ticket_threads (
    id VARCHAR(100) PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    thread_type VARCHAR(50),
    direction VARCHAR(10),
    content TEXT,
    summary TEXT,
    body TEXT,
    from_address VARCHAR(255),
    to_address VARCHAR(255),
    cc_address VARCHAR(255),
    bcc_address VARCHAR(255),
    created_time DATETIME,
    modified_time DATETIME,
    is_internal BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_thread_type (thread_type),
    INDEX idx_direction (direction),
    INDEX idx_created_time (created_time)
);

-- Sync status table
CREATE TABLE IF NOT EXISTS sync_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type VARCHAR(50) NOT NULL,
    last_sync_time TIMESTAMP,
    total_tickets_synced INT DEFAULT 0,
    new_tickets INT DEFAULT 0,
    updated_tickets INT DEFAULT 0,
    failed_tickets INT DEFAULT 0,
    sync_duration_seconds INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'running',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sync_type (sync_type),
    INDEX idx_last_sync_time (last_sync_time),
    INDEX idx_status (status)
);

-- API logs table
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(100),
    method VARCHAR(10),
    status_code INT,
    response_time_ms INT,
    request_size_bytes INT,
    response_size_bytes INT,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (endpoint),
    INDEX idx_status_code (status_code),
    INDEX idx_created_at (created_at)
);

-- Create views for common queries
CREATE VIEW IF NOT EXISTS tickets_summary AS
SELECT 
    id,
    ticket_number,
    subject,
    status,
    created_time,
    closed_time,
    cf_closed_by,
    department_id,
    assignee_id,
    priority,
    channel,
    thread_count,
    last_updated
FROM tickets
ORDER BY created_time DESC;

CREATE VIEW IF NOT EXISTS closed_tickets_by_user AS
SELECT 
    cf_closed_by,
    COUNT(*) as total_closed,
    COUNT(CASE WHEN DATE(closed_time) = CURDATE() THEN 1 END) as closed_today,
    COUNT(CASE WHEN DATE(closed_time) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as closed_this_week,
    COUNT(CASE WHEN DATE(closed_time) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as closed_this_month
FROM tickets 
WHERE status = 'Closed' AND cf_closed_by IS NOT NULL AND cf_closed_by != ''
GROUP BY cf_closed_by
ORDER BY total_closed DESC;

CREATE VIEW IF NOT EXISTS tickets_by_status AS
SELECT 
    status,
    COUNT(*) as count,
    COUNT(CASE WHEN DATE(created_time) = CURDATE() THEN 1 END) as created_today,
    COUNT(CASE WHEN DATE(created_time) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as created_this_week
FROM tickets 
GROUP BY status
ORDER BY count DESC;

-- Insert initial sync status
INSERT IGNORE INTO sync_status (sync_type, last_sync_time, status) 
VALUES ('tickets_sync', NULL, 'pending');

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS GetTicketsByDateRange(
    IN start_date DATE,
    IN end_date DATE,
    IN ticket_status VARCHAR(50)
)
BEGIN
    SELECT 
        t.*,
        GROUP_CONCAT(CONCAT(tcf.field_name, ':', tcf.field_value) SEPARATOR '|') as custom_fields
    FROM tickets t
    LEFT JOIN ticket_custom_fields tcf ON t.id = tcf.ticket_id
    WHERE DATE(t.created_time) BETWEEN start_date AND end_date
    AND (ticket_status IS NULL OR t.status = ticket_status)
    GROUP BY t.id
    ORDER BY t.created_time DESC;
END //

CREATE PROCEDURE IF NOT EXISTS GetClosedTicketsByUser(
    IN user_name VARCHAR(100),
    IN days_back INT
)
BEGIN
    SELECT 
        t.*,
        COUNT(tt.id) as thread_count_actual
    FROM tickets t
    LEFT JOIN ticket_threads tt ON t.id = tt.ticket_id
    WHERE t.status = 'Closed'
    AND (user_name IS NULL OR t.cf_closed_by = user_name)
    AND (days_back IS NULL OR DATE(t.closed_time) >= DATE_SUB(CURDATE(), INTERVAL days_back DAY))
    GROUP BY t.id
    ORDER BY t.closed_time DESC;
END //

DELIMITER ;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_tickets_composite ON tickets(status, created_time, cf_closed_by);
CREATE INDEX IF NOT EXISTS idx_threads_composite ON ticket_threads(ticket_id, created_time);
CREATE INDEX IF NOT EXISTS idx_custom_fields_composite ON ticket_custom_fields(ticket_id, field_name);

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON zoho_tickets_db.* TO 'zoho_user'@'localhost' IDENTIFIED BY 'your_password';
-- FLUSH PRIVILEGES;


