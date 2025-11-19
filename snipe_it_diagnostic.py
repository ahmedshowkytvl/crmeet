#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT API Diagnostic Tool
Enhanced diagnostic tool to troubleshoot Snipe-IT API connection issues
"""

import requests
import json
import sys
from urllib.parse import urljoin


def diagnose_snipe_it_connection():
    """Comprehensive diagnostic for Snipe-IT API connection"""
    
    # Configuration
    BASE_URL = "http://127.0.0.1:8000"
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'User-Agent': 'Snipe-IT-Diagnostic/1.0'
    }
    
    print("🔍 Snipe-IT API Diagnostic Tool")
    print("="*60)
    print(f"🎯 Target URL: {BASE_URL}")
    print(f"🔑 API Token: {API_TOKEN[:50]}...")
    print()
    
    # Test 1: Basic connectivity
    print("🔍 Test 1: Basic Server Connectivity")
    print("-" * 40)
    try:
        response = requests.get(BASE_URL, timeout=10)
        print(f"✅ Server is responding")
        print(f"   Status Code: {response.status_code}")
        print(f"   Content-Type: {response.headers.get('content-type', 'Unknown')}")
        print(f"   Server: {response.headers.get('server', 'Unknown')}")
        
        # Check if it's Laravel or Snipe-IT
        if 'laravel' in response.text.lower() or 'stafftobia' in response.text.lower():
            print("⚠️  WARNING: This appears to be a Laravel application, not Snipe-IT!")
            print("   Snipe-IT should be running on a different port or subdomain")
        elif 'snipe' in response.text.lower():
            print("✅ This appears to be Snipe-IT!")
        else:
            print("❓ Unknown application type")
            
    except requests.exceptions.ConnectionError:
        print("❌ Connection Error: Server is not running or not accessible")
        print("   Possible solutions:")
        print("   • Check if Snipe-IT is installed and running")
        print("   • Verify the URL and port")
        print("   • Check firewall settings")
        return False
    except requests.exceptions.Timeout:
        print("❌ Timeout Error: Server is not responding")
        return False
    except Exception as e:
        print(f"❌ Unexpected Error: {e}")
        return False
    
    # Test 2: API endpoint availability
    print("\n🔍 Test 2: API Endpoint Availability")
    print("-" * 40)
    
    api_endpoints = [
        '/api/v1/version',
        '/api/v1/users/me',
        '/api/v1/hardware',
        '/api/v1/categories',
        '/api/v1/users'
    ]
    
    for endpoint in api_endpoints:
        url = urljoin(BASE_URL, endpoint)
        try:
            response = requests.get(url, headers=headers, timeout=5)
            print(f"   {endpoint}: {response.status_code}")
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('status') == 'success':
                        print(f"      ✅ API working correctly")
                    else:
                        print(f"      ⚠️  API returned error: {data.get('messages', 'Unknown')}")
                except json.JSONDecodeError:
                    print(f"      ❌ Invalid JSON response")
            elif response.status_code == 401:
                print(f"      ❌ Authentication failed - check API token")
            elif response.status_code == 404:
                print(f"      ❌ Endpoint not found - Snipe-IT may not be installed")
            else:
                print(f"      ❌ HTTP {response.status_code}")
                
        except Exception as e:
            print(f"   {endpoint}: ❌ Error - {e}")
    
    # Test 3: Check if Snipe-IT is installed
    print("\n🔍 Test 3: Snipe-IT Installation Check")
    print("-" * 40)
    
    snipe_indicators = [
        '/login',
        '/setup',
        '/assets',
        '/hardware',
        '/admin'
    ]
    
    snipe_found = False
    for indicator in snipe_indicators:
        url = urljoin(BASE_URL, indicator)
        try:
            response = requests.get(url, timeout=5)
            if response.status_code == 200:
                content = response.text.lower()
                if any(keyword in content for keyword in ['snipe', 'asset management', 'inventory']):
                    print(f"✅ Found Snipe-IT at: {indicator}")
                    snipe_found = True
                    break
        except:
            continue
    
    if not snipe_found:
        print("❌ Snipe-IT installation not detected")
        print("   This URL appears to be running a different application")
    
    # Test 4: Alternative URLs to try
    print("\n🔍 Test 4: Alternative URLs to Check")
    print("-" * 40)
    
    alternative_urls = [
        "http://127.0.0.1:8080",
        "http://localhost:8000",
        "http://localhost:8080",
        "http://127.0.0.1:3000",
        "http://localhost:3000"
    ]
    
    for alt_url in alternative_urls:
        try:
            response = requests.get(alt_url, timeout=3)
            if response.status_code == 200:
                content = response.text.lower()
                if 'snipe' in content:
                    print(f"✅ Found Snipe-IT at: {alt_url}")
                else:
                    print(f"ℹ️  Server running at: {alt_url} (not Snipe-IT)")
        except:
            print(f"❌ No response from: {alt_url}")
    
    # Test 5: Token validation
    print("\n🔍 Test 5: API Token Analysis")
    print("-" * 40)
    
    try:
        import base64
        import jwt
        
        # Decode JWT token
        decoded = jwt.decode(API_TOKEN, options={"verify_signature": False})
        
        print(f"✅ Token is valid JWT format")
        print(f"   User ID: {decoded.get('sub', 'Unknown')}")
        print(f"   Issued At: {decoded.get('iat', 'Unknown')}")
        print(f"   Expires At: {decoded.get('exp', 'Unknown')}")
        
        # Check expiration
        import time
        current_time = int(time.time())
        exp_time = decoded.get('exp', 0)
        
        if exp_time > current_time:
            print(f"✅ Token is not expired")
        else:
            print(f"❌ Token has expired!")
            
    except Exception as e:
        print(f"❌ Token analysis failed: {e}")
    
    # Recommendations
    print("\n🔧 Recommendations")
    print("-" * 40)
    
    print("1. Install Snipe-IT:")
    print("   • Download from: https://snipe-it.readme.io/docs/installation")
    print("   • Or use Docker: docker run -d -p 8080:80 snipeit/snipe-it")
    print()
    
    print("2. Check your current setup:")
    print("   • Is Snipe-IT actually installed?")
    print("   • What port is it running on?")
    print("   • Is it accessible from your browser?")
    print()
    
    print("3. Alternative solutions:")
    print("   • Use a different port (8080, 3000, etc.)")
    print("   • Install Snipe-IT on a subdomain")
    print("   • Use Snipe-IT hosted service")
    print()
    
    print("4. Test with curl:")
    print(f"   curl -H 'Authorization: Bearer {API_TOKEN[:50]}...' {BASE_URL}/api/v1/users/me")


def test_with_different_urls():
    """Test API with different possible URLs"""
    print("\n🔍 Testing Different URLs")
    print("="*60)
    
    possible_urls = [
        "http://127.0.0.1:8000",
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
    
    for url in possible_urls:
        print(f"\n🔍 Testing: {url}")
        try:
            # Test basic connectivity
            response = requests.get(url, timeout=5)
            print(f"   Status: {response.status_code}")
            
            # Test API endpoint
            api_url = f"{url}/api/v1/users/me"
            api_response = requests.get(api_url, headers=headers, timeout=5)
            
            if api_response.status_code == 200:
                try:
                    data = api_response.json()
                    if data.get('status') == 'success':
                        print(f"   ✅ Snipe-IT API working!")
                        print(f"   User: {data.get('payload', {}).get('first_name', '')} {data.get('payload', {}).get('last_name', '')}")
                        return url
                    else:
                        print(f"   ⚠️  API error: {data.get('messages', 'Unknown')}")
                except:
                    print(f"   ❌ Invalid JSON")
            else:
                print(f"   ❌ API failed: {api_response.status_code}")
                
        except requests.exceptions.ConnectionError:
            print(f"   ❌ Connection failed")
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    return None


def main():
    """Main diagnostic function"""
    print("🚀 Snipe-IT API Diagnostic Tool")
    print("="*60)
    
    # Run comprehensive diagnosis
    diagnose_snipe_it_connection()
    
    # Test different URLs
    working_url = test_with_different_urls()
    
    if working_url:
        print(f"\n🎉 Found working Snipe-IT at: {working_url}")
        print(f"Update your configuration to use: {working_url}")
    else:
        print(f"\n❌ No working Snipe-IT installation found")
        print("Please install Snipe-IT first:")
        print("1. Visit: https://snipe-it.readme.io/docs/installation")
        print("2. Or use Docker: docker run -d -p 8080:80 snipeit/snipe-it")
        print("3. Then run this diagnostic again")


if __name__ == "__main__":
    main()

