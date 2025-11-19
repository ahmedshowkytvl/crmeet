<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeRequest;
use App\Models\User;

class EmployeeRequestController extends Controller
{
    public function index()
    {
        $requests = EmployeeRequest::with(['employee', 'requestedBy', 'manager'])->latest()->paginate(10);
        return view('requests.index', compact('requests'));
    }

    public function create()
    {
        $users = User::all();
        return view('requests.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'requested_by' => 'required|exists:users,id',
            'manager_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        EmployeeRequest::create($request->all());

        return redirect()->route('requests.index')->with('success', 'تم إنشاء الطلب بنجاح');
    }

    public function show(EmployeeRequest $request)
    {
        $request->load(['employee', 'requestedBy', 'manager', 'comments.user']);
        return view('requests.show', compact('request'));
    }

    public function updateStatus(Request $request, EmployeeRequest $employeeRequest)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $employeeRequest->update([
            'status' => $request->status,
        ]);

        return redirect()->route('requests.index')->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    public function destroy(EmployeeRequest $request)
    {
        $request->delete();
        return redirect()->route('requests.index')->with('success', 'تم حذف الطلب بنجاح');
    }
}
