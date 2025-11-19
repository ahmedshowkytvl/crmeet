<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Shoutout;
use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;

class EetLifeController extends Controller
{
    /**
     * Display the EET Life dashboard
     */
    public function index()
    {
        // Get upcoming events (next 30 days)
        $upcomingEvents = Event::upcoming()
            ->where('date', '<=', now()->addDays(30))
            ->limit(6)
            ->get();

        // Get recent events (last 30 days)
        $recentEvents = Event::recent()
            ->where('date', '>=', now()->subDays(30))
            ->limit(4)
            ->get();

        // Get birthdays this month
        $birthdaysThisMonth = User::whereMonth('birth_date', now()->month)
            ->whereNotNull('birth_date')
            ->with('department')
            ->get();

        // Get employee highlights (featured employees)
        $employeeHighlights = User::where('is_featured', true)
            ->where('status', 'active')
            ->with('department')
            ->limit(3)
            ->get();

        // Get recent shoutouts
        $recentShoutouts = Shoutout::public()
            ->recent()
            ->with('user')
            ->limit(10)
            ->get();

        // Get recent announcements
        $announcements = Announcement::visibleToUser(auth()->id())
            ->recent()
            ->limit(5)
            ->get();

        return view('eet-life.index', compact(
            'upcomingEvents',
            'recentEvents', 
            'birthdaysThisMonth',
            'employeeHighlights',
            'recentShoutouts',
            'announcements'
        ));
    }

    /**
     * Store a new shoutout
     */
    public function storeShoutout(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'recipient_name' => 'nullable|string|max:100',
            'type' => 'required|in:birthday,achievement,general,thanks'
        ]);

        $shoutout = Shoutout::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'recipient_name' => $request->recipient_name,
            'type' => $request->type,
            'is_public' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shoutout posted successfully!',
            'shoutout' => $shoutout->load('user')
        ]);
    }

    /**
     * Get events data for AJAX
     */
    public function getEvents(Request $request)
    {
        $type = $request->get('type', 'upcoming');
        
        if ($type === 'upcoming') {
            $events = Event::upcoming()
                ->where('date', '<=', now()->addDays(30))
                ->limit(6)
                ->get();
        } else {
            $events = Event::recent()
                ->where('date', '>=', now()->subDays(30))
                ->limit(4)
                ->get();
        }

        return response()->json($events);
    }

    /**
     * Get shoutouts data for AJAX
     */
    public function getShoutouts()
    {
        $shoutouts = Shoutout::public()
            ->recent()
            ->with('user')
            ->limit(10)
            ->get();

        return response()->json($shoutouts);
    }
}
