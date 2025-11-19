const { Database, TicketRepository } = require('./src/database/database');
const winston = require('winston');

class AnalyticsService {
    constructor(config) {
        this.database = new Database(config);
        this.ticketRepository = new TicketRepository(this.database);
        
        this.logger = winston.createLogger({
            level: 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/analytics.log' })
            ]
        });
    }

    async getTicketAnalytics() {
        try {
            const queries = {
                // Overall statistics
                totalTickets: 'SELECT COUNT(*) as count FROM tickets',
                openTickets: 'SELECT COUNT(*) as count FROM tickets WHERE status = \'Open\'',
                closedTickets: 'SELECT COUNT(*) as count FROM tickets WHERE status = \'Closed\'',
                pendingTickets: 'SELECT COUNT(*) as count FROM tickets WHERE status = \'Pending\'',
                
                // Performance metrics
                avgResponseTime: 'SELECT AVG(response_time) as avg FROM tickets WHERE response_time IS NOT NULL',
                avgResolutionTime: 'SELECT AVG(resolution_time) as avg FROM tickets WHERE resolution_time IS NOT NULL',
                
                // Department breakdown
                ticketsByDepartment: `
                    SELECT department_name, COUNT(*) as count 
                    FROM tickets 
                    WHERE department_name IS NOT NULL 
                    GROUP BY department_name 
                    ORDER BY count DESC
                `,
                
                // Assignee performance
                ticketsByAssignee: `
                    SELECT assignee_name, COUNT(*) as total_tickets,
                           COUNT(CASE WHEN status = 'Closed' THEN 1 END) as closed_tickets,
                           AVG(response_time) as avg_response_time
                    FROM tickets 
                    WHERE assignee_name IS NOT NULL 
                    GROUP BY assignee_name 
                    ORDER BY total_tickets DESC
                `,
                
                // Time-based analytics
                ticketsByMonth: `
                    SELECT DATE_TRUNC('month', created_time) as month,
                           COUNT(*) as count
                    FROM tickets 
                    WHERE created_time IS NOT NULL
                    GROUP BY DATE_TRUNC('month', created_time)
                    ORDER BY month DESC
                `,
                
                // Priority distribution
                ticketsByPriority: `
                    SELECT priority, COUNT(*) as count 
                    FROM tickets 
                    WHERE priority IS NOT NULL 
                    GROUP BY priority 
                    ORDER BY count DESC
                `,
                
                // Resolution time analysis
                resolutionTimeAnalysis: `
                    SELECT 
                        CASE 
                            WHEN resolution_time < 3600 THEN '< 1 hour'
                            WHEN resolution_time < 86400 THEN '1-24 hours'
                            WHEN resolution_time < 604800 THEN '1-7 days'
                            ELSE '> 7 days'
                        END as time_range,
                        COUNT(*) as count
                    FROM tickets 
                    WHERE resolution_time IS NOT NULL
                    GROUP BY 
                        CASE 
                            WHEN resolution_time < 3600 THEN '< 1 hour'
                            WHEN resolution_time < 86400 THEN '1-24 hours'
                            WHEN resolution_time < 604800 THEN '1-7 days'
                            ELSE '> 7 days'
                        END
                    ORDER BY count DESC
                `
            };

            const results = {};
            
            for (const [key, query] of Object.entries(queries)) {
                try {
                    const result = await this.database.query(query);
                    results[key] = result.rows;
                } catch (error) {
                    this.logger.error(`Failed to execute query for ${key}`, error);
                    results[key] = [];
                }
            }

            return {
                timestamp: new Date().toISOString(),
                analytics: results
            };

        } catch (error) {
            this.logger.error('Failed to get ticket analytics', error);
            throw error;
        }
    }

    async getSyncAnalytics() {
        try {
            const queries = {
                // Sync performance
                syncStats: `
                    SELECT 
                        sync_type,
                        COUNT(*) as total_syncs,
                        AVG(duration_ms) as avg_duration_ms,
                        AVG(tickets_processed) as avg_tickets_processed,
                        AVG(tickets_created) as avg_tickets_created,
                        AVG(tickets_updated) as avg_tickets_updated,
                        AVG(tickets_errors) as avg_tickets_errors,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_syncs,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_syncs
                    FROM sync_logs 
                    WHERE started_at >= NOW() - INTERVAL '30 days'
                    GROUP BY sync_type
                `,
                
                // Recent sync activity
                recentSyncs: `
                    SELECT 
                        sync_type,
                        started_at,
                        completed_at,
                        status,
                        tickets_processed,
                        tickets_created,
                        tickets_updated,
                        tickets_errors,
                        duration_ms
                    FROM sync_logs 
                    ORDER BY started_at DESC 
                    LIMIT 20
                `,
                
                // Sync success rate by hour
                syncSuccessByHour: `
                    SELECT 
                        EXTRACT(HOUR FROM started_at) as hour,
                        COUNT(*) as total_syncs,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_syncs,
                        ROUND(
                            COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / COUNT(*), 
                            2
                        ) as success_rate
                    FROM sync_logs 
                    WHERE started_at >= NOW() - INTERVAL '7 days'
                    GROUP BY EXTRACT(HOUR FROM started_at)
                    ORDER BY hour
                `
            };

            const results = {};
            
            for (const [key, query] of Object.entries(queries)) {
                try {
                    const result = await this.database.query(query);
                    results[key] = result.rows;
                } catch (error) {
                    this.logger.error(`Failed to execute sync query for ${key}`, error);
                    results[key] = [];
                }
            }

            return {
                timestamp: new Date().toISOString(),
                syncAnalytics: results
            };

        } catch (error) {
            this.logger.error('Failed to get sync analytics', error);
            throw error;
        }
    }

    async generateReport() {
        try {
            this.logger.info('Generating analytics report...');
            
            const ticketAnalytics = await this.getTicketAnalytics();
            const syncAnalytics = await this.getSyncAnalytics();
            
            const report = {
                generatedAt: new Date().toISOString(),
                ticketAnalytics,
                syncAnalytics,
                summary: {
                    totalTickets: ticketAnalytics.analytics.totalTickets[0]?.count || 0,
                    openTickets: ticketAnalytics.analytics.openTickets[0]?.count || 0,
                    closedTickets: ticketAnalytics.analytics.closedTickets[0]?.count || 0,
                    avgResponseTime: ticketAnalytics.analytics.avgResponseTime[0]?.avg || 0,
                    avgResolutionTime: ticketAnalytics.analytics.avgResolutionTime[0]?.avg || 0
                }
            };

            this.logger.info('Analytics report generated successfully', {
                totalTickets: report.summary.totalTickets,
                openTickets: report.summary.openTickets,
                closedTickets: report.summary.closedTickets
            });

            return report;

        } catch (error) {
            this.logger.error('Failed to generate analytics report', error);
            throw error;
        }
    }

    async close() {
        await this.database.close();
    }
}

// CLI usage
if (require.main === module) {
    require('dotenv').config();
    
    const analytics = new AnalyticsService(process.env);
    
    analytics.generateReport()
        .then(report => {
            console.log(JSON.stringify(report, null, 2));
            return analytics.close();
        })
        .catch(error => {
            console.error('Failed to generate report:', error);
            process.exit(1);
        });
}

module.exports = AnalyticsService;







