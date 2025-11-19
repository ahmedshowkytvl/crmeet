#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Refresh Zoho API token using authorization code
"""

import requests
from config import ZohoConfig

def refresh_access_token():
    """Refresh access token using authorization code"""
    config = ZohoConfig()
    
    print("Refreshing Zoho API Token...")
    print("="*50)
    
    # Use authorization code to get new tokens
    token_data = {
        'grant_type': 'authorization_code',
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'redirect_uri': 'https://www.google.com',
        'code': config.AUTHORIZATION_CODE
    }
    
    try:
        print("Requesting new tokens...")
        response = requests.post(config.TOKEN_URL, data=token_data)
        
        if response.status_code == 200:
            token_response = response.json()
            
            print("Successfully obtained new tokens!")
            print(f"New Access Token: {token_response.get('access_token', 'N/A')[:20]}...")
            print(f"New Refresh Token: {token_response.get('refresh_token', 'N/A')[:20]}...")
            print(f"Expires In: {token_response.get('expires_in', 'N/A')} seconds")
            
            # Update config file
            update_config_file(token_response.get('refresh_token'))
            
        else:
            print(f"Failed to refresh token: {response.status_code}")
            print(f"Response: {response.text}")
            
    except Exception as e:
        print(f"Error refreshing token: {e}")

def update_config_file(new_refresh_token):
    """Update config.py with new refresh token"""
    try:
        # Read current config
        with open('config.py', 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Replace refresh token
        import re
        pattern = r'REFRESH_TOKEN = "[^"]*"'
        replacement = f'REFRESH_TOKEN = "{new_refresh_token}"'
        new_content = re.sub(pattern, replacement, content)
        
        # Write updated config
        with open('config.py', 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print("Updated config.py with new refresh token")
        
    except Exception as e:
        print(f"Error updating config file: {e}")

if __name__ == "__main__":
    refresh_access_token()
