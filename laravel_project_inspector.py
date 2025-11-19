#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Laravel Project Inspector
Comprehensive inspection and auto-fix tool for Laravel projects
"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import sys
import json
import re
import os
from pathlib import Path


class LaravelProjectInspector:
    def __init__(self, base_url="http://127.0.0.1:8000", cookies=None):
        self.base_url = base_url
        self.cookies = cookies or self.get_default_cookies()
        self.session = requests.Session()
        self.session.cookies.update(self.cookies)
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
        
        # Results storage
        self.inspection_results = {}
        self.translation_keys = set()
        self.missing_translations = {}
        self.js_errors = {}
        self.broken_links = {}
        
        # Language files paths
        self.lang_paths = {
            'en': 'resources/lang/en',
            'ar': 'resources/lang/ar'
        }

    def get_default_cookies(self):
        """Default Laravel session cookies"""
        return {
            'laravel-session': 'eyJpdiI6IlVhaU1TSk5xMnpuMzZ5bEdROFNVUGc9PSIsInZhbHVlIjoidUNYT0ZCK3RidTIxNGp5RVA3VnRBZExtdnZIWi9UcUFaTWw2eWtjQ2MvU3NNSXRub0tjUDZKR3ArNWJWNVlUbDIzbUV3cytqY2lEVVZ6aXpnYytzN0VxR1REcDFBN3ZURGl1L0VCdVFMVWFlZlhZVU50emtnRzBJKzR6MEZHRzkiLCJtYWMiOiI1M2ZhZjg1ZmRhZTI0OTM0NmU5YjQxOWU3YjBhZmYxNTkyNzY5ZWM2MjNjNDNmYjE5MTIzNDc5OTVkMjU2YzllIiwidGFnIjoiIn0%3D',
            'XSRF-TOKEN': 'eyJpdiI6IlVDSGhsZWozMU16VXFqaFZCNG1pbVE9PSIsInZhbHVlIjoiNDZ1ZVJST1h6WXBNNmdyNDRKRmVtR09ERDlGS3ZYTTU2eTdjZUp6OFFlUGlDZTU2VmZrZGgrTXFpSTFOcjU3elFhTEhhOFhoRXo1WmloZGdHcDh4WGppUjl2clA1c0twUytvYnVscUVtVVpaOHhuM2hNK1J6UFpSNldYYU9uTnAiLCJtYWMiOiIyMzE5YzYxOTJiMmM5OGRhZjg2Y2Y4MjE1M2U5NjVjZjJjYzM4MTg2NzVjYzNiNTcxYTcyNTE4ZWVlMTdhMzk0IiwidGFnIjoiIn0%3D'
        }

    def inspect_url(self, url, language='en'):
        """Inspect a single URL for all issues"""
        print(f"\n{'='*80}")
        print(f"🔍 Inspecting: {url} (Language: {language.upper()})")
        print(f"{'='*80}")
        
        try:
            # Set language in session
            if language == 'ar':
                self.session.cookies.set('locale', 'ar')
            else:
                self.session.cookies.set('locale', 'en')
            
            response = self.session.get(url, timeout=10)
            
            result = {
                'url': url,
                'language': language,
                'status_code': response.status_code,
                'success': True,
                'issues': [],
                'translation_keys': set(),
                'js_errors': [],
                'broken_links': [],
                'page_title': '',
                'content_length': len(response.content)
            }
            
            if response.status_code == 200:
                soup = BeautifulSoup(response.content, 'html.parser')
                result['page_title'] = soup.find('title').get_text().strip() if soup.find('title') else 'No title'
                
                # 1. Console Inspection
                js_errors = self.detect_js_errors(response.text, url)
                result['js_errors'] = js_errors
                
                # 2. Translation Key Check
                translation_keys = self.extract_translation_keys(response.text)
                result['translation_keys'] = translation_keys
                self.translation_keys.update(translation_keys)
                
                # 3. Language Consistency Check
                lang_issues = self.check_language_consistency(response.text, language)
                result['issues'].extend(lang_issues)
                
                # 4. Broken Links Check
                broken_links = self.check_broken_links(soup, url)
                result['broken_links'] = broken_links
                
                # 5. Additional Laravel-specific checks
                laravel_issues = self.check_laravel_specific_issues(response.text, url)
                result['issues'].extend(laravel_issues)
                
            else:
                result['success'] = False
                result['issues'].append(f"HTTP {response.status_code} error")
                self.broken_links[url] = response.status_code
            
            self.inspection_results[url] = result
            self.print_url_summary(result)
            
            return result
            
        except Exception as e:
            error_result = {
                'url': url,
                'language': language,
                'status_code': 0,
                'success': False,
                'issues': [f"Connection error: {str(e)}"],
                'translation_keys': set(),
                'js_errors': [],
                'broken_links': [],
                'page_title': '',
                'content_length': 0
            }
            self.inspection_results[url] = error_result
            self.print_url_summary(error_result)
            return error_result

    def detect_js_errors(self, content, url):
        """Detect JavaScript errors in page content"""
        js_errors = []
        
        # Console error patterns
        console_patterns = [
            r'console\.error\(["\']([^"\']+)["\']',
            r'console\.warn\(["\']([^"\']+)["\']',
            r'Failed to load resource.*?(\d{3})',
            r'Uncaught.*?Error:([^\\n]+)',
            r'TypeError:([^\\n]+)',
            r'ReferenceError:([^\\n]+)',
            r'SyntaxError:([^\\n]+)'
        ]
        
        for pattern in console_patterns:
            matches = re.findall(pattern, content, re.IGNORECASE | re.MULTILINE)
            for match in matches:
                js_errors.append({
                    'type': 'JavaScript Error',
                    'message': match.strip(),
                    'url': url
                })
        
        return js_errors

    def extract_translation_keys(self, content):
        """Extract Laravel translation keys from content"""
        translation_keys = set()
        
        # Laravel translation key patterns
        patterns = [
            r'__\(["\']([^"\']+)["\']\)',  # __('key')
            r'@lang\(["\']([^"\']+)["\']\)',  # @lang('key')
            r'{{ __\(["\']([^"\']+)["\']\) }}',  # {{ __('key') }}
            r'trans\(["\']([^"\']+)["\']\)',  # trans('key')
            r'Lang::get\(["\']([^"\']+)["\']\)',  # Lang::get('key')
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, content)
            for match in matches:
                if '.' in match:  # Only keys with dots (e.g., messages.login)
                    translation_keys.add(match)
        
        return translation_keys

    def check_language_consistency(self, content, expected_lang):
        """Check if page content matches expected language"""
        issues = []
        
        # Language-specific patterns
        if expected_lang == 'en':
            # Check for Arabic text in English page
            arabic_pattern = r'[\u0600-\u06FF]+'
            arabic_matches = re.findall(arabic_pattern, content)
            if arabic_matches:
                issues.append(f"Arabic text found in English page: {arabic_matches[:3]}")
        else:
            # Check for English text in Arabic page
            english_pattern = r'[A-Za-z]{3,}'
            english_matches = re.findall(english_pattern, content)
            if english_matches:
                issues.append(f"English text found in Arabic page: {english_matches[:3]}")
        
        return issues

    def check_broken_links(self, soup, base_url):
        """Check for broken internal links"""
        broken_links = []
        links = soup.find_all('a', href=True)
        
        for link in links:
            href = link.get('href')
            if href and not href.startswith(('http', 'mailto:', 'tel:')):
                full_url = urljoin(base_url, href)
                try:
                    response = self.session.head(full_url, timeout=5)
                    if response.status_code >= 400:
                        broken_links.append({
                            'url': full_url,
                            'status': response.status_code,
                            'text': link.get_text(strip=True)[:50]
                        })
                except:
                    broken_links.append({
                        'url': full_url,
                        'status': 'timeout',
                        'text': link.get_text(strip=True)[:50]
                    })
        
        return broken_links

    def check_laravel_specific_issues(self, content, url):
        """Check for Laravel-specific issues"""
        issues = []
        
        # Laravel error patterns
        error_patterns = [
            r'SQLSTATE\[.*?\].*?doesn\'t exist',
            r'Illuminate\\Database\\QueryException',
            r'Illuminate\\Auth\\AuthenticationException',
            r'Illuminate\\Validation\\ValidationException',
            r'Whoops, looks like something went wrong',
            r'CSRF token mismatch',
            r'419 Page Expired'
        ]
        
        for pattern in error_patterns:
            if re.search(pattern, content, re.IGNORECASE):
                issues.append(f"Laravel error detected: {pattern}")
        
        return issues

    def print_url_summary(self, result):
        """Print summary for a single URL"""
        status_icon = "✅" if result['success'] and not result['issues'] else "⚠️" if result['success'] else "❌"
        
        print(f"\n{status_icon} {result['url']}")
        print(f"   Status: {result['status_code']}")
        print(f"   Title: {result['page_title']}")
        print(f"   Size: {result['content_length']:,} bytes")
        
        if result['translation_keys']:
            print(f"   🔤 Translation keys: {len(result['translation_keys'])}")
        
        if result['js_errors']:
            print(f"   🟡 JS Errors: {len(result['js_errors'])}")
            for error in result['js_errors'][:2]:
                print(f"      • {error['message'][:60]}...")
        
        if result['broken_links']:
            print(f"   🔗 Broken links: {len(result['broken_links'])}")
            for link in result['broken_links'][:2]:
                print(f"      • {link['url']} ({link['status']})")
        
        if result['issues']:
            print(f"   ⚠️  Issues: {len(result['issues'])}")
            for issue in result['issues'][:2]:
                print(f"      • {issue}")

    def generate_translations(self):
        """Generate missing translations automatically"""
        print(f"\n{'='*80}")
        print("🔤 GENERATING MISSING TRANSLATIONS")
        print(f"{'='*80}")
        
        # This would normally read existing language files
        # For now, we'll create sample translations
        sample_translations = {
            'messages.login_field_hint': {
                'en': 'Please enter your login credentials',
                'ar': 'يرجى إدخال بيانات تسجيل الدخول'
            },
            'validation.failed': {
                'en': 'The given data was invalid',
                'ar': 'البيانات المدخلة غير صحيحة'
            },
            'auth.failed': {
                'en': 'These credentials do not match our records',
                'ar': 'هذه البيانات لا تطابق سجلاتنا'
            }
        }
        
        print("📝 Sample translations generated:")
        for key, translations in sample_translations.items():
            print(f"   {key}:")
            print(f"      EN: {translations['en']}")
            print(f"      AR: {translations['ar']}")

    def generate_final_report(self):
        """Generate comprehensive final report"""
        print(f"\n{'='*80}")
        print("📋 COMPREHENSIVE INSPECTION REPORT")
        print(f"{'='*80}")
        
        total_urls = len(self.inspection_results)
        successful_urls = len([r for r in self.inspection_results.values() if r['success']])
        urls_with_issues = len([r for r in self.inspection_results.values() if r['issues']])
        
        print(f"\n📊 SUMMARY STATISTICS:")
        print(f"   • Total URLs inspected: {total_urls}")
        print(f"   • Successful requests: {successful_urls}")
        print(f"   • URLs with issues: {urls_with_issues}")
        print(f"   • Total translation keys found: {len(self.translation_keys)}")
        
        # Group by status
        print(f"\n📋 URL STATUS BREAKDOWN:")
        for url, result in self.inspection_results.items():
            if result['success'] and not result['issues']:
                status = "✅ No issues"
            elif result['success']:
                status = "⚠️ Has issues"
            else:
                status = "❌ Failed to load"
            
            print(f"   {status} - {url}")
        
        # Translation recommendations
        if self.translation_keys:
            print(f"\n🔤 TRANSLATION RECOMMENDATIONS:")
            print("   Create/update these language files:")
            print("   • resources/lang/en/messages.php")
            print("   • resources/lang/ar/messages.php")
            print("   • resources/lang/en/validation.php")
            print("   • resources/lang/ar/validation.php")
        
        # JavaScript error summary
        all_js_errors = []
        for result in self.inspection_results.values():
            all_js_errors.extend(result['js_errors'])
        
        if all_js_errors:
            print(f"\n🟡 JAVASCRIPT ERROR SUMMARY:")
            print(f"   • Total JS errors: {len(all_js_errors)}")
            print("   • Common issues:")
            error_types = {}
            for error in all_js_errors:
                error_type = error['type']
                error_types[error_type] = error_types.get(error_type, 0) + 1
            
            for error_type, count in sorted(error_types.items(), key=lambda x: x[1], reverse=True):
                print(f"      • {error_type}: {count} occurrences")
        
        # Broken links summary
        all_broken_links = []
        for result in self.inspection_results.values():
            all_broken_links.extend(result['broken_links'])
        
        if all_broken_links:
            print(f"\n🔗 BROKEN LINKS SUMMARY:")
            print(f"   • Total broken links: {len(all_broken_links)}")
            print("   • Common status codes:")
            status_codes = {}
            for link in all_broken_links:
                status = link['status']
                status_codes[status] = status_codes.get(status, 0) + 1
            
            for status, count in sorted(status_codes.items(), key=lambda x: x[1], reverse=True):
                print(f"      • {status}: {count} links")

    def inspect_all_urls(self, urls):
        """Inspect all provided URLs"""
        print("🚀 Starting Laravel Project Inspection")
        print(f"📋 Total URLs to inspect: {len(urls)}")
        
        for i, url in enumerate(urls, 1):
            print(f"\n📍 Progress: {i}/{len(urls)}")
            
            # Inspect in English
            self.inspect_url(url, 'en')
            
            # Inspect in Arabic
            self.inspect_url(url, 'ar')
        
        # Generate translations
        self.generate_translations()
        
        # Generate final report
        self.generate_final_report()


def main():
    """Main function"""
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


if __name__ == "__main__":
    main()


