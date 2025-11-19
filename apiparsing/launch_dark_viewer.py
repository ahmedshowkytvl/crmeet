#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch Dark Ticket Viewer
"""

import os
import sys
import subprocess
import time

def main():
    print("="*70)
    print("  LAUNCHING DARK TICKET VIEWER")
    print("="*70)
    print()
    print("Features:")
    print("  - Black/Dark Theme")
    print("  - NEVER shows Auto Close tickets")
    print("  - Professional interface")
    print("  - Real-time statistics")
    print()
    print("Starting server on http://localhost:5001")
    print("Press Ctrl+C to stop")
    print("="*70)
    print()
    
    try:
        # Start the dark viewer
        subprocess.run([sys.executable, "ticket_web_viewer_dark.py"])
    except KeyboardInterrupt:
        print("\nShutting down Dark Ticket Viewer...")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()

