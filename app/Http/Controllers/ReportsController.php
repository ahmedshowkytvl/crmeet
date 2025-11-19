<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\ReportsExportService;
use App\Services\ReportsImportService;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * عرض صفحة التقارير الرئيسية
     */
    public function index()
    {
        $modules = $this->getAllModules();
        return view('reports.index', compact('modules'));
    }

    /**
     * الحصول على جميع الوحدات المتاحة في النظام
     */
    private function getAllModules()
    {
        return [
            // إدارة المستخدمين والأدوار
            [
                'name' => __('messages.module_users'),
                'name_en' => 'Users',
                'model' => 'User',
                'table' => 'users',
                'icon' => 'fas fa-users',
                'color' => '#007bff',
                'description' => __('messages.module_desc_users')
            ],
            [
                'name' => __('messages.module_roles'),
                'name_en' => 'Roles',
                'model' => 'Role',
                'table' => 'roles',
                'icon' => 'fas fa-user-shield',
                'color' => '#6c757d',
                'description' => __('messages.module_desc_roles')
            ],
            [
                'name' => __('messages.module_permissions'),
                'name_en' => 'Permissions',
                'model' => 'Permission',
                'table' => 'permissions',
                'icon' => 'fas fa-key',
                'color' => '#28a745',
                'description' => __('messages.module_desc_permissions')
            ],
            [
                'name' => __('messages.module_departments'),
                'name_en' => 'Departments',
                'model' => 'Department',
                'table' => 'departments',
                'icon' => 'fas fa-building',
                'color' => '#17a2b8',
                'description' => __('messages.module_desc_departments')
            ],
            [
                'name' => __('messages.module_branches'),
                'name_en' => 'Branches',
                'model' => 'Branch',
                'table' => 'branches',
                'icon' => 'fas fa-map-marked-alt',
                'color' => '#ffc107',
                'description' => __('messages.module_desc_branches')
            ],

            // إدارة جهات الاتصال
            [
                'name' => __('messages.module_contacts'),
                'name_en' => 'Contacts',
                'model' => 'Contact',
                'table' => 'contacts',
                'icon' => 'fas fa-address-book',
                'color' => '#6f42c1',
                'description' => __('messages.module_desc_contacts')
            ],
            [
                'name' => __('messages.module_contact_categories'),
                'name_en' => 'Contact Categories',
                'model' => 'ContactCategory',
                'table' => 'contact_categories',
                'icon' => 'fas fa-tags',
                'color' => '#e83e8c',
                'description' => __('messages.module_desc_contact_categories')
            ],
            [
                'name' => __('messages.module_contact_interactions'),
                'name_en' => 'Contact Interactions',
                'model' => 'ContactInteraction',
                'table' => 'contact_interactions',
                'icon' => 'fas fa-handshake',
                'color' => '#fd7e14',
                'description' => __('messages.module_desc_contact_interactions')
            ],

            // إدارة المهام
            [
                'name' => __('messages.module_tasks'),
                'name_en' => 'Tasks',
                'model' => 'Task',
                'table' => 'tasks',
                'icon' => 'fas fa-tasks',
                'color' => '#dc3545',
                'description' => __('messages.module_desc_tasks')
            ],
            [
                'name' => __('messages.module_task_templates'),
                'name_en' => 'Task Templates',
                'model' => 'TaskTemplate',
                'table' => 'task_templates',
                'icon' => 'fas fa-clipboard-list',
                'color' => '#20c997',
                'description' => __('messages.module_desc_task_templates')
            ],
            [
                'name' => __('messages.module_schedule_events'),
                'name_en' => 'Schedule Events',
                'model' => 'ScheduleEvent',
                'table' => 'schedule_events',
                'icon' => 'fas fa-calendar-alt',
                'color' => '#6610f2',
                'description' => __('messages.module_desc_schedule_events')
            ],
            [
                'name' => __('messages.module_events'),
                'name_en' => 'Events',
                'model' => 'Event',
                'table' => 'events',
                'icon' => 'fas fa-calendar-check',
                'color' => '#e21b7a',
                'description' => __('messages.module_desc_events')
            ],

            // إدارة الأصول
            [
                'name' => __('messages.module_assets'),
                'name_en' => 'Assets',
                'model' => 'Asset',
                'table' => 'assets',
                'icon' => 'fas fa-laptop',
                'color' => '#007bff',
                'description' => __('messages.module_desc_assets')
            ],
            [
                'name' => __('messages.module_asset_categories'),
                'name_en' => 'Asset Categories',
                'model' => 'AssetCategory',
                'table' => 'asset_categories',
                'icon' => 'fas fa-layer-group',
                'color' => '#28a745',
                'description' => __('messages.module_desc_asset_categories')
            ],
            [
                'name' => __('messages.module_asset_locations'),
                'name_en' => 'Asset Locations',
                'model' => 'AssetLocation',
                'table' => 'asset_locations',
                'icon' => 'fas fa-map-marker-alt',
                'color' => '#17a2b8',
                'description' => __('messages.module_desc_asset_locations')
            ],
            [
                'name' => __('messages.module_asset_assignments'),
                'name_en' => 'Asset Assignments',
                'model' => 'AssetAssignment',
                'table' => 'asset_assignments',
                'icon' => 'fas fa-user-check',
                'color' => '#ffc107',
                'description' => __('messages.module_desc_asset_assignments')
            ],
            [
                'name' => __('messages.module_asset_logs'),
                'name_en' => 'Asset Logs',
                'model' => 'AssetLog',
                'table' => 'asset_logs',
                'icon' => 'fas fa-history',
                'color' => '#6c757d',
                'description' => __('messages.module_desc_asset_logs')
            ],

            // إدارة المستودعات
            [
                'name' => __('messages.module_warehouses'),
                'name_en' => 'Warehouses',
                'model' => 'Warehouse',
                'table' => 'warehouses',
                'icon' => 'fas fa-warehouse',
                'color' => '#6f42c1',
                'description' => __('messages.module_desc_warehouses')
            ],
            [
                'name' => __('messages.module_warehouse_cabinets'),
                'name_en' => 'Warehouse Cabinets',
                'model' => 'WarehouseCabinet',
                'table' => 'warehouse_cabinets',
                'icon' => 'fas fa-archive',
                'color' => '#e83e8c',
                'description' => __('messages.module_desc_warehouse_cabinets')
            ],
            [
                'name' => __('messages.module_warehouse_shelves'),
                'name_en' => 'Warehouse Shelves',
                'model' => 'WarehouseShelf',
                'table' => 'warehouse_shelves',
                'icon' => 'fas fa-layer-group',
                'color' => '#fd7e14',
                'description' => __('messages.module_desc_warehouse_shelves')
            ],
            [
                'name' => __('messages.module_inventory'),
                'name_en' => 'Inventory',
                'model' => 'Inventory',
                'table' => 'inventory',
                'icon' => 'fas fa-boxes',
                'color' => '#20c997',
                'description' => __('messages.module_desc_inventory')
            ],
            [
                'name' => __('messages.module_stock_movements'),
                'name_en' => 'Stock Movements',
                'model' => 'StockMovement',
                'table' => 'stock_movements',
                'icon' => 'fas fa-exchange-alt',
                'color' => '#6610f2',
                'description' => __('messages.module_desc_stock_movements')
            ],
            [
                'name' => __('messages.module_suppliers'),
                'name_en' => 'Suppliers',
                'model' => 'Supplier',
                'table' => 'suppliers',
                'icon' => 'fas fa-truck',
                'color' => '#dc3545',
                'description' => __('messages.module_desc_suppliers')
            ],

            // إدارة كلمات المرور
            [
                'name' => __('messages.module_password_accounts'),
                'name_en' => 'Password Accounts',
                'model' => 'PasswordAccount',
                'table' => 'password_accounts',
                'icon' => 'fas fa-lock',
                'color' => '#007bff',
                'description' => __('messages.module_desc_password_accounts')
            ],
            [
                'name' => __('messages.module_password_categories'),
                'name_en' => 'Password Categories',
                'model' => 'PasswordCategory',
                'table' => 'password_categories',
                'icon' => 'fas fa-folder-open',
                'color' => '#28a745',
                'description' => __('messages.module_desc_password_categories')
            ],
            [
                'name' => __('messages.module_password_assignments'),
                'name_en' => 'Password Assignments',
                'model' => 'PasswordAssignment',
                'table' => 'password_assignments',
                'icon' => 'fas fa-user-lock',
                'color' => '#17a2b8',
                'description' => __('messages.module_desc_password_assignments')
            ],

            // إدارة التواصل
            [
                'name' => __('messages.module_chat_rooms'),
                'name_en' => 'Chat Rooms',
                'model' => 'ChatRoom',
                'table' => 'chat_rooms',
                'icon' => 'fas fa-comments',
                'color' => '#ffc107',
                'description' => __('messages.module_desc_chat_rooms')
            ],
            [
                'name' => __('messages.module_chat_messages'),
                'name_en' => 'Chat Messages',
                'model' => 'ChatMessage',
                'table' => 'chat_messages',
                'icon' => 'fas fa-comment-alt',
                'color' => '#6c757d',
                'description' => __('messages.module_desc_chat_messages')
            ],
            [
                'name' => __('messages.module_notifications'),
                'name_en' => 'Notifications',
                'model' => 'Notification',
                'table' => 'notifications',
                'icon' => 'fas fa-bell',
                'color' => '#6f42c1',
                'description' => __('messages.module_desc_notifications')
            ],
            [
                'name' => __('messages.module_announcements'),
                'name_en' => 'Announcements',
                'model' => 'Announcement',
                'table' => 'announcements',
                'icon' => 'fas fa-bullhorn',
                'color' => '#e83e8c',
                'description' => __('messages.module_desc_announcements')
            ],

            // إدارة الموظفين
            [
                'name' => __('messages.module_employee_emails'),
                'name_en' => 'Employee Emails',
                'model' => 'EmployeeEmail',
                'table' => 'employee_emails',
                'icon' => 'fas fa-envelope',
                'color' => '#fd7e14',
                'description' => __('messages.module_desc_employee_emails')
            ],
            [
                'name' => __('messages.module_employee_requests'),
                'name_en' => 'Employee Requests',
                'model' => 'EmployeeRequest',
                'table' => 'employee_requests',
                'icon' => 'fas fa-file-alt',
                'color' => '#20c997',
                'description' => __('messages.module_desc_employee_requests')
            ],
            [
                'name' => __('messages.module_hiring_documents'),
                'name_en' => 'Hiring Documents',
                'model' => 'HiringDocument',
                'table' => 'hiring_documents',
                'icon' => 'fas fa-file-contract',
                'color' => '#6610f2',
                'description' => __('messages.module_desc_hiring_documents')
            ],
            [
                'name' => __('messages.module_user_achievements'),
                'name_en' => 'User Achievements',
                'model' => 'UserAchievement',
                'table' => 'user_achievements',
                'icon' => 'fas fa-trophy',
                'color' => '#e21b7a',
                'description' => __('messages.module_desc_user_achievements')
            ],

            // التكاملات الخارجية
            [
                'name' => __('messages.module_user_zoho_stats'),
                'name_en' => 'Zoho Stats',
                'model' => 'UserZohoStat',
                'table' => 'user_zoho_stats',
                'icon' => 'fas fa-chart-bar',
                'color' => '#dc3545',
                'description' => __('messages.module_desc_user_zoho_stats')
            ],
            [
                'name' => __('messages.module_zoho_ticket_cache'),
                'name_en' => 'Zoho Ticket Cache',
                'model' => 'ZohoTicketCache',
                'table' => 'zoho_ticket_cache',
                'icon' => 'fas fa-ticket-alt',
                'color' => '#007bff',
                'description' => __('messages.module_desc_zoho_ticket_cache')
            ],
            [
                'name' => __('messages.module_zoho_department_mappings'),
                'name_en' => 'Zoho Department Mapping',
                'model' => 'ZohoDepartmentMapping',
                'table' => 'zoho_department_mappings',
                'icon' => 'fas fa-sitemap',
                'color' => '#28a745',
                'description' => __('messages.module_desc_zoho_department_mappings')
            ],
            [
                'name' => __('messages.module_snipe_it_sync_logs'),
                'name_en' => 'SnipeIt Sync Logs',
                'model' => 'SnipeItSyncLog',
                'table' => 'snipe_it_sync_logs',
                'icon' => 'fas fa-sync',
                'color' => '#17a2b8',
                'description' => __('messages.module_desc_snipe_it_sync_logs')
            ],

            // متنوعة
            [
                'name' => __('messages.module_comments'),
                'name_en' => 'Comments',
                'model' => 'Comment',
                'table' => 'comments',
                'icon' => 'fas fa-comment',
                'color' => '#ffc107',
                'description' => __('messages.module_desc_comments')
            ],
            [
                'name' => __('messages.module_audit_logs'),
                'name_en' => 'Audit Logs',
                'model' => 'AuditLog',
                'table' => 'audit_logs',
                'icon' => 'fas fa-search',
                'color' => '#6c757d',
                'description' => __('messages.module_desc_audit_logs')
            ],
            [
                'name' => __('messages.module_shoutouts'),
                'name_en' => 'Shoutouts',
                'model' => 'Shoutout',
                'table' => 'shoutouts',
                'icon' => 'fas fa-star',
                'color' => '#6f42c1',
                'description' => __('messages.module_desc_shoutouts')
            ]
        ];
    }

    /**
     * تصدير بيانات وحدة معينة
     */
    public function export(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'format' => 'required|in:excel,csv,json,sql'
        ]);

        $table = $request->module;
        
        // التحقق من وجود الجدول
        if (!Schema::hasTable($table)) {
            return response()->json([
                'success' => false,
                'message' => "الجدول {$table} غير موجود"
            ], 404);
        }

        try {
            $data = DB::table($table)->get();
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            
            $exportService = new ReportsExportService();
            
            switch ($request->format) {
                case 'excel':
                    return $exportService->exportToExcel($data, $table, $timestamp);
                    
                case 'csv':
                    return $exportService->exportToCsv($data, $table, $timestamp);
                    
                case 'json':
                    return $exportService->exportToJson($data, $table, $timestamp);
                    
                case 'sql':
                    return $exportService->exportToSql($data, $table, $timestamp);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصدير: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * استيراد بيانات إلى وحدة معينة
     */
    public function import(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'file' => 'required|file|max:10240' // 10MB max
        ]);

        $table = $request->module;
        
        // التحقق من وجود الجدول
        if (!Schema::hasTable($table)) {
            return response()->json([
                'success' => false,
                'message' => "الجدول {$table} غير موجود"
            ], 404);
        }

        try {
            $importService = new ReportsImportService();
            $result = $importService->import($request->file('file'), $table);
            
            return response()->json([
                'success' => true,
                'message' => 'تم استيراد البيانات بنجاح',
                'imported_rows' => $result['imported_rows']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على إحصائيات وحدة معينة
     */
    public function getModuleStats(Request $request)
    {
        $table = $request->query('table');
        
        if (empty($table)) {
            return response()->json([
                'success' => false,
                'message' => 'اسم الجدول مطلوب'
            ], 400);
        }
        
        if (!Schema::hasTable($table)) {
            return response()->json([
                'success' => false,
                'message' => "الجدول {$table} غير موجود"
            ], 404);
        }

        try {
            $totalRecords = DB::table($table)->count();
            $columns = Schema::getColumnListing($table);
            
            // محاولة الحصول على آخر تحديث
            $lastUpdated = null;
            if (in_array('updated_at', $columns)) {
                $lastUpdated = DB::table($table)
                    ->whereNotNull('updated_at')
                    ->max('updated_at');
            } elseif (in_array('created_at', $columns)) {
                $lastUpdated = DB::table($table)
                    ->whereNotNull('created_at')
                    ->max('created_at');
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'table' => $table,
                    'total_records' => $totalRecords,
                    'columns' => $columns,
                    'column_count' => count($columns),
                    'last_updated' => $lastUpdated,
                    'table_size' => $this->getTableSize($table)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * توليد SQL Dump
     */
    private function generateSQLDump($table, $data)
    {
        $sql = "-- SQL Dump for table: {$table}\n";
        $sql .= "-- Generated at: " . Carbon::now() . "\n\n";
        
        if ($data->isEmpty()) {
            $sql .= "-- No data found in table {$table}\n";
            return $sql;
        }
        
        // الحصول على أسماء الأعمدة
        $columns = array_keys((array) $data->first());
        $columnsStr = implode(', ', array_map(function($col) {
            return "`{$col}`";
        }, $columns));
        
        $sql .= "INSERT INTO `{$table}` ({$columnsStr}) VALUES\n";
        
        $values = [];
        foreach ($data as $row) {
            $rowValues = [];
            foreach ((array) $row as $value) {
                if (is_null($value)) {
                    $rowValues[] = 'NULL';
                } elseif (is_string($value)) {
                    $rowValues[] = "'" . addslashes($value) . "'";
                } else {
                    $rowValues[] = $value;
                }
            }
            $values[] = '(' . implode(', ', $rowValues) . ')';
        }
        
        $sql .= implode(",\n", $values) . ";\n";
        
        return $sql;
    }

    /**
     * حساب حجم الجدول التقريبي
     */
    private function getTableSize($table)
    {
        try {
            $result = DB::select("
                SELECT 
                    table_name AS 'table',
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$table]);
            
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * استخراج نسخة احتياطية شاملة من النظام
     */
    public function fullBackup()
    {
        try {
            $modules = $this->getAllModules();
            $backup = [
                'backup_info' => [
                    'created_at' => Carbon::now(),
                    'system' => 'CRM System',
                    'version' => '1.0',
                    'total_modules' => count($modules)
                ],
                'data' => []
            ];
            
            foreach ($modules as $module) {
                $table = $module['table'];
                if (Schema::hasTable($table)) {
                    $data = DB::table($table)->get();
                    $backup['data'][$table] = [
                        'module_info' => $module,
                        'records_count' => $data->count(),
                        'data' => $data
                    ];
                }
            }
            
            $filename = 'crm_full_backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.json';
            
            return response()->json($backup)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * استعادة نسخة احتياطية شاملة
     */
    public function fullRestore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:50240', // 50MB max
            'overwrite_existing' => 'boolean'
        ]);
        
        try {
            $file = $request->file('backup_file');
            $content = file_get_contents($file->getPathname());
            $backup = json_decode($content, true);
            
            if (!$backup || !isset($backup['data'])) {
                throw new \Exception('ملف النسخة الاحتياطية غير صحيح');
            }
            
            $restoredTables = 0;
            $totalRecords = 0;
            
            DB::beginTransaction();
            
            foreach ($backup['data'] as $tableName => $tableData) {
                if (Schema::hasTable($tableName)) {
                    // حذف البيانات الموجودة إذا كان مطلوباً
                    if ($request->overwrite_existing) {
                        DB::table($tableName)->truncate();
                    }
                    
                    // استيراد البيانات
                    foreach ($tableData['data'] as $record) {
                        DB::table($tableName)->insert((array) $record);
                        $totalRecords++;
                    }
                    
                    $restoredTables++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'تم استعادة النسخة الاحتياطية بنجاح',
                'restored_tables' => $restoredTables,
                'total_records' => $totalRecords
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استعادة النسخة الاحتياطية: ' . $e->getMessage()
            ], 500);
        }
    }
}
