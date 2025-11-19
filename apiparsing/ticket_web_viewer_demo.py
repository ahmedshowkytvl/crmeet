#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Demo version of ticket viewer with mock data (for testing when API is not available)
"""

from flask import Flask, render_template, jsonify
from datetime import datetime, timedelta
import random

app = Flask(__name__)

def generate_mock_tickets():
    """Generate mock ticket data for demo"""
    tickets = []
    statuses = ['Open', 'Closed', 'In Progress']
    channels = ['Email', 'Phone', 'Chat']
    categories = ['Technical', 'Billing', 'General', 'Sales']
    priorities = ['Low', 'Medium', 'High', 'Urgent']
    
    for i in range(20):
        ticket_id = f"2834{800 + i}"
        created_time = datetime.now() - timedelta(hours=random.randint(1, 72))
        status = random.choice(statuses)
        
        ticket = {
            'id': f"mock_{i}",
            'ticketNumber': ticket_id,
            'subject': f"Mock Ticket Subject {i} - Sample Issue Description",
            'status': status,
            'createdTime': created_time.strftime("%m/%d/%Y %I:%M:%S %p"),
            'closedTime': (created_time + timedelta(hours=random.randint(1, 24))).strftime("%m/%d/%Y %I:%M:%S %p") if status == 'Closed' else 'N/A',
            'email': f"user{i}@example.com",
            'cf_fields_count': random.randint(0, 3),
            'custom_fields_count': random.randint(0, 2),
            'threadCount': random.randint(1, 5),
            'channel': random.choice(channels)
        }
        tickets.append(ticket)
    
    return tickets

def generate_mock_ticket_details(ticket_id):
    """Generate mock detailed ticket data"""
    ticket_number = f"2834{800 + int(ticket_id.split('_')[1])}"
    
    # Generate mock processing time
    processing_times = ["2h 15m", "1d 3h 45m", "4h 30m", "6m 15s", "1d 12h", None]
    processing_time = random.choice(processing_times)
    
    return {
        'id': ticket_id,
        'ticketNumber': ticket_number,
        'subject': f'Mock Ticket Subject - Detailed View for {ticket_number}',
        'status': random.choice(['Open', 'Closed', 'In Progress']),
        'createdTime': (datetime.now() - timedelta(hours=random.randint(1, 72))).strftime("%m/%d/%Y %I:%M:%S %p"),
        'closedTime': (datetime.now() - timedelta(hours=random.randint(1, 24))).strftime("%m/%d/%Y %I:%M:%S %p"),
        'email': f"customer@example.com",
        'category': random.choice(['Technical', 'Billing', 'General', 'Sales', 'No Category Set']),
        'priority': random.choice(['Low', 'Medium', 'High', 'Urgent', 'Not Set']),
        'phone': random.choice(['+1234567890', 'N/A', '+9876543210']),
        'assignee': random.choice(['Assigned', 'Not Assigned']),
        'department': f"ID: 766285000{random.randint(100000, 999999)}",
        'channel': random.choice(['Email', 'Phone', 'Chat']),
        'threadCount': random.randint(2, 8),
        'commentCount': random.randint(0, 5),
        'layoutId': f"ID: 766285000{random.randint(100000, 999999)}",
        'contactId': f"ID: 766285000{random.randint(100000, 999999)}",
        'relationship': random.choice(['None', 'Customer', 'Partner']),
        'language': random.choice(['English', 'Arabic', 'Spanish']),
        'statusType': random.choice(['Open', 'Closed', 'In Progress']),
        'isSpam': False,
        'isArchived': False,
        'onholdTime': random.choice(['Not On Hold', 'On Hold']),
        'taskCount': random.randint(0, 3),
        'attachmentCount': random.randint(0, 5),
        'followerCount': random.randint(0, 2),
        'classification': random.choice(['None', 'Technical', 'Billing']),
        'resolution': random.choice(['No Resolution', 'Resolved', 'Escalated']),
        'createdBy': random.choice(['Unknown', 'System', 'Admin']),
        'modifiedBy': random.choice(['Unknown', 'System', 'Admin']),
        'cf_closed_by': random.choice(['N/A', 'Admin User', 'System Auto']),
        'cf_fields': {
            'cf_closed_by': random.choice(['N/A', 'Admin User', 'System Auto']),
            'cf_priority': random.choice(['Normal', 'High', 'Low']),
            'cf_category': random.choice(['Support', 'Sales', 'Technical'])
        },
        'custom_fields': {
            'Custom Field 1': f'Value {random.randint(1, 100)}',
            'Custom Field 2': f'Text {random.randint(1, 50)}'
        },
        'cf_fields_count': random.randint(1, 3),
        'custom_fields_count': random.randint(1, 2),
        'processing_time': processing_time,
        'last_thread_body': f"This is a mock thread body for ticket {ticket_number}. It contains sample content that would normally come from the actual ticket thread. The content includes details about the customer's issue and any responses from the support team.",
        'threads': [
            {'direction': 'in', 'createdTime': '2025-10-07T10:00:00Z'},
            {'direction': 'out', 'createdTime': '2025-10-07T11:30:00Z'},
            {'direction': 'in', 'createdTime': '2025-10-07T14:15:00Z'}
        ]
    }

@app.route('/')
def index():
    """Main page"""
    return render_template('ticket_viewer.html')

@app.route('/api/tickets')
def get_tickets():
    """Get mock tickets"""
    try:
        tickets = generate_mock_tickets()
        return jsonify({
            'success': True,
            'tickets': tickets,
            'count': len(tickets)
        })
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>')
def get_ticket_details(ticket_id):
    """Get mock ticket details"""
    try:
        ticket = generate_mock_ticket_details(ticket_id)
        return jsonify({
            'success': True,
            'ticket': ticket
        })
    except Exception as e:
        return jsonify({'error': f'Server error: {str(e)}'}), 500

if __name__ == '__main__':
    print("Starting Demo Ticket Viewer...")
    print("This version uses mock data for testing")
    print("Open your browser and go to: http://localhost:5001")
    app.run(debug=True, host='0.0.0.0', port=5001)
