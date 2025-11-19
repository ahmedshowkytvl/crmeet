#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch Manual Closed Tickets Collector
تشغيل جلب التذاكر المغلقة يدوياً
"""

import subprocess
import sys
import os

def check_requirements():
    """Check if required packages are installed"""
    try:
        import requests
        print("SUCCESS: All requirements are available")
        return True
    except ImportError as e:
        print(f"ERROR: Missing requirement: {e}")
        print("Please install requirements using:")
        print("pip install requests")
        return False

def main():
    print("="*60)
    print("  MANUAL CLOSED TICKETS COLLECTOR - LAUNCHER")
    print("  مشغل جلب التذاكر المغلقة يدوياً")
    print("="*60)
    print()
    
    # Check requirements
    if not check_requirements():
        return
    
    # Check if main file exists
    if not os.path.exists('get_manual_closed_tickets.py'):
        print("ERROR: get_manual_closed_tickets.py file not found")
        return
    
    print("Starting Manual Closed Tickets Collector...")
    print("This will collect all tickets closed manually (non Auto Close) from current month")
    print("Estimated time: 5-10 minutes")
    print()
    
    try:
        # Run the collector
        subprocess.run([sys.executable, 'get_manual_closed_tickets.py'])
    except KeyboardInterrupt:
        print("\nCollection stopped by user")
    except Exception as e:
        print(f"ERROR running collector: {e}")

if __name__ == "__main__":
    main()
