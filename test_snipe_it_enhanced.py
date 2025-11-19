#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT API Test with Enhanced Error Handling
Enhanced version of the API test with better error reporting
"""

import requests
import json
import sys
from urllib.parse import urljoin


def test_snipe_it_api_enhanced():
    """Enhanced Snipe-IT API test with detailed error reporting"""
    
    # Configuration
    BASE_URL = "http://127.0.0.1:8000"
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'User-Agent': 'Snipe-IT-Test/1.0'
    }
    
    print("🚀 Enhanced Snipe-IT API Test")
    print("="*60)
    print(f"🎯 Target URL: {BASE_URL}")
    print(f"🔑 Token: {API_TOKEN[:50]}...")
    print()
    
    # Test 1: Basic server check
    print("🔍 Test 1: Basic Server Check")
    print("-" * 40)
    
    try:
        response = requests.get(BASE_URL, timeout=10)
        print(f"✅ Server responding: {response.status_code}")
        print(f"   Content-Type: {response.headers.get('content-type', 'Unknown')}")
        
        # Check what's running
        content = response.text.lower()
        if 'laravel' in content or 'stafftobia' in content:
            print("⚠️  WARNING: This is Laravel/StaffTobia, not Snipe-IT!")
            print("   Snipe-IT should be on a different port (e.g., 8080)")
        elif 'snipe' in content:
            print("✅ This appears to be Snipe-IT!")
        else:
            print("❓ Unknown application")
            
    except requests.exceptions.ConnectionError:
        print("❌ Connection Error: Server not accessible")
        print("   Possible causes:")
        print("   • Snipe-IT not installed")
        print("   • Wrong URL/port")
        print("   • Server not running")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False
    
    # Test 2: API endpoint tests
    print("\n🔍 Test 2: API Endpoint Tests")
    print("-" * 40)
    
    endpoints = [
        ('/api/v1/version', 'API Version'),
        ('/api/v1/users/me', 'Current User'),
        ('/api/v1/hardware?limit=5', 'Assets'),
        ('/api/v1/categories', 'Categories'),
        ('/api/v1/users?limit=5', 'Users')
    ]
    
    success_count = 0
    
    for endpoint, description in endpoints:
        url = urljoin(BASE_URL, endpoint)
        print(f"\n   Testing {description} ({endpoint})...")
        
        try:
            response = requests.get(url, headers=headers, timeout=10)
            print(f"   Status: {response.status_code}")
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('status') == 'success':
                        print(f"   ✅ Success!")
                        
                        # Show some data
                        payload = data.get('payload', {})
                        if isinstance(payload, dict):
                            if 'first_name' in payload:
                                print(f"      User: {payload.get('first_name')} {payload.get('last_name')}")
                            elif 'version' in payload:
                                print(f"      Version: {payload.get('version')}")
                        elif isinstance(payload, list):
                            print(f"      Found {len(payload)} items")
                            
                        success_count += 1
                    else:
                        print(f"   ❌ API Error: {data.get('messages', 'Unknown error')}")
                        
                except json.JSONDecodeError as e:
                    print(f"   ❌ Invalid JSON: {e}")
                    print(f"   Response: {response.text[:200]}...")
                    
            elif response.status_code == 401:
                print(f"   ❌ Authentication failed")
                print(f"   Check if API token is valid")
                
            elif response.status_code == 404:
                print(f"   ❌ Endpoint not found")
                print(f"   Snipe-IT may not be installed or configured")
                
            elif response.status_code == 500:
                print(f"   ❌ Server error")
                print(f"   Check Snipe-IT logs")
                
            else:
                print(f"   ❌ HTTP {response.status_code}")
                
        except requests.exceptions.Timeout:
            print(f"   ❌ Timeout")
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    # Summary
    print(f"\n📊 Test Summary")
    print("="*60)
    print(f"✅ Successful tests: {success_count}/{len(endpoints)}")
    
    if success_count == 0:
        print("\n❌ No tests passed. Possible issues:")
        print("1. Snipe-IT not installed")
        print("2. Wrong URL/port")
        print("3. Invalid API token")
        print("4. Server not running")
        print("\n🔧 Solutions:")
        print("• Run: python setup_snipe_it.py")
        print("• Or install Snipe-IT manually")
        print("• Check if it's running on port 8080")
        
    elif success_count < len(endpoints):
        print(f"\n⚠️  Partial success. Some endpoints failed.")
        print("Check the specific error messages above.")
        
    else:
        print(f"\n🎉 All tests passed! Snipe-IT API is working correctly.")
    
    return success_count > 0


def test_alternative_urls():
    """Test alternative URLs where Snipe-IT might be running"""
    print("\n🔍 Testing Alternative URLs")
    print("="*60)
    
    alternative_urls = [
        "http://127.0.0.1:8080",
        "http://localhost:8000", 
        "http://localhost:8080",
        "http://127.0.0.1:3000",
        "http://localhost:3000"
    ]
    
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    for url in alternative_urls:
        print(f"\n🔍 Testing: {url}")
        
        try:
            # Test basic connectivity
            response = requests.get(url, timeout=5)
            print(f"   Server: {response.status_code}")
            
            if response.status_code == 200:
                # Test API
                api_url = f"{url}/api/v1/users/me"
                api_response = requests.get(api_url, headers=headers, timeout=5)
                
                if api_response.status_code == 200:
                    try:
                        data = api_response.json()
                        if data.get('status') == 'success':
                            print(f"   ✅ Snipe-IT API working!")
                            print(f"   User: {data.get('payload', {}).get('first_name', '')} {data.get('payload', {}).get('last_name', '')}")
                            print(f"\n🎉 Found working Snipe-IT at: {url}")
                            print(f"Update your scripts to use: {url}")
                            return url
                    except:
                        pass
                        
        except requests.exceptions.ConnectionError:
            print(f"   ❌ No response")
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    print(f"\n❌ No working Snipe-IT found on any port")
    return None


def main():
    """Main function"""
    print("🚀 Snipe-IT API Enhanced Test")
    print("="*60)
    
    # Run enhanced test
    success = test_snipe_it_api_enhanced()
    
    if not success:
        # Try alternative URLs
        working_url = test_alternative_urls()
        
        if not working_url:
            print(f"\n🔧 Next Steps:")
            print("1. Install Snipe-IT:")
            print("   python setup_snipe_it.py")
            print("2. Or install manually:")
            print("   https://snipe-it.readme.io/docs/installation")
            print("3. Or use Docker:")
            print("   docker run -d -p 8080:80 snipeit/snipe-it")


if __name__ == "__main__":
    main()

