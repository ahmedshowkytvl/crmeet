#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Main program for working with Zoho API
Can be used to extract data and create records in various Zoho services
"""

from zoho_api import ZohoAPI
import json

def print_separator(title=""):
    """
    Print separator line with optional title
    """
    print("\n" + "="*60)
    if title:
        print(f" {title} ")
        print("="*60)

def display_menu():
    """
    Display available options menu
    """
    print_separator("Available Options")
    print("1. Test connection with Zoho API")
    print("2. Get Leads list")
    print("3. Get Contacts list")
    print("4. Get Accounts list")
    print("5. Get Customers list (Zoho Books)")
    print("6. Get Items list (Zoho Books)")
    print("7. Create new Lead")
    print("8. Create new Contact")
    print("9. Create new Invoice (Zoho Books)")
    print("10. Get Tickets (Zoho Desk)")
    print("11. Create new Ticket (Zoho Desk)")
    print("12. Export data to JSON file")
    print("0. Exit")
    print("="*60)

def save_to_json(data, filename):
    """
    Save data to JSON file
    """
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        print(f"✅ Data saved to file: {filename}")
    except Exception as e:
        print(f"❌ Error saving file: {e}")

def create_sample_lead():
    """
    Create sample new Lead
    """
    return {
        "First_Name": input("First Name: "),
        "Last_Name": input("Last Name: "),
        "Email": input("Email: "),
        "Phone": input("Phone: "),
        "Company": input("Company: "),
        "Lead_Source": "Website",
        "Lead_Status": "Not Contacted"
    }

def create_sample_contact():
    """
    Create sample new Contact
    """
    return {
        "First_Name": input("First Name: "),
        "Last_Name": input("Last Name: "),
        "Email": input("Email: "),
        "Phone": input("Phone: "),
        "Account_Name": input("Account Name: "),
        "Mailing_Street": input("Address: ")
    }

def create_sample_invoice():
    """
    Create sample new invoice
    """
    print("Creating new invoice:")
    customer_name = input("Customer Name: ")
    item_name = input("Item Name: ")
    quantity = input("Quantity: ")
    rate = input("Rate: ")
    
    return {
        "customer_name": customer_name,
        "line_items": [
            {
                "name": item_name,
                "quantity": int(quantity),
                "rate": float(rate)
            }
        ]
    }

def create_sample_ticket():
    """
    Create sample new ticket
    """
    print("Creating new ticket:")
    subject = input("Subject: ")
    description = input("Description: ")
    priority = input("Priority (Low/Medium/High/Urgent): ")
    status = input("Status (Open/In Progress/Closed): ")
    email = input("Contact Email: ")
    
    return {
        "subject": subject,
        "description": description,
        "priority": priority,
        "status": status,
        "contact": {
            "email": email
        }
    }

def main():
    """
    Main program function
    """
    print_separator("Welcome to Zoho API Program")
    print("This program helps you work with various Zoho services")
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    while True:
        display_menu()
        choice = input("\nChoose option number (0-12): ").strip()
        
        if choice == "0":
            print("Thank you for using the program. Goodbye!")
            break
            
        elif choice == "1":
            print_separator("Testing Connection")
            zoho.test_connection()
            
        elif choice == "2":
            print_separator("Leads List")
            try:
                page = int(input("Page number (default 1): ") or "1")
                leads = zoho.get_leads(page=page)
                if leads and 'data' in leads:
                    print(f"Found {len(leads['data'])} lead(s)")
                    for i, lead in enumerate(leads['data'][:5], 1):  # Show first 5 only
                        print(f"{i}. {lead.get('First_Name', '')} {lead.get('Last_Name', '')} - {lead.get('Email', '')}")
                    
                    if len(leads['data']) > 5:
                        print(f"... and {len(leads['data']) - 5} more lead(s)")
                else:
                    print("No leads found")
            except ValueError:
                print("Invalid page number")
                
        elif choice == "3":
            print_separator("Contacts List")
            try:
                page = int(input("Page number (default 1): ") or "1")
                contacts = zoho.get_contacts(page=page)
                if contacts and 'data' in contacts:
                    print(f"Found {len(contacts['data'])} contact(s)")
                    for i, contact in enumerate(contacts['data'][:5], 1):
                        print(f"{i}. {contact.get('First_Name', '')} {contact.get('Last_Name', '')} - {contact.get('Email', '')}")
                    
                    if len(contacts['data']) > 5:
                        print(f"... and {len(contacts['data']) - 5} more contact(s)")
                else:
                    print("No contacts found")
            except ValueError:
                print("Invalid page number")
                
        elif choice == "4":
            print_separator("Accounts List")
            try:
                page = int(input("Page number (default 1): ") or "1")
                accounts = zoho.get_accounts(page=page)
                if accounts and 'data' in accounts:
                    print(f"Found {len(accounts['data'])} account(s)")
                    for i, account in enumerate(accounts['data'][:5], 1):
                        print(f"{i}. {account.get('Account_Name', '')} - {account.get('Website', '')}")
                    
                    if len(accounts['data']) > 5:
                        print(f"... and {len(accounts['data']) - 5} more account(s)")
                else:
                    print("No accounts found")
            except ValueError:
                print("Invalid page number")
                
        elif choice == "5":
            print_separator("Customers List (Zoho Books)")
            customers = zoho.get_customers()
            if customers and 'contacts' in customers:
                print(f"Found {len(customers['contacts'])} customer(s)")
                for i, customer in enumerate(customers['contacts'][:5], 1):
                    print(f"{i}. {customer.get('contact_name', '')} - {customer.get('email', '')}")
                
                if len(customers['contacts']) > 5:
                    print(f"... and {len(customers['contacts']) - 5} more customer(s)")
            else:
                print("No customers found")
                
        elif choice == "6":
            print_separator("Items List (Zoho Books)")
            items = zoho.get_items()
            if items and 'items' in items:
                print(f"Found {len(items['items'])} item(s)")
                for i, item in enumerate(items['items'][:5], 1):
                    print(f"{i}. {item.get('name', '')} - {item.get('rate', 0)}")
                
                if len(items['items']) > 5:
                    print(f"... and {len(items['items']) - 5} more item(s)")
            else:
                print("No items found")
                
        elif choice == "7":
            print_separator("Create New Lead")
            lead_data = create_sample_lead()
            result = zoho.create_lead(lead_data)
            if result:
                print("✅ Lead created successfully")
            else:
                print("❌ Failed to create Lead")
                
        elif choice == "8":
            print_separator("Create New Contact")
            contact_data = create_sample_contact()
            result = zoho.create_contact(contact_data)
            if result:
                print("✅ Contact created successfully")
            else:
                print("❌ Failed to create Contact")
                
        elif choice == "9":
            print_separator("Create New Invoice (Zoho Books)")
            invoice_data = create_sample_invoice()
            result = zoho.create_invoice(invoice_data)
            if result:
                print("✅ Invoice created successfully")
            else:
                print("❌ Failed to create Invoice")
                
        elif choice == "10":
            print_separator("Tickets List (Zoho Desk)")
            try:
                limit = int(input("Number of tickets to retrieve (default 10): ") or "10")
                
                # Optional filters
                print("\nOptional filters (press Enter to skip):")
                status_filter = input("Filter by status (Open/In Progress/Closed): ").strip() or None
                priority_filter = input("Filter by priority (Low/Medium/High/Urgent): ").strip() or None
                
                tickets = zoho.get_tickets(limit=limit, status=status_filter, priority=priority_filter)
                if tickets and 'data' in tickets:
                    if len(tickets['data']) > 0:
                        print(f"Found {len(tickets['data'])} ticket(s)")
                        for i, ticket in enumerate(tickets['data'][:5], 1):
                            print(f"{i}. [{ticket.get('id', 'N/A')}] {ticket.get('subject', 'No Subject')} - {ticket.get('status', 'Unknown')} ({ticket.get('priority', 'Unknown')})")
                        
                        if len(tickets['data']) > 5:
                            print(f"... and {len(tickets['data']) - 5} more ticket(s)")
                            
                        # Show ticket info if available
                        if 'info' in tickets:
                            info = tickets['info']
                            print(f"\nTotal records: {info.get('count', len(tickets['data']))}")
                    else:
                        print("No tickets found matching the specified criteria")
                        if status_filter or priority_filter:
                            print("Try removing filters or using different values")
                else:
                    print("No tickets found or error retrieving data")
            except ValueError:
                print("Invalid page number")
                
        elif choice == "11":
            print_separator("Create New Ticket (Zoho Desk)")
            ticket_data = create_sample_ticket()
            result = zoho.create_ticket(ticket_data)
            if result:
                print("✅ Ticket created successfully")
                if 'data' in result and result['data']:
                    ticket = result['data']
                    print(f"Ticket ID: {ticket.get('id', 'N/A')}")
            else:
                print("❌ Failed to create Ticket")
                
        elif choice == "12":
            print_separator("Export Data")
            export_choice = input("Choose data type to export (1: Leads, 2: Contacts, 3: Accounts, 4: Tickets): ")
            
            if export_choice == "1":
                data = zoho.get_leads()
                filename = "leads_export.json"
            elif export_choice == "2":
                data = zoho.get_contacts()
                filename = "contacts_export.json"
            elif export_choice == "3":
                data = zoho.get_accounts()
                filename = "accounts_export.json"
            elif export_choice == "4":
                data = zoho.get_tickets()
                filename = "tickets_export.json"
            else:
                print("Invalid choice")
                continue
            
            if data:
                save_to_json(data, filename)
            else:
                print("❌ Failed to get data")
                
        else:
            print("❌ Invalid choice. Please try again.")
        
        input("\nPress Enter to continue...")

if __name__ == "__main__":
    main()

