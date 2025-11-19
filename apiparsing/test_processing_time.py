#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test script to check processing time calculation
"""

from zoho_api import ZohoAPI
from datetime import datetime

def calculate_processing_time(threads):
    """Calculate processing time between last outgoing and incoming threads"""
    if not threads or len(threads) < 2:
        return None
    
    # Separate outgoing and incoming threads
    outgoing_threads = [t for t in threads if t.get('direction') == 'out']
    incoming_threads = [t for t in threads if t.get('direction') == 'in']
    
    if not outgoing_threads or not incoming_threads:
        return None
    
    # Sort by created time
    outgoing_threads.sort(key=lambda x: x.get('createdTime', ''))
    incoming_threads.sort(key=lambda x: x.get('createdTime', ''))
    
    # Get last outgoing and last incoming
    last_outgoing = outgoing_threads[-1]
    last_incoming = incoming_threads[-1]
    
    print(f"🔍 Last Outgoing Thread: {last_outgoing.get('createdTime')}")
    print(f"🔍 Last Incoming Thread: {last_incoming.get('createdTime')}")
    
    # Check if last incoming is after last outgoing
    try:
        outgoing_time = datetime.fromisoformat(last_outgoing.get('createdTime', '').replace('Z', '+00:00'))
        incoming_time = datetime.fromisoformat(last_incoming.get('createdTime', '').replace('Z', '+00:00'))
        
        if incoming_time > outgoing_time:
            # Calculate difference
            time_diff = incoming_time - outgoing_time
            
            # Format the difference
            days = time_diff.days
            hours, remainder = divmod(time_diff.seconds, 3600)
            minutes, seconds = divmod(remainder, 60)
            
            if days > 0:
                return f"{days}d {hours}h {minutes}m"
            elif hours > 0:
                return f"{hours}h {minutes}m"
            else:
                return f"{minutes}m {seconds}s"
        else:
            return "No response yet"
    except Exception as e:
        print(f"❌ Error calculating time: {e}")
        return "Unable to calculate"
    
    return None

def test_processing_time():
    """Test processing time calculation"""
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    # Get tickets
    tickets_response = zoho.get_tickets(limit=10)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    
    print("🔍 Testing Processing Time Calculation")
    print("="*60)
    
    for i, ticket in enumerate(tickets[:5], 1):  # Test first 5 tickets
        ticket_number = ticket.get('ticketNumber', 'N/A')
        thread_count = int(ticket.get('threadCount', 0))
        
        print(f"\n{i}. Ticket #{ticket_number} - Threads: {thread_count}")
        
        if thread_count > 1:
            # Get threads for this ticket
            threads_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket.get('id')}/threads", 
                                               params={'orgId': zoho.config.ORG_ID})
            
            if threads_response and 'data' in threads_response:
                threads = threads_response['data']
                processing_time = calculate_processing_time(threads)
                
                if processing_time:
                    print(f"   ⏱️ Processing Time: {processing_time}")
                else:
                    print(f"   ❌ No processing time calculated")
            else:
                print(f"   ❌ Failed to get threads")
        else:
            print(f"   ⚠️ Not enough threads for calculation")

if __name__ == "__main__":
    test_processing_time()
