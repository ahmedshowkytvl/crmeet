<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventManagementController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index()
    {
        $events = Event::with('creator')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($events);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'organizer' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'in:upcoming,ongoing,completed,cancelled',
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
            $data['image_url'] = $imagePath;
        }

        $event = Event::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'event' => $event->load('creator')
        ]);
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        return response()->json($event->load('creator', 'announcements'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'organizer' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'in:upcoming,ongoing,completed,cancelled',
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($event->image_url) {
                Storage::disk('public')->delete($event->image_url);
            }
            
            $imagePath = $request->file('image')->store('events', 'public');
            $data['image_url'] = $imagePath;
        }

        $event->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'event' => $event->load('creator')
        ]);
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        // Delete image if exists
        if ($event->image_url) {
            Storage::disk('public')->delete($event->image_url);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * Get events for dropdown (for announcements)
     */
    public function getEventsForDropdown()
    {
        $events = Event::select('id', 'title', 'date')
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($events);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Event $event)
    {
        $event->update(['is_featured' => !$event->is_featured]);

        return response()->json([
            'success' => true,
            'message' => 'Event featured status updated',
            'is_featured' => $event->is_featured
        ]);
    }
}
