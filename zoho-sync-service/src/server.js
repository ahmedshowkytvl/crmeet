require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const winston = require('winston');
const { Database } = require('./database/database');
const TicketsAPI = require('./api/ticketsAPI');
const SyncScheduler = require('./scheduler/syncScheduler');

class ZohoSyncService {
    constructor() {
        this.app = express();
        this.config = process.env;
        this.database = null;
        this.syncScheduler = null;
        this.ticketsAPI = null;

        this.logger = winston.createLogger({
            level: this.config.LOG_LEVEL || 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console({
                    format: winston.format.combine(
                        winston.format.colorize(),
                        winston.format.simple()
                    )
                }),
                new winston.transports.File({ filename: 'logs/app.log' }),
                new winston.transports.File({ 
                    filename: 'logs/error.log', 
                    level: 'error' 
                })
            ]
        });

        this.setupMiddleware();
        this.setupRoutes();
        this.setupErrorHandling();
    }

    setupMiddleware() {
        // Security middleware
        this.app.use(helmet());

        // CORS middleware
        this.app.use(cors({
            origin: process.env.ALLOWED_ORIGINS?.split(',') || '*',
            methods: ['GET', 'POST', 'PUT', 'DELETE'],
            allowedHeaders: ['Content-Type', 'Authorization']
        }));

        // Rate limiting
        const limiter = rateLimit({
            windowMs: parseInt(this.config.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000, // 15 minutes
            max: parseInt(this.config.RATE_LIMIT_MAX_REQUESTS) || 100,
            message: {
                success: false,
                error: 'Too many requests',
                message: 'Rate limit exceeded. Please try again later.'
            },
            standardHeaders: true,
            legacyHeaders: false,
        });
        this.app.use('/api', limiter);

        // Body parsing middleware
        this.app.use(express.json({ limit: '10mb' }));
        this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));

        // Request logging middleware
        this.app.use((req, res, next) => {
            this.logger.info('Incoming request', {
                method: req.method,
                url: req.url,
                ip: req.ip,
                userAgent: req.get('User-Agent'),
                timestamp: new Date().toISOString()
            });
            next();
        });
    }

    setupRoutes() {
        // Health check endpoint
        this.app.get('/health', async (req, res) => {
            try {
                const health = {
                    status: 'healthy',
                    timestamp: new Date().toISOString(),
                    uptime: process.uptime(),
                    memory: process.memoryUsage(),
                    version: process.env.npm_package_version || '1.0.0'
                };

                // Check database connection
                if (this.database) {
                    const dbHealthy = await this.database.testConnection();
                    health.database = dbHealthy ? 'connected' : 'disconnected';
                } else {
                    health.database = 'not_initialized';
                }

                // Check Redis connection
                if (this.syncScheduler) {
                    const queueStats = await this.syncScheduler.getQueueStats();
                    health.queue = queueStats ? 'connected' : 'disconnected';
                    health.queueStats = queueStats;
                } else {
                    health.queue = 'not_initialized';
                }

                // Check last sync time
                if (this.ticketsAPI) {
                    const lastSyncTime = await this.ticketsAPI.ticketRepository.getLastSyncTime();
                    health.lastSyncTime = lastSyncTime;
                }

                const statusCode = health.database === 'connected' && health.queue === 'connected' ? 200 : 503;
                res.status(statusCode).json(health);

            } catch (error) {
                this.logger.error('Health check failed', error);
                res.status(503).json({
                    status: 'unhealthy',
                    error: error.message,
                    timestamp: new Date().toISOString()
                });
            }
        });

        // Manual sync trigger endpoint (protected)
        this.app.post('/api/sync/trigger', async (req, res) => {
            try {
                const { syncType = 'full' } = req.body;
                
                if (!['full', 'incremental'].includes(syncType)) {
                    return res.status(400).json({
                        success: false,
                        error: 'Invalid sync type. Must be "full" or "incremental"'
                    });
                }

                if (!this.syncScheduler) {
                    return res.status(503).json({
                        success: false,
                        error: 'Sync scheduler not initialized'
                    });
                }

                const result = await this.syncScheduler.triggerManualSync(syncType);

                if (result.success) {
                    this.logger.info('Manual sync triggered', {
                        syncType,
                        jobId: result.jobId,
                        timestamp: new Date().toISOString()
                    });

                    res.json({
                        success: true,
                        message: result.message,
                        jobId: result.jobId,
                        syncType,
                        timestamp: new Date().toISOString()
                    });
                } else {
                    res.status(500).json({
                        success: false,
                        error: result.error
                    });
                }

            } catch (error) {
                this.logger.error('Failed to trigger manual sync', error);
                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to trigger manual sync'
                });
            }
        });

        // Sync status endpoint
        this.app.get('/api/sync/status', async (req, res) => {
            try {
                if (!this.syncScheduler) {
                    return res.status(503).json({
                        success: false,
                        error: 'Sync scheduler not initialized'
                    });
                }

                const queueStats = await this.syncScheduler.getQueueStats();
                const lastSyncTime = await this.syncScheduler.getLastSyncTime();

                res.json({
                    success: true,
                    data: {
                        queueStats,
                        lastSyncTime,
                        timestamp: new Date().toISOString()
                    }
                });

            } catch (error) {
                this.logger.error('Failed to get sync status', error);
                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to get sync status'
                });
            }
        });

        // Root endpoint
        this.app.get('/', (req, res) => {
            res.json({
                service: 'Zoho Sync Service',
                version: '1.0.0',
                description: 'Microservice for syncing Zoho Desk tickets to local database',
                endpoints: {
                    health: '/health',
                    tickets: '/api/tickets',
                    syncTrigger: '/api/sync/trigger',
                    syncStatus: '/api/sync/status'
                },
                timestamp: new Date().toISOString()
            });
        });
    }

    setupErrorHandling() {
        // 404 handler
        this.app.use('*', (req, res) => {
            res.status(404).json({
                success: false,
                error: 'Not found',
                message: `Route ${req.method} ${req.originalUrl} not found`,
                timestamp: new Date().toISOString()
            });
        });

        // Global error handler
        this.app.use((error, req, res, next) => {
            this.logger.error('Unhandled error', {
                error: error.message,
                stack: error.stack,
                url: req.url,
                method: req.method,
                timestamp: new Date().toISOString()
            });

            res.status(500).json({
                success: false,
                error: 'Internal server error',
                message: process.env.NODE_ENV === 'development' ? error.message : 'Something went wrong',
                timestamp: new Date().toISOString()
            });
        });
    }

    async initialize() {
        try {
            this.logger.info('Initializing Zoho Sync Service...');

            // Initialize database
            this.database = new Database(this.config);
            const dbConnected = await this.database.testConnection();
            
            if (!dbConnected) {
                throw new Error('Failed to connect to database');
            }

            this.logger.info('Database connected successfully');

            // Initialize tickets API
            this.ticketsAPI = new TicketsAPI(this.database, this.config);
            this.app.use('/api/tickets', this.ticketsAPI.getRouter());

            this.logger.info('Tickets API initialized');

            // Initialize sync scheduler
            this.syncScheduler = new SyncScheduler(this.config);
            
            // Start recurring sync
            await this.syncScheduler.scheduleRecurringSync();
            
            this.logger.info('Sync scheduler initialized and started');

            this.logger.info('Zoho Sync Service initialized successfully');

        } catch (error) {
            this.logger.error('Failed to initialize service', error);
            throw error;
        }
    }

    async start() {
        try {
            await this.initialize();

            const port = this.config.PORT || 3001;
            
            this.server = this.app.listen(port, () => {
                this.logger.info(`Zoho Sync Service started on port ${port}`, {
                    port,
                    environment: this.config.NODE_ENV || 'development',
                    timestamp: new Date().toISOString()
                });
            });

            // Graceful shutdown handling
            process.on('SIGTERM', () => this.shutdown('SIGTERM'));
            process.on('SIGINT', () => this.shutdown('SIGINT'));

        } catch (error) {
            this.logger.error('Failed to start service', error);
            process.exit(1);
        }
    }

    async shutdown(signal) {
        this.logger.info(`Received ${signal}, shutting down gracefully...`);

        try {
            // Close HTTP server
            if (this.server) {
                await new Promise((resolve) => {
                    this.server.close(resolve);
                });
                this.logger.info('HTTP server closed');
            }

            // Close sync scheduler
            if (this.syncScheduler) {
                await this.syncScheduler.close();
                this.logger.info('Sync scheduler closed');
            }

            // Close database connection
            if (this.database) {
                await this.database.close();
                this.logger.info('Database connection closed');
            }

            this.logger.info('Graceful shutdown completed');
            process.exit(0);

        } catch (error) {
            this.logger.error('Error during shutdown', error);
            process.exit(1);
        }
    }
}

// Start the service if this file is run directly
if (require.main === module) {
    const service = new ZohoSyncService();
    service.start().catch((error) => {
        console.error('Failed to start service:', error);
        process.exit(1);
    });
}

module.exports = ZohoSyncService;







