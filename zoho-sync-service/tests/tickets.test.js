const request = require('supertest');
const ZohoSyncService = require('../src/server');
const { Database, TicketRepository } = require('../src/database/database');
const ZohoClient = require('../src/clients/zohoClient');

// Mock the external dependencies
jest.mock('../src/clients/zohoClient');
jest.mock('../src/database/database');

describe('Zoho Sync Service', () => {
    let app;
    let mockDatabase;
    let mockTicketRepository;

    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();

        // Mock database
        mockDatabase = {
            testConnection: jest.fn().mockResolvedValue(true),
            query: jest.fn(),
            close: jest.fn()
        };

        // Mock ticket repository
        mockTicketRepository = {
            createTables: jest.fn(),
            upsertTicket: jest.fn(),
            getTickets: jest.fn(),
            getTicketById: jest.fn(),
            getLastSyncTime: jest.fn(),
            createSyncLog: jest.fn(),
            updateSyncLog: jest.fn(),
            getSyncStats: jest.fn(),
            db: mockDatabase
        };

        Database.mockImplementation(() => mockDatabase);
        TicketRepository.mockImplementation(() => mockTicketRepository);

        // Create app instance
        const service = new ZohoSyncService();
        app = service.app;
    });

    describe('Health Check', () => {
        it('should return healthy status', async () => {
            mockTicketRepository.getLastSyncTime.mockResolvedValue(new Date());

            const response = await request(app)
                .get('/health')
                .expect(200);

            expect(response.body.status).toBe('healthy');
            expect(response.body.database).toBe('connected');
        });

        it('should return unhealthy status when database is disconnected', async () => {
            mockDatabase.testConnection.mockResolvedValue(false);

            const response = await request(app)
                .get('/health')
                .expect(503);

            expect(response.body.status).toBe('unhealthy');
        });
    });

    describe('Tickets API', () => {
        beforeEach(() => {
            mockTicketRepository.getTickets.mockResolvedValue({
                tickets: [
                    {
                        id: 1,
                        zoho_ticket_id: '12345',
                        subject: 'Test Ticket',
                        status: 'Open',
                        created_time: new Date()
                    }
                ],
                pagination: {
                    page: 1,
                    limit: 50,
                    totalCount: 1,
                    totalPages: 1,
                    hasNext: false,
                    hasPrev: false
                }
            });
        });

        it('should get tickets with default pagination', async () => {
            const response = await request(app)
                .get('/api/tickets')
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(response.body.data).toHaveLength(1);
            expect(response.body.data[0].subject).toBe('Test Ticket');
        });

        it('should get tickets with filters', async () => {
            const response = await request(app)
                .get('/api/tickets?status=Open&page=1&limit=10')
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(mockTicketRepository.getTickets).toHaveBeenCalledWith(
                { status: 'Open' },
                { page: 1, limit: 10, sortBy: 'modified_time', sortOrder: 'desc' }
            );
        });

        it('should validate query parameters', async () => {
            const response = await request(app)
                .get('/api/tickets?page=invalid&limit=200')
                .expect(400);

            expect(response.body.success).toBe(false);
            expect(response.body.error).toBe('Validation error');
        });

        it('should get specific ticket by ID', async () => {
            mockTicketRepository.getTicketById.mockResolvedValue({
                id: 1,
                zoho_ticket_id: '12345',
                subject: 'Test Ticket',
                status: 'Open'
            });

            const response = await request(app)
                .get('/api/tickets/12345')
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(response.body.data.zoho_ticket_id).toBe('12345');
        });

        it('should return 404 for non-existent ticket', async () => {
            mockTicketRepository.getTicketById.mockResolvedValue(null);

            const response = await request(app)
                .get('/api/tickets/nonexistent')
                .expect(404);

            expect(response.body.success).toBe(false);
            expect(response.body.error).toBe('Ticket not found');
        });

        it('should get ticket statistics', async () => {
            mockTicketRepository.getSyncStats.mockResolvedValue({
                total_tickets: '100',
                open_tickets: '20',
                closed_tickets: '70',
                pending_tickets: '10',
                last_sync_time: new Date()
            });

            const response = await request(app)
                .get('/api/tickets/stats/summary')
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(response.body.data.totalTickets).toBe(100);
            expect(response.body.data.openTickets).toBe(20);
        });
    });

    describe('Sync Management', () => {
        it('should trigger manual sync', async () => {
            // Mock the sync scheduler
            const mockSyncScheduler = {
                triggerManualSync: jest.fn().mockResolvedValue({
                    success: true,
                    jobId: 'test-job-123',
                    message: 'Manual sync triggered successfully'
                })
            };

            // We need to mock the service initialization
            jest.spyOn(ZohoSyncService.prototype, 'initialize').mockImplementation(async function() {
                this.syncScheduler = mockSyncScheduler;
            });

            const response = await request(app)
                .post('/api/sync/trigger')
                .send({ syncType: 'full' })
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(response.body.jobId).toBe('test-job-123');
        });

        it('should validate sync type', async () => {
            const response = await request(app)
                .post('/api/sync/trigger')
                .send({ syncType: 'invalid' })
                .expect(400);

            expect(response.body.success).toBe(false);
            expect(response.body.error).toContain('Invalid sync type');
        });
    });

    describe('Error Handling', () => {
        it('should handle 404 for unknown routes', async () => {
            const response = await request(app)
                .get('/unknown-route')
                .expect(404);

            expect(response.body.success).toBe(false);
            expect(response.body.error).toBe('Not found');
        });

        it('should handle database errors gracefully', async () => {
            mockTicketRepository.getTickets.mockRejectedValue(new Error('Database connection failed'));

            const response = await request(app)
                .get('/api/tickets')
                .expect(500);

            expect(response.body.success).toBe(false);
            expect(response.body.error).toBe('Internal server error');
        });
    });
});

