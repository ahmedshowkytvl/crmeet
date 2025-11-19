#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
List recent tickets
"""

from zoho_api import ZohoAPI

def list_recent_tickets():
    """List recent tickets"""
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
    
    print(f"📋 Recent {len(tickets)} tickets:")
    print(f"{'='*60}")
    
    for ticket in tickets:
        print(f"#{ticket['ticketNumber']} - {ticket['subject']} - {ticket['status']}")

if __name__ == "__main__":
    list_recent_tickets()
