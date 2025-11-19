import requests
import json
from datetime import datetime, timedelta
from config import ZohoConfig

class ZohoAPI:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        self.token_expires_at = None
        
    def get_access_token(self):
        """
        Get access token using refresh token
        """
        try:
            url = self.config.TOKEN_URL
            
            data = {
                'refresh_token': self.config.REFRESH_TOKEN,
                'client_id': self.config.CLIENT_ID,
                'client_secret': self.config.CLIENT_SECRET,
                'grant_type': 'refresh_token'
            }
            
            response = requests.post(url, data=data)
            response.raise_for_status()
            
            token_data = response.json()
            self.access_token = token_data.get('access_token')
            
            # Calculate expiration time (usually 1 hour)
            expires_in = token_data.get('expires_in', 3600)
            self.token_expires_at = datetime.now() + timedelta(seconds=expires_in)
            
            print(f"Successfully obtained access token")
            return self.access_token
            
        except requests.exceptions.RequestException as e:
            print(f"Error getting access token: {e}")
            return None
    
    def is_token_valid(self):
        """
        Check if token is valid
        """
        if not self.access_token or not self.token_expires_at:
            return False
        return datetime.now() < self.token_expires_at
    
    def ensure_valid_token(self):
        """
        Ensure a valid token exists
        """
        if not self.is_token_valid():
            return self.get_access_token()
        return self.access_token
    
    def make_request(self, method, url, headers=None, data=None, params=None):
        """
        Make HTTP request with automatic token management
        """
        # Ensure valid token exists
        token = self.ensure_valid_token()
        if not token:
            return None
        
        # Setup headers
        if headers is None:
            headers = {}
        
        headers.update({
            'Authorization': f'Zoho-oauthtoken {token}',
            'Content-Type': 'application/json'
        })
        
        try:
            if method.upper() == 'GET':
                response = requests.get(url, headers=headers, params=params)
            elif method.upper() == 'POST':
                response = requests.post(url, headers=headers, json=data, params=params)
            elif method.upper() == 'PUT':
                response = requests.put(url, headers=headers, json=data, params=params)
            elif method.upper() == 'DELETE':
                response = requests.delete(url, headers=headers, params=params)
            else:
                raise ValueError(f"Unsupported HTTP method: {method}")
            
            response.raise_for_status()
            
            # Handle empty responses (status 204 or empty content)
            if response.status_code == 204 or not response.text.strip():
                return {"data": [], "info": {"count": 0}}
            
            return response.json()
            
        except requests.exceptions.RequestException as e:
            print(f"Request error: {e}")
            return None
        except ValueError as e:
            # Handle JSON parsing errors for empty responses
            print(f"JSON parsing error: {e}")
            return {"data": [], "info": {"count": 0}}
    
    # CRM functions
    def get_leads(self, page=1, per_page=200):
        """
        Get leads list from Zoho CRM
        """
        url = f"{self.config.BASE_URLS['crm']}/Leads"
        params = {
            'page': page,
            'per_page': per_page
        }
        return self.make_request('GET', url, params=params)
    
    def get_contacts(self, page=1, per_page=200):
        """
        Get contacts list from Zoho CRM
        """
        url = f"{self.config.BASE_URLS['crm']}/Contacts"
        params = {
            'page': page,
            'per_page': per_page
        }
        return self.make_request('GET', url, params=params)
    
    def get_accounts(self, page=1, per_page=200):
        """
        Get accounts list from Zoho CRM
        """
        url = f"{self.config.BASE_URLS['crm']}/Accounts"
        params = {
            'page': page,
            'per_page': per_page
        }
        return self.make_request('GET', url, params=params)
    
    def create_lead(self, lead_data):
        """
        Create new lead in Zoho CRM
        """
        url = f"{self.config.BASE_URLS['crm']}/Leads"
        data = {'data': [lead_data]}
        return self.make_request('POST', url, data=data)
    
    def create_contact(self, contact_data):
        """
        Create new contact in Zoho CRM
        """
        url = f"{self.config.BASE_URLS['crm']}/Contacts"
        data = {'data': [contact_data]}
        return self.make_request('POST', url, data=data)
    
    # Books functions
    def get_customers(self):
        """
        Get customers list from Zoho Books
        """
        url = f"{self.config.BASE_URLS['books']}/contacts"
        params = {
            'organization_id': self.config.ORG_ID
        }
        return self.make_request('GET', url, params=params)
    
    def get_items(self):
        """
        Get items list from Zoho Books
        """
        url = f"{self.config.BASE_URLS['books']}/items"
        params = {
            'organization_id': self.config.ORG_ID
        }
        return self.make_request('GET', url, params=params)
    
    def create_invoice(self, invoice_data):
        """
        Create new invoice in Zoho Books
        """
        url = f"{self.config.BASE_URLS['books']}/invoices"
        params = {
            'organization_id': self.config.ORG_ID
        }
        return self.make_request('POST', url, data=invoice_data, params=params)
    
    # Desk functions
    def get_tickets(self, limit=200, status=None, priority=None):
        """
        Get tickets list from Zoho Desk
        """
        url = f"{self.config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': self.config.ORG_ID
        }
        
        # Add limit parameter if specified
        if limit:
            params['limit'] = limit
        
        # Add optional filters
        if status:
            params['status'] = status
        if priority:
            params['priority'] = priority
            
        return self.make_request('GET', url, params=params)
    
    def get_ticket_by_id(self, ticket_id):
        """
        Get specific ticket by ID from Zoho Desk
        """
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
        params = {
            'orgId': self.config.ORG_ID
        }
        return self.make_request('GET', url, params=params)
    
    def create_ticket(self, ticket_data):
        """
        Create new ticket in Zoho Desk
        """
        url = f"{self.config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': self.config.ORG_ID
        }
        data = ticket_data
        return self.make_request('POST', url, data=data, params=params)
    
    def update_ticket(self, ticket_id, ticket_data):
        """
        Update existing ticket in Zoho Desk
        """
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
        params = {
            'orgId': self.config.ORG_ID
        }
        return self.make_request('PUT', url, data=ticket_data, params=params)
    
    def test_connection(self):
        """
        Test connection with Zoho API
        """
        print("Testing connection with Zoho API...")
        
        # Try to get access token
        token = self.get_access_token()
        if not token:
            print("❌ Failed to get access token")
            return False
        
        print("✅ Successfully obtained access token")
        
        # Test CRM connection
        try:
            leads_response = self.get_leads(per_page=1)
            if leads_response:
                print("✅ Zoho CRM connection is working properly")
            else:
                print("⚠️  There might be an issue with Zoho CRM connection")
        except Exception as e:
            print(f"⚠️  Error testing Zoho CRM: {e}")
        
        # Test Books connection
        try:
            customers_response = self.get_customers()
            if customers_response:
                print("✅ Zoho Books connection is working properly")
            else:
                print("⚠️  There might be an issue with Zoho Books connection")
        except Exception as e:
            print(f"⚠️  Error testing Zoho Books: {e}")
        
        # Test Desk connection
        try:
            tickets_response = self.get_tickets(limit=1)
            if tickets_response:
                print("✅ Zoho Desk connection is working properly")
            else:
                print("⚠️  There might be an issue with Zoho Desk connection")
        except Exception as e:
            print(f"⚠️  Error testing Zoho Desk: {e}")
        
        return True

