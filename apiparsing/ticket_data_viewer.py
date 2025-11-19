#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Flask web application for viewing complete ticket data
"""

from flask import Flask, render_template, request, jsonify
from zoho_api import ZohoAPI
import json
from datetime import datetime

app = Flask(__name__)

class TicketDataViewer:
    def __init__(self):
        self.zoho = ZohoAPI()
    
    def get_complete_ticket_data(self, ticket_number):
        """Get complete data for a specific ticket number"""
        try:
            # Get access token with retry
            token = None
            for attempt in range(3):
                token = self.zoho.get_access_token()
                if token:
                    break
                print(f"Attempt {attempt + 1} to get access token failed")
                if attempt < 2:
                    import time
                    time.sleep(2)  # Wait 2 seconds before retry
            
            if not token:
                return {"error": "Failed to get access token after 3 attempts. Please check your credentials."}
            
            # Find ticket by number
            tickets_response = self.zoho.get_tickets(limit=100)
            if not tickets_response or 'data' not in tickets_response:
                return {"error": "Failed to get tickets list"}
            
            tickets = tickets_response['data']
            target_ticket = None
            
            for ticket in tickets:
                if ticket.get('ticketNumber') == ticket_number:
                    target_ticket = ticket
                    break
            
            if not target_ticket:
                return {"error": f"Ticket #{ticket_number} not found in recent tickets"}
            
            ticket_id = target_ticket['id']
            
            # Get full ticket details
            ticket_response = self.zoho.make_request('GET', 
                f"{self.zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                params={'orgId': self.zoho.config.ORG_ID})
            
            if not ticket_response:
                return {"error": "Failed to get ticket details"}
            
            ticket = ticket_response
            
            # Get threads
            threads_response = self.zoho.make_request('GET', 
                f"{self.zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads",
                params={'orgId': self.zoho.config.ORG_ID})
            
            threads = []
            if threads_response and 'data' in threads_response:
                threads = threads_response['data']
                
                # Get comments for each thread
                for thread in threads:
                    thread_id = thread.get('id')
                    if thread_id:
                        comments_response = self.zoho.make_request('GET', 
                            f"{self.zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads/{thread_id}/comments",
                            params={'orgId': self.zoho.config.ORG_ID})
                        
                        if comments_response and 'data' in comments_response:
                            thread['comments'] = comments_response['data']
                        else:
                            thread['comments'] = []
            
            # Compile complete data
            complete_data = {
                'export_info': {
                    'exported_at': datetime.now().isoformat(),
                    'ticket_number': ticket_number,
                    'ticket_id': ticket_id,
                    'export_type': 'complete_ticket_data'
                },
                'ticket': ticket,
                'threads': threads,
                'statistics': {
                    'total_threads': len(threads),
                    'total_comments': sum(len(thread.get('comments', [])) for thread in threads)
                }
            }
            
            return complete_data
            
        except Exception as e:
            return {"error": f"Error retrieving ticket data: {str(e)}"}

# Initialize viewer
viewer = TicketDataViewer()

@app.route('/')
def index():
    return render_template('ticket_data_viewer.html')

@app.route('/api/tickets')
def get_recent_tickets():
    """API endpoint to get recent tickets"""
    try:
        # Get access token with retry
        token = None
        for attempt in range(3):
            token = viewer.zoho.get_access_token()
            if token:
                break
            if attempt < 2:
                import time
                time.sleep(1)  # Wait 1 second before retry
        
        if not token:
            return jsonify({"error": "Failed to get access token. Please check your Zoho credentials."})
        
        tickets_response = viewer.zoho.get_tickets(limit=100)
        if not tickets_response or 'data' not in tickets_response:
            return jsonify({"error": "Failed to get tickets list"})
        
        tickets = tickets_response['data']
        # Add summary info for each ticket
        for ticket in tickets:
            ticket['summary'] = {
                'has_cf_closed_by': ticket.get('cf', {}).get('cf_closed_by') is not None,
                'cf_closed_by_value': ticket.get('cf', {}).get('cf_closed_by'),
                'has_custom_fields': bool(ticket.get('customFields')),
                'custom_fields_count': len([v for v in (ticket.get('customFields') or {}).values() if v is not None]),
                'cf_fields_count': len([v for v in (ticket.get('cf') or {}).values() if v is not None])
            }
        
        return jsonify({"tickets": tickets})
    except Exception as e:
        return jsonify({"error": f"Error retrieving tickets: {str(e)}"})

@app.route('/api/ticket/<ticket_number>')
def get_ticket_data(ticket_number):
    """API endpoint to get ticket data"""
    data = viewer.get_complete_ticket_data(ticket_number)
    return jsonify(data)

if __name__ == '__main__':
    print("🚀 Starting Ticket Data Viewer...")
    print("=" * 60)
    print("🌐 Open your browser and go to: http://localhost:5001")
    print("⏹️  Press Ctrl+C to stop the server")
    print("=" * 60)
    app.run(host='0.0.0.0', port=5001, debug=True)
