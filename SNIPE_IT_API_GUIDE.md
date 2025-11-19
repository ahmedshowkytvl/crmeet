# Snipe-IT API Integration Guide

Complete guide for integrating with Snipe-IT Asset Management System using the provided API token.

## 🔑 API Token Information

Your JWT token is valid and contains:
- **User ID**: 2
- **Expiration**: 2234-06-18 (valid for ~4 years)
- **Scopes**: Full API access

## 📋 Available Endpoints

Based on the [Snipe-IT API documentation](https://snipe-it.readme.io/reference/api-overview), here are the main endpoints:

### Assets (Hardware)
- `GET /api/v1/hardware` - Get all assets
- `GET /api/v1/hardware/{id}` - Get specific asset
- `GET /api/v1/hardware/bytag/{tag}` - Get asset by tag
- `GET /api/v1/hardware/byserial/{serial}` - Get asset by serial
- `POST /api/v1/hardware` - Create new asset
- `PUT /api/v1/hardware/{id}` - Update asset
- `DELETE /api/v1/hardware/{id}` - Delete asset
- `POST /api/v1/hardware/{id}/checkout` - Checkout asset
- `POST /api/v1/hardware/{id}/checkin` - Checkin asset

### Users
- `GET /api/v1/users` - Get all users
- `GET /api/v1/users/{id}` - Get specific user
- `GET /api/v1/users/me` - Get current user
- `POST /api/v1/users` - Create user
- `PUT /api/v1/users/{id}` - Update user

### Categories
- `GET /api/v1/categories` - Get all categories
- `GET /api/v1/categories/{id}` - Get specific category
- `POST /api/v1/categories` - Create category

### Models
- `GET /api/v1/models` - Get all models
- `GET /api/v1/models/{id}` - Get specific model
- `POST /api/v1/models` - Create model

### Manufacturers
- `GET /api/v1/manufacturers` - Get all manufacturers
- `GET /api/v1/manufacturers/{id}` - Get specific manufacturer
- `POST /api/v1/manufacturers` - Create manufacturer

### Status Labels
- `GET /api/v1/statuslabels` - Get all status labels
- `GET /api/v1/statuslabels/{id}` - Get specific status label
- `POST /api/v1/statuslabels` - Create status label

### Locations
- `GET /api/v1/locations` - Get all locations
- `GET /api/v1/locations/{id}` - Get specific location
- `POST /api/v1/locations` - Create location

### Companies
- `GET /api/v1/companies` - Get all companies
- `GET /api/v1/companies/{id}` - Get specific company
- `POST /api/v1/companies` - Create company

## 🚀 Quick Start

### 1. Test Your API Connection
```bash
python test_snipe_it_api.py
```

### 2. Use the Full Client
```python
from snipe_it_client import SnipeITClient

# Initialize client
client = SnipeITClient(
    base_url="http://127.0.0.1:8000",
    api_token="YOUR_API_TOKEN"
)

# Test connection
if client.test_connection():
    print("✅ Connected successfully!")
    
    # Get assets
    assets = client.get_assets(limit=10)
    print(f"Found {len(assets.get('rows', []))} assets")
```

## 📝 Example Usage

### Get All Assets
```python
assets = client.get_assets()
if assets['status'] == 'success':
    for asset in assets['rows']:
        print(f"Asset: {asset['name']} (Tag: {asset['asset_tag']})")
```

### Create New Asset
```python
new_asset = {
    "name": "Dell Laptop",
    "model_id": 1,
    "status_id": 1,
    "asset_tag": "LAPTOP001",
    "serial": "DL123456789"
}

result = client.create_asset(new_asset)
if result['status'] == 'success':
    print("Asset created successfully!")
```

### Search Assets
```python
# Search by name, tag, or serial
results = client.search_assets("Dell")
```

### Checkout Asset
```python
checkout_data = {
    "assigned_user": 5,
    "note": "Assigned to John Doe"
}

result = client.checkout_asset(asset_id=123, checkout_data=checkout_data)
```

## 🔧 Authentication Methods

### Method 1: Bearer Token (Recommended)
```bash
Authorization: Bearer YOUR_JWT_TOKEN
```

### Method 2: Query Parameter
```bash
GET /api/v1/hardware?api_token=YOUR_JWT_TOKEN
```

## 📊 Response Format

Snipe-IT uses a consistent response format:

### Success Response
```json
{
  "status": "success",
  "payload": {
    // Actual data here
  },
  "messages": null
}
```

### Error Response
```json
{
  "status": "error",
  "messages": "Error description or validation errors"
}
```

## ⚠️ Important Notes

1. **HTTP Status Codes**: Snipe-IT returns `200 OK` even for errors. Check the JSON `status` field.

2. **Rate Limiting**: API requests are throttled. Respect rate limits.

3. **Data Validation**: Always validate data before sending to API.

4. **Error Handling**: Always check the `status` field in responses.

## 🛠️ Troubleshooting

### Common Issues

1. **401 Unauthorized**
   - Check if API token is valid
   - Verify token hasn't expired
   - Ensure proper Authorization header format

2. **404 Not Found**
   - Verify endpoint URL is correct
   - Check if resource exists

3. **422 Validation Error**
   - Check required fields
   - Validate data types
   - Review validation messages

### Debug Tips

1. **Test with curl**:
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     http://127.0.0.1:8000/api/v1/users/me
```

2. **Check API Explorer**: Use the [Snipe-IT API Explorer](https://snipe-it.readme.io/reference/api-overview) for testing.

3. **Enable Debug Mode**: Add debug logging to see request/response details.

## 📚 Additional Resources

- [Snipe-IT API Documentation](https://snipe-it.readme.io/reference/api-overview)
- [API Permissions Guide](https://snipe-it.readme.io/reference/api-permissions)
- [Postman Collection](https://snipe-it.readme.io/reference/testing-in-postman)
- [Swagger/OpenAPI Spec](https://snipe-it.readme.io/reference/api-overview)

## 🎯 Next Steps

1. Run the test script to verify connection
2. Explore available endpoints
3. Implement asset management features
4. Add error handling and validation
5. Integrate with your Laravel application

## 🔧 Advanced Integration

### Laravel-Snipe-IT Integration Service

Use the `laravel_snipe_integration.py` for comprehensive integration:

```python
from laravel_snipe_integration import LaravelSnipeITIntegration

# Initialize integration
integration = LaravelSnipeITIntegration(
    laravel_url="http://127.0.0.1:8000",
    snipe_url="http://127.0.0.1:8000",
    snipe_token="YOUR_TOKEN"
)

# Sync users from Laravel to Snipe-IT
user_count, errors = integration.sync_users()

# Sync assets
asset_count, errors = integration.sync_assets()

# Create asset from Laravel task
asset = integration.create_asset_from_task(task_data)
```

### Advanced Operations

Use `snipe_it_advanced.py` for complex operations:

```python
from snipe_it_advanced import SnipeITAdvancedOperations

advanced = SnipeITAdvancedOperations(BASE_URL, API_TOKEN)

# Setup default data
default_data = advanced.setup_default_data()

# Create asset hierarchy
hierarchy = advanced.create_asset_hierarchy({
    'manufacturer': 'Dell',
    'category': 'Laptops',
    'model': 'Latitude 5520'
})

# Generate comprehensive reports
report = advanced.generate_asset_report()
```

## 📋 Complete File Structure

```
snipe-it-integration/
├── snipe_it_client.py              # Main API client
├── test_snipe_it_api.py            # Quick connection test
├── snipe_it_advanced.py            # Advanced operations
├── laravel_snipe_integration.py    # Laravel integration service
├── requirements_laravel.txt        # Python dependencies
└── SNIPE_IT_API_GUIDE.md          # This guide
```

## 🚀 Quick Start Commands

```bash
# Install dependencies
pip install -r requirements_laravel.txt

# Test API connection
python test_snipe_it_api.py

# Run advanced operations
python snipe_it_advanced.py

# Run Laravel integration
python laravel_snipe_integration.py
```

## 🔗 Integration Examples

### 1. Sync Laravel Users to Snipe-IT
```python
# Get Laravel users via API
laravel_users = requests.get(f"{LARAVEL_URL}/api/users").json()

# Sync to Snipe-IT
for user in laravel_users:
    snipe_user_data = {
        'first_name': user['first_name'],
        'last_name': user['last_name'],
        'username': user['email'],
        'email': user['email'],
        'activated': True
    }
    client.create_user(snipe_user_data)
```

### 2. Create Asset from Laravel Task
```python
# Get task from Laravel
task = requests.get(f"{LARAVEL_URL}/api/tasks/123").json()

# Create asset in Snipe-IT
asset_data = {
    'name': task['title'],
    'asset_tag': f"TASK-{task['id']}",
    'notes': task['description'],
    'model_id': 1,
    'status_id': 1
}
client.create_asset(asset_data)
```

### 3. Bulk Asset Operations
```python
# Prepare bulk operations
operations = [
    {'type': 'checkout', 'asset_id': 1, 'data': {'assigned_user': 5}},
    {'type': 'checkin', 'asset_id': 2},
    {'type': 'update', 'asset_id': 3, 'data': {'notes': 'Updated'}}
]

# Execute bulk operations
results = advanced.bulk_asset_operations(operations)
```

## 📊 Monitoring and Reporting

### Generate Asset Reports
```python
# Generate comprehensive report
report = advanced.generate_asset_report()

# Report includes:
# - Total assets by status
# - Assets by category
# - Assets by location
# - Total asset value
# - Detailed asset information
```

### Integration Status
```python
# Check integration status
report = integration.generate_integration_report()

# Monitor sync status
print(f"Users synced: {report['sync_status']['users_synced']}")
print(f"Assets synced: {report['sync_status']['assets_synced']}")
```

## 🛡️ Security Best Practices

1. **Token Management**: Store API tokens securely
2. **Rate Limiting**: Respect API rate limits
3. **Error Handling**: Always handle API errors gracefully
4. **Data Validation**: Validate data before sending to API
5. **Logging**: Log all API operations for debugging

## 🔧 Troubleshooting

### Common Issues and Solutions

1. **401 Unauthorized**
   ```python
   # Check token validity
   if not client.test_connection():
       print("Token expired or invalid")
   ```

2. **Rate Limiting**
   ```python
   import time
   time.sleep(1)  # Wait between requests
   ```

3. **Data Validation Errors**
   ```python
   # Check required fields
   required_fields = ['name', 'model_id', 'status_id']
   for field in required_fields:
       if field not in asset_data:
           print(f"Missing required field: {field}")
   ```

## 📈 Performance Optimization

1. **Batch Operations**: Use bulk operations when possible
2. **Pagination**: Use limit/offset for large datasets
3. **Caching**: Cache frequently accessed data
4. **Async Operations**: Use async requests for better performance

## 🎉 Success Metrics

Track these metrics for successful integration:

- **Sync Success Rate**: Percentage of successful syncs
- **API Response Time**: Average response time
- **Error Rate**: Percentage of failed requests
- **Data Accuracy**: Consistency between systems
- **User Adoption**: Usage of integrated features
