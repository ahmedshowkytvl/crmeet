# Zoho Desk API - Ticket Details Documentation

## 📋 Overview
This document explains how to get ticket details using Zoho Desk API with examples and best practices.

## 🔑 Authentication

### 1. Get Access Token
```python
import requests

# Refresh token request
def get_access_token():
    url = "https://accounts.zoho.com/oauth/v2/token"
    
    data = {
        'refresh_token': 'YOUR_REFRESH_TOKEN',
        'client_id': 'YOUR_CLIENT_ID',
        'client_secret': 'YOUR_CLIENT_SECRET',
        'grant_type': 'refresh_token'
    }
    
    response = requests.post(url, data=data)
    token_data = response.json()
    return token_data['access_token']
```

### 2. Headers for API Requests
```python
headers = {
    "Authorization": f"Zoho-oauthtoken {access_token}",
    "orgId": "YOUR_ORG_ID",
    "contentType": "application/json; charset=utf-8"
}
```

## 🎫 Ticket API Endpoints

### 1. Get All Tickets
```python
def get_all_tickets(access_token, org_id, limit=20):
    url = f"https://desk.zoho.com/api/v1/tickets"
    
    params = {
        'orgId': org_id,
        'limit': limit,
        'sortBy': '-modifiedTime'  # Sort by latest modified
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

### 2. Get Specific Ticket Details
```python
def get_ticket_details(ticket_id, access_token, org_id):
    url = f"https://desk.zoho.com/api/v1/tickets/{ticket_id}"
    
    params = {
        'orgId': org_id
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

### 3. Get Ticket Threads
```python
def get_ticket_threads(ticket_id, access_token, org_id):
    url = f"https://desk.zoho.com/api/v1/tickets/{ticket_id}/threads"
    
    params = {
        'orgId': org_id
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

## 📊 Complete Ticket Details Structure

### Ticket Object Fields
```json
{
    "id": "766285000467900294",
    "ticketNumber": "2837477",
    "layoutId": "766285000006097316",
    "email": "customer@example.com",
    "phone": "+1234567890",
    "subject": "Ticket Subject",
    "status": "Closed",
    "statusType": "Closed",
    "createdTime": "2025-10-08T09:23:39.000Z",
    "modifiedTime": "2025-10-08T10:43:26.000Z",
    "category": "Technical",
    "subCategory": "Hardware",
    "priority": "High",
    "channel": "Email",
    "dueDate": "2025-10-10T00:00:00.000Z",
    "responseDueDate": "2025-10-09T00:00:00.000Z",
    "commentCount": "5",
    "sentiment": "Positive",
    "threadCount": "3",
    "closedTime": "2025-10-08T09:23:40.000Z",
    "onholdTime": null,
    "accountId": "766285000008811813",
    "departmentId": "766285000006092035",
    "contactId": "766285000008811813",
    "productId": "766285000001234567",
    "assigneeId": "766285000000139001",
    "teamId": "766285000000139002",
    "relationshipType": "Customer",
    "lastThread": {
        "channel": "EMAIL",
        "isDraft": false,
        "isForward": false,
        "direction": "in"
    },
    "customerResponseTime": "2025-10-08T09:23:39.000Z",
    "isArchived": false,
    "source": {
        "extId": null,
        "appName": "Zoho Desk",
        "appPhotoURL": null,
        "permalink": null,
        "type": "SYSTEM"
    },
    "isSpam": false,
    "channelCode": "EMAIL",
    "webUrl": "https://support.example.com/support/ShowHomePage.do#Cases/dv/766285000467900294",
    "cf": {
        "cf_closed_by": "System Admin",
        "cf_priority": "High",
        "cf_category": "Technical"
    },
    "customFields": {
        "Custom Field 1": "Value 1",
        "Custom Field 2": "Value 2"
    },
    "department": {
        "name": "Technical Support",
        "id": "766285000006092035"
    },
    "assignee": {
        "firstName": "John",
        "lastName": "Doe",
        "id": "766285000000139001"
    },
    "createdBy": {
        "firstName": "Jane",
        "lastName": "Smith",
        "id": "766285000000372105"
    },
    "modifiedBy": {
        "firstName": "Mike",
        "lastName": "Johnson",
        "id": "766285000000372105"
    }
}
```

## 🔍 Advanced Search and Filtering

### 1. Search Tickets by Date Range
```python
def search_tickets_by_date(access_token, org_id, from_date, to_date):
    url = "https://desk.zoho.com/api/v1/tickets/search"
    
    # Format dates: YYYY-MM-DDTHH:MM:SS.000Z
    params = f"from=0&limit=100&sortBy=-modifiedTime&modifiedTimeRange={from_date},{to_date}"
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(f"{url}?{params}", headers=headers)
    return response.json()
```

### 2. Search Tickets by Status
```python
def search_tickets_by_status(access_token, org_id, status):
    url = f"https://desk.zoho.com/api/v1/tickets"
    
    params = {
        'orgId': org_id,
        'status': status,  # Open, Closed, In Progress, etc.
        'limit': 100
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

### 3. Search Tickets by Department

#### الطريقة الأولى: استخدام departmentIds parameter
```python
def search_tickets_by_department(access_token, org_id, department_id):
    url = f"https://desk.zoho.com/api/v1/tickets"
    
    params = {
        'orgId': org_id,
        'departmentIds': department_id,  # معامل القسم الصحيح
        'limit': 100
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

#### الطريقة الثانية: استخدام search endpoint مع filter
```python
def search_tickets_by_department_search(access_token, org_id, department_id):
    search_url = f"https://desk.zoho.com/api/v1/tickets/search"
    
    # بناء معاملات البحث مع فلتر القسم
    params = f"from=0&limit=100&sortBy=-createdTime&filter=departmentId:{department_id}"
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(f"{search_url}?{params}", headers=headers)
    return response.json()
```

#### جلب قائمة الأقسام المتاحة
```python
def get_departments_list(access_token, org_id):
    url = f"https://desk.zoho.com/api/v1/departments"
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers)
    return response.json()
```

### 4. Search Tickets EXCLUDING Auto Close

نظرًا لأن Zoho API لا يدعم negation filters مباشرة، يجب استخدام client-side filtering:

```python
def search_exclude_auto_close(access_token, org_id, limit=100):
    """البحث عن جميع التذاكر ماعدا Auto Close"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    url = f"https://desk.zoho.com/api/v1/tickets"
    params = {
        'orgId': org_id,
        'limit': limit
    }
    
    response = requests.get(url, headers=headers, params=params)
    all_tickets = response.json().get('data', [])
    
    # Filter out Auto Close tickets
    filtered_tickets = [
        ticket for ticket in all_tickets
        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
    ]
    
    return filtered_tickets
```

#### البحث المتقدم مع فلاتر متعددة + استبعاد Auto Close

```python
def search_with_multiple_filters(access_token, org_id, status=None, 
                                 department_id=None, exclude_auto_close=True):
    """بحث متقدم مع استبعاد Auto Close"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    url = "https://desk.zoho.com/api/v1/tickets"
    params = {'orgId': org_id, 'limit': 100}
    
    # Add optional API filters
    if status:
        params['status'] = status
    if department_id:
        params['departmentIds'] = department_id
    
    response = requests.get(url, headers=headers, params=params)
    all_tickets = response.json().get('data', [])
    
    # Apply client-side filter for Auto Close
    if exclude_auto_close:
        filtered_tickets = [
            ticket for ticket in all_tickets
            if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
        ]
    else:
        filtered_tickets = all_tickets
    
    return filtered_tickets
```

**أمثلة الاستخدام:**

```python
# Example 1: Get all tickets excluding Auto Close
tickets = search_exclude_auto_close(access_token, org_id, limit=100)

# Example 2: Get Closed tickets excluding Auto Close
tickets = search_with_multiple_filters(
    access_token, org_id, 
    status='Closed', 
    exclude_auto_close=True
)

# Example 3: Get department tickets excluding Auto Close
tickets = search_with_multiple_filters(
    access_token, org_id,
    department_id='766285000016070029',
    exclude_auto_close=True
)
```

## 📝 Thread Details Structure

### Thread Object Fields
```json
{
    "id": "766285000467900295",
    "ticketId": "766285000467900294",
    "content": "Thread content here...",
    "summary": "Thread summary",
    "body": "Full thread body",
    "direction": "in",  // in, out
    "channel": "EMAIL",
    "createdTime": "2025-10-08T09:23:39.000Z",
    "modifiedTime": "2025-10-08T09:23:39.000Z",
    "isDraft": false,
    "isForward": false,
    "isPublic": true,
    "isPrivate": false,
    "sender": {
        "firstName": "Customer",
        "lastName": "Name",
        "email": "customer@example.com"
    },
    "toContacts": [
        {
            "firstName": "Agent",
            "lastName": "Name",
            "email": "agent@example.com"
        }
    ],
    "ccContacts": [],
    "bccContacts": [],
    "attachments": [
        {
            "id": "766285000467900296",
            "fileName": "attachment.pdf",
            "fileSize": "1024",
            "contentType": "application/pdf",
            "downloadUrl": "https://desk.zoho.com/api/v1/attachments/766285000467900296/download"
        }
    ]
}
```

## 🛠️ Practical Implementation Example

### Complete Ticket Details Function
```python
import requests
from datetime import datetime, timedelta

class ZohoTicketAPI:
    def __init__(self, client_id, client_secret, refresh_token, org_id):
        self.client_id = client_id
        self.client_secret = client_secret
        self.refresh_token = refresh_token
        self.org_id = org_id
        self.access_token = None
        
    def get_access_token(self):
        """Get access token using refresh token"""
        url = "https://accounts.zoho.com/oauth/v2/token"
        
        data = {
            'refresh_token': self.refresh_token,
            'client_id': self.client_id,
            'client_secret': self.client_secret,
            'grant_type': 'refresh_token'
        }
        
        response = requests.post(url, data=data)
        response.raise_for_status()
        
        token_data = response.json()
        self.access_token = token_data['access_token']
        return self.access_token
    
    def get_ticket_with_full_details(self, ticket_id):
        """Get complete ticket details including threads"""
        
        # Get access token
        if not self.access_token:
            self.get_access_token()
        
        headers = {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.org_id,
            "contentType": "application/json; charset=utf-8"
        }
        
        # Get ticket details
        ticket_url = f"https://desk.zoho.com/api/v1/tickets/{ticket_id}"
        ticket_response = requests.get(ticket_url, headers=headers, 
                                     params={'orgId': self.org_id})
        ticket_response.raise_for_status()
        ticket_data = ticket_response.json()
        
        # Get ticket threads
        threads_url = f"https://desk.zoho.com/api/v1/tickets/{ticket_id}/threads"
        threads_response = requests.get(threads_url, headers=headers,
                                      params={'orgId': self.org_id})
        threads_response.raise_for_status()
        threads_data = threads_response.json()
        
        # Combine data
        ticket_data['threads'] = threads_data.get('data', [])
        
        return ticket_data
    
    def search_tickets_today(self):
        """Get all tickets modified today"""
        
        if not self.access_token:
            self.get_access_token()
        
        headers = {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.org_id,
            "contentType": "application/json; charset=utf-8"
        }
        
        # Today's date range
        today = datetime.now()
        from_date = today.strftime('%Y-%m-%dT00:00:00.000Z')
        to_date = today.strftime('%Y-%m-%dT23:59:59.000Z')
        
        # Search URL
        search_url = "https://desk.zoho.com/api/v1/tickets/search"
        params = f"from=0&limit=100&sortBy=-modifiedTime&modifiedTimeRange={from_date},{to_date}"
        
        response = requests.get(f"{search_url}?{params}", headers=headers)
        response.raise_for_status()
        
        return response.json()

# Usage Example
api = ZohoTicketAPI(
    client_id="YOUR_CLIENT_ID",
    client_secret="YOUR_CLIENT_SECRET", 
    refresh_token="YOUR_REFRESH_TOKEN",
    org_id="YOUR_ORG_ID"
)

# Get specific ticket details
ticket_details = api.get_ticket_with_full_details("766285000467900294")

# Get today's tickets
today_tickets = api.search_tickets_today()
```

## 🔧 Error Handling

### Common Error Responses
```python
def handle_api_errors(response):
    """Handle common API errors"""
    
    if response.status_code == 401:
        return "Authentication failed - check your tokens"
    elif response.status_code == 403:
        return "Access forbidden - check your permissions"
    elif response.status_code == 429:
        return "Rate limit exceeded - wait before retrying"
    elif response.status_code == 500:
        return "Server error - try again later"
    else:
        return f"API Error {response.status_code}: {response.text}"

# Example usage
try:
    response = requests.get(url, headers=headers, params=params)
    response.raise_for_status()
    data = response.json()
except requests.exceptions.RequestException as e:
    error_msg = handle_api_errors(response)
    print(f"Error: {error_msg}")
```

## 📋 Best Practices

1. **Rate Limiting**: Add delays between requests to avoid hitting rate limits
2. **Caching**: Cache frequently accessed data like department and user names
3. **Error Handling**: Always handle API errors gracefully
4. **Pagination**: Use `from` and `limit` parameters for large datasets
5. **Date Formatting**: Use ISO 8601 format for date ranges
6. **Token Management**: Refresh tokens before they expire

## 🔗 Useful Links

- [Zoho Desk API Documentation](https://desk.zoho.com/DeskAPIDocument)
- [OAuth 2.0 Setup Guide](https://www.zoho.com/desk/help/api/v2/oauth-setup.html)
- [Rate Limiting Guidelines](https://www.zoho.com/desk/help/api/v2/rate-limit.html)
