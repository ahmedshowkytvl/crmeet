<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * عرض صفحة إحصائيات التقدم
     */
    public function index()
    {
        return view('task-progress.index');
    }
}
