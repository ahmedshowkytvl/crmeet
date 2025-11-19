#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Display last 100 tickets with detailed information (same format as single ticket)
"""

from zoho_api import ZohoAPI
from datetime import datetime
import time

def format_date(date_string):
    """Format date string to readable format"""
    if not date_string:
        return "N/A"
    try:
        date = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return date.strftime("%m/%d/%Y %I:%M:%S %p")
    except:
        return date_string

def display_ticket_detailed(ticket, index):
    """Display single ticket with detailed information"""
    print(f"\n{'='*80}")
    print(f"🎫 TICKET #{ticket.get('ticketNumber', 'N/A')} INFORMATION ({index}/100)")
    print(f"{'='*80}")
    
    print(f"📋 Subject:")
    print(f"{ticket.get('subject', 'No Subject')}")
    
    print(f"\n📧 Email:")
    print(f"{ticket.get('email', 'N/A')}")
    
    print(f"\n📊 Status:")
    print(f"{ticket.get('status', 'Unknown')}")
    
    print(f"\n🏷️ Category:")
    category = ticket.get('category') or ticket.get('subCategory') or 'No Category Set'
    print(f"{category}")
    
    print(f"\n⚡ Priority:")
    print(f"{ticket.get('priority', 'Not Set')}")
    
    print(f"\n📅 Created:")
    print(f"{format_date(ticket.get('createdTime'))}")
    
    print(f"\n📅 Due:")
    print(f"{format_date(ticket.get('dueDate'))}")
    
    print(f"\n📞 Phone:")
    print(f"{ticket.get('phone', 'N/A')}")
    
    print(f"\n👤 Assignee:")
    assignee = "Assigned" if ticket.get('assigneeId') else "Not Assigned"
    print(f"{assignee}")
    
    print(f"\n🏢 Department:")
    dept_id = ticket.get('departmentId', 'N/A')
    print(f"ID: {dept_id}")
    
    print(f"\n🌐 Channel:")
    print(f"{ticket.get('channel', 'Unknown')}")
    
    print(f"\n💬 Threads:")
    print(f"{ticket.get('threadCount', 0)}")
    
    print(f"\n💭 Comments:")
    print(f"{ticket.get('commentCount', 0)}")
    
    print(f"\n🏗️ Layout ID:")
    print(f"{ticket.get('layoutId', 'N/A')}")
    
    print(f"\n👥 Contact ID:")
    print(f"{ticket.get('contactId', 'N/A')}")
    
    print(f"\n🔗 Relationship:")
    print(f"{ticket.get('relationshipType', 'None')}")
    
    print(f"\n🌐 Language:")
    print(f"{ticket.get('language', 'Unknown')}")
    
    print(f"\n📅 Closed:")
    print(f"{format_date(ticket.get('closedTime'))}")
    
    print(f"\n🗂️ Status Type:")
    print(f"{ticket.get('statusType', 'Unknown')}")
    
    print(f"\n🚫 Is Spam:")
    print(f"{'Yes' if ticket.get('isSpam') else 'No'}")
    
    print(f"\n📦 Is Archived:")
    print(f"{'Yes' if ticket.get('isArchived') else 'No'}")
    
    print(f"\n⏱️ On Hold Time:")
    print(f"{'On Hold' if ticket.get('onholdTime') else 'Not On Hold'}")
    
    print(f"\n📊 Task Count:")
    print(f"{ticket.get('taskCount', 0)}")
    
    print(f"\n📎 Attachment Count:")
    print(f"{ticket.get('attachmentCount', 0)}")
    
    print(f"\n👥 Follower Count:")
    print(f"{ticket.get('followerCount', 0)}")
    
    print(f"\n🏷️ Classification:")
    print(f"{ticket.get('classification', 'None')}")
    
    print(f"\n📝 Resolution:")
    print(f"{ticket.get('resolution', 'No Resolution')}")
    
    print(f"\n👤 Created By:")
    print(f"{ticket.get('createdBy', 'Unknown')}")
    
    print(f"\n✏️ Modified By:")
    print(f"{ticket.get('modifiedBy', 'Unknown')}")
    
    # CF Closed By
    print(f"\n💥 CF Closed By:")
    cf_closed_by = ticket.get('cf', {}).get('cf_closed_by')
    if cf_closed_by:
        print(f"{cf_closed_by}")
    else:
        print(f"N/A")
    
    print(f"\n{'='*80}")

def get_last_100_tickets_detailed():
    """Get and display last 100 tickets with detailed information"""
    zoho = ZohoAPI()
    
    print("🔍 Getting last 100 tickets with detailed information...")
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("Successfully obtained access token")
    print("✅ Access token obtained")
    
    # Get tickets
    tickets_response = zoho.get_tickets(limit=100)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    print(f"✅ Found {len(tickets)} tickets")
    
    # Display each ticket with detailed information
    for i, ticket in enumerate(tickets, 1):
        display_ticket_detailed(ticket, i)
        
        # Add a small delay to avoid overwhelming the API
        if i < len(tickets):
            time.sleep(0.1)
    
    print(f"\n🎉 Displayed {len(tickets)} tickets with detailed information successfully!")

if __name__ == "__main__":
    get_last_100_tickets_detailed()
