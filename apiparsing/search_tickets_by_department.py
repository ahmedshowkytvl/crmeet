#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Search tickets by Department ID - Zoho Desk API
"""

import requests
from datetime import datetime
from config import ZohoConfig

def search_tickets_by_department(department_id, limit=10):
    """
    جلب التذاكر من قسم محدد باستخدام Department ID
    
    Args:
        department_id (str): معرف القسم المطلوب
        limit (int): عدد التذاكر المطلوب جلبها (1-100)
    """
    
    config = ZohoConfig()
    
    print(f"=== Search tickets from department {department_id} ===")
    
    # الحصول على Access Token
    print("1. Getting Access Token...")
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    token_response = requests.post(config.TOKEN_URL, data=token_data)
    
    if token_response.status_code != 200:
        print(f"Token error: {token_response.text}")
        return
    
    access_token = token_response.json()['access_token']
    print(f"Access Token obtained successfully")
    
    # إعداد Headers
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    # جلب التذاكر من القسم المحدد
    print(f"2. Getting tickets from department {department_id}...")
    
    # الطريقة الأولى: استخدام departmentIds parameter
    url = f"{config.BASE_URLS['desk']}/tickets"
    params = {
        'orgId': config.ORG_ID,
        'departmentIds': department_id,  # معامل القسم
        'limit': limit
    }
    
    response = requests.get(url, headers=headers, params=params)
    
    if response.status_code == 200:
        tickets_data = response.json()
        tickets = tickets_data.get('data', [])
        
        print(f"Found {len(tickets)} tickets in department {department_id}")
        print("\n=== Available Tickets ===")
        
        for i, ticket in enumerate(tickets, 1):
            print(f"{i}. #{ticket.get('ticketNumber')}: {ticket.get('subject')}")
            print(f"   Status: {ticket.get('status')}")
            print(f"   Email: {ticket.get('email')}")
            print(f"   Created: {ticket.get('createdTime')}")
            print(f"   Department: {ticket.get('department', {}).get('name', 'Not specified')}")
            print(f"   Department ID: {ticket.get('departmentId', 'Not specified')}")
            print("   " + "-" * 50)
            
    else:
        print(f"Error getting tickets: {response.text}")
        
        # محاولة الطريقة البديلة باستخدام البحث
        print("\nTrying alternative search method...")
        search_by_department_alternative(access_token, config, department_id, limit)

def search_by_department_alternative(access_token, config, department_id, limit):
    """
    طريقة بديلة للبحث عن التذاكر باستخدام search endpoint
    """
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    # استخدام search endpoint مع filter
    search_url = f"{config.BASE_URLS['desk']}/tickets/search"
    
    # بناء معاملات البحث
    params = f"from=0&limit={limit}&sortBy=-createdTime"
    
    # إضافة فلتر للقسم
    filter_params = f"&filter=departmentId:{department_id}"
    
    full_url = f"{search_url}?{params}{filter_params}"
    
    print(f"Search using: {full_url}")
    
    response = requests.get(full_url, headers=headers)
    
    if response.status_code == 200:
        search_data = response.json()
        tickets = search_data.get('data', [])
        
        print(f"Found {len(tickets)} tickets (alternative method)")
        
        for i, ticket in enumerate(tickets, 1):
            print(f"{i}. #{ticket.get('ticketNumber')}: {ticket.get('subject')}")
            print(f"   Department: {ticket.get('departmentId', 'Not specified')}")
            print("   " + "-" * 30)
    else:
        print(f"Alternative search error: {response.text}")

def get_all_departments():
    """
    جلب قائمة جميع الأقسام المتاحة
    """
    
    config = ZohoConfig()
    
    # الحصول على Access Token
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    token_response = requests.post(config.TOKEN_URL, data=token_data)
    
    if token_response.status_code != 200:
        print(f"Token error: {token_response.text}")
        return
    
    access_token = token_response.json()['access_token']
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": config.ORG_ID,
        "contentType": "application/json; charset=utf-8"
    }
    
    # جلب قائمة الأقسام
    departments_url = f"{config.BASE_URLS['desk']}/departments"
    response = requests.get(departments_url, headers=headers)
    
    if response.status_code == 200:
        departments_data = response.json()
        departments = departments_data.get('data', [])
        
        print("=== Available Departments ===")
        for dept in departments:
            print(f"ID: {dept.get('id')} - Name: {dept.get('name')}")
            
        return departments
    else:
        print(f"Error getting departments: {response.text}")
        return []

def main():
    """
    الدالة الرئيسية لتشغيل الأمثلة
    """
    
    print("=== Search Tickets by Department - Zoho Desk API ===\n")
    
    # عرض الأقسام المتاحة أولاً
    print("1. Getting available departments...")
    departments = get_all_departments()
    
    if not departments:
        print("Cannot get departments. Try using known department IDs:")
        print("- 766285000006092035 (General Department)")
        print("- 766285000016070029 (Contracting - KSA)")
        print("- 766285000016070030 (Support Department)")
        
        # استخدام معرف قسم افتراضي للاختبار
        test_department_id = "766285000016070029"
        print(f"\nTesting search using department: {test_department_id}")
        search_tickets_by_department(test_department_id, limit=5)
    else:
        # استخدام أول قسم متاح للاختبار
        if departments:
            test_department_id = departments[0]['id']
            test_department_name = departments[0]['name']
            print(f"\nTesting search using department: {test_department_name} (ID: {test_department_id})")
            search_tickets_by_department(test_department_id, limit=5)

if __name__ == "__main__":
    main()
