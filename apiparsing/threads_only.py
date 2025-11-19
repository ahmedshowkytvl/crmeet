#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Threads Only Viewer - عرض الـ Threads فقط
سكريبت مبسط لعرض الـ Threads والـ Comments فقط
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
        return dt.strftime("%Y-%m-%d %H:%M")
    except:
        return date_string

def get_threads_for_ticket(zoho, ticket_id):
    """Get threads for a ticket"""
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
        response = requests.get(url, headers=headers, params={'limit': 20})
        response.raise_for_status()
        
        if response.status_code == 204 or not response.text.strip():
            return {"data": []}
        
        return response.json()
    except Exception as e:
        print(f"Error: {e}")
        return None

def display_thread_summary(thread, index):
    """Display thread summary"""
    print(f"\n{'─'*60}")
    print(f"💬 THREAD #{index}")
    print(f"{'─'*60}")
    
    print(f"📧 Channel: {thread.get('channel', 'Unknown')}")
    print(f"📤 Direction: {thread.get('direction', 'Unknown')}")
    print(f"⏰ Time: {format_datetime(thread.get('createdTime'))}")
    # Better handling of sender/receiver information
    from_info = thread.get('from', 'N/A')
    to_info = thread.get('to', 'N/A')
    
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
    
    # Show content preview
    content = thread.get('content', '')
    if content:
        clean_content = content.replace('<br>', ' ').replace('<p>', '').replace('</p>', ' ')
        preview = clean_content[:200] + "..." if len(clean_content) > 200 else clean_content
        print(f"📝 Content: {preview}")
    
    # Show attachments if any
    if thread.get('attachments'):
        print(f"📎 Attachments: {len(thread.get('attachments', []))} file(s)")

def main():
    """Main function - Quick threads view"""
    print("🚀 THREADS VIEWER - ZOHO DESK")
    print("="*60)
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("✅ Connected to Zoho Desk")
    
    # Get tickets
    print("\n📋 Fetching tickets...")
    tickets_response = zoho.get_tickets(limit=10)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets")
    
    # Process each ticket
    for i, ticket in enumerate(tickets, 1):
        ticket_id = ticket.get('id')
        ticket_number = ticket.get('ticketNumber', 'N/A')
        subject = ticket.get('subject', 'No Subject')
        
        print(f"\n{'='*60}")
        print(f"🎫 TICKET #{i}: {ticket_number}")
        print(f"📝 Subject: {subject}")
        print(f"📊 Status: {ticket.get('status', 'Unknown')}")
        print(f"💬 Thread Count: {ticket.get('threadCount', '0')}")
        print(f"{'='*60}")
        
        # Get threads for this ticket
        threads_response = get_threads_for_ticket(zoho, ticket_id)
        
        if threads_response and threads_response.get('data'):
            threads = threads_response['data']
            print(f"📋 Found {len(threads)} threads:")
            
            for j, thread in enumerate(threads, 1):
                display_thread_summary(thread, j)
        else:
            print("❌ No threads found for this ticket")
        
        # Ask if user wants to continue
        if i < len(tickets):
            continue_choice = input(f"\nPress Enter for next ticket (or 'q' to quit): ").strip().lower()
            if continue_choice == 'q':
                break
    
    print(f"\n{'='*60}")
    print("✅ THREADS VIEW COMPLETED")
    print(f"{'='*60}")

if __name__ == "__main__":
    main()
