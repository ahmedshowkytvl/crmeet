#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Zoho Bulk Ticket Sync - جلب 2000 تذكرة من Zoho
Logic: Fetch 2000 tickets using pagination (0-99, 100-199, etc.)
"""

import sys
import io
import requests
import json
import time
from datetime import datetime, timedelta
from typing import List, Dict

# Fix encoding for Windows console
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Configuration
ZOHO_CONFIG = {
    'token_url': 'https://accounts.zoho.com/oauth/v2/token',
    'base_url': 'https://desk.zoho.com/api/v1',
    'client_id': '1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT',
    'client_secret': '30624b06180b20ab5252fc8e6145ad175762a367a0',
    'refresh_token': '1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee',
    'org_id': '786481962'
}

class ZohoBulkSync:
    def __init__(self):
        self.access_token = None
        self.all_tickets = []
        self.failed_requests = []
        
    def get_access_token(self):
        """Get or refresh access token"""
        try:
            response = requests.post(ZOHO_CONFIG['token_url'], data={
                'refresh_token': ZOHO_CONFIG['refresh_token'],
                'client_id': ZOHO_CONFIG['client_id'],
                'client_secret': ZOHO_CONFIG['client_secret'],
                'grant_type': 'refresh_token'
            })
            
            if response.status_code == 200:
                data = response.json()
                self.access_token = data['access_token']
                print(f"✅ Access token obtained successfully")
                return True
            else:
                print(f"❌ Failed to get access token: {response.status_code}")
                print(f"Response: {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Error getting access token: {e}")
            return False
    
    def fetch_tickets_batch(self, from_index: int, limit: int = 100) -> Dict:
        """
        Fetch a batch of tickets using from and limit parameters
        Example: from=0, limit=100 returns tickets 0-99
                 from=100, limit=100 returns tickets 100-199
        """
        url = f"{ZOHO_CONFIG['base_url']}/tickets"
        
        params = {
            'orgId': ZOHO_CONFIG['org_id'],
            'from': from_index,  # Starting index
            'limit': limit,      # Number of tickets to fetch
            'sortBy': '-createdTime'  # Most recent first
        }
        
        headers = {
            'Authorization': f'Zoho-oauthtoken {self.access_token}',
            'Content-Type': 'application/json'
        }
        
        try:
            print(f"📡 Fetching tickets {from_index} to {from_index + limit - 1}...")
            
            response = requests.get(url, headers=headers, params=params, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                tickets = data.get('data', [])
                total_count = data.get('info', {}).get('count', 0)
                
                print(f"   ✅ Fetched {len(tickets)} tickets (Total available: {total_count})")
                return {
                    'success': True,
                    'tickets': tickets,
                    'count': len(tickets),
                    'total_available': total_count,
                    'has_more': len(tickets) >= limit
                }
            else:
                print(f"   ❌ API error: {response.status_code}")
                print(f"   Response: {response.text[:200]}")
                return {
                    'success': False,
                    'error': f"Status {response.status_code}",
                    'tickets': []
                }
                
        except Exception as e:
            print(f"   ❌ Request failed: {e}")
            return {
                'success': False,
                'error': str(e),
                'tickets': []
            }
    
    def sync_tickets(self, target_count: int = 2000):
        """
        Sync tickets from Zoho with pagination logic
        Strategy: Fetch in batches of 100 (0-99, 100-199, 200-299, etc.)
        """
        print(f"\n{'='*60}")
        print(f"🚀 Starting Zoho Bulk Sync")
        print(f"📊 Target: {target_count} tickets")
        print(f"{'='*60}\n")
        
        # Step 1: Get access token
        if not self.get_access_token():
            print("❌ Cannot proceed without access token")
            return False
        
        # Step 2: Fetch tickets in batches
        batch_size = 100
        batches_to_fetch = (target_count + batch_size - 1) // batch_size  # Ceiling division
        
        print(f"\n📦 Fetching {batches_to_fetch} batches (100 tickets each)...\n")
        
        total_fetched = 0
        consecutive_empty_batches = 0
        max_empty_batches = 3  # Stop after 3 empty batches
        
        for batch_num in range(batches_to_fetch):
            from_index = batch_num * batch_size
            batch_result = self.fetch_tickets_batch(from_index, batch_size)
            
            if batch_result['success']:
                tickets = batch_result['tickets']
                
                if len(tickets) > 0:
                    self.all_tickets.extend(tickets)
                    total_fetched += len(tickets)
                    consecutive_empty_batches = 0
                    
                    print(f"   📦 Progress: {total_fetched}/{target_count} tickets collected")
                    
                    # Check if we've reached the target
                    if total_fetched >= target_count:
                        print(f"\n✅ Target reached! Collected {total_fetched} tickets")
                        break
                else:
                    consecutive_empty_batches += 1
                    print(f"   ⚠️  Empty batch (no more tickets available)")
                    
                    if consecutive_empty_batches >= max_empty_batches:
                        print(f"\n⚠️  Stopping: {max_empty_batches} consecutive empty batches")
                        break
            else:
                self.failed_requests.append({
                    'batch': batch_num,
                    'from_index': from_index,
                    'error': batch_result.get('error', 'Unknown error')
                })
                print(f"   ❌ Batch {batch_num} failed: {batch_result.get('error')}")
            
            # Rate limiting: Wait between requests
            if batch_num < batches_to_fetch - 1:  # Don't wait after last batch
                time.sleep(1)  # 1 second delay between requests
        
        # Step 3: Summary
        print(f"\n{'='*60}")
        print(f"📊 SYNC SUMMARY")
        print(f"{'='*60}")
        print(f"✅ Total tickets fetched: {len(self.all_tickets)}")
        print(f"❌ Failed batches: {len(self.failed_requests)}")
        
        if len(self.failed_requests) > 0:
            print(f"\n⚠️  Failed batches:")
            for fail in self.failed_requests:
                print(f"   - Batch {fail['batch']} (index {fail['from_index']}): {fail['error']}")
        
        return len(self.all_tickets) > 0
    
    def save_to_json(self, filename: str = 'zoho_tickets_bulk.json'):
        """Save fetched tickets to JSON file"""
        output_data = {
            'sync_timestamp': datetime.now().isoformat(),
            'total_tickets': len(self.all_tickets),
            'tickets': self.all_tickets
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(output_data, f, indent=2, ensure_ascii=False)
        
        print(f"\n💾 Data saved to: {filename}")
        return filename
    
    def filter_tickets_by_date(self, tickets: List[Dict], days_back: int) -> List[Dict]:
        """Filter tickets by date (last N days)"""
        cutoff_date = datetime.now() - timedelta(days=days_back)
        
        filtered = []
        for ticket in tickets:
            created_time = ticket.get('createdTime')
            if created_time:
                try:
                    ticket_date = datetime.fromisoformat(created_time.replace('Z', '+00:00'))
                    if ticket_date >= cutoff_date:
                        filtered.append(ticket)
                except:
                    pass  # Skip tickets with invalid dates
        
        return filtered

def main():
    """Main execution function"""
    print("Zoho Bulk Ticket Sync Script")
    print("=" * 60)
    
    # Create sync instance
    sync = ZohoBulkSync()
    
    # Sync 2000 tickets
    success = sync.sync_tickets(target_count=2000)
    
    if success:
        # Save to JSON
        filename = sync.save_to_json('zoho_tickets_bulk.json')
        
        # Filter yesterday's tickets (2000 tickets)
        yesterday_tickets = sync.filter_tickets_by_date(sync.all_tickets, days_back=1)
        print(f"\n📅 Yesterday's tickets: {len(yesterday_tickets)}")
        
        # Filter today's tickets (500 tickets max)
        today_tickets = sync.filter_tickets_by_date(sync.all_tickets, days_back=0)
        today_tickets = today_tickets[:500]  # Limit to 500
        print(f"📅 Today's tickets: {len(today_tickets)}")
        
        # Save filtered data
        with open('zoho_tickets_yesterday.json', 'w', encoding='utf-8') as f:
            json.dump({
                'sync_timestamp': datetime.now().isoformat(),
                'filter': 'last_24_hours',
                'total_tickets': len(yesterday_tickets),
                'tickets': yesterday_tickets
            }, f, indent=2, ensure_ascii=False)
        
        with open('zoho_tickets_today.json', 'w', encoding='utf-8') as f:
            json.dump({
                'sync_timestamp': datetime.now().isoformat(),
                'filter': 'today_only',
                'total_tickets': len(today_tickets),
                'tickets': today_tickets
            }, f, indent=2, ensure_ascii=False)
        
        print("\n✅ Sync completed successfully!")
    else:
        print("\n❌ Sync failed!")

if __name__ == '__main__':
    main()
