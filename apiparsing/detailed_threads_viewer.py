#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Detailed Threads Viewer for Zoho Desk Tickets
عرض تفصيلي للـ Threads والـ Comments في تذاكر Zoho Desk
"""

from zoho_api import ZohoAPI
import requests
import json
from datetime import datetime

def format_datetime(date_string):
    """Format datetime string to readable format"""
    if not date_string:
        return "N/A"
    try:
        dt = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except:
        return date_string

def get_ticket_threads(zoho, ticket_id):
    """
    Get threads for a specific ticket
    """
    url = f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
    
    # Ensure valid token
    token = zoho.ensure_valid_token()
    if not token:
        return None
    
    headers = {
        'Authorization': f'Zoho-oauthtoken {token}',
        'orgId': zoho.config.ORG_ID,
        'Content-Type': 'application/json'
    }
    
    params = {
        'limit': 50  # Get more threads
    }
    
    try:
        response = requests.get(url, headers=headers, params=params)
        response.raise_for_status()
        
        if response.status_code == 204 or not response.text.strip():
            return {"data": [], "info": {"count": 0}}
        
        return response.json()
    except Exception as e:
        print(f"Error getting threads: {e}")
        return None

def get_ticket_comments(zoho, ticket_id):
    """
    Get comments for a specific ticket
    """
    url = f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/comments"
    
    # Ensure valid token
    token = zoho.ensure_valid_token()
    if not token:
        return None
    
    headers = {
        'Authorization': f'Zoho-oauthtoken {token}',
        'orgId': zoho.config.ORG_ID,
        'Content-Type': 'application/json'
    }
    
    params = {
        'limit': 50
    }
    
    try:
        response = requests.get(url, headers=headers, params=params)
        response.raise_for_status()
        
        if response.status_code == 204 or not response.text.strip():
            return {"data": [], "info": {"count": 0}}
        
        return response.json()
    except Exception as e:
        print(f"Error getting comments: {e}")
        return None

def display_thread_details(thread, index):
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
    phone_info = thread.get('phone', 'N/A')
    
    # Check for actual fields used by Zoho Desk
    if from_info == 'N/A' or from_info is None:
        from_info = thread.get('fromEmailAddress', 'N/A')
    if to_info == 'N/A' or to_info is None:
        to_info = thread.get('to', 'N/A')
    
    # Parse email addresses from the format "Name <email@domain.com>"
    def parse_email(email_string):
        if not email_string or email_string == 'N/A':
            return email_string
        import re
        # Extract email from "Name <email@domain.com>" format
        email_match = re.search(r'<([^>]+)>', email_string)
        if email_match:
            return email_match.group(1)
        return email_string
    
    def parse_name(email_string):
        if not email_string or email_string == 'N/A':
            return email_string
        import re
        # Extract name from "Name <email@domain.com>" format
        name_match = re.match(r'^"([^"]+)"', email_string)
        if name_match:
            return name_match.group(1)
        # If no quotes, try to extract before <
        name_match = re.match(r'^([^<]+)', email_string)
        if name_match and '<' in email_string:
            return name_match.group(1).strip()
        return email_string
    
    # Parse the actual email addresses
    from_email = parse_email(from_info)
    from_name = parse_name(from_info)
    to_email = parse_email(to_info)
    to_name = parse_name(to_info)
    
    print(f"👤 From: {from_name} <{from_email}>")
    print(f"👥 To: {to_name} <{to_email}>")
    print(f"📧 Email: {email_info}")
    print(f"📞 Phone: {phone_info}")
    
    # Show CC if available
    cc_info = thread.get('cc', '')
    if cc_info and cc_info.strip():
        print(f"📋 CC: {cc_info}")
    
    # Show BCC if available
    bcc_info = thread.get('bcc', '')
    if bcc_info and bcc_info.strip():
        print(f"📋 BCC: {bcc_info}")
    
    # Show additional contact information if available
    if thread.get('contact'):
        contact = thread.get('contact', {})
        print(f"👤 Contact Name: {contact.get('name', 'N/A')}")
        print(f"📧 Contact Email: {contact.get('email', 'N/A')}")
    
    if thread.get('sender'):
        sender = thread.get('sender', {})
        print(f"👤 Sender Name: {sender.get('name', 'N/A')}")
        print(f"📧 Sender Email: {sender.get('email', 'N/A')}")
    
    # Show all available fields for debugging
    print(f"\n🔍 ALL AVAILABLE FIELDS:")
    for key, value in thread.items():
        if key not in ['content', 'attachments', 'contact', 'sender']:  # Skip large fields
            print(f"   {key}: {value}")
    
    # Thread content
    content = thread.get('content', '')
    if content:
        print(f"\n📝 CONTENT:")
        print(f"{'─'*40}")
        # Clean and display content
        clean_content = content.replace('<br>', '\n').replace('<p>', '').replace('</p>', '\n')
        print(clean_content[:500] + "..." if len(clean_content) > 500 else clean_content)
        print(f"{'─'*40}")
    
    # Thread properties
    print(f"\n🔧 PROPERTIES:")
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
    
    # CC and BCC
    if thread.get('cc'):
        print(f"\n📋 CC: {', '.join(thread.get('cc', []))}")
    if thread.get('bcc'):
        print(f"📋 BCC: {', '.join(thread.get('bcc', []))}")

def display_comment_details(comment, index):
    """Display detailed comment information"""
    print(f"\n{'─'*80}")
    print(f"💭 COMMENT #{index}")
    print(f"{'─'*80}")
    
    print(f"🆔 Comment ID: {comment.get('id', 'N/A')}")
    print(f"👤 Author: {comment.get('author', {}).get('name', 'Unknown')}")
    print(f"📧 Author Email: {comment.get('author', {}).get('email', 'N/A')}")
    print(f"⏰ Created: {format_datetime(comment.get('createdTime'))}")
    print(f"🔄 Modified: {format_datetime(comment.get('modifiedTime'))}")
    
    # Comment content
    content = comment.get('content', '')
    if content:
        print(f"\n📝 CONTENT:")
        print(f"{'─'*40}")
        clean_content = content.replace('<br>', '\n').replace('<p>', '').replace('</p>', '\n')
        print(clean_content[:500] + "..." if len(clean_content) > 500 else clean_content)
        print(f"{'─'*40}")
    
    # Comment properties
    print(f"\n🔧 PROPERTIES:")
    print(f"   Is Public: {comment.get('isPublic', False)}")
    print(f"   Is Private: {comment.get('isPrivate', False)}")
    print(f"   Is Internal: {comment.get('isInternal', False)}")

def display_ticket_with_threads_and_comments(zoho, ticket):
    """Display ticket with all threads and comments"""
    print(f"\n{'='*100}")
    print(f"🎫 TICKET DETAILS: {ticket.get('ticketNumber', 'N/A')}")
    print(f"{'='*100}")
    
    # Basic ticket info
    print(f"📋 Subject: {ticket.get('subject', 'No Subject')}")
    print(f"📧 Email: {ticket.get('email', 'N/A')}")
    print(f"📊 Status: {ticket.get('status', 'Unknown')}")
    print(f"⏰ Created: {format_datetime(ticket.get('createdTime'))}")
    print(f"💬 Thread Count: {ticket.get('threadCount', '0')}")
    print(f"💭 Comment Count: {ticket.get('commentCount', '0')}")
    
    ticket_id = ticket.get('id')
    if not ticket_id:
        print("❌ No ticket ID found")
        return
    
    # Get and display threads
    print(f"\n{'🔍 FETCHING THREADS...':^100}")
    threads_response = get_ticket_threads(zoho, ticket_id)
    
    if threads_response and threads_response.get('data'):
        threads = threads_response['data']
        print(f"✅ Found {len(threads)} threads")
        
        for i, thread in enumerate(threads, 1):
            display_thread_details(thread, i)
            
            # Ask user if they want to continue
            if i < len(threads):
                continue_choice = input(f"\nPress Enter for next thread (or 'q' to skip remaining threads): ").strip().lower()
                if continue_choice == 'q':
                    break
    else:
        print("❌ No threads found or error retrieving threads")
    
    # Get and display comments
    print(f"\n{'🔍 FETCHING COMMENTS...':^100}")
    comments_response = get_ticket_comments(zoho, ticket_id)
    
    if comments_response and comments_response.get('data'):
        comments = comments_response['data']
        print(f"✅ Found {len(comments)} comments")
        
        for i, comment in enumerate(comments, 1):
            display_comment_details(comment, i)
            
            # Ask user if they want to continue
            if i < len(comments):
                continue_choice = input(f"\nPress Enter for next comment (or 'q' to skip remaining comments): ").strip().lower()
                if continue_choice == 'q':
                    break
    else:
        print("❌ No comments found or error retrieving comments")

def main():
    """Main function"""
    print("🚀 DETAILED THREADS & COMMENTS VIEWER")
    print("="*100)
    print("This script displays detailed threads and comments for Zoho Desk tickets")
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
        print("1. View all tickets with threads and comments")
        print("2. Search specific ticket by number")
        print("3. View recent tickets with threads")
        print("0. Exit")
        
        choice = input("\nChoose option (0-3): ").strip()
        
        if choice == "0":
            print("👋 Goodbye!")
            break
        elif choice == "1":
            view_all_tickets_detailed(zoho)
        elif choice == "2":
            search_specific_ticket(zoho)
        elif choice == "3":
            view_recent_tickets(zoho)
        else:
            print("❌ Invalid choice")

def view_all_tickets_detailed(zoho):
    """View all tickets with detailed threads and comments"""
    print("\n🔍 Fetching all tickets...")
    tickets_response = zoho.get_tickets(limit=10)  # Limit to 10 for detailed view
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets")
    
    for i, ticket in enumerate(tickets, 1):
        print(f"\n{'📋 PROCESSING TICKET':^100}")
        print(f"Ticket {i} of {len(tickets)}: {ticket.get('ticketNumber', 'N/A')}")
        
        display_ticket_with_threads_and_comments(zoho, ticket)
        
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
        display_ticket_with_threads_and_comments(zoho, found_ticket)
    else:
        print(f"❌ Ticket {ticket_number} not found")

def view_recent_tickets(zoho):
    """View recent tickets with threads"""
    print("\n🔍 Fetching recent tickets...")
    tickets_response = zoho.get_tickets(limit=5)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} recent tickets")
    
    for i, ticket in enumerate(tickets, 1):
        print(f"\n{'📋 PROCESSING RECENT TICKET':^100}")
        print(f"Ticket {i} of {len(tickets)}: {ticket.get('ticketNumber', 'N/A')}")
        
        # Show basic ticket info
        print(f"📋 Subject: {ticket.get('subject', 'No Subject')}")
        print(f"📊 Status: {ticket.get('status', 'Unknown')}")
        print(f"💬 Threads: {ticket.get('threadCount', '0')}")
        print(f"💭 Comments: {ticket.get('commentCount', '0')}")
        
        # Ask if user wants detailed view
        detail_choice = input("View detailed threads and comments? (y/n): ").strip().lower()
        if detail_choice == 'y':
            display_ticket_with_threads_and_comments(zoho, ticket)
        
        if i < len(tickets):
            continue_choice = input("Continue to next ticket? (y/n): ").strip().lower()
            if continue_choice != 'y':
                break

if __name__ == "__main__":
    main()
