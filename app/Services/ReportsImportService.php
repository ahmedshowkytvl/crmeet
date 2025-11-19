<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ReportsImportService
{
    /**
     * استيراد البيانات من ملف
     */
    public function import($file, $tableName)
    {
        $extension = $file->getClientOriginalExtension();
        
        switch (strtolower($extension)) {
            case 'xlsx':
            case 'xls':
                return $this->importFromExcel($file, $tableName);
                
            case 'csv':
                return $this->importFromCsv($file, $tableName);
                
            case 'json':
                return $this->importFromJson($file, $tableName);
                
            case 'sql':
                return $this->importFromSql($file, $tableName);
                
            default:
                throw new \Exception('نوع الملف غير مدعوم');
        }
    }
    
    /**
     * استيراد من ملف Excel
     */
    private function importFromExcel($file, $tableName)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        
        if (empty($data) || count($data) < 2) {
            throw new \Exception('الملف فارغ أو لا يحتوي على بيانات صحيحة');
        }
        
        // إزالة العناوين
        $headers = array_shift($data);
        $translatedHeaders = array_map([$this, 'translateColumnFromArabic'], $headers);
        
        $importedRows = 0;
        
        foreach ($data as $row) {
            // تجاهل الصفوف الفارغة
            if (empty(array_filter($row))) {
                continue;
            }
            
            $recordData = [];
            foreach ($translatedHeaders as $index => $header) {
                if ($header && isset($row[$index])) {
                    $recordData[$header] = $row[$index];
                }
            }
            
            if ($this->insertRecord($tableName, $recordData)) {
                $importedRows++;
            }
        }
        
        return ['imported_rows' => $importedRows];
    }
    
    /**
     * استيراد من ملف CSV
     */
    private function importFromCsv($file, $tableName)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        
        return $this->importFromArray($data, $tableName);
    }
    
    /**
     * استيراد من ملف JSON
     */
    private function importFromJson($file, $tableName)
    {
        $content = file_get_contents($file->getPathname());
        $jsonData = json_decode($content, true);
        
        if (!$jsonData || !isset($jsonData['data'])) {
            throw new \Exception('تنسيق ملف JSON غير صحيح');
        }
        
        $importedRows = 0;
        
        foreach ($jsonData['data'] as $row) {
            if ($this->insertRecord($tableName, (array) $row)) {
                $importedRows++;
            }
        }
        
        return ['imported_rows' => $importedRows];
    }
    
    /**
     * استيراد من ملف SQL
     */
    private function importFromSql($file, $tableName)
    {
        $sql = file_get_contents($file->getPathname());
        
        DB::beginTransaction();
        try {
            DB::unprepared($sql);
            DB::commit();
            
            return ['imported_rows' => 0]; // لا يمكن حساب العدد الدقيق للـ SQL
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * استيراد من مصفوفة
     */
    private function importFromArray($data, $tableName)
    {
        if (empty($data) || count($data) < 2) {
            throw new \Exception('البيانات فارغة أو غير صحيحة');
        }
        
        // إزالة العناوين
        $headers = array_shift($data);
        $translatedHeaders = array_map([$this, 'translateColumnFromArabic'], $headers);
        
        $importedRows = 0;
        
        foreach ($data as $row) {
            if (empty(array_filter($row))) {
                continue;
            }
            
            $recordData = [];
            foreach ($translatedHeaders as $index => $header) {
                if ($header && isset($row[$index])) {
                    $recordData[$header] = $row[$index];
                }
            }
            
            if ($this->insertRecord($tableName, $recordData)) {
                $importedRows++;
            }
        }
        
        return ['imported_rows' => $importedRows];
    }
    
    /**
     * إدخال سجل واحد
     */
    private function insertRecord($tableName, $data)
    {
        try {
            // إزالة العناصر الفارغة
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
            
            if (empty($data)) {
                return false;
            }
            
            // ترجمة أسماء الأعمدة
            $data = $this->translateColumns($data);
            
            // معالجة البيانات الخاصة
            $data = $this->processSpecialFields($data);
            
            // التحقق من وجود الأعمدة المطلوبة في الجدول
            $tableColumns = Schema::getColumnListing($tableName);
            $data = array_intersect_key($data, array_flip($tableColumns));
            
            // إضافة timestamps إذا لم تكن موجودة
            if (in_array('created_at', $tableColumns) && !isset($data['created_at'])) {
                $data['created_at'] = Carbon::now();
            }
            if (in_array('updated_at', $tableColumns) && !isset($data['updated_at'])) {
                $data['updated_at'] = Carbon::now();
            }
            
            // التحقق من عدم وجود السجل (تجنب التكرار)
            $exists = false;
            if (isset($data['id'])) {
                $exists = DB::table($tableName)->where('id', $data['id'])->exists();
            } elseif (isset($data['email'])) {
                $exists = DB::table($tableName)->where('email', $data['email'])->exists();
            } elseif (isset($data['name'])) {
                $exists = DB::table($tableName)->where('name', $data['name'])->exists();
            }
            
            if (!$exists) {
                // إزالة id إذا كان فارغاً للسماح بالـ auto increment
                if (isset($data['id']) && empty($data['id'])) {
                    unset($data['id']);
                }
                
                DB::table($tableName)->insert($data);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            \Log::error("خطأ في استيراد البيانات للجدول {$tableName}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ترجمة أسماء الأعمدة من العربية للإنجليزية
     */
    private function translateColumns($data)
    {
        $translations = [
            'المعرف' => 'id',
            'الاسم' => 'name',
            'الاسم بالعربية' => 'name_ar',
            'الاسم بالإنجليزية' => 'name_en',
            'البريد الإلكتروني' => 'email',
            'اسم المستخدم' => 'username',
            'كلمة المرور' => 'password',
            'نوع المستخدم' => 'user_type',
            'معرف الدور' => 'role_id',
            'صورة الملف الشخصي' => 'profile_picture',
            'معرف القسم' => 'department_id',
            'معرف الفرع' => 'branch_id',
            'معرف المدير' => 'manager_id',
            'منشئ بواسطة' => 'created_by',
            'تاريخ الإنشاء' => 'created_at',
            'تاريخ التحديث' => 'updated_at',
            'تاريخ الحذف' => 'deleted_at',
            'نشط' => 'is_active',
            'مفعل' => 'is_enabled',
            'مؤرشف' => 'is_archived',
            'الحالة' => 'status',
            'الوصف' => 'description',
            'الملاحظات' => 'notes',
            'الهاتف' => 'phone',
            'العنوان' => 'address',
            'المنصب' => 'position',
            'تاريخ التوظيف' => 'hire_date',
            'العنوان' => 'title',
            'المحتوى' => 'content',
            'معرف الفئة' => 'category_id',
            'الأولوية' => 'priority',
            'تاريخ الاستحقاق' => 'due_date',
            'تاريخ الإنجاز' => 'completed_at',
            'مُكلف إلى' => 'assigned_to',
            'معرف الأصل' => 'asset_id',
            'معرف الموقع' => 'location_id',
            'الرقم التسلسلي' => 'serial_number',
            'الموديل' => 'model',
            'العلامة التجارية' => 'brand',
            'تاريخ الشراء' => 'purchase_date',
            'انتهاء الضمان' => 'warranty_expiry',
            'القيمة' => 'value',
            'الكمية' => 'quantity',
            'السعر' => 'price',
            'المجموع' => 'total',
            'معرف المورد' => 'supplier_id',
            'شخص الاتصال' => 'contact_person',
            'الموقع الإلكتروني' => 'website',
            'الرقم الضريبي' => 'tax_number',
            'رقم الحساب البنكي' => 'bank_account',
            'شروط الدفع' => 'payment_terms',
        ];

        $translatedData = [];
        foreach ($data as $key => $value) {
            $translatedKey = $translations[$key] ?? $key;
            $translatedData[$translatedKey] = $value;
        }

        return $translatedData;
    }
    
    /**
     * ترجمة اسم العمود من العربية للإنجليزية
     */
    private function translateColumnFromArabic($column)
    {
        $translations = [
            'المعرف' => 'id',
            'الاسم' => 'name',
            'الاسم بالعربية' => 'name_ar',
            'الاسم بالإنجليزية' => 'name_en',
            'البريد الإلكتروني' => 'email',
            'اسم المستخدم' => 'username',
            'كلمة المرور' => 'password',
            'نوع المستخدم' => 'user_type',
            'معرف الدور' => 'role_id',
            'صورة الملف الشخصي' => 'profile_picture',
            'معرف القسم' => 'department_id',
            'معرف الفرع' => 'branch_id',
            'معرف المدير' => 'manager_id',
            'منشئ بواسطة' => 'created_by',
            'تاريخ الإنشاء' => 'created_at',
            'تاريخ التحديث' => 'updated_at',
            'تاريخ الحذف' => 'deleted_at',
            'نشط' => 'is_active',
            'مفعل' => 'is_enabled',
            'مؤرشف' => 'is_archived',
            'الحالة' => 'status',
            'الوصف' => 'description',
            'الملاحظات' => 'notes',
            'الهاتف' => 'phone',
            'العنوان' => 'address',
            'المنصب' => 'position',
            'تاريخ التوظيف' => 'hire_date',
            'العنوان' => 'title',
            'المحتوى' => 'content',
            'معرف الفئة' => 'category_id',
            'الأولوية' => 'priority',
            'تاريخ الاستحقاق' => 'due_date',
            'تاريخ الإنجاز' => 'completed_at',
            'مُكلف إلى' => 'assigned_to',
            'معرف الأصل' => 'asset_id',
            'معرف الموقع' => 'location_id',
            'الرقم التسلسلي' => 'serial_number',
            'الموديل' => 'model',
            'العلامة التجارية' => 'brand',
            'تاريخ الشراء' => 'purchase_date',
            'انتهاء الضمان' => 'warranty_expiry',
            'القيمة' => 'value',
            'الكمية' => 'quantity',
            'السعر' => 'price',
            'المجموع' => 'total',
            'معرف المورد' => 'supplier_id',
            'شخص الاتصال' => 'contact_person',
            'الموقع الإلكتروني' => 'website',
            'الرقم الضريبي' => 'tax_number',
            'رقم الحساب البنكي' => 'bank_account',
            'شروط الدفع' => 'payment_terms',
        ];

        return $translations[trim($column)] ?? trim($column);
    }
    
    /**
     * معالجة الحقول الخاصة
     */
    private function processSpecialFields($data)
    {
        // معالجة كلمات المرور
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // معالجة القيم المنطقية
        $booleanFields = ['is_active', 'is_enabled', 'is_archived', 'status'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if ($value === 'نعم' || $value === 'Yes' || $value === '1' || $value === true) {
                    $data[$field] = 1;
                } elseif ($value === 'لا' || $value === 'No' || $value === '0' || $value === false) {
                    $data[$field] = 0;
                }
            }
        }

        // معالجة التواريخ
        $dateFields = ['created_at', 'updated_at', 'deleted_at', 'hire_date', 'due_date', 'completed_at', 'purchase_date', 'warranty_expiry'];
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = Carbon::parse($data[$field])->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // إزالة التاريخ إذا لم يكن صحيحاً
                    unset($data[$field]);
                }
            }
        }

        // معالجة الأرقام
        $numericFields = ['price', 'value', 'quantity', 'total'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = floatval(str_replace(',', '', $data[$field]));
            }
        }

        return $data;
    }
}