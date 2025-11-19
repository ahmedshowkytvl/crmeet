const express = require('express');
const Joi = require('joi');
const winston = require('winston');
const { TicketRepository } = require('../database/database');

class TicketsAPI {
    constructor(database, config) {
        this.router = express.Router();
        this.ticketRepository = new TicketRepository(database);
        this.config = config;

        this.logger = winston.createLogger({
            level: config.LOG_LEVEL || 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/tickets-api.log' })
            ]
        });

        this.setupRoutes();
        this.setupValidation();
    }

    setupValidation() {
        this.ticketsQuerySchema = Joi.object({
            page: Joi.number().integer().min(1).default(1),
            limit: Joi.number().integer().min(1).max(100).default(50),
            status: Joi.string().valid('Open', 'Closed', 'Pending', 'In Progress', 'Resolved'),
            department: Joi.string(),
            assignee: Joi.string(),
            priority: Joi.string().valid('High', 'Medium', 'Low', 'Critical'),
            dateFrom: Joi.date().iso(),
            dateTo: Joi.date().iso(),
            search: Joi.string().max(255),
            sortBy: Joi.string().valid('created_time', 'modified_time', 'status', 'priority', 'subject').default('modified_time'),
            sortOrder: Joi.string().valid('asc', 'desc').default('desc')
        });
    }

    setupRoutes() {
        // GET /tickets - Get all tickets with filters and pagination
        this.router.get('/', async (req, res) => {
            try {
                const { error, value } = this.ticketsQuerySchema.validate(req.query);
                if (error) {
                    return res.status(400).json({
                        success: false,
                        error: 'Validation error',
                        details: error.details.map(d => d.message)
                    });
                }

                const filters = {
                    status: value.status,
                    department: value.department,
                    assignee: value.assignee,
                    priority: value.priority,
                    dateFrom: value.dateFrom,
                    dateTo: value.dateTo,
                    search: value.search
                };

                const pagination = {
                    page: value.page,
                    limit: value.limit,
                    sortBy: value.sortBy,
                    sortOrder: value.sortOrder
                };

                const result = await this.ticketRepository.getTickets(filters, pagination);

                this.logger.info('Tickets fetched successfully', {
                    filters,
                    pagination,
                    resultCount: result.tickets.length,
                    timestamp: new Date().toISOString()
                });

                res.json({
                    success: true,
                    data: result.tickets,
                    pagination: result.pagination,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                this.logger.error('Failed to fetch tickets', {
                    error: error.message,
                    query: req.query,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch tickets'
                });
            }
        });

        // GET /tickets/:id - Get specific ticket by Zoho ID
        this.router.get('/:id', async (req, res) => {
            try {
                const ticketId = req.params.id;
                
                if (!ticketId) {
                    return res.status(400).json({
                        success: false,
                        error: 'Ticket ID is required'
                    });
                }

                const ticket = await this.ticketRepository.getTicketById(ticketId);

                if (!ticket) {
                    return res.status(404).json({
                        success: false,
                        error: 'Ticket not found'
                    });
                }

                this.logger.info('Ticket fetched successfully', {
                    ticketId,
                    timestamp: new Date().toISOString()
                });

                res.json({
                    success: true,
                    data: ticket,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                this.logger.error('Failed to fetch ticket', {
                    ticketId: req.params.id,
                    error: error.message,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch ticket'
                });
            }
        });

        // GET /tickets/stats/summary - Get ticket statistics
        this.router.get('/stats/summary', async (req, res) => {
            try {
                const stats = await this.ticketRepository.getSyncStats();

                this.logger.info('Ticket stats fetched successfully', {
                    timestamp: new Date().toISOString()
                });

                res.json({
                    success: true,
                    data: {
                        totalTickets: parseInt(stats.total_tickets) || 0,
                        openTickets: parseInt(stats.open_tickets) || 0,
                        closedTickets: parseInt(stats.closed_tickets) || 0,
                        pendingTickets: parseInt(stats.pending_tickets) || 0,
                        lastSyncTime: stats.last_sync_time,
                        timestamp: new Date().toISOString()
                    }
                });

            } catch (error) {
                this.logger.error('Failed to fetch ticket stats', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch ticket statistics'
                });
            }
        });

        // GET /tickets/departments - Get unique departments
        this.router.get('/meta/departments', async (req, res) => {
            try {
                const query = `
                    SELECT DISTINCT department_id, department_name 
                    FROM tickets 
                    WHERE department_id IS NOT NULL 
                    ORDER BY department_name
                `;
                
                const result = await this.ticketRepository.db.query(query);
                const departments = result.rows.map(row => ({
                    id: row.department_id,
                    name: row.department_name
                }));

                res.json({
                    success: true,
                    data: departments,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                this.logger.error('Failed to fetch departments', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch departments'
                });
            }
        });

        // GET /tickets/assignees - Get unique assignees
        this.router.get('/meta/assignees', async (req, res) => {
            try {
                const query = `
                    SELECT DISTINCT assignee_id, assignee_name, assignee_email 
                    FROM tickets 
                    WHERE assignee_id IS NOT NULL 
                    ORDER BY assignee_name
                `;
                
                const result = await this.ticketRepository.db.query(query);
                const assignees = result.rows.map(row => ({
                    id: row.assignee_id,
                    name: row.assignee_name,
                    email: row.assignee_email
                }));

                res.json({
                    success: true,
                    data: assignees,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                this.logger.error('Failed to fetch assignees', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch assignees'
                });
            }
        });

        // GET /tickets/statuses - Get unique statuses
        this.router.get('/meta/statuses', async (req, res) => {
            try {
                const query = `
                    SELECT DISTINCT status, COUNT(*) as count 
                    FROM tickets 
                    WHERE status IS NOT NULL 
                    GROUP BY status 
                    ORDER BY status
                `;
                
                const result = await this.ticketRepository.db.query(query);
                const statuses = result.rows.map(row => ({
                    status: row.status,
                    count: parseInt(row.count)
                }));

                res.json({
                    success: true,
                    data: statuses,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                this.logger.error('Failed to fetch statuses', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });

                res.status(500).json({
                    success: false,
                    error: 'Internal server error',
                    message: 'Failed to fetch statuses'
                });
            }
        });
    }

    getRouter() {
        return this.router;
    }
}

module.exports = TicketsAPI;







