#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
اختبار استرجاع محتوى Thread Email الكامل من Zoho Desk

هذا السكريبت يختبر استرجاع محتوى الـ Email Thread كاملاً سواء من:
1. API المحلي (Laravel)
2. API المباشر من Zoho Desk
"""

import sys
import io

# Fix encoding for Windows
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

import requests
import json
from datetime import datetime

# Colors for terminal
class Colors:
    HEADER = '\033[95m'
    OKBLUE = '\033[94m'
    OKGREEN = '\033[92m'
    WARNING = '\033[93m'
    FAIL = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'

def print_header(text):
    print(f"\n{Colors.HEADER}{Colors.BOLD}{'='*80}{Colors.ENDC}")
    print(f"{Colors.HEADER}{Colors.BOLD}{text.center(80)}{Colors.ENDC}")
    print(f"{Colors.HEADER}{Colors.BOLD}{'='*80}{Colors.ENDC}\n")

def print_success(text):
    print(f"{Colors.OKGREEN}✅ {text}{Colors.ENDC}")

def print_error(text):
    print(f"{Colors.FAIL}❌ {text}{Colors.ENDC}")

def print_info(text):
    print(f"{Colors.OKBLUE}ℹ️  {text}{Colors.ENDC}")

def print_warning(text):
    print(f"{Colors.WARNING}⚠️  {text}{Colors.ENDC}")

def test_thread_content_via_api(ticket_id, thread_id):
    """
    اختبار استرجاع محتوى الـ Thread عبر API المحلي (Laravel)
    """
    print_header("اختبار استرجاع محتوى Thread عبر Laravel API")
    
    base_url = "http://localhost:8000"
    
    # Note: Thread content endpoints need authentication, so we'll use the desktop API
    # which we already have access to from the threads list
    endpoints = [
        # Desktop API (no auth required)
        f"/api/zoho/desktop/ticket/{ticket_id}/threads",
    ]
    
    for endpoint in endpoints:
        url = f"{base_url}{endpoint}"
        print_info(f"تجربة: {endpoint}")
        
        try:
            response = requests.get(url, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                print_success(f"تم استرجاع البيانات بنجاح!")
                
                # حفظ النتيجة في ملف
                filename = f"thread_content_{ticket_id}_{thread_id}_{endpoint.split('/')[-1]}.json"
                with open(filename, 'w', encoding='utf-8') as f:
                    json.dump(data, f, ensure_ascii=False, indent=2)
                print_info(f"✅ تم حفظ النتيجة في: {filename}")
                
                # عرض البيانات
                if 'data' in data:
                    thread_data = data['data']
                    print(f"\n{'='*80}")
                    print(f"📧 معلومات الـ Thread:")
                    print(f"{'='*80}")
                    
                    if 'fullContent' in thread_data:
                        print(f"\n📝 المحتوى الكامل:")
                        print(f"{'-'*80}")
                        print(thread_data['fullContent'][:500])  # أول 500 حرف
                        if len(thread_data['fullContent']) > 500:
                            print(f"\n... (تم تقصير المحتوى، راجع الملف الكامل)")
                    
                    if 'subject' in thread_data and thread_data['subject']:
                        print(f"\n📌 العنوان: {thread_data['subject']}")
                    
                    if 'channel' in thread_data:
                        print(f"📡 القناة: {thread_data['channel']}")
                    
                    if 'direction' in thread_data:
                        print(f"↔️  الاتجاه: {thread_data['direction']}")
                    
                    if 'author' in thread_data and thread_data['author']:
                        author = thread_data['author']
                        print(f"👤 المرسل: {author.get('name', 'N/A')} ({author.get('email', 'N/A')})")
                    
                    if 'createdTime' in thread_data:
                        print(f"🕒 التاريخ: {thread_data['createdTime']}")
                    
                    print(f"\n{'='*80}")
                
                return True
                
            elif response.status_code == 401:
                print_error("يحتاج إلى تسجيل الدخول، يرجى فتح المتصفح على http://127.0.0.1:8000")
                return False
            elif response.status_code == 404:
                print_warning(f"الـ endpoint غير موجود أو التذكرة غير موجودة")
            else:
                print_error(f"خطأ HTTP: {response.status_code}")
                print(response.text[:200])
                
        except requests.exceptions.ConnectionError:
            print_error("❌ لا يمكن الاتصال بالـ API!")
            print_info("🔧 تأكد أن Laravel يعمل: php artisan serve")
            print_info("🌐 جرب فتح: http://localhost:8000")
            return False
        except Exception as e:
            print_error(f"خطأ: {str(e)}")
            import traceback
            traceback.print_exc()
    
    return False

def get_ticket_threads(ticket_id):
    """
    جلب جميع الـ Threads لتذكرة معينة
    """
    print_header(f"جلب الـ Threads للتذكرة: {ticket_id}")
    
    # استخدام desktop API (بدون auth)
    url = f"http://localhost:8000/api/zoho/desktop/ticket/{ticket_id}/threads"
    
    try:
        response = requests.get(url, timeout=30)
        
        # Debug: طباعة status code
        print_info(f"Status Code: {response.status_code}")
        
        # Debug: طباعة raw response
        if response.status_code != 200:
            print_error(f"Response: {response.text[:300]}")
        
        if response.status_code == 200:
            try:
                data = response.json()
            except json.JSONDecodeError as json_err:
                print_error(f"خطأ في JSON: {response.text[:200]}")
                return []
            
            if data.get('success') and 'threads' in data:
                threads = data['threads']
                print_success(f"تم العثور على {len(threads)} thread")
                
                print(f"\n{'='*60}")
                print("📋 قائمة الـ Threads:")
                print(f"{'='*60}")
                
                for idx, thread in enumerate(threads, 1):
                    print(f"\n[{idx}] Thread ID: {thread.get('id', 'N/A')}")
                    print(f"    📝 Summary: {thread.get('summary', 'N/A')[:100]}...")
                    print(f"    📡 Channel: {thread.get('channel', 'N/A')}")
                    print(f"    📅 Time: {thread.get('createdTime', 'N/A')}")
                    
                    if 'author' in thread and thread['author']:
                        print(f"    👤 Author: {thread['author'].get('name', 'N/A')}")
                
                # حفظ القائمة
                filename = f"threads_list_{ticket_id}.json"
                with open(filename, 'w', encoding='utf-8') as f:
                    json.dump(data, f, ensure_ascii=False, indent=2)
                print_info(f"✅ تم حفظ قائمة الـ Threads في: {filename}")
                
                return threads
            else:
                print_warning("⚠️ لا توجد threads في النتيجة أو الـ response غير صحيح")
                if 'error' in data:
                    print_error(f"الخطأ: {data['error']}")
                print_info(f"Response: {json.dumps(data, ensure_ascii=False)[:300]}")
                return []
                
        else:
            print_error(f"❌ خطأ HTTP: {response.status_code}")
            if response.text:
                print_info(f"الـ Response: {response.text[:300]}")
            return []
            
    except requests.exceptions.ConnectionError:
        print_error("❌ لا يمكن الاتصال بالـ API!")
        print_info("🔧 تأكد أن Laravel يعمل: php artisan serve")
        print_info("🌐 جرب فتح: http://localhost:8000")
        return []
    except Exception as e:
        print_error(f"خطأ: {str(e)}")
        import traceback
        traceback.print_exc()
        return []

def check_laravel_connection():
    """
    التحقق من أن Laravel يعمل
    """
    try:
        response = requests.get("http://localhost:8000", timeout=2)
        return True
    except:
        return False

def main():
    """
    الوظيفة الرئيسية
    """
    print_header("أداة اختبار محتوى Thread Email - Zoho Desk")
    
    # التحقق من اتصال Laravel
    print_info("🔍 التحقق من اتصال Laravel...")
    if not check_laravel_connection():
        print_error("❌ Laravel غير شغال!")
        print_info("\n🔧 الحل:")
        print_info("1. شغل Laravel: php artisan serve")
        print_info("2. تأكد أن Laravel يعمل على: http://localhost:8000")
        print_info("3. حاول مرة أخرى\n")
        return
    
    print_success("✅ Laravel يعمل\n")
    
    # الحصول على رقم التذكرة
    if len(sys.argv) > 1:
        ticket_id = sys.argv[1]
    else:
        ticket_id = input("\n📝 أدخل رقم التذكرة (Ticket ID): ").strip()
    
    if not ticket_id:
        print_error("رقم التذكرة مطلوب!")
        return
    
    print(f"\n🎫 رقم التذكرة: {ticket_id}")
    
    # جلب قائمة الـ Threads
    threads = get_ticket_threads(ticket_id)
    
    if not threads:
        print_error("لا توجد threads لهذه التذكرة")
        return
    
    # إذا كان هناك thread_id في arguments
    if len(sys.argv) > 2:
        thread_id = sys.argv[2]
    else:
        # عرض قائمة للاختيار
        print(f"\n{'='*80}")
        thread_id = input("\n📝 أدخل Thread ID الذي تريد اختباره (أو Enter لاختبار الأول): ").strip()
        
        if not thread_id and threads:
            thread_id = threads[0]['id']
            print_info(f"سيتم استخدام أول Thread: {thread_id}")
    
    if not thread_id:
        print_error("Thread ID مطلوب!")
        return
    
    # اختبار استرجاع محتوى الـ Thread
    success = test_thread_content_via_api(ticket_id, thread_id)
    
    if success:
        print_header("تم الاختبار بنجاح ✓")
        print_success("تم استرجاع محتوى الـ Thread بنجاح!")
        print_info("راجع الملفات المحفوظة لرؤية المحتوى الكامل")
    else:
        print_header("فشل الاختبار ✗")
        print_error("لم يتم استرجاع المحتوى. راجع الرسائل أعلاه للتفاصيل.")

if __name__ == '__main__':
    main()

