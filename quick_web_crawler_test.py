#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Quick Web Crawler Test
"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import sys
import json
import re


def detect_errors(page_content, url):
    """Detect Laravel errors and JavaScript issues in page content"""
    errors = []
    
    # Laravel Error Patterns
    laravel_patterns = {
        'Laravel Exception': [
            r'Illuminate\\Database\\QueryException',
            r'Illuminate\\Http\\Exceptions\\HttpResponseException',
            r'Illuminate\\Auth\\AuthenticationException',
            r'Illuminate\\Validation\\ValidationException',
            r'Illuminate\\Database\\Eloquent\\ModelNotFoundException',
            r'Illuminate\\Session\\TokenMismatchException',
            r'Illuminate\\Http\\Exceptions\\ThrottleRequestsException'
        ],
        'Database Errors': [
            r'SQLSTATE\[.*?\].*?doesn\'t exist',
            r'SQLSTATE\[.*?\].*?Unknown column',
            r'SQLSTATE\[.*?\].*?Duplicate entry',
            r'SQLSTATE\[.*?\].*?Access denied',
            r'SQLSTATE\[.*?\].*?Connection refused'
        ],
        'Laravel Debug Info': [
            r'Whoops, looks like something went wrong',
            r'Laravel Framework',
            r'Stack trace:',
            r'#0.*?vendor/laravel',
            r'Exception trace:',
            r'at Illuminate\\'
        ],
        'Authentication Errors': [
            r'Unauthenticated',
            r'This action is unauthorized',
            r'CSRF token mismatch',
            r'419 Page Expired',
            r'Too Many Attempts'
        ],
        'Validation Errors': [
            r'The given data was invalid',
            r'validation\.errors',
            r'field is required',
            r'field must be',
            r'field format is invalid'
        ]
    }
    
    # JavaScript Error Patterns
    js_patterns = {
        'JavaScript Errors': [
            r'Uncaught.*?Error:',
            r'TypeError:',
            r'ReferenceError:',
            r'SyntaxError:',
            r'Cannot read property.*?of undefined',
            r'Cannot read properties.*?of undefined',
            r'is not a function',
            r'is not defined',
            r'Unexpected token',
            r'Unexpected end of input'
        ],
        'Console Errors': [
            r'console\.error',
            r'console\.warn',
            r'Failed to load resource',
            r'404.*?not found',
            r'500.*?Internal Server Error',
            r'Network Error',
            r'CORS policy'
        ],
        'jQuery/Ajax Errors': [
            r'jQuery.*?error',
            r'AJAX.*?error',
            r'XMLHttpRequest.*?error',
            r'fetch.*?error',
            r'Promise.*?rejected'
        ]
    }
    
    # Check Laravel errors
    for category, patterns in laravel_patterns.items():
        for pattern in patterns:
            matches = re.findall(pattern, page_content, re.IGNORECASE | re.MULTILINE)
            if matches:
                errors.append({
                    'type': 'Laravel',
                    'category': category,
                    'pattern': pattern,
                    'matches': matches[:3],  # First 3 matches
                    'url': url
                })
    
    # Check JavaScript errors
    for category, patterns in js_patterns.items():
        for pattern in patterns:
            matches = re.findall(pattern, page_content, re.IGNORECASE | re.MULTILINE)
            if matches:
                errors.append({
                    'type': 'JavaScript',
                    'category': category,
                    'pattern': pattern,
                    'matches': matches[:3],  # First 3 matches
                    'url': url
                })
    
    return errors


