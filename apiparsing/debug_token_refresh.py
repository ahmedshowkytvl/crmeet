#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Debug token refresh process
"""

import requests
import json
from config import ZohoConfig

def debug_token_refresh():
    """Debug token refresh with detailed output"""
    config = ZohoConfig()
    
    print("Debugging Token Refresh...")
    print("="*50)
    
    print(f"Client ID: {config.CLIENT_ID}")
    print(f"Authorization Code: {config.AUTHORIZATION_CODE[:20]}...")
    
    # Use authorization code to get new tokens
    token_data = {
        'grant_type': 'authorization_code',
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'redirect_uri': 'https://www.google.com',
        'code': config.AUTHORIZATION_CODE
    }
    
    print(f"\nRequest data:")
    for key, value in token_data.items():
        if key == 'code':
            print(f"   {key}: {value[:20]}...")
        else:
            print(f"   {key}: {value}")
    
    try:
        print(f"\nSending request to: {config.TOKEN_URL}")
        response = requests.post(config.TOKEN_URL, data=token_data)
        
        print(f"Response Status: {response.status_code}")
        print(f"Response Headers: {dict(response.headers)}")
        
        try:
            response_json = response.json()
            print(f"Response JSON: {json.dumps(response_json, indent=2)}")
        except:
            print(f"Response Text: {response.text}")
            
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    debug_token_refresh()
