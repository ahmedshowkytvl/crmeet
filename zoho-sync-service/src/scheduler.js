require('dotenv').config();
const SyncScheduler = require('./src/scheduler/syncScheduler');
const winston = require('winston');

// Setup logger
const logger = winston.createLogger({
    level: process.env.LOG_LEVEL || 'info',
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
        new winston.transports.File({ filename: 'logs/scheduler.log' })
    ]
});

async function startScheduler() {
    try {
        logger.info('Starting Zoho Sync Scheduler...');
        
        const scheduler = new SyncScheduler(process.env);
        
        // Start recurring sync
        await scheduler.scheduleRecurringSync();
        
        logger.info('Scheduler started successfully');
        
        // Keep the process running
        process.on('SIGTERM', async () => {
            logger.info('Received SIGTERM, shutting down scheduler...');
            await scheduler.close();
            process.exit(0);
        });
        
        process.on('SIGINT', async () => {
            logger.info('Received SIGINT, shutting down scheduler...');
            await scheduler.close();
            process.exit(0);
        });
        
    } catch (error) {
        logger.error('Failed to start scheduler', error);
        process.exit(1);
    }
}

// Start if this file is run directly
if (require.main === module) {
    startScheduler();
}

module.exports = startScheduler;







