<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleEvent;

class MeetingRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // User should be authenticated via middleware (web or sanctum)
        // If not authenticated, middleware will handle it
        $query = MeetingRoom::with('creator');
        
        // Filter available rooms
        if ($request->has('available') && $request->available == 'true') {
            $query->available();
        }
        
        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        // Filter by minimum capacity
        if ($request->has('min_capacity')) {
            $query->where('capacity', '>=', $request->min_capacity);
        }
        
        $rooms = $query->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'name_ar' => $room->name_ar,
                'description' => $room->description,
                'description_ar' => $room->description_ar,
                'capacity' => $room->capacity,
                'location' => $room->location,
                'location_ar' => $room->location_ar,
                'amenities' => $room->amenities,
                'is_available' => $room->is_available,
                'image' => $room->image,
                'hourly_rate' => $room->hourly_rate,
                'created_by' => $room->created_by,
                'creator' => $room->creator ? [
                    'id' => $room->creator->id,
                    'name' => $room->creator->name,
                ] : null,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $rooms->values(),
            'count' => $rooms->count(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }
        
        // Check authorization
        try {
            if (!$user->hasPermission('manage-meeting-rooms')) {
                // Fallback check
                if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بإنشاء غرف الاجتماعات',
                    ], 403);
                }
            }
        } catch (\Exception $e) {
            // Fallback: only allow admin
            if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإنشاء غرف الاجتماعات',
                ], 403);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'is_available' => 'nullable|boolean',
            'availability_schedule' => 'nullable|array',
            'image' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $room = MeetingRoom::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'location_ar' => $request->location_ar,
            'amenities' => $request->amenities,
            'is_available' => $request->is_available ?? true,
            'availability_schedule' => $request->availability_schedule,
            'image' => $request->image,
            'hourly_rate' => $request->hourly_rate,
            'created_by' => $user->id,
        ]);
        
        $room->load('creator');
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء غرفة الاجتماع بنجاح',
            'data' => $this->formatRoom($room),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $room = MeetingRoom::with('creator')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $this->formatRoom($room),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }
        
        $room = MeetingRoom::findOrFail($id);
        
        // Check authorization
        try {
            if (!$user->hasPermission('manage-meeting-rooms')) {
                if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بتعديل غرف الاجتماعات',
                    ], 403);
                }
            }
        } catch (\Exception $e) {
            if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتعديل غرف الاجتماعات',
                ], 403);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'capacity' => 'sometimes|required|integer|min:1',
            'location' => 'sometimes|required|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'is_available' => 'nullable|boolean',
            'availability_schedule' => 'nullable|array',
            'image' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $room->update($request->only([
            'name', 'name_ar', 'description', 'description_ar', 'capacity',
            'location', 'location_ar', 'amenities', 'is_available',
            'availability_schedule', 'image', 'hourly_rate'
        ]));
        
        $room->load('creator');
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث غرفة الاجتماع بنجاح',
            'data' => $this->formatRoom($room),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً',
            ], 401);
        }
        
        $room = MeetingRoom::findOrFail($id);
        
        // Check authorization
        try {
            if (!$user->hasPermission('manage-meeting-rooms')) {
                if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غير مصرح لك بحذف غرف الاجتماعات',
                    ], 403);
                }
            }
        } catch (\Exception $e) {
            if ($user->user_type !== 'admin' && $user->role_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف غرف الاجتماعات',
                ], 403);
            }
        }
        
        // Check if room has upcoming events
        $upcomingEvents = $room->events()
            ->where('start_time', '>=', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();
        
        if ($upcomingEvents > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الغرفة لأن لديها أحداث قادمة',
            ], 409);
        }
        
        $room->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف غرفة الاجتماع بنجاح',
        ]);
    }

    /**
     * Get available rooms for a specific time slot
     */
    public function available(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'capacity' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        
        $query = MeetingRoom::available();
        
        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }
        
        $rooms = $query->get()->filter(function ($room) use ($startTime, $endTime) {
            return $room->isAvailableFor($startTime, $endTime);
        })->map(function ($room) {
            return $this->formatRoom($room);
        });
        
        return response()->json([
            'success' => true,
            'data' => $rooms->values(),
        ]);
    }

    /**
     * Get available time slots for a room
     */
    public function availableTimeSlots(Request $request, string $id)
    {
        $room = MeetingRoom::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'duration' => 'nullable|integer|min:15',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $date = Carbon::parse($request->date);
        $duration = $request->duration ?? 60;
        
        $slots = $room->getAvailableTimeSlots($date, $duration);
        
        return response()->json([
            'success' => true,
            'data' => $slots,
        ]);
    }

    /**
     * Book a room for a specific time slot
     */
    public function book(Request $request, string $id)
    {
        $user = Auth::user();
        $room = MeetingRoom::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        
        // Check if room is available for the time slot
        if (!$room->isAvailableFor($startTime, $endTime)) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'الغرفة غير متاحة في هذا الوقت' : 'Room is not available at this time',
            ], 409);
        }
        
        // Check if room is active
        if (!$room->is_available) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'الغرفة غير متاحة للحجز' : 'Room is not available for booking',
            ], 409);
        }
        
        DB::beginTransaction();
        try {
            // Create event for room booking
            $event = ScheduleEvent::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'timezone' => $user->timezone ?? 'UTC',
                'event_type' => 'meeting',
                'meeting_room_id' => $room->id,
                'user_id' => $user->id,
                'status' => 'scheduled',
                'priority' => 'medium',
                'color' => '#3788d8',
            ]);
            
            // Add attendees if provided
            if ($request->attendee_ids && is_array($request->attendee_ids)) {
                $attendees = [];
                foreach ($request->attendee_ids as $attendeeId) {
                    $attendees[$attendeeId] = [
                        'role' => 'attendee',
                        'rsvp_status' => 'pending',
                    ];
                }
                $event->attendees()->attach($attendees);
            }
            
            DB::commit();
            
            $event->load(['user', 'meetingRoom', 'attendees']);
            
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() === 'ar' ? 'تم حجز الغرفة بنجاح' : 'Room booked successfully',
                'data' => [
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_time' => $event->start_time->toIso8601String(),
                        'end_time' => $event->end_time->toIso8601String(),
                    ],
                    'room' => $this->formatRoom($room),
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حجز الغرفة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get room bookings for a specific date range
     */
    public function bookings(Request $request, string $id)
    {
        $room = MeetingRoom::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        
        $bookings = $room->events()
            ->whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['user', 'attendees'])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_time->toIso8601String(),
                    'end' => $event->end_time->toIso8601String(),
                    'owner' => [
                        'id' => $event->user->id,
                        'name' => $event->user->name,
                    ],
                    'attendees' => $event->attendees->map(function ($attendee) {
                        return [
                            'id' => $attendee->id,
                            'name' => $attendee->name,
                        ];
                    }),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Format room for response
     */
    private function formatRoom($room)
    {
        return [
            'id' => $room->id,
            'name' => $room->name,
            'name_ar' => $room->name_ar,
            'description' => $room->description,
            'description_ar' => $room->description_ar,
            'capacity' => $room->capacity,
            'location' => $room->location,
            'location_ar' => $room->location_ar,
            'amenities' => $room->amenities,
            'is_available' => $room->is_available,
            'availability_schedule' => $room->availability_schedule,
            'image' => $room->image,
            'hourly_rate' => $room->hourly_rate,
            'created_by' => $room->created_by,
            'creator' => $room->creator ? [
                'id' => $room->creator->id,
                'name' => $room->creator->name,
            ] : null,
            'created_at' => $room->created_at->toIso8601String(),
            'updated_at' => $room->updated_at->toIso8601String(),
        ];
    }
}
