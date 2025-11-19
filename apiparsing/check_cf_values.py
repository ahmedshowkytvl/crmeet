#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Check all CF Closed By values to see the actual options
"""

from zoho_api import ZohoAPI

def check_all_cf_values():
    """Check all CF Closed By values"""
    zoho = ZohoAPI()
    
    # Get access token
    token = zoho.get_access_token()
    if not token:
        print("❌ Failed to get access token")
        return
    
    # Get more tickets to see variety
    tickets_response = zoho.get_tickets(limit=100)
    if not tickets_response or 'data' not in tickets_response:
        print("❌ Failed to get tickets")
        return
    
    tickets = tickets_response['data']
    
    print("🔍 Checking ALL CF Closed By values:")
    print("="*60)
    
    cf_values = {}
    closed_tickets = []
    
    for ticket in tickets:
        status = ticket.get('status', 'Unknown')
        cf_closed_by = ticket.get('cf', {}).get('cf_closed_by')
        
        if cf_closed_by is not None:
            cf_values[cf_closed_by] = cf_values.get(cf_closed_by, 0) + 1
        
        if status == 'Closed':
            closed_tickets.append({
                'number': ticket.get('ticketNumber'),
                'cf_closed_by': cf_closed_by,
                'subject': ticket.get('subject', '')[:50] + '...'
            })
    
    print("📊 CF Closed By Values Found:")
    for value, count in sorted(cf_values.items()):
        print(f"   '{value}': {count} tickets")
    
    print(f"\n📋 Closed Tickets with CF Closed By values:")
    print("-" * 60)
    
    for ticket in closed_tickets[:10]:  # Show first 10
        print(f"#{ticket['number']} - CF: '{ticket['cf_closed_by']}' - {ticket['subject']}")
    
    if len(closed_tickets) > 10:
        print(f"... and {len(closed_tickets) - 10} more closed tickets")

if __name__ == "__main__":
    check_all_cf_values()
