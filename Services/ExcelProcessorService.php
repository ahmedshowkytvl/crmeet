<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ExcelProcessorService
{
    /**
     * معالجة ملف Excel واستخراج البيانات
     */
    public function processFile($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data) || count($data) < 2) {
                throw new \Exception('الملف فارغ أو لا يحتوي على بيانات صحيحة');
            }

            $headers = array_shift($data);
            $employees = [];

            foreach ($data as $index => $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $employeeData = [];
                foreach ($headers as $colIndex => $header) {
                    $employeeData[trim($header)] = $row[$colIndex] ?? '';
                }
                
                $employeeData['_row_number'] = $index + 2;
                $employees[] = $employeeData;
            }

            if (empty($employees)) {
                throw new \Exception('لا توجد بيانات صحيحة في الملف');
            }

            return $employees;

        } catch (\Exception $e) {
            Log::error('خطأ في معالجة ملف Excel: ' . $e->getMessage());
            throw $e;
        }
    }
}
