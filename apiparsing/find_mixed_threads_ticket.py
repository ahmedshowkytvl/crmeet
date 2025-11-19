#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Find tickets with both incoming and outgoing threads
"""

from zoho_api import ZohoAPI

def find_mixed_threads_ticket():
    """Find a ticket with both incoming and outgoing threads"""
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
    
    print("🔍 Searching for tickets with both incoming and outgoing threads:")
    print("="*70)
    
    found = False
    for ticket in tickets:
        thread_count = int(ticket.get('threadCount', 0))
        if thread_count > 2:  # Need at least 3 threads to have both types
            # Get threads for this ticket
            threads_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket.get('id')}/threads", 
                                               params={'orgId': zoho.config.ORG_ID})
            
            if threads_response and 'data' in threads_response:
                threads = threads_response['data']
                
                # Check if it has both incoming and outgoing
                incoming_count = len([t for t in threads if t.get('direction') == 'in'])
                outgoing_count = len([t for t in threads if t.get('direction') == 'out'])
                
                if incoming_count > 0 and outgoing_count > 0:
                    print(f"✅ Found: #{ticket.get('ticketNumber')} - {thread_count} threads")
                    print(f"   Subject: {ticket.get('subject', 'No Subject')}")
                    print(f"   Status: {ticket.get('status', 'Unknown')}")
                    print(f"   Incoming: {incoming_count}, Outgoing: {outgoing_count}")
                    
                    # Show thread details
                    print(f"\n📋 Thread Details:")
                    for i, thread in enumerate(threads, 1):
                        direction = thread.get('direction', 'Unknown')
                        time = thread.get('createdTime', 'No time')
                        print(f"   {i}. {direction} - {time}")
                    
                    found = True
                    break
    
    if not found:
        print("❌ No tickets with both incoming and outgoing threads found")

if __name__ == "__main__":
    find_mixed_threads_ticket()
