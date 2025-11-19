<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckController extends Controller
{
    /**
     * Check the health of the application and database
     */
    public function check(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Test a simple query
            DB::select('SELECT 1');
            
            $status = 'online';
            $message = 'Database connection is working properly';
            $details = [
                'database' => 'Connected',
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ];
            
        } catch (\Exception $e) {
            $status = 'offline';
            $message = 'Database connection failed: ' . $e->getMessage();
            $details = [
                'database' => 'Disconnected',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ], $status === 'online' ? 200 : 503);
    }
}
