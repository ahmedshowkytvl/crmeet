const { Queue, Worker } = require('bullmq');
const Redis = require('ioredis');
const ZohoClient = require('../clients/zohoClient');
const { Database, TicketRepository } = require('../database/database');
const winston = require('winston');

class SyncScheduler {
    constructor(config) {
        this.config = config;
        this.redis = new Redis({
            host: config.REDIS_HOST,
            port: config.REDIS_PORT,
            password: config.REDIS_PASSWORD || undefined,
            db: config.REDIS_DB || 0,
            retryDelayOnFailover: 100,
            maxRetriesPerRequest: 3,
        });

        this.queue = new Queue('zoho-sync', {
            connection: this.redis,
            defaultJobOptions: {
                removeOnComplete: 10,
                removeOnFail: 5,
                attempts: 3,
                backoff: {
                    type: 'exponential',
                    delay: 2000,
                },
            },
        });

        this.logger = winston.createLogger({
            level: config.LOG_LEVEL || 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/sync-scheduler.log' })
            ]
        });

        this.zohoClient = new ZohoClient(config);
        this.database = new Database(config);
        this.ticketRepository = new TicketRepository(this.database);

        this.setupWorker();
    }

    setupWorker() {
        this.worker = new Worker('zoho-sync', async (job) => {
            return await this.processSyncJob(job);
        }, {
            connection: this.redis,
            concurrency: 1, // Process one sync job at a time
        });

        this.worker.on('completed', (job) => {
            this.logger.info('Sync job completed', {
                jobId: job.id,
                result: job.returnvalue,
                timestamp: new Date().toISOString()
            });
        });

        this.worker.on('failed', (job, err) => {
            this.logger.error('Sync job failed', {
                jobId: job?.id,
                error: err.message,
                timestamp: new Date().toISOString()
            });
        });

        this.worker.on('error', (err) => {
            this.logger.error('Worker error', {
                error: err.message,
                timestamp: new Date().toISOString()
            });
        });
    }

    async processSyncJob(job) {
        const { syncType = 'full', lastSyncTime = null } = job.data;
        const startTime = Date.now();
        
        this.logger.info('Starting sync job', {
            jobId: job.id,
            syncType,
            lastSyncTime,
            timestamp: new Date().toISOString()
        });

        let syncLogId;
        try {
            // Create sync log entry
            syncLogId = await this.ticketRepository.createSyncLog({
                type: syncType,
                startedAt: new Date(),
                status: 'running',
                metadata: { jobId: job.id }
            });

            // Test database connection
            const dbConnected = await this.database.testConnection();
            if (!dbConnected) {
                throw new Error('Database connection failed');
            }

            // Ensure tables exist
            await this.ticketRepository.createTables();

            let ticketsProcessed = 0;
            let ticketsCreated = 0;
            let ticketsUpdated = 0;
            let ticketsErrors = 0;

            // Fetch tickets from Zoho
            const tickets = await this.zohoClient.getAllTickets({
                updatedSince: lastSyncTime,
                limit: parseInt(this.config.BATCH_SIZE) || 100
            });

            this.logger.info(`Fetched ${tickets.length} tickets from Zoho`, {
                jobId: job.id,
                timestamp: new Date().toISOString()
            });

            // Process tickets in batches
            const batchSize = parseInt(this.config.BATCH_SIZE) || 100;
            for (let i = 0; i < tickets.length; i += batchSize) {
                const batch = tickets.slice(i, i + batchSize);
                
                this.logger.info(`Processing batch ${Math.floor(i / batchSize) + 1}`, {
                    batchSize: batch.length,
                    totalProcessed: ticketsProcessed,
                    jobId: job.id,
                    timestamp: new Date().toISOString()
                });

                for (const ticket of batch) {
                    try {
                        const result = await this.ticketRepository.upsertTicket(ticket);
                        
                        if (result.id) {
                            // Check if this was an update or insert by checking if updated_at equals created_at
                            const isNewTicket = result.updated_at === result.created_at;
                            if (isNewTicket) {
                                ticketsCreated++;
                            } else {
                                ticketsUpdated++;
                            }
                        }
                        
                        ticketsProcessed++;
                        
                        // Update job progress
                        await job.updateProgress(Math.round((ticketsProcessed / tickets.length) * 100));
                        
                    } catch (error) {
                        ticketsErrors++;
                        this.logger.error('Failed to process ticket', {
                            ticketId: ticket.id,
                            error: error.message,
                            jobId: job.id,
                            timestamp: new Date().toISOString()
                        });
                    }
                }

                // Add delay between batches to respect rate limits
                if (i + batchSize < tickets.length) {
                    await this.delay(2000); // 2 second delay between batches
                }
            }

            const duration = Date.now() - startTime;

            // Update sync log
            await this.ticketRepository.updateSyncLog(syncLogId, {
                completedAt: new Date(),
                status: 'completed',
                ticketsProcessed,
                ticketsCreated,
                ticketsUpdated,
                ticketsErrors,
                durationMs: duration
            });

            this.logger.info('Sync job completed successfully', {
                jobId: job.id,
                ticketsProcessed,
                ticketsCreated,
                ticketsUpdated,
                ticketsErrors,
                duration: `${duration}ms`,
                timestamp: new Date().toISOString()
            });

            return {
                success: true,
                ticketsProcessed,
                ticketsCreated,
                ticketsUpdated,
                ticketsErrors,
                duration
            };

        } catch (error) {
            const duration = Date.now() - startTime;
            
            this.logger.error('Sync job failed', {
                jobId: job.id,
                error: error.message,
                duration: `${duration}ms`,
                timestamp: new Date().toISOString()
            });

            // Update sync log with error
            if (syncLogId) {
                await this.ticketRepository.updateSyncLog(syncLogId, {
                    completedAt: new Date(),
                    status: 'failed',
                    errorMessage: error.message,
                    durationMs: duration
                });
            }

            throw error;
        }
    }

    async scheduleSync(syncType = 'full', delay = 0) {
        const jobData = {
            syncType,
            lastSyncTime: syncType === 'incremental' ? await this.getLastSyncTime() : null
        };

        const job = await this.queue.add('sync-tickets', jobData, {
            delay: delay * 1000, // Convert seconds to milliseconds
            jobId: `sync-${syncType}-${Date.now()}`
        });

        this.logger.info('Sync job scheduled', {
            jobId: job.id,
            syncType,
            delay,
            timestamp: new Date().toISOString()
        });

        return job;
    }

    async scheduleRecurringSync() {
        const intervalMinutes = parseInt(this.config.SYNC_INTERVAL_MINUTES) || 10;
        
        // Schedule initial sync
        await this.scheduleSync('full', 0);
        
        // Schedule recurring incremental syncs
        setInterval(async () => {
            try {
                await this.scheduleSync('incremental', 0);
            } catch (error) {
                this.logger.error('Failed to schedule recurring sync', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });
            }
        }, intervalMinutes * 60 * 1000);

        this.logger.info('Recurring sync scheduled', {
            intervalMinutes,
            timestamp: new Date().toISOString()
        });
    }

    async getLastSyncTime() {
        try {
            return await this.ticketRepository.getLastSyncTime();
        } catch (error) {
            this.logger.error('Failed to get last sync time', error);
            return null;
        }
    }

    async getQueueStats() {
        try {
            const waiting = await this.queue.getWaiting();
            const active = await this.queue.getActive();
            const completed = await this.queue.getCompleted();
            const failed = await this.queue.getFailed();

            return {
                waiting: waiting.length,
                active: active.length,
                completed: completed.length,
                failed: failed.length
            };
        } catch (error) {
            this.logger.error('Failed to get queue stats', error);
            return null;
        }
    }

    async triggerManualSync(syncType = 'full') {
        try {
            const job = await this.scheduleSync(syncType, 0);
            return {
                success: true,
                jobId: job.id,
                message: 'Manual sync triggered successfully'
            };
        } catch (error) {
            this.logger.error('Failed to trigger manual sync', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async close() {
        try {
            await this.worker.close();
            await this.queue.close();
            await this.redis.quit();
            await this.database.close();
            
            this.logger.info('Sync scheduler closed successfully');
        } catch (error) {
            this.logger.error('Error closing sync scheduler', error);
        }
    }
}

module.exports = SyncScheduler;
