#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Get detailed information for a specific ticket
"""
import sys
import io
import json
from zoho_api import ZohoAPI
from datetime import datetime

# Fix encoding for Windows
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def format_date(date_string):
    """Format date string to readable format"""
    if not date_string:
        return "N/A"
    try:
        date = datetime.fromisoformat(date_string.replace('Z', '+00:00'))
        return date.strftime("%Y-%m-%d %H:%M:%S")
    except:
        return date_string

def get_ticket_details(ticket_id):
    """Get detailed information for a specific ticket"""
    print(f"\n{'='*80}")
    print(f"جلب تفاصيل التذكرة رقم: {ticket_id}")
    print(f"{'='*80}\n")
    
    try:
        zoho = ZohoAPI()
        
        # Get access token
        print("🔑 الحصول على رمز الوصول...")
        token = zoho.get_access_token()
        if not token:
            print("❌ فشل في الحصول على رمز الوصول")
            return None
        
        print("✅ تم الحصول على رمز الوصول بنجاح\n")
        
        # Get ticket details
        print(f"📋 جلب تفاصيل التذكرة...")
        ticket_url = f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}"
        ticket_response = zoho.make_request('GET', ticket_url, params={'orgId': zoho.config.ORG_ID})
        
        if not ticket_response:
            print("❌ لم يتم العثور على التذكرة")
            return None
        
        ticket = ticket_response
        print("✅ تم جلب تفاصيل التذكرة بنجاح\n")
        
        # Get threads for this ticket
        print("💬 جلب المحادثات...")
        threads_url = f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}/threads"
        threads_response = zoho.make_request('GET', threads_url, params={'orgId': zoho.config.ORG_ID})
        
        threads = []
        if threads_response and 'data' in threads_response:
            threads = threads_response['data']
            print(f"✅ تم جلب {len(threads)} محادثة\n")
        else:
            print("⚠️  لا توجد محادثات\n")
        
        # Display ticket information
        print(f"\n{'='*80}")
        print("📊 معلومات التذكرة")
        print(f"{'='*80}")
        print(f"🆔 رقم التذكرة: {ticket.get('ticketNumber', 'N/A')}")
        print(f"📝 العنوان: {ticket.get('subject', 'لا يوجد عنوان')}")
        print(f"📧 البريد الإلكتروني: {ticket.get('email', 'N/A')}")
        print(f"📞 الهاتف: {ticket.get('phone', 'N/A')}")
        print(f"🔖 الحالة: {ticket.get('status', 'Unknown')}")
        print(f"📅 تاريخ الإنشاء: {format_date(ticket.get('createdTime'))}")
        print(f"📅 تاريخ الإغلاق: {format_date(ticket.get('closedTime'))}")
        print(f"📂 القناة: {ticket.get('channel', 'Unknown')}")
        print(f"🏷️  الفئة: {ticket.get('category', 'N/A')} / {ticket.get('subCategory', 'N/A')}")
        print(f"⚡ الأولوية: {ticket.get('priority', 'Not Set')}")
        print(f"👤 المسؤول: {ticket.get('assigneeId', 'Not Assigned')}")
        print(f"🏢 القسم: {ticket.get('departmentId', 'N/A')}")
        print(f"💬 عدد المحادثات: {ticket.get('threadCount', 0)}")
        print(f"💬 عدد التعليقات: {ticket.get('commentCount', 0)}")
        print(f"📎 عدد المرفقات: {ticket.get('attachmentCount', 0)}")
        print(f"👥 عدد المتابعين: {ticket.get('followerCount', 0)}")
        print(f"📋 Layout ID: {ticket.get('layoutId', 'N/A')}")
        print(f"📞 Contact ID: {ticket.get('contactId', 'N/A')}")
        print(f"🔗 نوع العلاقة: {ticket.get('relationshipType', 'None')}")
        print(f"🌐 اللغة: {ticket.get('language', 'Unknown')}")
        print(f"🏷️  نوع الحالة: {ticket.get('statusType', 'Unknown')}")
        print(f"🚫 متطفل: {ticket.get('isSpam', False)}")
        print(f"📦 أرشيف: {ticket.get('isArchived', False)}")
        print(f"⏸️  وقت الانتظار: {ticket.get('onholdTime', 'Not On Hold')}")
        print(f"✅ عدد المهام: {ticket.get('taskCount', 0)}")
        print(f"🏷️  التصنيف: {ticket.get('classification', 'None')}")
        print(f"✅ الحل: {ticket.get('resolution', 'No Resolution')}")
        print(f"👤 تم الإنشاء بواسطة: {ticket.get('createdBy', 'Unknown')}")
        print(f"👤 تم التعديل بواسطة: {ticket.get('modifiedBy', 'Unknown')}")
        
        # Display CF fields
        print(f"\n{'='*80}")
        print("📋 حقول CF (Custom Fields)")
        print(f"{'='*80}")
        cf_fields = ticket.get('cf', {})
        if cf_fields:
            for key, value in cf_fields.items():
                if value and value != 'N/A':
                    print(f"  • {key}: {value}")
        else:
            print("لا توجد حقول CF")
        
        # Display custom fields
        print(f"\n{'='*80}")
        print("📋 الحقول المخصصة")
        print(f"{'='*80}")
        custom_fields = ticket.get('customFields', {})
        if custom_fields:
            for key, value in custom_fields.items():
                if value and value != 'N/A':
                    print(f"  • {key}: {value}")
        else:
            print("لا توجد حقول مخصصة")
        
        # Display threads
        if threads:
            print(f"\n{'='*80}")
            print("💬 المحادثات")
            print(f"{'='*80}")
            for i, thread in enumerate(threads, 1):
                print(f"\n--- محادثة {i} ---")
                print(f"🆔 Thread ID: {thread.get('id', 'N/A')}")
                print(f"📧 من: {thread.get('from', 'N/A')}")
                print(f"📧 إلى: {thread.get('to', 'N/A')}")
                print(f"📝 الرد على: {thread.get('cc', 'N/A')}")
                print(f"🔖 الاتجاه: {thread.get('direction', 'N/A')}")
                print(f"📅 التاريخ: {format_date(thread.get('createdTime'))}")
                print(f"📌 العداد: {thread.get('isRead', 'N/A')}")
                print(f"🔔 عام: {thread.get('isPublic', 'N/A')}")
                print(f"📎 مرفقات: {thread.get('attachmentCount', 0)}")
                print(f"🔗 ارتباطات: {thread.get('associationsCount', 0)}")
                
                # Get thread body
                content = thread.get('content') or thread.get('summary') or thread.get('body') or thread.get('comment', {}).get('content', '')
                if content:
                    print(f"📄 المحتوى:")
                    print(f"   {content[:200]}..." if len(content) > 200 else f"   {content}")
        
        # Save to file
        output_file = f"ticket_{ticket_id}_details.json"
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump({
                'ticket': ticket,
                'threads': threads
            }, f, ensure_ascii=False, indent=2)
        
        print(f"\n{'='*80}")
        print(f"✅ تم حفظ التفاصيل الكاملة في الملف: {output_file}")
        print(f"{'='*80}\n")
        
        return ticket
        
    except Exception as e:
        print(f"\n❌ حدث خطأ: {str(e)}")
        import traceback
        traceback.print_exc()
        return None

if __name__ == '__main__':
    # Ticket ID
    ticket_id = "766285000471452490"
    get_ticket_details(ticket_id)

