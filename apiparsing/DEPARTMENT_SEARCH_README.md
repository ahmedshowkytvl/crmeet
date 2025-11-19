# البحث عن التذاكر حسب القسم - Zoho Desk API

## 📋 نظرة عامة

هذا الدليل يوضح كيفية البحث عن التذاكر من قسم محدد باستخدام Zoho Desk API.

## 🔍 طرق البحث

### 1. الطريقة الأولى: استخدام `departmentIds` parameter

```python
import requests

def search_tickets_by_department(access_token, org_id, department_id, limit=100):
    url = "https://desk.zoho.com/api/v1/tickets"
    
    params = {
        'orgId': org_id,
        'departmentIds': department_id,  # معامل القسم
        'limit': limit
    }
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(url, headers=headers, params=params)
    return response.json()
```

### 2. الطريقة الثانية: استخدام search endpoint مع filter

```python
def search_tickets_by_department_search(access_token, org_id, department_id):
    search_url = "https://desk.zoho.com/api/v1/tickets/search"
    
    # بناء معاملات البحث مع فلتر القسم
    params = f"from=0&limit=100&sortBy=-createdTime&filter=departmentId:{department_id}"
    
    headers = {
        "Authorization": f"Zoho-oauthtoken {access_token}",
        "orgId": org_id,
        "contentType": "application/json; charset=utf-8"
    }
    
    response = requests.get(f"{search_url}?{params}", headers=headers)
    return response.json()
```

## 🎯 مثال عملي

```python
# مثال كامل
from config import ZohoConfig
import requests

config = ZohoConfig()

# الحصول على Access Token
token_data = {
    'refresh_token': config.REFRESH_TOKEN,
    'client_id': config.CLIENT_ID,
    'client_secret': config.CLIENT_SECRET,
    'grant_type': 'refresh_token'
}

token_response = requests.post(config.TOKEN_URL, data=token_data)
access_token = token_response.json()['access_token']

# البحث عن التذاكر من قسم محدد
department_id = "766285000016070029"  # Contracting - KSA
tickets = search_tickets_by_department(access_token, config.ORG_ID, department_id)

print(f"تم العثور على {len(tickets['data'])} تذكرة")
for ticket in tickets['data']:
    print(f"#{ticket['ticketNumber']}: {ticket['subject']} - {ticket['status']}")
```

## 📊 النتائج من الاختبار

```
=== Search tickets from department 766285000016070029 ===
Found 5 tickets in department 766285000016070029

=== Available Tickets ===
1. #2838124: 13 OCT 2025
   Status: Open
   Email: reservation@madareemcrown.com
   Department ID: 766285000016070029

2. #2838189: Changes in the extranet
   Status: Closed
   Email: operation@etg.sa
   Department ID: 766285000016070029
```

## 🔧 المعاملات المتاحة

### departmentIds parameter
- **النوع**: String
- **الوصف**: معرف القسم المطلوب البحث فيه
- **المثال**: `"766285000016070029"`

### معاملات إضافية
- `limit`: عدد التذاكر المطلوب جلبها (1-100)
- `from`: الفهرس للبداية (للصفحات)
- `sortBy`: ترتيب النتائج (-createdTime, -modifiedTime)

## 📝 ملاحظات مهمة

1. **معرفات الأقسام المعروفة**:
   - `766285000006092035` - General Department
   - `766285000016070029` - Contracting - KSA
   - `766285000016070030` - Support Department

2. **الصلاحيات المطلوبة**:
   - `Desk.tickets.READ`
   - `Desk.departments.READ`

3. **Rate Limiting**:
   - Zoho API له حدود على عدد الطلبات
   - استخدم `time.sleep()` بين الطلبات المتتالية

## 🚀 كيفية التشغيل

```bash
python search_tickets_by_department.py
```

## 📚 ملفات ذات صلة

- `ZOHO_API_DOCUMENTATION.md` - التوثيق الكامل
- `ticket_api_example.py` - أمثلة أخرى على API
- `config.py` - إعدادات Zoho API
