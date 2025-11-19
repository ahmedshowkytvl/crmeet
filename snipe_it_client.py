#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Snipe-IT API Client
Comprehensive client for communicating with Snipe-IT Asset Management System
"""

import requests
import json
from typing import Dict, List, Optional, Any


class SnipeITClient:
    def __init__(self, base_url: str, api_token: str):
        """
        Initialize Snipe-IT API client
        
        Args:
            base_url: Base URL of Snipe-IT instance (e.g., http://127.0.0.1:8000)
            api_token: JWT API token from Snipe-IT
        """
        self.base_url = base_url.rstrip('/')
        self.api_token = api_token
        self.session = requests.Session()
        
        # Set default headers
        self.session.headers.update({
            'Authorization': f'Bearer {api_token}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        })
    
    def _make_request(self, method: str, endpoint: str, data: Optional[Dict] = None, params: Optional[Dict] = None) -> Dict:
        """
        Make API request to Snipe-IT
        
        Args:
            method: HTTP method (GET, POST, PUT, PATCH, DELETE)
            endpoint: API endpoint (without /api/v1/)
            data: Request body data
            params: Query parameters
            
        Returns:
            API response as dictionary
        """
        url = f"{self.base_url}/api/v1/{endpoint}"
        
        try:
            response = self.session.request(
                method=method,
                url=url,
                json=data,
                params=params,
                timeout=30
            )
            
            # Snipe-IT returns 200 OK even for errors, check JSON status
            result = response.json()
            
            if result.get('status') == 'error':
                print(f"❌ API Error: {result.get('messages', 'Unknown error')}")
                return result
            
            return result
            
        except requests.exceptions.RequestException as e:
            print(f"❌ Request failed: {e}")
            return {'status': 'error', 'messages': str(e)}
        except json.JSONDecodeError as e:
            print(f"❌ JSON decode error: {e}")
            return {'status': 'error', 'messages': 'Invalid JSON response'}
    
    # ==================== ASSETS (HARDWARE) ====================
    
    def get_assets(self, limit: int = 500, offset: int = 0, search: str = None) -> Dict:
        """Get all assets"""
        params = {'limit': limit, 'offset': offset}
        if search:
            params['search'] = search
        
        return self._make_request('GET', 'hardware', params=params)
    
    def get_asset(self, asset_id: int) -> Dict:
        """Get specific asset by ID"""
        return self._make_request('GET', f'hardware/{asset_id}')
    
    def get_asset_by_tag(self, asset_tag: str) -> Dict:
        """Get asset by asset tag"""
        return self._make_request('GET', f'hardware/bytag/{asset_tag}')
    
    def get_asset_by_serial(self, serial: str) -> Dict:
        """Get asset by serial number"""
        return self._make_request('GET', f'hardware/byserial/{serial}')
    
    def create_asset(self, asset_data: Dict) -> Dict:
        """Create new asset"""
        return self._make_request('POST', 'hardware', data=asset_data)
    
    def update_asset(self, asset_id: int, asset_data: Dict) -> Dict:
        """Update existing asset"""
        return self._make_request('PUT', f'hardware/{asset_id}', data=asset_data)
    
    def delete_asset(self, asset_id: int) -> Dict:
        """Delete asset"""
        return self._make_request('DELETE', f'hardware/{asset_id}')
    
    def checkout_asset(self, asset_id: int, checkout_data: Dict) -> Dict:
        """Checkout asset to user"""
        return self._make_request('POST', f'hardware/{asset_id}/checkout', data=checkout_data)
    
    def checkin_asset(self, asset_id: int) -> Dict:
        """Checkin asset"""
        return self._make_request('POST', f'hardware/{asset_id}/checkin')
    
    # ==================== USERS ====================
    
    def get_users(self, limit: int = 500, offset: int = 0) -> Dict:
        """Get all users"""
        params = {'limit': limit, 'offset': offset}
        return self._make_request('GET', 'users', params=params)
    
    def get_user(self, user_id: int) -> Dict:
        """Get specific user"""
        return self._make_request('GET', f'users/{user_id}')
    
    def get_current_user(self) -> Dict:
        """Get current authenticated user"""
        return self._make_request('GET', 'users/me')
    
    def create_user(self, user_data: Dict) -> Dict:
        """Create new user"""
        return self._make_request('POST', 'users', data=user_data)
    
    def update_user(self, user_id: int, user_data: Dict) -> Dict:
        """Update user"""
        return self._make_request('PUT', f'users/{user_id}', data=user_data)
    
    # ==================== CATEGORIES ====================
    
    def get_categories(self) -> Dict:
        """Get all categories"""
        return self._make_request('GET', 'categories')
    
    def get_category(self, category_id: int) -> Dict:
        """Get specific category"""
        return self._make_request('GET', f'categories/{category_id}')
    
    def create_category(self, category_data: Dict) -> Dict:
        """Create new category"""
        return self._make_request('POST', 'categories', data=category_data)
    
    # ==================== MODELS ====================
    
    def get_models(self) -> Dict:
        """Get all models"""
        return self._make_request('GET', 'models')
    
    def get_model(self, model_id: int) -> Dict:
        """Get specific model"""
        return self._make_request('GET', f'models/{model_id}')
    
    def create_model(self, model_data: Dict) -> Dict:
        """Create new model"""
        return self._make_request('POST', 'models', data=model_data)
    
    # ==================== MANUFACTURERS ====================
    
    def get_manufacturers(self) -> Dict:
        """Get all manufacturers"""
        return self._make_request('GET', 'manufacturers')
    
    def get_manufacturer(self, manufacturer_id: int) -> Dict:
        """Get specific manufacturer"""
        return self._make_request('GET', f'manufacturers/{manufacturer_id}')
    
    def create_manufacturer(self, manufacturer_data: Dict) -> Dict:
        """Create new manufacturer"""
        return self._make_request('POST', 'manufacturers', data=manufacturer_data)
    
    # ==================== STATUS LABELS ====================
    
    def get_status_labels(self) -> Dict:
        """Get all status labels"""
        return self._make_request('GET', 'statuslabels')
    
    def get_status_label(self, status_id: int) -> Dict:
        """Get specific status label"""
        return self._make_request('GET', f'statuslabels/{status_id}')
    
    def create_status_label(self, status_data: Dict) -> Dict:
        """Create new status label"""
        return self._make_request('POST', 'statuslabels', data=status_data)
    
    # ==================== LOCATIONS ====================
    
    def get_locations(self) -> Dict:
        """Get all locations"""
        return self._make_request('GET', 'locations')
    
    def get_location(self, location_id: int) -> Dict:
        """Get specific location"""
        return self._make_request('GET', f'locations/{location_id}')
    
    def create_location(self, location_data: Dict) -> Dict:
        """Create new location"""
        return self._make_request('POST', 'locations', data=location_data)
    
    # ==================== COMPANIES ====================
    
    def get_companies(self) -> Dict:
        """Get all companies"""
        return self._make_request('GET', 'companies')
    
    def get_company(self, company_id: int) -> Dict:
        """Get specific company"""
        return self._make_request('GET', f'companies/{company_id}')
    
    def create_company(self, company_data: Dict) -> Dict:
        """Create new company"""
        return self._make_request('POST', 'companies', data=company_data)
    
    # ==================== UTILITY METHODS ====================
    
    def test_connection(self) -> bool:
        """Test API connection"""
        result = self.get_current_user()
        return result.get('status') == 'success'
    
    def get_api_version(self) -> Dict:
        """Get API version"""
        return self._make_request('GET', 'version')
    
    def search_assets(self, query: str) -> Dict:
        """Search assets by name, tag, or serial"""
        return self.get_assets(search=query)


def main():
    """Example usage of Snipe-IT API client"""
    
    # Your API configuration
    BASE_URL = "http://127.0.0.1:8000"
    API_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo"
    
    # Initialize client
    client = SnipeITClient(BASE_URL, API_TOKEN)
    
    print("🚀 Snipe-IT API Client Test")
    print("="*50)
    
    # Test connection
    print("🔍 Testing connection...")
    if client.test_connection():
        print("✅ Connection successful!")
        
        # Get current user info
        user_info = client.get_current_user()
        if user_info.get('status') == 'success':
            user = user_info.get('payload', {})
            print(f"👤 Logged in as: {user.get('first_name', '')} {user.get('last_name', '')}")
        
        # Get API version
        version = client.get_api_version()
        if version.get('status') == 'success':
            print(f"📋 API Version: {version.get('payload', {}).get('version', 'Unknown')}")
        
        # Get some assets
        print("\n📦 Getting assets...")
        assets = client.get_assets(limit=5)
        if assets.get('status') == 'success':
            asset_list = assets.get('rows', [])
            print(f"✅ Found {len(asset_list)} assets")
            for asset in asset_list[:3]:  # Show first 3
                print(f"   • {asset.get('name', 'Unnamed')} (Tag: {asset.get('asset_tag', 'N/A')})")
        else:
            print(f"❌ Failed to get assets: {assets.get('messages', 'Unknown error')}")
        
        # Get categories
        print("\n📂 Getting categories...")
        categories = client.get_categories()
        if categories.get('status') == 'success':
            category_list = categories.get('rows', [])
            print(f"✅ Found {len(category_list)} categories")
            for category in category_list[:3]:  # Show first 3
                print(f"   • {category.get('name', 'Unnamed')}")
        
        # Get users
        print("\n👥 Getting users...")
        users = client.get_users(limit=5)
        if users.get('status') == 'success':
            user_list = users.get('rows', [])
            print(f"✅ Found {len(user_list)} users")
            for user in user_list[:3]:  # Show first 3
                print(f"   • {user.get('first_name', '')} {user.get('last_name', '')} ({user.get('username', '')})")
        
    else:
        print("❌ Connection failed!")
        print("Please check:")
        print("  • Snipe-IT server is running")
        print("  • API token is valid")
        print("  • Network connectivity")


if __name__ == "__main__":
    main()
