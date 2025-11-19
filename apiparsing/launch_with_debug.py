#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch ticket viewer with detailed error logging
"""

import webbrowser
import threading
import time
import sys
import traceback
from ticket_web_viewer import app

def open_browser():
    """Open browser after a short delay"""
    time.sleep(2)
    webbrowser.open('http://localhost:5000')

def test_api_connection():
    """Test API connection with detailed output"""
    print("Testing Zoho API connection...")
    print("-" * 50)
    
    try:
        from zoho_api import ZohoAPI
        from config import ZohoConfig
        
        # Show config
        config = ZohoConfig()
        print(f"Client ID: {config.CLIENT_ID}")
        print(f"Refresh Token: {config.REFRESH_TOKEN[:20]}...")
        print(f"Org ID: {config.ORG_ID}")
        print()
        
        # Test API
        zoho = ZohoAPI()
        print("Getting access token...")
        token = zoho.get_access_token()
        
        if token:
            print("Access token obtained successfully!")
            print(f"Token: {token[:20]}...")
            
            # Test tickets
            print("\nTesting tickets API...")
            tickets = zoho.get_tickets(limit=1)
            if tickets and 'data' in tickets:
                print(f"Tickets API working! Found {len(tickets['data'])} ticket(s)")
                return True
            else:
                print("Tickets API failed - no data returned")
                return False
        else:
            print("Failed to get access token")
            return False
            
    except Exception as e:
        print(f"API test error: {e}")
        traceback.print_exc()
        return False

if __name__ == '__main__':
    print("Starting Ticket Viewer with Debug Mode")
    print("=" * 60)
    
    # Test API first
    api_ok = test_api_connection()
    
    print("\n" + "=" * 60)
    
    if not api_ok:
        print("API connection failed!")
        print("You can still try the demo version: python launch_demo.py")
        print("=" * 60)
    
    # Open browser
    print("Opening browser in 2 seconds...")
    browser_thread = threading.Thread(target=open_browser)
    browser_thread.daemon = True
    browser_thread.start()
    
    # Start Flask with detailed logging
    print("Starting Flask server...")
    print("All errors will be displayed in this console")
    print("Press Ctrl+C to stop the server")
    print("=" * 60)
    
    try:
        # Enable detailed error logging
        import logging
        logging.basicConfig(level=logging.DEBUG)
        
        app.run(debug=True, host='0.0.0.0', port=5000, use_reloader=False)
    except KeyboardInterrupt:
        print("\nServer stopped by user")
    except Exception as e:
        print(f"\nFlask server error: {e}")
        traceback.print_exc()
        print("\nTry running: python launch_demo.py")
