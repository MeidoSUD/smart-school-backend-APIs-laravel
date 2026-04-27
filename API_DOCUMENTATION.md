# Smart School LMS API Documentation

## Overview

This documentation describes all API endpoints for the Smart School LMS mobile application (Flutter/Dart). The API is built using Laravel and provides authentication, user management, attendance, fees, homework, and other student-related features.

## Base URL

```
http://your-domain.com/api
```

## Authentication

The API uses token-based authentication with Laravel Sanctum. Include the token in the request header:

```
Authorization: Bearer YOUR_TOKEN
```

Or as a query parameter:
```
?token=YOUR_TOKEN
```

---

## Response Format

### Success Response
```json
{
    "status": "success",
    "data": { ... },
    "message": "Success message",
    "timestamp": "2024-01-15 10:30:00"
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error message",
    "timestamp": "2024-01-15 10:30:00"
}
```

---

## Public Endpoints (No Authentication Required)

### 1. Authentication

#### POST /api/auth/login
**Description:** User login with username and password

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| username | string | Yes | User's username |
| password | string | User's password (plain text) |

**Response:**
```json
{
    "status": "success",
    "data": {
        "token": "64-character-token-string",
        "user": {
            "id": 1,
            "user_id": 1,
            "username": "student001",
            "role": "student",
            "is_active": true
        }
    },
    "message": "Login successful"
}
```

**Error Responses:**
- 401: Invalid username or password
- 403: Account is disabled

---

## Protected Endpoints (Authentication Required)

### 2. Authentication

#### POST /api/auth/logout
**Description:** Logout user and invalidate token

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "message": "Logged out successfully"
}
```

#### POST /api/auth/changepass
**Description:** Change user password

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| current_pass | string | Yes | Current password |
| new_pass | string | Yes | New password |
| new_pass_confirmation | string | Yes | Confirm new password |

**Response:**
```json
{
    "status": "success",
    "message": "Password changed successfully"
}
```

---

### 3. User Management

#### GET /api/user/dashboard
**Description:** Get user dashboard data

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "attendence_percentage": 85.5,
        "studentsession_username": "student001",
        "student_data": { ... },
        "low_attendance_limit": 75
    }
}
```

#### GET /api/user/profile
**Description:** Get user profile

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "sch_setting": { ... },
        "student": {
            "id": 1,
            "admission_no": "ADM001",
            "firstname": "John",
            "lastname": "Doe",
            "class": "Class 5",
            "section": "A"
        },
        "role": "student"
    }
}
```

#### GET /api/user/fees
**Description:** Get student fees overview

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "sch_setting": { ... },
        "student": { ... },
        "payment_method": false,
        "student_due_fee": [],
        "transport_fees": []
    }
}
```

#### GET /api/user/getfees
**Description:** Get detailed fees

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "sch_setting": { ... },
        "student": { ... },
        "student_due_fee": [],
        "student_discount_fee": [],
        "transport_fees": []
    }
}
```

---

### 4. Attendance

#### GET /api/attendence
**Description:** Get attendance types

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "attendence_type": "day",
        "language": "english"
    }
}
```

#### GET /api/attendence/getAttendence
**Description:** Get attendance records for date range

**Headers:** `Authorization: Bearer TOKEN`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| start | date | No | Start date (Y-m-d), defaults to first of month |
| end | date | No | End date (Y-m-d), defaults to last of month |

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "title": "Present",
            "start": "2024-01-15",
            "end": "2024-01-15",
            "backgroundColor": "#27ab00",
            "borderColor": "#27ab00",
            "event_type": "Present"
        }
    ]
}
```

#### POST /api/attendence/getdaysubattendence
**Description:** Get attendance for specific date

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Date (Y-m-d), defaults to today |

**Response:**
```json
{
    "status": "success",
    "data": {
        "attendencetypeslist": [
            { "id": 1, "type": "Present" },
            { "id": 2, "type": "Absent" }
        ],
        "attendence": []
    }
}
```

---

### 5. Books & Library

#### GET /api/book
**Description:** Get list of books

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title": "Add Book",
        "title_list": "Book Details",
        "listbook": []
    }
}
```

#### GET /api/book/issue
**Description:** Get book issue status

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title": "Add Book",
        "title_list": "Book Details",
        "isCheck": "1",
        "bookList": []
    }
}
```

---

### 6. Calendar

#### GET /api/calendar
**Description:** Get calendar events

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "event_colors": ["#03a9f4", "#c53da9"],
        "tasklist": { ... }
    }
}
```

