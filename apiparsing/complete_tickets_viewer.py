#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Complete Tickets Viewer with Detailed Threads
عرض شامل للتذاكر مع تفاصيل الـ Threads والـ Comments
"""

from zoho_api import ZohoAPI
import requests
import json
from datetime import datetime

def format_datetime(date_string):
    """Format datetime string"""
    if not date_string:
        return "N/A"
    try:
        dt = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except:
        return date_string

def get_ticket_threads(zoho, ticket_id):
    """Get threads for a specific ticket"""
    url = f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
    
    token = zoho.ensure_valid_token()
    if not token:
        return None
    
    headers = {
        'Authorization': f'Zoho-oauthtoken {token}',
        'orgId': zoho.config.ORG_ID,
        'Content-Type': 'application/json'
    }
    
    try:
        response = requests.get(url, headers=headers, params={'limit': 50})
        response.raise_for_status()
        print(response.json())
        if response.status_code == 204 or not response.text.strip():
            return {"data": []}
        
        return response.json()
    except Exception as e:
        print(f"Error getting threads: {e}")
        return None

def display_ticket_header(ticket):
    """Display ticket header information"""
    print(f"\n{'='*100}")
    print(f"🎫 TICKET: {ticket.get('ticketNumber', 'N/A')} - {ticket.get('subject', 'No Subject')}")
    print(f"{'='*100}")
    
    print(f"📧 Email: {ticket.get('email', 'N/A')}")
    print(f"📊 Status: {ticket.get('status', 'Unknown')} | Priority: {ticket.get('priority', 'Not Set')}")
    print(f"🏷️  Category: {ticket.get('category', 'No Category')} | Channel: {ticket.get('channel', 'Unknown')}")
    print(f"⏰ Created: {format_datetime(ticket.get('createdTime'))}")
    print(f"📅 Due: {format_datetime(ticket.get('dueDate'))}")
    print(f"💬 Thread Count: {ticket.get('threadCount', '0')} | Comments: {ticket.get('commentCount', '0')}")
    print(f"👤 Assignee: {ticket.get('assigneeId', 'Not Assigned')}")
    print(f"🏢 Department: {ticket.get('departmentId', 'N/A')}")

def display_thread_detailed(thread, index):
    """Display detailed thread information"""
    print(f"\n{'─'*80}")
    print(f"💬 THREAD #{index}")
    print(f"{'─'*80}")
    
    print(f"🆔 Thread ID: {thread.get('id', 'N/A')}")
    print(f"📧 Channel: {thread.get('channel', 'Unknown')}")
    print(f"📤 Direction: {thread.get('direction', 'Unknown')}")
    print(f"⏰ Created: {format_datetime(thread.get('createdTime'))}")
    # Better handling of sender/receiver information
    from_info = thread.get('from', 'N/A')
    to_info = thread.get('to', 'N/A')
    email_info = thread.get('email', 'N/A')
    
    # Check for actual fields used by Zoho Desk
    if from_info == 'N/A' or from_info is None:
        from_info = thread.get('fromEmailAddress', 'N/A')
    if to_info == 'N/A' or to_info is None:
        to_info = thread.get('to', 'N/A')
    
    # Parse email addresses
    def parse_email(email_string):
        if not email_string or email_string == 'N/A':
            return email_string
        import re
        email_match = re.search(r'<([^>]+)>', email_string)
        return email_match.group(1) if email_match else email_string
    
    def parse_name(email_string):
        if not email_string or email_string == 'N/A':
            return email_string
        import re
        name_match = re.match(r'^"([^"]+)"', email_string)
        if name_match:
            return name_match.group(1)
        name_match = re.match(r'^([^<]+)', email_string)
        return name_match.group(1).strip() if name_match and '<' in email_string else email_string
    
    from_email = parse_email(from_info)
    from_name = parse_name(from_info)
    to_email = parse_email(to_info)
    to_name = parse_name(to_info)
    
    print(f"👤 From: {from_name} <{from_email}>")
    print(f"👥 To: {to_name} <{to_email}>")
    print(f"📧 Email: {email_info}")
    
    # Show CC if available
    cc_info = thread.get('cc', '')
    if cc_info and cc_info.strip():
        print(f"📋 CC: {cc_info}")
    
    # Thread content
    content = thread.get('content', '')
    if content:
        print(f"\n📝 THREAD CONTENT:")
        print(f"{'─'*50}")
        # Clean HTML tags
        clean_content = content.replace('<br>', '\n').replace('<p>', '').replace('</p>', '\n').replace('<div>', '').replace('</div>', '\n')
        clean_content = clean_content.replace('&nbsp;', ' ').replace('&amp;', '&').replace('&lt;', '<').replace('&gt;', '>')
        print(clean_content)
        print(f"{'─'*50}")
    
    # Thread properties
    print(f"\n🔧 THREAD PROPERTIES:")
    print(f"   Is Draft: {thread.get('isDraft', False)}")
    print(f"   Is Forward: {thread.get('isForward', False)}")
    print(f"   Is Private: {thread.get('isPrivate', False)}")
    print(f"   Is Public: {thread.get('isPublic', False)}")
    print(f"   Is Reply: {thread.get('isReply', False)}")
    
    # Attachments
    if thread.get('attachments'):
        print(f"\n📎 ATTACHMENTS:")
        for i, attachment in enumerate(thread.get('attachments', []), 1):
            print(f"   {i}. {attachment.get('name', 'Unknown')} ({attachment.get('size', 'Unknown size')})")
            print(f"      URL: {attachment.get('url', 'N/A')}")
    
    # CC and BCC
    if thread.get('cc'):
        print(f"\n📋 CC: {', '.join(thread.get('cc', []))}")
    if thread.get('bcc'):
        print(f"📋 BCC: {', '.join(thread.get('bcc', []))}")

def process_ticket_with_threads(zoho, ticket):
    """Process a single ticket with all its threads"""
    display_ticket_header(ticket)
    
    ticket_id = ticket.get('id')
    if not ticket_id:
        print("❌ No ticket ID found")
        return
    
    # Get threads
    print(f"\n🔍 FETCHING THREADS...")
    threads_response = get_ticket_threads(zoho, ticket_id)
    
    if threads_response and threads_response.get('data'):
        threads = threads_response['data']
        print(f"✅ Found {len(threads)} threads")
        
        for i, thread in enumerate(threads, 1):
            display_thread_detailed(thread, i)
            
            # Ask user if they want to continue
            if i < len(threads):
                continue_choice = input(f"\nPress Enter for next thread (or 'q' to skip remaining threads): ").strip().lower()
                if continue_choice == 'q':
                    print("⏭️  Skipping remaining threads...")
                    break
    else:
        print("❌ No threads found or error retrieving threads")

def main():
    """Main function"""
    print("🚀 COMPLETE TICKETS VIEWER WITH THREADS")
    print("="*100)
    print("This script displays detailed ticket information with all threads")
    print("="*100)
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("✅ Connected to Zoho Desk")
    
    while True:
        print(f"\n{'📋 MENU':^100}")
        print("1. View recent tickets with detailed threads")
        print("2. Search specific ticket by number")
        print("3. View tickets by status")
        print("4. Export tickets with threads to JSON")
        print("0. Exit")
        
        choice = input("\nChoose option (0-4): ").strip()
        
        if choice == "0":
            print("👋 Goodbye!")
            break
        elif choice == "1":
            view_recent_tickets(zoho)
        elif choice == "2":
            search_specific_ticket(zoho)
        elif choice == "3":
            view_tickets_by_status(zoho)
        elif choice == "4":
            export_tickets_with_threads(zoho)
        else:
            print("❌ Invalid choice")

def view_recent_tickets(zoho):
    """View recent tickets with threads"""
    limit = input("How many recent tickets to view? (default 5): ").strip()
    limit = int(limit) if limit.isdigit() else 5
    
    print(f"\n🔍 Fetching {limit} recent tickets...")
    tickets_response = zoho.get_tickets(limit=limit)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets")
    
    for i, ticket in enumerate(tickets, 1):
        print(f"\n{'📋 PROCESSING TICKET':^100}")
        print(f"Ticket {i} of {len(tickets)}: {ticket.get('ticketNumber', 'N/A')}")
        
        process_ticket_with_threads(zoho, ticket)
        
        if i < len(tickets):
            continue_choice = input(f"\nPress Enter for next ticket (or 'q' to quit): ").strip().lower()
            if continue_choice == 'q':
                break

def search_specific_ticket(zoho):
    """Search for a specific ticket by number"""
    ticket_number = input("Enter ticket number: ").strip()
    if not ticket_number:
        print("❌ No ticket number provided")
        return
    
    print(f"🔍 Searching for ticket: {ticket_number}")
    
    # Get all tickets and find the matching one
    tickets_response = zoho.get_tickets(limit=100)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    found_ticket = None
    for ticket in tickets_response['data']:
        if ticket.get('ticketNumber') == ticket_number:
            found_ticket = ticket
            break
    
    if found_ticket:
        print(f"✅ Found ticket: {ticket_number}")
        process_ticket_with_threads(zoho, found_ticket)
    else:
        print(f"❌ Ticket {ticket_number} not found")

def view_tickets_by_status(zoho):
    """View tickets by status"""
    status = input("Enter status (Open/Closed/In Progress): ").strip()
    if not status:
        print("❌ No status provided")
        return
    
    print(f"🔍 Fetching tickets with status: {status}")
    tickets_response = zoho.get_tickets(limit=20, status=status)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found with this status")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets with status: {status}")
    
    for i, ticket in enumerate(tickets, 1):
        print(f"\n{'📋 PROCESSING TICKET':^100}")
        print(f"Ticket {i} of {len(tickets)}: {ticket.get('ticketNumber', 'N/A')}")
        
        process_ticket_with_threads(zoho, ticket)
        
        if i < len(tickets):
            continue_choice = input(f"\nPress Enter for next ticket (or 'q' to quit): ").strip().lower()
            if continue_choice == 'q':
                break

def export_tickets_with_threads(zoho):
    """Export tickets with threads to JSON"""
    print("📤 Exporting tickets with threads to JSON...")
    
    tickets_response = zoho.get_tickets(limit=10)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    export_data = []
    
    for i, ticket in enumerate(tickets, 1):
        print(f"Processing ticket {i} of {len(tickets)}...")
        
        ticket_data = {
            'ticket': ticket,
            'threads': []
        }
        
        # Get threads for this ticket
        ticket_id = ticket.get('id')
        if ticket_id:
            threads_response = get_ticket_threads(zoho, ticket_id)
            if threads_response and threads_response.get('data'):
                ticket_data['threads'] = threads_response['data']
        
        export_data.append(ticket_data)
    
    filename = f"tickets_with_threads_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(export_data, f, ensure_ascii=False, indent=2)
        print(f"✅ Exported {len(export_data)} tickets with threads to: {filename}")
    except Exception as e:
        print(f"❌ Error exporting: {e}")

if __name__ == "__main__":
    main()
