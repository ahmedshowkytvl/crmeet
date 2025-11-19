#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Web interface for viewing tickets - Summary view and detailed view
"""

from flask import Flask, render_template, jsonify, request
from zoho_api import ZohoAPI
from datetime import datetime
import json

app = Flask(__name__)

def format_date(date_string):
    """Format date string to readable format"""
    if not date_string:
        return "N/A"
    try:
        date = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return date.strftime("%m/%d/%Y %I:%M:%S %p")
    except:
        return date_string

def get_cf_fields_count(ticket):
    """Count CF fields that have values"""
    cf_fields = ticket.get('cf', {})
    if not cf_fields:
        return 0
    return len([v for v in cf_fields.values() if v is not None and v != 'N/A' and v != ''])

def get_custom_fields_count(ticket):
    """Count custom fields that have values"""
    custom_fields = ticket.get('customFields', {})
    if not custom_fields:
        return 0
    return len([v for v in custom_fields.values() if v is not None and v != 'N/A' and v != ''])

def calculate_processing_time(threads):
    """Calculate processing time between last outgoing and incoming threads"""
    if not threads or len(threads) < 2:
        return None
    
    # Separate outgoing and incoming threads
    outgoing_threads = [t for t in threads if t.get('direction') == 'out']
    incoming_threads = [t for t in threads if t.get('direction') == 'in']
    
    if not outgoing_threads or not incoming_threads:
        return None
    
    # Sort by created time
    outgoing_threads.sort(key=lambda x: x.get('createdTime', ''))
    incoming_threads.sort(key=lambda x: x.get('createdTime', ''))
    
    # Get last outgoing and last incoming
    last_outgoing = outgoing_threads[-1]
    last_incoming = incoming_threads[-1]
    
    # Check if last incoming is after last outgoing
    try:
        outgoing_time = datetime.fromisoformat(last_outgoing.get('createdTime', '').replace('Z', '+00:00'))
        incoming_time = datetime.fromisoformat(last_incoming.get('createdTime', '').replace('Z', '+00:00'))
        
        if incoming_time > outgoing_time:
            # Calculate difference
            time_diff = incoming_time - outgoing_time
            
            # Format the difference
            days = time_diff.days
            hours, remainder = divmod(time_diff.seconds, 3600)
            minutes, seconds = divmod(remainder, 60)
            
            if days > 0:
                return f"{days}d {hours}h {minutes}m"
            elif hours > 0:
                return f"{hours}h {minutes}m"
            else:
                return f"{minutes}m {seconds}s"
        else:
            return "No response yet"
    except:
        return "Unable to calculate"
    
    return None

@app.route('/')
def index():
    """Main page"""
    return render_template('ticket_viewer.html')

@app.route('/api/tickets')
def get_tickets():
    """Get tickets with optional filtering by cf_closed_by"""
    try:
        zoho = ZohoAPI()
        
        # Get access token with retry logic
        token = None
        for attempt in range(3):
            token = zoho.get_access_token()
            if token:
                break
            print(f"Access token attempt {attempt + 1} failed, retrying...")
            import time
            time.sleep(1)
        
        if not token:
            return jsonify({'error': 'Failed to get access token after 3 attempts'}), 500
        
        # Get filter parameters from query string
        cf_closed_by_filter = request.args.get('cf_closed_by', '').strip()
        status_filter = request.args.get('status', '').strip()
        limit = int(request.args.get('limit', 20))
        
        # Get tickets
        tickets_response = zoho.get_tickets(limit=limit)
        if not tickets_response or 'data' not in tickets_response:
            return jsonify({'error': 'Failed to get tickets'}), 500
        
        tickets = tickets_response['data']
        
        # Apply filters
        filtered_tickets = []
        for ticket in tickets:
            # Filter by cf_closed_by if specified
            if cf_closed_by_filter:
                ticket_cf_closed_by = ticket.get('cf', {}).get('cf_closed_by', '')
                if cf_closed_by_filter.lower() not in ticket_cf_closed_by.lower():
                    continue
            
            # Filter by status if specified
            if status_filter:
                ticket_status = ticket.get('status', '')
                if status_filter.lower() != ticket_status.lower():
                    continue
            
            filtered_tickets.append(ticket)
        
        # Format tickets for summary view
        formatted_tickets = []
        for ticket in filtered_tickets:
            formatted_ticket = {
                'id': ticket.get('id'),
                'ticketNumber': ticket.get('ticketNumber'),
                'subject': ticket.get('subject', 'No Subject'),
                'status': ticket.get('status', 'Unknown'),
                'createdTime': format_date(ticket.get('createdTime')),
                'closedTime': format_date(ticket.get('closedTime')),
                'email': ticket.get('email', 'N/A'),
                'cf_fields_count': get_cf_fields_count(ticket),
                'custom_fields_count': get_custom_fields_count(ticket),
                'threadCount': ticket.get('threadCount', 0),
                'channel': ticket.get('channel', 'Unknown'),
                'cf_closed_by': ticket.get('cf', {}).get('cf_closed_by', 'N/A')
            }
            formatted_tickets.append(formatted_ticket)
        
        return jsonify({
            'success': True,
            'tickets': formatted_tickets,
            'count': len(formatted_tickets),
            'total_fetched': len(tickets),
            'filters_applied': {
                'cf_closed_by': cf_closed_by_filter,
                'status': status_filter,
                'limit': limit
            }
        })
    
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>')
def get_ticket_details(ticket_id):
    """Get detailed information for a specific ticket"""
    try:
        zoho = ZohoAPI()
        
        # Get access token with retry logic
        token = None
        for attempt in range(3):
            token = zoho.get_access_token()
            if token:
                break
            print(f"Access token attempt {attempt + 1} failed, retrying...")
            import time
            time.sleep(1)
        
        if not token:
            return jsonify({'error': 'Failed to get access token after 3 attempts'}), 500
        
        # Get ticket details
        ticket_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                                          params={'orgId': zoho.config.ORG_ID})
        
        if not ticket_response:
            return jsonify({'error': 'Ticket not found'}), 404
        
        ticket = ticket_response
        
        # Get threads for this ticket
        threads_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads", 
                                           params={'orgId': zoho.config.ORG_ID})
        
        threads = []
        last_thread_body = ""
        
        if threads_response and 'data' in threads_response:
            threads = threads_response['data']
            if threads:
                # Get the last thread body
                last_thread = threads[-1]
                last_thread_body = last_thread.get('content') or last_thread.get('summary') or last_thread.get('body') or "No content available"
        
        # Calculate processing time
        processing_time = calculate_processing_time(threads)
        
        # Format detailed ticket information
        detailed_ticket = {
            'id': ticket.get('id'),
            'ticketNumber': ticket.get('ticketNumber'),
            'subject': ticket.get('subject', 'No Subject'),
            'status': ticket.get('status', 'Unknown'),
            'createdTime': format_date(ticket.get('createdTime')),
            'closedTime': format_date(ticket.get('closedTime')),
            'email': ticket.get('email', 'N/A'),
            'category': ticket.get('category') or ticket.get('subCategory') or 'No Category Set',
            'priority': ticket.get('priority', 'Not Set'),
            'phone': ticket.get('phone', 'N/A'),
            'assignee': 'Assigned' if ticket.get('assigneeId') else 'Not Assigned',
            'department': ticket.get('departmentId', 'N/A'),
            'channel': ticket.get('channel', 'Unknown'),
            'threadCount': ticket.get('threadCount', 0),
            'commentCount': ticket.get('commentCount', 0),
            'layoutId': ticket.get('layoutId', 'N/A'),
            'contactId': ticket.get('contactId', 'N/A'),
            'relationship': ticket.get('relationshipType', 'None'),
            'language': ticket.get('language', 'Unknown'),
            'statusType': ticket.get('statusType', 'Unknown'),
            'isSpam': ticket.get('isSpam', False),
            'isArchived': ticket.get('isArchived', False),
            'onholdTime': ticket.get('onholdTime', 'Not On Hold'),
            'taskCount': ticket.get('taskCount', 0),
            'attachmentCount': ticket.get('attachmentCount', 0),
            'followerCount': ticket.get('followerCount', 0),
            'classification': ticket.get('classification', 'None'),
            'resolution': ticket.get('resolution', 'No Resolution'),
            'createdBy': ticket.get('createdBy', 'Unknown'),
            'modifiedBy': ticket.get('modifiedBy', 'Unknown'),
            'cf_closed_by': ticket.get('cf', {}).get('cf_closed_by', 'N/A'),
            'cf_fields': ticket.get('cf', {}),
            'custom_fields': ticket.get('customFields', {}),
            'cf_fields_count': get_cf_fields_count(ticket),
            'custom_fields_count': get_custom_fields_count(ticket),
            'processing_time': processing_time,
            'last_thread_body': last_thread_body,
            'threads': threads
        }
        
        return jsonify({
            'success': True,
            'ticket': detailed_ticket
        })
    
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/filters/cf_closed_by')
def get_cf_closed_by_options():
    """Get all unique cf_closed_by values for filtering"""
    try:
        zoho = ZohoAPI()
        
        # Get access token with retry logic
        token = None
        for attempt in range(3):
            token = zoho.get_access_token()
            if token:
                break
            print(f"Access token attempt {attempt + 1} failed, retrying...")
            import time
            time.sleep(1)
        
        if not token:
            return jsonify({'error': 'Failed to get access token after 3 attempts'}), 500
        
        # Get a larger sample of tickets to find unique cf_closed_by values
        tickets_response = zoho.get_tickets(limit=200)
        if not tickets_response or 'data' not in tickets_response:
            return jsonify({'error': 'Failed to get tickets'}), 500
        
        tickets = tickets_response['data']
        
        # Extract unique cf_closed_by values
        cf_closed_by_values = set()
        for ticket in tickets:
            cf_closed_by = ticket.get('cf', {}).get('cf_closed_by', '')
            if cf_closed_by and cf_closed_by.strip():
                cf_closed_by_values.add(cf_closed_by.strip())
        
        # Convert to sorted list
        cf_closed_by_list = sorted(list(cf_closed_by_values))
        
        return jsonify({
            'success': True,
            'cf_closed_by_options': cf_closed_by_list,
            'count': len(cf_closed_by_list)
        })
    
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
