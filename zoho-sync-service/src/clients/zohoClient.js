const axios = require('axios');
const winston = require('winston');

class ZohoClient {
    constructor(config) {
        this.clientId = config.ZOHO_CLIENT_ID;
        this.clientSecret = config.ZOHO_CLIENT_SECRET;
        this.refreshToken = config.ZOHO_REFRESH_TOKEN;
        this.orgId = config.ZOHO_ORG_ID;
        this.baseUrl = config.ZOHO_DESK_URL;
        this.accessToken = null;
        this.tokenExpiry = null;
        
        this.logger = winston.createLogger({
            level: 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.Console(),
                new winston.transports.File({ filename: 'logs/zoho-client.log' })
            ]
        });

        this.httpClient = axios.create({
            timeout: 30000,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        // Add request interceptor for logging
        this.httpClient.interceptors.request.use(
            (config) => {
                this.logger.info('Zoho API Request', {
                    method: config.method,
                    url: config.url,
                    timestamp: new Date().toISOString()
                });
                return config;
            },
            (error) => {
                this.logger.error('Zoho API Request Error', error);
                return Promise.reject(error);
            }
        );

        // Add response interceptor for logging
        this.httpClient.interceptors.response.use(
            (response) => {
                this.logger.info('Zoho API Response', {
                    status: response.status,
                    url: response.config.url,
                    timestamp: new Date().toISOString()
                });
                return response;
            },
            (error) => {
                this.logger.error('Zoho API Response Error', {
                    status: error.response?.status,
                    message: error.message,
                    url: error.config?.url,
                    timestamp: new Date().toISOString()
                });
                return Promise.reject(error);
            }
        );
    }

    async getAccessToken() {
        try {
            if (this.accessToken && this.tokenExpiry && new Date() < this.tokenExpiry) {
                return this.accessToken;
            }

            const response = await this.httpClient.post('https://accounts.zoho.com/oauth/v2/token', {
                refresh_token: this.refreshToken,
                client_id: this.clientId,
                client_secret: this.clientSecret,
                grant_type: 'refresh_token'
            });

            this.accessToken = response.data.access_token;
            this.tokenExpiry = new Date(Date.now() + (response.data.expires_in * 1000) - 60000); // 1 minute buffer

            this.logger.info('Access token refreshed successfully');
            return this.accessToken;
        } catch (error) {
            this.logger.error('Failed to get access token', error);
            throw new Error(`Failed to get access token: ${error.message}`);
        }
    }

    async makeRequest(method, endpoint, data = null, params = {}) {
        const token = await this.getAccessToken();
        
        const config = {
            method,
            url: `${this.baseUrl}/api/v1/${endpoint}`,
            headers: {
                'Authorization': `Zoho-oauthtoken ${token}`,
                'orgId': this.orgId
            },
            params
        };

        if (data) {
            config.data = data;
        }

        return this.httpClient(config);
    }

    async getTickets(options = {}) {
        const {
            from = 0,
            limit = 100,
            updatedSince = null,
            status = null,
            department = null,
            owner = null
        } = options;

        const params = {
            from,
            limit,
            sortBy: 'modifiedTime',
            sortOrder: 'desc'
        };

        if (updatedSince) {
            params.modifiedTime = updatedSince;
        }

        if (status) {
            params.status = status;
        }

        if (department) {
            params.department = department;
        }

        if (owner) {
            params.assignee = owner;
        }

        try {
            const response = await this.makeRequest('GET', 'tickets', null, params);
            return response.data;
        } catch (error) {
            this.logger.error('Failed to fetch tickets', {
                error: error.message,
                params,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getAllTickets(options = {}) {
        const allTickets = [];
        let from = options.from || 0;
        const limit = options.limit || 100;
        let hasMore = true;

        this.logger.info('Starting to fetch all tickets', {
            updatedSince: options.updatedSince,
            timestamp: new Date().toISOString()
        });

        while (hasMore) {
            try {
                const response = await this.getTickets({
                    ...options,
                    from,
                    limit
                });

                const tickets = response.data || [];
                allTickets.push(...tickets);

                this.logger.info(`Fetched ${tickets.length} tickets`, {
                    totalFetched: allTickets.length,
                    from,
                    limit,
                    timestamp: new Date().toISOString()
                });

                // Check if there are more tickets
                hasMore = tickets.length === limit;
                from += limit;

                // Add delay to respect rate limits
                if (hasMore) {
                    await this.delay(1000); // 1 second delay
                }

            } catch (error) {
                this.logger.error('Error fetching tickets batch', {
                    error: error.message,
                    from,
                    limit,
                    timestamp: new Date().toISOString()
                });
                throw error;
            }
        }

        this.logger.info('Completed fetching all tickets', {
            totalTickets: allTickets.length,
            timestamp: new Date().toISOString()
        });

        return allTickets;
    }

    async getTicketById(ticketId) {
        try {
            const response = await this.makeRequest('GET', `tickets/${ticketId}`);
            return response.data;
        } catch (error) {
            this.logger.error('Failed to fetch ticket by ID', {
                ticketId,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getDepartments() {
        try {
            const response = await this.makeRequest('GET', 'departments');
            return response.data;
        } catch (error) {
            this.logger.error('Failed to fetch departments', {
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    async getAgents() {
        try {
            const response = await this.makeRequest('GET', 'agents');
            return response.data;
        } catch (error) {
            this.logger.error('Failed to fetch agents', {
                error: error.message,
                timestamp: new Date().toISOString()
            });
            throw error;
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async retryWithBackoff(fn, maxAttempts = 3, baseDelay = 1000) {
        let lastError;
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                return await fn();
            } catch (error) {
                lastError = error;
                
                if (attempt === maxAttempts) {
                    this.logger.error(`All ${maxAttempts} attempts failed`, {
                        error: error.message,
                        timestamp: new Date().toISOString()
                    });
                    throw error;
                }

                const delay = baseDelay * Math.pow(2, attempt - 1);
                this.logger.warn(`Attempt ${attempt} failed, retrying in ${delay}ms`, {
                    error: error.message,
                    nextAttempt: attempt + 1,
                    timestamp: new Date().toISOString()
                });

                await this.delay(delay);
            }
        }
        
        throw lastError;
    }
}

module.exports = ZohoClient;





