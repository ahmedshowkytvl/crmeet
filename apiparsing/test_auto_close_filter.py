#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test Auto Close filter in ticket viewer
"""

import requests
import json

def test_filter():
    """Test the Auto Close filter"""
    
    base_url = "http://localhost:5000"
    
    print("="*70)
    print("  TESTING AUTO CLOSE FILTER")
    print("="*70)
    
    # Test 1: Get statistics
    print("\n[Test 1] Getting ticket statistics...")
    stats_response = requests.get(f"{base_url}/api/tickets/stats")
    
    if stats_response.status_code == 200:
        stats = stats_response.json()
        if stats.get('success'):
            s = stats['stats']
            print(f"Total tickets: {s['total_tickets']}")
            print(f"Auto Close tickets: {s['auto_close_count']}")
            print(f"Manual Close tickets: {s['manual_close_count']}")
            print(f"Open tickets: {s['open_count']}")
            print(f"Excluding Auto Close: {s['excluding_auto_close']}")
    else:
        print(f"Error: {stats_response.status_code}")
    
    # Test 2: Get tickets WITH Auto Close filter (default)
    print("\n" + "="*70)
    print("[Test 2] Getting tickets (excluding Auto Close - default)...")
    tickets_response = requests.get(f"{base_url}/api/tickets")
    
    if tickets_response.status_code == 200:
        data = tickets_response.json()
        if data.get('success'):
            print(f"Tickets returned: {data['count']}")
            print(f"Exclude Auto Close: {data.get('exclude_auto_close', 'N/A')}")
            print(f"Total before filter: {data.get('total_before_filter', 'N/A')}")
            
            print("\nFirst 5 tickets:")
            for i, ticket in enumerate(data['tickets'][:5], 1):
                print(f"{i}. #{ticket['ticketNumber']}: {ticket['subject'][:50]}")
                print(f"   Status: {ticket['status']}")
    else:
        print(f"Error: {tickets_response.status_code}")
    
    # Test 3: Get tickets WITHOUT Auto Close filter
    print("\n" + "="*70)
    print("[Test 3] Getting tickets (including Auto Close)...")
    tickets_response = requests.get(f"{base_url}/api/tickets?exclude_auto_close=false")
    
    if tickets_response.status_code == 200:
        data = tickets_response.json()
        if data.get('success'):
            print(f"Tickets returned: {data['count']}")
            print(f"Exclude Auto Close: {data.get('exclude_auto_close', 'N/A')}")
            
            print("\nFirst 5 tickets:")
            for i, ticket in enumerate(data['tickets'][:5], 1):
                print(f"{i}. #{ticket['ticketNumber']}: {ticket['subject'][:50]}")
                print(f"   Status: {ticket['status']}")
    else:
        print(f"Error: {tickets_response.status_code}")
    
    print("\n" + "="*70)
    print("  TEST COMPLETE")
    print("="*70)

if __name__ == "__main__":
    print("\nMake sure the ticket viewer is running on http://localhost:5000")
    print("Start it with: python ticket_web_viewer_fixed.py\n")
    
    try:
        test_filter()
    except requests.exceptions.ConnectionError:
        print("\nError: Could not connect to http://localhost:5000")
        print("Please make sure the ticket viewer is running first.")
    except Exception as e:
        print(f"\nError: {e}")


