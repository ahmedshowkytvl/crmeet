#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test enhanced scope with better API access
"""

import requests
from config import ZohoConfig

def test_enhanced_scope():
    config = ZohoConfig()
    
    print("Testing enhanced scope...")
    print(f"Enhanced scope: {config.ENHANCED_SCOPE}")
    
    # Test refresh token with enhanced scope
    data = {
        'refresh_token': config.REFRESH_TOKEN,
        'client_id': config.CLIENT_ID,
        'client_secret': config.CLIENT_SECRET,
        'grant_type': 'refresh_token'
    }
    
    print("\nGetting access token...")
    response = requests.post(config.TOKEN_URL, data=data)
    
    if response.status_code == 200:
        token_data = response.json()
        access_token = token_data.get('access_token')
        print(f"Access token obtained: {access_token[:20]}...")
        
        # Test departments API
        print("\nTesting departments API...")
        headers = {
            "Authorization": f"Zoho-oauthtoken {access_token}",
            "orgId": config.ORG_ID,
            "contentType": "application/json; charset=utf-8"
        }
        
        # Try to get departments list
        dept_response = requests.get(f"{config.BASE_URLS['desk']}/departments", 
                                   headers=headers, 
                                   params={'orgId': config.ORG_ID})
        
        print(f"Departments API status: {dept_response.status_code}")
        if dept_response.status_code == 200:
            print("Departments API accessible!")
            dept_data = dept_response.json()
            if dept_data.get('data'):
                print("Available departments:")
                for dept in dept_data['data'][:5]:  # Show first 5
                    print(f"  - {dept.get('name', 'Unknown')} (ID: {dept.get('id', 'Unknown')})")
        else:
            print(f"Departments API error: {dept_response.text}")
        
        # Try to get agents list
        print("\nTesting agents API...")
        agents_response = requests.get(f"{config.BASE_URLS['desk']}/agents", 
                                     headers=headers, 
                                     params={'orgId': config.ORG_ID})
        
        print(f"Agents API status: {agents_response.status_code}")
        if agents_response.status_code == 200:
            print("Agents API accessible!")
            agents_data = agents_response.json()
            if agents_data.get('data'):
                print("Available agents:")
                for agent in agents_data['data'][:5]:  # Show first 5
                    name = f"{agent.get('firstName', '')} {agent.get('lastName', '')}".strip()
                    print(f"  - {name or 'Unknown'} (ID: {agent.get('id', 'Unknown')})")
        else:
            print(f"Agents API error: {agents_response.text}")
            
    else:
        print(f"Failed to get access token: {response.status_code}")
        print(f"Response: {response.text}")

if __name__ == "__main__":
    test_enhanced_scope()
