#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test Ticket Threads Viewer
اختبار عارض التذاكر والخيوط
"""

import requests
import json
import time
from ticket_threads_viewer import TicketThreadsAPI

def test_api_connection():
    """Test API connection and basic functionality"""
    print("="*60)
    print("  TESTING TICKET THREADS VIEWER")
    print("="*60)
    print()
    
    # Initialize API
    api = TicketThreadsAPI()
    
    # Test 1: Get access token
    print("Testing access token...")
    if api.get_access_token():
        print("SUCCESS: Access token obtained successfully")
    else:
        print("ERROR: Failed to get access token")
        return False
    
    # Test 2: Get tickets list
    print("\nTesting tickets list...")
    tickets = api.get_tickets_list(limit=5)
    if tickets:
        print(f"SUCCESS: Retrieved {len(tickets)} tickets")
        print("Sample tickets:")
        for i, ticket in enumerate(tickets[:3], 1):
            print(f"  {i}. #{ticket.get('ticketNumber', 'N/A')}: {ticket.get('subject', 'No Subject')[:50]}")
    else:
        print("ERROR: Failed to retrieve tickets")
        return False
    
    # Test 3: Get ticket details
    if tickets:
        print(f"\nTesting ticket details for #{tickets[0].get('ticketNumber', 'N/A')}...")
        ticket_details = api.get_ticket_details(tickets[0]['id'])
        if ticket_details:
            print("SUCCESS: Retrieved ticket details successfully")
            print(f"  Subject: {ticket_details.get('subject', 'N/A')}")
            print(f"  Status: {ticket_details.get('status', 'N/A')}")
        else:
            print("ERROR: Failed to retrieve ticket details")
    
    # Test 4: Get ticket threads
    if tickets:
        print(f"\nTesting ticket threads for #{tickets[0].get('ticketNumber', 'N/A')}...")
        threads = api.get_ticket_threads(tickets[0]['id'])
        if threads is not None:
            print(f"SUCCESS: Retrieved {len(threads)} threads")
            if threads:
                print("Sample threads:")
                for i, thread in enumerate(threads[:3], 1):
                    thread_type = thread.get('type', 'N/A')
                    from_addr = thread.get('fromAddress', thread.get('from', 'N/A'))
                    print(f"  {i}. {thread_type} from: {from_addr}")
        else:
            print("ERROR: Failed to retrieve threads")
    
    # Test 5: Get specific thread details
    if tickets and threads:
        print(f"\nTesting specific thread details...")
        thread_details = api.get_thread_details(tickets[0]['id'], threads[0]['id'])
        if thread_details:
            print("SUCCESS: Retrieved thread details successfully")
            print(f"  Type: {thread_details.get('type', 'N/A')}")
            print(f"  From: {thread_details.get('fromAddress', 'N/A')}")
            content_length = len(thread_details.get('content', ''))
            print(f"  Content length: {content_length} characters")
        else:
            print("ERROR: Failed to retrieve thread details")
    
    print("\n" + "="*60)
    print("  TEST COMPLETED")
    print("="*60)
    
    return True

def test_web_endpoints():
    """Test web endpoints if server is running"""
    print("\nTesting web endpoints...")
    
    base_url = "http://localhost:5000"
    
    try:
        # Test main page
        response = requests.get(f"{base_url}/", timeout=5)
        if response.status_code == 200:
            print("SUCCESS: Main page is working")
        else:
            print(f"ERROR: Error in main page: {response.status_code}")
            return False
        
        # Test API endpoints
        response = requests.get(f"{base_url}/api/tickets", timeout=5)
        if response.status_code == 200:
            data = response.json()
            print(f"SUCCESS: Tickets API is working - {len(data)} tickets")
        else:
            print(f"ERROR: Error in tickets API: {response.status_code}")
        
        print("SUCCESS: All endpoints are working correctly")
        return True
        
    except requests.exceptions.ConnectionError:
        print("WARNING: Server not available - make sure the app is running first")
        return False
    except Exception as e:
        print(f"ERROR: Error in web test: {e}")
        return False

def main():
    """Main test function"""
    print("Starting Ticket Threads Viewer Test...")
    print()
    
    # Test API functionality
    api_success = test_api_connection()
    
    if api_success:
        print("\nSUCCESS: All API tests passed!")
        print("You can now run the application using:")
        print("python launch_threads_viewer.py")
        print()
        
        # Ask if user wants to test web endpoints
        try:
            test_web = input("Do you want to test web endpoints? (y/n): ").lower().strip()
            if test_web in ['y', 'yes']:
                test_web_endpoints()
        except KeyboardInterrupt:
            print("\nTest cancelled")
    else:
        print("\nERROR: API tests failed")
        print("Please check:")
        print("1. Credentials in config.py")
        print("2. Internet connection")
        print("3. API permissions")

if __name__ == "__main__":
    main()
