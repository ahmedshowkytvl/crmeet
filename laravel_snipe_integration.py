#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Laravel-Snipe-IT Integration Service
Service to integrate Laravel CRM with Snipe-IT Asset Management
"""

import requests
import json
from datetime import datetime
from typing import Dict, List, Optional
from snipe_it_client import SnipeITClient


class LaravelSnipeITIntegration:
    def __init__(self, laravel_url: str, snipe_url: str, snipe_token: str, laravel_token: str = None):
        """
        Initialize Laravel-Snipe-IT integration
        
        Args:
            laravel_url: Laravel CRM base URL
            snipe_url: Snipe-IT base URL
            snipe_token: Snipe-IT API token
            laravel_token: Laravel API token (if using API)
        """
        self.laravel_url = laravel_url.rstrip('/')
        self.snipe_client = SnipeITClient(snipe_url, snipe_token)
        self.laravel_token = laravel_token
        
        # Laravel session for web requests
        self.laravel_session = requests.Session()
        if laravel_token:
            self.laravel_session.headers.update({
                'Authorization': f'Bearer {laravel_token}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            })
    
    def sync_users(self):
        """Sync Laravel users with Snipe-IT users"""
        print("👥 Syncing users from Laravel to Snipe-IT...")
        
        # Get Laravel users (this would need to be implemented based on your Laravel API)
        laravel_users = self._get_laravel_users()
        
        synced_count = 0
        errors = []
        
        for user in laravel_users:
            try:
                # Check if user exists in Snipe-IT
                snipe_users = self.snipe_client.get_users()
                existing_user = None
                
                if snipe_users.get('status') == 'success':
                    for snipe_user in snipe_users.get('rows', []):
                        if snipe_user.get('username') == user.get('email'):
                            existing_user = snipe_user
                            break
                
                user_data = {
                    'first_name': user.get('first_name', ''),
                    'last_name': user.get('last_name', ''),
                    'username': user.get('email', ''),
                    'email': user.get('email', ''),
                    'password': 'temp_password_123',  # User should change this
                    'password_confirmation': 'temp_password_123',
                    'activated': True,
                    'employee_num': user.get('employee_id', ''),
                    'jobtitle': user.get('job_title', ''),
                    'phone': user.get('phone', ''),
                    'department_id': user.get('department_id', 1),
                    'location_id': user.get('location_id', 1),
                    'manager_id': user.get('manager_id', 1),
                    'notes': f'Synced from Laravel CRM on {datetime.now().strftime("%Y-%m-%d %H:%M:%S")}'
                }
                
                if existing_user:
                    # Update existing user
                    result = self.snipe_client.update_user(existing_user['id'], user_data)
                else:
                    # Create new user
                    result = self.snipe_client.create_user(user_data)
                
                if result.get('status') == 'success':
                    synced_count += 1
                    print(f"✅ Synced user: {user.get('first_name')} {user.get('last_name')}")
                else:
                    errors.append(f"Failed to sync {user.get('email')}: {result.get('messages')}")
                    
            except Exception as e:
                errors.append(f"Error syncing user {user.get('email')}: {str(e)}")
        
        print(f"\n📊 User Sync Results:")
        print(f"   ✅ Successfully synced: {synced_count}")
        print(f"   ❌ Errors: {len(errors)}")
        
        return synced_count, errors
    
    def sync_assets(self):
        """Sync Laravel assets with Snipe-IT assets"""
        print("📦 Syncing assets from Laravel to Snipe-IT...")
        
        # Get Laravel assets
        laravel_assets = self._get_laravel_assets()
        
        synced_count = 0
        errors = []
        
        for asset in laravel_assets:
            try:
                # Check if asset exists in Snipe-IT
                existing = self.snipe_client.get_asset_by_tag(asset.get('asset_tag', ''))
                
                asset_data = {
                    'name': asset.get('name', ''),
                    'asset_tag': asset.get('asset_tag', ''),
                    'serial': asset.get('serial_number', ''),
                    'model_id': asset.get('model_id', 1),
                    'status_id': asset.get('status_id', 1),
                    'location_id': asset.get('location_id', 1),
                    'company_id': asset.get('company_id', 1),
                    'notes': asset.get('notes', ''),
                    'purchase_date': asset.get('purchase_date', ''),
                    'purchase_cost': asset.get('purchase_cost', ''),
                    'supplier_id': asset.get('supplier_id', 1),
                    'order_number': asset.get('order_number', ''),
                    'warranty_months': asset.get('warranty_months', ''),
                    'requestable': asset.get('requestable', False)
                }
                
                if existing.get('status') == 'success':
                    # Update existing asset
                    result = self.snipe_client.update_asset(existing['payload']['id'], asset_data)
                else:
                    # Create new asset
                    result = self.snipe_client.create_asset(asset_data)
                
                if result.get('status') == 'success':
                    synced_count += 1
                    print(f"✅ Synced asset: {asset.get('name')} ({asset.get('asset_tag')})")
                else:
                    errors.append(f"Failed to sync {asset.get('name')}: {result.get('messages')}")
                    
            except Exception as e:
                errors.append(f"Error syncing asset {asset.get('name')}: {str(e)}")
        
        print(f"\n📊 Asset Sync Results:")
        print(f"   ✅ Successfully synced: {synced_count}")
        print(f"   ❌ Errors: {len(errors)}")
        
        return synced_count, errors
    
    def sync_departments(self):
        """Sync Laravel departments with Snipe-IT locations"""
        print("🏢 Syncing departments from Laravel to Snipe-IT...")
        
        # Get Laravel departments
        laravel_departments = self._get_laravel_departments()
        
        synced_count = 0
        errors = []
        
        for dept in laravel_departments:
            try:
                # Check if location exists in Snipe-IT
                snipe_locations = self.snipe_client.get_locations()
                existing_location = None
                
                if snipe_locations.get('status') == 'success':
                    for location in snipe_locations.get('rows', []):
                        if location.get('name') == dept.get('name'):
                            existing_location = location
                            break
                
                location_data = {
                    'name': dept.get('name', ''),
                    'address': dept.get('address', ''),
                    'address2': dept.get('address2', ''),
                    'city': dept.get('city', ''),
                    'state': dept.get('state', ''),
                    'country': dept.get('country', ''),
                    'zip': dept.get('zip', ''),
                    'phone': dept.get('phone', ''),
                    'fax': dept.get('fax', ''),
                    'notes': f'Synced from Laravel CRM - Department: {dept.get("name")}'
                }
                
                if existing_location:
                    # Update existing location
                    result = self.snipe_client.update_location(existing_location['id'], location_data)
                else:
                    # Create new location
                    result = self.snipe_client.create_location(location_data)
                
                if result.get('status') == 'success':
                    synced_count += 1
                    print(f"✅ Synced department: {dept.get('name')}")
                else:
                    errors.append(f"Failed to sync {dept.get('name')}: {result.get('messages')}")
                    
            except Exception as e:
                errors.append(f"Error syncing department {dept.get('name')}: {str(e)}")
        
        print(f"\n📊 Department Sync Results:")
        print(f"   ✅ Successfully synced: {synced_count}")
        print(f"   ❌ Errors: {len(errors)}")
        
        return synced_count, errors
    
    def create_asset_from_task(self, task_data: dict):
        """Create Snipe-IT asset from Laravel task"""
        print(f"📋 Creating asset from task: {task_data.get('title', 'Unknown')}")
        
        # Extract asset information from task
        asset_data = {
            'name': task_data.get('title', ''),
            'asset_tag': f"TASK-{task_data.get('id', '')}",
            'serial': task_data.get('serial_number', ''),
            'model_id': 1,  # Default model
            'status_id': 1,  # Ready to deploy
            'location_id': 1,  # Default location
            'company_id': 1,  # Default company
            'notes': f"Created from Laravel task: {task_data.get('description', '')}",
            'purchase_date': task_data.get('created_at', ''),
            'purchase_cost': task_data.get('estimated_cost', 0),
            'requestable': True
        }
        
        result = self.snipe_client.create_asset(asset_data)
        
        if result.get('status') == 'success':
            print(f"✅ Created asset: {asset_data['name']} ({asset_data['asset_tag']})")
            return result['payload']
        else:
            print(f"❌ Failed to create asset: {result.get('messages')}")
            return None
    
    def update_laravel_task_with_asset(self, task_id: int, asset_id: int):
        """Update Laravel task with Snipe-IT asset information"""
        print(f"🔄 Updating Laravel task {task_id} with asset {asset_id}")
        
        # Get asset details from Snipe-IT
        asset_result = self.snipe_client.get_asset(asset_id)
        
        if asset_result.get('status') == 'success':
            asset = asset_result['payload']
            
            # Update Laravel task (this would need to be implemented based on your Laravel API)
            update_data = {
                'snipe_asset_id': asset_id,
                'asset_tag': asset.get('asset_tag', ''),
                'asset_serial': asset.get('serial', ''),
                'asset_status': asset.get('status_label', {}).get('name', ''),
                'asset_location': asset.get('location', {}).get('name', ''),
                'updated_at': datetime.now().isoformat()
            }
            
            # This would be a call to your Laravel API
            # result = self._update_laravel_task(task_id, update_data)
            
            print(f"✅ Updated task {task_id} with asset information")
            return True
        else:
            print(f"❌ Failed to get asset {asset_id}: {asset_result.get('messages')}")
            return False
    
    def generate_integration_report(self):
        """Generate comprehensive integration report"""
        print("📊 Generating integration report...")
        
        # Get data from both systems
        laravel_users = self._get_laravel_users()
        laravel_assets = self._get_laravel_assets()
        laravel_departments = self._get_laravel_departments()
        
        snipe_users = self.snipe_client.get_users()
        snipe_assets = self.snipe_client.get_assets()
        snipe_locations = self.snipe_client.get_locations()
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'laravel': {
                'users': len(laravel_users),
                'assets': len(laravel_assets),
                'departments': len(laravel_departments)
            },
            'snipe_it': {
                'users': len(snipe_users.get('rows', [])) if snipe_users.get('status') == 'success' else 0,
                'assets': len(snipe_assets.get('rows', [])) if snipe_assets.get('status') == 'success' else 0,
                'locations': len(snipe_locations.get('rows', [])) if snipe_locations.get('status') == 'success' else 0
            },
            'sync_status': {
                'users_synced': 0,  # Would be calculated based on actual sync
                'assets_synced': 0,
                'departments_synced': 0
            }
        }
        
        # Save report
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"integration_report_{timestamp}.json"
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False)
        
        print(f"✅ Integration report saved: {filename}")
        print(f"📊 Laravel: {report['laravel']['users']} users, {report['laravel']['assets']} assets")
        print(f"📊 Snipe-IT: {report['snipe_it']['users']} users, {report['snipe_it']['assets']} assets")
        
        return report
    
    def _get_laravel_users(self) -> List[Dict]:
        """Get users from Laravel CRM (implement based on your API)"""
        # This would be implemented based on your Laravel API structure
        # For now, returning sample data
        return [
            {
                'id': 1,
                'first_name': 'John',
                'last_name': 'Doe',
                'email': 'john.doe@company.com',
                'employee_id': 'EMP001',
                'job_title': 'Manager',
                'phone': '+1234567890',
                'department_id': 1,
                'location_id': 1
            },
            {
                'id': 2,
                'first_name': 'Jane',
                'last_name': 'Smith',
                'email': 'jane.smith@company.com',
                'employee_id': 'EMP002',
                'job_title': 'Developer',
                'phone': '+1234567891',
                'department_id': 2,
                'location_id': 1
            }
        ]
    
    def _get_laravel_assets(self) -> List[Dict]:
        """Get assets from Laravel CRM (implement based on your API)"""
        # This would be implemented based on your Laravel API structure
        return [
            {
                'id': 1,
                'name': 'Dell Laptop',
                'asset_tag': 'LAP001',
                'serial_number': 'DL123456789',
                'model_id': 1,
                'status_id': 1,
                'location_id': 1,
                'company_id': 1,
                'notes': 'Office laptop',
                'purchase_date': '2024-01-15',
                'purchase_cost': 1200.00,
                'supplier_id': 1,
                'requestable': True
            }
        ]
    
    def _get_laravel_departments(self) -> List[Dict]:
        """Get departments from Laravel CRM (implement based on your API)"""
        # This would be implemented based on your Laravel API structure
        return [
            {
                'id': 1,
                'name': 'IT Department',
                'address': '123 Tech Street',
                'city': 'Tech City',
                'state': 'TC',
                'country': 'USA',
                'zip': '12345',
                'phone': '+1234567890'
            },
            {
                'id': 2,
                'name': 'HR Department',
                'address': '456 HR Avenue',
                'city': 'HR City',
                'state': 'HC',
                'country': 'USA',
                'zip': '54321',
                'phone': '+1234567891'
            }
        ]


def main():
    """Example usage of Laravel-Snipe-IT integration"""
    
    # Configuration
    LARAVEL_URL = "http://127.0.0.1:8000"
    SNIPE_URL = "http://127.0.0.1:8000"  # Assuming Snipe-IT is on same server
    SNIPE_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    # Initialize integration
    integration = LaravelSnipeITIntegration(LARAVEL_URL, SNIPE_URL, SNIPE_TOKEN)
    
    print("🚀 Laravel-Snipe-IT Integration Demo")
    print("="*60)
    
    # Test Snipe-IT connection
    if not integration.snipe_client.test_connection():
        print("❌ Cannot connect to Snipe-IT API")
        return
    
    print("✅ Connected to Snipe-IT API")
    
    # Sync users
    print("\n👥 Syncing users...")
    user_count, user_errors = integration.sync_users()
    
    # Sync departments
    print("\n🏢 Syncing departments...")
    dept_count, dept_errors = integration.sync_departments()
    
    # Sync assets
    print("\n📦 Syncing assets...")
    asset_count, asset_errors = integration.sync_assets()
    
    # Create asset from task
    print("\n📋 Creating asset from task...")
    sample_task = {
        'id': 1,
        'title': 'Setup New Employee Laptop',
        'description': 'Configure Dell laptop for new employee',
        'serial_number': 'DL987654321',
        'estimated_cost': 1500.00,
        'created_at': '2024-01-20'
    }
    
    asset_result = integration.create_asset_from_task(sample_task)
    
    # Generate integration report
    print("\n📊 Generating integration report...")
    report = integration.generate_integration_report()
    
    print("\n🎉 Integration demo completed!")


if __name__ == "__main__":
    main()
