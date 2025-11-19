#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple Manual Closed Tickets Collector
جلب التذاكر المغلقة يدوياً - نسخة مبسطة
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

def get_manual_closed_tickets():
    """Get manually closed tickets"""
    access_token, config = get_access_token()
    if not access_token:
        return []
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    print("Getting closed tickets...")
    
    all_tickets = []
    from_index = 0
    batch_size = 100
    
    while len(all_tickets) < 500:  # Limit to 500 tickets
        url = f"{config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': config.ORG_ID,
            'from': from_index,
            'limit': batch_size,
            'status': 'Closed'
        }
        
        try:
            print(f"Fetching batch: from={from_index}, limit={batch_size}")
            response = requests.get(url, headers=headers, params=params)
            
            if response.status_code == 200:
                tickets_data = response.json()
                batch_tickets = tickets_data.get('data', [])
                
                if not batch_tickets:
                    print("No more tickets available")
                    break
                
                # Filter for manually closed tickets (non Auto Close)
                filtered_tickets = []
                for ticket in batch_tickets:
                    closed_by = ticket.get('cf', {}).get('cf_closed_by', '')
                    if closed_by and closed_by != 'Auto Close':
                        filtered_tickets.append(ticket)
                        print(f"  Found: #{ticket.get('ticketNumber')} - Closed by: {closed_by}")
                
                all_tickets.extend(filtered_tickets)
                print(f"Batch result: {len(batch_tickets)} total, {len(filtered_tickets)} manually closed")
                print(f"Total collected: {len(all_tickets)}")
                
                if len(batch_tickets) < batch_size:
                    break
                
                from_index += batch_size
                time.sleep(1)  # Rate limiting
                
            else:
                print(f"Error: {response.status_code}")
                print(f"Response: {response.text}")
                break
                
        except Exception as e:
            print(f"Exception: {e}")
            break
    
    return all_tickets

def main():
    print("="*60)
    print("  SIMPLE MANUAL CLOSED TICKETS COLLECTOR")
    print("  جلب التذاكر المغلقة يدوياً - نسخة مبسطة")
    print("="*60)
    print()
    
    tickets = get_manual_closed_tickets()
    
    if tickets:
        print(f"\nFound {len(tickets)} manually closed tickets:")
        for i, ticket in enumerate(tickets[:10], 1):  # Show first 10
            closed_by = ticket.get('cf', {}).get('cf_closed_by', 'Unknown')
            closed_time = ticket.get('closedTime', 'Unknown')
            print(f"  {i}. #{ticket.get('ticketNumber')} - Closed by: {closed_by} - Time: {closed_time[:10]}")
        
        if len(tickets) > 10:
            print(f"  ... and {len(tickets) - 10} more")
        
        # Save to file
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"manual_closed_tickets_simple_{timestamp}.json"
        
        try:
            with open(filename, 'w', encoding='utf-8') as f:
                json.dump(tickets, f, indent=2, ensure_ascii=False)
            print(f"\nData saved to {filename}")
        except Exception as e:
            print(f"Error saving file: {e}")
    else:
        print("No manually closed tickets found")

if __name__ == "__main__":
    main()
