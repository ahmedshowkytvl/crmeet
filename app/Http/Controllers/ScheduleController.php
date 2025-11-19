<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class ScheduleController extends Controller
{
    /**
     * Display the schedule calendar page
     */
    public function index()
    {
        // Ensure locale is set from session if available
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        
        return view('schedule.index');
    }
}
