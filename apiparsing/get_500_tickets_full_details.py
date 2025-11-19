#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get Full Details for 500 Tickets with Complete Threads
جلب تفاصيل كاملة لـ 500 تذكرة مع جميع الخيوط
"""

import requests
import json
import time
import os
from datetime import datetime
from config import ZohoConfig

class MassTicketDetailsCollector:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        self.tickets_data = []
        self.failed_tickets = []
        self.progress_file = "progress_500_tickets.json"
        
    def get_access_token(self):
        """Get access token with retry logic"""
        print("Getting access token...")
        
        token_data = {
            'refresh_token': self.config.REFRESH_TOKEN,
            'client_id': self.config.CLIENT_ID,
            'client_secret': self.config.CLIENT_SECRET,
            'grant_type': 'refresh_token'
        }
        
        for attempt in range(3):
            try:
                response = requests.post(self.config.TOKEN_URL, data=token_data)
                if response.status_code == 200:
                    self.access_token = response.json()['access_token']
                    print(f"Access token obtained successfully (attempt {attempt + 1})")
                    return True
                else:
                    print(f"Token attempt {attempt + 1} failed: {response.status_code}")
            except Exception as e:
                print(f"Token attempt {attempt + 1} error: {e}")
            
            if attempt < 2:
                print("Waiting 3 seconds before retry...")
                time.sleep(3)
        
        print("Failed to get access token after 3 attempts")
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
            
            try:
                print(f"Fetching batch: from={from_index}, limit={current_batch_size}")
                response = requests.get(url, headers=self.get_headers(), params=params)
                
                if response.status_code == 200:
                    tickets_data = response.json()
                    batch_tickets = tickets_data.get('data', [])
                    
                    if not batch_tickets:
                        print("No more tickets available")
                        break
                    
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
                        break
                    
                    from_index += current_batch_size
                    
                    # Rate limiting between batches
                    time.sleep(1)
                    
                else:
                    print(f"Error getting tickets batch: {response.status_code}")
                    break
                    
            except Exception as e:
                print(f"Exception getting tickets batch: {e}")
                break
        
        print(f"Final result: {len(all_tickets)} tickets collected")
        return all_tickets
    
    def get_ticket_details(self, ticket_id):
        """Get full details for a specific ticket"""
        try:
            # Get ticket details
            ticket_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
            ticket_response = requests.get(ticket_url, headers=self.get_headers(), 
                                         params={'orgId': self.config.ORG_ID})
            
            if ticket_response.status_code != 200:
                return None
            
            ticket_data = ticket_response.json()
            
            # Get threads for this ticket
            threads_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
            threads_response = requests.get(threads_url, headers=self.get_headers(),
                                          params={'orgId': self.config.ORG_ID})
            
            threads_data = []
            if threads_response.status_code == 200:
                threads_data = threads_response.json().get('data', [])
            
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
            'collected_data': collected_data[-10:] if len(collected_data) > 10 else collected_data,  # Keep last 10 for verification
            'failed_tickets': self.failed_tickets
        }
        
        try:
            with open(self.progress_file, 'w', encoding='utf-8') as f:
                json.dump(progress_data, f, indent=2, ensure_ascii=False)
        except Exception as e:
            print(f"Error saving progress: {e}")
    
    def collect_tickets_details_mass(self, ticket_ids, delay_between_requests=0.2):
        """Collect full details for multiple tickets with progress tracking"""
        print(f"Collecting details for {len(ticket_ids)} tickets...")
        print(f"Delay between requests: {delay_between_requests} seconds")
        print(f"Estimated time: {(len(ticket_ids) * delay_between_requests / 60):.1f} minutes")
        
        collected_data = []
        start_time = time.time()
        
        for i, ticket_id in enumerate(ticket_ids, 1):
            print(f"\n[{i}/{len(ticket_ids)}] Processing ticket {ticket_id}")
            
            # Get full details
            ticket_details = self.get_ticket_details(ticket_id)
            
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
        for i in range(0, len(collected_data), chunk_size):
            chunk = collected_data[i:i + chunk_size]
            chunk_filename = f"tickets_full_details_chunk_{i//chunk_size + 1}_{timestamp}.json"
            self.save_to_file(chunk, chunk_filename)
        
        # Save summary
        summary_filename = f"tickets_500_summary_{timestamp}.json"
        self.save_to_file(summary, summary_filename)
        
        # Save complete data (if not too large)
        if len(collected_data) <= 100:
            complete_filename = f"tickets_complete_data_{timestamp}.json"
            self.save_to_file(collected_data, complete_filename)
        
        return timestamp
    
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
                'chunks_created': (total_tickets + chunk_size - 1) // 50 if total_tickets > 0 else 0,
                'estimated_total_size_mb': (total_tickets * 0.05) if total_tickets > 0 else 0  # Rough estimate
            }
        }
        
        return summary

def main():
    print("="*80)
    print("  MASS TICKET DETAILS COLLECTOR - 500 TICKETS")
    print("="*80)
    print()
    
    # Initialize collector
    collector = MassTicketDetailsCollector()
    
    # Get access token
    if not collector.get_access_token():
        print("Failed to get access token. Exiting...")
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
    
    # Show first few ticket numbers for verification
    print("First 5 ticket numbers:")
    for i, ticket in enumerate(tickets_list[:5]):
        print(f"  {i+1}. #{ticket.get('ticketNumber')}: {ticket.get('subject', 'No Subject')[:50]}")
    
    # Confirm before proceeding
    print(f"\nReady to collect full details for {len(ticket_ids)} tickets")
    print("This will take approximately 10-15 minutes with rate limiting")
    print("Progress will be saved every 10 tickets")
    
    # Collect full details
    print("\n" + "="*50)
    print("COLLECTING FULL DETAILS")
    print("="*50)
    
    collected_data = collector.collect_tickets_details_mass(ticket_ids, delay_between_requests=0.2)
    
    # Generate summary
    print("\n" + "="*50)
    print("GENERATING SUMMARY")
    print("="*50)
    
    summary = collector.generate_summary_report(collected_data)
    
    # Save all data
    timestamp = collector.save_to_files(collected_data, summary)
    
    # Print final summary
    print("\n" + "="*80)
    print("  COLLECTION COMPLETED")
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
        for ticket_id in collector.failed_tickets[:10]:  # Show first 10
            print(f"  {ticket_id}")
        if len(collector.failed_tickets) > 10:
            print(f"  ... and {len(collector.failed_tickets) - 10} more")
    
    print(f"\nFiles created with timestamp {timestamp}:")
    print(f"  - tickets_full_details_chunk_*_{timestamp}.json (detailed data in chunks)")
    print(f"  - tickets_500_summary_{timestamp}.json (summary report)")
    if summary['total_tickets_collected'] <= 100:
        print(f"  - tickets_complete_data_{timestamp}.json (complete data)")
    
    # Clean up progress file
    if os.path.exists(collector.progress_file):
        os.remove(collector.progress_file)
        print(f"  - Progress file {collector.progress_file} cleaned up")

if __name__ == "__main__":
    main()

