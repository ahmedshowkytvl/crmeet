<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BatchEmployeeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeRequestController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ContactCardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactCategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DynamicOrgChartController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AddAdminUserController;
use App\Http\Controllers\BatchUserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetLocationController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetLogController;
use App\Http\Controllers\AssetDashboardController;
use App\Http\Controllers\WarehouseCabinetController;
use App\Http\Controllers\AssetMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatValidationController;
use App\Http\Controllers\ZohoStatsController;
use App\Http\Controllers\ZohoAdminController;
use App\Http\Controllers\ZohoDepartmentMappingController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\TaskProgressController;
use App\Http\Controllers\EetLifeController;
use App\Http\Controllers\EventManagementController;
use App\Http\Controllers\AnnouncementManagementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\SnipeItController;
use App\Http\Controllers\TaskDashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ZohoAdvancedSearchController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MeetingRoomManagementController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Language switching
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Broadcasting Routes (for WebSocket authentication)
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Notification Routes - Fixed to avoid conflict with login redirect
Route::middleware('auth')->group(function () {
    Route::get('/notifications/api', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
});

// Chat Validation Routes
Route::middleware('auth')->group(function () {
    Route::get('/api/chat/validate/{roomId}', [ChatValidationController::class, 'validatePrivateChat'])->name('chat.validate');
    Route::post('/api/chat/create-private', [ChatValidationController::class, 'createPrivateChat'])->name('chat.create-private');
    Route::post('/api/chat/create-group', [ChatValidationController::class, 'createGroupRoom'])->name('chat.create-group');
});

    // Health Check
    Route::get('/api/health-check', [HealthCheckController::class, 'check'])->name('health.check');

    // Suppliers
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'suppliers.index',
        'create' => 'suppliers.create',
        'store' => 'suppliers.store',
        'show' => 'suppliers.show',
        'edit' => 'suppliers.edit',
        'update' => 'suppliers.update',
        'destroy' => 'suppliers.destroy',
    ]);
    Route::post('suppliers/{supplier}/archive', [SupplierController::class, 'archive'])->name('suppliers.archive');
    Route::post('suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    Route::get('suppliers-archived', [SupplierController::class, 'archived'])->name('suppliers.archived');
    
    // Supplier Notes Routes
    Route::post('suppliers/{supplier}/notes', [SupplierController::class, 'storeNote'])->name('suppliers.notes.store');
    Route::put('supplier-notes/{note}', [SupplierController::class, 'updateNote'])->name('supplier.notes.update');
    Route::delete('supplier-notes/{note}', [SupplierController::class, 'deleteNote'])->name('supplier.notes.delete');

// Test Error Page
Route::get('/test-error', function() {
    return view('errors.test');
});

// Home Page (New Frontend)
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Dashboard (Old)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Users Routes
Route::middleware(['auth'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    
    // Batch Edit Routes - Must be before parameterized routes
    Route::get('users/batch-edit', [UserController::class, 'batchEdit'])->name('users.batch-edit')->middleware('permission:users.edit');
    Route::put('users/batch-update', [UserController::class, 'batchUpdate'])->name('users.batch-update')->middleware('permission:users.edit');
    Route::delete('users/batch-delete', [UserController::class, 'batchDelete'])->name('users.batch-delete')->middleware('permission:users.delete');
    
    // Advanced Batch User Creation Routes - Must be before parameterized routes
    Route::get('users/batch-create', [BatchUserController::class, 'showAdvancedBatchCreate'])->name('users.batch-create');
    Route::post('users/batch-create', [BatchUserController::class, 'batchCreate'])->name('users.batch-create.store');
    
    // Parameterized routes - Must be after specific routes
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');
    Route::post('users/{user}/archive', [UserController::class, 'archive'])->name('users.archive')->middleware('permission:users.delete');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->middleware('permission:users.delete');
    Route::get('users-archived', [UserController::class, 'archived'])->name('users.archived')->middleware('permission:users.view');
    Route::get('users/{user}/contact-card', [ContactCardController::class, 'show'])->name('users.contact-card');
    Route::get('users/{user}/edit-contact-card', [ContactCardController::class, 'edit'])->name('users.edit-contact-card');
    Route::put('users/{user}/update-contact-card', [ContactCardController::class, 'update'])->name('users.update-contact-card');
    Route::post('users/{user}/quick-message', [ContactCardController::class, 'sendQuickMessage'])->name('users.quick-message');
    Route::get('users/{user}/quick-chat', [ContactCardController::class, 'sendQuickMessage'])->name('users.quick-chat');
    Route::post('users/{user}/schedule-meeting', [ContactCardController::class, 'scheduleMeeting'])->name('users.schedule-meeting');
    Route::post('users/{user}/privacy-settings', [ContactCardController::class, 'updatePrivacySettings'])->name('users.privacy-settings');
    
    // Batch Employee Management Routes
    Route::get('batch/create', [BatchEmployeeController::class, 'create'])->name('batch.create');
    Route::post('batch/store', [BatchEmployeeController::class, 'store'])->name('batch.store');
    Route::get('batch/template', [BatchEmployeeController::class, 'downloadTemplate'])->name('batch.template');
    
    
    // Contacts Routes
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::get('contacts/quick-search', [ContactController::class, 'quickSearch'])->name('contacts.quick-search');
    Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    
    // Contact Categories Routes
    Route::resource('contact-categories', ContactCategoryController::class);
    Route::patch('contact-categories/{contactCategory}/toggle-status', [ContactCategoryController::class, 'toggleStatus'])->name('contact-categories.toggle-status');
    Route::post('contact-categories/update-order', [ContactCategoryController::class, 'updateOrder'])->name('contact-categories.update-order');
    Route::get('contact-categories-api', [ContactCategoryController::class, 'getCategories'])->name('contact-categories.api');
});

// Tasks Routes
Route::resource('tasks', TaskController::class);
Route::patch('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

// Task Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('task-dashboard', [TaskDashboardController::class, 'index'])->name('task-dashboard');
    Route::get('task-dashboard/recent-completed', [TaskDashboardController::class, 'getRecentCompletedTasksData'])->name('task-dashboard.recent-completed');
    
    // Schedule Calendar Routes
    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    
        // Meeting Rooms Management Routes
        Route::get('schedule/rooms', [MeetingRoomManagementController::class, 'index'])->name('schedule.rooms.index');
        Route::get('schedule/rooms/book', [MeetingRoomManagementController::class, 'book'])->name('schedule.rooms.book');
        Route::get('schedule/rooms/book/{id}', [MeetingRoomManagementController::class, 'book'])->name('schedule.rooms.book.room');
        
        // Meeting Rooms API (session-based for all requests)
        Route::get('api/schedule/meeting-rooms', [\App\Http\Controllers\Api\MeetingRoomController::class, 'index']);
        Route::post('api/schedule/meeting-rooms', [\App\Http\Controllers\Api\MeetingRoomController::class, 'store']);
        Route::get('api/schedule/meeting-rooms/available', [\App\Http\Controllers\Api\MeetingRoomController::class, 'available']);
        Route::get('api/schedule/meeting-rooms/{id}', [\App\Http\Controllers\Api\MeetingRoomController::class, 'show']);
        Route::put('api/schedule/meeting-rooms/{id}', [\App\Http\Controllers\Api\MeetingRoomController::class, 'update']);
        Route::delete('api/schedule/meeting-rooms/{id}', [\App\Http\Controllers\Api\MeetingRoomController::class, 'destroy']);
        Route::get('api/schedule/meeting-rooms/{id}/available-time-slots', [\App\Http\Controllers\Api\MeetingRoomController::class, 'availableTimeSlots']);
        Route::get('api/schedule/meeting-rooms/{id}/bookings', [\App\Http\Controllers\Api\MeetingRoomController::class, 'bookings']);
        Route::post('api/schedule/meeting-rooms/{id}/book', [\App\Http\Controllers\Api\MeetingRoomController::class, 'book']);
    
    // Temporary Employee Management Routes
    Route::get('/employees/temp-management', [EmployeeController::class, 'tempManagement'])->name('employees.temp-management');
    Route::get('/employees/temp-data', [EmployeeController::class, 'getTempData'])->name('employees.temp-data');
    Route::put('/employees/{id}/temp-update', [EmployeeController::class, 'updateTempData'])->name('employees.temp-update');
    Route::post('/employees/bulk-column-transfer', [EmployeeController::class, 'bulkColumnTransfer'])->name('employees.bulk-column-transfer');
});

