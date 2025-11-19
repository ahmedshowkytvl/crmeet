#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple debug server to test API endpoints
"""

from flask import Flask, jsonify
from zoho_api import ZohoAPI
import traceback

app = Flask(__name__)

@app.route('/')
def index():
    return "Debug Server Running - Test endpoints at /test"

@app.route('/test')
def test_api():
    """Test API connection"""
    try:
        print("Testing API connection...")
        zoho = ZohoAPI()
        token = zoho.get_access_token()
        
        if not token:
            return jsonify({'error': 'Failed to get access token'}), 500
        
        print("Token obtained, getting tickets...")
        tickets_response = zoho.get_tickets(limit=1)
        
        if not tickets_response:
            return jsonify({'error': 'No tickets response'}), 500
        
        return jsonify({
            'success': True,
            'token_preview': token[:20] + '...',
            'tickets_count': len(tickets_response.get('data', [])),
            'first_ticket': tickets_response.get('data', [{}])[0] if tickets_response.get('data') else None
        })
        
    except Exception as e:
        print(f"Error: {e}")
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/test-ticket/<ticket_id>')
def test_ticket_details(ticket_id):
    """Test ticket details"""
    try:
        print(f"Testing ticket details for: {ticket_id}")
        zoho = ZohoAPI()
        token = zoho.get_access_token()
        
        if not token:
            return jsonify({'error': 'Failed to get access token'}), 500
        
        # Get ticket details
        ticket_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                                          params={'orgId': zoho.config.ORG_ID})
        
        if not ticket_response:
            return jsonify({'error': 'Ticket not found'}), 404
        
        return jsonify({
            'success': True,
            'ticket': ticket_response
        })
        
    except Exception as e:
        print(f"Error: {e}")
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting Debug Server...")
    print("Test endpoints:")
    print("  http://localhost:5002/test")
    print("  http://localhost:5002/test-ticket/766285000467900294")
    app.run(debug=True, host='0.0.0.0', port=5002)
