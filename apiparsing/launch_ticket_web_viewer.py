#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch Ticket Web Viewer
تشغيل عارض التذاكر على الويب
"""

import subprocess
import sys
import os

def check_requirements():
    """Check if required packages are installed"""
    try:
        import flask
        import requests
        print("SUCCESS: All requirements are available")
        return True
    except ImportError as e:
        print(f"ERROR: Missing requirement: {e}")
        print("Please install requirements using:")
        print("pip install flask requests")
        return False

def main():
    print("="*60)
    print("  TICKET WEB VIEWER - LAUNCHER")
    print("  مشغل عارض التذاكر على الويب")
    print("="*60)
    print()
    
    # Check requirements
    if not check_requirements():
        return
    
    # Check if main file exists
    if not os.path.exists('ticket_web_viewer.py'):
        print("ERROR: ticket_web_viewer.py file not found")
        return
    
    print("Starting Ticket Web Viewer...")
    print("Features:")
    print("  - Filter tickets by cf_closed_by")
    print("  - Filter tickets by status")
    print("  - Adjustable ticket limit")
    print("  - Real-time data from Zoho Desk")
    print()
    print("API Endpoints:")
    print("  GET /api/tickets - Get tickets with filtering")
    print("  GET /api/ticket/<id> - Get ticket details")
    print("  GET /api/filters/cf_closed_by - Get cf_closed_by options")
    print()
    print("Example usage:")
    print('  curl "http://localhost:5000/api/tickets?status=Closed&limit=100"')
    print('  curl "http://localhost:5000/api/tickets?cf_closed_by=Auto Close"')
    print('  curl "http://localhost:5000/api/filters/cf_closed_by"')
    print()
    print("Server will start on: http://localhost:5000")
    print("Press Ctrl+C to stop the server")
    print()
    
    try:
        # Run the web viewer
        subprocess.run([sys.executable, 'ticket_web_viewer.py'])
    except KeyboardInterrupt:
        print("\nServer stopped by user")
    except Exception as e:
        print(f"ERROR running server: {e}")

if __name__ == "__main__":
    main()
