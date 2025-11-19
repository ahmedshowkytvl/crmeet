#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch the demo ticket viewer
"""

import webbrowser
import threading
import time
from ticket_web_viewer_demo import app

def open_browser():
    """Open browser after a short delay"""
    time.sleep(2)
    webbrowser.open('http://localhost:5001')

if __name__ == '__main__':
    print("🎭 Starting Demo Ticket Viewer...")
    print("📝 This version uses mock data for testing")
    print("📱 Opening browser in 2 seconds...")
    
    # Open browser in a separate thread
    browser_thread = threading.Thread(target=open_browser)
    browser_thread.daemon = True
    browser_thread.start()
    
    # Start Flask app
    app.run(debug=True, host='0.0.0.0', port=5001)
