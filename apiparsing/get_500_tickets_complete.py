#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get Complete Details for 500 Tickets with Full Threads
جلب تفاصيل كاملة لـ 500 تذكرة مع جميع الخيوط
"""

import requests
import json
import time
import os
from datetime import datetime
from config import ZohoConfig

class MassTicketCollector500:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        self.tickets_data = []
        self.failed_tickets = []
        self.rate_limit_wait_time = 120  # Wait 2 minutes for rate limiting
        self.progress_file = "progress_500_collection.json"
        
    def wait_for_rate_limit(self, wait_time=None):
        """Wait for rate limit to reset"""
        if wait_time is None:
            wait_time = self.rate_limit_wait_time
        
        print(f"Rate limit detected. Waiting {wait_time} seconds...")
        for i in range(wait_time, 0, -15):
            print(f"  Waiting {i} seconds...")
            time.sleep(min(15, i))
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
                    
                    # Check if it's a rate limit error
                    if "too many requests" in response.text.lower() or "access denied" in response.text.lower():
                        if attempt < max_retries - 1:
                            wait_time = 120 + (attempt * 60)  # 2, 3, 4, 5 minutes
                            self.wait_for_rate_limit(wait_time)
                            continue
                    
                    # For other errors, wait shorter time
                    if attempt < max_retries - 1:
                        print("Waiting 15 seconds before retry...")
                        time.sleep(15)
                        
            except Exception as e:
                print(f"Token attempt {attempt + 1} error: {e}")
                if attempt < max_retries - 1:
                    time.sleep(15)
        
        print("Failed to get access token after all retries")
        return False
    
    def get_headers(self):
        """Get request headers"""
        return {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.config.ORG_ID,
            "contentType": "application/json; charset=utf-8"
        }
    
    def get_tickets_list_batch(self, batch_size=100, total_limit=500):
        """Get tickets in batches to handle pagination"""
        print(f"Getting {total_limit} tickets in batches of {batch_size}...")
        
        all_tickets = []
        from_index = 0
        
        while len(all_tickets) < total_limit:
            current_batch_size = min(batch_size, total_limit - len(all_tickets))
            
            url = f"{self.config.BASE_URLS['desk']}/tickets"
            params = {
                'orgId': self.config.ORG_ID,
                'from': from_index,
                'limit': current_batch_size
            }
            
            max_retries = 3
            for attempt in range(max_retries):
                try:
                    print(f"Fetching batch: from={from_index}, limit={current_batch_size} (attempt {attempt + 1})")
                    response = requests.get(url, headers=self.get_headers(), params=params)
                    
                    if response.status_code == 200:
                        tickets_data = response.json()
                        batch_tickets = tickets_data.get('data', [])
                        
                        if not batch_tickets:
                            print("No more tickets available")
                            return all_tickets
                        
                        # Filter out Auto Close tickets
                        filtered_tickets = [
                            ticket for ticket in batch_tickets
                            if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
                        ]
                        
                        all_tickets.extend(filtered_tickets)
                        print(f"Batch result: {len(batch_tickets)} total, {len(filtered_tickets)} after filtering")
                        print(f"Total collected so far: {len(all_tickets)}")
                        
                        # Check if we got fewer tickets than requested
                        if len(batch_tickets) < current_batch_size:
                            print("Reached end of available tickets")
                            return all_tickets
                        
                        from_index += current_batch_size
                        break  # Success, exit retry loop
                        
                    elif "too many requests" in response.text.lower():
                        print(f"Rate limit hit on batch attempt {attempt + 1}")
                        if attempt < max_retries - 1:
                            self.wait_for_rate_limit()
                            continue
                    else:
                        print(f"Error getting tickets batch: {response.status_code}")
                        if attempt < max_retries - 1:
                            time.sleep(30)
                        
                except Exception as e:
                    print(f"Exception getting tickets batch: {e}")
                    if attempt < max_retries - 1:
                        time.sleep(30)
            
            # Rate limiting between batches
            if len(all_tickets) < total_limit:
                print("Waiting 2 seconds between batches...")
                time.sleep(2)
        
        print(f"Final result: {len(all_tickets)} tickets collected")
        return all_tickets
    
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
                            time.sleep(60)
                            continue
                    else:
                        print(f"  Error getting ticket details: {ticket_response.status_code}")
                        return None
                        
                except Exception as e:
                    print(f"  Exception getting ticket details: {e}")
                    if attempt < max_retries - 1:
                        time.sleep(10)
            
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
                            time.sleep(60)
                            continue
                    else:
                        print(f"  Error getting threads: {threads_response.status_code}")
                        threads_data = []
                        break
                        
                except Exception as e:
                    print(f"  Exception getting threads: {e}")
                    if attempt < max_retries - 1:
                        time.sleep(10)
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
    
    def save_progress(self, collected_data, processed_count, total_count):
        """Save progress to file"""
        progress_data = {
            'processed_count': processed_count,
            'total_count': total_count,
            'last_update': datetime.now().isoformat(),
            'collected_data': collected_data[-5:] if len(collected_data) > 5 else collected_data,  # Keep last 5 for verification
            'failed_tickets': self.failed_tickets,
            'success_rate': f"{(processed_count / total_count * 100):.1f}%" if total_count > 0 else "0%"
        }
        
        try:
            with open(self.progress_file, 'w', encoding='utf-8') as f:
                json.dump(progress_data, f, indent=2, ensure_ascii=False)
            print(f"Progress saved: {processed_count}/{total_count} ({progress_data['success_rate']})")
        except Exception as e:
            print(f"Error saving progress: {e}")
    
    def collect_tickets_details_mass(self, ticket_ids, delay_between_requests=1.0):
        """Collect full details for multiple tickets with progress tracking"""
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
            if i % 10 == 0:
                elapsed_time = time.time() - start_time
                avg_time_per_ticket = elapsed_time / i
                remaining_tickets = len(ticket_ids) - i
                estimated_remaining_time = remaining_tickets * avg_time_per_ticket
                
                print(f"  PROGRESS: {i}/{len(ticket_ids)} ({(i/len(ticket_ids)*100):.1f}%)")
                print(f"  Elapsed: {(elapsed_time/60):.1f} min, Remaining: {(estimated_remaining_time/60):.1f} min")
                
                # Save progress every 10 tickets
                self.save_progress(collected_data, i, len(ticket_ids))
            
            # Add delay to avoid rate limiting
            if i < len(ticket_ids):
                time.sleep(delay_between_requests)
        
        return collected_data
    
    def save_to_files(self, collected_data, summary):
        """Save collected data to multiple files"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        
        # Save detailed data in chunks (to avoid huge files)
        chunk_size = 50
        chunk_files = []
        for i in range(0, len(collected_data), chunk_size):
            chunk = collected_data[i:i + chunk_size]
            chunk_filename = f"tickets_500_chunk_{i//chunk_size + 1}_{timestamp}.json"
            if self.save_to_file(chunk, chunk_filename):
                chunk_files.append(chunk_filename)
        
        # Save summary
        summary_filename = f"tickets_500_summary_{timestamp}.json"
        self.save_to_file(summary, summary_filename)
        
        return timestamp, chunk_files
    
    def save_to_file(self, data, filename):
        """Save data to JSON file"""
        try:
            with open(filename, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=2, ensure_ascii=False)
            print(f"Data saved to {filename}")
            return True
        except Exception as e:
            print(f"Error saving {filename}: {e}")
            return False
    
    def generate_summary_report(self, collected_data):
        """Generate comprehensive summary report"""
        total_tickets = len(collected_data)
        total_threads = sum(item['threads_count'] for item in collected_data)
        failed_count = len(self.failed_tickets)
        
        # Analyze ticket statuses
        status_counts = {}
        department_counts = {}
        thread_counts = {}
        
        for item in collected_data:
            ticket = item['ticket']
            
            # Status analysis
            status = ticket.get('status', 'Unknown')
            status_counts[status] = status_counts.get(status, 0) + 1
            
            # Department analysis
            dept_id = ticket.get('departmentId', 'Unknown')
            department_counts[dept_id] = department_counts.get(dept_id, 0) + 1
            
            # Thread count analysis
            thread_count = item['threads_count']
            if thread_count == 0:
                thread_counts['0'] = thread_counts.get('0', 0) + 1
            elif thread_count <= 5:
                thread_counts['1-5'] = thread_counts.get('1-5', 0) + 1
            elif thread_count <= 10:
                thread_counts['6-10'] = thread_counts.get('6-10', 0) + 1
            else:
                thread_counts['10+'] = thread_counts.get('10+', 0) + 1
        
        summary = {
            'collection_date': datetime.now().isoformat(),
            'total_tickets_requested': total_tickets + failed_count,
            'total_tickets_collected': total_tickets,
            'total_threads_collected': total_threads,
            'failed_tickets': self.failed_tickets,
            'failed_count': failed_count,
            'success_rate': f"{(total_tickets / (total_tickets + failed_count) * 100):.1f}%" if (total_tickets + failed_count) > 0 else "0%",
            'average_threads_per_ticket': total_threads / total_tickets if total_tickets > 0 else 0,
            'analysis': {
                'status_distribution': status_counts,
                'department_distribution': department_counts,
                'thread_count_distribution': thread_counts
            },
            'file_info': {
                'chunks_created': (total_tickets + 49) // 50 if total_tickets > 0 else 0,
                'estimated_total_size_mb': round(total_tickets * 0.05, 2) if total_tickets > 0 else 0
            }
        }
        
        return summary

