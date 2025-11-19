<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class DynamicOrgChartController extends Controller
{
    /**
     * Display the dynamic organizational chart page
     */
    public function index()
    {
        return view('dynamic-org-chart');
    }

    /**
     * Get organizational chart data as JSON
     */
    public function getData()
    {
        try {
            // Get all users with their relationships
            $users = User::with([
                'manager',
                'department',
                'role',
                'subordinates'
            ])
            ->get();

            // Transform data for organizational chart
            $orgData = $this->transformUsersForOrgChart($users);

            return response()->json([
                'success' => true,
                'data' => $orgData,
                'total_users' => $users->count(),
                'departments' => Department::count(),
                'message' => 'تم تحميل البيانات بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل بيانات الهيكل التنظيمي: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Transform users data for organizational chart
     */
    private function transformUsersForOrgChart($users)
    {
        $transformedUsers = [];

        foreach ($users as $user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'name_ar' => $user->name_ar ?? $user->name,
                'email' => $user->email,
                'position' => $user->position ?? $user->job_title ?? 'موظف',
                'position_ar' => $user->position_ar ?? $user->position ?? $user->job_title ?? 'موظف',
                'profile_picture' => $user->profile_picture ?? $user->profile_photo ?? asset('storage/profile_pictures/1757589898_476653860_2660576597666150_4039160244521826942_n.jpg'),
                'manager_id' => $user->manager_id,
                'department_id' => $user->department_id,
                'role_id' => $user->role_id,
                'phone_work' => $user->phone_work,
                'phone_mobile' => $user->phone_home ?? $user->phone_personal,
                'hiring_date' => $user->hiring_date ?? $user->hire_date,
                'is_manager' => $user->subordinates && $user->subordinates->count() > 0,
                'subordinates_count' => $user->subordinates ? $user->subordinates->count() : 0,
                'created_at' => $user->created_at,
            ];

            // Add department information
            if ($user->department) {
                $userData['department'] = [
                    'id' => $user->department->id,
                    'name' => $user->department->name,
                    'name_ar' => $user->department->name_ar ?? $user->department->name,
                    'code' => $user->department->code,
                ];
            }

            // Add role information
            if ($user->role) {
                $userData['role'] = [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'name_ar' => $user->role->name_ar ?? $user->role->name,
                    'slug' => $user->role->slug,
                ];
            }

            // Add manager information
            if ($user->manager) {
                $userData['manager'] = [
                    'id' => $user->manager->id,
                    'name' => $user->manager->name,
                    'name_ar' => $user->manager->name_ar ?? $user->manager->name,
                ];
            }

            $transformedUsers[] = $userData;
        }

        return $transformedUsers;
    }

    /**
     * Get organizational statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_departments' => Department::count(),
                'managers_count' => User::whereHas('subordinates')->count(),
                'employees_count' => User::whereDoesntHave('subordinates')->count(),
                'users_by_department' => User::select('department_id', DB::raw('count(*) as count'))
                    ->with('department:id,name,name_ar')
                    ->groupBy('department_id')
                    ->get(),
                'users_by_role' => User::select('role_id', DB::raw('count(*) as count'))
                    ->with('role:id,name,name_ar')
                    ->groupBy('role_id')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department hierarchy
     */
    public function getDepartmentHierarchy()
    {
        try {
            $departments = Department::with([
                'manager',
                'headManager',
                'teamLeaders',
                'users'
            ])->active()->get();

            $hierarchy = $this->buildDepartmentHierarchy($departments);

            return response()->json([
                'success' => true,
                'data' => $hierarchy
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تحميل هيكل الأقسام: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build department hierarchy structure
     */
    private function buildDepartmentHierarchy($departments)
    {
        $hierarchy = [];

        foreach ($departments as $department) {
            $deptData = [
                'id' => $department->id,
                'name' => $department->name,
                'name_ar' => $department->name_ar ?? $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'manager' => $department->manager ? [
                    'id' => $department->manager->id,
                    'name' => $department->manager->name,
                    'name_ar' => $department->manager->name_ar ?? $department->manager->name,
                ] : null,
                'head_manager' => $department->headManager ? [
                    'id' => $department->headManager->id,
                    'name' => $department->headManager->name,
                    'name_ar' => $department->headManager->name_ar ?? $department->headManager->name,
                ] : null,
                'team_leaders' => $department->teamLeaders->map(function($leader) {
                    return [
                        'id' => $leader->id,
                        'name' => $leader->name,
                        'name_ar' => $leader->name_ar ?? $leader->name,
                    ];
                }),
                'employees_count' => $department->users->count(),
                'employees' => $department->users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'name_ar' => $user->name_ar ?? $user->name,
                        'position' => $user->position ?? $user->job_title ?? 'موظف',
                    ];
                }),
            ];

            $hierarchy[] = $deptData;
        }

        return $hierarchy;
    }

    /**
     * Export organizational chart data
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            
            if ($format === 'json') {
                $data = $this->getData()->getData();
                return response()->json($data);
            }

            // Add more export formats here (PDF, Excel, etc.)
            return response()->json([
                'success' => false,
                'message' => 'تنسيق التصدير غير مدعوم'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تصدير البيانات: ' . $e->getMessage()
            ], 500);
        }
    }
}
