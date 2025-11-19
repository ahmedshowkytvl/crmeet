#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test processing time calculation on ticket #2821499
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
    
    print(f"📊 Threads Analysis:")
    print(f"   Total threads: {len(threads)}")
    print(f"   Outgoing threads: {len(outgoing_threads)}")
    print(f"   Incoming threads: {len(incoming_threads)}")
    
    if not outgoing_threads or not incoming_threads:
        return None
    
    # Sort by created time
    outgoing_threads.sort(key=lambda x: x.get('createdTime', ''))
    incoming_threads.sort(key=lambda x: x.get('createdTime', ''))
    
    # Get last outgoing and last incoming
    last_outgoing = outgoing_threads[-1]
    last_incoming = incoming_threads[-1]
    
    print(f"\n🔍 Last Outgoing Thread:")
    print(f"   Time: {last_outgoing.get('createdTime')}")
    print(f"   Direction: {last_outgoing.get('direction')}")
    
    print(f"\n🔍 Last Incoming Thread:")
    print(f"   Time: {last_incoming.get('createdTime')}")
    print(f"   Direction: {last_incoming.get('direction')}")
    
    # Check if last incoming is after last outgoing
    try:
        outgoing_time = datetime.fromisoformat(last_outgoing.get('createdTime', '').replace('Z', '+00:00'))
        incoming_time = datetime.fromisoformat(last_incoming.get('createdTime', '').replace('Z', '+00:00'))
        
        print(f"\n🕐 Time Comparison:")
        print(f"   Outgoing: {outgoing_time}")
        print(f"   Incoming: {incoming_time}")
        
        if incoming_time > outgoing_time:
            # Calculate difference
            time_diff = incoming_time - outgoing_time
            
            print(f"   Difference: {time_diff}")
            
            # Format the difference
            days = time_diff.days
            hours, remainder = divmod(time_diff.seconds, 3600)
            minutes, seconds = divmod(remainder, 60)
            
            if days > 0:
                result = f"{days}d {hours}h {minutes}m"
            elif hours > 0:
                result = f"{hours}h {minutes}m"
            else:
                result = f"{minutes}m {seconds}s"
            
            print(f"\n⏱️ Processing Time: {result}")
            return result
        else:
            print(f"\n❌ Last incoming is before last outgoing - No response yet")
            return "No response yet"
    except Exception as e:
        print(f"❌ Error calculating time: {e}")
        return "Unable to calculate"
    
    return None

def test_processing_calculation():
    """Test processing time calculation"""
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    # Get tickets list to find the ticket
    tickets_response = zoho.get_tickets(limit=100)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    target_ticket = None
    
    # Find ticket #2821499
    for ticket in tickets:
        if ticket.get('ticketNumber') == '2821499':
            target_ticket = ticket
            break
    
    if not target_ticket:
        print("❌ Ticket #2821499 not found in recent tickets")
        return
    
    print(f"🎫 Testing Ticket #{target_ticket.get('ticketNumber')}")
    print(f"   Subject: {target_ticket.get('subject', 'No Subject')}")
    print(f"   Status: {target_ticket.get('status', 'Unknown')}")
    print(f"   Threads: {target_ticket.get('threadCount', 0)}")
    
    # Get threads for this ticket
    threads_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{target_ticket.get('id')}/threads", 
                                       params={'orgId': zoho.config.ORG_ID})
    
    if threads_response and 'data' in threads_response:
        threads = threads_response['data']
        print(f"\n📋 All Threads:")
        for i, thread in enumerate(threads, 1):
            direction = thread.get('direction', 'Unknown')
            time = thread.get('createdTime', 'No time')
            print(f"   {i}. {direction} - {time}")
        
        processing_time = calculate_processing_time(threads)
        
        if processing_time:
            print(f"\n🎉 SUCCESS! Processing Time Calculated: {processing_time}")
        else:
            print(f"\n❌ Failed to calculate processing time")
    else:
        print("❌ Failed to get threads")

if __name__ == "__main__":
    test_processing_calculation()
