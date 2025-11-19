<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\DateParserService;
use App\Services\EmployeeDataService;
use App\Services\ExcelProcessorService;

class BatchUserController extends Controller
{
    protected $dateParser;
    protected $employeeDataService;
    protected $excelProcessor;

    public function __construct(
        DateParserService $dateParser,
        EmployeeDataService $employeeDataService,
        ExcelProcessorService $excelProcessor
    ) {
        $this->dateParser = $dateParser;
        $this->employeeDataService = $employeeDataService;
        $this->excelProcessor = $excelProcessor;
    }

    /**
     * عرض صفحة إضافة الموظفين المتقدمة
     */
    public function showAdvancedBatchCreate()
    {
        return view('users.batch-create-advanced');
    }

    /**
     * معالجة حفظ الموظفين دفعة واحدة
     */
    public function batchCreate(Request $request)
    {
        try {
            $validatedData = $this->validateBatchRequest($request);
            $employees = $validatedData['employees'];
            $defaultValues = $validatedData['defaultValues'];

            $results = $this->processBatchEmployees($employees, $defaultValues);

            return $this->buildSuccessResponse($results, count($employees));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->buildValidationErrorResponse($e);
        } catch (\Exception $e) {
            Log::error('خطأ عام في BatchUserController: ' . $e->getMessage());
            return $this->buildErrorResponse('حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    /**
     * معالجة ملف Excel مباشرة
     */
    public function processExcelFile(Request $request)
    {
        try {
            $validatedFile = $this->validateExcelFile($request);
            $employees = $this->excelProcessor->processFile($validatedFile);
            
            $results = $this->processBatchEmployees($employees, []);

            return $this->buildSuccessResponse($results, count($employees));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->buildValidationErrorResponse($e);
        } catch (\Exception $e) {
            Log::error('خطأ عام في معالجة ملف Excel: ' . $e->getMessage());
            return $this->buildErrorResponse('حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على قائمة الأقسام للواجهة
     */
    public function getDepartments()
    {
        try {
            $departments = Department::select('id', 'name', 'name_ar', 'code')
                ->where('is_active', true)
                ->orderBy('name_ar')
                ->get();
            
            return response()->json($departments);
        } catch (\Exception $e) {
            Log::error('خطأ في جلب الأقسام: ' . $e->getMessage());
            return response()->json(['error' => 'خطأ في جلب الأقسام'], 500);
        }
    }

    /**
     * تصدير قالب Excel
     */
    public function downloadTemplate()
    {
        try {
            $templateData = $this->buildTemplateData();
            $filename = 'قالب_الموظفين_' . date('Y-m-d') . '.xlsx';
            
            return response()->json([
                'success' => true,
                'template_data' => $templateData,
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء قالب Excel: ' . $e->getMessage());
            return response()->json(['error' => 'خطأ في إنشاء القالب'], 500);
        }
    }

    /**
     * الحصول على إحصائيات النظام
     */
    public function getSystemStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_departments' => Department::count(),
                'active_departments' => Department::where('is_active', true)->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count()
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('خطأ في جلب إحصائيات النظام: ' . $e->getMessage());
            return response()->json(['error' => 'خطأ في جلب الإحصائيات'], 500);
        }
    }

    // ========== Private Helper Methods ==========

    /**
     * التحقق من صحة طلب الدفعة
     */
    private function validateBatchRequest(Request $request)
    {
        return $request->validate([
            'employees' => 'required|array|min:1',
            'employees.*.name' => 'required|string|max:255',
            'employees.*.email' => 'required|email',
            'defaultValues' => 'sometimes|array'
        ], [
            'employees.required' => 'يرجى إرسال بيانات الموظفين',
            'employees.min' => 'يجب إرسال موظف واحد على الأقل',
            'employees.*.name.required' => 'الاسم مطلوب لجميع الموظفين',
            'employees.*.email.required' => 'البريد الإلكتروني مطلوب لجميع الموظفين',
            'employees.*.email.email' => 'البريد الإلكتروني غير صحيح'
        ]);
    }

    /**
     * التحقق من صحة ملف Excel
     */
    private function validateExcelFile(Request $request)
    {
        return $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240'
        ], [
            'excel_file.required' => 'يرجى اختيار ملف Excel',
            'excel_file.mimes' => 'يجب أن يكون الملف من نوع Excel (.xlsx أو .xls)',
            'excel_file.max' => 'حجم الملف يجب أن يكون أقل من 10 ميجابايت'
        ]);
    }

    /**
     * معالجة مجموعة من الموظفين
     */
    private function processBatchEmployees(array $employees, array $defaultValues)
    {
        $results = [
            'saved' => 0,
            'failed' => 0,
            'errors' => [],
            'success_employees' => [],
            'failed_employees' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($employees as $index => $employeeData) {
                $result = $this->employeeDataService->createEmployee($employeeData, $defaultValues, $index + 1);
                
                if ($result['success']) {
                    $results['saved']++;
                    $results['success_employees'][] = $result['employee'];
                } else {
                    $results['failed']++;
                    $results['failed_employees'][] = [
                        'row' => $employeeData['_row_number'] ?? $index + 1,
                        'data' => $employeeData,
                        'error' => $result['error']
                    ];
                    $results['errors'][] = "الصف {$result['row']}: {$result['error']}";
                }
            }

            DB::commit();
            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في حفظ الموظفين دفعة واحدة: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * بناء استجابة النجاح
     */
    private function buildSuccessResponse(array $results, int $totalEmployees)
    {
        return response()->json([
            'success' => true,
            'message' => "تم حفظ {$results['saved']} موظف بنجاح من أصل {$totalEmployees}",
            'saved' => $results['saved'],
            'failed' => $results['failed'],
            'errors' => $results['errors'],
            'success_employees' => $results['success_employees'],
            'failed_employees' => $results['failed_employees']
        ]);
    }

    /**
     * بناء استجابة خطأ التحقق
     */
    private function buildValidationErrorResponse(\Illuminate\Validation\ValidationException $e)
    {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $e->errors()
        ], 422);
    }

    /**
     * بناء استجابة الخطأ العام
     */
    private function buildErrorResponse(string $message, int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    /**
     * بناء بيانات القالب
     */
    private function buildTemplateData()
    {
        return [
            ['الاسم', 'البريد الإلكتروني', 'رقم الهاتف', 'المنصب', 'القسم', 'تاريخ التعيين', 'تاريخ الميلاد', 'تاريخ انتهاء العقد', 'العنوان', 'ملاحظات'],
            ['أحمد محمد', 'ahmed@example.com', '966501234567', 'مطور ويب', 'تقنية المعلومات', '01-JAN-2025', '15-يناير-1990', '31-ديسمبر-2025', 'الرياض، المملكة العربية السعودية', 'موظف جديد'],
            ['فاطمة علي', 'fatima@example.com', '966501234568', 'مصممة جرافيك', 'التسويق', '15-Jan-2024', '22-فبراير-1988', '30-يونيو-2026', 'جدة، المملكة العربية السعودية', 'خبرة 3 سنوات'],
            ['محمد أحمد', 'mohamed@example.com', '966501234569', 'محاسب', 'المحاسبة', '12/2023', '10-مارس-1992', '28-فبراير-2027', 'الدمام، المملكة العربية السعودية', 'خريج جديد'],
            ['سارة خالد', 'sara@example.com', '966501234570', 'مديرة مبيعات', 'المبيعات', '2024-06-15', '5-أبريل-1985', '15-أغسطس-2028', 'الرياض، المملكة العربية السعودية', 'خبرة 8 سنوات'],
            ['عبدالله سالم', 'abdullah@example.com', '966501234571', 'مهندس برمجيات', 'تقنية المعلومات', '01-JUL-2024', '18-مايو-1993', '31-ديسمبر-2029', 'الخبر، المملكة العربية السعودية', 'خريج حديث']
        ];
    }
}