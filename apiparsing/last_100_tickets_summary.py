#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Display last 100 tickets summary
"""

from zoho_api import ZohoAPI
from datetime import datetime

def format_date(date_string):
    """Format date string to readable format"""
    if not date_string:
        return "N/A"
    try:
        date = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return date.strftime("%m/%d/%Y %I:%M:%S %p")
    except:
        return date_string

def get_last_100_tickets_summary():
    """Get and display last 100 tickets summary"""
    zoho = ZohoAPI()
    
    print("🔍 Getting last 100 tickets summary...")
    
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
    
    print(f"\n{'='*100}")
    print(f"📋 LAST {len(tickets)} TICKETS SUMMARY")
    print(f"{'='*100}")
    
    # Display summary for each ticket
    for i, ticket in enumerate(tickets, 1):
        cf_closed_by = ticket.get('cf', {}).get('cf_closed_by')
        closed_by_info = f" | 🔒 Closed by: {cf_closed_by}" if cf_closed_by else ""
        
        print(f"\n{i:3d}. #{ticket.get('ticketNumber', 'N/A')} - {ticket.get('status', 'Unknown')}{closed_by_info}")
        print(f"     📋 {ticket.get('subject', 'No Subject')}")
        print(f"     📧 {ticket.get('email', 'N/A')} | 📅 {format_date(ticket.get('createdTime'))}")
        print(f"     🏷️ {ticket.get('category') or ticket.get('subCategory') or 'No Category'} | ⚡ {ticket.get('priority', 'Not Set')}")
        print(f"     👤 {'Assigned' if ticket.get('assigneeId') else 'Not Assigned'} | 💬 {ticket.get('threadCount', 0)} threads | 💭 {ticket.get('commentCount', 0)} comments")
        print(f"     🏢 Dept: {ticket.get('departmentId', 'N/A')} | 📋 Layout: {ticket.get('layoutId', 'N/A')}")
        print(f"     {'-'*80}")
    
    print(f"\n🎉 Summary of {len(tickets)} tickets completed!")

if __name__ == "__main__":
    get_last_100_tickets_summary()
