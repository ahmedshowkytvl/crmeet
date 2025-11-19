# البحث عن التذاكر مع استبعاد Auto Close

## 📋 نظرة عامة

هذا الدليل يوضح كيفية البحث عن جميع التذاكر **ماعدا** التذاكر المغلقة تلقائيًا (Auto Close).

## 🔍 الطرق المتاحة

### 1. الطريقة الأساسية: Client-side Filtering

نظرًا لأن Zoho API لا يدعم `searchStr` parameter بشكل مباشر، أفضل طريقة هي جلب التذاكر ثم فلترتها في الكود:

```python
import requests
from config import ZohoConfig

def search_exclude_auto_close(access_token, org_id, limit=100):
    """البحث عن جميع التذاكر ماعدا Auto Close"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    # Get all tickets
    url = f"https://desk.zoho.com/api/v1/tickets"
    params = {
        'orgId': org_id,
        'limit': limit
    }
    
    response = requests.get(url, headers=headers, params=params)
    all_tickets = response.json().get('data', [])
    
    # Filter out Auto Close tickets
    filtered_tickets = [
        ticket for ticket in all_tickets
        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
    ]
    
    return filtered_tickets
```

### 2. البحث المتقدم مع فلاتر متعددة

يمكنك الجمع بين عدة فلاتر:

```python
def search_with_filters(access_token, org_id, status=None, department_id=None):
    """بحث متقدم مع استبعاد Auto Close"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    url = "https://desk.zoho.com/api/v1/tickets"
    params = {'orgId': org_id, 'limit': 100}
    
    # Add optional filters
    if status:
        params['status'] = status  # 'Open', 'Closed', etc.
    if department_id:
        params['departmentIds'] = department_id
    
    response = requests.get(url, headers=headers, params=params)
    all_tickets = response.json().get('data', [])
    
    # Filter out Auto Close
    filtered_tickets = [
        ticket for ticket in all_tickets
        if ticket.get('cf', {}).get('cf_closed_by') != 'Auto Close'
    ]
    
    return filtered_tickets
```

## 🎯 أمثلة عملية

### مثال 1: جلب جميع التذاكر (ماعدا Auto Close)

```python
from config import ZohoConfig
import requests

config = ZohoConfig()

# Get access token
token_data = {
    'refresh_token': config.REFRESH_TOKEN,
    'client_id': config.CLIENT_ID,
    'client_secret': config.CLIENT_SECRET,
    'grant_type': 'refresh_token'
}

token_response = requests.post(config.TOKEN_URL, data=token_data)
access_token = token_response.json()['access_token']

# Search excluding Auto Close
tickets = search_exclude_auto_close(access_token, config.ORG_ID, limit=100)

print(f"Found {len(tickets)} tickets (excluding Auto Close)")
for ticket in tickets[:5]:  # Show first 5
    print(f"#{ticket['ticketNumber']}: {ticket['subject']}")
    print(f"  Status: {ticket['status']}")
    print(f"  CF Closed By: {ticket.get('cf', {}).get('cf_closed_by', 'N/A')}")
```

### مثال 2: جلب التذاكر المغلقة (ماعدا Auto Close)

```python
# Get only Closed tickets (excluding Auto Close)
tickets = search_with_filters(
    access_token, 
    config.ORG_ID, 
    status='Closed'
)

print(f"Closed tickets (excluding Auto Close): {len(tickets)}")
```

### مثال 3: جلب تذاكر قسم محدد (ماعدا Auto Close)

```python
# Get tickets from specific department (excluding Auto Close)
tickets = search_with_filters(
    access_token, 
    config.ORG_ID, 
    department_id='766285000016070029'  # Contracting - KSA
)

print(f"Department tickets (excluding Auto Close): {len(tickets)}")
```

## 📊 نتائج الاختبار

```
=== Alternative Method: Client-side filtering ===
Total tickets: 20
After filtering (excluding Auto Close): 20
Auto Close tickets filtered out: 0

=== Filtered Tickets (First 10) ===
1. #2838992: Urgent - Failed booking
   Status: Open
   CF Closed By: N/A

2. #2838487: EET Global Webservice
   Status: Open
   CF Closed By: N/A

3. #2838991: Changes in the extranet
   Status: Closed
   CF Closed By: N/A
```

## 🔧 الفلاتر المتاحة

### معاملات API الأساسية
- `limit`: عدد التذاكر (1-100)
- `status`: حالة التذكرة (Open, Closed, In Progress, etc.)
- `departmentIds`: معرف القسم
- `from`: الفهرس للبداية (pagination)
- `sortBy`: ترتيب النتائج (-modifiedTime, -createdTime)

### فلترة Custom Fields في الكود
```python
# Filter by cf_closed_by
tickets = [t for t in all_tickets 
           if t.get('cf', {}).get('cf_closed_by') != 'Auto Close']

# Filter by multiple CF conditions
tickets = [t for t in all_tickets 
           if t.get('cf', {}).get('cf_closed_by') != 'Auto Close'
           and t.get('cf', {}).get('cf_priority') == 'High']
```

## ⚠️ ملاحظات مهمة

1. **API Limitation**: 
   - Zoho Desk API لا يدعم `searchStr` parameter مباشرة
   - يجب استخدام client-side filtering للحقول المخصصة (CF)

2. **Performance**:
   - للبحث في عدد كبير من التذاكر، استخدم pagination
   - اجلب البيانات على دفعات (batches) من 100 تذكرة

3. **CF Field Format**:
   - الحقول المخصصة تكون في `ticket['cf']` object
   - استخدم `.get()` لتجنب errors عند عدم وجود القيمة

4. **Rate Limiting**:
   - أضف `time.sleep(0.1)` بين الطلبات
   - راقب response headers لمعرفة حد الطلبات

## 🚀 مثال كامل مع Pagination

```python
def get_all_tickets_exclude_auto_close(access_token, org_id):
    """جلب جميع التذاكر مع pagination واستبعاد Auto Close"""
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    all_filtered_tickets = []
    from_index = 0
    limit = 100
    
    while True:
        url = f"https://desk.zoho.com/api/v1/tickets"
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
        
        # Filter out Auto Close
        filtered = [
            t for t in tickets
            if t.get('cf', {}).get('cf_closed_by') != 'Auto Close'
        ]
        
        all_filtered_tickets.extend(filtered)
        
        # Check if more pages exist
        if len(tickets) < limit:
            break
            
        from_index += limit
        
        # Rate limiting
        import time
        time.sleep(0.1)
    
    return all_filtered_tickets

# Usage
tickets = get_all_tickets_exclude_auto_close(access_token, config.ORG_ID)
print(f"Total tickets (excluding Auto Close): {len(tickets)}")
```

## 📝 قيم CF Closed By المحتملة

- `"Auto Close"` - تم الإغلاق تلقائيًا
- `"System Admin"` - تم الإغلاق بواسطة المسؤول
- `"Agent Name"` - تم الإغلاق بواسطة موظف
- `null` أو غير موجود - لم يتم الإغلاق

## 🔗 ملفات ذات صلة

- `search_exclude_auto_close.py` - المثال الكامل
- `ZOHO_API_DOCUMENTATION.md` - التوثيق الكامل للـ API
- `DEPARTMENT_SEARCH_README.md` - البحث حسب القسم
- `config.py` - إعدادات Zoho API