// Comments Routes
Route::middleware(['auth'])->group(function () {
    Route::post('comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::put('comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
});

// Manager Routes
Route::middleware(['auth', 'permission:users.manage_team'])->group(function () {
    Route::get('manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('manager/team-members', [ManagerController::class, 'teamMembers'])->name('manager.team-members');
    Route::get('manager/team-tasks', [ManagerController::class, 'teamTasks'])->name('manager.team-tasks');
    Route::get('manager/team-requests', [ManagerController::class, 'teamRequests'])->name('manager.team-requests');
    Route::post('manager/requests/{request}/approve', [ManagerController::class, 'approveRequest'])->name('manager.approve-request');
});

// Departments Routes
Route::resource('departments', DepartmentController::class);
Route::get('departments/{department}/organizational-chart', [DepartmentController::class, 'organizationalChart'])->name('departments.organizational-chart');
Route::get('departments/{department}/zoho-tickets', [DepartmentController::class, 'zohoTickets'])->name('departments.zoho-tickets');
Route::post('departments/{department}/refresh-closed-by', [DepartmentController::class, 'refreshClosedBy'])->name('departments.refresh-closed-by');

    // Employee Requests Routes
    Route::resource('requests', EmployeeRequestController::class);
    Route::patch('requests/{request}/status', [EmployeeRequestController::class, 'updateStatus'])->name('requests.update-status');
    
    // Batch User Creation Routes (Additional API routes)
    Route::post('/users/excel-upload', [BatchUserController::class, 'processExcelFile'])->name('users.excel-upload');
    Route::get('/api/departments', [BatchUserController::class, 'getDepartments'])->name('api.departments');
    Route::get('/api/system-stats', [BatchUserController::class, 'getSystemStats'])->name('api.system-stats');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Settings Routes
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::put('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    
    // Role Management Routes
    Route::resource('roles', RoleController::class);
    Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
    
    // Employee Profile Routes
    Route::get('employee-profiles/{user}', [EmployeeProfileController::class, 'show'])->name('employee-profiles.show');
    Route::get('employee-profiles/{user}/edit', [EmployeeProfileController::class, 'edit'])->name('employee-profiles.edit');
    Route::put('employee-profiles/{user}', [EmployeeProfileController::class, 'update'])->name('employee-profiles.update');
    Route::post('employee-profiles/{user}/documents', [EmployeeProfileController::class, 'storeDocument'])->name('employee-profiles.store-document');
    Route::delete('employee-profiles/documents/{document}', [EmployeeProfileController::class, 'deleteDocument'])->name('employee-profiles.delete-document');
    Route::get('team-members', [EmployeeProfileController::class, 'teamMembers'])->name('employee-profiles.team-members');
    Route::get('shared-tasks', [EmployeeProfileController::class, 'sharedTasks'])->name('employee-profiles.shared-tasks');
});

// Organizational Chart Route (Separate Full Screen Page)
Route::get('/org-chart', [OrgChartController::class, 'index'])->name('org-chart');

// Dynamic Organizational Chart Routes
Route::get('/dynamic-org-chart', [DynamicOrgChartController::class, 'index'])->name('dynamic-org-chart');
Route::get('/api/organizational-chart', [DynamicOrgChartController::class, 'getData'])->name('api.organizational-chart');
Route::get('/api/org-statistics', [DynamicOrgChartController::class, 'getStatistics'])->name('api.org-statistics');
Route::get('/api/department-hierarchy', [DynamicOrgChartController::class, 'getDepartmentHierarchy'])->name('api.department-hierarchy');
Route::post('/api/org-export', [DynamicOrgChartController::class, 'export'])->name('api.org-export');

// Password Reset Route (Temporary)
Route::get('/reset-admin-password', [PasswordResetController::class, 'resetPassword'])->name('reset-admin-password');

// Add Admin User Routes (Temporary)
Route::get('/add-admin-user', [AddAdminUserController::class, 'addAdminUser'])->name('add-admin-user');
Route::get('/check-admin-user', [AddAdminUserController::class, 'checkAdminUser'])->name('check-admin-user');
Route::get('/system-stats', [AddAdminUserController::class, 'getSystemStats'])->name('system-stats');

// Assets Control System Routes
Route::middleware(['auth'])->prefix('assets')->name('assets.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AssetDashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [AssetDashboardController::class, 'statistics'])->name('statistics');
    
    // Assets
    Route::resource('assets', AssetController::class);
    Route::get('assets/{asset}/print-barcode', [AssetController::class, 'printBarcode'])->name('assets.print-barcode');
    Route::get('assets/{asset}/download-barcode', [AssetController::class, 'downloadBarcode'])->name('assets.download-barcode');
    
    // Categories
    Route::resource('categories', AssetCategoryController::class)->names([
        'index' => 'asset-categories.index',
        'create' => 'asset-categories.create',
        'store' => 'asset-categories.store',
        'show' => 'asset-categories.show',
        'edit' => 'asset-categories.edit',
        'update' => 'asset-categories.update',
        'destroy' => 'asset-categories.destroy',
    ]);
    Route::patch('categories/{category}/toggle-status', [AssetCategoryController::class, 'toggleStatus'])->name('asset-categories.toggle-status');
    Route::get('categories/{category}/properties', [AssetCategoryController::class, 'showProperties'])->name('asset-categories.show-properties');
    Route::get('categories/properties/{property}', [AssetCategoryController::class, 'showProperty'])->name('asset-categories.show-property');
    Route::post('categories/{category}/properties', [AssetCategoryController::class, 'storeProperty'])->name('asset-categories.store-property');
    Route::put('properties/{property}', [AssetCategoryController::class, 'updateProperty'])->name('properties.update');
    Route::delete('properties/{property}', [AssetCategoryController::class, 'destroyProperty'])->name('properties.destroy');
    
    // Locations
    Route::resource('locations', AssetLocationController::class)->names([
        'index' => 'locations.index',
        'create' => 'locations.create',
        'store' => 'locations.store',
        'show' => 'locations.show',
        'edit' => 'locations.edit',
        'update' => 'locations.update',
        'destroy' => 'locations.destroy',
    ]);
    Route::patch('locations/{location}/toggle-status', [AssetLocationController::class, 'toggleStatus'])->name('locations.toggle-status');
    
    // Assignments
    Route::resource('assignments', AssetAssignmentController::class)->names([
        'index' => 'assignments.index',
        'create' => 'assignments.create',
        'store' => 'assignments.store',
        'show' => 'assignments.show',
    ]);
    Route::get('assignments/{assignment}/return', [AssetAssignmentController::class, 'showReturnForm'])->name('assignments.return');
    Route::post('assignments/{assignment}/return', [AssetAssignmentController::class, 'return'])->name('assignments.return.store');
    Route::get('assignments/assets', [AssetAssignmentController::class, 'getAssets'])->name('assignments.assets');
    
    // Logs
    Route::get('logs', [AssetLogController::class, 'index'])->name('logs.index');
    Route::get('logs/asset/{asset}', [AssetLogController::class, 'asset'])->name('logs.asset');
    Route::get('logs/export', [AssetLogController::class, 'export'])->name('logs.export');
    
    // Warehouse Cabinets
    Route::resource('cabinets', WarehouseCabinetController::class)->names([
        'index' => 'warehouse.cabinets.index',
        'create' => 'warehouse.cabinets.create',
        'store' => 'warehouse.cabinets.store',
        'show' => 'warehouse.cabinets.show',
        'edit' => 'warehouse.cabinets.edit',
        'update' => 'warehouse.cabinets.update',
        'destroy' => 'warehouse.cabinets.destroy',
    ]);
    Route::get('cabinets/{cabinet}/shelves', [WarehouseCabinetController::class, 'manageShelves'])->name('warehouse.cabinets.shelves');
    Route::post('cabinets/{cabinet}/shelves', [WarehouseCabinetController::class, 'addShelf'])->name('warehouse.cabinets.add-shelf');
    Route::put('shelves/{shelf}', [WarehouseCabinetController::class, 'updateShelf'])->name('warehouse.shelves.update');
    Route::delete('shelves/{shelf}', [WarehouseCabinetController::class, 'deleteShelf'])->name('warehouse.shelves.delete');
    
    // Asset Movement
    Route::get('movement', [AssetMovementController::class, 'index'])->name('movement.index');
    Route::get('movement/{asset}', [AssetMovementController::class, 'show'])->name('movement.show');
    Route::get('movement/{asset}/store', [AssetMovementController::class, 'storeForm'])->name('movement.store');
    Route::post('movement/{asset}/store', [AssetMovementController::class, 'store'])->name('movement.store.store');
    Route::get('movement/{asset}/checkout', [AssetMovementController::class, 'checkoutForm'])->name('movement.checkout');
    Route::post('movement/{asset}/checkout', [AssetMovementController::class, 'checkout'])->name('movement.checkout.store');
    Route::get('movement/{asset}/return', [AssetMovementController::class, 'returnForm'])->name('movement.return');
    Route::post('movement/{asset}/return', [AssetMovementController::class, 'return'])->name('movement.return.store');
    Route::get('movement/{asset}/move', [AssetMovementController::class, 'moveForm'])->name('movement.move');
    Route::post('movement/{asset}/move', [AssetMovementController::class, 'move'])->name('movement.move.store');
    Route::post('movement/{asset}/maintenance', [AssetMovementController::class, 'maintenance'])->name('movement.maintenance');
    Route::get('api/available-shelves', [AssetMovementController::class, 'getAvailableShelves'])->name('api.available-shelves');
    Route::get('movement/{asset}/export-history', [AssetMovementController::class, 'exportHistory'])->name('movement.export-history');
});

