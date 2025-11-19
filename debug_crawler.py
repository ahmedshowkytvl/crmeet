#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from bs4 import BeautifulSoup
import csv
import re
from urllib.parse import urljoin, urlparse
import time

def test_crawler():
    print("Starting debug crawler...")
    
    base_url = "http://127.0.0.1:8000/"
    cookies = {
        "laravel-session": "eyJpdiI6IjFucHZGUktZYktxdS9jd29qZlJGTGc9PSIsInZhbHVlIjoiNjRLRVA5SXhad2Qyd05OSWVCQW5IMGYrQnlONUxDVFFhSVl1SVdnUXVFcFBzNjd0VjZEeXRIWCtFU25rZHBoOUFYVTRTT1hqQUJlMzhpN3QvalFhUUlWampzNUlUSkdKZFFoWVYrMElIa3o1ekdVTCt2QkV4T294WGJkdG5nNDMiLCJtYWMiOiJhZmJmZGUwZDZhNGI0MDhlOGQ4YjEzNmNmZjk2MDM2ZjgzMWJiMzJmNjdhZTE4NjQyZmQ5NzdmODdlM2NhODFmIiwidGFnIjoiIn0%3D",
        "sahara.sid": "s%3AG1F-vnQfSjmOPc3e89i-KLD9UzmG3N-Z.2s7f%2FCaALS2KurLB4HmTDw9EarWhZMxDV4xqWjWSETw",
        "XSRF-TOKEN": "eyJpdiI6IlB3akJPVDI5bW5qUnJxQkhnZHNySVE9PSIsInZhbHVlIjoicTV3STd3RkJESWd4TFRxTHo4VFFtUGYrR21PQkpwcElUeHArS21ndVlmUkRuYWVLY0hYanVLaHFNdDB1NE52UjZZQURBdzVxdjJZby9PWmdYYm9mQ3hKZFdTSGZpS1haN21qc1pLKzd3eHhKcmQvY0dUM0huZHUwOGthSkFGaGwiLCJtYWMiOiJmZTNhMWE4NTNhYjI5NWQwYjRjZjVmNzM2YzM4YzM4YmVlNmZhM2ZmNWUxMTFjODU2N2RiOTBkNDhjODZlOTAyIiwidGFnIjoiIn0%3D"
    }
    
    session = requests.Session()
    session.cookies.update(cookies)
    
    print(f"Testing URL: {base_url}")
    
    try:
        response = session.get(base_url, timeout=10)
        print(f"Status: {response.status_code}")
        print(f"Content length: {len(response.text)}")
        
        # Extract links
        soup = BeautifulSoup(response.text, 'html.parser')
        links = []
        
        for link in soup.find_all('a', href=True):
            href = link['href']
            if href.startswith('/') or href.startswith(base_url):
                full_url = urljoin(base_url, href)
                links.append(full_url)
        
        print(f"Found {len(links)} links:")
        for i, link in enumerate(links[:10]):  # Show first 10 links
            print(f"  {i+1}. {link}")
        
        if len(links) > 10:
            print(f"  ... and {len(links) - 10} more")
        
        # Check for errors
        error_patterns = ['ErrorException', 'Internal Server Error', 'Attempt to read property']
        found_errors = []
        
        for pattern in error_patterns:
            if re.search(pattern, response.text, re.IGNORECASE):
                found_errors.append(pattern)
        
        if found_errors:
            print(f"Errors found: {found_errors}")
        else:
            print("No errors detected")
            
        return len(links)
        
    except Exception as e:
        print(f"Error: {e}")
        return 0

if __name__ == "__main__":
    test_crawler()




