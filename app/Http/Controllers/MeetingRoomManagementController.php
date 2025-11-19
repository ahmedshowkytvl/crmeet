<?php

namespace App\Http\Controllers;

use App\Models\MeetingRoom;
use App\Models\ScheduleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MeetingRoomManagementController extends Controller
{
    /**
     * Display the meeting rooms management page
     */
    public function index()
    {
        return view('schedule.rooms.index');
    }

    /**
     * Display room booking page
     */
    public function book($id = null)
    {
        return view('schedule.rooms.book', ['roomId' => $id]);
    }
}

