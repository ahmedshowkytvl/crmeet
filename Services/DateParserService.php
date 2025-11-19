<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DateParserService
{
    /**
     * تحليل التاريخ وتحويله إلى تنسيق موحد
     */
    public function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        
        try {
            $dateString = trim($dateString);
            
            // معالجة الأرقام التسلسلية لـ Excel أولاً
            if (is_numeric($dateString)) {
                $serialNumber = (float)$dateString;
                
                if ($serialNumber >= 1 && $serialNumber <= 100000) {
                    return $this->parseExcelSerialDate($serialNumber);
                }
            }
            
            // معالجة الأرقام التسلسلية الكبيرة (مثل: 47950، 46203)
            if (is_numeric($dateString)) {
                $serialNumber = (float)$dateString;
                
                if ($serialNumber > 25569) { // 1970-01-01 في Excel
                    return $this->parseLargeExcelSerialDate($serialNumber);
                }
            }
            
            // محاولة تحليل التاريخ بجميع الأشكال
            $formats = $this->getSupportedDateFormats();
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date !== false) {
                    return $this->formatDateToStandard($date);
                }
            }
            
            // محاولة معالجة تنسيقات خاصة
            $specialFormats = $this->parseSpecialDateFormats($dateString);
            if ($specialFormats) {
                return $specialFormats;
            }
            
            // محاولة باستخدام strtotime كخيار أخير
            $timestamp = strtotime($dateString);
            if ($timestamp !== false) {
                $date = new \DateTime();
                $date->setTimestamp($timestamp);
                return $this->formatDateToStandard($date);
            }
            
        } catch (\Exception $e) {
            Log::warning("خطأ في تحليل التاريخ: {$dateString} - " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * تحليل الأرقام التسلسلية لـ Excel
     */
    private function parseExcelSerialDate($serialNumber)
    {
        $excelEpoch = new \DateTime('1900-01-01');
        $excelEpoch->modify('-2 days'); // تصحيح خطأ Excel المعروف
        
        $days = $serialNumber - 1; // Excel يبدأ من 1
        $excelEpoch->modify("+{$days} days");
        
        return $this->formatDateToStandard($excelEpoch);
    }

    /**
     * تحليل الأرقام التسلسلية الكبيرة لـ Excel
     */
    private function parseLargeExcelSerialDate($serialNumber)
    {
        // تحويل من Excel serial date إلى timestamp
        $timestamp = ($serialNumber - 25569) * 86400; // 25569 = 1970-01-01 في Excel
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        
        return $this->formatDateToStandard($date);
    }

    /**
     * الحصول على قائمة تنسيقات التواريخ المدعومة
     */
    private function getSupportedDateFormats()
    {
        return [
            // تنسيقات شائعة
            'Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y', 'm-d-Y',
            'd.m.Y', 'm.d.Y', 'd/m/y', 'm/d/y', 'd-m-y', 'm-d-y',
            
            // تنسيقات DD-MM-YYYY مع أصفار (مثل: 03-11-1995)
            'd-m-Y', 'd/m/Y', 'd.m.Y',
            
            // تنسيقات MM-DD-YYYY مع أصفار (مثل: 11-03-1995)
            'm-d-Y', 'm/d/Y', 'm.d.Y',
            
            // تنسيقات مع الشهر بالكلمات (إنجليزي)
            'd-M-Y', 'd-F-Y', 'M-d-Y', 'F-d-Y', 'd M Y', 'd F Y',
            'M d Y', 'F d Y',
            
            // تنسيقات مع الشهر بالكلمات (عربي)
            'd-M-Y', 'd-F-Y', 'd M Y', 'd F Y',
            
            // تنسيقات Excel الشائعة
            'j-n-Y', 'n-j-Y', 'j/n/Y', 'n/j/Y',
            
            // تنسيقات مع وقت
            'Y-m-d H:i:s', 'd/m/Y H:i:s', 'm/d/Y H:i:s',
        ];
    }

    /**
     * معالجة تنسيقات التواريخ الخاصة
     */
    private function parseSpecialDateFormats($dateString)
    {
        // معالجة تنسيق DD-MM-YYYY (مثل: 03-11-1995)
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            
            // التحقق من صحة التاريخ
            if (checkdate($month, $day, $year)) {
                $dateString = "{$year}-{$month}-{$day}";
                $date = \DateTime::createFromFormat('Y-m-d', $dateString);
                if ($date !== false) {
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        // معالجة تنسيق DD-Mon-YYYY (مثل: 01-JAN-2025)
        if (preg_match('/^(\d{1,2})-([A-Z]{3})-(\d{4})$/', $dateString, $matches)) {
            return $this->parseMonthAbbreviationFormat($matches);
        }
        
        // معالجة تنسيق DD-Mon-YYYY (مثل: 15-Jan-2025)
        if (preg_match('/^(\d{1,2})-([A-Za-z]{3})-(\d{4})$/', $dateString, $matches)) {
            return $this->parseMonthAbbreviationFormat($matches, true);
        }
        
        // معالجة تنسيق MM/YYYY (مثل: 12/2024)
        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
            return $this->parseMonthYearFormat($matches);
        }
        
        // معالجة التواريخ المختلطة (يوم + شهر + رقم تسلسلي)
        $mixedResult = $this->parseMixedDateFormats($dateString);
        if ($mixedResult) {
            return $mixedResult;
        }
        
        // معالجة التواريخ العربية
        return $this->parseArabicDate($dateString);
    }

    /**
     * معالجة تنسيق الشهر المختصر
     */
    private function parseMonthAbbreviationFormat($matches, $caseSensitive = false)
    {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = $caseSensitive ? ucfirst(strtolower($matches[2])) : strtoupper($matches[2]);
        $year = $matches[3];
        
        $monthMap = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
        ];
        
        if ($caseSensitive) {
            $monthMap = array_merge($monthMap, [
                'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
                'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
                'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12'
            ]);
        }
        
        if (isset($monthMap[$month])) {
            $dateString = "{$year}-{$monthMap[$month]}-{$day}";
            $date = \DateTime::createFromFormat('Y-m-d', $dateString);
            if ($date !== false) {
                return $this->formatDateToStandard($date);
            }
        }
        
        return null;
    }

    /**
     * معالجة تنسيق الشهر/السنة
     */
    private function parseMonthYearFormat($matches)
    {
        $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $year = $matches[2];
        $dateString = "{$year}-{$month}-01";
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($date !== false) {
            return $this->formatDateToStandard($date);
        }
        return null;
    }

    /**
     * معالجة التواريخ المختلطة (يوم + شهر + رقم تسلسلي)
     */
    private function parseMixedDateFormats($dateString)
    {
        // معالجة تنسيق "01 يناير 47950" أو "15 فبراير 46203"
        if (preg_match('/^(\d{1,2})\s+([^0-9]+)\s+(\d{5,})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthText = trim($matches[2]);
            $serialNumber = $matches[3];
            
            // تحويل الشهر العربي إلى إنجليزي
            $arabicMonths = [
                'يناير' => 'January', 'فبراير' => 'February', 'مارس' => 'March',
                'أبريل' => 'April', 'مايو' => 'May', 'يونيو' => 'June',
                'يوليو' => 'July', 'أغسطس' => 'August', 'سبتمبر' => 'September',
                'أكتوبر' => 'October', 'نوفمبر' => 'November', 'ديسمبر' => 'December'
            ];
            
            if (isset($arabicMonths[$monthText])) {
                $englishMonth = $arabicMonths[$monthText];
                
                // تحويل الرقم التسلسلي إلى سنة
                if (is_numeric($serialNumber) && $serialNumber > 25569) {
                    $excelDate = (float)$serialNumber;
                    $timestamp = ($excelDate - 25569) * 86400;
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    $year = $date->format('Y');
                    
                    // إنشاء التاريخ النهائي
                    $finalDateString = "{$day} {$englishMonth} {$year}";
                    $finalDate = \DateTime::createFromFormat('j F Y', $finalDateString);
                    
                    if ($finalDate !== false) {
                        return $this->formatDateToStandard($finalDate);
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * معالجة التواريخ العربية
     */
    private function parseArabicDate($dateString)
    {
        $arabicMonths = [
            'يناير' => 'January', 'فبراير' => 'February', 'مارس' => 'March',
            'أبريل' => 'April', 'مايو' => 'May', 'يونيو' => 'June',
            'يوليو' => 'July', 'أغسطس' => 'August', 'سبتمبر' => 'September',
            'أكتوبر' => 'October', 'نوفمبر' => 'November', 'ديسمبر' => 'December'
        ];
        
        foreach ($arabicMonths as $arabic => $english) {
            if (strpos($dateString, $arabic) !== false) {
                $englishDate = str_replace($arabic, $english, $dateString);
                $timestamp = strtotime($englishDate);
                if ($timestamp !== false) {
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        // معالجة تنسيق "15-يناير-1990"
        if (preg_match('/^(\d{1,2})-([^0-9]+)-(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthText = trim($matches[2]);
            $year = $matches[3];
            
            if (isset($arabicMonths[$monthText])) {
                $englishMonth = $arabicMonths[$monthText];
                $dateString = "{$day} {$englishMonth} {$year}";
                $timestamp = strtotime($dateString);
                if ($timestamp !== false) {
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        // معالجة تنسيق "15 يناير 1990"
        if (preg_match('/^(\d{1,2})\s+([^0-9]+)\s+(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthText = trim($matches[2]);
            $year = $matches[3];
            
            if (isset($arabicMonths[$monthText])) {
                $englishMonth = $arabicMonths[$monthText];
                $dateString = "{$day} {$englishMonth} {$year}";
                $timestamp = strtotime($dateString);
                if ($timestamp !== false) {
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        // معالجة تنسيق "15 يناير 1990" مع مسافات إضافية
        if (preg_match('/^(\d{1,2})\s+([^0-9]+)\s+(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthText = trim($matches[2]);
            $year = $matches[3];
            
            if (isset($arabicMonths[$monthText])) {
                $englishMonth = $arabicMonths[$monthText];
                $dateString = "{$day} {$englishMonth} {$year}";
                $timestamp = strtotime($dateString);
                if ($timestamp !== false) {
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        // معالجة تنسيق "15 يناير 1990" مع مسافات إضافية
        if (preg_match('/^(\d{1,2})\s+([^0-9]+)\s+(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthText = trim($matches[2]);
            $year = $matches[3];
            
            if (isset($arabicMonths[$monthText])) {
                $englishMonth = $arabicMonths[$monthText];
                $dateString = "{$day} {$englishMonth} {$year}";
                $timestamp = strtotime($dateString);
                if ($timestamp !== false) {
                    $date = new \DateTime();
                    $date->setTimestamp($timestamp);
                    return $this->formatDateToStandard($date);
                }
            }
        }
        
        return null;
    }

    /**
     * تحويل التاريخ إلى تنسيق موحد DD Month YYYY للعرض و YYYY-MM-DD للحفظ
     */
    private function formatDateToStandard(\DateTime $date)
    {
        // إرجاع التاريخ بصيغة YYYY-MM-DD للحفظ في قاعدة البيانات
        return $date->format('Y-m-d');
    }
    
    /**
     * تحويل التاريخ إلى تنسيق عربي للعرض
     */
    public function formatDateForDisplay(\DateTime $date)
    {
        $arabicMonths = [
            'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس',
            'April' => 'أبريل', 'May' => 'مايو', 'June' => 'يونيو',
            'July' => 'يوليو', 'August' => 'أغسطس', 'September' => 'سبتمبر',
            'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر'
        ];
        
        $day = $date->format('d');
        $month = $arabicMonths[$date->format('F')] ?? $date->format('F');
        $year = $date->format('Y');
        
        return "{$day} {$month} {$year}";
    }

    /**
     * التحقق من صحة التاريخ
     */
    public function validateDate($dateString)
    {
        if (empty($dateString)) {
            return ['valid' => true];
        }
        
        try {
            $parsedDate = $this->parseDate($dateString);
            
            if ($parsedDate === null) {
                return ['valid' => false, 'error' => 'تنسيق التاريخ غير مدعوم'];
            }
            
            $date = new \DateTime();
            $inputDate = new \DateTime($parsedDate);
            
            // التحقق من أن التاريخ ليس قبل 1900
            $minDate = new \DateTime('1900-01-01');
            if ($inputDate < $minDate) {
                return ['valid' => false, 'error' => 'التاريخ قديم جداً'];
            }
            
            // التحقق من أن التاريخ ليس بعد 2050
            $maxDate = new \DateTime('2050-12-31');
            if ($inputDate > $maxDate) {
                return ['valid' => false, 'error' => 'التاريخ في المستقبل البعيد'];
            }
            
            return ['valid' => true, 'parsed_date' => $parsedDate];
            
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'خطأ في معالجة التاريخ'];
        }
    }
}
