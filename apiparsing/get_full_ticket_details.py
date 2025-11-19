#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get Full Ticket Details with Complete Threads
جلب تفاصيل كاملة للتذاكر مع جميع الخيوط
"""

import requests
import json
import time
import os
from datetime import datetime
from config import ZohoConfig

class FullTicketDetailsCollector:
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
                print("Waiting 2 seconds before retry...")
                time.sleep(2)
        
        print("Failed to get access token after 3 attempts")
        return False
    
    def get_headers(self):
        """Get request headers"""
        return {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.config.ORG_ID,
            "contentType": "application/json; charset=utf-8"
        }
    
    def get_tickets_list(self, limit=100, exclude_auto_close=True):
        """Get list of tickets"""
        print(f"Getting {limit} tickets from Zoho...")
        
        url = f"{self.config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': self.config.ORG_ID,
            'limit': limit
        }
        
        try:
            response = requests.get(url, headers=self.get_headers(), params=params)
            if response.status_code == 200:
                tickets_data = response.json()
                all_tickets = tickets_data.get('data', [])
                
                if exclude_auto_close:
                    # Filter out Auto Close tickets
                    filtered_tickets = [
                        ticket for ticket in all_tickets
                        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
                    ]
                    print(f"Total tickets: {len(all_tickets)}")
                    print(f"After filtering Auto Close: {len(filtered_tickets)}")
                    print(f"Auto Close excluded: {len(all_tickets) - len(filtered_tickets)}")
                    return filtered_tickets
                else:
                    return all_tickets
            else:
                print(f"Error getting tickets: {response.status_code} - {response.text}")
                return []
        except Exception as e:
            print(f"Exception getting tickets: {e}")
            return []
    
    def get_ticket_details(self, ticket_id):
        """Get full details for a specific ticket"""
        print(f"Getting details for ticket {ticket_id}...")
        
        try:
            # Get ticket details
            ticket_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
            ticket_response = requests.get(ticket_url, headers=self.get_headers(), 
                                         params={'orgId': self.config.ORG_ID})
            
            if ticket_response.status_code != 200:
                print(f"Error getting ticket details: {ticket_response.status_code}")
                return None
            
            ticket_data = ticket_response.json()
            
            # Get threads for this ticket
            threads_url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
            threads_response = requests.get(threads_url, headers=self.get_headers(),
                                          params={'orgId': self.config.ORG_ID})
            
            threads_data = []
            if threads_response.status_code == 200:
                threads_data = threads_response.json().get('data', [])
                print(f"  Found {len(threads_data)} threads")
            else:
                print(f"  Error getting threads: {threads_response.status_code}")
            
            # Combine ticket details with threads
            full_ticket_data = {
                'ticket': ticket_data,
                'threads': threads_data,
                'threads_count': len(threads_data),
                'collected_at': datetime.now().isoformat()
            }
            
            return full_ticket_data
            
        except Exception as e:
            print(f"Exception getting ticket details: {e}")
            return None
    
    def collect_tickets_details(self, ticket_ids, delay_between_requests=0.5):
        """Collect full details for multiple tickets"""
        print(f"Collecting details for {len(ticket_ids)} tickets...")
        print(f"Delay between requests: {delay_between_requests} seconds")
        
        collected_data = []
        
        for i, ticket_id in enumerate(ticket_ids, 1):
            print(f"\n[{i}/{len(ticket_ids)}] Processing ticket {ticket_id}")
            
            # Get full details
            ticket_details = self.get_ticket_details(ticket_id)
            
            if ticket_details:
                collected_data.append(ticket_details)
                print(f"  SUCCESS: Collected ticket details with {ticket_details['threads_count']} threads")
            else:
                self.failed_tickets.append(ticket_id)
                print(f"  FAILED: Could not get details for ticket {ticket_id}")
            
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
    print("  FULL TICKET DETAILS COLLECTOR")
    print("="*80)
    print()
    
    # Initialize collector
    collector = FullTicketDetailsCollector()
    
    # Get access token
    if not collector.get_access_token():
        print("Failed to get access token. Exiting...")
        return
    
    # Test with 20 tickets first
    print("\n" + "="*50)
    print("TESTING WITH 20 TICKETS")
    print("="*50)
    
    # Get list of tickets (excluding Auto Close)
    tickets_list = collector.get_tickets_list(limit=20, exclude_auto_close=True)
    
    if not tickets_list:
        print("No tickets found. Exiting...")
        return
    
    # Extract ticket IDs
    ticket_ids = [ticket['id'] for ticket in tickets_list]
    print(f"Found {len(ticket_ids)} ticket IDs to process")
    
    # Collect full details
    collected_data = collector.collect_tickets_details(ticket_ids, delay_between_requests=0.3)
    
    # Generate summary
    summary = collector.generate_summary_report(collected_data)
    
    # Save data
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    
    # Save detailed data
    details_filename = f"tickets_full_details_{timestamp}.json"
    collector.save_to_file(collected_data, details_filename)
    
    # Save summary
    summary_filename = f"tickets_summary_{timestamp}.json"
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
    
    # Ask if user wants to continue with more tickets
    print("\n" + "="*50)
    print("TEST COMPLETED SUCCESSFULLY")
    print("="*50)
    print("To collect 500 tickets, modify the limit in get_tickets_list()")
    print("Example: tickets_list = collector.get_tickets_list(limit=500)")

if __name__ == "__main__":
    main()

