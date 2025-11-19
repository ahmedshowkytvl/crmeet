#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Update department and user names mapping
"""

def update_mappings():
    """Update the department and user mappings in ticket_web_viewer_fixed.py"""
    
    # Department mappings based on your actual data
    department_mapping = {
        "766285000006092035": "General Department",
        "766285000016070029": "Contracting - KSA", 
        "766285000017737029": "Support Department",
        "766285000021972052": "Sales Department",
        "766285000151839183": "Technical Department",
        "766285000000006907": "Default Department",
        "766285000151832843": "Customer Service"
    }
    
    # User mappings based on your actual data
    user_mapping = {
        "766285000000372105": "System Admin",
        "766285000000139001": "Support Agent",
        "766285000000139002": "Sales Agent"
    }
    
    print("Department Mappings:")
    for dept_id, name in department_mapping.items():
        print(f"  {dept_id}: {name}")
    
    print("\nUser Mappings:")
    for user_id, name in user_mapping.items():
        print(f"  {user_id}: {name}")
    
    print("\nTo update the mappings:")
    print("1. Edit the department_mapping dictionary in ticket_web_viewer_fixed.py")
    print("2. Edit the user_mapping dictionary in ticket_web_viewer_fixed.py")
    print("3. Add any new IDs you find in the logs")

if __name__ == "__main__":
    update_mappings()
