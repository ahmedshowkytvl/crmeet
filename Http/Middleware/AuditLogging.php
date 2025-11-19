<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AuditService;

class AuditLogging
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip audit logging for certain routes
        if ($this->shouldSkipAudit($request)) {
            return $next($request);
        }

        $response = $next($request);

        // Log the action after the request is processed
        try {
            $this->auditService->logAction(
                user: Auth::user(),
                actionType: $this->getActionType($request),
                module: $this->getModule($request),
                recordId: $this->getRecordId($request),
                recordName: $this->getRecordName($request),
                details: $this->getDetails($request, $response),
                status: $response->isSuccessful() ? 'success' : 'failed',
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        } catch (\Exception $e) {
            // Log the error but don't break the request
            Log::error('Audit logging failed: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Determine if the request should skip audit logging.
     */
    protected function shouldSkipAudit(Request $request): bool
    {
        $skipRoutes = [
            'audit.*',
            'notifications.*',
            'api/health-check',
            'api/system-monitor.*',
            'api/chat.*',
            'api/notifications.*',
            'api/org-.*',
            'api/departments',
            'api/system-stats',
            'api/available-shelves',
            'api/templates-for-department',
            'lang.switch',
            'user-status.*',
        ];

        $currentRoute = $request->route()?->getName();

        if (!$currentRoute) {
            return true;
        }

        foreach ($skipRoutes as $skipRoute) {
            if (fnmatch($skipRoute, $currentRoute)) {
                return true;
            }
        }

        // Skip GET requests for index pages (to avoid too much logging)
        if ($request->isMethod('GET') && str_ends_with($currentRoute, '.index')) {
            return true;
        }

        // Skip AJAX requests
        if ($request->ajax()) {
            return true;
        }

        return false;
    }

    /**
     * Get the action type from the request.
     */
    protected function getActionType(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return 'unknown';
        }

        // Map HTTP methods and route patterns to action types
        if (str_contains($routeName, '.store')) {
            return 'create';
        } elseif (str_contains($routeName, '.update')) {
            return 'update';
        } elseif (str_contains($routeName, '.destroy')) {
            return 'delete';
        } elseif (str_contains($routeName, '.show')) {
            return 'view';
        } elseif (str_contains($routeName, '.login')) {
            return 'login';
        } elseif (str_contains($routeName, '.logout')) {
            return 'logout';
        } elseif (str_contains($routeName, '.export')) {
            return 'export';
        } elseif (str_contains($routeName, '.import')) {
            return 'import';
        } elseif (str_contains($routeName, '.archive')) {
            return 'archive';
        } elseif (str_contains($routeName, '.restore')) {
            return 'restore';
        } elseif (str_contains($routeName, '.approve')) {
            return 'approve';
        } elseif (str_contains($routeName, '.reject')) {
            return 'reject';
        } elseif (str_contains($routeName, '.assign')) {
            return 'assign';
        } elseif (str_contains($routeName, '.unassign')) {
            return 'unassign';
        }

        return strtolower($method);
    }

    /**
     * Get the module from the request.
     */
    protected function getModule(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return 'unknown';
        }

        // Extract module from route name
        $parts = explode('.', $routeName);
        $module = $parts[0];

        // Map route prefixes to module names
        $moduleMap = [
            'users' => 'employees',
            'password-accounts' => 'password_accounts',
            'password-categories' => 'password_categories',
            'suppliers' => 'suppliers',
            'contacts' => 'contacts',
            'contact-categories' => 'contact_categories',
            'tasks' => 'tasks',
            'departments' => 'departments',
            'requests' => 'requests',
            'chat' => 'chat',
            'eet-life' => 'eet_life',
            'assets' => 'assets',
            'zoho' => 'zoho',
            'notifications' => 'notifications',
            'profile' => 'profile',
            'settings' => 'settings',
            'roles' => 'roles',
            'password-assignments' => 'password_assignments',
            'password-audit' => 'password_audit',
            'task-templates' => 'task_templates',
            'task-progress' => 'task_progress',
        ];

        return $moduleMap[$module] ?? $module;
    }

    /**
     * Get the record ID from the request.
     */
    protected function getRecordId(Request $request): ?int
    {
        $route = $request->route();
        
        if (!$route) {
            return null;
        }

        // Try to get ID from route parameters
        $parameters = $route->parameters();
        
        foreach (['id', 'user', 'task', 'department', 'supplier', 'contact', 'asset', 'role'] as $param) {
            if (isset($parameters[$param])) {
                $value = $parameters[$param];
                if (is_numeric($value)) {
                    return (int) $value;
                }
                // If it's a model instance, get its ID
                if (is_object($value) && method_exists($value, 'getKey')) {
                    return $value->getKey();
                }
            }
        }

        return null;
    }

    /**
     * Get the record name from the request.
     */
    protected function getRecordName(Request $request): ?string
    {
        $route = $request->route();
        
        if (!$route) {
            return null;
        }

        $parameters = $route->parameters();
        
        // Try to get name from model instances
        foreach ($parameters as $param) {
            if (is_object($param)) {
                if (method_exists($param, 'name')) {
                    return $param->name;
                } elseif (method_exists($param, 'title')) {
                    return $param->title;
                } elseif (method_exists($param, 'getKey')) {
                    return '#' . $param->getKey();
                }
            }
        }

        return null;
    }

    /**
     * Get additional details from the request and response.
     */
    protected function getDetails(Request $request, $response): array
    {
        $details = [];

        // Add request data for non-GET requests
        if (!$request->isMethod('GET')) {
            $details['request_data'] = $request->except(['_token', '_method', 'password', 'password_confirmation']);
        }

        // Add response status
        $details['response_status'] = $response->getStatusCode();

        // Add route information
        if ($request->route()) {
            $details['route'] = $request->route()->getName();
            $details['route_parameters'] = $request->route()->parameters();
        }

        // Add request method
        $details['method'] = $request->method();

        // Add request URL
        $details['url'] = $request->fullUrl();

        return $details;
    }
}


