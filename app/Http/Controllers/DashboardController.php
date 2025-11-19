<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use App\Models\EmployeeRequest;
use App\Models\Comment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'total_departments' => Department::count(),
            'pending_requests' => EmployeeRequest::where('status', 'pending')->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'recent_tasks' => Task::with('assignedTo')->latest()->take(5)->get(),
            'recent_requests' => EmployeeRequest::with('employee')->latest()->take(5)->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}