#### GET /api/calendar/getevents
**Description:** Get all calendar events

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "title": "Holiday",
            "start": "2024-01-26",
            "end": "2024-01-26",
            "description": "Republic Day",
            "backgroundColor": "#03a9f4",
            "event_type": "public"
        }
    ]
}
```

#### POST /api/calendar/addtodo
**Description:** Add new task

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| task_title | string | Yes | Task title |
| task_date | date | Yes | Task date |
| eventid | integer | No | Event ID for edit |

**Response:**
```json
{
    "status": "success",
    "message": "Task created successfully"
}
```

---

### 7. Chat

#### GET /api/chat/myuser
**Description:** Get chat users

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "chat_user": [],
        "userList": []
    }
}
```

#### POST /api/chat/getChatRecord
**Description:** Get chat records

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| chat_connection_id | integer | Yes | Connection ID |

**Response:**
```json
{
    "status": "success",
    "data": {
        "chatList": [],
        "chat_to_user": 0,
        "chat_connection_id": 0
    }
}
```

#### POST /api/chat/newMessage
**Description:** Send new message

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| chat_connection_id | integer | Yes | Connection ID |
| chat_to_user | integer | Yes | Recipient user ID |
| message | string | Yes | Message content |

**Response:**
```json
{
    "status": "success",
    "data": {
        "last_insert_id": 1
    },
    "message": "Message sent"
}
```

---

### 8. Content & Downloads

#### GET /api/content/list
**Description:** Get content list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title": "Downloads"
    }
}
```

#### GET /api/content/getsharelist
**Description:** Get shared content

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "contents": []
    }
}
```

#### GET /api/content/assignment
**Description:** Get assignments

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title_list": "List of Assignment",
        "list": []
    }
}
```

#### GET /api/content/studymaterial
**Description:** Get study materials

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title_list": "List of Study Material",
        "list": []
    }
}
```

---

### 9. Exams

#### GET /api/exam
**Description:** Get exam list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "class_id": 1,
        "section_id": 1,
        "examlist": []
    }
}
```

#### GET /api/exam/{id}
**Description:** Get exam details

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "exam": { ... }
    }
}
```

#### POST /api/exam/examresult
**Description:** Get exam results

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "exam_result": []
    }
}
```

#### GET /api/examschedule
**Description:** Get exam schedule

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "examSchedule": []
    }
}
```

---

### 10. Homework

#### GET /api/homework
**Description:** Get homework list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "created_by": "",
        "evaluated_by": "",
        "homeworklist": [],
        "closedhomeworklist": []
    }
}
```

#### GET /api/homework/homework_detail/{id}/{status}
**Description:** Get homework details

**Headers:** `Authorization: Bearer TOKEN`

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | Homework ID |
| status | string | Status |

**Response:**
```json
{
    "status": "success",
    "data": {
        "homework_status": "active",
        "homework_id": 1,
        "result": { ... }
    }
}
```

#### POST /api/homework/upload_docs
**Description:** Upload homework documents

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| homework_id | integer | Yes | Homework ID |
| message | string | Yes | Description message |
| file | file | No | Attachment file |

**Response:**
```json
{
    "status": "success",
    "message": "Homework submitted successfully"
}
```

---

### 11. Hostel

#### GET /api/hostel
**Description:** Get hostel list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "listhostel": []
    }
}
```

#### GET /api/hostel/room
**Description:** Get hostel rooms

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "listroom": []
    }
}
```

---

### 12. Marks

#### GET /api/mark/marklist
**Description:** Get marks list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "gradeList": [],
        "examSchedule": [],
        "student": { ... }
    }
}
```

---

### 13. Notifications

#### GET /api/notification
**Description:** Get notifications

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "notificationlist": []
    }
}
```

#### POST /api/notification/updatestatus
**Description:** Update notification status

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| notification_id | integer | Yes | Notification ID |

**Response:**
```json
{
    "status": "success",
    "data": {
        "notification": true
    },
    "message": "Status updated successfully"
}
```

---

### 14. Offline Payment

#### GET /api/offlinepayment
**Description:** Get offline payments

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "student": { ... },
        "payment_list": []
    }
}
```

#### POST /api/offlinepayment/add
**Description:** Add offline payment

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| amount | float | Yes | Payment amount |
| payment_mode | string | Yes | Payment mode |

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1
    },
    "message": "Payment request submitted successfully"
}
```

---

### 15. Online Exam

#### GET /api/onlineexam
**Description:** Get online exam list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "student": { ... },
        "examList": []
    }
}
```

