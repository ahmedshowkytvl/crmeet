<?php

namespace App\Http\Controllers;

use App\Services\ZohoApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZohoAdvancedSearchController extends Controller
{
    protected $apiClient;

    public function __construct(ZohoApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Display the advanced search page
     */
    public function index()
    {
        return view('zoho.advanced-search');
    }

    /**
     * Search tickets by text
     */
    public function searchByText(Request $request)
    {
        try {
            $searchQuery = $request->input('search_text');
            
            if (empty($searchQuery)) {
                return response()->json([
                    'success' => false,
                    'error' => 'يرجى إدخال نص البحث',
                    'message' => 'empty_search_query'
                ], 400);
            }

            Log::info('Searching by text', ['search_query' => $searchQuery]);

            $result = $this->apiClient->advancedSearchByText($searchQuery, 1000);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل في الاتصال بـ Zoho API. يرجى المحاولة مرة أخرى',
                    'message' => 'zoho_connection_failed'
                ], 500);
            }

            if (!isset($result['data']) || empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'لم يتم العثور على أي تذاكر تطابق نص البحث',
                    'message' => 'no_results',
                    'search_query' => $searchQuery
                ]);
            }

            Log::info('Search successful', ['results_count' => $result['count']]);

            return response()->json([
                'success' => true,
                'tickets' => $result['data'],
                'count' => $result['count']
            ]);

        } catch (\Exception $e) {
            Log::error('Error in search by text', [
                'search_query' => $request->input('search_text'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى',
                'message' => 'search_failed',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search tickets by custom field (CF_Closed_by)
     */
    public function searchByCustomField(Request $request)
    {
        try {
            $fieldValue = $request->input('cf_closed_by');
            
            if (empty($fieldValue)) {
                return response()->json([
                    'success' => false,
                    'error' => 'يرجى إدخال اسم الموظف',
                    'message' => 'empty_field_value'
                ], 400);
            }

            Log::info('Searching by custom field', ['field_value' => $fieldValue]);

            $result = $this->apiClient->advancedSearchByCustomField('cf_closed_by', $fieldValue, 1000);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل في الاتصال بـ Zoho API. يرجى المحاولة مرة أخرى',
                    'message' => 'zoho_connection_failed'
                ], 500);
            }

            if (!isset($result['data']) || empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => "لم يتم العثور على أي تذاكر تم إغلاقها بواسطة: {$fieldValue}",
                    'message' => 'no_results',
                    'field_value' => $fieldValue
                ]);
            }

            Log::info('Search successful', ['results_count' => $result['count']]);

            return response()->json([
                'success' => true,
                'tickets' => $result['data'],
                'count' => $result['count']
            ]);

        } catch (\Exception $e) {
            Log::error('Error in search by custom field', [
                'field_value' => $request->input('cf_closed_by'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى',
                'message' => 'search_failed',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search tickets by time range
     */
    public function searchByTimeRange(Request $request)
    {
        try {
            $period = $request->input('period'); // 'day', 'month', 'year'
            
            if (empty($period)) {
                return response()->json([
                    'success' => false,
                    'error' => 'يرجى اختيار فترة البحث (اليوم، الشهر، أو السنة)',
                    'message' => 'missing_period'
                ], 400);
            }

            if (!in_array($period, ['day', 'month', 'year'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'فترة غير صحيحة. استخدم: day, month, أو year',
                    'message' => 'invalid_period'
                ], 400);
            }

            $startDate = '';
            $endDate = now()->format('Y-m-d');
            
            switch ($period) {
                case 'day':
                    $startDate = now()->format('Y-m-d');
                    $periodName = 'اليوم';
                    break;
                case 'month':
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $periodName = 'هذا الشهر';
                    break;
                case 'year':
                    $startDate = now()->startOfYear()->format('Y-m-d');
                    $periodName = 'هذا العام';
                    break;
                default:
                    $periodName = $period;
            }

            Log::info('Searching by time range', [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $result = $this->apiClient->advancedSearchByTimeRange($startDate, $endDate, 5000);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل في الاتصال بـ Zoho API. يرجى المحاولة مرة أخرى',
                    'message' => 'zoho_connection_failed'
                ], 500);
            }

            if (!isset($result['data']) || empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => "لم يتم العثور على أي تذاكر في فترة ({$periodName})",
                    'message' => 'no_results',
                    'period' => $periodName
                ]);
            }

            Log::info('Search successful', [
                'period' => $period,
                'results_count' => $result['count']
            ]);

            return response()->json([
                'success' => true,
                'tickets' => $result['data'],
                'count' => $result['count'],
                'period' => $periodName
            ]);

        } catch (\Exception $e) {
            Log::error('Error in search by time range', [
                'period' => $request->input('period'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء البحث. يرجى التحقق من سجلات الأخطاء',
                'message' => 'search_failed',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

