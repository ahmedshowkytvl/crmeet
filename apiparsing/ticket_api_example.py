#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple example of getting ticket details from Zoho Desk API
"""

import requests
import json
from datetime import datetime
from config import ZohoConfig

def get_ticket_details_example():
    """Complete example of getting ticket details"""
    
    config = ZohoConfig()
    
    # Step 1: Get access token
    print("1. Getting access token...")
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
    print(f"Access token obtained: {access_token[:20]}...")
    
    # Step 2: Get tickets list
    print("\n2. Getting tickets list...")
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    tickets_url = f"{config.BASE_URLS['desk']}/tickets"
    tickets_params = {'orgId': config.ORG_ID, 'limit': 5}
    
    tickets_response = requests.get(tickets_url, headers=headers, params=tickets_params)
    if tickets_response.status_code != 200:
        print(f"Tickets error: {tickets_response.text}")
        return
    
    tickets_data = tickets_response.json()
    print(f"Found {len(tickets_data['data'])} tickets")
    
    # Step 3: Get detailed info for first ticket
    if tickets_data['data']:
        ticket_id = tickets_data['data'][0]['id']
        ticket_number = tickets_data['data'][0]['ticketNumber']
        
        print(f"\n3. Getting detailed info for ticket #{ticket_number}...")
        
        # Get ticket details
        ticket_url = f"{config.BASE_URLS['desk']}/tickets/{ticket_id}"
        ticket_response = requests.get(ticket_url, headers=headers, params={'orgId': config.ORG_ID})
        
        if ticket_response.status_code == 200:
            ticket_details = ticket_response.json()
            
            print("Ticket Details:")
            print(f"   ID: {ticket_details.get('id')}")
            print(f"   Number: {ticket_details.get('ticketNumber')}")
            print(f"   Subject: {ticket_details.get('subject')}")
            print(f"   Status: {ticket_details.get('status')}")
            print(f"   Email: {ticket_details.get('email')}")
            print(f"   Created: {ticket_details.get('createdTime')}")
            print(f"   Department ID: {ticket_details.get('departmentId')}")
            print(f"   Assignee ID: {ticket_details.get('assigneeId')}")
            
            # Get threads
            print(f"\n4. Getting threads for ticket #{ticket_number}...")
            threads_url = f"{config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
            threads_response = requests.get(threads_url, headers=headers, params={'orgId': config.ORG_ID})
            
            if threads_response.status_code == 200:
                threads_data = threads_response.json()
                print(f"Found {len(threads_data['data'])} threads")
                
                for i, thread in enumerate(threads_data['data']):
                    print(f"   Thread {i+1}: {thread.get('direction')} - {thread.get('createdTime')}")
            else:
                print(f"Threads error: {threads_response.text}")
                
        else:
            print(f"Ticket details error: {ticket_response.text}")

def search_tickets_by_date_example():
    """Example of searching tickets by date range"""
    
    config = ZohoConfig()
    
    # Get access token
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
    
    # Search for tickets modified today
    today = datetime.now()
    from_date = today.strftime('%Y-%m-%dT00:00:00.000Z')
    to_date = today.strftime('%Y-%m-%dT23:59:59.000Z')
    
    search_url = f"{config.BASE_URLS['desk']}/tickets/search"
    params = f"from=0&limit=10&sortBy=-modifiedTime&modifiedTimeRange={from_date},{to_date}"
    
    print(f"Searching for tickets modified between {from_date} and {to_date}")
    
    response = requests.get(f"{search_url}?{params}", headers=headers)
    
    if response.status_code == 200:
        search_data = response.json()
        print(f"Found {len(search_data.get('data', []))} tickets")
        
        for ticket in search_data.get('data', []):
            print(f"   #{ticket.get('ticketNumber')}: {ticket.get('subject')} - {ticket.get('status')}")
    else:
        print(f"Search error: {response.text}")

if __name__ == "__main__":
    print("=== Zoho Desk API Ticket Details Example ===\n")
    
    print("Running ticket details example...")
    get_ticket_details_example()
    
    print("\n" + "="*50)
    print("Running search by date example...")
    search_tickets_by_date_example()
