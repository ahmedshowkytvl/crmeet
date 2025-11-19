#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick launcher for the Beautiful Ticket Web Viewer
"""

import subprocess
import sys
import webbrowser
import time
import os

def check_flask():
    """Check if Flask is installed"""
    try:
        import flask
        return True
    except ImportError:
        return False

def install_flask():
    """Install Flask if not available"""
    print("📦 Installing Flask...")
    subprocess.check_call([sys.executable, "-m", "pip", "install", "flask"])
    print("✅ Flask installed successfully!")

def main():
    """Main launcher function"""
    print("🚀 Beautiful Zoho Desk Ticket Viewer")
    print("="*50)
    
    # Check if Flask is installed
    if not check_flask():
        print("⚠️  Flask not found. Installing...")
        install_flask()
    
    # Check if templates directory exists
    if not os.path.exists('templates'):
        print("❌ Templates directory not found!")
        print("Please make sure you're running this from the correct directory.")
        return
    
    print("✅ All dependencies ready!")
    print("🌐 Starting web server...")
    print("📱 Your browser will open automatically")
    print("⏹️  Press Ctrl+C to stop the server")
    print("="*50)
    
    try:
        # Import and run the web viewer
        from ticket_web_viewer import main as run_web_viewer
        run_web_viewer()
    except KeyboardInterrupt:
        print("\n👋 Web viewer stopped. Goodbye!")
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    main()