def crawl_page(url, cookies=None, visited=None, max_depth=2, current_depth=0):
    """Crawl a single page and collect all sub-links with error detection"""
    
    if visited is None:
        visited = set()
    
    if url in visited or current_depth > max_depth:
        return visited, {}
    
    visited.add(url)
    
    try:
        print(f"{'  ' * current_depth}⏳ Crawling: {url}")
        
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        
        # Prepare cookies if provided
        cookie_dict = {}
        if cookies:
            for cookie in cookies:
                cookie_dict[cookie['name']] = cookie['value']
        
        response = requests.get(url, headers=headers, cookies=cookie_dict, timeout=10)
        response.raise_for_status()
        
        # Parse HTML
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Page information
        title = soup.find('title')
        page_title = title.get_text().strip() if title else 'No title'
        
        # Extract links
        links = soup.find_all('a', href=True)
        images = soup.find_all('img')
        forms = soup.find_all('form')
        
        # Process links
        page_links = {}
        sub_links = []
        
        for link in links:
            href = link.get('href')
            if href:
                full_url = urljoin(url, href)
                text = link.get_text(strip=True)[:50] or 'No text'
                
                # Only process internal links (same domain)
                if is_internal_link(url, full_url):
                    page_links[full_url] = text
                    if full_url not in visited and current_depth < max_depth:
                        sub_links.append(full_url)
        
        # Detect errors in page content
        page_content = response.text
        errors = detect_errors(page_content, url)
        
        # Store page data
        page_data = {
            'title': page_title,
            'links': page_links,
            'images_count': len(images),
            'forms_count': len(forms),
            'status_code': response.status_code,
            'size': len(response.content),
            'errors': errors
        }
        
        # Print errors if found
        if errors:
            print(f"{'  ' * current_depth}⚠️  Found {len(errors)} errors!")
            for error in errors:
                print(f"{'  ' * current_depth}   🔴 {error['type']} - {error['category']}")
                for match in error['matches'][:2]:  # Show first 2 matches
                    print(f"{'  ' * current_depth}      • {match[:80]}...")
        else:
            print(f"{'  ' * current_depth}✓ Found {len(page_links)} internal links (no errors)")
        
        # Recursively crawl sub-links
        for sub_url in sub_links:
            visited, _ = crawl_page(sub_url, cookies, visited, max_depth, current_depth + 1)
        
        return visited, page_data
        
    except Exception as e:
        print(f"{'  ' * current_depth}❌ Error crawling {url}: {str(e)[:100]}")
        return visited, {}


def is_internal_link(base_url, link_url):
    """Check if link is internal to the same domain"""
    from urllib.parse import urlparse
    
    try:
        base_domain = urlparse(base_url).netloc
        link_domain = urlparse(link_url).netloc
        return base_domain == link_domain
    except:
        return False


def quick_crawl(url, cookies=None, max_depth=2):
    """Quick test crawl for a single website with sub-link collection"""
    
    print(f"""
╔══════════════════════════════════════════════════════════╗
║              🚀 Advanced Web Crawler                     ║
╚══════════════════════════════════════════════════════════╝

🔍 Starting URL: {url}
📊 Max depth: {max_depth}
    """)
    
    try:
        print("⏳ Starting comprehensive crawl...")
        
        # Prepare cookies if provided
        cookie_dict = {}
        if cookies:
            print("🍪 Using provided cookies...")
            for cookie in cookies:
                cookie_dict[cookie['name']] = cookie['value']
        
        # Start crawling
        visited, page_data = crawl_page(url, cookies, max_depth=max_depth)
        
        # Display results
        print(f"\n{'='*80}")
        print("📋 CRAWLING RESULTS SUMMARY")
        print(f"{'='*80}")
        
        print(f"✅ Total pages crawled: {len(visited)}")
        print(f"🍪 Cookies used: {len(cookie_dict) if cookie_dict else 0}")
        
        # Display all discovered pages
        print(f"\n📄 DISCOVERED PAGES:")
        print("="*80)
        
        all_links = set()
        all_errors = []
        
        for i, page_url in enumerate(visited, 1):
            print(f"\n{i}. {page_url}")
            if page_url in page_data:
                data = page_data[page_url]
                print(f"   📄 Title: {data.get('title', 'No title')}")
                print(f"   🔗 Internal links: {len(data.get('links', {}))}")
                print(f"   🖼️  Images: {data.get('images_count', 0)}")
                print(f"   📝 Forms: {data.get('forms_count', 0)}")
                print(f"   📊 Size: {data.get('size', 0):,} bytes")
                
                # Show errors for this page
                page_errors = data.get('errors', [])
                if page_errors:
                    print(f"   ⚠️  Errors: {len(page_errors)}")
                    for error in page_errors:
                        print(f"      🔴 {error['type']} - {error['category']}")
                        for match in error['matches'][:1]:  # Show first match
                            print(f"         • {match[:60]}...")
                    all_errors.extend(page_errors)
                else:
                    print(f"   ✅ No errors found")
                
                # Add links to total count
                all_links.update(data.get('links', {}).keys())
        
        # Display all unique links found
        print(f"\n🔗 ALL UNIQUE INTERNAL LINKS FOUND:")
        print("="*80)
        
        for i, link_url in enumerate(sorted(all_links), 1):
            # Find which page this link was found on
            source_pages = []
            for page_url, data in page_data.items():
                if link_url in data.get('links', {}):
                    source_pages.append(page_url)
            
            print(f"{i:3d}. {link_url}")
            if source_pages:
                print(f"      Found on: {', '.join(source_pages[:2])}{'...' if len(source_pages) > 2 else ''}")
        
        # Display error summary
        if all_errors:
            print(f"\n🚨 ERROR SUMMARY:")
            print("="*80)
            
            # Group errors by type
            laravel_errors = [e for e in all_errors if e['type'] == 'Laravel']
            js_errors = [e for e in all_errors if e['type'] == 'JavaScript']
            
            if laravel_errors:
                print(f"\n🔴 Laravel Errors ({len(laravel_errors)}):")
                for category in set(e['category'] for e in laravel_errors):
                    count = len([e for e in laravel_errors if e['category'] == category])
                    print(f"   • {category}: {count} errors")
            
            if js_errors:
                print(f"\n🟡 JavaScript Errors ({len(js_errors)}):")
                for category in set(e['category'] for e in js_errors):
                    count = len([e for e in js_errors if e['category'] == category])
                    print(f"   • {category}: {count} errors")
            
            # Show most common errors
            print(f"\n📋 Most Common Errors:")
            error_counts = {}
            for error in all_errors:
                key = f"{error['type']} - {error['category']}"
                error_counts[key] = error_counts.get(key, 0) + 1
            
            sorted_errors = sorted(error_counts.items(), key=lambda x: x[1], reverse=True)
            for error_type, count in sorted_errors[:5]:  # Top 5
                print(f"   • {error_type}: {count} occurrences")
        
        print(f"\n{'='*80}")
        print(f"📊 FINAL STATISTICS:")
        print(f"   • Total pages visited: {len(visited)}")
        print(f"   • Total unique internal links: {len(all_links)}")
        print(f"   • Total errors found: {len(all_errors)}")
        print(f"   • Crawl depth: {max_depth}")
        print(f"{'='*80}")
        
        return True
        
    except Exception as e:
        print(f"❌ Unexpected error: {e}")
        return False


