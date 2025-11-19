<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class ReportsExportService
{
    /**
     * تصدير البيانات إلى ملف Excel بصيغة XLSX
     */
    public function exportToExcel($data, $tableName, $timestamp)
    {
        $spreadsheet = $this->createSpreadsheet($data, $tableName);
        $writer = new Xlsx($spreadsheet);
        
        $filename = "{$tableName}_{$timestamp}.xlsx";
        
        // إنشاء ملف مؤقت بامتداد .xlsx
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $tempFileWithExt = $tempFile . '.xlsx';
        rename($tempFile, $tempFileWithExt);
        
        // حفظ الملف بصيغة XLSX
        $writer->save($tempFileWithExt);
        
        return response()->download($tempFileWithExt, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ])->deleteFileAfterSend();
    }
    
    /**
     * تصدير البيانات إلى ملف CSV
     */
    public function exportToCsv($data, $tableName, $timestamp)
    {
        $spreadsheet = $this->createSpreadsheet($data, $tableName);
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setSheetIndex(0);
        
        $filename = "{$tableName}_{$timestamp}.csv";
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'text/csv'
        ])->deleteFileAfterSend();
    }
    
    /**
     * تصدير البيانات إلى ملف JSON
     */
    public function exportToJson($data, $tableName, $timestamp)
    {
        $filename = "{$tableName}_{$timestamp}.json";
        
        $jsonData = [
            'table' => $tableName,
            'exported_at' => $timestamp,
            'total_records' => $data->count(),
            'data' => $data->toArray()
        ];
        
        return response()->json($jsonData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ]);
    }
    
    /**
     * تصدير البيانات إلى ملف SQL
     */
    public function exportToSql($data, $tableName, $timestamp)
    {
        $sql = $this->generateSQLDump($tableName, $data);
        $filename = "{$tableName}_{$timestamp}.sql";
        
        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ]);
    }
    
    /**
     * إنشاء جدول Excel منسق
     */
    private function createSpreadsheet($data, $tableName)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle(substr($tableName, 0, 31)); // حد أقصى 31 حرف لأسماء الأوراق
        
        if ($data->isEmpty()) {
            $worksheet->setCellValue('A1', 'لا توجد بيانات');
            return $spreadsheet;
        }
        
        // الحصول على أسماء الأعمدة
        $firstRow = (array) $data->first();
        $headers = array_keys($firstRow);
        $translatedHeaders = array_map([$this, 'translateHeading'], $headers);
        
        // إضافة العناوين
        $col = 1;
        foreach ($translatedHeaders as $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $worksheet->setCellValue($columnLetter . '1', $header);
            $col++;
        }
        
        // تنسيق العناوين
        $lastColumnLetter = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:' . $lastColumnLetter . '1';
        $worksheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F648E']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ],
            ],
        ]);
        
        // إضافة البيانات
        $row = 2;
        foreach ($data as $item) {
            $rowData = (array) $item;
            $col = 1;
            
            foreach ($headers as $header) {
                $value = $rowData[$header] ?? '';
                
                // تنسيق البيانات
                if (in_array($header, ['created_at', 'updated_at', 'deleted_at']) && $value) {
                    try {
                        $value = Carbon::parse($value)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        // إبقاء القيمة كما هي
                    }
                } elseif (is_bool($value)) {
                    $value = $value ? 'نعم' : 'لا';
                } elseif ($value === '1' || $value === 1) {
                    if (in_array($header, ['is_active', 'is_enabled', 'is_archived', 'status'])) {
                        $value = 'نعم';
                    }
                } elseif ($value === '0' || $value === 0) {
                    if (in_array($header, ['is_active', 'is_enabled', 'is_archived', 'status'])) {
                        $value = 'لا';
                    }
                }
                
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $worksheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // تنسيق البيانات
        if ($row > 2) {
            $lastColumnLetter = Coordinate::stringFromColumnIndex(count($headers));
            $dataRange = 'A2:' . $lastColumnLetter . ($row - 1);
            $worksheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }
        
        // ضبط عرض الأعمدة تلقائياً
        foreach (range(1, count($headers)) as $col) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $worksheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }
        
        return $spreadsheet;
    }
    
    /**
     * ترجمة أسماء الأعمدة
     */
    private function translateHeading($heading)
    {
        $translations = [
            'id' => 'المعرف',
            'name' => 'الاسم',
            'name_ar' => 'الاسم بالعربية',
            'name_en' => 'الاسم بالإنجليزية',
            'email' => 'البريد الإلكتروني',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'user_type' => 'نوع المستخدم',
            'role_id' => 'معرف الدور',
            'profile_picture' => 'صورة الملف الشخصي',
            'department_id' => 'معرف القسم',
            'branch_id' => 'معرف الفرع',
            'manager_id' => 'معرف المدير',
            'created_by' => 'منشئ بواسطة',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
            'deleted_at' => 'تاريخ الحذف',
            'is_active' => 'نشط',
            'is_enabled' => 'مفعل',
            'is_archived' => 'مؤرشف',
            'status' => 'الحالة',
            'description' => 'الوصف',
            'notes' => 'الملاحظات',
            'phone' => 'الهاتف',
            'address' => 'العنوان',
            'position' => 'المنصب',
            'hire_date' => 'تاريخ التوظيف',
            'title' => 'العنوان',
            'content' => 'المحتوى',
            'category_id' => 'معرف الفئة',
            'priority' => 'الأولوية',
            'due_date' => 'تاريخ الاستحقاق',
            'completed_at' => 'تاريخ الإنجاز',
            'assigned_to' => 'مُكلف إلى',
            'asset_id' => 'معرف الأصل',
            'location_id' => 'معرف الموقع',
            'serial_number' => 'الرقم التسلسلي',
            'model' => 'الموديل',
            'brand' => 'العلامة التجارية',
            'purchase_date' => 'تاريخ الشراء',
            'warranty_expiry' => 'انتهاء الضمان',
            'condition' => 'الحالة',
            'value' => 'القيمة',
            'quantity' => 'الكمية',
            'price' => 'السعر',
            'total' => 'المجموع',
            'supplier_id' => 'معرف المورد',
            'contact_person' => 'شخص الاتصال',
            'website' => 'الموقع الإلكتروني',
            'tax_number' => 'الرقم الضريبي',
            'bank_account' => 'رقم الحساب البنكي',
            'payment_terms' => 'شروط الدفع',
        ];

        return $translations[$heading] ?? ucfirst(str_replace('_', ' ', $heading));
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
}
