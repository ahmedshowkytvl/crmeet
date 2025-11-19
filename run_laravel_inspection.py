#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Laravel Project Inspector Runner
Simple script to run the comprehensive Laravel inspection
"""

from laravel_project_inspector import LaravelProjectInspector

def main():
    """Run Laravel project inspection"""
    
    print("""
╔══════════════════════════════════════════════════════════╗
║           🔍 Laravel Project Inspector                   ║
╚══════════════════════════════════════════════════════════╝

This tool will inspect all URLs in your Laravel project for:
• JavaScript errors and console warnings
• Missing translation keys
• Language consistency issues
• Broken links and HTTP errors
• Laravel-specific errors

Starting inspection...
    """)
    
    # URLs to inspect
    urls = [
        "http://127.0.0.1:8000/",
        "http://127.0.0.1:8000/chat",
        "http://127.0.0.1:8000/dashboard",
        "http://127.0.0.1:8000/users",
        "http://127.0.0.1:8000/password-accounts",
        "http://127.0.0.1:8000/password-categories",
        "http://127.0.0.1:8000/suppliers",
        "http://127.0.0.1:8000/contacts",
        "http://127.0.0.1:8000/contact-categories",
        "http://127.0.0.1:8000/tasks",
        "http://127.0.0.1:8000/departments",
        "http://127.0.0.1:8000/requests",
        "http://127.0.0.1:8000/zoho/eet-life",
        "http://127.0.0.1:8000/zoho/audit",
        "http://127.0.0.1:8000/assets/dashboard",
        "http://127.0.0.1:8000/assets/assets",
        "http://127.0.0.1:8000/assets/categories",
        "http://127.0.0.1:8000/assets/locations",
        "http://127.0.0.1:8000/assets/assignments",
        "http://127.0.0.1:8000/assets/logs",
        "http://127.0.0.1:8000/lang/en",
        "http://127.0.0.1:8000/lang/ar",
        "http://127.0.0.1:8000/users/123",
        "http://127.0.0.1:8000/users/create",
        "http://127.0.0.1:8000/tasks/create",
        "http://127.0.0.1:8000/departments/create",
        "http://127.0.0.1:8000/requests/create"
    ]
    
    # Create inspector instance
    inspector = LaravelProjectInspector()
    
    # Run inspection
    inspector.inspect_all_urls(urls)
    
    print("\n" + "="*80)
    print("🎉 Inspection completed!")
    print("="*80)

if __name__ == "__main__":
    main()


