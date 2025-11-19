<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduleEvent;
use App\Models\MeetingRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = ScheduleEvent::with(['user', 'meetingRoom', 'attendees'])
            ->forUser($user->id);
        
        // Filter by date range
        if ($request->has('start') && $request->has('end')) {
            $start = Carbon::parse($request->start)->startOfDay();
            $end = Carbon::parse($request->end)->endOfDay();
            $query->inDateRange($start, $end);
        }
        
        // Filter by event type
        if ($request->has('event_type')) {
            $query->ofType($request->event_type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start' => $event->start_time->toIso8601String(),
                'end' => $event->end_time->toIso8601String(),
                'timezone' => $event->timezone,
                'location' => $event->location,
                'event_type' => $event->event_type,
                'status' => $event->status,
                'priority' => $event->priority,
                'color' => $event->color,
                'is_recurring' => $event->is_recurring,
                'recurring_pattern' => $event->recurring_pattern,
                'meeting_room' => $event->meetingRoom ? [
                    'id' => $event->meetingRoom->id,
                    'name' => $event->meetingRoom->name,
                    'location' => $event->meetingRoom->location,
                ] : null,
                'owner' => [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                ],
                'attendees' => $event->attendees->map(function ($attendee) {
                    return [
                        'id' => $attendee->id,
                        'name' => $attendee->name,
                        'role' => $attendee->pivot->role,
                        'rsvp_status' => $attendee->pivot->rsvp_status,
                    ];
                }),
                'can_edit' => $event->canEdit($user->id),
                'can_delete' => $event->isOwner($user->id),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'event_type' => 'nullable|in:meeting,event,reminder,task',
            'priority' => 'nullable|in:low,medium,high',
            'color' => 'nullable|string|max:7',
            'meeting_room_id' => 'nullable|exists:meeting_rooms,id',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
            'is_recurring' => 'nullable|boolean',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'recurring_rules' => 'nullable|array',
            'recurring_end_date' => 'nullable|date',
            'reminders' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        DB::beginTransaction();
        try {
            // Check meeting room availability if provided
            if ($request->meeting_room_id) {
                $room = MeetingRoom::findOrFail($request->meeting_room_id);
                $startTime = Carbon::parse($request->start_time);
                $endTime = Carbon::parse($request->end_time);
                
                if (!$room->isAvailableFor($startTime, $endTime)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'غرفة الاجتماع غير متاحة في هذا الوقت',
                    ], 409);
                }
            }
            
            $event = ScheduleEvent::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_time' => Carbon::parse($request->start_time),
                'end_time' => Carbon::parse($request->end_time),
                'timezone' => $request->timezone ?? $user->timezone ?? 'UTC',
                'location' => $request->location,
                'event_type' => $request->event_type ?? 'event',
                'priority' => $request->priority ?? 'medium',
                'color' => $request->color ?? '#3788d8',
                'meeting_room_id' => $request->meeting_room_id,
                'user_id' => $user->id,
                'is_recurring' => $request->is_recurring ?? false,
                'recurring_pattern' => $request->recurring_pattern,
                'recurring_rules' => $request->recurring_rules,
                'recurring_end_date' => $request->recurring_end_date ? Carbon::parse($request->recurring_end_date) : null,
                'reminders' => $request->reminders,
                'status' => 'scheduled',
            ]);
            
            // Add attendees
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
            
            // Generate recurring events if needed
            if ($event->is_recurring && $event->recurring_pattern) {
                $event->generateRecurringEvents($event->recurring_end_date);
            }
            
            DB::commit();
            
            $event->load(['user', 'meetingRoom', 'attendees']);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحدث بنجاح',
                'data' => $this->formatEvent($event),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحدث',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $event = ScheduleEvent::with(['user', 'meetingRoom', 'attendees'])->findOrFail($id);
        
        // Check authorization
        if (!$event->user_id === $user->id && !$event->isAttendee($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى هذا الحدث',
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $this->formatEvent($event),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $event = ScheduleEvent::findOrFail($id);
        
        // Check authorization
        if (!$event->canEdit($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا الحدث',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'event_type' => 'nullable|in:meeting,event,reminder,task',
            'status' => 'nullable|in:scheduled,confirmed,cancelled,completed',
            'priority' => 'nullable|in:low,medium,high',
            'color' => 'nullable|string|max:7',
            'meeting_room_id' => 'nullable|exists:meeting_rooms,id',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
            'reminders' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        DB::beginTransaction();
        try {
            // Check meeting room availability if changed
            if ($request->has('meeting_room_id') || $request->has('start_time') || $request->has('end_time')) {
                $roomId = $request->meeting_room_id ?? $event->meeting_room_id;
                $startTime = $request->start_time ? Carbon::parse($request->start_time) : $event->start_time;
                $endTime = $request->end_time ? Carbon::parse($request->end_time) : $event->end_time;
                
                if ($roomId) {
                    $room = MeetingRoom::findOrFail($roomId);
                    if (!$room->isAvailableFor($startTime, $endTime, $event->id)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'غرفة الاجتماع غير متاحة في هذا الوقت',
                        ], 409);
                    }
                }
            }
            
            $event->update($request->only([
                'title', 'description', 'start_time', 'end_time', 'timezone',
                'location', 'event_type', 'status', 'priority', 'color',
                'meeting_room_id', 'reminders'
            ]));
            
            // Update attendees if provided
            if ($request->has('attendee_ids')) {
                $event->attendees()->detach();
                if (is_array($request->attendee_ids) && count($request->attendee_ids) > 0) {
                    $attendees = [];
                    foreach ($request->attendee_ids as $attendeeId) {
                        $attendees[$attendeeId] = [
                            'role' => 'attendee',
                            'rsvp_status' => 'pending',
                        ];
                    }
                    $event->attendees()->attach($attendees);
                }
            }
            
            DB::commit();
            
            $event->load(['user', 'meetingRoom', 'attendees']);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحدث بنجاح',
                'data' => $this->formatEvent($event),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحدث',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $event = ScheduleEvent::findOrFail($id);
        
        // Check authorization
        if (!$event->isOwner($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الحدث',
            ], 403);
        }
        
        $event->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف الحدث بنجاح',
        ]);
    }

    /**
     * RSVP to an event
     */
    public function rsvp(Request $request, string $id)
    {
        $user = Auth::user();
        $event = ScheduleEvent::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'rsvp_status' => 'required|in:accepted,declined,tentative',
            'response_note' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        if (!$event->isAttendee($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'أنت لست مدعوًا إلى هذا الحدث',
            ], 403);
        }
        
        $event->attendees()->updateExistingPivot($user->id, [
            'rsvp_status' => $request->rsvp_status,
            'response_note' => $request->response_note,
            'responded_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الحضور بنجاح',
        ]);
    }

    /**
     * Format event for response
     */
    private function formatEvent($event)
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'start' => $event->start_time->toIso8601String(),
            'end' => $event->end_time->toIso8601String(),
            'timezone' => $event->timezone,
            'location' => $event->location,
            'event_type' => $event->event_type,
            'status' => $event->status,
            'priority' => $event->priority,
            'color' => $event->color,
            'is_recurring' => $event->is_recurring,
            'recurring_pattern' => $event->recurring_pattern,
            'recurring_rules' => $event->recurring_rules,
            'meeting_room' => $event->meetingRoom ? [
                'id' => $event->meetingRoom->id,
                'name' => $event->meetingRoom->name,
                'name_ar' => $event->meetingRoom->name_ar,
                'location' => $event->meetingRoom->location,
                'capacity' => $event->meetingRoom->capacity,
            ] : null,
            'owner' => [
                'id' => $event->user->id,
                'name' => $event->user->name,
                'email' => $event->user->email,
            ],
            'attendees' => $event->attendees->map(function ($attendee) {
                return [
                    'id' => $attendee->id,
                    'name' => $attendee->name,
                    'email' => $attendee->email,
                    'role' => $attendee->pivot->role,
                    'rsvp_status' => $attendee->pivot->rsvp_status,
                    'responded_at' => $attendee->pivot->responded_at,
                ];
            }),
            'reminders' => $event->reminders,
            'created_at' => $event->created_at->toIso8601String(),
            'updated_at' => $event->updated_at->toIso8601String(),
        ];
    }
}
