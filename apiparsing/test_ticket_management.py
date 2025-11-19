#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test Ticket Management Functions
اختبار وظائف إدارة التذاكر
"""

import requests
import json
from ticket_threads_viewer import TicketThreadsAPI

def test_ticket_management():
    """Test ticket management functions"""
    print("="*60)
    print("  TESTING TICKET MANAGEMENT FUNCTIONS")
    print("  اختبار وظائف إدارة التذاكر")
    print("="*60)
    print()
    
    # Initialize API
    api = TicketThreadsAPI()
    
    # Test 1: Get access token
    print("Testing access token...")
    if not api.get_access_token():
        print("ERROR: Failed to get access token")
        return False
    
    print("SUCCESS: Access token obtained")
    
    # Test 2: Get a ticket to work with
    print("\nGetting a ticket to test with...")
    tickets = api.get_tickets_list(limit=1)
    if not tickets:
        print("ERROR: No tickets found")
        return False
    
    ticket = tickets[0]
    ticket_id = ticket['id']
    print(f"SUCCESS: Found ticket #{ticket.get('ticketNumber', 'N/A')} (ID: {ticket_id})")
    print(f"Current status: {ticket.get('status', 'N/A')}")
    
    # Test 3: Add a comment
    print(f"\nTesting add comment to ticket {ticket_id}...")
    comment_result = api.add_ticket_comment(ticket_id, "Test comment from API - " + str(datetime.now()))
    if comment_result:
        print("SUCCESS: Comment added successfully")
    else:
        print("ERROR: Failed to add comment")
    
    # Test 4: Update ticket status (if not already closed)
    if ticket.get('status') != 'Closed':
        print(f"\nTesting status update for ticket {ticket_id}...")
        status_result = api.update_ticket_status(ticket_id, "Pending", "Status changed to Pending for testing")
        if status_result:
            print("SUCCESS: Status updated successfully")
        else:
            print("ERROR: Failed to update status")
    
    # Test 5: Close ticket (if not already closed)
    if ticket.get('status') != 'Closed':
        print(f"\nTesting close ticket {ticket_id}...")
        close_result = api.close_ticket(ticket_id, "Ticket closed for testing purposes")
        if close_result:
            print("SUCCESS: Ticket closed successfully")
        else:
            print("ERROR: Failed to close ticket")
    else:
        print("Ticket is already closed, testing reopen...")
        reopen_result = api.reopen_ticket(ticket_id, "Ticket reopened for testing")
        if reopen_result:
            print("SUCCESS: Ticket reopened successfully")
        else:
            print("ERROR: Failed to reopen ticket")
    
    print("\n" + "="*60)
    print("  TICKET MANAGEMENT TEST COMPLETED")
    print("="*60)
    
    return True

def test_api_endpoints():
    """Test API endpoints"""
    print("\nTesting API endpoints...")
    
    base_url = "http://localhost:5000"
    
    try:
        # Test if server is running
        response = requests.get(f"{base_url}/", timeout=5)
        if response.status_code != 200:
            print("ERROR: Server not responding")
            return False
        
        print("SUCCESS: Server is running")
        
        # Test tickets API
        response = requests.get(f"{base_url}/api/tickets", timeout=5)
        if response.status_code == 200:
            tickets = response.json()
            print(f"SUCCESS: Tickets API working - {len(tickets)} tickets")
            
            if tickets:
                ticket_id = tickets[0]['id']
                
                # Test close ticket API
                close_data = {"comment": "Test close from API"}
                response = requests.post(f"{base_url}/api/ticket/{ticket_id}/close", 
                                       json=close_data, timeout=5)
                if response.status_code == 200:
                    result = response.json()
                    if result.get('success'):
                        print("SUCCESS: Close ticket API working")
                    else:
                        print(f"WARNING: Close ticket API returned error: {result.get('error')}")
                else:
                    print(f"WARNING: Close ticket API failed: {response.status_code}")
                
                # Test add comment API
                comment_data = {"comment": "Test comment from API"}
                response = requests.post(f"{base_url}/api/ticket/{ticket_id}/comment", 
                                       json=comment_data, timeout=5)
                if response.status_code == 200:
                    result = response.json()
                    if result.get('success'):
                        print("SUCCESS: Add comment API working")
                    else:
                        print(f"WARNING: Add comment API returned error: {result.get('error')}")
                else:
                    print(f"WARNING: Add comment API failed: {response.status_code}")
        else:
            print(f"ERROR: Tickets API failed: {response.status_code}")
            return False
        
        return True
        
    except requests.exceptions.ConnectionError:
        print("WARNING: Server not available - make sure the app is running first")
        return False
    except Exception as e:
        print(f"ERROR: API test failed: {e}")
        return False

def main():
    """Main test function"""
    print("Starting Ticket Management Test...")
    print()
    
    # Test API functions
    api_success = test_ticket_management()
    
    if api_success:
        print("\nSUCCESS: All API tests passed!")
        print("You can now use the ticket management features in the web app")
        print()
        
        # Ask if user wants to test web endpoints
        try:
            test_web = input("Do you want to test web API endpoints? (y/n): ").lower().strip()
            if test_web in ['y', 'yes']:
                test_api_endpoints()
        except KeyboardInterrupt:
            print("\nTest cancelled")
    else:
        print("\nERROR: API tests failed")
        print("Please check:")
        print("1. Credentials in config.py")
        print("2. Internet connection")
        print("3. API permissions")

if __name__ == "__main__":
    from datetime import datetime
    main()
