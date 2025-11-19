# Zoho Sync Service

A reliable microservice that automatically syncs Zoho Desk tickets to a local database every 10 minutes. This service reduces API rate limit issues, eliminates heavy direct calls to Zoho, and improves performance for employees accessing ticket data.

## Features

- 🔄 **Automatic Sync**: Syncs tickets every 10 minutes (configurable)
- 🚀 **High Performance**: Local database queries instead of API calls
- 🔒 **Rate Limit Protection**: Built-in retry logic with exponential backoff
- 📊 **Comprehensive API**: RESTful endpoints with filtering and pagination
- 🏥 **Health Monitoring**: Built-in health checks and monitoring
- 📝 **Detailed Logging**: Comprehensive logging with Winston
- 🐳 **Docker Ready**: Complete Docker setup with PostgreSQL and Redis
- 🧪 **Tested**: Unit and integration tests included

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Zoho Desk     │───▶│  Sync Service   │───▶│  Local Database │
│     API         │    │   (Node.js)     │    │  (PostgreSQL)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │     Redis       │
                       │   (BullMQ)      │
                       └─────────────────┘
```

## Quick Start

### Prerequisites

- Node.js 18+
- PostgreSQL 12+
- Redis 6+
- Zoho Desk API credentials

### Installation

1. **Clone and install dependencies:**
```bash
git clone <repository-url>
cd zoho-sync-service
npm install
```

2. **Configure environment:**
```bash
cp .env.example .env
# Edit .env with your configuration
```

3. **Start with Docker Compose:**
```bash
docker-compose up -d
```

4. **Or start manually:**
```bash
# Start PostgreSQL and Redis
# Then start the service
npm start
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | PostgreSQL host | localhost |
| `DB_PORT` | PostgreSQL port | 5432 |
| `DB_NAME` | Database name | zoho_sync |
| `DB_USER` | Database user | postgres |
| `DB_PASSWORD` | Database password | - |
| `REDIS_HOST` | Redis host | localhost |
| `REDIS_PORT` | Redis port | 6379 |
| `ZOHO_CLIENT_ID` | Zoho OAuth client ID | - |
| `ZOHO_CLIENT_SECRET` | Zoho OAuth client secret | - |
| `ZOHO_REFRESH_TOKEN` | Zoho refresh token | - |
| `ZOHO_ORG_ID` | Zoho organization ID | - |
| `SYNC_INTERVAL_MINUTES` | Sync interval in minutes | 10 |
| `PORT` | Service port | 3001 |

### Zoho API Setup

1. Go to [Zoho API Console](https://api-console.zoho.com/)
2. Create a new application
3. Generate OAuth credentials
4. Get refresh token using OAuth flow
5. Configure the environment variables

## API Endpoints

### Health Check
```http
GET /health
```

### Tickets
```http
GET /api/tickets
GET /api/tickets/:id
GET /api/tickets/stats/summary
GET /api/tickets/meta/departments
GET /api/tickets/meta/assignees
GET /api/tickets/meta/statuses
```

### Sync Management
```http
POST /api/sync/trigger
GET /api/sync/status
```

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | number | Page number (default: 1) |
| `limit` | number | Items per page (default: 50, max: 100) |
| `status` | string | Filter by status (Open, Closed, Pending, etc.) |
| `department` | string | Filter by department ID |
| `assignee` | string | Filter by assignee ID |
| `priority` | string | Filter by priority (High, Medium, Low, Critical) |
| `dateFrom` | string | Filter from date (ISO format) |
| `dateTo` | string | Filter to date (ISO format) |
| `search` | string | Search in subject and description |
| `sortBy` | string | Sort field (created_time, modified_time, status, etc.) |
| `sortOrder` | string | Sort order (asc, desc) |

### Example Requests

```bash
# Get all tickets
curl "http://localhost:3001/api/tickets"

# Get tickets with filters
curl "http://localhost:3001/api/tickets?status=Open&page=1&limit=10"

# Get specific ticket
curl "http://localhost:3001/api/tickets/12345"

# Get ticket statistics
curl "http://localhost:3001/api/tickets/stats/summary"

# Trigger manual sync
curl -X POST "http://localhost:3001/api/sync/trigger" \
  -H "Content-Type: application/json" \
  -d '{"syncType": "full"}'
```

## Database Schema

### Tickets Table
```sql
CREATE TABLE tickets (
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
```

### Sync Logs Table
```sql
CREATE TABLE sync_logs (
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
```

## Development

### Running Tests
```bash
npm test
npm run test:watch
```

### Development Mode
```bash
npm run dev
```

### Code Structure
```
src/
├── api/           # API routes and controllers
├── clients/       # External API clients
├── database/      # Database layer and models
├── scheduler/     # Background job scheduling
└── server.js      # Main application entry point
```

## Monitoring

### Health Check
The service provides a comprehensive health check endpoint that monitors:
- Database connectivity
- Redis connectivity
- Queue status
- Last sync time
- Service uptime and memory usage

### Logging
Logs are written to:
- Console (with colors in development)
- `logs/app.log` (all logs)
- `logs/error.log` (errors only)
- `logs/zoho-client.log` (Zoho API calls)
- `logs/database.log` (database queries)
- `logs/sync-scheduler.log` (sync operations)

### Metrics
The service tracks:
- Sync job duration and success rate
- Tickets processed per sync
- API response times
- Error rates and types

## Deployment

### Docker
```bash
# Build and run
docker build -t zoho-sync-service .
docker run -p 3001:3001 --env-file .env zoho-sync-service

# Or use docker-compose
docker-compose up -d
```

### Production Considerations

1. **Environment Variables**: Use secure environment variable management
2. **Database**: Use managed PostgreSQL service (AWS RDS, Google Cloud SQL, etc.)
3. **Redis**: Use managed Redis service (AWS ElastiCache, Google Cloud Memorystore, etc.)
4. **Monitoring**: Set up monitoring and alerting for:
   - Service health
   - Sync job failures
   - High error rates
   - Database performance
5. **Backups**: Regular database backups
6. **Scaling**: Consider horizontal scaling for high-volume scenarios

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check PostgreSQL is running
   - Verify connection credentials
   - Check network connectivity

2. **Zoho API Authentication Failed**
   - Verify OAuth credentials
   - Check refresh token validity
   - Ensure proper API permissions

3. **Sync Jobs Failing**
   - Check Redis connectivity
   - Review error logs
   - Verify Zoho API rate limits

4. **High Memory Usage**
   - Monitor batch sizes
   - Check for memory leaks
   - Consider reducing sync frequency

### Debug Mode
Set `LOG_LEVEL=debug` for detailed logging.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

MIT License - see LICENSE file for details.

## Support

For issues and questions:
- Create an issue in the repository
- Check the logs for error details
- Review the health check endpoint
- Consult the troubleshooting section







