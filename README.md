# Advanced Web Crawler

A comprehensive Python web crawler that automatically discovers and crawls sub-links with cookie support.

## Features

- 🚀 **Recursive Crawling** - Automatically follows internal links to discover all pages
- 🍪 **Cookie Support** - Full support for authenticated sessions
- 📊 **Comprehensive Analysis** - Link, image, and form discovery on every page
- 🧪 **Multiple Testing Modes** - Single site, multiple sites, and deep crawling
- 📝 **Detailed Reporting** - Complete statistics and page-by-page analysis
- 🔍 **Smart Filtering** - Only crawls internal links (same domain)
- ⚡ **Configurable Depth** - Control how deep the crawler goes
- 🚨 **Error Detection** - Automatically detects Laravel and JavaScript errors
- 🔴 **Laravel Error Detection** - Database, authentication, validation, and exception errors
- 🟡 **JavaScript Error Detection** - Console errors, AJAX failures, and syntax errors

## Installation

1. Install required packages:
```bash
pip install -r requirements.txt
```

## Usage

### Command Line

```bash
# Basic crawl with default cookies (depth 2)
python quick_web_crawler_test.py https://127.0.0.1:8000

# Custom depth crawl
python quick_web_crawler_test.py https://127.0.0.1:8000 cookies.json 3

# Interactive menu
python quick_web_crawler_test.py
```

### Interactive Menu

Run without arguments to access the interactive menu:

1. **Test single site (with sub-links)** - Crawl a website and all its internal pages
2. **Test multiple sites** - Test predefined test sites
3. **Test with custom cookies file** - Load cookies from JSON file
4. **Deep crawl (depth 3+)** - Advanced crawling for large sites
5. **Exit** - Quit the program

## Crawling Depth

- **Depth 1**: Only the starting page
- **Depth 2**: Starting page + all pages linked from it (default)
- **Depth 3+**: Recursive crawling through multiple levels

## Cookie Format

The crawler supports cookies in JSON format. Example:

```json
[
    {
        "domain": "127.0.0.1",
        "name": "laravel-session",
        "value": "session_value_here",
        "path": "/",
        "httpOnly": true,
        "secure": false
    }
]
```

## Default Cookies

The script includes default Laravel session cookies for localhost testing:
- `laravel-session`
- `XSRF-TOKEN`

## Output Example

```
╔══════════════════════════════════════════════════════════╗
║              🚀 Advanced Web Crawler                     ║
╚══════════════════════════════════════════════════════════╝

🔍 Starting URL: http://127.0.0.1:8000
📊 Max depth: 2

⏳ Starting comprehensive crawl...
⏳ Crawling: http://127.0.0.1:8000
✓ Found 20 internal links (no errors)
  ⏳ Crawling: http://127.0.0.1:8000/dashboard
  ⚠️  Found 2 errors!
     🔴 Laravel - Database Errors
        • SQLSTATE[42S02]: Base table or view doesn't exist...
     🔴 JavaScript - Console Errors
        • Failed to load resource: 404 (Not Found)
  ⏳ Crawling: http://127.0.0.1:8000/users
  ✓ Found 8 internal links (no errors)

================================================================================
📋 CRAWLING RESULTS SUMMARY
================================================================================
✅ Total pages crawled: 25
🍪 Cookies used: 2

📄 DISCOVERED PAGES:
================================================================================

1. http://127.0.0.1:8000
   📄 Title: Dashboard
   🔗 Internal links: 20
   🖼️  Images: 5
   📝 Forms: 2
   📊 Size: 45,230 bytes
   ✅ No errors found

2. http://127.0.0.1:8000/dashboard
   📄 Title: Dashboard
   🔗 Internal links: 15
   🖼️  Images: 3
   📝 Forms: 1
   📊 Size: 38,450 bytes
   ⚠️  Errors: 2
      🔴 Laravel - Database Errors
         • SQLSTATE[42S02]: Base table or view doesn't exist...

🚨 ERROR SUMMARY:
================================================================================

🔴 Laravel Errors (3):
   • Database Errors: 2 errors
   • Authentication Errors: 1 errors

🟡 JavaScript Errors (2):
   • Console Errors: 2 errors

📋 Most Common Errors:
   • Laravel - Database Errors: 2 occurrences
   • JavaScript - Console Errors: 2 occurrences
   • Laravel - Authentication Errors: 1 occurrences

🔗 ALL UNIQUE INTERNAL LINKS FOUND:
================================================================================
  1. http://127.0.0.1:8000/chat
      Found on: http://127.0.0.1:8000
  2. http://127.0.0.1:8000/dashboard
      Found on: http://127.0.0.1:8000
  3. http://127.0.0.1:8000/users
      Found on: http://127.0.0.1:8000

================================================================================
📊 FINAL STATISTICS:
   • Total pages visited: 25
   • Total unique internal links: 45
   • Total errors found: 5
   • Crawl depth: 2
================================================================================
```

## Advanced Features

### Smart Link Filtering
- Only crawls internal links (same domain)
- Avoids infinite loops with visited page tracking
- Respects crawl depth limits

### Comprehensive Data Collection
- Page titles and metadata
- Link counts and destinations
- Image and form statistics
- Response codes and page sizes
- Source page tracking for each link
- Real-time error detection and reporting

### Error Detection System
- **Laravel Errors**: Database exceptions, authentication issues, validation errors
- **JavaScript Errors**: Console errors, AJAX failures, syntax errors
- **Pattern Matching**: Advanced regex patterns for error detection
- **Categorized Reporting**: Errors grouped by type and frequency
- **Real-time Alerts**: Immediate error notification during crawling

### Error Handling
- Connection errors with helpful messages
- Timeout handling
- HTTP error management
- Graceful failure recovery

## Requirements

- Python 3.6+
- requests
- beautifulsoup4
- lxml (optional, for better HTML parsing)

## Use Cases

- **Website Auditing** - Complete site structure analysis
- **SEO Analysis** - Internal linking structure review
- **Security Testing** - Authentication and session testing
- **Content Discovery** - Finding all accessible pages
- **Site Mapping** - Creating comprehensive site maps