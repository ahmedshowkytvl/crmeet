const { Pool } = require('pg');
const winston = require('winston');

class Database {
    constructor(config) {
        this.pool = new Pool({
            host: config.DB_HOST,
            port: config.DB_PORT,
            database: config.DB_NAME,
            user: config.DB_USER,
            password: config.DB_PASSWORD,
            ssl: config.DB_SSL === 'true' ? { rejectUnauthorized: false } : false,
            max: 20,
            idleTimeoutMillis: 30000,
            connectionTimeoutMillis: 2000,
        });

        this.logger = winston.createLogger({
            level: 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/database.log' })
            ]
        });

        // Handle pool errors
        this.pool.on('error', (err) => {
            this.logger.error('Unexpected error on idle client', err);
        });
    }

    async query(text, params = []) {
        const start = Date.now();
        try {
            const result = await this.pool.query(text, params);
            const duration = Date.now() - start;
            
            this.logger.debug('Database query executed', {
                query: text,
                duration: `${duration}ms`,
                rows: result.rowCount,
                timestamp: new Date().toISOString()
            });
            
            return result;
        } catch (error) {
            const duration = Date.now() - start;
            this.logger.error('Database query failed', {
                query: text,
                error: error.message,
                duration: `${duration}ms`,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getClient() {
        return await this.pool.connect();
    }

    async close() {
        await this.pool.end();
    }

    async testConnection() {
        try {
            const result = await this.query('SELECT NOW()');
            this.logger.info('Database connection test successful', {
                timestamp: result.rows[0].now
            });
            return true;
        } catch (error) {
            this.logger.error('Database connection test failed', error);
            return false;
        }
    }
}

class TicketRepository {
    constructor(database) {
        this.db = database;
        this.logger = winston.createLogger({
            level: 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/ticket-repository.log' })
            ]
        });
    }

    async createTables() {
        const createTicketsTable = `
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
        `;

        const createIndexes = `
            CREATE INDEX IF NOT EXISTS idx_tickets_zoho_id ON tickets(zoho_ticket_id);
            CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets(status);
            CREATE INDEX IF NOT EXISTS idx_tickets_department ON tickets(department_id);
            CREATE INDEX IF NOT EXISTS idx_tickets_assignee ON tickets(assignee_id);
            CREATE INDEX IF NOT EXISTS idx_tickets_created_time ON tickets(created_time);
            CREATE INDEX IF NOT EXISTS idx_tickets_modified_time ON tickets(modified_time);
            CREATE INDEX IF NOT EXISTS idx_tickets_synced_at ON tickets(synced_at);
        `;

        const createSyncLogTable = `
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
        `;

        try {
            await this.db.query(createTicketsTable);
            await this.db.query(createIndexes);
            await this.db.query(createSyncLogTable);
            
            this.logger.info('Database tables created successfully');
        } catch (error) {
            this.logger.error('Failed to create database tables', error);
            throw error;
        }
    }

    async upsertTicket(ticketData) {
        const query = `
            INSERT INTO tickets (
                zoho_ticket_id, ticket_number, subject, description, status, priority,
                department_id, department_name, assignee_id, assignee_name, assignee_email,
                requester_id, requester_name, requester_email, created_time, modified_time,
                closed_time, response_time, resolution_time, thread_count, custom_fields,
                tags, raw_data, synced_at, updated_at
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16,
                $17, $18, $19, $20, $21, $22, $23, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
            ON CONFLICT (zoho_ticket_id) 
            DO UPDATE SET
                ticket_number = EXCLUDED.ticket_number,
                subject = EXCLUDED.subject,
                description = EXCLUDED.description,
                status = EXCLUDED.status,
                priority = EXCLUDED.priority,
                department_id = EXCLUDED.department_id,
                department_name = EXCLUDED.department_name,
                assignee_id = EXCLUDED.assignee_id,
                assignee_name = EXCLUDED.assignee_name,
                assignee_email = EXCLUDED.assignee_email,
                requester_id = EXCLUDED.requester_id,
                requester_name = EXCLUDED.requester_name,
                requester_email = EXCLUDED.requester_email,
                created_time = EXCLUDED.created_time,
                modified_time = EXCLUDED.modified_time,
                closed_time = EXCLUDED.closed_time,
                response_time = EXCLUDED.response_time,
                resolution_time = EXCLUDED.resolution_time,
                thread_count = EXCLUDED.thread_count,
                custom_fields = EXCLUDED.custom_fields,
                tags = EXCLUDED.tags,
                raw_data = EXCLUDED.raw_data,
                synced_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            RETURNING id, zoho_ticket_id, updated_at;
        `;

        const values = [
            ticketData.id,
            ticketData.ticketNumber,
            ticketData.subject,
            ticketData.description,
            ticketData.status,
            ticketData.priority,
            ticketData.department?.id,
            ticketData.department?.name,
            ticketData.assignee?.id,
            ticketData.assignee?.name,
            ticketData.assignee?.email,
            ticketData.contact?.id,
            ticketData.contact?.name,
            ticketData.contact?.email,
            ticketData.createdTime ? new Date(ticketData.createdTime) : null,
            ticketData.modifiedTime ? new Date(ticketData.modifiedTime) : null,
            ticketData.closedTime ? new Date(ticketData.closedTime) : null,
            ticketData.responseTime,
            ticketData.resolutionTime,
            ticketData.threadCount || 0,
            ticketData.customFields ? JSON.stringify(ticketData.customFields) : null,
            ticketData.tags || [],
            JSON.stringify(ticketData)
        ];

        try {
            const result = await this.db.query(query, values);
            return result.rows[0];
        } catch (error) {
            this.logger.error('Failed to upsert ticket', {
                ticketId: ticketData.id,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getTickets(filters = {}, pagination = {}) {
        const {
            status,
            department,
            assignee,
            priority,
            dateFrom,
            dateTo,
            search
        } = filters;

        const {
            page = 1,
            limit = 50,
            sortBy = 'modified_time',
            sortOrder = 'desc'
        } = pagination;

        let whereConditions = [];
        let queryParams = [];
        let paramIndex = 1;

        if (status) {
            whereConditions.push(`status = $${paramIndex}`);
            queryParams.push(status);
            paramIndex++;
        }

        if (department) {
            whereConditions.push(`department_id = $${paramIndex}`);
            queryParams.push(department);
            paramIndex++;
        }

        if (assignee) {
            whereConditions.push(`assignee_id = $${paramIndex}`);
            queryParams.push(assignee);
            paramIndex++;
        }

        if (priority) {
            whereConditions.push(`priority = $${paramIndex}`);
            queryParams.push(priority);
            paramIndex++;
        }

        if (dateFrom) {
            whereConditions.push(`created_time >= $${paramIndex}`);
            queryParams.push(new Date(dateFrom));
            paramIndex++;
        }

        if (dateTo) {
            whereConditions.push(`created_time <= $${paramIndex}`);
            queryParams.push(new Date(dateTo));
            paramIndex++;
        }

        if (search) {
            whereConditions.push(`(subject ILIKE $${paramIndex} OR description ILIKE $${paramIndex})`);
            queryParams.push(`%${search}%`);
            paramIndex++;
        }

        const whereClause = whereConditions.length > 0 ? `WHERE ${whereConditions.join(' AND ')}` : '';
        const offset = (page - 1) * limit;

        const countQuery = `SELECT COUNT(*) FROM tickets ${whereClause}`;
        const dataQuery = `
            SELECT * FROM tickets 
            ${whereClause}
            ORDER BY ${sortBy} ${sortOrder.toUpperCase()}
            LIMIT $${paramIndex} OFFSET $${paramIndex + 1}
        `;

        queryParams.push(limit, offset);

        try {
            const [countResult, dataResult] = await Promise.all([
                this.db.query(countQuery, queryParams.slice(0, -2)),
                this.db.query(dataQuery, queryParams)
            ]);

            const totalCount = parseInt(countResult.rows[0].count);
            const totalPages = Math.ceil(totalCount / limit);

            return {
                tickets: dataResult.rows,
                pagination: {
                    page,
                    limit,
                    totalCount,
                    totalPages,
                    hasNext: page < totalPages,
                    hasPrev: page > 1
                }
            };
        } catch (error) {
            this.logger.error('Failed to get tickets', {
                filters,
                pagination,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getTicketById(ticketId) {
        const query = 'SELECT * FROM tickets WHERE zoho_ticket_id = $1';
        
        try {
            const result = await this.db.query(query, [ticketId]);
            return result.rows[0] || null;
        } catch (error) {
            this.logger.error('Failed to get ticket by ID', {
                ticketId,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getLastSyncTime() {
        const query = `
            SELECT MAX(synced_at) as last_sync 
            FROM tickets 
            WHERE synced_at IS NOT NULL
        `;
        
        try {
            const result = await this.db.query(query);
            return result.rows[0]?.last_sync || null;
        } catch (error) {
            this.logger.error('Failed to get last sync time', error);
            throw error;
        }
    }

    async createSyncLog(syncData) {
        const query = `
            INSERT INTO sync_logs (
                sync_type, started_at, status, metadata
            ) VALUES ($1, $2, $3, $4)
            RETURNING id
        `;

        try {
            const result = await this.db.query(query, [
                syncData.type,
                syncData.startedAt,
                syncData.status,
                JSON.stringify(syncData.metadata || {})
            ]);
            return result.rows[0].id;
        } catch (error) {
            this.logger.error('Failed to create sync log', error);
            throw error;
        }
    }

    async updateSyncLog(logId, updateData) {
        const query = `
            UPDATE sync_logs SET
                completed_at = $1,
                status = $2,
                tickets_processed = $3,
                tickets_created = $4,
                tickets_updated = $5,
                tickets_errors = $6,
                error_message = $7,
                duration_ms = $8
            WHERE id = $9
        `;

        try {
            await this.db.query(query, [
                updateData.completedAt,
                updateData.status,
                updateData.ticketsProcessed || 0,
                updateData.ticketsCreated || 0,
                updateData.ticketsUpdated || 0,
                updateData.ticketsErrors || 0,
                updateData.errorMessage,
                updateData.durationMs,
                logId
            ]);
        } catch (error) {
            this.logger.error('Failed to update sync log', error);
            throw error;
        }
    }

    async getSyncStats() {
        const query = `
            SELECT 
                COUNT(*) as total_tickets,
                COUNT(CASE WHEN status = 'Open' THEN 1 END) as open_tickets,
                COUNT(CASE WHEN status = 'Closed' THEN 1 END) as closed_tickets,
                COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_tickets,
                MAX(synced_at) as last_sync_time
            FROM tickets
        `;

        try {
            const result = await this.db.query(query);
            return result.rows[0];
        } catch (error) {
            this.logger.error('Failed to get sync stats', error);
            throw error;
        }
    }
}

module.exports = { Database, TicketRepository };






