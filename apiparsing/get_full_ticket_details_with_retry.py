#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get Full Ticket Details with Smart Retry Logic
جلب تفاصيل كاملة للتذاكر مع آلية إعادة المحاولة الذكية
"""

import requests
import json
import time
import os
from datetime import datetime
from config import ZohoConfig

class SmartTicketDetailsCollector:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        self.tickets_data = []
        self.failed_tickets = []
        self.rate_limit_wait_time = 60  # Wait 1 minute for rate limiting
        
    def wait_for_rate_limit(self, wait_time=None):
        """Wait for rate limit to reset"""
        if wait_time is None:
            wait_time = self.rate_limit_wait_time
        
        print(f"Rate limit detected. Waiting {wait_time} seconds...")
        for i in range(wait_time, 0, -10):
            print(f"  Waiting {i} seconds...")
            time.sleep(min(10, i))
        print("Resuming...")
    
    def get_access_token_with_retry(self, max_retries=5):
        """Get access token with smart retry logic"""
        print("Getting access token with smart retry...")
        
        token_data = {
            'refresh_token': self.config.REFRESH_TOKEN,
            'client_id': self.config.CLIENT_ID,
            'client_secret': self.config.CLIENT_SECRET,
            'grant_type': 'refresh_token'
        }
        
        for attempt in range(max_retries):
            try:
                print(f"Token attempt {attempt + 1}/{max_retries}...")
                response = requests.post(self.config.TOKEN_URL, data=token_data)
                
                if response.status_code == 200:
                    self.access_token = response.json()['access_token']
                    print(f"Access token obtained successfully!")
                    return True
                else:
                    print(f"Token attempt {attempt + 1} failed: {response.status_code}")
                    print(f"Response: {response.text}")
                    
                    # Check if it's a rate limit error
                    if "too many requests" in response.text.lower() or "access denied" in response.text.lower():
                        if attempt < max_retries - 1:
                            wait_time = 60 + (attempt * 30)  # Increase wait time with each attempt
                            self.wait_for_rate_limit(wait_time)
                            continue
                    
                    # For other errors, wait shorter time
                    if attempt < max_retries - 1:
                        print("Waiting 10 seconds before retry...")
                        time.sleep(10)
                        
            except Exception as e:
                print(f"Token attempt {attempt + 1} error: {e}")
                if attempt < max_retries - 1:
                    time.sleep(10)
        
        print("Failed to get access token after all retries")
        return False
    
    def get_headers(self):
        """Get request headers"""
        return {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.config.ORG_ID,
            "contentType": "application/json; charset=utf-8"
        }
    
    def get_tickets_list_safe(self, limit=20, exclude_auto_close=True):
        """Get list of tickets with error handling"""
        print(f"Getting {limit} tickets from Zoho...")
        
        url = f"{self.config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': self.config.ORG_ID,
            'limit': limit
        }
        
        max_retries = 3
        for attempt in range(max_retries):
            try:
                print(f"Fetching tickets (attempt {attempt + 1}/{max_retries})...")
                response = requests.get(url, headers=self.get_headers(), params=params)
                
                if response.status_code == 200:
                    tickets_data = response.json()
                    all_tickets = tickets_data.get('data', [])
                    
                    if exclude_auto_close:
                        filtered_tickets = [
                            ticket for ticket in all_tickets
                            if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
                        ]
                        print(f"Total tickets: {len(all_tickets)}")
                        print(f"After filtering Auto Close: {len(filtered_tickets)}")
                        return filtered_tickets
                    else:
                        return all_tickets
                        
                elif "too many requests" in response.text.lower():
                    print(f"Rate limit hit on attempt {attempt + 1}")
                    if attempt < max_retries - 1:
                        self.wait_for_rate_limit()
                        continue
                else:
                    print(f"Error getting tickets: {response.status_code} - {response.text}")
                    
            except Exception as e:
                print(f"Exception getting tickets: {e}")
                if attempt < max_retries - 1:
                    time.sleep(5)
        
        print("Failed to get tickets after all retries")
        return []
    
    def get_ticket_details_safe(self, ticket_id, max_retries=3):
        """Get full details for a specific ticket with error handling"""
        try:
            # Get ticket details
            ticket_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
            
            for attempt in range(max_retries):
                try:
                    ticket_response = requests.get(ticket_url, headers=self.get_headers(), 
                                                 params={'orgId': self.config.ORG_ID})
                    
                    if ticket_response.status_code == 200:
                        ticket_data = ticket_response.json()
                        break
                    elif "too many requests" in ticket_response.text.lower():
                        print(f"  Rate limit on ticket details, attempt {attempt + 1}")
                        if attempt < max_retries - 1:
                            time.sleep(30)
                            continue
                    else:
                        print(f"  Error getting ticket details: {ticket_response.status_code}")
                        return None
                        
                except Exception as e:
                    print(f"  Exception getting ticket details: {e}")
                    if attempt < max_retries - 1:
                        time.sleep(5)
            
            if ticket_response.status_code != 200:
                return None
            
            # Get threads for this ticket
            threads_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
            
            for attempt in range(max_retries):
                try:
                    threads_response = requests.get(threads_url, headers=self.get_headers(),
                                                  params={'orgId': self.config.ORG_ID})
                    
                    if threads_response.status_code == 200:
                        threads_data = threads_response.json().get('data', [])
                        break
                    elif "too many requests" in threads_response.text.lower():
                        print(f"  Rate limit on threads, attempt {attempt + 1}")
                        if attempt < max_retries - 1:
                            time.sleep(30)
                            continue
                    else:
                        print(f"  Error getting threads: {threads_response.status_code}")
                        threads_data = []
                        break
                        
                except Exception as e:
                    print(f"  Exception getting threads: {e}")
                    if attempt < max_retries - 1:
                        time.sleep(5)
                    else:
                        threads_data = []
            
            # Combine ticket details with threads
            full_ticket_data = {
                'ticket': ticket_data,
                'threads': threads_data,
                'threads_count': len(threads_data),
                'collected_at': datetime.now().isoformat()
            }
            
            return full_ticket_data
            
        except Exception as e:
            print(f"Exception getting ticket details for {ticket_id}: {e}")
            return None
    
    def collect_tickets_details_safe(self, ticket_ids, delay_between_requests=1.0):
        """Collect full details for multiple tickets with smart delays"""
        print(f"Collecting details for {len(ticket_ids)} tickets...")
        print(f"Delay between requests: {delay_between_requests} seconds")
        print(f"Estimated time: {(len(ticket_ids) * delay_between_requests / 60):.1f} minutes")
        
        collected_data = []
        start_time = time.time()
        
        for i, ticket_id in enumerate(ticket_ids, 1):
            print(f"\n[{i}/{len(ticket_ids)}] Processing ticket {ticket_id}")
            
            # Get full details
            ticket_details = self.get_ticket_details_safe(ticket_id)
            
            if ticket_details:
                collected_data.append(ticket_details)
                threads_count = ticket_details['threads_count']
                print(f"  SUCCESS: {threads_count} threads collected")
            else:
                self.failed_tickets.append(ticket_id)
                print(f"  FAILED: Could not get details")
            
            # Progress tracking
            if i % 5 == 0:
                elapsed_time = time.time() - start_time
                avg_time_per_ticket = elapsed_time / i
                remaining_tickets = len(ticket_ids) - i
                estimated_remaining_time = remaining_tickets * avg_time_per_ticket
                
                print(f"  PROGRESS: {i}/{len(ticket_ids)} ({(i/len(ticket_ids)*100):.1f}%)")
                print(f"  Elapsed: {(elapsed_time/60):.1f} min, Remaining: {(estimated_remaining_time/60):.1f} min")
            
            # Add delay to avoid rate limiting
            if i < len(ticket_ids):
                time.sleep(delay_between_requests)
        
        return collected_data
    
    def save_to_file(self, data, filename):
        """Save collected data to JSON file"""
        print(f"Saving data to {filename}...")
        
        try:
            with open(filename, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=2, ensure_ascii=False)
            print(f"Data saved successfully to {filename}")
            return True
        except Exception as e:
            print(f"Error saving data: {e}")
            return False
    
    def generate_summary_report(self, collected_data):
        """Generate summary report"""
        total_tickets = len(collected_data)
        total_threads = sum(item['threads_count'] for item in collected_data)
        failed_count = len(self.failed_tickets)
        
        summary = {
            'collection_date': datetime.now().isoformat(),
            'total_tickets_requested': total_tickets + failed_count,
            'total_tickets_collected': total_tickets,
            'total_threads_collected': total_threads,
            'failed_tickets': self.failed_tickets,
            'failed_count': failed_count,
            'success_rate': f"{(total_tickets / (total_tickets + failed_count) * 100):.1f}%" if (total_tickets + failed_count) > 0 else "0%",
            'average_threads_per_ticket': total_threads / total_tickets if total_tickets > 0 else 0
        }
        
        return summary

def main():
    print("="*80)
    print("  SMART TICKET DETAILS COLLECTOR")
    print("="*80)
    print()
    print("This script will:")
    print("  - Handle rate limiting automatically")
    print("  - Retry failed requests with smart delays")
    print("  - Collect full ticket details with threads")
    print("  - Start with 20 tickets for testing")
    print()
    
    # Initialize collector
    collector = SmartTicketDetailsCollector()
    
    # Get access token with retry
    if not collector.get_access_token_with_retry():
        print("Failed to get access token after all retries. Exiting...")
        return
    
    # Get list of tickets (excluding Auto Close)
    print("\n" + "="*50)
    print("GETTING TICKETS LIST")
    print("="*50)
    
    tickets_list = collector.get_tickets_list_safe(limit=20, exclude_auto_close=True)
    
    if not tickets_list:
        print("No tickets found. Exiting...")
        return
    
    # Extract ticket IDs
    ticket_ids = [ticket['id'] for ticket in tickets_list]
    print(f"Found {len(ticket_ids)} ticket IDs to process")
    
    # Show first few ticket numbers for verification
    print("\nFirst 5 tickets:")
    for i, ticket in enumerate(tickets_list[:5]):
        print(f"  {i+1}. #{ticket.get('ticketNumber')}: {ticket.get('subject', 'No Subject')[:50]}")
    
    # Collect full details
    print("\n" + "="*50)
    print("COLLECTING FULL DETAILS")
    print("="*50)
    
    collected_data = collector.collect_tickets_details_safe(ticket_ids, delay_between_requests=1.5)
    
    # Generate summary
    summary = collector.generate_summary_report(collected_data)
    
    # Save data
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    details_filename = f"tickets_full_details_smart_{timestamp}.json"
    summary_filename = f"tickets_summary_smart_{timestamp}.json"
    
    collector.save_to_file(collected_data, details_filename)
    collector.save_to_file(summary, summary_filename)
    
    # Print summary
    print("\n" + "="*80)
    print("  COLLECTION SUMMARY")
    print("="*80)
    print(f"Total tickets requested: {summary['total_tickets_requested']}")
    print(f"Total tickets collected: {summary['total_tickets_collected']}")
    print(f"Total threads collected: {summary['total_threads_collected']}")
    print(f"Failed tickets: {summary['failed_count']}")
    print(f"Success rate: {summary['success_rate']}")
    print(f"Average threads per ticket: {summary['average_threads_per_ticket']:.1f}")
    
    if collector.failed_tickets:
        print(f"\nFailed ticket IDs: {collector.failed_tickets}")
    
    print(f"\nFiles created:")
    print(f"  - {details_filename} (detailed data)")
    print(f"  - {summary_filename} (summary report)")
    
    print("\n" + "="*50)
    print("TEST COMPLETED SUCCESSFULLY")
    print("="*50)
    print("Ready for 500 tickets collection!")

if __name__ == "__main__":
    main()