def test_multiple_sites(cookies=None, max_depth=1):
    """Test multiple websites"""
    
    test_sites = [
        "https://example.com",
        "https://httpbin.org",
        "https://jsonplaceholder.typicode.com"
    ]
    
    print("""
╔══════════════════════════════════════════════════════════╗
║           🧪 Testing Multiple Sites                     ║
╚══════════════════════════════════════════════════════════╝
    """)
    
    results = {}
    
    for site in test_sites:
        print(f"\n{'='*60}")
        print(f"📍 Testing: {site}")
        print(f"{'='*60}")
        
        success = quick_crawl(site, cookies, max_depth)
        results[site] = success
        
        print("\n")
    
    # Results summary
    print("\n" + "="*60)
    print("📋 Test Results Summary")
    print("="*60)
    
    for site, success in results.items():
        status = "✅ Success" if success else "❌ Failed"
        print(f"{status} - {site}")


def load_cookies_from_file(filename):
    """Load cookies from JSON file"""
    try:
        with open(filename, 'r', encoding='utf-8') as f:
            cookies = json.load(f)
        print(f"🍪 Loaded {len(cookies)} cookies from {filename}")
        return cookies
    except FileNotFoundError:
        print(f"❌ Cookie file {filename} not found")
        return None
    except json.JSONDecodeError:
        print(f"❌ Invalid JSON in cookie file {filename}")
        return None


