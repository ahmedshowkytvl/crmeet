<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\User;
use App\Models\Task;
use App\Models\ZohoDepartmentMapping;
use App\Models\ZohoTicketCache;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount('users');
        
        // Handle per_page parameter
        $perPage = $request->get('per_page', 15);
        
        if ($perPage === 'all') {
            $departments = $query->latest()->get();
            // Create a custom paginator for "all" option
            $departments = new \Illuminate\Pagination\LengthAwarePaginator(
                $departments,
                $departments->count(),
                $departments->count(),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
        } else {
            $departments = $query->latest()->paginate($perPage);
        }
        
        // Add task counts for each department
        foreach($departments as $department) {
            $userIds = $department->users->pluck('id');
            
            $department->critical_tasks_count = Task::whereIn('assigned_to', $userIds)
                ->where('priority', 'high')->count();
                
            $department->medium_tasks_count = Task::whereIn('assigned_to', $userIds)
                ->where('priority', 'medium')->count();
                
            $department->low_tasks_count = Task::whereIn('assigned_to', $userIds)
                ->where('priority', 'low')->count();
        }
        
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $users = User::all();
        return view('departments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'zoho_id' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'name_ar', 'description', 'description_ar', 
            'manager_id'
        ]);

        // Process zoho_id - convert comma-separated string to array
        if ($request->filled('zoho_id')) {
            $zohoIds = array_map('trim', explode(',', $request->zoho_id));
            $zohoIds = array_filter($zohoIds); // Remove empty values
            
            // Validate that all IDs are numeric
            foreach ($zohoIds as $id) {
                if (!is_numeric($id)) {
                    return redirect()->back()
                        ->withErrors(['zoho_id' => 'معرفات Zoho يجب أن تكون أرقام فقط'])
                        ->withInput();
                }
            }
            
            $data['zoho_id'] = !empty($zohoIds) ? $zohoIds : null;
        } else {
            $data['zoho_id'] = null;
        }

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'تم إنشاء القسم بنجاح');
    }

    public function show(Department $department, Request $request)
    {
        $department->load(['users', 'tasks']);
        
        // Pagination: 20 tickets per page
        $perPage = 20;
        
        // ⚠️ مهم: هذه الصفحة تعتمد فقط على الكاش (ZohoTicketCache)
        // لا يتم جلب أي بيانات من Zoho API مباشرة هنا
        // للحديث البيانات، استخدم صفحة Zoho Bulk Sync
        
        // جلب جميع التذاكر لهذا القسم من الكاش فقط (للبحث في جميع التذاكر)
        $allTicketsForSearch = ZohoTicketCache::with(['user', 'department'])
            ->where('department_id', $department->id)
            ->orderBy('created_at_zoho', 'desc')
            ->get();
        
        // جلب جميع التذاكر للعرض مع pagination (من الكاش فقط)
        $zohoTickets = ZohoTicketCache::with(['user', 'department'])
            ->where('department_id', $department->id)
            ->orderBy('created_at_zoho', 'desc')
            ->paginate($perPage);
        
        // إحصائيات لجميع التذاكر (ليس فقط المعروضة)
        $allTicketsCount = ZohoTicketCache::where('department_id', $department->id)->count();
        $allTickets = ZohoTicketCache::where('department_id', $department->id)->get();
        
        $ticketStats = [
            'total_tickets' => $allTicketsCount,
            'closed_tickets' => $allTickets->where('status', 'Closed')->count(),
            'open_tickets' => $allTickets->where('status', 'Open')->count(),
            'pending_tickets' => $allTickets->where('status', 'Pending')->count(),
            'in_progress_tickets' => $allTickets->where('status', 'In Progress')->count(),
        ];
        
        return view('departments.show', compact('department', 'zohoTickets', 'ticketStats', 'allTicketsForSearch'));
    }

    public function organizationalChart(Department $department)
    {
        $department->load(['users.role', 'users.manager', 'tasks']);
        return view('departments.organizational-chart', compact('department'));
    }

    public function zohoTickets(Department $department, Request $request)
    {
        // Pagination: 20 tickets per page
        $perPage = 20;
        
        // جلب التذاكر مع pagination
        $zohoTickets = ZohoTicketCache::with(['user', 'department'])
            ->where('department_id', $department->id)
            ->orderBy('created_at_zoho', 'desc')
            ->paginate($perPage);
        
        // إحصائيات لجميع التذاكر (ليس فقط المعروضة)
        $allTicketsCount = ZohoTicketCache::where('department_id', $department->id)->count();
        $allTickets = ZohoTicketCache::where('department_id', $department->id)->get();
        
        $ticketStats = [
            'total_tickets' => $allTicketsCount,
            'closed_tickets' => $allTickets->where('status', 'Closed')->count(),
            'open_tickets' => $allTickets->where('status', 'Open')->count(),
            'pending_tickets' => $allTickets->where('status', 'Pending')->count(),
            'in_progress_tickets' => $allTickets->where('status', 'In Progress')->count(),
        ];
        
        return view('departments.zoho-tickets', compact('department', 'zohoTickets', 'ticketStats'));
    }

    public function edit(Department $department)
    {
        $users = User::all();
        
        // جلب الـ Zoho mappings المرتبطة بهذا القسم
        $zohoMappings = ZohoDepartmentMapping::where('local_department_id', $department->id)
                                           ->with('localDepartment')
                                           ->get();
        
        // جلب آخر 200 تذكرة Zoho لهذا القسم (من قاعدة البيانات المحلية)
        $zohoTickets = ZohoTicketCache::with(['user', 'department'])
            ->where('department_id', $department->id)
            ->orderBy('created_at_zoho', 'desc')
            ->limit(200)
            ->get();
        
        // إحصائيات سريعة للتذاكر
        $ticketStats = [
            'total_tickets' => $zohoTickets->count(),
            'closed_tickets' => $zohoTickets->where('status', 'Closed')->count(),
            'open_tickets' => $zohoTickets->where('status', 'Open')->count(),
            'pending_tickets' => $zohoTickets->where('status', 'Pending')->count(),
            'in_progress_tickets' => $zohoTickets->where('status', 'In Progress')->count(),
        ];
        
        return view('departments.edit', compact('department', 'users', 'zohoMappings', 'zohoTickets', 'ticketStats'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'zoho_id' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'name_ar', 'description', 'description_ar', 
            'manager_id'
        ]);

        // Process zoho_id - convert comma-separated string to array
        if ($request->filled('zoho_id')) {
            $zohoIds = array_map('trim', explode(',', $request->zoho_id));
            $zohoIds = array_filter($zohoIds); // Remove empty values
            
            // Validate that all IDs are numeric
            foreach ($zohoIds as $id) {
                if (!is_numeric($id)) {
                    return redirect()->back()
                        ->withErrors(['zoho_id' => 'معرفات Zoho يجب أن تكون أرقام فقط'])
                        ->withInput();
                }
            }
            
            $data['zoho_id'] = !empty($zohoIds) ? $zohoIds : null;
        } else {
            $data['zoho_id'] = null;
        }

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'تم تحديث القسم بنجاح');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'تم حذف القسم بنجاح');
    }

    public function refreshClosedBy(Department $department, Request $request)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            
            // جلب جميع التذاكر لهذا القسم (حتى التي تحتوي على بيانات خاطئة)
            $tickets = ZohoTicketCache::where('department_id', $department->id)->get();
            
            $updated = 0;
            $errors = 0;
            
            foreach ($tickets as $ticket) {
                try {
                    // جلب تفاصيل التذكرة من Zoho API
                    $ticketData = $apiClient->getTicket($ticket->zoho_ticket_id);
                    
                    if (!$ticketData || !isset($ticketData['data'])) {
                        continue;
                    }
                    
                    $ticketDetails = $ticketData['data'];
                    
                    // حفظ القيمة القديمة من closed_by_name
                    $oldValue = $ticket->closed_by_name;
                    
                    // جلب cf_closed_by من cf
                    $cfClosedBy = $ticketDetails['cf']['cf_closed_by'] ?? null;
                    
                    // إذا لم توجد في cf، ابحث في customFields
                    if (empty($cfClosedBy) && isset($ticketDetails['customFields']['Closed By']) && !empty($ticketDetails['customFields']['Closed By'])) {
                        $cfClosedBy = $ticketDetails['customFields']['Closed By'];
                    }
                    
                    // تحديث raw_data كاملة من Zoho API (لضمان أن البيانات محدثة)
                    $ticket->raw_data = $ticketDetails;
                    
                    // تحديث closed_by_name إذا وجدت قيمة من raw_data
                    if (!empty($cfClosedBy) && $cfClosedBy !== 'Auto Close' && $cfClosedBy !== 'Unknown Agent') {
                        $ticket->closed_by_name = $cfClosedBy;
                        Log::info('Updated closed_by_name from raw_data', [
                            'ticket_number' => $ticket->ticket_number,
                            'old_closed_by_name' => $oldValue,
                            'new_closed_by_name' => $cfClosedBy
                        ]);
                    }
                    
                    // raw_data دائماً محدثة من Zoho API
                    $needsUpdate = true;
                    
                    if ($needsUpdate) {
                        $ticket->save();
                        $updated++;
                        
                        Log::info('Updated ticket from Zoho', [
                            'ticket_number' => $ticket->ticket_number,
                            'old_closed_by_name' => $oldValue,
                            'new_closed_by_name' => $cfClosedBy ?? $ticket->closed_by_name,
                            'raw_data_updated' => true,
                            'cf_closed_by_from_zoho' => $cfClosedBy
                        ]);
                    }
                    
                    // Rate limiting
                    usleep(500000); // 0.5 seconds
                    
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error refreshing closed_by for ticket', [
                        'ticket_id' => $ticket->zoho_ticket_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "تم تحديث {$updated} تذكرة بنجاح",
                'updated' => $updated,
                'errors' => $errors,
                'total' => $tickets->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}