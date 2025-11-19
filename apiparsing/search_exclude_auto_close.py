#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Search tickets EXCLUDING Auto Close tickets
البحث عن جميع التذاكر ماعدا التذاكر المغلقة تلقائياً
"""

import requests
from datetime import datetime
from config import ZohoConfig

def search_tickets_exclude_auto_close(limit=100):
    """
    البحث عن جميع التذاكر ماعدا التذاكر التي تم إغلاقها بـ Auto Close
    
    Args:
        limit (int): عدد التذاكر المطلوب جلبها
    """
    
    config = ZohoConfig()
    
    print("=== Search All Tickets EXCEPT Auto Close ===\n")
    
    # Get Access Token
    print("1. Getting Access Token...")
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    token_response = requests.post(config.TOKEN_URL, data=token_data)
    
    if token_response.status_code != 200:
        print(f"Token error: {token_response.text}")
        return
    
    access_token = token_response.json()['access_token']
    print("Access Token obtained successfully\n")
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    # Method 1: Using search endpoint with NOT filter
    print("2. Searching tickets (excluding Auto Close)...")
    
    search_url = f"{config.BASE_URLS['desk']}/tickets/search"
    
    # Build search parameters with negation filter
    # Syntax: !(cf_closed_by:"Auto Close")
    params = f"from=0&limit={limit}&sortBy=-modifiedTime&searchStr=!(cf_closed_by:\"Auto Close\")"
    
    full_url = f"{search_url}?{params}"
    print(f"Search URL: {full_url}\n")
    
    response = requests.get(full_url, headers=headers)
    
    if response.status_code == 200:
        search_data = response.json()
        tickets = search_data.get('data', [])
        
        print(f"Found {len(tickets)} tickets (excluding Auto Close)")
        print("\n=== Tickets Found ===")
        
        auto_close_count = 0
        manual_close_count = 0
        open_count = 0
        
        for i, ticket in enumerate(tickets, 1):
            ticket_number = ticket.get('ticketNumber')
            subject = ticket.get('subject', 'N/A')[:50]
            status = ticket.get('status')
            cf_closed_by = ticket.get('cf', {}).get('cf_closed_by', 'N/A')
            
            print(f"{i}. #{ticket_number}: {subject}")
            print(f"   Status: {status}")
            print(f"   CF Closed By: {cf_closed_by}")
            
            if cf_closed_by == 'Auto Close':
                auto_close_count += 1
                print(f"   [WARNING] This should NOT appear!")
            elif status == 'Closed':
                manual_close_count += 1
            elif status == 'Open':
                open_count += 1
                
            print("   " + "-" * 50)
        
        print("\n=== Summary ===")
        print(f"Total tickets found: {len(tickets)}")
        print(f"Auto Close tickets (should be 0): {auto_close_count}")
        print(f"Manual Close tickets: {manual_close_count}")
        print(f"Open tickets: {open_count}")
        
    else:
        print(f"Search error: {response.status_code}")
        print(f"Response: {response.text}")
        print("\nTrying alternative method...")
        search_exclude_auto_close_alternative(access_token, config, limit)

def search_exclude_auto_close_alternative(access_token, config, limit):
    """
    Alternative method: Get all tickets and filter out Auto Close in code
    """
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    print("\n=== Alternative Method: Client-side filtering ===")
    
    # Get all tickets
    tickets_url = f"{config.BASE_URLS['desk']}/tickets"
    params = {
        'orgId': config.ORG_ID,
        'limit': limit
    }
    
    response = requests.get(tickets_url, headers=headers, params=params)
    
    if response.status_code == 200:
        tickets_data = response.json()
        all_tickets = tickets_data.get('data', [])
        
        # Filter out Auto Close tickets
        filtered_tickets = [
            ticket for ticket in all_tickets
            if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
        ]
        
        print(f"Total tickets: {len(all_tickets)}")
        print(f"After filtering (excluding Auto Close): {len(filtered_tickets)}")
        print(f"Auto Close tickets filtered out: {len(all_tickets) - len(filtered_tickets)}")
        
        print("\n=== Filtered Tickets (First 10) ===")
        for i, ticket in enumerate(filtered_tickets[:10], 1):
            print(f"{i}. #{ticket.get('ticketNumber')}: {ticket.get('subject', 'N/A')[:50]}")
            print(f"   Status: {ticket.get('status')}")
            print(f"   CF Closed By: {ticket.get('cf', {}).get('cf_closed_by', 'N/A')}")
            print("   " + "-" * 40)
            
        return filtered_tickets
    else:
        print(f"Error: {response.text}")
        return []

def search_with_multiple_filters(status=None, department_id=None, exclude_auto_close=True):
    """
    Advanced search with multiple filters
    البحث المتقدم مع فلاتر متعددة
    
    Args:
        status: حالة التذكرة (Open, Closed, etc.)
        department_id: معرف القسم
        exclude_auto_close: استبعاد Auto Close
    """
    
    config = ZohoConfig()
    
    print(f"\n=== Advanced Search with Multiple Filters ===")
    print(f"Status: {status or 'All'}")
    print(f"Department: {department_id or 'All'}")
    print(f"Exclude Auto Close: {exclude_auto_close}\n")
    
    # Get Access Token
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    token_response = requests.post(config.TOKEN_URL, data=token_data)
    access_token = token_response.json()['access_token']
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    # Build search URL
    tickets_url = f"{config.BASE_URLS['desk']}/tickets"
    params = {
        'orgId': config.ORG_ID,
        'limit': 100
    }
    
    # Add optional filters
    if status:
        params['status'] = status
    if department_id:
        params['departmentIds'] = department_id
    
    response = requests.get(tickets_url, headers=headers, params=params)
    
    if response.status_code == 200:
        tickets_data = response.json()
        all_tickets = tickets_data.get('data', [])
        
        # Apply Auto Close filter if needed
        if exclude_auto_close:
            filtered_tickets = [
                ticket for ticket in all_tickets
                if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
            ]
        else:
            filtered_tickets = all_tickets
        
        print(f"Results:")
        print(f"  Total tickets: {len(all_tickets)}")
        print(f"  After filtering: {len(filtered_tickets)}")
        
        if exclude_auto_close:
            print(f"  Auto Close excluded: {len(all_tickets) - len(filtered_tickets)}")
        
        return filtered_tickets
    else:
        print(f"Error: {response.text}")
        return []

def main():
    """
    Main function to run examples
    """
    
    print("="*70)
    print("  ZOHO DESK - SEARCH EXCLUDING AUTO CLOSE TICKETS")
    print("="*70)
    
    # Example 1: Basic search excluding Auto Close
    print("\n[Example 1] Basic search excluding Auto Close:")
    search_tickets_exclude_auto_close(limit=20)
    
    # Example 2: Search with multiple filters
    print("\n" + "="*70)
    print("\n[Example 2] Search Closed tickets (excluding Auto Close):")
    search_with_multiple_filters(status='Closed', exclude_auto_close=True)
    
    # Example 3: Search specific department excluding Auto Close
    print("\n" + "="*70)
    print("\n[Example 3] Search Contracting-KSA department (excluding Auto Close):")
    search_with_multiple_filters(
        department_id='766285000016070029',  # Contracting - KSA
        exclude_auto_close=True
    )

if __name__ == "__main__":
    main()