#### GET /api/onlineexam/{id}
**Description:** Get exam details

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "result": { ... },
        "questions": []
    }
}
```

#### POST /api/onlineexam/submit
**Description:** Submit online exam

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| onlineexam_id | integer | Yes | Exam ID |
| answers | json | Yes | Answers JSON |

**Response:**
```json
{
    "status": "success",
    "data": {
        "result": { ... }
    },
    "message": "Exam submitted successfully"
}
```

---

### 16. Transport

#### GET /api/route
**Description:** Get transport routes

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "listroute": { ... }
    }
}
```

---

### 17. Subjects

#### GET /api/subject
**Description:** Get subject list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "subjects": []
    }
}
```

---

### 18. Syllabus

#### GET /api/syllabus
**Description:** Get syllabus

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "this_week_start": "2024-01-15",
        "this_week_end": "2024-01-21"
    }
}
```

#### GET /api/syllabus/status
**Description:** Get syllabus completion status

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "subjects_data": [],
        "status": {
            "1": "Complete",
            "0": "Incomplete"
        }
    }
}
```

---

### 19. Teachers

#### GET /api/teacher
**Description:** Get teacher list

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "title": "Teachers",
        "teachers": [],
        "teacherlist": [],
        "reviews": []
    }
}
```

#### POST /api/teacher/rating
**Description:** Rate teacher

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| staff_id | integer | Yes | Teacher ID |
| comment | string | Yes | Rating comment |
| rate | integer | Yes | Rating (1-5) |

**Response:**
```json
{
    "status": "success",
    "message": "Rating saved successfully"
}
```

---

### 20. Timeline

#### POST /api/timeline/add
**Description:** Add timeline entry

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| timeline_title | string | Yes | Title |
| timeline_date | date | Yes | Date |
| student_id | integer | Yes | Student ID |
| timeline_doc | file | No | Document |
| timeline_desc | string | No | Description |

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1
    },
    "message": "Timeline added successfully"
}
```

---

### 21. Timetable

#### GET /api/timetable
**Description:** Get student timetable

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "timetable": {
            "Monday": [
                {
                    "subject": "Mathematics",
                    "subject_code": "MATH",
                    "teacher": "Mr. John",
                    "time_from": "09:00:00",
                    "time_to": "10:00:00",
                    "room": "Room 101"
                }
            ]
        }
    }
}
```

---

### 22. Video Tutorials

#### GET /api/video_tutorial
**Description:** Get video tutorials

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "student": { ... },
        "video_list": []
    }
}
```

---

### 23. Visitors

#### GET /api/visitors
**Description:** Get visitor log

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "visitor_list": []
    }
}
```

---

### 24. Apply Leave

#### GET /api/apply_leave
**Description:** Get leave applications

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": {
        "results": [],
        "studentclasses": []
    }
}
```

#### GET /api/apply_leave/{id}
**Description:** Get leave details

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "data": { ... }
}
```

#### POST /api/apply_leave/add
**Description:** Apply for leave

**Headers:** `Authorization: Bearer TOKEN`

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| apply_date | date | Yes | Application date |
| from_date | date | Yes | Start date |
| to_date | date | Yes | End date |
| message | string | Yes | Reason |
| leave_id | integer | No | Leave ID for edit |
| files[] | files | No | Documents |

**Response:**
```json
{
    "status": "success",
    "data": {
        "leave_id": 1
    },
    "message": "Leave application submitted successfully"
}
```

#### DELETE /api/apply_leave/{id}
**Description:** Remove leave application

**Headers:** `Authorization: Bearer TOKEN`

**Response:**
```json
{
    "status": "success",
    "message": "Leave removed successfully"
}
```

---

### 25. Ping

#### GET /api/ping
**Description:** Health check endpoint (no auth required)

**Response:**
```json
{
    "status": "success",
    "message": "API is working",
    "timestamp": "2024-01-15 10:30:00",
    "php_version": "8.2.0"
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Account disabled |
| 404 | Not Found - Resource not found |
| 422 | Validation Error |
| 500 | Internal Server Error |

---

## Rate Limiting

The API is rate-limited to 60 requests per minute per IP address.

---

## Testing with Postman

### Import Collection

1. Create a new Postman collection
2. Add requests with the base URL: `http://your-domain.com/api`

### Sample Test Flow

1. **Login:**
   - POST `/api/auth/login`
   - Body: `{ "username": "student001", "password": "12345" }`

2. **Copy token** from response

3. **Test protected endpoints:**
   - Add header: `Authorization: Bearer YOUR_TOKEN`
   - GET `/api/user/dashboard`

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2024-01-15 | Initial release - All endpoints converted from CodeIgniter |