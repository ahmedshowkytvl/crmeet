#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test token refresh
"""

import requests
import json
from config import ZohoConfig

def test_token_refresh():
    config = ZohoConfig()
    
    print("Testing token refresh...")
    print(f"Token URL: {config.TOKEN_URL}")
    print(f"Client ID: {config.CLIENT_ID}")
    print(f"Refresh Token: {config.REFRESH_TOKEN[:20]}...")
    
    token_data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    print("\nSending request...")
    response = requests.post(config.TOKEN_URL, data=token_data)
    
    print(f"Status Code: {response.status_code}")
    print(f"Response: {response.text}")
    
    if response.status_code == 200:
        token_info = response.json()
        print(f"\nSuccess! Access Token: {token_info.get('access_token', 'N/A')[:20]}...")
        return token_info.get('access_token')
    else:
        print("\nFailed to get access token")
        return None

if __name__ == "__main__":
    test_token_refresh()

