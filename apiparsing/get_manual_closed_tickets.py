#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Manual Closed Tickets Collector - Current Month
جلب التذاكر المغلقة يدوياً - الشهر الحالي
"""

import requests
import json
import time
from datetime import datetime, timedelta
from config import ZohoConfig

class ManualClosedTicketsCollector:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        self.tickets_data = []
        self.failed_tickets = []
        
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
    
    def get_manual_closed_tickets(self, limit=1000):
        """Get manually closed tickets (non Auto Close) from current month"""
        print("Getting manually closed tickets from current month...")
        
        # Calculate current month date range
        now = datetime.now()
        start_of_month = now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
        
        # Format dates for Zoho API
        start_date = start_of_month.strftime("%Y-%m-%d")
        end_date = now.strftime("%Y-%m-%d")
        
        print(f"Date range: {start_date} to {end_date}")
        print("Note: Will filter by date after fetching tickets")
        
        all_tickets = []
        from_index = 0
        batch_size = 100
        
        while len(all_tickets) < limit:
            current_batch_size = min(batch_size, limit - len(all_tickets))
            
            url = f"{self.config.BASE_URLS['desk']}/tickets"
            params = {
                'orgId': self.config.ORG_ID,
                'from': from_index,
                'limit': current_batch_size,
                'status': 'Closed'  # Only closed tickets
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
                    
                    # Filter for manually closed tickets (non Auto Close) from current month
                    filtered_tickets = []
                    for ticket in batch_tickets:
                        # Check if closed by is not Auto Close
                        closed_by = ticket.get('cf', {}).get('cf_closed_by', '')
                        if closed_by and closed_by != 'Auto Close':
                            # Check if ticket was closed in current month
                            closed_time = ticket.get('closedTime', '')
                            if closed_time:
                                try:
                                    # Parse the closed time (handle different formats)
                                    if 'T' in closed_time:
                                        closed_date = datetime.fromisoformat(closed_time.replace('Z', '+00:00'))
                                    else:
                                        closed_date = datetime.strptime(closed_time, '%Y-%m-%d')
                                    
                                    if closed_date >= start_of_month:
                                        filtered_tickets.append(ticket)
                                        print(f"  Found manually closed ticket: #{ticket.get('ticketNumber')} - Closed by: {closed_by}")
                                except Exception as date_error:
                                    print(f"  Date parsing error for ticket {ticket.get('ticketNumber')}: {date_error}")
                                    # If date parsing fails, include the ticket anyway
                                    filtered_tickets.append(ticket)
                            else:
                                # If no closed time, include the ticket anyway
                                filtered_tickets.append(ticket)
                    
                    all_tickets.extend(filtered_tickets)
                    print(f"Batch result: {len(batch_tickets)} total, {len(filtered_tickets)} manually closed from current month")
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
                    print(f"Response: {response.text}")
                    break
                    
            except Exception as e:
                print(f"Exception getting tickets batch: {e}")
                break
        
        print(f"Final result: {len(all_tickets)} manually closed tickets collected")
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
    
    def collect_tickets_details(self, ticket_ids, delay_between_requests=0.2):
        """Collect full details for multiple tickets"""
        print(f"Collecting details for {len(ticket_ids)} tickets...")
        print(f"Delay between requests: {delay_between_requests} seconds")
        
        collected_data = []
        start_time = time.time()
        
        for i, ticket_id in enumerate(ticket_ids, 1):
            print(f"\n[{i}/{len(ticket_ids)}] Processing ticket {ticket_id}")
            
            # Get full details
            ticket_details = self.get_ticket_details(ticket_id)
            
            if ticket_details:
                collected_data.append(ticket_details)
                threads_count = ticket_details['threads_count']
                ticket_number = ticket_details['ticket'].get('ticketNumber', 'N/A')
                closed_by = ticket_details['ticket'].get('cf', {}).get('cf_closed_by', 'N/A')
                print(f"  SUCCESS: #{ticket_number} - Closed by: {closed_by} - {threads_count} threads")
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
            
            # Add delay to avoid rate limiting
            if i < len(ticket_ids):
                time.sleep(delay_between_requests)
        
        return collected_data
    
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
        """Generate summary report"""
        total_tickets = len(collected_data)
        total_threads = sum(item['threads_count'] for item in collected_data)
        failed_count = len(self.failed_tickets)
        
        # Analyze closed by
        closed_by_counts = {}
        department_counts = {}
        
        for item in collected_data:
            ticket = item['ticket']
            
            # Closed by analysis
            closed_by = ticket.get('cf', {}).get('cf_closed_by', 'Unknown')
            closed_by_counts[closed_by] = closed_by_counts.get(closed_by, 0) + 1
            
            # Department analysis
            dept_id = ticket.get('departmentId', 'Unknown')
            department_counts[dept_id] = department_counts.get(dept_id, 0) + 1
        
        summary = {
            'collection_date': datetime.now().isoformat(),
            'collection_period': f"Current month ({datetime.now().strftime('%Y-%m')})",
            'total_tickets_requested': total_tickets + failed_count,
            'total_tickets_collected': total_tickets,
            'total_threads_collected': total_threads,
            'failed_tickets': self.failed_tickets,
            'failed_count': failed_count,
            'success_rate': f"{(total_tickets / (total_tickets + failed_count) * 100):.1f}%" if (total_tickets + failed_count) > 0 else "0%",
            'average_threads_per_ticket': total_threads / total_tickets if total_tickets > 0 else 0,
            'analysis': {
                'closed_by_distribution': closed_by_counts,
                'department_distribution': department_counts
            }
        }
        
        return summary

def main():
    print("="*80)
    print("  MANUAL CLOSED TICKETS COLLECTOR - CURRENT MONTH")
    print("  جلب التذاكر المغلقة يدوياً - الشهر الحالي")
    print("="*80)
    print()
    
    # Initialize collector
    collector = ManualClosedTicketsCollector()
    
    # Get access token
    if not collector.get_access_token():
        print("Failed to get access token. Exiting...")
        return
    
    # Get list of manually closed tickets from current month
    print("\n" + "="*50)
    print("GETTING MANUALLY CLOSED TICKETS")
    print("="*50)
    
    tickets_list = collector.get_manual_closed_tickets(limit=1000)
    
    if not tickets_list:
        print("No manually closed tickets found for current month.")
        return
    
    # Extract ticket IDs
    ticket_ids = [ticket['id'] for ticket in tickets_list]
    print(f"Found {len(ticket_ids)} manually closed ticket IDs to process")
    
    # Show first few ticket numbers for verification
    print("First 5 manually closed tickets:")
    for i, ticket in enumerate(tickets_list[:5]):
        closed_by = ticket.get('cf', {}).get('cf_closed_by', 'Unknown')
        closed_time = ticket.get('closedTime', 'Unknown')
        print(f"  {i+1}. #{ticket.get('ticketNumber')}: Closed by {closed_by} on {closed_time[:10]}")
    
    # Confirm before proceeding
    print(f"\nReady to collect full details for {len(ticket_ids)} manually closed tickets")
    print("This will take approximately 5-10 minutes with rate limiting")
    
    # Collect full details
    print("\n" + "="*50)
    print("COLLECTING FULL DETAILS")
    print("="*50)
    
    collected_data = collector.collect_tickets_details(ticket_ids, delay_between_requests=0.2)
    
    # Generate summary
    print("\n" + "="*50)
    print("GENERATING SUMMARY")
    print("="*50)
    
    summary = collector.generate_summary_report(collected_data)
    
    # Save data
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    
    # Save detailed data
    detailed_filename = f"manual_closed_tickets_{timestamp}.json"
    collector.save_to_file(collected_data, detailed_filename)
    
    # Save summary
    summary_filename = f"manual_closed_summary_{timestamp}.json"
    collector.save_to_file(summary, summary_filename)
    
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
    
    print(f"\nClosed By Distribution:")
    for closed_by, count in summary['analysis']['closed_by_distribution'].items():
        print(f"  {closed_by}: {count}")
    
    if collector.failed_tickets:
        print(f"\nFailed ticket IDs ({len(collector.failed_tickets)}):")
        for ticket_id in collector.failed_tickets[:10]:  # Show first 10
            print(f"  {ticket_id}")
        if len(collector.failed_tickets) > 10:
            print(f"  ... and {len(collector.failed_tickets) - 10} more")
    
    print(f"\nFiles created:")
    print(f"  - {detailed_filename} (detailed data)")
    print(f"  - {summary_filename} (summary report)")

if __name__ == "__main__":
    main()
