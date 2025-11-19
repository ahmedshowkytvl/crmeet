# Meeting Rooms API Documentation

## Overview
Complete API documentation for the Meeting Rooms Management System with booking functionality.

## Base URL
```
/api/schedule/meeting-rooms
```
or
```
/api/rooms
```

## Authentication
All endpoints require authentication via Laravel Sanctum. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. List All Rooms
**GET** `/api/schedule/meeting-rooms` or `/api/rooms`

**Query Parameters:**
- `available` (optional): Filter by availability (true/false)
- `location` (optional): Filter by location
- `min_capacity` (optional): Minimum capacity

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Conference Room A",
      "name_ar": "قاعة المؤتمرات أ",
      "description": "Large conference room",
      "capacity": 20,
      "location": "First Floor",
      "location_ar": "الطابق الأول",
      "amenities": ["projector", "whiteboard"],
      "is_available": true,
      "hourly_rate": 100.00,
      "created_by": 1,
      "creator": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ]
}
```

---

### 2. Create New Room
**POST** `/api/schedule/meeting-rooms` or `/api/rooms`

**Required Permission:** `manage-meeting-rooms`

**Request Body:**
```json
{
  "name": "Conference Room B",
  "name_ar": "قاعة المؤتمرات ب",
  "description": "Medium conference room",
  "capacity": 10,
  "location": "Second Floor",
  "location_ar": "الطابق الثاني",
  "amenities": ["projector", "video_conference"],
  "is_available": true,
  "hourly_rate": 75.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "تم إنشاء غرفة الاجتماع بنجاح",
  "data": {
    "id": 2,
    "name": "Conference Room B",
    ...
  }
}
```

---

### 3. Get Room Details
**GET** `/api/schedule/meeting-rooms/{id}` or `/api/rooms/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Conference Room A",
    ...
  }
}
```

---

### 4. Update Room
**PUT** `/api/schedule/meeting-rooms/{id}` or `/api/rooms/{id}`

**Required Permission:** `manage-meeting-rooms`

**Request Body:** (same as create, all fields optional)

**Response:**
```json
{
  "success": true,
  "message": "تم تحديث غرفة الاجتماع بنجاح",
  "data": { ... }
}
```

---

### 5. Delete Room
**DELETE** `/api/schedule/meeting-rooms/{id}` or `/api/rooms/{id}`

**Required Permission:** `manage-meeting-rooms`

**Response:**
```json
{
  "success": true,
  "message": "تم حذف غرفة الاجتماع بنجاح"
}
```

**Note:** Cannot delete rooms with upcoming events.

---

### 6. Get Available Rooms
**GET** `/api/schedule/meeting-rooms/available`

**Query Parameters:**
- `start_time` (required): Start time (ISO 8601)
- `end_time` (required): End time (ISO 8601)
- `capacity` (optional): Minimum capacity

**Example:**
```
GET /api/schedule/meeting-rooms/available?start_time=2024-01-01T10:00:00Z&end_time=2024-01-01T11:00:00Z&capacity=10
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Conference Room A",
      ...
    }
  ]
}
```

---

### 7. Get Available Time Slots
**GET** `/api/schedule/meeting-rooms/{id}/available-time-slots`

**Query Parameters:**
- `date` (required): Date (YYYY-MM-DD)
- `duration` (optional): Duration in minutes (default: 60)

**Example:**
```
GET /api/schedule/meeting-rooms/1/available-time-slots?date=2024-01-01&duration=60
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "start": "2024-01-01 09:00:00",
      "end": "2024-01-01 10:00:00"
    },
    {
      "start": "2024-01-01 10:00:00",
      "end": "2024-01-01 11:00:00"
    }
  ]
}
```

---

### 8. Book Room
**POST** `/api/schedule/meeting-rooms/{id}/book` or `/api/rooms/{id}/book`

**Request Body:**
```json
{
  "title": "Team Meeting",
  "description": "Weekly team meeting",
  "start_time": "2024-01-01T10:00:00Z",
  "end_time": "2024-01-01T11:00:00Z",
  "attendee_ids": [1, 2, 3]
}
```

**Response:**
```json
{
  "success": true,
  "message": "تم حجز الغرفة بنجاح",
  "data": {
    "event": {
      "id": 1,
      "title": "Team Meeting",
      "start_time": "2024-01-01T10:00:00Z",
      "end_time": "2024-01-01T11:00:00Z"
    },
    "room": {
      "id": 1,
      "name": "Conference Room A",
      ...
    }
  }
}
```

**Error Response (Conflict):**
```json
{
  "success": false,
  "message": "الغرفة غير متاحة في هذا الوقت"
}
```

---

### 9. Get Room Bookings
**GET** `/api/schedule/meeting-rooms/{id}/bookings`

**Query Parameters:**
- `start` (required): Start date (ISO 8601)
- `end` (required): End date (ISO 8601)

**Example:**
```
GET /api/schedule/meeting-rooms/1/bookings?start=2024-01-01T00:00:00Z&end=2024-01-31T23:59:59Z
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Team Meeting",
      "start": "2024-01-01T10:00:00Z",
      "end": "2024-01-01T11:00:00Z",
      "owner": {
        "id": 1,
        "name": "John Doe"
      },
      "attendees": [
        {
          "id": 2,
          "name": "Jane Smith"
        }
      ]
    }
  ]
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "errors": {
    "name": ["The name field is required."],
    "capacity": ["The capacity must be at least 1."]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "غير مصرح لك بإنشاء غرف الاجتماعات"
}
```

### 409 Conflict
```json
{
  "success": false,
  "message": "الغرفة غير متاحة في هذا الوقت"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "حدث خطأ أثناء حجز الغرفة",
  "error": "Error message"
}
```

---

## Features

### Double-Booking Prevention
The system automatically prevents double-booking by checking room availability before creating events. If a room is already booked for overlapping times, the booking will be rejected with a 409 Conflict response.

### Availability Status
Rooms can have an `is_available` status that can be toggled by admins. Even if a room has no bookings, it cannot be booked if `is_available` is set to `false`.

### Capacity Filtering
You can filter rooms by minimum capacity when searching for available rooms.

### Time Slot Availability
The system provides available time slots for a specific room on a specific date, making it easy to find suitable booking times.

---

## Example Usage

### Create a Room
```bash
curl -X POST http://your-domain/api/schedule/meeting-rooms \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Conference Room A",
    "capacity": 20,
    "location": "First Floor",
    "is_available": true
  }'
```

### Book a Room
```bash
curl -X POST http://your-domain/api/schedule/meeting-rooms/1/book \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Team Meeting",
    "start_time": "2024-01-01T10:00:00Z",
    "end_time": "2024-01-01T11:00:00Z",
    "attendee_ids": [1, 2, 3]
  }'
```

### Get Available Rooms
```bash
curl -X GET "http://your-domain/api/schedule/meeting-rooms/available?start_time=2024-01-01T10:00:00Z&end_time=2024-01-01T11:00:00Z" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## UI Pages

### Room Management
- **URL:** `/schedule/rooms`
- **Description:** Admin page to create, update, and delete meeting rooms
- **Access:** Requires `manage-meeting-rooms` permission

### Room Booking
- **URL:** `/schedule/rooms/book` or `/schedule/rooms/book/{id}`
- **Description:** User-friendly interface to book meeting rooms
- **Access:** All authenticated users

### Schedule Calendar
- **URL:** `/schedule`
- **Description:** Calendar view showing all events and room bookings
- **Access:** All authenticated users

---

## Notes

- All dates and times should be in ISO 8601 format
- The system supports timezone conversion
- Room bookings create events in the schedule system
- Deleted rooms cannot have upcoming events
- Room availability is checked in real-time before booking

