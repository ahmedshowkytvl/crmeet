#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick Start Script for 500 Tickets Collection
سكريبت سريع لجمع 500 تذكرة
"""

import subprocess
import sys
import time
from datetime import datetime

def main():
    print("="*80)
    print("  500 TICKETS COLLECTION - QUICK START")
    print("="*80)
    print()
    print("This script will run the complete 500 tickets collection process.")
    print("Estimated time: 15-25 minutes")
    print("The process includes:")
    print("  - Getting access token with retry logic")
    print("  - Collecting 500 tickets in batches")
    print("  - Getting full details and threads for each ticket")
    print("  - Saving data in organized chunks")
    print("  - Generating comprehensive summary")
    print()
    
    # Confirm before starting
    response = input("Do you want to start the 500 tickets collection? (y/n): ")
    if response.lower() != 'y':
        print("Collection cancelled.")
        return
    
    print("\n" + "="*50)
    print("STARTING 500 TICKETS COLLECTION")
    print("="*50)
    print(f"Start time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print()
    
    try:
        # Run the main collection script
        result = subprocess.run([
            sys.executable, 
            "get_500_tickets_complete.py"
        ], capture_output=False, text=True)
        
        if result.returncode == 0:
            print("\n" + "="*50)
            print("COLLECTION COMPLETED SUCCESSFULLY!")
            print("="*50)
            print(f"End time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
            print()
            print("Check the output files for your collected data:")
            print("  - tickets_500_chunk_*.json (detailed data)")
            print("  - tickets_500_summary_*.json (summary report)")
        else:
            print(f"\nCollection failed with exit code: {result.returncode}")
            print("Check the output above for error details.")
            
    except KeyboardInterrupt:
        print("\n\nCollection interrupted by user.")
        print("Progress has been saved. You can resume later.")
    except Exception as e:
        print(f"\nUnexpected error: {e}")
        print("Please check the error details and try again.")

if __name__ == "__main__":
    main()

