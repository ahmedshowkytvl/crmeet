#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Test ticket data structure
"""

from zoho_api import ZohoAPI

def test_ticket_data():
    zoho = ZohoAPI()
    token = zoho.get_access_token()
    
    if not token:
        print("Failed to get access token")
        return
    
    # Get a ticket ID first
    tickets = zoho.get_tickets(limit=1)
    if not tickets or not tickets.get('data'):
        print("No tickets found")
        return
    
    ticket_id = tickets['data'][0]['id']
    print(f'Testing ticket ID: {ticket_id}')
    
    # Get ticket details
    ticket_response = zoho.make_request('GET', f"{zoho.config.BASE_URLS['desk']}/tickets/{ticket_id}", 
                                      params={'orgId': zoho.config.ORG_ID})
    
    if ticket_response:
        print("\n=== TICKET DETAILS ===")
        print('Department data:', ticket_response.get('department'))
        print('ModifiedBy data:', ticket_response.get('modifiedBy'))
        print('DepartmentId:', ticket_response.get('departmentId'))
        print('AssigneeId:', ticket_response.get('assigneeId'))
        
        # Check all keys related to department/modified
        print("\n=== ALL RELEVANT KEYS ===")
        for key, value in ticket_response.items():
            if any(word in key.lower() for word in ['department', 'modified', 'assignee', 'created']):
                print(f'  {key}: {value}')
    else:
        print("Failed to get ticket details")

if __name__ == "__main__":
    test_ticket_data()