describe('ZohoClient', () => {
    let zohoClient;
    let mockAxios;

    beforeEach(() => {
        const config = {
            ZOHO_CLIENT_ID: 'test-client-id',
            ZOHO_CLIENT_SECRET: 'test-client-secret',
            ZOHO_REFRESH_TOKEN: 'test-refresh-token',
            ZOHO_ORG_ID: 'test-org-id',
            ZOHO_DESK_URL: 'https://desk.zoho.com'
        };

        zohoClient = new ZohoClient(config);
        
        // Mock axios
        mockAxios = {
            post: jest.fn(),
            get: jest.fn(),
            interceptors: {
                request: { use: jest.fn() },
                response: { use: jest.fn() }
            }
        };

        zohoClient.httpClient = mockAxios;
    });

    it('should get access token successfully', async () => {
        mockAxios.post.mockResolvedValue({
            data: {
                access_token: 'test-access-token',
                expires_in: 3600
            }
        });

        const token = await zohoClient.getAccessToken();
        
        expect(token).toBe('test-access-token');
        expect(mockAxios.post).toHaveBeenCalledWith(
            'https://accounts.zoho.com/oauth/v2/token',
            expect.objectContaining({
                refresh_token: 'test-refresh-token',
                client_id: 'test-client-id',
                client_secret: 'test-client-secret',
                grant_type: 'refresh_token'
            })
        );
    });

    it('should handle token refresh errors', async () => {
        mockAxios.post.mockRejectedValue(new Error('Token refresh failed'));

        await expect(zohoClient.getAccessToken()).rejects.toThrow('Failed to get access token');
    });

    it('should fetch tickets with retry logic', async () => {
        mockAxios.get.mockResolvedValue({
            data: {
                data: [
                    { id: '1', subject: 'Test Ticket 1' },
                    { id: '2', subject: 'Test Ticket 2' }
                ]
            }
        });

        const tickets = await zohoClient.getTickets({ limit: 10 });
        
        expect(tickets.data).toHaveLength(2);
        expect(tickets.data[0].subject).toBe('Test Ticket 1');
    });
});

describe('TicketRepository', () => {
    let ticketRepository;
    let mockDatabase;

    beforeEach(() => {
        mockDatabase = {
            query: jest.fn(),
            testConnection: jest.fn().mockResolvedValue(true)
        };

        ticketRepository = new TicketRepository(mockDatabase);
    });

    it('should create tables successfully', async () => {
        mockDatabase.query.mockResolvedValue({ rows: [] });

        await ticketRepository.createTables();

        expect(mockDatabase.query).toHaveBeenCalledTimes(3); // tickets table, indexes, sync_logs table
    });

    it('should upsert ticket successfully', async () => {
        const ticketData = {
            id: '12345',
            ticketNumber: 'TKT-001',
            subject: 'Test Ticket',
            status: 'Open',
            createdTime: new Date(),
            modifiedTime: new Date()
        };

        mockDatabase.query.mockResolvedValue({
            rows: [{ id: 1, zoho_ticket_id: '12345', updated_at: new Date() }]
        });

        const result = await ticketRepository.upsertTicket(ticketData);

        expect(result.id).toBe(1);
        expect(result.zoho_ticket_id).toBe('12345');
    });

    it('should get tickets with pagination', async () => {
        const mockTickets = [
            { id: 1, zoho_ticket_id: '12345', subject: 'Test Ticket' }
        ];

        mockDatabase.query
            .mockResolvedValueOnce({ rows: [{ count: '1' }] }) // count query
            .mockResolvedValueOnce({ rows: mockTickets }); // data query

        const result = await ticketRepository.getTickets({}, { page: 1, limit: 50 });

        expect(result.tickets).toHaveLength(1);
        expect(result.pagination.totalCount).toBe(1);
        expect(result.pagination.page).toBe(1);
    });
});







