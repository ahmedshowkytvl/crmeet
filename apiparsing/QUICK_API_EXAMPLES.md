# أمثلة سريعة - Zoho Desk API Requests

## 🚀 طلبات API جاهزة للاستخدام

### 1️⃣ جلب جميع التذاكر (ماعدا Auto Close)

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets?orgId={orgId}&limit=100

# Headers
Authorization: Zoho-oauthtoken {access_token}
orgId: {orgId}
contentType: application/json; charset=utf-8
```

```python
# Python Code
import requests

url = "https://desk.zoho.com/api/v1/tickets"
headers = {
    "Authorization": f"Zoho-oauthtoken {access_token}",
    "orgId": org_id,
    "contentType": "application/json; charset=utf-8"
}
params = {'orgId': org_id, 'limit': 100}

response = requests.get(url, headers=headers, params=params)
all_tickets = response.json()['data']

# Filter out Auto Close
filtered = [t for t in all_tickets 
            if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']
```

---

### 2️⃣ جلب تذاكر قسم محدد (ماعدا Auto Close)

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets?orgId={orgId}&departmentIds=766285000016070029&limit=100

# Headers
Authorization: Zoho-oauthtoken {access_token}
orgId: {orgId}
```

```python
# Python Code
url = "https://desk.zoho.com/api/v1/tickets"
params = {
    'orgId': org_id,
    'departmentIds': '766285000016070029',  # Contracting - KSA
    'limit': 100
}

response = requests.get(url, headers=headers, params=params)
all_tickets = response.json()['data']

# Filter out Auto Close
filtered = [t for t in all_tickets 
            if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']
```

---

### 3️⃣ جلب التذاكر المغلقة فقط (ماعدا Auto Close)

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets?orgId={orgId}&status=Closed&limit=100

# Headers
Authorization: Zoho-oauthtoken {access_token}
orgId: {orgId}
```

```python
# Python Code
url = "https://desk.zoho.com/api/v1/tickets"
params = {
    'orgId': org_id,
    'status': 'Closed',
    'limit': 100
}

response = requests.get(url, headers=headers, params=params)
all_tickets = response.json()['data']

# Filter out Auto Close
filtered = [t for t in all_tickets 
            if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']

print(f"Total Closed tickets: {len(all_tickets)}")
print(f"Excluding Auto Close: {len(filtered)}")
```

---

### 4️⃣ جلب التذاكر المفتوحة (Open)

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets?orgId={orgId}&status=Open&limit=100
```

```python
# Python Code
params = {
    'orgId': org_id,
    'status': 'Open',
    'limit': 100
}

response = requests.get(url, headers=headers, params=params)
open_tickets = response.json()['data']
```

---

### 5️⃣ جلب تفاصيل تذكرة محددة

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets/{ticket_id}?orgId={orgId}

# Headers
Authorization: Zoho-oauthtoken {access_token}
orgId: {orgId}
```

```python
# Python Code
ticket_id = "766285000467993175"
url = f"https://desk.zoho.com/api/v1/tickets/{ticket_id}"
params = {'orgId': org_id}

response = requests.get(url, headers=headers, params=params)
ticket_details = response.json()

print(f"Ticket #{ticket_details['ticketNumber']}")
print(f"Subject: {ticket_details['subject']}")
print(f"Status: {ticket_details['status']}")
print(f"CF Closed By: {ticket_details.get('cf', {}).get('cf_closed_by', 'N/A')}")
```

---

### 6️⃣ البحث بحسب التاريخ

```bash
# API Request
GET https://desk.zoho.com/api/v1/tickets/search?from=0&limit=100&sortBy=-modifiedTime&modifiedTimeRange=2025-10-08T00:00:00.000Z,2025-10-08T23:59:59.000Z

# Headers
Authorization: Zoho-oauthtoken {access_token}
orgId: {orgId}
```

```python
# Python Code
from datetime import datetime

today = datetime.now()
from_date = today.strftime('%Y-%m-%dT00:00:00.000Z')
to_date = today.strftime('%Y-%m-%dT23:59:59.000Z')

search_url = "https://desk.zoho.com/api/v1/tickets/search"
params = f"from=0&limit=100&sortBy=-modifiedTime&modifiedTimeRange={from_date},{to_date}"

response = requests.get(f"{search_url}?{params}", headers=headers)
tickets = response.json()['data']
```

---

### 7️⃣ فلترة متعددة: قسم محدد + حالة محددة (ماعدا Auto Close)

```python
# Python Code - Multiple Filters
def advanced_search(access_token, org_id, department_id, status, exclude_auto_close=True):
    url = "https://desk.zoho.com/api/v1/tickets"
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    params = {
        'orgId': org_id,
        'departmentIds': department_id,  # e.g., '766285000016070029'
        'status': status,                # e.g., 'Closed'
        'limit': 100
    }
    
    response = requests.get(url, headers=headers, params=params)
    all_tickets = response.json().get('data', [])
    
    if exclude_auto_close:
        filtered = [t for t in all_tickets 
                   if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']
        return filtered
    
    return all_tickets

# Usage
tickets = advanced_search(
    access_token=access_token,
    org_id=org_id,
    department_id='766285000016070029',  # Contracting - KSA
    status='Closed',
    exclude_auto_close=True
)

print(f"Found {len(tickets)} Closed tickets in Contracting-KSA (excluding Auto Close)")
```

---

## 🔑 معرفات الأقسام المعروفة

```python
DEPARTMENT_IDS = {
    'general': '766285000006092035',
    'contracting_ksa': '766285000016070029',
    'support': '766285000016070030'
}
```

---

## 📊 حالات التذاكر المتاحة

```python
TICKET_STATUSES = [
    'Open',
    'Closed',
    'On Hold',
    'In Progress',
    'Escalated'
]
```

---

## 🎯 مثال كامل مع Pagination

```python
def get_all_tickets_with_pagination(access_token, org_id, exclude_auto_close=True):
    """جلب جميع التذاكر مع pagination"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    all_filtered_tickets = []
    from_index = 0
    limit = 100
    
    while True:
        url = "https://desk.zoho.com/api/v1/tickets"
        params = {
            'orgId': org_id,
            'from': from_index,
            'limit': limit
        }
        
        response = requests.get(url, headers=headers, params=params)
        data = response.json()
        tickets = data.get('data', [])
        
        if not tickets:
            break
        
        # Filter Auto Close if needed
        if exclude_auto_close:
            filtered = [t for t in tickets 
                       if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']
        else:
            filtered = tickets
        
        all_filtered_tickets.extend(filtered)
        
        print(f"Fetched {len(tickets)} tickets, filtered to {len(filtered)} (total: {len(all_filtered_tickets)})")
        
        if len(tickets) < limit:
            break
        
        from_index += limit
        
        # Rate limiting
        import time
        time.sleep(0.1)
    
    return all_filtered_tickets

# Usage
all_tickets = get_all_tickets_with_pagination(access_token, org_id)
print(f"\nTotal tickets (excluding Auto Close): {len(all_tickets)}")
```

---

## 🚀 تشغيل الأمثلة

```bash
# تشغيل مثال البحث مع استبعاد Auto Close
python search_exclude_auto_close.py

# تشغيل مثال البحث حسب القسم
python search_tickets_by_department.py

# تشغيل مثال جلب تفاصيل التذاكر
python ticket_api_example.py
```

---

## 📚 ملفات التوثيق

1. **`ZOHO_API_DOCUMENTATION.md`** - التوثيق الكامل
2. **`EXCLUDE_AUTO_CLOSE_README.md`** - دليل استبعاد Auto Close
3. **`DEPARTMENT_SEARCH_README.md`** - دليل البحث حسب القسم
4. **`QUICK_API_EXAMPLES.md`** - هذا الملف (أمثلة سريعة)

---

## ⚡ نصائح الأداء

1. **Pagination**: استخدم `from` و `limit` للصفحات الكبيرة
2. **Rate Limiting**: أضف `time.sleep(0.1)` بين الطلبات
3. **Caching**: احفظ النتائج المتكررة (departments, users)
4. **Batch Processing**: عالج التذاكر على دفعات من 100

---

## 🔧 استكشاف الأخطاء

### خطأ 401 (Unauthorized)
```python
# تحقق من Access Token
print(f"Token: {access_token[:20]}...")  # طباعة أول 20 حرف فقط

# تجديد Token
token_response = requests.post(config.TOKEN_URL, data=token_data)
access_token = token_response.json()['access_token']
```

### خطأ 422 (Unprocessable Entity)
```python
# تحقق من المعاملات
print(f"Params: {params}")
print(f"URL: {url}")
```

### Rate Limiting
```python
# أضف تأخير بين الطلبات
import time
time.sleep(0.2)  # 200ms delay
```

