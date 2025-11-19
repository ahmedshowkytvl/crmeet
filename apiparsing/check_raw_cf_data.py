#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Check raw CF data for closed tickets
"""

from zoho_api import ZohoAPI
import json

def check_raw_cf_data():
    """Check raw CF data"""
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
    
    print("🔍 Raw CF Data for closed tickets:")
    print("="*60)
    
    for ticket in tickets:
        if ticket.get('status') == 'Closed':
            print(f"📋 Ticket #{ticket.get('ticketNumber')}")
            print(f"Subject: {ticket.get('subject', 'N/A')}")
            print(f"CF Data: {json.dumps(ticket.get('cf', {}), indent=2)}")
            print("-" * 40)
            break

if __name__ == "__main__":
    check_raw_cf_data()
