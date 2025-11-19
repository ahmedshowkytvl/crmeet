#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT API Quick Test
Simple script to test your Snipe-IT API connection
"""

import requests
import json


def test_snipe_it_api():
    """Test Snipe-IT API connection with your token"""
    
    # Your configuration
    BASE_URL = "http://127.0.0.1:8000"
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    print("🚀 Snipe-IT API Test")
    print("="*50)
    
    # Test 1: Get current user
    print("🔍 Test 1: Getting current user...")
    try:
        response = requests.get(f"{BASE_URL}/api/v1/users/me", headers=headers, timeout=10)
        result = response.json()
        
        if result.get('status') == 'success':
            user = result.get('payload', {})
            print(f"✅ Success! Logged in as: {user.get('first_name', '')} {user.get('last_name', '')}")
            print(f"   Username: {user.get('username', 'N/A')}")
            print(f"   Email: {user.get('email', 'N/A')}")
        else:
            print(f"❌ Failed: {result.get('messages', 'Unknown error')}")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 2: Get API version
    print("\n🔍 Test 2: Getting API version...")
    try:
        response = requests.get(f"{BASE_URL}/api/v1/version", headers=headers, timeout=10)
        result = response.json()
        
        if result.get('status') == 'success':
            version = result.get('payload', {})
            print(f"✅ API Version: {version.get('version', 'Unknown')}")
        else:
            print(f"❌ Failed: {result.get('messages', 'Unknown error')}")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 3: Get assets
    print("\n🔍 Test 3: Getting assets...")
    try:
        response = requests.get(f"{BASE_URL}/api/v1/hardware?limit=5", headers=headers, timeout=10)
        result = response.json()
        
        if result.get('status') == 'success':
            assets = result.get('rows', [])
            print(f"✅ Found {len(assets)} assets")
            for asset in assets[:3]:
                print(f"   • {asset.get('name', 'Unnamed')} (Tag: {asset.get('asset_tag', 'N/A')})")
        else:
            print(f"❌ Failed: {result.get('messages', 'Unknown error')}")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 4: Get categories
    print("\n🔍 Test 4: Getting categories...")
    try:
        response = requests.get(f"{BASE_URL}/api/v1/categories", headers=headers, timeout=10)
        result = response.json()
        
        if result.get('status') == 'success':
            categories = result.get('rows', [])
            print(f"✅ Found {len(categories)} categories")
            for category in categories[:3]:
                print(f"   • {category.get('name', 'Unnamed')}")
        else:
            print(f"❌ Failed: {result.get('messages', 'Unknown error')}")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 5: Get users
    print("\n🔍 Test 5: Getting users...")
    try:
        response = requests.get(f"{BASE_URL}/api/v1/users?limit=5", headers=headers, timeout=10)
        result = response.json()
        
        if result.get('status') == 'success':
            users = result.get('rows', [])
            print(f"✅ Found {len(users)} users")
            for user in users[:3]:
                print(f"   • {user.get('first_name', '')} {user.get('last_name', '')} ({user.get('username', '')})")
        else:
            print(f"❌ Failed: {result.get('messages', 'Unknown error')}")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    print("\n" + "="*50)
    print("🎉 API Test Complete!")


if __name__ == "__main__":
    test_snipe_it_api()
