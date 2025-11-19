#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test Dark Ticket Viewer
"""

import requests
import json

def test_dark_viewer():
    """Test the Dark Ticket Viewer"""
    
    base_url = "http://localhost:5001"
    
    print("="*70)
    print("  TESTING DARK TICKET VIEWER")
    print("="*70)
    
    # Test 1: Get statistics
    print("\n[Test 1] Getting ticket statistics...")
    try:
        stats_response = requests.get(f"{base_url}/api/tickets/stats")
        
        if stats_response.status_code == 200:
            stats = stats_response.json()
            if stats.get('success'):
                s = stats['stats']
                print(f"OK - Total tickets: {s['total_tickets']}")
                print(f"OK - Auto Close tickets: {s['auto_close_count']}")
                print(f"OK - Available tickets (excluding Auto Close): {s['excluding_auto_close']}")
                print(f"OK - Open tickets: {s['open_count']}")
                print(f"OK - Manual Close tickets: {s['manual_close_count']}")
                print(f"Note: {s['dark_viewer_note']}")
        else:
            print(f"ERROR: {stats_response.status_code}")
    except Exception as e:
        print(f"Connection error: {e}")
        return
    
    # Test 2: Get tickets (should exclude Auto Close)
    print("\n" + "="*70)
    print("[Test 2] Getting tickets (Auto Close should be excluded)...")
    try:
        tickets_response = requests.get(f"{base_url}/api/tickets")
        
        if tickets_response.status_code == 200:
            data = tickets_response.json()
            if data.get('success'):
                print(f"OK - Tickets returned: {data['count']}")
                print(f"OK - Exclude Auto Close: {data.get('exclude_auto_close', 'N/A')}")
                print(f"OK - Total before filter: {data.get('total_before_filter', 'N/A')}")
                print(f"OK - Auto Close excluded: {data.get('auto_close_excluded', 'N/A')}")
                
                print("\nFirst 5 tickets:")
                for i, ticket in enumerate(data['tickets'][:5], 1):
                    cf_closed_by = ticket.get('cf_closed_by', 'N/A')
                    print(f"{i}. #{ticket['ticketNumber']}: {ticket['subject'][:50]}")
                    print(f"   Status: {ticket['status']}")
                    print(f"   CF Closed By: {cf_closed_by}")
                    if cf_closed_by == 'Auto Close':
                        print(f"   WARNING: This should NOT appear!")
                    else:
                        print(f"   OK: Not Auto Close")
                    print()
            else:
                print(f"API Error: {data.get('error', 'Unknown error')}")
        else:
            print(f"HTTP Error: {tickets_response.status_code}")
    except Exception as e:
        print(f"Error: {e}")
    
    # Test 3: Try to access a ticket details
    print("\n" + "="*70)
    print("[Test 3] Testing ticket details access...")
    try:
        # First get a ticket ID
        tickets_response = requests.get(f"{base_url}/api/tickets")
        if tickets_response.status_code == 200:
            data = tickets_response.json()
            if data.get('success') and data['tickets']:
                ticket_id = data['tickets'][0]['id']
                print(f"Testing with ticket ID: {ticket_id}")
                
                detail_response = requests.get(f"{base_url}/api/ticket/{ticket_id}")
                if detail_response.status_code == 200:
                    detail_data = detail_response.json()
                    if detail_data.get('success'):
                        print(f"OK - Successfully loaded ticket details")
                        ticket = detail_data['ticket']
                        print(f"   Subject: {ticket['subject']}")
                        print(f"   Status: {ticket['status']}")
                        print(f"   CF Closed By: {ticket.get('cf_closed_by', 'N/A')}")
                    else:
                        print(f"Detail API Error: {detail_data.get('error')}")
                elif detail_response.status_code == 403:
                    print(f"OK - Correctly blocked Auto Close ticket (403 Forbidden)")
                else:
                    print(f"HTTP Error: {detail_response.status_code}")
    except Exception as e:
        print(f"Error: {e}")
    
    print("\n" + "="*70)
    print("  DARK VIEWER TEST COMPLETE")
    print("="*70)
    print()
    print("Open your browser and go to: http://localhost:5001")
    print("You should see a beautiful dark theme interface")
    print("Auto Close tickets should NEVER appear")

if __name__ == "__main__":
    print("\nMake sure the Dark Ticket Viewer is running on http://localhost:5001")
    print("Start it with: python ticket_web_viewer_dark.py\n")
    
    try:
        test_dark_viewer()
    except requests.exceptions.ConnectionError:
        print("\nERROR: Could not connect to http://localhost:5001")
        print("Please make sure the Dark Ticket Viewer is running first.")
        print("Run: python ticket_web_viewer_dark.py")
    except Exception as e:
        print(f"\nERROR: {e}")
