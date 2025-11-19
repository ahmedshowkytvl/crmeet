<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\EmployeeRequest;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get team members
        $teamMembers = User::where('manager_id', $user->id)->with(['assignedTasks', 'department'])->get();
        
        // Get team task statistics
        $teamTaskIds = $teamMembers->pluck('id')->toArray();
        $teamTaskIds[] = $user->id; // Include manager's own tasks
        
        $completedTasks = Task::whereIn('assigned_to', $teamTaskIds)
            ->where('status', 'completed')
            ->count();
            
        $pendingTasks = Task::whereIn('assigned_to', $teamTaskIds)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
            
        $pendingRequests = EmployeeRequest::where('manager_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // Calculate performance metrics
        $totalTasks = Task::whereIn('assigned_to', $teamTaskIds)->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        
        $onTimeTasks = Task::whereIn('assigned_to', $teamTaskIds)
            ->where('status', 'completed')
            ->where('completed_at', '<=', \DB::raw('due_date'))
            ->count();
        $onTimeRate = $completedTasks > 0 ? round(($onTimeTasks / $completedTasks) * 100, 1) : 0;
        
        // Get recent activities
        $recentActivities = collect();
        
        // Recent task completions
        $recentTaskCompletions = Task::whereIn('assigned_to', $teamTaskIds)
            ->where('status', 'completed')
            ->with('assignedTo')
            ->latest('completed_at')
            ->limit(5)
            ->get()
            ->map(function($task) {
                return (object)[
                    'type' => 'task',
                    'description' => "Task '{$task->title}' completed by {$task->assignedTo->name}",
                    'created_at' => $task->completed_at
                ];
            });
            
        // Recent requests
        $recentRequests = EmployeeRequest::where('manager_id', $user->id)
            ->with('employee')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($request) {
                return (object)[
                    'type' => 'request',
                    'description' => "New request '{$request->title}' from {$request->employee->name}",
                    'created_at' => $request->created_at
                ];
            });
            
        $recentActivities = $recentTaskCompletions->merge($recentRequests)
            ->sortByDesc('created_at')
            ->take(5);
        
        return view('manager.dashboard', compact(
            'teamMembers',
            'completedTasks',
            'pendingTasks',
            'pendingRequests',
            'completionRate',
            'onTimeRate',
            'recentActivities'
        ));
    }
    
    public function teamMembers()
    {
        $teamMembers = User::where('manager_id', Auth::id())
            ->with(['department', 'assignedTasks'])
            ->paginate(10);
            
        return view('manager.team-members', compact('teamMembers'));
    }
    
    public function teamTasks()
    {
        $teamMemberIds = User::where('manager_id', Auth::id())->pluck('id')->toArray();
        $teamMemberIds[] = Auth::id();
        
        $tasks = Task::whereIn('assigned_to', $teamMemberIds)
            ->with(['assignedTo', 'assignedBy', 'department'])
            ->latest()
            ->paginate(15);
            
        return view('manager.team-tasks', compact('tasks'));
    }
    
    public function teamRequests()
    {
        $requests = EmployeeRequest::where('manager_id', Auth::id())
            ->with(['employee', 'department'])
            ->latest()
            ->paginate(15);
            
        return view('manager.team-requests', compact('requests'));
    }
    
    public function approveRequest(Request $request, EmployeeRequest $employeeRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $employeeRequest->update([
            'status' => $request->status,
            'manager_notes' => $request->notes,
            'approved_at' => now()
        ]);
        
        $status = $request->status === 'approved' ? 'approved' : 'rejected';
        return redirect()->back()->with('success', "Request {$status} successfully.");
    }
}