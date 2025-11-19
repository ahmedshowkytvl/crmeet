#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ticket Threads Viewer - Web Application
عرض التذاكر والخيوط - تطبيق ويب
"""

from flask import Flask, render_template, request, jsonify, redirect, url_for
import requests
import json
import time
from datetime import datetime
from config import ZohoConfig

app = Flask(__name__)

class TicketThreadsAPI:
    def __init__(self):
        self.config = ZohoConfig()
        self.access_token = None
        
    def get_access_token(self):
        """Get access token with retry logic"""
        print("Getting access token...")
        
        token_data = {
            'refresh_token': self.config.REFRESH_TOKEN,
            'client_id': self.config.CLIENT_ID,
            'client_secret': self.config.CLIENT_SECRET,
            'grant_type': 'refresh_token'
        }
        
        try:
            response = requests.post(self.config.TOKEN_URL, data=token_data)
            if response.status_code == 200:
                self.access_token = response.json()['access_token']
                print("✅ Access token obtained successfully")
                return True
            else:
                print(f"❌ Token failed: {response.status_code}")
                print(f"Response: {response.text[:200]}")
                print("\n⚠️  الحل: استخدم Desktop App الموجود في المجلد الرئيسي:")
                print("   python zoho_tickets_viewer.py")
                return False
        except Exception as e:
            print(f"❌ Token error: {e}")
            print("\n⚠️  الحل: استخدم Desktop App الموجود في المجلد الرئيسي:")
            print("   python zoho_tickets_viewer.py")
            return False
    
    def get_headers(self):
        """Get request headers"""
        return {
            "Authorization": f"Zoho-oauthtoken {self.access_token}",
            "orgId": self.config.ORG_ID,
            "contentType": "application/json; charset=utf-8"
        }
    
    def get_tickets_list(self, limit=50):
        """Get tickets list"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets"
        params = {
            'orgId': self.config.ORG_ID,
            'limit': limit
        }
        
        try:
            response = requests.get(url, headers=self.get_headers(), params=params)
            if response.status_code == 200:
                return response.json().get('data', [])
            else:
                print(f"Error getting tickets: {response.status_code}")
                return None
        except Exception as e:
            print(f"Exception getting tickets: {e}")
            return None
    
    def get_ticket_details(self, ticket_id):
        """Get ticket details"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
        params = {'orgId': self.config.ORG_ID}
        
        try:
            response = requests.get(url, headers=self.get_headers(), params=params)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error getting ticket details: {response.status_code}")
                return None
        except Exception as e:
            print(f"Exception getting ticket details: {e}")
            return None
    
    def get_ticket_threads(self, ticket_id):
        """Get ticket threads"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
        params = {'orgId': self.config.ORG_ID}
        
        try:
            response = requests.get(url, headers=self.get_headers(), params=params)
            if response.status_code == 200:
                return response.json().get('data', [])
            else:
                print(f"Error getting threads: {response.status_code}")
                return None
        except Exception as e:
            print(f"Exception getting threads: {e}")
            return None
    
    def get_thread_details(self, ticket_id, thread_id):
        """Get specific thread details with full email body"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads/{thread_id}"
        params = {'orgId': self.config.ORG_ID}
        
        try:
            response = requests.get(url, headers=self.get_headers(), params=params)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error getting thread details: {response.status_code}")
                return None
        except Exception as e:
            print(f"Exception getting thread details: {e}")
            return None
    
    def update_ticket_status(self, ticket_id, status, comment=None):
        """Update ticket status (Open, Pending, Closed, etc.)"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}"
        params = {'orgId': self.config.ORG_ID}
        
        # Prepare update data - Zoho Desk expects specific format
        update_data = {
            "status": status
        }
        
        # Add comment if provided - comments are added separately via threads
        if comment:
            # First add the comment as a thread
            comment_result = self.add_ticket_comment(ticket_id, comment)
            if not comment_result:
                print("Warning: Failed to add comment, but continuing with status update")
        
        try:
            # Use PUT instead of PATCH for Zoho Desk API
            response = requests.put(url, headers=self.get_headers(), 
                                  params=params, json=update_data)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error updating ticket status: {response.status_code}")
                print(f"Response: {response.text}")
                return None
        except Exception as e:
            print(f"Exception updating ticket status: {e}")
            return None
    
    def close_ticket(self, ticket_id, comment=None):
        """Close a ticket with optional comment"""
        return self.update_ticket_status(ticket_id, "Closed", comment)
    
    def reopen_ticket(self, ticket_id, comment=None):
        """Reopen a ticket with optional comment"""
        return self.update_ticket_status(ticket_id, "Open", comment)
    
    def add_ticket_comment(self, ticket_id, comment):
        """Add a comment to a ticket"""
        if not self.access_token:
            if not self.get_access_token():
                return None
        
        url = f"{self.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
        params = {'orgId': self.config.ORG_ID}
        
        # Prepare comment data
        comment_data = {
            "content": comment,
            "contentType": "plainText"
        }
        
        try:
            response = requests.post(url, headers=self.get_headers(), 
                                   params=params, json=comment_data)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error adding comment: {response.status_code}")
                print(f"Response: {response.text}")
                return None
        except Exception as e:
            print(f"Exception adding comment: {e}")
            return None

# Initialize API
api = TicketThreadsAPI()

@app.route('/')
def index():
    """Main page - show tickets list"""
    tickets = api.get_tickets_list(limit=50)
    if tickets is None:
        return render_template('error.html', message="فشل في جلب التذاكر")
    
    # Filter out Auto Close tickets
    filtered_tickets = [
        ticket for ticket in tickets
        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
    ]
    
    return render_template('tickets_list.html', tickets=filtered_tickets)

