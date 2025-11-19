-- Initialize Zoho Sync Service Database
-- This script runs when the PostgreSQL container starts

-- Create database if it doesn't exist (handled by POSTGRES_DB env var)
-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Create tables
CREATE TABLE IF NOT EXISTS tickets (
    id SERIAL PRIMARY KEY,
    zoho_ticket_id VARCHAR(255) UNIQUE NOT NULL,
    ticket_number VARCHAR(255),
    subject TEXT,
    description TEXT,
    status VARCHAR(100),
    priority VARCHAR(50),
    department_id VARCHAR(255),
    department_name VARCHAR(255),
    assignee_id VARCHAR(255),
    assignee_name VARCHAR(255),
    assignee_email VARCHAR(255),
    requester_id VARCHAR(255),
    requester_name VARCHAR(255),
    requester_email VARCHAR(255),
    created_time TIMESTAMP,
    modified_time TIMESTAMP,
    closed_time TIMESTAMP,
    response_time INTEGER,
    resolution_time INTEGER,
    thread_count INTEGER DEFAULT 0,
    custom_fields JSONB,
    tags TEXT[],
    raw_data JSONB,
    synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_tickets_zoho_id ON tickets(zoho_ticket_id);
CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets(status);
CREATE INDEX IF NOT EXISTS idx_tickets_department ON tickets(department_id);
CREATE INDEX IF NOT EXISTS idx_tickets_assignee ON tickets(assignee_id);
CREATE INDEX IF NOT EXISTS idx_tickets_created_time ON tickets(created_time);
CREATE INDEX IF NOT EXISTS idx_tickets_modified_time ON tickets(modified_time);
CREATE INDEX IF NOT EXISTS idx_tickets_synced_at ON tickets(synced_at);

-- Create sync logs table
CREATE TABLE IF NOT EXISTS sync_logs (
    id SERIAL PRIMARY KEY,
    sync_type VARCHAR(50) NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    status VARCHAR(20) DEFAULT 'running',
    tickets_processed INTEGER DEFAULT 0,
    tickets_created INTEGER DEFAULT 0,
    tickets_updated INTEGER DEFAULT 0,
    tickets_errors INTEGER DEFAULT 0,
    error_message TEXT,
    duration_ms INTEGER,
    metadata JSONB
);

-- Create indexes for sync logs
CREATE INDEX IF NOT EXISTS idx_sync_logs_type ON sync_logs(sync_type);
CREATE INDEX IF NOT EXISTS idx_sync_logs_status ON sync_logs(status);
CREATE INDEX IF NOT EXISTS idx_sync_logs_started_at ON sync_logs(started_at);

-- Insert initial data (optional)
-- This can be used to insert any initial configuration or test data

-- Grant permissions (if needed for specific users)
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO your_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO your_user;







