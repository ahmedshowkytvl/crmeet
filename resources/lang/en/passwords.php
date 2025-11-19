<?php

return [
    // General
    'password_management' => 'Password Management',
    'password_accounts' => 'Password Accounts',
    'password_account' => 'Password Account',
    'create_account' => 'Create New Account',
    'add_new_credentials' => 'ADD New Credentials',
    'edit_account' => 'Edit Account',
    'delete_account' => 'Delete Account',
    'view_password' => 'View Password',
    'hide_password' => 'Hide Password',
    'copy_password' => 'Copy Password',
    'password_copied' => 'Password copied to clipboard',
    
    // Account Fields
    'account_name' => 'Account Name',
    'account_name_ar' => 'Account Name (Arabic)',
    'email_username' => 'Email / Username',
    'email_username_placeholder' => 'Enter email or username',
    'email_username_hint' => 'You can enter email, username, or any other identifier',
    'password' => 'Password',
    'confirm_password' => 'Confirm Password',
    'login_url' => 'Login URL',
    'notes' => 'Notes',
    'notes_ar' => 'Notes (Arabic)',
    'category' => 'Category',
    'category_ar' => 'Category (Arabic)',
    'icon' => 'Icon',
    'requires_2fa' => 'Requires 2FA',
    'expires_at' => 'Expires At',
    'is_shared' => 'Shared Account',
    'is_active' => 'Active',
    
    // Categories
    'social_media' => 'Social Media',
    'work_tools' => 'Work Tools',
    'email_services' => 'Email Services',
    'cloud_storage' => 'Cloud Storage',
    'development' => 'Development',
    'design' => 'Design',
    'marketing' => 'Marketing',
    'finance' => 'Finance',
    'other' => 'Other',
    
    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'expired' => 'Expired',
    'expiring_soon' => 'Expiring Soon',
    'shared' => 'Shared',
    'private' => 'Private',
    
    // Access Levels
    'read_only' => 'Read Only',
    'manage' => 'Manage',
    'can_view_password' => 'Can View Password',
    'can_edit_password' => 'Can Edit Password',
    'can_delete_account' => 'Can Delete Account',
    
    // Actions
    'assign' => 'Assign',
    'unassign' => 'Unassign',
    'assign_to_users' => 'Assign to Users',
    'assigned_users' => 'Assigned Users',
    'available_users' => 'Available Users',
    'search_users' => 'Search Users',
    
    // Audit Logs
    'audit_logs' => 'Audit Logs',
    'action' => 'Action',
    'performed_by' => 'Performed By',
    'performed_at' => 'Performed At',
    'ip_address' => 'IP Address',
    'user_agent' => 'User Agent',
    'old_values' => 'Old Values',
    'new_values' => 'New Values',
    
    // Audit Actions
    'viewed' => 'Viewed',
    'created' => 'Created',
    'updated' => 'Updated',
    'deleted' => 'Deleted',
    'assigned' => 'Assigned',
    'unassigned' => 'Unassigned',
    'password_changed' => 'Password Changed',
    'expired' => 'Expired',
    'expiring_soon' => 'Expiring Soon',
    
    // Messages
    'account_created_successfully' => 'Account created successfully',
    'account_updated_successfully' => 'Account updated successfully',
    'account_deleted_successfully' => 'Account deleted successfully',
    'password_updated_successfully' => 'Password updated successfully',
    'assignment_created_successfully' => 'Assignment created successfully',
    'assignment_revoked_successfully' => 'Assignment revoked successfully',
    'no_accounts_found' => 'No accounts found',
    'no_assignments_found' => 'No assignments found',
    'no_audit_logs_found' => 'No audit logs found',
    
    // Validation Messages
    'name_required' => 'Account name is required',
    'password_required' => 'Password is required',
    'password_min_length' => 'Password must be at least 6 characters',
    'email_invalid' => 'Invalid email address',
    'url_invalid' => 'Invalid URL',
    'expires_at_after_today' => 'Expiration date must be after today',
    
    // Filters
    'filter_by_category' => 'Filter by Category',
    'filter_by_status' => 'Filter by Status',
    'search_accounts' => 'Search Accounts',
    'clear_filters' => 'Clear Filters',
    'all_categories' => 'All Categories',
    'all_statuses' => 'All Statuses',
    
    // Statistics
    'total_accounts' => 'Total Accounts',
    'active_accounts' => 'Active Accounts',
    'expired_accounts' => 'Expired Accounts',
    'expiring_soon_accounts' => 'Expiring Soon',
    'shared_accounts' => 'Shared Accounts',
    'private_accounts' => 'Private Accounts',
    
    // Security
    'password_security_warning' => 'Security Warning: Passwords are encrypted and protected',
    'view_password_warning' => 'Warning: Viewing password will be logged in audit trail',
    'password_expiry_warning' => 'Warning: Password expires soon',
    'password_expired_warning' => 'Warning: Password has expired',
    
    // Notifications
    'password_expiry_notification' => 'Password Expiry Notification',
    'password_changed_notification' => 'Password Changed Notification',
    'account_assigned_notification' => 'Account Assigned Notification',
    'account_unassigned_notification' => 'Account Unassigned Notification',
    
    // Export
    'export_accounts' => 'Export Accounts',
    'export_audit_logs' => 'Export Audit Logs',
    'export_format' => 'Export Format',
    'csv_format' => 'CSV',
    'excel_format' => 'Excel',
    'pdf_format' => 'PDF',
    
    // Batch Actions
    'batch_actions' => 'Batch Actions',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    'batch_delete' => 'Batch Delete',
    'batch_export' => 'Batch Export',
    'batch_assign' => 'Batch Assign',
    'no_accounts_selected' => 'No accounts selected',
    'confirm_batch_delete' => 'Are you sure you want to delete',
    'accounts' => 'accounts',
    'batch_delete_success' => 'Successfully deleted :count accounts',
    'batch_assign_success' => 'Successfully assigned :count accounts',
    'no_accounts_or_users_selected' => 'No accounts or users selected',
    'unsupported_export_format' => 'Unsupported export format',
    'shared' => 'Shared',
    'private' => 'Private',
    'yes' => 'Yes',
    'no' => 'No',
    'select_users_to_assign' => 'Select Users to Assign',
    'access_level' => 'Access Level',
    'select_users' => 'Select Users',
    'selected_accounts' => 'Selected Accounts',
    'batch_assign_info' => ':count accounts will be assigned to selected users',
    'assign_accounts' => 'Assign Accounts',
    'please_select_at_least_one_user' => 'Please select at least one user',
    
    // AI Notes Generation
    'generate_ai_notes' => 'Generate AI Notes',
    'ai_notes_generated_successfully' => 'AI notes generated successfully!',
    'failed_to_generate_notes' => 'Failed to generate notes',
    'an_error_occurred_while_generating_notes' => 'An error occurred while generating notes',
    'please_enter_account_name_first' => 'Please enter account name first',
    'generating' => 'Generating...',
    'ai_notes_description' => 'Click the button to generate AI-powered security notes based on your account details.',
    'please_log_in_to_use_this_feature' => 'Please log in to use this feature',
];
