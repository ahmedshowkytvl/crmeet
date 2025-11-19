#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Demo Ticket Threads Viewer - Web Application
عارض التذاكر والخيوط التجريبي - تطبيق ويب
"""

from flask import Flask, render_template, request, jsonify, redirect, url_for
import json
from datetime import datetime

app = Flask(__name__)

# Demo data
DEMO_TICKETS = [
    {
        'id': '123456789',
        'ticketNumber': 'TKT-001',
        'subject': 'مشكلة في النظام',
        'status': 'Open',
        'priority': 'High',
        'createdTime': '2024-01-15T10:30:00Z',
        'contact': {'name': 'أحمد محمد', 'email': 'ahmed@example.com'},
        'department': {'name': 'الدعم الفني'}
    },
    {
        'id': '123456790',
        'ticketNumber': 'TKT-002',
        'subject': 'طلب ميزة جديدة',
        'status': 'Pending',
        'priority': 'Medium',
        'createdTime': '2024-01-14T14:20:00Z',
        'contact': {'name': 'فاطمة علي', 'email': 'fatima@example.com'},
        'department': {'name': 'التطوير'}
    },
    {
        'id': '123456791',
        'ticketNumber': 'TKT-003',
        'subject': 'استفسار عن الفواتير',
        'status': 'Closed',
        'priority': 'Low',
        'createdTime': '2024-01-13T09:15:00Z',
        'contact': {'name': 'محمد حسن', 'email': 'mohamed@example.com'},
        'department': {'name': 'المحاسبة'}
    }
]

DEMO_THREADS = {
    '123456789': [
        {
            'id': 'thread_001',
            'type': 'Email',
            'subject': 'مشكلة في النظام',
            'fromAddress': 'ahmed@example.com',
            'toAddress': 'support@company.com',
            'createdTime': '2024-01-15T10:30:00Z',
            'content': '<p>مرحباً،</p><p>أواجه مشكلة في النظام حيث لا يمكنني تسجيل الدخول. يرجى المساعدة.</p><p>شكراً،<br>أحمد</p>'
        },
        {
            'id': 'thread_002',
            'type': 'Reply',
            'subject': 'رد: مشكلة في النظام',
            'fromAddress': 'support@company.com',
            'toAddress': 'ahmed@example.com',
            'createdTime': '2024-01-15T11:00:00Z',
            'content': '<p>مرحباً أحمد،</p><p>شكراً لتواصلك معنا. سنقوم بفحص المشكلة وإصلاحها في أقرب وقت ممكن.</p><p>مع تحيات فريق الدعم</p>'
        }
    ],
    '123456790': [
        {
            'id': 'thread_003',
            'type': 'Email',
            'subject': 'طلب ميزة جديدة',
            'fromAddress': 'fatima@example.com',
            'toAddress': 'dev@company.com',
            'createdTime': '2024-01-14T14:20:00Z',
            'content': '<p>مرحباً،</p><p>أود طلب إضافة ميزة جديدة للنظام. هل يمكن إضافة إمكانية تصدير البيانات بصيغة PDF؟</p><p>شكراً،<br>فاطمة</p>'
        }
    ],
    '123456791': [
        {
            'id': 'thread_004',
            'type': 'Email',
            'subject': 'استفسار عن الفواتير',
            'fromAddress': 'mohamed@example.com',
            'toAddress': 'accounting@company.com',
            'createdTime': '2024-01-13T09:15:00Z',
            'content': '<p>مرحباً،</p><p>أريد الاستفسار عن الفاتورة رقم 12345. متى يمكنني الحصول عليها؟</p><p>شكراً،<br>محمد</p>'
        },
        {
            'id': 'thread_005',
            'type': 'Reply',
            'subject': 'رد: استفسار عن الفواتير',
            'fromAddress': 'accounting@company.com',
            'toAddress': 'mohamed@example.com',
            'createdTime': '2024-01-13T10:00:00Z',
            'content': '<p>مرحباً محمد،</p><p>تم إرسال الفاتورة رقم 12345 إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد.</p><p>مع تحيات فريق المحاسبة</p>'
        }
    ]
}

@app.route('/')
def index():
    """Main page - show tickets list"""
    return render_template('tickets_list.html', tickets=DEMO_TICKETS)

@app.route('/ticket/<ticket_id>')
def ticket_details(ticket_id):
    """Show ticket details and threads"""
    ticket = next((t for t in DEMO_TICKETS if t['id'] == ticket_id), None)
    threads = DEMO_THREADS.get(ticket_id, [])
    
    if not ticket:
        return render_template('error.html', message="التذكرة غير موجودة")
    
    return render_template('ticket_details.html', 
                         ticket=ticket, 
                         threads=threads)

@app.route('/thread/<ticket_id>/<thread_id>')
def thread_details(ticket_id, thread_id):
    """Show specific thread details with full email body"""
    threads = DEMO_THREADS.get(ticket_id, [])
    thread = next((t for t in threads if t['id'] == thread_id), None)
    
    if not thread:
        return render_template('error.html', message="الخيط غير موجود")
    
    return render_template('thread_details.html', 
                         thread=thread,
                         ticket_id=ticket_id)

@app.route('/api/thread/<ticket_id>/<thread_id>')
def api_thread_details(ticket_id, thread_id):
    """API endpoint to get thread details as JSON"""
    threads = DEMO_THREADS.get(ticket_id, [])
    thread = next((t for t in threads if t['id'] == thread_id), None)
    
    if not thread:
        return jsonify({'error': 'الخيط غير موجود'}), 404
    
    return jsonify(thread)

@app.route('/api/tickets')
def api_tickets():
    """API endpoint to get tickets list as JSON"""
    return jsonify(DEMO_TICKETS)

@app.route('/api/ticket/<ticket_id>/threads')
def api_ticket_threads(ticket_id):
    """API endpoint to get ticket threads as JSON"""
    threads = DEMO_THREADS.get(ticket_id, [])
    return jsonify(threads)

if __name__ == '__main__':
    print("="*60)
    print("  DEMO TICKET THREADS VIEWER - WEB APPLICATION")
    print("  عارض التذاكر والخيوط التجريبي - تطبيق ويب")
    print("="*60)
    print()
    print("SUCCESS: Demo application ready")
    print("Web server starting...")
    print("Open your browser and go to: http://localhost:5000")
    print()
    
    app.run(debug=True, host='0.0.0.0', port=5000)
