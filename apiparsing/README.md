# Zoho API Parser Application

This application helps you work with various Zoho services like CRM and Books through API.

## Features

- 🔐 Automatic access token management
- 📊 Data extraction from Zoho CRM (Leads, Contacts, Accounts)
- 💰 Working with Zoho Books (Customers, Items, Invoices)
- 🎫 Ticket management with Zoho Desk (View, Create, Update tickets)
- 📁 Export data to JSON files
- 🔄 Create new records in Zoho

## Installation

1. Make sure you have Python 3.6 or later
2. Install required libraries:

```bash
pip install -r requirements.txt
```

## Setup

1. Make sure to update `config.py` with correct credentials:
   - `CLIENT_ID`: Client ID
   - `CLIENT_SECRET`: Client Secret
   - `REFRESH_TOKEN`: Refresh Token
   - `ORG_ID`: Organization ID

## Usage

Run the main program:

```bash
python main.py
```

### Available Options:

1. **Test Connection**: Verify credentials and connection
2. **View Leads**: Get list of potential customers
3. **View Contacts**: Get list of contacts
4. **View Accounts**: Get list of accounts
5. **View Customers**: Get list of Zoho Books customers
6. **View Items**: Get list of Zoho Books items
7. **Create New Lead**: Add new potential customer
8. **Create New Contact**: Add new contact
9. **Create Invoice**: Add new invoice in Zoho Books
10. **View Tickets**: Get list of support tickets from Zoho Desk
11. **Create New Ticket**: Add new support ticket in Zoho Desk
12. **Export Data**: Save data to JSON files

## File Structure

```
apiparsing/
├── main.py              # Main program
├── zoho_api.py          # Zoho API handling class
├── config.py            # Configuration settings
├── requirements.txt     # Required libraries
└── README.md           # This file
```

## Programming Usage Example

```python
from zoho_api import ZohoAPI

# Create Zoho API object
zoho = ZohoAPI()

# Test connection
zoho.test_connection()

# Get leads list
leads = zoho.get_leads()

# Create new lead
new_lead = {
    "First_Name": "John",
    "Last_Name": "Doe",
    "Email": "john@example.com",
    "Phone": "+1234567890",
    "Company": "Tech Company"
}
result = zoho.create_lead(new_lead)

# Get tickets with filters
tickets = zoho.get_tickets(status="Open", priority="High")

# Create new ticket
new_ticket = {
    "subject": "Technical Support Request",
    "description": "Need help with API integration",
    "priority": "Medium",
    "status": "Open",
    "contact": {
        "email": "customer@example.com"
    }
}
result = zoho.create_ticket(new_ticket)
```

## Troubleshooting

### Common Issues:

1. **Error getting access token**:
   - Check refresh token validity
   - Verify CLIENT_ID and CLIENT_SECRET

2. **Permission error**:
   - Make sure appropriate permissions are enabled in Zoho
   - Check Organization ID (ORG_ID)

3. **Connection error**:
   - Check internet connection
   - Verify no firewall blocking the connection

## Security

⚠️ **Warning**: Do not share credentials with anyone
- Keep `config.py` file secure
- Do not upload credentials to public repositories
- Use environment variables in production

## Support

If you encounter any issues, please check:
1. Credential validity
2. API permissions in Zoho account
3. Internet connection

## License

This project is open source and available for personal and commercial use.

