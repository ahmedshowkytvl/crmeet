#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch the ticket viewer web application
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
    print("🚀 Starting Ticket Viewer Web Application...")
    print("📱 Opening browser in 2 seconds...")
    
    # Open browser in a separate thread
    browser_thread = threading.Thread(target=open_browser)
    browser_thread.daemon = True
    browser_thread.start()
    
    # Start Flask app
    app.run(debug=True, host='0.0.0.0', port=5000)
