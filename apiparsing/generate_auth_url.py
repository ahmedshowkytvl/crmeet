#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generate new authorization URL for Zoho API
"""

from config import ZohoConfig
import urllib.parse

def generate_auth_url():
    """Generate authorization URL for Zoho API"""
    config = ZohoConfig()
    
    print("Generating Zoho Authorization URL...")
    print("="*60)
    
    # Authorization URL parameters
    auth_params = {
        'response_type': 'code',
        'client_id': config.CLIENT_ID,
        'scope': 'Desk.tickets.READ,Desk.contacts.READ,Desk.tickets.UPDATE',
        'redirect_uri': 'https://www.google.com',
        'access_type': 'offline'
    }
    
    # Base URL
    base_url = "https://accounts.zoho.com/oauth/v2/auth"
    
    # Create query string
    query_string = urllib.parse.urlencode(auth_params)
    auth_url = f"{base_url}?{query_string}"
    
    print("Authorization URL:")
    print(f"{auth_url}")
    print("\n" + "="*60)
    print("Instructions:")
    print("1. Copy the URL above")
    print("2. Open it in your browser")
    print("3. Login to your Zoho account")
    print("4. Grant permissions to the application")
    print("5. You'll be redirected to Google with a 'code' parameter")
    print("6. Copy the code value from the URL")
    print("7. Update your config.py with the new authorization code")
    print("\nExample redirect URL:")
    print("https://www.google.com/?code=1000.NEW_CODE_HERE&location=...")
    print("\nAfter getting the new code, run:")
    print("python refresh_token.py")

if __name__ == "__main__":
    generate_auth_url()
