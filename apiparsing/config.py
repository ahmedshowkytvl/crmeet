# Zoho API Configuration File
# Contains credentials and connection settings

class ZohoConfig:
    # OAuth token URL
    TOKEN_URL = "https://accounts.zoho.com/oauth/v2/token"
    
    # Credentials
    CLIENT_ID = "1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT"
    CLIENT_SECRET = "30624b06180b20ab5252fc8e6145ad175762a367a0"
    REFRESH_TOKEN = "1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee"
    
    # Organization ID
    ORG_ID = "786481962"
    
    # OAuth authorization code (if needed)
    AUTHORIZATION_CODE = "1000.b4661996af0e5f0aafe9310abee0b345.f3396f9660c9f5e300c9df742defb709"
    
    # Enhanced scope for better API access
    ENHANCED_SCOPE = "Desk.tickets.ALL,Desk.search.READ,Desk.tickets.READ,Desk.tickets.UPDATE,Desk.tickets.CREATE,Desk.contacts.READ,Desk.agents.READ,Desk.departments.READ"
    
    # Base URLs for different Zoho services
    BASE_URLS = {
        'crm': 'https://www.zohoapis.com/crm/v2',
        'books': 'https://books.zoho.com/api/v3',
        'inventory': 'https://inventory.zoho.com/api/v1',
        'projects': 'https://projects.zoho.com/restapi',
        'desk': 'https://desk.zoho.com/api/v1'
    }