def main():
    print("="*80)
    print("  MASS TICKET COLLECTOR - 500 TICKETS WITH FULL THREADS")
    print("="*80)
    print()
    print("This script will:")
    print("  - Collect 500 tickets with complete details")
    print("  - Get all threads for each ticket")
    print("  - Handle rate limiting automatically")
    print("  - Save data in chunks for easy handling")
    print("  - Exclude Auto Close tickets")
    print()
    print("Estimated time: 15-25 minutes")
    print("Files will be saved in chunks of 50 tickets each")
    print()
    
    # Initialize collector
    collector = MassTicketCollector500()
    
    # Get access token with retry
    if not collector.get_access_token_with_retry():
        print("Failed to get access token after all retries. Exiting...")
        return
    
    # Get list of 500 tickets (excluding Auto Close)
    print("\n" + "="*50)
    print("GETTING 500 TICKETS LIST")
    print("="*50)
    
    tickets_list = collector.get_tickets_list_batch(batch_size=100, total_limit=500)
    
    if not tickets_list:
        print("No tickets found. Exiting...")
        return
    
    # Extract ticket IDs
    ticket_ids = [ticket['id'] for ticket in tickets_list]
    print(f"Found {len(ticket_ids)} ticket IDs to process")
    
    # Show sample tickets for verification
    print("\nSample tickets:")
    for i, ticket in enumerate(tickets_list[:3]):
        print(f"  {i+1}. #{ticket.get('ticketNumber')}: {ticket.get('subject', 'No Subject')[:60]}")
    
    # Confirm before proceeding
    print(f"\nReady to collect full details for {len(ticket_ids)} tickets")
    print("This will take approximately 15-25 minutes with rate limiting")
    print("Progress will be saved every 10 tickets")
    
    # Collect full details
    print("\n" + "="*50)
    print("COLLECTING FULL DETAILS")
    print("="*50)
    
    collected_data = collector.collect_tickets_details_mass(ticket_ids, delay_between_requests=1.2)
    
    # Generate summary
    print("\n" + "="*50)
    print("GENERATING SUMMARY")
    print("="*50)
    
    summary = collector.generate_summary_report(collected_data)
    
    # Save all data
    timestamp, chunk_files = collector.save_to_files(collected_data, summary)
    
    # Print final summary
    print("\n" + "="*80)
    print("  500 TICKETS COLLECTION COMPLETED")
    print("="*80)
    print(f"Total tickets requested: {summary['total_tickets_requested']}")
    print(f"Total tickets collected: {summary['total_tickets_collected']}")
    print(f"Total threads collected: {summary['total_threads_collected']}")
    print(f"Failed tickets: {summary['failed_count']}")
    print(f"Success rate: {summary['success_rate']}")
    print(f"Average threads per ticket: {summary['average_threads_per_ticket']:.1f}")
    
    print(f"\nStatus Distribution:")
    for status, count in summary['analysis']['status_distribution'].items():
        print(f"  {status}: {count}")
    
    print(f"\nThread Count Distribution:")
    for range_key, count in summary['analysis']['thread_count_distribution'].items():
        print(f"  {range_key} threads: {count} tickets")
    
    if collector.failed_tickets:
        print(f"\nFailed ticket IDs ({len(collector.failed_tickets)}):")
        for ticket_id in collector.failed_tickets[:5]:  # Show first 5
            print(f"  {ticket_id}")
        if len(collector.failed_tickets) > 5:
            print(f"  ... and {len(collector.failed_tickets) - 5} more")
    
    print(f"\nFiles created with timestamp {timestamp}:")
    for chunk_file in chunk_files:
        print(f"  - {chunk_file} (50 tickets each)")
    print(f"  - tickets_500_summary_{timestamp}.json (summary report)")
    
    # Clean up progress file
    if os.path.exists(collector.progress_file):
        os.remove(collector.progress_file)
        print(f"  - Progress file cleaned up")
    
    print(f"\nCollection completed successfully!")
    print(f"Total estimated file size: {summary['file_info']['estimated_total_size_mb']} MB")

if __name__ == "__main__":
    main()