// Routes for image editor
Route::get('/image-editor', function () {
    return view('image-editor');
})->name('image-editor');

// Route for uploading cropped images
Route::post('/upload-cropped-image', function (Request $request) {
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = 'profile_' . time() . '.jpg';
        $path = $file->storeAs('public/profile_photos', $filename);
        
        return response()->json([
            'success' => true,
            'imagePath' => 'profile_photos/' . $filename,
            'url' => asset('storage/' . $path)
        ]);
    }
    
    return response()->json(['success' => false, 'message' => 'لم يتم رفع الصورة']);
});

// System Monitor Routes
Route::get('/system-monitor', [App\Http\Controllers\SystemMonitorController::class, 'index'])->name('system-monitor');
Route::get('/api/system-monitor/data', [App\Http\Controllers\SystemMonitorController::class, 'getSystemData'])->name('api.system-monitor.data');

// Direct Chat Route (without CSRF) - تم نقله إلى المجموعة أدناه

// Chat System Routes
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    // Chat Rooms
    Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('index');
    Route::get('/start', [App\Http\Controllers\ChatController::class, 'startChat'])->name('start');
    Route::post('/start', [App\Http\Controllers\ChatController::class, 'startChatWithUser'])->name('start.post');
    Route::post('/quick', [App\Http\Controllers\ChatController::class, 'startQuickChat'])->name('quick');
    Route::post('/direct', [App\Http\Controllers\ChatController::class, 'startDirectChat'])->name('direct');
    
    // Static Chat Interface Route (moved to main index)
    Route::get('/static', [App\Http\Controllers\ChatController::class, 'staticChat'])->name('static');
        Route::post('/static/send', [App\Http\Controllers\ChatController::class, 'sendStaticMessage'])->name('static.send');
        Route::get('/api/users', [App\Http\Controllers\ChatController::class, 'getUsers'])->name('api.users')->middleware('auth');
    
    // New API Routes for Modern Chat Interface
    Route::get('/api/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('api.conversations');
    Route::get('/api/messages/{chatId}', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('api.messages');
    Route::post('/api/create', [App\Http\Controllers\ChatController::class, 'createChat'])->name('api.create');
    Route::post('/api/{chatId}/mark-read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('api.mark-read');
    Route::delete('/api/conversations/{conversationId}', [App\Http\Controllers\ChatController::class, 'deleteConversation'])->name('api.delete-conversation');
    Route::get('/api/search/global', [App\Http\Controllers\ChatController::class, 'globalSearch'])->name('api.search.global');
    
    // Messages - يجب أن تكون قبل routes الـ {id}
    Route::post('/messages/send-text', [App\Http\Controllers\ChatMessageController::class, 'sendText'])->name('messages.send-text');
    
    // Chat Notifications
    Route::get('/api/notifications/unread-count', [App\Http\Controllers\ChatNotificationController::class, 'getUnreadCount'])->name('api.notifications.unread-count');
    Route::get('/api/notifications/recent', [App\Http\Controllers\ChatNotificationController::class, 'getRecentNotifications'])->name('api.notifications.recent');
    Route::post('/api/notifications/mark-read', [App\Http\Controllers\ChatNotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::post('/api/notifications/mark-all-read', [App\Http\Controllers\ChatNotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
    Route::get('/api/stats', [App\Http\Controllers\ChatNotificationController::class, 'getChatStats'])->name('api.stats');
    Route::post('/messages/send-contact', [App\Http\Controllers\ChatMessageController::class, 'sendContact'])->name('messages.send-contact');
    Route::get('/messages/{chatRoomId}', [App\Http\Controllers\ChatMessageController::class, 'getMessages'])->name('messages.get');
    Route::post('/messages/{messageId}/read', [App\Http\Controllers\ChatMessageController::class, 'markAsRead'])->name('messages.read');
    Route::post('/messages/{chatRoomId}/read-all', [App\Http\Controllers\ChatMessageController::class, 'markAllAsRead'])->name('messages.read-all');
    
    Route::get('/search/rooms', [App\Http\Controllers\ChatController::class, 'search'])->name('search.rooms');
    Route::get('/search/users', [App\Http\Controllers\ChatController::class, 'searchUsers'])->name('search.users');
    Route::get('/stats/overview', [App\Http\Controllers\ChatController::class, 'getStats'])->name('stats.overview');
    
    Route::get('/{id}', [App\Http\Controllers\ChatController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::post('/{id}/archive', [App\Http\Controllers\ChatController::class, 'archive'])->name('archive');
    Route::post('/{id}/unarchive', [App\Http\Controllers\ChatController::class, 'unarchive'])->name('unarchive');
    Route::post('/{id}/mute', [App\Http\Controllers\ChatController::class, 'mute'])->name('mute');
    Route::post('/{id}/unmute', [App\Http\Controllers\ChatController::class, 'unmute'])->name('unmute');
    Route::put('/messages/{messageId}/edit', [App\Http\Controllers\ChatMessageController::class, 'edit'])->name('messages.edit');
    Route::delete('/messages/{messageId}', [App\Http\Controllers\ChatMessageController::class, 'delete'])->name('messages.delete');
    Route::get('/messages/{chatRoomId}/search', [App\Http\Controllers\ChatMessageController::class, 'search'])->name('messages.search');
    Route::get('/messages/{chatRoomId}/stats', [App\Http\Controllers\ChatMessageController::class, 'getStats'])->name('messages.stats');
    
    // Files
    Route::post('/files/upload', [App\Http\Controllers\ChatFileController::class, 'upload'])->name('files.upload');
    Route::get('/files/{messageId}/download', [App\Http\Controllers\ChatFileController::class, 'download'])->name('files.download');
    Route::get('/files/{messageId}/view', [App\Http\Controllers\ChatFileController::class, 'viewImage'])->name('files.view');
    Route::delete('/files/{messageId}', [App\Http\Controllers\ChatFileController::class, 'delete'])->name('files.delete');
    Route::get('/files/{messageId}/info', [App\Http\Controllers\ChatFileController::class, 'getFileInfo'])->name('files.info');
    Route::get('/files/{chatRoomId}/list', [App\Http\Controllers\ChatFileController::class, 'getFiles'])->name('files.list');
});

// User Status Routes
Route::middleware(['auth'])->prefix('user-status')->name('user-status.')->group(function () {
    Route::post('/update', [App\Http\Controllers\UserStatusController::class, 'updateStatus'])->name('update');
    Route::get('/{userId}', [App\Http\Controllers\UserStatusController::class, 'getStatus'])->name('get');
    Route::post('/activity', [App\Http\Controllers\UserStatusController::class, 'updateActivity'])->name('activity');
    Route::get('/online/users', [App\Http\Controllers\UserStatusController::class, 'getOnlineUsers'])->name('online');
});

// Password Management System Routes
Route::middleware(['auth'])->group(function () {
    // Password Categories
    Route::resource('password-categories', App\Http\Controllers\PasswordCategoryController::class);
    Route::get('password-categories-api', [App\Http\Controllers\PasswordCategoryController::class, 'getCategories'])
         ->name('password-categories.api');
    
    // Password Accounts
    Route::resource('password-accounts', App\Http\Controllers\PasswordAccountController::class);
    Route::get('password-accounts/{passwordAccount}/password', [App\Http\Controllers\PasswordAccountController::class, 'showPassword'])
         ->name('password-accounts.show-password');
    
    // Password Assignments
    Route::resource('password-assignments', App\Http\Controllers\PasswordAssignmentController::class);
    Route::post('password-assignments/{account}/assign', [App\Http\Controllers\PasswordAssignmentController::class, 'assign'])
         ->name('password-assignments.assign');
    Route::delete('password-assignments/{assignment}/revoke', [App\Http\Controllers\PasswordAssignmentController::class, 'revoke'])
         ->name('password-assignments.revoke');
    
    // Batch Actions Routes
    Route::delete('password-accounts/batch-delete', [App\Http\Controllers\PasswordAccountController::class, 'batchDelete'])
         ->name('password-accounts.batch-delete');
    Route::post('password-accounts/batch-export', [App\Http\Controllers\PasswordAccountController::class, 'batchExport'])
         ->name('password-accounts.batch-export');
    Route::get('password-accounts/batch-assign', [App\Http\Controllers\PasswordAccountController::class, 'batchAssign'])
         ->name('password-accounts.batch-assign');
    Route::post('password-accounts/batch-assign', [App\Http\Controllers\PasswordAccountController::class, 'batchAssignStore'])
         ->name('password-accounts.batch-assign-store');
    
    // Password Audit Logs
    Route::get('password-audit', [App\Http\Controllers\PasswordAuditController::class, 'index'])
         ->name('password-audit.index');
    Route::get('password-audit/{account}', [App\Http\Controllers\PasswordAuditController::class, 'accountLogs'])
         ->name('password-audit.account');
    
    // AI Notes Generation
    Route::post('password-accounts/generate-notes', [App\Http\Controllers\PasswordAccountController::class, 'generateNotes'])
         ->name('password-accounts.generate-notes');
});

// Storage route for serving files (XAMPP compatibility)
Route::get('storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    $fileName = basename($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . $fileName . '"'
    ]);
})->where('path', '.*');

    // Zoho Integration Routes
// Language switching route
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::middleware(['auth'])->group(function () {
    // Advanced Search Page - keep in main zoho group
    Route::prefix('zoho')->group(function () {
        Route::get('/advanced-search', [ZohoAdvancedSearchController::class, 'index'])->name('zoho.advanced-search');
    });
    
    // Advanced Search API Routes - separate API routes
    Route::prefix('api/zoho')->group(function () {
        Route::post('/advanced-search/text', [ZohoAdvancedSearchController::class, 'searchByText'])->name('zoho.advanced-search.text');
        Route::post('/advanced-search/custom-field', [ZohoAdvancedSearchController::class, 'searchByCustomField'])->name('zoho.advanced-search.custom-field');
        Route::post('/advanced-search/time-range', [ZohoAdvancedSearchController::class, 'searchByTimeRange'])->name('zoho.advanced-search.time-range');
    });
});

Route::middleware(['auth'])->prefix('zoho')->group(function () {
    // User Dashboard - accessible by all authenticated users
    Route::get('/my-stats', [ZohoStatsController::class, 'dashboard'])->name('zoho.dashboard');
    
    // All Tickets Page - accessible by all authenticated users
    Route::get('/tickets', [ZohoStatsController::class, 'allTickets'])->name('zoho.tickets');
    
    // In Progress Tickets Page - accessible by all authenticated users
    Route::get('/tickets/in-progress', [ZohoStatsController::class, 'inProgressTickets'])->name('zoho.tickets.in-progress');
    
    // Reports and Leaderboard - for managers/admins
    Route::middleware(['permission:view-zoho-reports'])->group(function () {
        Route::get('/reports', [ZohoStatsController::class, 'reports'])->name('zoho.reports');
        Route::get('/leaderboard', [ZohoStatsController::class, 'leaderboard'])->name('zoho.leaderboard');
    });
    
    // Admin routes - for system administrators
    Route::middleware(['permission:manage-zoho'])->prefix('admin')->group(function () {
        Route::get('/', [ZohoAdminController::class, 'index'])->name('zoho.admin.index');
        Route::post('/auto-map', [ZohoAdminController::class, 'autoMapUsers'])->name('zoho.admin.autoMap');
        Route::post('/map-user', [ZohoAdminController::class, 'mapUser'])->name('zoho.admin.mapUser');
        Route::post('/toggle-user/{user}', [ZohoAdminController::class, 'toggleUser'])->name('zoho.admin.toggleUser');
        Route::post('/test-connection', [ZohoAdminController::class, 'testConnection'])->name('zoho.admin.testConnection');
        Route::post('/sync-now', [ZohoAdminController::class, 'syncNow'])->name('zoho.admin.syncNow');
        Route::get('/agents', [ZohoAdminController::class, 'getAgents'])->name('zoho.admin.getAgents');
    });

    // Department Mappings Management - for system administrators
    Route::middleware(['permission:manage-zoho'])->prefix('department-mappings')->group(function () {
        Route::get('/', [ZohoDepartmentMappingController::class, 'index'])->name('zoho.department-mappings.index');
        Route::get('/create', [ZohoDepartmentMappingController::class, 'create'])->name('zoho.department-mappings.create');
        Route::post('/', [ZohoDepartmentMappingController::class, 'store'])->name('zoho.department-mappings.store');
        Route::get('/{departmentMapping}', [ZohoDepartmentMappingController::class, 'show'])->name('zoho.department-mappings.show');
        Route::get('/{departmentMapping}/edit', [ZohoDepartmentMappingController::class, 'edit'])->name('zoho.department-mappings.edit');
        Route::put('/{departmentMapping}', [ZohoDepartmentMappingController::class, 'update'])->name('zoho.department-mappings.update');
        Route::delete('/{departmentMapping}', [ZohoDepartmentMappingController::class, 'destroy'])->name('zoho.department-mappings.destroy');
        Route::post('/bulk-update', [ZohoDepartmentMappingController::class, 'bulkUpdate'])->name('zoho.department-mappings.bulk-update');
        Route::post('/{departmentMapping}/toggle-active', [ZohoDepartmentMappingController::class, 'toggleActive'])->name('zoho.department-mappings.toggle-active');
    });

    // Task Templates Management
    Route::prefix('task-templates')->group(function () {
        Route::get('/', [TaskTemplateController::class, 'index'])->name('task-templates.index');
        Route::get('/create', [TaskTemplateController::class, 'create'])->name('task-templates.create');
        Route::post('/', [TaskTemplateController::class, 'store'])->name('task-templates.store');
        Route::get('/{taskTemplate}', [TaskTemplateController::class, 'show'])->name('task-templates.show');
        Route::get('/{taskTemplate}/edit', [TaskTemplateController::class, 'edit'])->name('task-templates.edit');
        Route::put('/{taskTemplate}', [TaskTemplateController::class, 'update'])->name('task-templates.update');
        Route::delete('/{taskTemplate}', [TaskTemplateController::class, 'destroy'])->name('task-templates.destroy');
        Route::post('/import', [TaskTemplateController::class, 'import'])->name('task-templates.import');
        Route::post('/{taskTemplate}/toggle-status', [TaskTemplateController::class, 'toggleStatus'])->name('task-templates.toggle-status');
        Route::get('/api/templates-for-department', [TaskTemplateController::class, 'getTemplatesForDepartment'])->name('task-templates.api.templates-for-department');
    });

    // Zoho Task Templates
    Route::get('/task-templates-management', [TaskTemplateController::class, 'zohoIndex'])->name('zoho.task-templates');

    // Task Progress Routes
    Route::get('/task-progress', [TaskProgressController::class, 'index'])->name('task-progress.index');
    
    // EET Life Routes
    Route::get('/eet-life', [EetLifeController::class, 'index'])->name('eet-life.index');
    Route::post('/eet-life/shoutouts', [EetLifeController::class, 'storeShoutout'])->name('eet-life.shoutouts.store');
    Route::get('/eet-life/events', [EetLifeController::class, 'getEvents'])->name('eet-life.events');
    Route::get('/eet-life/shoutouts', [EetLifeController::class, 'getShoutouts'])->name('eet-life.shoutouts');
    
    // Events Management Routes
    Route::middleware(['permission:events.manage'])->group(function () {
        Route::apiResource('events', EventManagementController::class);
        Route::get('events-dropdown', [EventManagementController::class, 'getEventsForDropdown'])->name('events.dropdown');
        Route::post('events/{event}/toggle-featured', [EventManagementController::class, 'toggleFeatured'])->name('events.toggle-featured');
    });
    
    // Announcements Management Routes
    Route::middleware(['permission:announcements.manage'])->group(function () {
        Route::apiResource('announcements', AnnouncementManagementController::class);
        Route::get('announcements-users', [AnnouncementManagementController::class, 'getUsersForTarget'])->name('announcements.users');
    });
    
    // Public announcement routes (visible to all users)
    Route::get('announcements-visible', [AnnouncementManagementController::class, 'getVisibleAnnouncements'])->name('announcements.visible');
    
    // Operations Management (Audit) Routes
    Route::middleware(['permission:view-audit-logs'])->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');
        Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export');
        Route::get('/api/audit', [AuditController::class, 'api'])->name('audit.api');
    });

    // Snipe-IT Integration Routes (temporarily protect by auth until permission schema unified)
    Route::middleware(['auth'])->group(function () {
        Route::get('/snipe-it', [SnipeItController::class, 'index'])->name('snipe-it.index');
        Route::get('/snipe-it/settings', [SnipeItController::class, 'settings'])->name('snipe-it.settings');
        Route::get('/zoho/snipe-it', [SnipeItController::class, 'testPage'])->name('snipe-it.test-page');
        
        // API Routes
        Route::prefix('api/snipe-it')->group(function () {
            Route::get('/get-user', [SnipeItController::class, 'getUser'])->name('snipe-it.get-user');
            Route::post('/test-connection', [SnipeItController::class, 'testConnection'])->name('snipe-it.test-connection');
            Route::post('/sync/assets', [SnipeItController::class, 'syncAssets'])->name('snipe-it.sync-assets');
            Route::post('/sync/users', [SnipeItController::class, 'syncUsers'])->name('snipe-it.sync-users');
            Route::post('/sync/categories', [SnipeItController::class, 'syncCategories'])->name('snipe-it.sync-categories');
            Route::get('/assets/{assetId}', [SnipeItController::class, 'getAssetDetails'])->name('snipe-it.get-asset');
            Route::put('/assets/{assetId}', [SnipeItController::class, 'updateAsset'])->name('snipe-it.update-asset');
            Route::post('/assets', [SnipeItController::class, 'createAsset'])->name('snipe-it.create-asset');
            Route::delete('/assets/{assetId}', [SnipeItController::class, 'deleteAsset'])->name('snipe-it.delete-asset');
            Route::get('/stats', [SnipeItController::class, 'getStats'])->name('snipe-it.get-stats');
            Route::get('/sync-logs', [SnipeItController::class, 'getSyncLogs'])->name('snipe-it.get-sync-logs');
            Route::post('/settings', [SnipeItController::class, 'saveSettings'])->name('snipe-it.save-settings');
            Route::post('/settings/reset', [SnipeItController::class, 'resetSettings'])->name('snipe-it.reset-settings');
        });
    });
});

// Zoho Bulk Sync Routes
Route::get('zoho/bulk-sync', [\App\Http\Controllers\ZohoBulkSyncController::class, 'index'])->name('zoho.bulk-sync.index');
Route::post('zoho/bulk-sync/execute', [\App\Http\Controllers\ZohoBulkSyncController::class, 'execute'])->name('zoho.bulk-sync.execute');

// Reports System Routes
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    // صفحة التقارير الرئيسية
    Route::get('/', [App\Http\Controllers\ReportsController::class, 'index'])->name('index');
    
    // Export & Import Routes
    Route::post('/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('export');
    Route::post('/import', [App\Http\Controllers\ReportsController::class, 'import'])->name('import');
    
    // Module Statistics
    Route::get('/module-stats', [App\Http\Controllers\ReportsController::class, 'getModuleStats'])->name('module-stats');
    
    // Full Backup & Restore
    Route::post('/full-backup', [App\Http\Controllers\ReportsController::class, 'fullBackup'])->name('full-backup');
    Route::post('/full-restore', [App\Http\Controllers\ReportsController::class, 'fullRestore'])->name('full-restore');
});
