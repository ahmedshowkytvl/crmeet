#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Dark Theme Ticket Viewer - Black Background with Auto Close Filter
"""

from flask import Flask, render_template, jsonify, request
from zoho_api import ZohoAPI
from datetime import datetime
import json
import traceback

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

# Cache for department and user names to avoid repeated API calls
_department_cache = {}
_user_cache = {}

def get_department_name(zoho, department_id):
    """Get department name by ID with caching"""
    if not department_id:
        return "N/A"
    
    # Check cache first
    if department_id in _department_cache:
        return _department_cache[department_id]
    
    # Create a mapping for common departments based on your data
    department_mapping = {
        "766285000006092035": "General Department",
        "766285000016070029": "Contracting - KSA", 
        "766285000017737029": "Support Department",
        "766285000021972052": "Sales Department",
        "766285000151839183": "Technical Department",
        "766285000000006907": "Default Department",
        "766285000151832843": "Customer Service"
    }
    
    name = department_mapping.get(department_id, f"Department {department_id}")
    
    # Cache the result
    _department_cache[department_id] = name
    
    return name

def get_user_name(zoho, user_id):
    """Get user name by ID with caching"""
    if not user_id:
        return "Unknown"
    
    # Check cache first
    if user_id in _user_cache:
        return _user_cache[user_id]
    
    # Create a mapping for common users based on your data
    user_mapping = {
        "766285000000372105": "System Admin",
        "766285000000139001": "Support Agent",
        "766285000000139002": "Sales Agent"
    }
    
    name = user_mapping.get(user_id, f"User {user_id}")
    
    # Cache the result
    _user_cache[user_id] = name
    
    return name

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
    """Main page with dark theme"""
    return render_template('ticket_viewer_dark.html')

@app.route('/api/tickets')
def get_tickets():
    """Get tickets - ALWAYS EXCLUDE Auto Close tickets"""
    try:
        print("DARK VIEWER: Getting tickets (excluding Auto Close)...")
        zoho = ZohoAPI()
        
        # Get access token with retry logic
        token = None
        for attempt in range(3):
            try:
                token = zoho.get_access_token()
                if token:
                    print(f"DARK VIEWER: Access token obtained on attempt {attempt + 1}")
                    break
                else:
                    print(f"DARK VIEWER: Access token attempt {attempt + 1} failed")
            except Exception as e:
                print(f"DARK VIEWER: Access token attempt {attempt + 1} error: {e}")
            
            if attempt < 2:
                print("DARK VIEWER: Waiting before retry...")
                import time
                time.sleep(1)
        
        if not token:
            print("DARK VIEWER: Failed to get access token after 3 attempts")
            return jsonify({'error': 'Failed to get access token after 3 attempts'}), 500
        
        print("DARK VIEWER: Getting tickets from Zoho...")
        # Get more tickets to account for filtering
        tickets_response = zoho.get_tickets(limit=100)
        if not tickets_response or 'data' not in tickets_response:
            print("DARK VIEWER: No tickets data received")
            return jsonify({'error': 'Failed to get tickets'}), 500
        
        all_tickets = tickets_response['data']
        print(f"DARK VIEWER: Received {len(all_tickets)} tickets")
        
        # ALWAYS filter out Auto Close tickets - NO EXCEPTIONS
        tickets = [
            ticket for ticket in all_tickets
            if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
        ]
        print(f"DARK VIEWER: After filtering Auto Close: {len(tickets)} tickets (excluded {len(all_tickets) - len(tickets)})")
        
        # Limit to 20 tickets for display
        tickets = tickets[:20]
        print(f"DARK VIEWER: Limiting to {len(tickets)} tickets for display")
        
        # Format tickets for summary view
        formatted_tickets = []
        for i, ticket in enumerate(tickets):
            try:
                formatted_ticket = {
                    'id': ticket.get('id'),
                    'ticketNumber': ticket.get('ticketNumber'),
                    'subject': ticket.get('subject', 'No Subject'),
                    'status': ticket.get('status', 'Unknown'),
                    'createdTime': format_date(ticket.get('createdTime')),
                    'closedTime': format_date(ticket.get('closedTime')),
                    'email': ticket.get('email', 'N/A'),
                    'department': get_department_name(zoho, ticket.get('departmentId')),
                    'cf_fields_count': get_cf_fields_count(ticket),
                    'custom_fields_count': get_custom_fields_count(ticket),
                    'threadCount': ticket.get('threadCount', 0),
                    'channel': ticket.get('channel', 'Unknown'),
                    'cf_closed_by': ticket.get('cf', {}).get('cf_closed_by', 'N/A')  # Show for verification
                }
                formatted_tickets.append(formatted_ticket)
                
                # Add small delay every 5 tickets to avoid rate limiting
                if i > 0 and i % 5 == 0:
                    import time
                    time.sleep(0.1)
                    
            except Exception as e:
                print(f"DARK VIEWER: Error formatting ticket {ticket.get('id', 'unknown')}: {e}")
                continue
        
        print(f"DARK VIEWER: Returning {len(formatted_tickets)} formatted tickets (NO Auto Close)")
        return jsonify({
            'success': True,
            'tickets': formatted_tickets,
            'count': len(formatted_tickets),
            'exclude_auto_close': True,  # Always true for dark viewer
            'total_before_filter': len(all_tickets),
            'auto_close_excluded': len(all_tickets) - len(tickets)
        })
    
    except Exception as e:
        print(f"DARK VIEWER: Error in get_tickets: {e}")
        traceback.print_exc()
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>')
def get_ticket_details(ticket_id):
    """Get detailed information for a specific ticket"""
    try:
        print(f"DARK VIEWER: Getting details for ticket {ticket_id}")
        zoho = ZohoAPI()
        
        # Get access token with retry logic
        token = None
        for attempt in range(3):
            try:
                token = zoho.get_access_token()
                if token:
                    print(f"DARK VIEWER: Access token obtained on attempt {attempt + 1}")
                    break
                else:
                    print(f"DARK VIEWER: Access token attempt {attempt + 1} failed")
            except Exception as e:
                print(f"DARK VIEWER: Access token attempt {attempt + 1} error: {e}")
            
            if attempt < 2:
                print("DARK VIEWER: Waiting before retry...")
                import time
                time.sleep(1)
        
        if not token:
            print("DARK VIEWER: Failed to get access token after 3 attempts")
            return jsonify({'error': 'Failed to get access token after 3 attempts'}), 500
        
        print(f"DARK VIEWER: Getting ticket details from Zoho...")
        # Get ticket details
        ticket_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                                          params={'orgId': zoho.config.ORG_ID})
        
        if not ticket_response:
            print(f"DARK VIEWER: Ticket {ticket_id} not found")
            return jsonify({'error': 'Ticket not found'}), 404
        
        # Check if this ticket is Auto Close - if so, don't show it
        cf_closed_by = ticket_response.get('cf', {}).get('cf_closed_by')
        if cf_closed_by == 'Auto Close':
            print(f"DARK VIEWER: Ticket {ticket_id} is Auto Close - BLOCKING access")
            return jsonify({'error': 'This ticket is not available (Auto Close)'}), 403
        
        print(f"DARK VIEWER: Getting threads for ticket {ticket_id}")
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
        
        print(f"DARK VIEWER: Formatting ticket details...")
        # Format detailed ticket information
        detailed_ticket = {
            'id': ticket_response.get('id'),
            'ticketNumber': ticket_response.get('ticketNumber'),
            'subject': ticket_response.get('subject', 'No Subject'),
            'status': ticket_response.get('status', 'Unknown'),
            'createdTime': format_date(ticket_response.get('createdTime')),
            'closedTime': format_date(ticket_response.get('closedTime')),
            'email': ticket_response.get('email', 'N/A'),
            'category': ticket_response.get('category') or ticket_response.get('subCategory') or 'No Category Set',
            'priority': ticket_response.get('priority', 'Not Set'),
            'phone': ticket_response.get('phone', 'N/A'),
            'assignee': 'Assigned' if ticket_response.get('assigneeId') else 'Not Assigned',
            'department': get_department_name(zoho, ticket_response.get('departmentId')),
            'department_id': ticket_response.get('departmentId', 'N/A'),
            'channel': ticket_response.get('channel', 'Unknown'),
            'threadCount': ticket_response.get('threadCount', 0),
            'commentCount': ticket_response.get('commentCount', 0),
            'layoutId': ticket_response.get('layoutId', 'N/A'),
            'contactId': ticket_response.get('contactId', 'N/A'),
            'relationship': ticket_response.get('relationshipType', 'None'),
            'language': ticket_response.get('language', 'Unknown'),
            'statusType': ticket_response.get('statusType', 'Unknown'),
            'isSpam': ticket_response.get('isSpam', False),
            'isArchived': ticket_response.get('isArchived', False),
            'onholdTime': ticket_response.get('onholdTime', 'Not On Hold'),
            'taskCount': ticket_response.get('taskCount', 0),
            'attachmentCount': ticket_response.get('attachmentCount', 0),
            'followerCount': ticket_response.get('followerCount', 0),
            'classification': ticket_response.get('classification', 'None'),
            'resolution': ticket_response.get('resolution', 'No Resolution'),
            'createdBy': ticket_response.get('createdBy', 'Unknown'),
            'modifiedBy': get_user_name(zoho, ticket_response.get('modifiedBy')),
            'modifiedBy_id': ticket_response.get('modifiedBy', 'Unknown'),
            'cf_closed_by': cf_closed_by,
            'cf_fields': ticket_response.get('cf', {}),
            'custom_fields': ticket_response.get('customFields', {}),
            'cf_fields_count': get_cf_fields_count(ticket_response),
            'custom_fields_count': get_custom_fields_count(ticket_response),
            'processing_time': processing_time,
            'last_thread_body': last_thread_body,
            'threads': threads
        }
        
        print(f"DARK VIEWER: Successfully formatted ticket details")
        return jsonify({
            'success': True,
            'ticket': detailed_ticket
        })
    
    except Exception as e:
        print(f"DARK VIEWER: Error in get_ticket_details: {e}")
        traceback.print_exc()
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/tickets/stats')
def get_tickets_stats():
    """Get statistics about tickets including Auto Close count"""
    try:
        print("DARK VIEWER: Getting ticket statistics...")
        zoho = ZohoAPI()
        
        # Get access token
        token = zoho.get_access_token()
        if not token:
            return jsonify({'error': 'Failed to get access token'}), 500
        
        # Get all tickets
        tickets_response = zoho.get_tickets(limit=100)
        if not tickets_response or 'data' not in tickets_response:
            return jsonify({'error': 'Failed to get tickets'}), 500
        
        all_tickets = tickets_response['data']
        
        # Calculate statistics
        total_tickets = len(all_tickets)
        auto_close_tickets = [t for t in all_tickets if t.get('cf', {}).get('cf_closed_by') == 'Auto Close']
        manual_close_tickets = [t for t in all_tickets if t.get('status') == 'Closed' and t.get('cf', {}).get('cf_closed_by') != 'Auto Close']
        open_tickets = [t for t in all_tickets if t.get('status') == 'Open']
        
        stats = {
            'total_tickets': total_tickets,
            'auto_close_count': len(auto_close_tickets),
            'manual_close_count': len(manual_close_tickets),
            'open_count': len(open_tickets),
            'excluding_auto_close': total_tickets - len(auto_close_tickets),
            'dark_viewer_note': 'Auto Close tickets are NEVER shown in this viewer'
        }
        
        print(f"DARK VIEWER: Stats - Total: {total_tickets}, Auto Close: {len(auto_close_tickets)}, Excluding: {stats['excluding_auto_close']}")
        return jsonify({
            'success': True,
            'stats': stats
        })
    
    except Exception as e:
        print(f"DARK VIEWER: Error in get_tickets_stats: {e}")
        traceback.print_exc()
        return jsonify({'error': f'Server error: {str(e)}'}), 500

if __name__ == '__main__':
    print("="*70)
    print("  STARTING DARK TICKET VIEWER (NO AUTO CLOSE)")
    print("="*70)
    print("Features:")
    print("  - Black/Dark Theme")
    print("  - NEVER shows Auto Close tickets")
    print("  - Running on http://localhost:5001")
    print("="*70)
    app.run(debug=True, host='0.0.0.0', port=5001)

