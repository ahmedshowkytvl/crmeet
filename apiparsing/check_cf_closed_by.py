#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Check CF Closed By values for recent tickets
"""

from zoho_api import ZohoAPI

def check_cf_closed_by():
    """Check CF Closed By values"""
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    # Get tickets
    tickets_response = zoho.get_tickets(limit=20)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    
    print("🔍 Checking CF Closed By for recent 20 tickets:")
    print("="*60)
    
    closed_count = 0
    cf_closed_by_count = 0
    
    for i, ticket in enumerate(tickets, 1):
        ticket_number = ticket.get('ticketNumber', 'N/A')
        status = ticket.get('status', 'Unknown')
        cf_closed_by = ticket.get('cf', {}).get('cf_closed_by')
        
        print(f"{i:2d}. #{ticket_number} - {status}")
        print(f"    CF Closed By: {cf_closed_by if cf_closed_by else 'N/A'}")
        
        if status == 'Closed':
            closed_count += 1
            if cf_closed_by and cf_closed_by != 'N/A':
                cf_closed_by_count += 1
                print(f"    ✅ Has CF Closed By value")
            else:
                print(f"    ❌ No CF Closed By value")
        print()
    
    print("="*60)
    print(f"📊 Summary:")
    print(f"   Total tickets: {len(tickets)}")
    print(f"   Closed tickets: {closed_count}")
    print(f"   Closed tickets with CF Closed By: {cf_closed_by_count}")
    print(f"   Closed tickets without CF Closed By: {closed_count - cf_closed_by_count}")

if __name__ == "__main__":
    check_cf_closed_by()
