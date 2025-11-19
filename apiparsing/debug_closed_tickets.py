#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Debug Manual Closed Tickets - Check what's actually in the data
تشخيص التذاكر المغلقة يدوياً - فحص البيانات الفعلية
"""

import requests
import json
import time
from datetime import datetime
from config import ZohoConfig

def get_access_token():
    """Get access token"""
    config = ZohoConfig()
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    try:
        response = requests.post(config.TOKEN_URL, data=token_data)
        if response.status_code == 200:
            return response.json()['access_token'], config
        else:
            print(f"Token failed: {response.status_code}")
            return None, None
    except Exception as e:
        print(f"Token error: {e}")
        return None, None

def debug_closed_tickets():
    """Debug closed tickets to see what's actually in the data"""
    access_token, config = get_access_token()
    if not access_token:
        return
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    print("Debugging closed tickets...")
    
    url = f"{config.BASE_URLS['desk']}/tickets"
    params = {
        'orgId': config.ORG_ID,
        'from': 0,
        'limit': 10,
        'status': 'Closed'
    }
    
    try:
        response = requests.get(url, headers=headers, params=params)
        
        if response.status_code == 200:
            tickets_data = response.json()
            tickets = tickets_data.get('data', [])
            
            print(f"Found {len(tickets)} closed tickets")
            print("\nAnalyzing first 5 tickets:")
            
            for i, ticket in enumerate(tickets[:5], 1):
                print(f"\n--- Ticket {i} ---")
                print(f"ID: {ticket.get('id')}")
                print(f"Number: #{ticket.get('ticketNumber')}")
                print(f"Status: {ticket.get('status')}")
                print(f"Created: {ticket.get('createdTime', 'N/A')}")
                print(f"Closed: {ticket.get('closedTime', 'N/A')}")
                
                # Check custom fields
                cf = ticket.get('cf', {})
                print(f"Custom Fields (cf): {cf}")
                
                # Check closed_by specifically
                closed_by = cf.get('cf_closed_by', 'NOT_FOUND')
                print(f"cf_closed_by: '{closed_by}'")
                
                # Check all custom field keys
                print(f"All CF keys: {list(cf.keys())}")
                
                # Check if there are other closed_by fields
                for key, value in cf.items():
                    if 'close' in key.lower() or 'closed' in key.lower():
                        print(f"  Found close-related field: {key} = {value}")
        
        else:
            print(f"Error: {response.status_code}")
            print(f"Response: {response.text}")
            
    except Exception as e:
        print(f"Exception: {e}")

def main():
    print("="*60)
    print("  DEBUG MANUAL CLOSED TICKETS")
    print("  تشخيص التذاكر المغلقة يدوياً")
    print("="*60)
    print()
    
    debug_closed_tickets()

if __name__ == "__main__":
    main()
