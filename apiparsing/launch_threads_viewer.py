#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Launch Ticket Threads Viewer
تشغيل عارض التذاكر والخيوط
"""

import subprocess
import sys
import os

def check_requirements():
    """Check if required packages are installed"""
    try:
        import flask
        import requests
        print("✅ جميع المتطلبات متوفرة")
        return True
    except ImportError as e:
        print(f"❌ متطلب مفقود: {e}")
        print("يرجى تثبيت المتطلبات باستخدام:")
        print("pip install flask requests")
        return False

def main():
    print("="*60)
    print("  TICKET THREADS VIEWER - LAUNCHER")
    print("  مشغل عارض التذاكر والخيوط")
    print("="*60)
    print()
    
    # Check requirements
    if not check_requirements():
        return
    
    # Change to apiparsing directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    apiparsing_dir = script_dir
    
    # Check if main file exists
    ticket_threads_file = os.path.join(apiparsing_dir, 'ticket_threads_viewer.py')
    if not os.path.exists(ticket_threads_file):
        print("❌ ملف ticket_threads_viewer.py غير موجود")
        print(f"   المسار المطلوب: {ticket_threads_file}")
        return
    
    print("🚀 بدء تشغيل عارض التذاكر والخيوط...")
    print("📱 سيتم فتح المتصفح تلقائياً على: http://localhost:5000")
    print("⏹️  لإيقاف الخادم، اضغط Ctrl+C")
    print()
    
    try:
        # Change to apiparsing directory and run
        os.chdir(apiparsing_dir)
        # Run the Flask app
        subprocess.run([sys.executable, 'ticket_threads_viewer.py'])
    except KeyboardInterrupt:
        print("\n👋 تم إيقاف الخادم")
    except Exception as e:
        print(f"❌ خطأ في تشغيل الخادم: {e}")

if __name__ == "__main__":
    main()
