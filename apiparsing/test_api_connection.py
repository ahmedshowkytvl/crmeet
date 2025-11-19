#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test Zoho API connection and access token
"""

from zoho_api import ZohoAPI
import time

def test_api_connection():
    """Test API connection with retry logic"""
    print("Testing Zoho API Connection...")
    print("="*50)
    
    zoho = ZohoAPI()
    
    # Test access token with retry
    print("Testing Access Token...")
    for attempt in range(3):
        print(f"   Attempt {attempt + 1}/3...")
        token = zoho.get_access_token()
        
        if token:
            print("   Access token obtained successfully!")
            break
        else:
            print(f"   Attempt {attempt + 1} failed")
            if attempt < 2:
                print("   Waiting 2 seconds before retry...")
                time.sleep(2)
    
    if not token:
        print("Failed to get access token after 3 attempts")
        print("Possible solutions:")
        print("   1. Check your Zoho API credentials in config.py")
        print("   2. Verify your refresh token is valid")
        print("   3. Check your internet connection")
        print("   4. Ensure Zoho API is accessible")
        return False
    
    # Test API call
    print("\nTesting API Call...")
    try:
        tickets_response = zoho.get_tickets(limit=1)
        if tickets_response and 'data' in tickets_response:
            print("   API call successful!")
            print(f"   Found {len(tickets_response['data'])} ticket(s)")
            return True
        else:
            print("   API call failed - no data returned")
            return False
    except Exception as e:
        print(f"   API call failed: {e}")
        return False

def test_specific_endpoints():
    """Test specific API endpoints"""
    print("\nTesting Specific Endpoints...")
    print("="*50)
    
    zoho = ZohoAPI()
    token = zoho.get_access_token()
    
    if not token:
        print("Cannot test endpoints without access token")
        return
    
    # Test tickets endpoint
    print("Testing /tickets endpoint...")
    try:
        response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets", 
                                   params={'orgId': zoho.config.ORG_ID, 'limit': 1})
        if response:
            print("   /tickets endpoint working")
        else:
            print("   /tickets endpoint failed")
    except Exception as e:
        print(f"   /tickets endpoint error: {e}")
    
    # Test specific ticket endpoint
    print("Testing /tickets/{id} endpoint...")
    try:
        # First get a ticket ID
        tickets = zoho.get_tickets(limit=1)
        if tickets and tickets.get('data'):
            ticket_id = tickets['data'][0].get('id')
            if ticket_id:
                response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                                           params={'orgId': zoho.config.ORG_ID})
                if response:
                    print(f"   /tickets/{ticket_id} endpoint working")
                else:
                    print(f"   /tickets/{ticket_id} endpoint failed")
            else:
                print("   No ticket ID found to test")
        else:
            print("   No tickets found to test with")
    except Exception as e:
        print(f"   /tickets/{id} endpoint error: {e}")

if __name__ == "__main__":
    success = test_api_connection()
    if success:
        test_specific_endpoints()
        print("\nAll tests completed!")
    else:
        print("\nConnection test failed. Please fix the issues above.")
