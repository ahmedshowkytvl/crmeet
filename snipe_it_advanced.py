#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT Advanced Operations
Advanced examples for Snipe-IT API integration
"""

from snipe_it_client import SnipeITClient
import json
from datetime import datetime


class SnipeITAdvancedOperations:
    def __init__(self, base_url: str, api_token: str):
        self.client = SnipeITClient(base_url, api_token)
    
    def sync_assets_with_laravel(self, laravel_assets: list):
        """Sync Laravel assets with Snipe-IT"""
        print("🔄 Syncing Laravel assets with Snipe-IT...")
        
        synced_count = 0
        errors = []
        
        for asset in laravel_assets:
            try:
                # Check if asset exists in Snipe-IT
                existing = self.client.get_asset_by_tag(asset.get('asset_tag', ''))
                
                if existing.get('status') == 'success':
                    # Update existing asset
                    result = self.client.update_asset(
                        existing['payload']['id'],
                        self._prepare_asset_data(asset)
                    )
                else:
                    # Create new asset
                    result = self.client.create_asset(self._prepare_asset_data(asset))
                
                if result.get('status') == 'success':
                    synced_count += 1
                    print(f"✅ Synced: {asset.get('name', 'Unknown')}")
                else:
                    errors.append(f"Failed to sync {asset.get('name')}: {result.get('messages')}")
                    
            except Exception as e:
                errors.append(f"Error syncing {asset.get('name')}: {str(e)}")
        
        print(f"\n📊 Sync Results:")
        print(f"   ✅ Successfully synced: {synced_count}")
        print(f"   ❌ Errors: {len(errors)}")
        
        if errors:
            print("\n❌ Errors:")
            for error in errors[:5]:  # Show first 5 errors
                print(f"   • {error}")
        
        return synced_count, errors
    
    def _prepare_asset_data(self, laravel_asset: dict) -> dict:
        """Prepare Laravel asset data for Snipe-IT format"""
        return {
            "name": laravel_asset.get('name', ''),
            "asset_tag": laravel_asset.get('asset_tag', ''),
            "serial": laravel_asset.get('serial_number', ''),
            "model_id": laravel_asset.get('model_id', 1),
            "status_id": laravel_asset.get('status_id', 1),
            "location_id": laravel_asset.get('location_id', 1),
            "company_id": laravel_asset.get('company_id', 1),
            "notes": laravel_asset.get('notes', ''),
            "purchase_date": laravel_asset.get('purchase_date', ''),
            "purchase_cost": laravel_asset.get('purchase_cost', ''),
            "supplier_id": laravel_asset.get('supplier_id', 1),
            "order_number": laravel_asset.get('order_number', ''),
            "warranty_months": laravel_asset.get('warranty_months', ''),
            "requestable": laravel_asset.get('requestable', False)
        }
    
    def create_asset_hierarchy(self, hierarchy_data: dict):
        """Create complete asset hierarchy (categories, models, manufacturers)"""
        print("🏗️ Creating asset hierarchy...")
        
        # Create manufacturer
        manufacturer_data = {
            "name": hierarchy_data.get('manufacturer', 'Unknown'),
            "url": hierarchy_data.get('manufacturer_url', ''),
            "support_url": hierarchy_data.get('support_url', ''),
            "support_phone": hierarchy_data.get('support_phone', ''),
            "support_email": hierarchy_data.get('support_email', '')
        }
        
        manufacturer_result = self.client.create_manufacturer(manufacturer_data)
        if manufacturer_result.get('status') != 'success':
            print(f"❌ Failed to create manufacturer: {manufacturer_result.get('messages')}")
            return None
        
        manufacturer_id = manufacturer_result['payload']['id']
        print(f"✅ Created manufacturer: {hierarchy_data.get('manufacturer')}")
        
        # Create category
        category_data = {
            "name": hierarchy_data.get('category', 'General'),
            "category_type": hierarchy_data.get('category_type', 'asset'),
            "eula_text": hierarchy_data.get('eula_text', ''),
            "use_default_eula": hierarchy_data.get('use_default_eula', True),
            "require_acceptance": hierarchy_data.get('require_acceptance', False),
            "checkin_email": hierarchy_data.get('checkin_email', False)
        }
        
        category_result = self.client.create_category(category_data)
        if category_result.get('status') != 'success':
            print(f"❌ Failed to create category: {category_result.get('messages')}")
            return None
        
        category_id = category_result['payload']['id']
        print(f"✅ Created category: {hierarchy_data.get('category')}")
        
        # Create model
        model_data = {
            "name": hierarchy_data.get('model', 'Generic Model'),
            "model_number": hierarchy_data.get('model_number', ''),
            "manufacturer_id": manufacturer_id,
            "category_id": category_id,
            "depreciation_id": hierarchy_data.get('depreciation_id', 1),
            "eol": hierarchy_data.get('eol', ''),
            "notes": hierarchy_data.get('model_notes', ''),
            "fieldset_id": hierarchy_data.get('fieldset_id', 1)
        }
        
        model_result = self.client.create_model(model_data)
        if model_result.get('status') != 'success':
            print(f"❌ Failed to create model: {model_result.get('messages')}")
            return None
        
        model_id = model_result['payload']['id']
        print(f"✅ Created model: {hierarchy_data.get('model')}")
        
        return {
            'manufacturer_id': manufacturer_id,
            'category_id': category_id,
            'model_id': model_id
        }
    
    def bulk_asset_operations(self, operations: list):
        """Perform bulk operations on assets"""
        print(f"⚡ Performing {len(operations)} bulk operations...")
        
        results = {
            'success': 0,
            'failed': 0,
            'errors': []
        }
        
        for operation in operations:
            try:
                op_type = operation.get('type')
                asset_id = operation.get('asset_id')
                data = operation.get('data', {})
                
                if op_type == 'checkout':
                    result = self.client.checkout_asset(asset_id, data)
                elif op_type == 'checkin':
                    result = self.client.checkin_asset(asset_id)
                elif op_type == 'update':
                    result = self.client.update_asset(asset_id, data)
                elif op_type == 'delete':
                    result = self.client.delete_asset(asset_id)
                else:
                    results['errors'].append(f"Unknown operation type: {op_type}")
                    continue
                
                if result.get('status') == 'success':
                    results['success'] += 1
                else:
                    results['failed'] += 1
                    results['errors'].append(f"Operation {op_type} failed: {result.get('messages')}")
                    
            except Exception as e:
                results['failed'] += 1
                results['errors'].append(f"Exception in {operation.get('type')}: {str(e)}")
        
        print(f"\n📊 Bulk Operations Results:")
        print(f"   ✅ Successful: {results['success']}")
        print(f"   ❌ Failed: {results['failed']}")
        
        return results
    
    def generate_asset_report(self, filters: dict = None):
        """Generate comprehensive asset report"""
        print("📊 Generating asset report...")
        
        # Get all assets
        assets_result = self.client.get_assets(limit=1000)
        if assets_result.get('status') != 'success':
            print(f"❌ Failed to get assets: {assets_result.get('messages')}")
            return None
        
        assets = assets_result.get('rows', [])
        
        # Get supporting data
        categories = self.client.get_categories()
        models = self.client.get_models()
        manufacturers = self.client.get_manufacturers()
        status_labels = self.client.get_status_labels()
        locations = self.client.get_locations()
        
        # Create lookup dictionaries
        category_lookup = {cat['id']: cat['name'] for cat in categories.get('rows', [])}
        model_lookup = {model['id']: model['name'] for model in models.get('rows', [])}
        manufacturer_lookup = {man['id']: man['name'] for man in manufacturers.get('rows', [])}
        status_lookup = {status['id']: status['name'] for status in status_labels.get('rows', [])}
        location_lookup = {loc['id']: loc['name'] for loc in locations.get('rows', [])}
        
        # Generate report data
        report = {
            'summary': {
                'total_assets': len(assets),
                'by_status': {},
                'by_category': {},
                'by_location': {},
                'by_manufacturer': {},
                'total_value': 0
            },
            'assets': []
        }
        
        for asset in assets:
            # Add lookup data
            asset['category_name'] = category_lookup.get(asset.get('category_id'), 'Unknown')
            asset['model_name'] = model_lookup.get(asset.get('model_id'), 'Unknown')
            asset['manufacturer_name'] = manufacturer_lookup.get(asset.get('manufacturer_id'), 'Unknown')
            asset['status_name'] = status_lookup.get(asset.get('status_id'), 'Unknown')
            asset['location_name'] = location_lookup.get(asset.get('location_id'), 'Unknown')
            
            # Update summary
            status = asset['status_name']
            category = asset['category_name']
            location = asset['location_name']
            manufacturer = asset['manufacturer_name']
            
            report['summary']['by_status'][status] = report['summary']['by_status'].get(status, 0) + 1
            report['summary']['by_category'][category] = report['summary']['by_category'].get(category, 0) + 1
            report['summary']['by_location'][location] = report['summary']['by_location'].get(location, 0) + 1
            report['summary']['by_manufacturer'][manufacturer] = report['summary']['by_manufacturer'].get(manufacturer, 0) + 1
            
            # Add to total value
            purchase_cost = float(asset.get('purchase_cost', 0) or 0)
            report['summary']['total_value'] += purchase_cost
            
            report['assets'].append(asset)
        
        # Save report to file
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"snipe_it_report_{timestamp}.json"
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False)
        
        print(f"✅ Report generated: {filename}")
        print(f"📊 Total assets: {report['summary']['total_assets']}")
        print(f"💰 Total value: ${report['summary']['total_value']:,.2f}")
        
        return report
    
    def setup_default_data(self):
        """Setup default categories, status labels, and locations"""
        print("🔧 Setting up default Snipe-IT data...")
        
        # Default categories
        categories = [
            {"name": "Computers", "category_type": "asset"},
            {"name": "Mobile Devices", "category_type": "asset"},
            {"name": "Network Equipment", "category_type": "asset"},
            {"name": "Accessories", "category_type": "accessory"},
            {"name": "Consumables", "category_type": "consumable"}
        ]
        
        # Default status labels
        status_labels = [
            {"name": "Ready to Deploy", "status_type": "deployable", "color": "#00a65a"},
            {"name": "Deployed", "status_type": "deployable", "color": "#3c8dbc"},
            {"name": "Pending", "status_type": "pending", "color": "#f39c12"},
            {"name": "Archived", "status_type": "archived", "color": "#6c757d"},
            {"name": "Out of Order", "status_type": "undeployable", "color": "#dc3545"}
        ]
        
        # Default locations
        locations = [
            {"name": "Main Office", "address": "123 Main St", "city": "City", "state": "State", "country": "Country"},
            {"name": "Branch Office", "address": "456 Branch Ave", "city": "City", "state": "State", "country": "Country"},
            {"name": "Warehouse", "address": "789 Warehouse Blvd", "city": "City", "state": "State", "country": "Country"}
        ]
        
        created_items = {
            'categories': [],
            'status_labels': [],
            'locations': []
        }
        
        # Create categories
        for category_data in categories:
            result = self.client.create_category(category_data)
            if result.get('status') == 'success':
                created_items['categories'].append(result['payload'])
                print(f"✅ Created category: {category_data['name']}")
        
        # Create status labels
        for status_data in status_labels:
            result = self.client.create_status_label(status_data)
            if result.get('status') == 'success':
                created_items['status_labels'].append(result['payload'])
                print(f"✅ Created status label: {status_data['name']}")
        
        # Create locations
        for location_data in locations:
            result = self.client.create_location(location_data)
            if result.get('status') == 'success':
                created_items['locations'].append(result['payload'])
                print(f"✅ Created location: {location_data['name']}")
        
        print(f"\n📊 Setup Complete:")
        print(f"   • Categories: {len(created_items['categories'])}")
        print(f"   • Status Labels: {len(created_items['status_labels'])}")
        print(f"   • Locations: {len(created_items['locations'])}")
        
        return created_items


def main():
    """Example usage of advanced Snipe-IT operations"""
    
    # Configuration
    BASE_URL = "http://127.0.0.1:8000"
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    # Initialize advanced operations
    advanced = SnipeITAdvancedOperations(BASE_URL, API_TOKEN)
    
    print("🚀 Snipe-IT Advanced Operations Demo")
    print("="*60)
    
    # Test connection first
    if not advanced.client.test_connection():
        print("❌ Cannot connect to Snipe-IT API")
        return
    
    print("✅ Connected to Snipe-IT API")
    
    # Example 1: Setup default data
    print("\n🔧 Setting up default data...")
    default_data = advanced.setup_default_data()
    
    # Example 2: Create asset hierarchy
    print("\n🏗️ Creating asset hierarchy...")
    hierarchy_data = {
        'manufacturer': 'Dell',
        'manufacturer_url': 'https://dell.com',
        'support_url': 'https://support.dell.com',
        'support_phone': '1-800-DELL',
        'support_email': 'support@dell.com',
        'category': 'Laptops',
        'category_type': 'asset',
        'model': 'Latitude 5520',
        'model_number': 'LAT5520',
        'model_notes': 'Business laptop with Intel i7'
    }
    
    hierarchy_result = advanced.create_asset_hierarchy(hierarchy_data)
    
    # Example 3: Sync Laravel assets (sample data)
    print("\n🔄 Syncing sample Laravel assets...")
    sample_laravel_assets = [
        {
            'name': 'Dell Laptop - John Doe',
            'asset_tag': 'LAP001',
            'serial_number': 'DL123456789',
            'model_id': hierarchy_result['model_id'] if hierarchy_result else 1,
            'status_id': 1,
            'location_id': 1,
            'notes': 'Assigned to John Doe',
            'purchase_cost': 1200.00
        },
        {
            'name': 'HP Printer - Office',
            'asset_tag': 'PRT001',
            'serial_number': 'HP987654321',
            'model_id': 1,
            'status_id': 1,
            'location_id': 1,
            'notes': 'Office printer',
            'purchase_cost': 300.00
        }
    ]
    
    synced_count, errors = advanced.sync_assets_with_laravel(sample_laravel_assets)
    
    # Example 4: Generate asset report
    print("\n📊 Generating asset report...")
    report = advanced.generate_asset_report()
    
    # Example 5: Bulk operations
    print("\n⚡ Performing bulk operations...")
    bulk_operations = [
        {
            'type': 'checkout',
            'asset_id': 1,
            'data': {
                'assigned_user': 1,
                'note': 'Bulk checkout operation'
            }
        }
    ]
    
    bulk_results = advanced.bulk_asset_operations(bulk_operations)
    
    print("\n🎉 Advanced operations completed!")


if __name__ == "__main__":
    main()
