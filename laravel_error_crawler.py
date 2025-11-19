#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Laravel Error Crawler
يقوم بفحص جميع الصفحات في النطاق المحلي واكتشاف أخطاء Laravel/PHP
"""

import requests
from bs4 import BeautifulSoup
import csv
import re
from urllib.parse import urljoin, urlparse
from typing import Set, List, Dict, Optional
import time
import sys
import os

# إعداد الترميز للنظام
if sys.platform == "win32":
    import codecs
    sys.stdout = codecs.getwriter("utf-8")(sys.stdout.detach())
    sys.stderr = codecs.getwriter("utf-8")(sys.stderr.detach())

class LaravelErrorCrawler:
    def __init__(self, base_url: str, cookies: List[Dict[str, str]], max_pages: int = 50):
        self.base_url = base_url.rstrip('/')
        self.domain = urlparse(base_url).netloc
        self.cookies = {cookie['name']: cookie['value'] for cookie in cookies}
        self.visited_urls: Set[str] = set()
        self.errors_found: List[Dict[str, str]] = []
        self.session = requests.Session()
        self.session.cookies.update(self.cookies)
        self.max_pages = max_pages
        
        # أنماط اكتشاف الأخطاء
        self.error_patterns = [
            r'ErrorException',
            r'Internal Server Error',
            r'Attempt to read property',
            r'Stack Trace',
            r'Fatal error',
            r'Parse error',
            r'Warning:',
            r'Notice:',
            r'Exception:',
            r'Error:',
            r'Call to undefined',
            r'Class.*not found',
            r'Method.*does not exist'
        ]
        
        # عناوين HTTP للاستجابة
        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }

    def is_same_domain(self, url: str) -> bool:
        """فحص ما إذا كان الرابط ينتمي لنفس النطاق"""
        try:
            parsed = urlparse(url)
            return parsed.netloc == self.domain or parsed.netloc == ''
        except:
            return False

    def normalize_url(self, url: str) -> str:
        """تطبيع الرابط"""
        if url.startswith('//'):
            url = 'http:' + url
        elif url.startswith('/'):
            url = self.base_url + url
        elif not url.startswith('http'):
            url = urljoin(self.base_url, url)
        
        # إزالة الـ fragments (#)
        parsed = urlparse(url)
        normalized = f"{parsed.scheme}://{parsed.netloc}{parsed.path}"
        if parsed.query:
            normalized += f"?{parsed.query}"
        return normalized

    def extract_links(self, html: str, current_url: str) -> List[str]:
        """استخراج الروابط من HTML"""
        try:
            soup = BeautifulSoup(html, 'html.parser')
            links = []
            
            for link in soup.find_all('a', href=True):
                href = link['href']
                
                # تجاهل الروابط الخارجية وروابط JavaScript
                if (href.startswith('javascript:') or 
                    href.startswith('mailto:') or 
                    href.startswith('tel:') or
                    href.startswith('#') or
                    href.startswith('data:')):
                    continue
                
                full_url = urljoin(current_url, href)
                
                if self.is_same_domain(full_url):
                    normalized_url = self.normalize_url(full_url)
                    links.append(normalized_url)
            
            return list(set(links))  # إزالة التكرار
        except Exception as e:
            print(f"خطأ في استخراج الروابط من {current_url}: {e}")
            return []

    def detect_error(self, html: str, status_code: int) -> Optional[Dict[str, str]]:
        """اكتشاف أخطاء Laravel/PHP في المحتوى"""
        if status_code >= 400:
            # فحص أكواد الخطأ
            for pattern in self.error_patterns:
                if re.search(pattern, html, re.IGNORECASE):
                    return self.extract_error_details(html, pattern, status_code)
        
        # فحص محتوى الصفحة حتى لو كان كود الاستجابة 200
        for pattern in self.error_patterns:
            if re.search(pattern, html, re.IGNORECASE):
                return self.extract_error_details(html, pattern, status_code)
        
        return None

    def extract_error_details(self, html: str, matched_pattern: str, status_code: int) -> Dict[str, str]:
        """استخراج تفاصيل الخطأ"""
        try:
            soup = BeautifulSoup(html, 'html.parser')
            
            # البحث عن نوع الخطأ
            error_type = "Unknown Error"
            error_message = "No specific error message found"
            stack_trace_line = ""
            
            # البحث في العناوين
            headings = soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])
            for heading in headings:
                heading_text = heading.get_text().strip()
                if any(pattern in heading_text for pattern in ['Error', 'Exception', 'Fatal', 'Warning']):
                    error_type = heading_text
                    break
            
            # البحث عن رسالة الخطأ
            text_content = soup.get_text()
            
            # البحث عن رسائل محددة
            error_message_patterns = [
                r'Attempt to read property.*?(?=\n|\.)',
                r'Call to undefined.*?(?=\n|\.)',
                r'Class.*?not found.*?(?=\n|\.)',
                r'Method.*?does not exist.*?(?=\n|\.)',
                r'Fatal error:.*?(?=\n|\.)',
                r'Parse error:.*?(?=\n|\.)',
                r'Warning:.*?(?=\n|\.)',
                r'Notice:.*?(?=\n|\.)'
            ]
            
            for pattern in error_message_patterns:
                match = re.search(pattern, text_content, re.IGNORECASE)
                if match:
                    error_message = match.group(0).strip()
                    break
            
            # البحث عن Stack Trace
            stack_trace_pattern = r'## Stack Trace.*?\n(.*?)(?=\n\n|\Z)'
            stack_match = re.search(stack_trace_pattern, text_content, re.DOTALL | re.IGNORECASE)
            if stack_match:
                stack_trace_content = stack_match.group(1)
                lines = stack_trace_content.strip().split('\n')
                if lines:
                    stack_trace_line = lines[0].strip()
            
            return {
                'error_type': error_type,
                'error_message': error_message,
                'stack_trace_line': stack_trace_line
            }
            
        except Exception as e:
            return {
                'error_type': f"Error Pattern: {matched_pattern}",
                'error_message': f"Failed to parse error details: {e}",
                'stack_trace_line': ""
            }

    def crawl_page(self, url: str) -> List[str]:
        """فحص صفحة واحدة وإرجاع الروابط الموجودة فيها"""
        if url in self.visited_urls:
            return []
        
        self.visited_urls.add(url)
        print(f"Processing URL #{len(self.visited_urls)}: {url}")
        
        try:
            try:
                print(f"فحص {url}...", end=" ")
            except UnicodeEncodeError:
                print(f"Checking {url}...", end=" ")
            
            response = self.session.get(url, headers=self.headers, timeout=10)
            
            # فحص الأخطاء
            error_info = self.detect_error(response.text, response.status_code)
            
            if error_info:
                try:
                    print("خطأ موجود!")
                except UnicodeEncodeError:
                    print("Error found!")
                error_data = {
                    'url': url,
                    'status_code': str(response.status_code),
                    'error_type': error_info['error_type'],
                    'error_message': error_info['error_message'],
                    'stack_trace_line': error_info['stack_trace_line']
                }
                self.errors_found.append(error_data)
            else:
                print("OK")
            
            # استخراج الروابط
            links = self.extract_links(response.text, url)
            return links
            
        except requests.exceptions.RequestException as e:
            try:
                print(f"خطأ في الاتصال: {e}")
            except UnicodeEncodeError:
                print(f"Connection error: {e}")
            return []
        except Exception as e:
            try:
                print(f"خطأ غير متوقع: {e}")
            except UnicodeEncodeError:
                print(f"Unexpected error: {e}")
            return []

    def crawl_all(self):
        """الزحف عبر جميع الصفحات"""
        try:
            print(f"بدء فحص الموقع: {self.base_url}")
        except UnicodeEncodeError:
            print(f"Starting website scan: {self.base_url}")
        print("=" * 50)
        
        urls_to_visit = [self.base_url]
        
        while urls_to_visit and len(self.visited_urls) < self.max_pages:
            current_url = urls_to_visit.pop(0)
            
            if current_url in self.visited_urls:
                continue
            
            new_links = self.crawl_page(current_url)
            
            # إضافة الروابط الجديدة إلى قائمة الانتظار
            for link in new_links:
                if (link not in self.visited_urls and 
                    link not in urls_to_visit and 
                    len(self.visited_urls) < self.max_pages):
                    urls_to_visit.append(link)
            
            # طباعة التقدم
            remaining = len(urls_to_visit)
            visited = len(self.visited_urls)
            try:
                print(f"Progress: {visited} visited, {remaining} remaining")
            except UnicodeEncodeError:
                print(f"Progress: {visited} visited, {remaining} remaining")
            
            # إضافة تأخير صغير لتجنب الضغط على الخادم
            time.sleep(0.1)
        
        if len(self.visited_urls) >= self.max_pages:
            try:
                print(f"Reached maximum page limit: {self.max_pages}")
            except UnicodeEncodeError:
                print(f"Reached maximum page limit: {self.max_pages}")

    def save_to_csv(self, filename: str = 'laravel_error_report.csv'):
        """حفظ النتائج في ملف CSV"""
        try:
            with open(filename, 'w', newline='', encoding='utf-8') as csvfile:
                fieldnames = ['URL', 'Status Code', 'Error Type', 'Error Message', 'Stack Trace Line']
                writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
                
                writer.writeheader()
                for error in self.errors_found:
                    writer.writerow({
                        'URL': error['url'],
                        'Status Code': error['status_code'],
                        'Error Type': error['error_type'],
                        'Error Message': error['error_message'],
                        'Stack Trace Line': error['stack_trace_line']
                    })
            
            try:
                print(f"\nتم حفظ النتائج في: {filename}")
            except UnicodeEncodeError:
                print(f"\nResults saved to: {filename}")
            
        except Exception as e:
            try:
                print(f"خطأ في حفظ الملف: {e}")
            except UnicodeEncodeError:
                print(f"Error saving file: {e}")

    def print_summary(self):
        """طباعة ملخص النتائج"""
        print("\n" + "=" * 50)
        try:
            print("ملخص الفحص:")
            print(f"عدد الصفحات المفحوصة: {len(self.visited_urls)}")
            print(f"عدد الأخطاء المكتشفة: {len(self.errors_found)}")
            print("تم حفظ النتائج في: laravel_error_report.csv")
        except UnicodeEncodeError:
            print("Scan Summary:")
            print(f"Pages checked: {len(self.visited_urls)}")
            print(f"Errors found: {len(self.errors_found)}")
            print("Results saved to: laravel_error_report.csv")
        print("=" * 50)

def main():
    # إعدادات الموقع والكوكيز
    base_url = "http://127.0.0.1:8000/"
    
    cookies = [
        {
            "name": "laravel-session",
            "value": "eyJpdiI6IjFucHZGUktZYktxdS9jd29qZlJGTGc9PSIsInZhbHVlIjoiNjRLRVA5SXhad2Qyd05OSWVCQW5IMGYrQnlONUxDVFFhSVl1SVdnUXVFcFBzNjd0VjZEeXRIWCtFU25rZHBoOUFYVTRTT1hqQUJlMzhpN3QvalFhUUlWampzNUlUSkdKZFFoWVYrMElIa3o1ekdVTCt2QkV4T294WGJkdG5nNDMiLCJtYWMiOiJhZmJmZGUwZDZhNGI0MDhlOGQ4YjEzNmNmZjk2MDM2ZjgzMWJiMzJmNjdhZTE4NjQyZmQ5NzdmODdlM2NhODFmIiwidGFnIjoiIn0%3D",
            "domain": "127.0.0.1"
        },
        {
            "name": "sahara.sid",
            "value": "s%3AG1F-vnQfSjmOPc3e89i-KLD9UzmG3N-Z.2s7f%2FCaALS2KurLB4HmTDw9EarWhZMxDV4xqWjWSETw",
            "domain": "127.0.0.1"
        },
        {
            "name": "XSRF-TOKEN",
            "value": "eyJpdiI6IlB3akJPVDI5bW5qUnJxQkhnZHNySVE9PSIsInZhbHVlIjoicTV3STd3RkJESWd4TFRxTHo4VFFtUGYrR21PQkpwcElUeHArS21ndVlmUkRuYWVLY0hYanVLaHFNdDB1NE52UjZZQURBdzVxdjJZby9PWmdYYm9mQ3hKZFdTSGZpS1haN21qc1pLKzd3eHhKcmQvY0dUM0huZHUwOGthSkFGaGwiLCJtYWMiOiJmZTNhMWE4NTNhYjI5NWQwYjRjZjVmNzM2YzM4YzM4YmVlNmZhM2ZmNWUxMTFjODU2N2RiOTBkNDhjODZlOTAyIiwidGFnIjoiIn0%3D",
            "domain": "127.0.0.1"
        }
    ]
    
    # إنشاء وبدء عملية الزحف
    crawler = LaravelErrorCrawler(base_url, cookies, max_pages=30)
    crawler.crawl_all()
    crawler.save_to_csv()
    crawler.print_summary()

if __name__ == "__main__":
    main()
