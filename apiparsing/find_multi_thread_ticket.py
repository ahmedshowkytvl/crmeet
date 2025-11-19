#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Find tickets with multiple threads
"""

from zoho_api import ZohoAPI

def find_multi_thread_ticket():
    """Find a ticket with multiple threads"""
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    # Get tickets
    tickets_response = zoho.get_tickets(limit=100)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    
    print("🔍 Searching for tickets with multiple threads:")
    print("="*60)
    
    found = False
    for ticket in tickets:
        thread_count = int(ticket.get('threadCount', 0))
        if thread_count > 1:
            print(f"✅ Found: #{ticket.get('ticketNumber')} - {thread_count} threads")
            print(f"   Subject: {ticket.get('subject', 'No Subject')}")
            print(f"   Status: {ticket.get('status', 'Unknown')}")
            found = True
            break
    
    if not found:
        print("❌ No tickets with multiple threads found in first 100 tickets")

if __name__ == "__main__":
    find_multi_thread_ticket()
