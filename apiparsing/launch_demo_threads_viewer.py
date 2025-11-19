#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch Demo Ticket Threads Viewer
تشغيل عارض التذاكر والخيوط التجريبي
"""

import subprocess
import sys
import os

def check_requirements():
    """Check if required packages are installed"""
    try:
        import flask
        print("SUCCESS: All requirements are available")
        return True
    except ImportError as e:
        print(f"ERROR: Missing requirement: {e}")
        print("Please install requirements using:")
        print("pip install flask")
        return False

def main():
    print("="*60)
    print("  DEMO TICKET THREADS VIEWER - LAUNCHER")
    print("  مشغل عارض التذاكر والخيوط التجريبي")
    print("="*60)
    print()
    
    # Check requirements
    if not check_requirements():
        return
    
    # Check if demo file exists
    if not os.path.exists('demo_threads_viewer.py'):
        print("ERROR: demo_threads_viewer.py file not found")
        return
    
    print("Starting Demo Ticket Threads Viewer...")
    print("This is a demo version with sample data")
    print("Browser will open automatically at: http://localhost:5000")
    print("To stop the server, press Ctrl+C")
    print()
    
    try:
        # Run the Flask app
        subprocess.run([sys.executable, 'demo_threads_viewer.py'])
    except KeyboardInterrupt:
        print("\nServer stopped")
    except Exception as e:
        print(f"ERROR running server: {e}")

if __name__ == "__main__":
    main()
