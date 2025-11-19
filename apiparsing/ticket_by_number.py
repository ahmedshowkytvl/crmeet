#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get ticket information by ticket number (not ID)
"""

from zoho_api import ZohoAPI
import sys
from datetime import datetime

def get_ticket_by_number(ticket_number):
    """Get ticket information by ticket number"""
    zoho = ZohoAPI()
    
    print(f"🔍 Searching for ticket number: {ticket_number}")
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("✅ Access token obtained")
    
    # Get tickets list to find the ticket
    tickets_response = zoho.get_tickets(limit=100)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets list")
        return
    
    tickets = tickets_response['data']
    target_ticket = None
    
    for ticket in tickets:
        if ticket.get('ticketNumber') == ticket_number:
            target_ticket = ticket
            break
    
    if not target_ticket:
        print(f"❌ Ticket #{ticket_number} not found in recent 100 tickets")
        print(f"📊 Searched through {len(tickets)} tickets")
        return
    
    ticket = target_ticket
    
    # Format date
    def format_date(date_string):
        if not date_string: return "N/A"
        try:
            date = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
            return date.strftime("%m/%d/%Y %I:%M:%S %p")
        except:
            return date_string
    
    # Display information
    print(f"\n{'='*80}")
    print(f"🎫 TICKET #{ticket_number} INFORMATION")
    print(f"{'='*80}")
    
    print(f"📋 Subject: {ticket.get('subject', 'No Subject')}")
    print(f"📧 Email: {ticket.get('email', 'N/A')}")
    print(f"📊 Status: {ticket.get('status', 'Unknown')}")
    print(f"🏷️ Category: {ticket.get('category') or ticket.get('subCategory') or 'No Category Set'}")
    print(f"⚡ Priority: {ticket.get('priority', 'Not Set')}")
    print(f"📅 Created: {format_date(ticket.get('createdTime'))}")
    print(f"📅 Due: {format_date(ticket.get('dueDate'))}")
    print(f"📞 Phone: {ticket.get('phone', 'N/A')}")
    print(f"👤 Assignee: {'Assigned' if ticket.get('assigneeId') else 'Not Assigned'}")
    print(f"🏢 Department: ID: {ticket.get('departmentId', 'N/A')}")
    print(f"🌐 Channel: {ticket.get('channel', 'Unknown')}")
    print(f"💬 Threads: {ticket.get('threadCount', 0)}")
    print(f"💭 Comments: {ticket.get('commentCount', 0)}")
    print(f"🏗️ Layout ID: {ticket.get('layoutId', 'N/A')}")
    print(f"👥 Contact ID: {ticket.get('contactId', 'N/A')}")
    print(f"🔗 Relationship: {ticket.get('relationshipType', 'None')}")
    print(f"🌐 Language: {ticket.get('language', 'Unknown')}")
    print(f"📅 Closed: {format_date(ticket.get('closedTime'))}")
    print(f"🗂️ Status Type: {ticket.get('statusType', 'Unknown')}")
    print(f"🚫 Is Spam: {'Yes' if ticket.get('isSpam') else 'No'}")
    print(f"📦 Is Archived: {'Yes' if ticket.get('isArchived') else 'No'}")
    print(f"⏱️ On Hold Time: {'On Hold' if ticket.get('onholdTime') else 'Not On Hold'}")
    print(f"📊 Task Count: {ticket.get('taskCount', 0)}")
    print(f"📎 Attachment Count: {ticket.get('attachmentCount', 0)}")
    print(f"👥 Follower Count: {ticket.get('followerCount', 0)}")
    print(f"🏷️ Classification: {ticket.get('classification', 'None')}")
    print(f"📝 Resolution: {ticket.get('resolution', 'No Resolution')}")
    print(f"👤 Created By: {ticket.get('createdBy', 'Unknown')}")
    print(f"✏️ Modified By: {ticket.get('modifiedBy', 'Unknown')}")
    
    # CF Closed By
    cf_closed_by = ticket.get('cf', {}).get('cf_closed_by')
    print(f"💥 CF Closed By: {cf_closed_by if cf_closed_by else 'N/A'}")
    
    print(f"\n{'='*80}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python ticket_by_number.py <ticket_number>")
        print("Example: python ticket_by_number.py 2834200")
        sys.exit(1)
    
    get_ticket_by_number(sys.argv[1])
