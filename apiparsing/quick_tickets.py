#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick Ticket Viewer - عرض سريع للتذاكر
يعرض تفاصيل التذاكر فوراً عند التشغيل
"""

from zoho_api import ZohoAPI
import json
from datetime import datetime

def format_datetime(date_string):
    """Format datetime string to readable format"""
    if not date_string:
        return "N/A"
    try:
        dt = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return dt.strftime("%Y-%m-%d %H:%M")
    except:
        return date_string

def display_quick_ticket(ticket, index):
    """Display ticket information in a compact format"""
    print(f"\n{'='*60}")
    print(f"🎫 TICKET #{index}")
    print(f"{'='*60}")
    
    print(f"📋 Number: {ticket.get('ticketNumber', 'N/A')}")
    print(f"📝 Subject: {ticket.get('subject', 'No Subject')}")
    print(f"📧 Email: {ticket.get('email', 'N/A')}")
    print(f"📊 Status: {ticket.get('status', 'Unknown')} | Priority: {ticket.get('priority', 'Not Set')}")
    print(f"🏷️ Category: {ticket.get('category', 'No Category')} | Channel: {ticket.get('channel', 'Unknown')}")
    print(f"⏰ Created: {format_datetime(ticket.get('createdTime'))}")
    print(f"📅 Due: {format_datetime(ticket.get('dueDate'))}")
    print(f"💬 Comments: {ticket.get('commentCount', '0')} | Threads: {ticket.get('threadCount', '0')}")
    print(f"👤 Assignee: {ticket.get('assigneeId', 'Not Assigned')}")
    print(f"🏢 Department: {ticket.get('departmentId', 'N/A')}")
    
    if ticket.get('webUrl'):
        print(f"🌐 URL: {ticket.get('webUrl')}")

def main():
    """Main function - displays tickets immediately"""
    print("🚀 QUICK TICKET VIEWER - ZOHO DESK")
    print("="*60)
    print("Fetching tickets...")
    
    # Create Zoho API object
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    print("✅ Connected to Zoho Desk")
    
    # Get tickets
    tickets_response = zoho.get_tickets(limit=20)
    
    if not tickets_response or 'data' not in tickets_response:
        print("❌ No tickets found")
        return
    
    tickets = tickets_response['data']
    print(f"📋 Found {len(tickets)} tickets\n")
    
    # Display all tickets
    for i, ticket in enumerate(tickets, 1):
        display_quick_ticket(ticket, i)
        
        # Add separator between tickets
        if i < len(tickets):
            print("\n" + "─"*60)
    
    print(f"\n{'='*60}")
    print(f"✅ DISPLAYED {len(tickets)} TICKETS")
    print(f"{'='*60}")
    
    # Ask if user wants to save to file
    save_choice = input("\n💾 Save tickets to JSON file? (y/n): ").strip().lower()
    if save_choice == 'y':
        filename = f"tickets_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        try:
            with open(filename, 'w', encoding='utf-8') as f:
                json.dump(tickets_response, f, ensure_ascii=False, indent=2)
            print(f"✅ Saved to: {filename}")
        except Exception as e:
            print(f"❌ Error saving: {e}")

if __name__ == "__main__":
    main()
