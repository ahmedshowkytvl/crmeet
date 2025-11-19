<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrgChartController extends Controller
{
    /**
     * Display the organizational chart page
     */
    public function index()
    {
        return view('org-chart');
    }
}
