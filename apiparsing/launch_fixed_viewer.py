#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch the fixed ticket viewer with better error handling
"""

import webbrowser
import threading
import time
from ticket_web_viewer import app

def open_browser():
    """Open browser after a short delay"""
    time.sleep(2)
    webbrowser.open('http://localhost:5000')

if __name__ == '__main__':
    print("Starting Ticket Viewer...")
    print("Make sure your Zoho API tokens are properly configured")
    print("Opening browser in 2 seconds...")
    print("If you see errors, try the demo version: python launch_demo.py")
    print("="*60)
    
    # Test API connection first
    try:
        print("Testing Zoho API connection...")
        from zoho_api import ZohoAPI
        zoho = ZohoAPI()
        token = zoho.get_access_token()
        if token:
            print("✅ API connection successful!")
        else:
            print("❌ API connection failed!")
    except Exception as e:
        print(f"❌ API test error: {e}")
    
    print("="*60)
    
    # Open browser in a separate thread
    browser_thread = threading.Thread(target=open_browser)
    browser_thread.daemon = True
    browser_thread.start()
    
    # Start Flask app with better error handling
    try:
        print("Starting Flask server...")
        app.run(debug=True, host='0.0.0.0', port=5000)
    except Exception as e:
        print(f"❌ Flask server error: {e}")
        print("Try running the demo version: python launch_demo.py")
