#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Zoho Desk Ticket Details Viewer
عرض تفاصيل تذاكر Zoho Desk بشكل مفصل
"""

from zoho_api import ZohoAPI
import json
from datetime import datetime

def format_datetime(date_string):
    """
    Format datetime string to readable format
    """
    if not date_string:
        return "N/A"
    try:
        # Parse ISO format datetime
        dt = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except:
        return date_string

def display_ticket_details(ticket):
    """
    Display detailed ticket information
    """
    print("="*80)
    print(f"TICKET DETAILS")
    print("="*80)
    
    # Basic Information
    print(f"🎫 Ticket ID: {ticket.get('id', 'N/A')}")
    print(f"📋 Ticket Number: {ticket.get('ticketNumber', 'N/A')}")
    print(f"📝 Subject: {ticket.get('subject', 'No Subject')}")
    print(f"📧 Email: {ticket.get('email', 'N/A')}")
    print(f"📞 Phone: {ticket.get('phone', 'N/A')}")
    
    # Status Information
    print(f"\n📊 STATUS INFORMATION:")
    print(f"   Status: {ticket.get('status', 'Unknown')}")
    print(f"   Status Type: {ticket.get('statusType', 'Unknown')}")
    print(f"   Priority: {ticket.get('priority', 'Not Set')}")
    print(f"   Category: {ticket.get('category', 'No Category')}")
    print(f"   Sub Category: {ticket.get('subCategory', 'None')}")
    print(f"   Channel: {ticket.get('channel', 'Unknown')}")
    print(f"   Language: {ticket.get('language', 'Unknown')}")
    
    # Timestamps
    print(f"\n⏰ TIMESTAMPS:")
    print(f"   Created: {format_datetime(ticket.get('createdTime'))}")
    print(f"   Due Date: {format_datetime(ticket.get('dueDate'))}")
    print(f"   Response Due: {format_datetime(ticket.get('responseDueDate'))}")
    print(f"   Customer Response: {format_datetime(ticket.get('customerResponseTime'))}")
    print(f"   Closed: {format_datetime(ticket.get('closedTime'))}")
    print(f"   On Hold: {format_datetime(ticket.get('onholdTime'))}")
    
    # Counts and Statistics
    print(f"\n📈 STATISTICS:")
    print(f"   Comments Count: {ticket.get('commentCount', '0')}")
    print(f"   Thread Count: {ticket.get('threadCount', '0')}")
    print(f"   Sentiment: {ticket.get('sentiment', 'Not Analyzed')}")
    print(f"   Is Spam: {ticket.get('isSpam', False)}")
    print(f"   Is Archived: {ticket.get('isArchived', False)}")
    
    # Assignment Information
    print(f"\n👥 ASSIGNMENT:")
    print(f"   Assignee ID: {ticket.get('assigneeId', 'Not Assigned')}")
    print(f"   Team ID: {ticket.get('teamId', 'Not Assigned')}")
    print(f"   Department ID: {ticket.get('departmentId', 'N/A')}")
    print(f"   Account ID: {ticket.get('accountId', 'N/A')}")
    print(f"   Contact ID: {ticket.get('contactId', 'N/A')}")
    print(f"   Product ID: {ticket.get('productId', 'N/A')}")
    
    # Relationship
    print(f"\n🔗 RELATIONSHIP:")
    print(f"   Relationship Type: {ticket.get('relationshipType', 'None')}")
    
    # Last Thread Information
    if ticket.get('lastThread'):
        last_thread = ticket.get('lastThread', {})
        print(f"\n💬 LAST THREAD:")
        print(f"   Channel: {last_thread.get('channel', 'Unknown')}")
        print(f"   Direction: {last_thread.get('direction', 'Unknown')}")
        print(f"   Is Draft: {last_thread.get('isDraft', False)}")
        print(f"   Is Forward: {last_thread.get('isForward', False)}")
    
    # Source Information
    if ticket.get('source'):
        source = ticket.get('source', {})
        print(f"\n📱 SOURCE:")
        print(f"   Type: {source.get('type', 'Unknown')}")
        print(f"   App Name: {source.get('appName', 'N/A')}")
        print(f"   External ID: {source.get('extId', 'N/A')}")
    
    # Web URL
    if ticket.get('webUrl'):
        print(f"\n🌐 WEB URL: {ticket.get('webUrl')}")
    
    print("="*80)

def get_all_tickets_detailed():
    """
    Get all tickets with detailed information
    """
    print("🔍 Fetching all tickets with detailed information...")
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("✅ Access token obtained successfully")
    
    # Get tickets (increased limit to get more tickets)
    print("\n📋 Fetching tickets from Zoho Desk...")
    tickets_response = zoho.get_tickets(limit=50)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found or error retrieving tickets")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets")
    
    # Display each ticket in detail
    for i, ticket in enumerate(tickets, 1):
        print(f"\n\n🔄 Processing ticket {i} of {len(tickets)}")
        display_ticket_details(ticket)
        
        # Ask user if they want to continue
        if i < len(tickets):
            continue_choice = input(f"\nPress Enter to view next ticket (or 'q' to quit, 's' to save current ticket): ").strip().lower()
            if continue_choice == 'q':
                print("👋 Exiting...")
                break
            elif continue_choice == 's':
                save_ticket_to_file(ticket)
    
    print(f"\n✅ Completed viewing {min(i, len(tickets))} tickets")

def save_ticket_to_file(ticket):
    """
    Save ticket details to a JSON file
    """
    filename = f"ticket_{ticket.get('ticketNumber', 'unknown')}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(ticket, f, ensure_ascii=False, indent=2)
        print(f"💾 Ticket saved to: {filename}")
    except Exception as e:
        print(f"❌ Error saving ticket: {e}")

def get_ticket_by_number():
    """
    Get specific ticket by ticket number
    """
    ticket_number = input("Enter ticket number: ").strip()
    if not ticket_number:
        print("❌ No ticket number provided")
        return
    
    print(f"🔍 Searching for ticket number: {ticket_number}")
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    # Get all tickets and find the one with matching number
    tickets_response = zoho.get_tickets(limit=100)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    # Find ticket by number
    found_ticket = None
    for ticket in tickets_response['data']:
        if ticket.get('ticketNumber') == ticket_number:
            found_ticket = ticket
            break
    
    if found_ticket:
        print(f"✅ Found ticket: {ticket_number}")
        display_ticket_details(found_ticket)
    else:
        print(f"❌ Ticket number {ticket_number} not found")

def main():
    """
    Main function with menu
    """
    print("="*80)
    print("🎫 ZOHO DESK TICKET DETAILS VIEWER")
    print("="*80)
    print("This script displays detailed information about Zoho Desk tickets")
    print("="*80)
    
    while True:
        print("\n📋 MENU:")
        print("1. View all tickets with details")
        print("2. Search ticket by number")
        print("3. Export all tickets to JSON")
        print("0. Exit")
        
        choice = input("\nChoose option (0-3): ").strip()
        
        if choice == "0":
            print("👋 Goodbye!")
            break
        elif choice == "1":
            get_all_tickets_detailed()
        elif choice == "2":
            get_ticket_by_number()
        elif choice == "3":
            export_all_tickets()
        else:
            print("❌ Invalid choice")

def export_all_tickets():
    """
    Export all tickets to JSON file
    """
    print("📤 Exporting all tickets to JSON file...")
    
    zoho = ZohoAPI()
    tickets_response = zoho.get_tickets(limit=100)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    filename = f"all_tickets_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(tickets_response, f, ensure_ascii=False, indent=2)
        print(f"✅ Exported {len(tickets_response['data'])} tickets to: {filename}")
    except Exception as e:
        print(f"❌ Error exporting tickets: {e}")

if __name__ == "__main__":
    main()
