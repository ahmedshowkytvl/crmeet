#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import sys

def test_connection():
    """اختبار الاتصال بالخادم"""
    url = "http://127.0.0.1:8000/"
    
    cookies = {
        "laravel-session": "eyJpdiI6IjFucHZGUktZYktxdS9jd29qZlJGTGc9PSIsInZhbHVlIjoiNjRLRVA5SXhad2Qyd05OSWVCQW5IMGYrQnlONUxDVFFhSVl1SVdnUXVFcFBzNjd0VjZEeXRIWCtFU25rZHBoOUFYVTRTT1hqQUJlMzhpN3QvalFhUUlWampzNUlUSkdKZFFoWVYrMElIa3o1ekdVTCt2QkV4T294WGJkdG5nNDMiLCJtYWMiOiJhZmJmZGUwZDZhNGI0MDhlOGQ4YjEzNmNmZjk2MDM2ZjgzMWJiMzJmNjdhZTE4NjQyZmQ5NzdmODdlM2NhODFmIiwidGFnIjoiIn0%3D",
        "sahara.sid": "s%3AG1F-vnQfSjmOPc3e89i-KLD9UzmG3N-Z.2s7f%2FCaALS2KurLB4HmTDw9EarWhZMxDV4xqWjWSETw",
        "XSRF-TOKEN": "eyJpdiI6IlB3akJPVDI5bW5qUnJxQkhnZHNySVE9PSIsInZhbHVlIjoicTV3STd3RkJESWd4TFRxTHo4VFFtUGYrR21PQkpwcElUeHArS21ndVlmUkRuYWVLY0hYanVLaHFNdDB1NE52UjZZQURBdzVxdjJZby9PWmdYYm9mQ3hKZFdTSGZpS1haN21qc1pLKzd3eHhKcmQvY0dUM0huZHUwOGthSkFGaGwiLCJtYWMiOiJmZTNhMWE4NTNhYjI5NWQwYjRjZjVmNzM2YzM4YzM4YmVlNmZhM2ZmNWUxMTFjODU2N2RiOTBkNDhjODZlOTAyIiwidGFnIjoiIn0%3D"
    }
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    }
    
    print(f"Testing connection to: {url}")
    
    try:
        response = requests.get(url, cookies=cookies, headers=headers, timeout=5)
        print(f"Status Code: {response.status_code}")
        print(f"Response Length: {len(response.text)} characters")
        print(f"Content-Type: {response.headers.get('content-type', 'Unknown')}")
        
        if "ErrorException" in response.text or "Internal Server Error" in response.text:
            print("ERROR FOUND in response!")
        else:
            print("No obvious errors detected")
            
        return True
        
    except requests.exceptions.ConnectionError:
        print("ERROR: Cannot connect to server. Is Laravel running on port 8000?")
        print("Try running: php artisan serve")
        return False
    except requests.exceptions.Timeout:
        print("ERROR: Connection timeout")
        return False
    except Exception as e:
        print(f"ERROR: {e}")
        return False

if __name__ == "__main__":
    test_connection()