@app.route('/ticket/<ticket_id>')
def ticket_details(ticket_id):
    """Show ticket details and threads"""
    ticket_details = api.get_ticket_details(ticket_id)
    threads = api.get_ticket_threads(ticket_id)
    
    if ticket_details is None:
        return render_template('error.html', message="فشل في جلب تفاصيل التذكرة")
    
    return render_template('ticket_details.html', 
                         ticket=ticket_details, 
                         threads=threads or [])

@app.route('/thread/<ticket_id>/<thread_id>')
def thread_details(ticket_id, thread_id):
    """Show specific thread details with full email body"""
    thread_details = api.get_thread_details(ticket_id, thread_id)
    
    if thread_details is None:
        return render_template('error.html', message="فشل في جلب تفاصيل الخيط")
    
    return render_template('thread_details.html', 
                         thread=thread_details,
                         ticket_id=ticket_id)

@app.route('/api/thread/<ticket_id>/<thread_id>')
def api_thread_details(ticket_id, thread_id):
    """API endpoint to get thread details as JSON"""
    thread_details = api.get_thread_details(ticket_id, thread_id)
    
    if thread_details is None:
        return jsonify({'error': 'فشل في جلب تفاصيل الخيط'}), 500
    
    return jsonify(thread_details)

@app.route('/api/tickets')
def api_tickets():
    """API endpoint to get tickets list as JSON"""
    tickets = api.get_tickets_list(limit=50)
    if tickets is None:
        return jsonify({'error': 'فشل في جلب التذاكر'}), 500
    
    # Filter out Auto Close tickets
    filtered_tickets = [
        ticket for ticket in tickets
        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
    ]
    
    return jsonify(filtered_tickets)

@app.route('/api/ticket/<ticket_id>/threads')
def api_ticket_threads(ticket_id):
    """API endpoint to get ticket threads as JSON"""
    threads = api.get_ticket_threads(ticket_id)
    if threads is None:
        return jsonify({'error': 'فشل في جلب الخيوط'}), 500
    
    return jsonify(threads)

@app.route('/api/ticket/<ticket_id>/close', methods=['POST'])
def api_close_ticket(ticket_id):
    """API endpoint to close a ticket"""
    try:
        data = request.get_json() or {}
        comment = data.get('comment', '')
        
        print(f"Closing ticket {ticket_id}")
        result = api.close_ticket(ticket_id, comment)
        
        if result is None:
            return jsonify({'error': 'فشل في إغلاق التذكرة - تحقق من صلاحيات API'}), 500
        
        return jsonify({'success': True, 'message': 'تم إغلاق التذكرة بنجاح', 'data': result})
    
    except Exception as e:
        print(f"Exception in api_close_ticket: {e}")
        return jsonify({'error': f'خطأ في الخادم: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>/reopen', methods=['POST'])
def api_reopen_ticket(ticket_id):
    """API endpoint to reopen a ticket"""
    try:
        data = request.get_json() or {}
        comment = data.get('comment', '')
        
        print(f"Reopening ticket {ticket_id}")
        result = api.reopen_ticket(ticket_id, comment)
        
        if result is None:
            return jsonify({'error': 'فشل في إعادة فتح التذكرة - تحقق من صلاحيات API'}), 500
        
        return jsonify({'success': True, 'message': 'تم إعادة فتح التذكرة بنجاح', 'data': result})
    
    except Exception as e:
        print(f"Exception in api_reopen_ticket: {e}")
        return jsonify({'error': f'خطأ في الخادم: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>/status', methods=['POST'])
def api_update_ticket_status(ticket_id):
    """API endpoint to update ticket status"""
    try:
        data = request.get_json() or {}
        status = data.get('status')
        comment = data.get('comment', '')
        
        if not status:
            return jsonify({'error': 'الحالة مطلوبة'}), 400
        
        print(f"Updating ticket {ticket_id} status to {status}")
        result = api.update_ticket_status(ticket_id, status, comment)
        
        if result is None:
            return jsonify({'error': 'فشل في تحديث حالة التذكرة - تحقق من صلاحيات API'}), 500
        
        return jsonify({'success': True, 'message': 'تم تحديث حالة التذكرة بنجاح', 'data': result})
    
    except Exception as e:
        print(f"Exception in api_update_ticket_status: {e}")
        return jsonify({'error': f'خطأ في الخادم: {str(e)}'}), 500

@app.route('/api/ticket/<ticket_id>/comment', methods=['POST'])
def api_add_ticket_comment(ticket_id):
    """API endpoint to add a comment to a ticket"""
    try:
        data = request.get_json() or {}
        comment = data.get('comment', '')
        
        if not comment:
            return jsonify({'error': 'التعليق مطلوب'}), 400
        
        print(f"Adding comment to ticket {ticket_id}")
        result = api.add_ticket_comment(ticket_id, comment)
        
        if result is None:
            return jsonify({'error': 'فشل في إضافة التعليق - تحقق من صلاحيات API'}), 500
        
        return jsonify({'success': True, 'message': 'تم إضافة التعليق بنجاح', 'data': result})
    
    except Exception as e:
        print(f"Exception in api_add_ticket_comment: {e}")
        return jsonify({'error': f'خطأ في الخادم: {str(e)}'}), 500

if __name__ == '__main__':
    print("="*60)
    print("  TICKET THREADS VIEWER - WEB APPLICATION")
    print("  عارض التذاكر والخيوط - تطبيق ويب")
    print("="*60)
    print()
    
    # Test API connection
    if api.get_access_token():
        print("✅ API connection successful")
        print("🌐 Starting web server...")
        print("📱 Open your browser and go to: http://localhost:5000")
        print()
        
        app.run(debug=True, host='0.0.0.0', port=5000)
    else:
        print("❌ Failed to connect to API. Please check your configuration.")