def main():
    """Main function"""
    
    # Default cookies for Laravel session
    default_cookies = [
        {
            "domain": "127.0.0.1",
            "expirationDate": 1760803735.420551,
            "hostOnly": True,
            "httpOnly": True,
            "name": "laravel-session",
            "path": "/",
            "sameSite": "lax",
            "secure": False,
            "session": False,
            "storeId": None,
            "value": "eyJpdiI6IlVhaU1TSk5xMnpuMzZ5bEdROFNVUGc9PSIsInZhbHVlIjoidUNYT0ZCK3RidTIxNGp5RVA3VnRBZExtdnZIWi9UcUFaTWw2eWtjQ2MvU3NNSXRub0tjUDZKR3ArNWJWNVlUbDIzbUV3cytqY2lEVVZ6aXpnYytzN0VxR1REcDFBN3ZURGl1L0VCdVFMVWFlZlhZVU50emtnRzBJKzR6MEZHRzkiLCJtYWMiOiI1M2ZhZjg1ZmRhZTI0OTM0NmU5YjQxOWU3YjBhZmYxNTkyNzY5ZWM2MjNjNDNmYjE5MTIzNDc5OTVkMjU2YzllIiwidGFnIjoiIn0%3D"
        },
        {
            "domain": "127.0.0.1",
            "expirationDate": 1760803735.420375,
            "hostOnly": True,
            "httpOnly": False,
            "name": "XSRF-TOKEN",
            "path": "/",
            "sameSite": "lax",
            "secure": False,
            "session": False,
            "storeId": None,
            "value": "eyJpdiI6IlVDSGhsZWozMU16VXFqaFZCNG1pbVE9PSIsInZhbHVlIjoiNDZ1ZVJST1h6WXBNNmdyNDRKRmVtR09ERDlGS3ZYTTU2eTdjZUp6OFFlUGlDZTU2VmZrZGgrTXFpSTFOcjU3elFhTEhhOFhoRXo1WmloZGdHcDh4WGppUjl2clA1c0twUytvYnVscUVtVVpaOHhuM2hNK1J6UFpSNldYYU9uTnAiLCJtYWMiOiIyMzE5YzYxOTJiMmM5OGRhZjg2Y2Y4MjE1M2U5NjVjZjJjYzM4MTg2NzVjYzNiNTcxYTcyNTE4ZWVlMTdhMzk0IiwidGFnIjoiIn0%3D"
        }
    ]
    
    if len(sys.argv) > 1:
        # Use URL from command line
        url = sys.argv[1]
        
        # Check if cookies file is provided
        cookies = default_cookies
        if len(sys.argv) > 2:
            cookies = load_cookies_from_file(sys.argv[2]) or default_cookies
        
        # Check for depth parameter
        max_depth = 2
        if len(sys.argv) > 3:
            try:
                max_depth = int(sys.argv[3])
            except ValueError:
                max_depth = 2
        
        quick_crawl(url, cookies, max_depth)
    else:
        # Interactive menu
        print("""
╔══════════════════════════════════════════════════════════╗
║            🕷️  Advanced Web Crawler                      ║
╚══════════════════════════════════════════════════════════╝

Usage:
  python quick_test.py <url> [cookies_file] [depth]    # Test single site
  python quick_test.py                                  # Interactive menu
        """)
        
        print("\nChoose an option:")
        print("1. Test single site (with sub-links)")
        print("2. Test multiple sites")
        print("3. Test with custom cookies file")
        print("4. Deep crawl (depth 3+)")
        print("0. Exit")
        
        choice = input("\nChoice: ").strip()
        
        if choice == '1':
            url = input("\nEnter website URL: ").strip()
            if url:
                depth = input("Enter crawl depth (default 2): ").strip()
                max_depth = int(depth) if depth.isdigit() else 2
                quick_crawl(url, default_cookies, max_depth)
            else:
                print("❌ No URL entered!")
                
        elif choice == '2':
            depth = input("Enter crawl depth (default 1): ").strip()
            max_depth = int(depth) if depth.isdigit() else 1
            test_multiple_sites(default_cookies, max_depth)
            
        elif choice == '3':
            cookies_file = input("\nEnter cookies file path: ").strip()
            cookies = load_cookies_from_file(cookies_file) if cookies_file else default_cookies
            url = input("Enter website URL: ").strip()
            if url:
                depth = input("Enter crawl depth (default 2): ").strip()
                max_depth = int(depth) if depth.isdigit() else 2
                quick_crawl(url, cookies, max_depth)
            else:
                print("❌ No URL entered!")
        
        elif choice == '4':
            url = input("\nEnter website URL: ").strip()
            if url:
                print("⚠️  Deep crawl will take longer and may hit many pages!")
                depth = input("Enter crawl depth (3-5 recommended): ").strip()
                max_depth = int(depth) if depth.isdigit() else 3
                quick_crawl(url, default_cookies, max_depth)
            else:
                print("❌ No URL entered!")
            
        elif choice == '0':
            print("👋 Goodbye!")
        else:
            print("❌ Invalid choice!")


if __name__ == "__main__":
    main()
