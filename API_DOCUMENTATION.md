# Smart School LMS - API Documentation

**Version:** 1.0.0

Smart School LMS - Mobile Application API

This API powers the Smart School Learning Management System mobile application. It provides endpoints for student and parent access to:

- **Authentication** - Login, logout, password management
- **Admissions** - Online admission forms and status tracking
- **Dashboard** - Student overview with attendance percentages
- **Profile & Fees** - Student profiles and fee management
- **Attendance** - Daily and monthly attendance records
- **Exams & Marks** - Exam schedules, results, and grade reports
- **Homework** - View and submit homework assignments
- **Timetable** - Class schedules
- **Content** - Study materials and assignments
- **Notifications** - Push and in-app notifications
- **Calendar** - Events, tasks, and todo management
- **Chat** - Messaging system
- **Library** - Book catalog and issues
- **Transport** - Route and bus details
- **Hostel** - Hostel and room listings
- **Online Exams** - Take and submit online examinations
- **Syllabus** - Syllabus tracking and progress
- **Teachers** - Teacher listings and ratings
- **Video Tutorials** - Educational video content
- **Visitors** - Visitor logs
- **Leave Applications** - Apply for and manage leave
- **Timeline** - Student activity timeline

### Authentication
Most endpoints require authentication via Bearer token. Obtain a token by calling the login endpoint with valid credentials.

---

## Base URL

- `http://localhost/api`

## Authentication

All protected endpoints require a Bearer token. Include it in the Authorization header:

```
Authorization: Bearer <your-token>
```

---

## Admission

### GET `/admission` 🔓 Public

**Check if online admission is enabled**

**Response:**

```json
{
  "status": "success",
  "data": {
    "enabled": true,
    "instructions": null,
    "conditions": null,
    "amount": null,
    "payment_enabled": true
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/admission/classes` 🔓 Public

**Get list of active classes**

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": null,
  "timestamp": "string"
}
```

### GET `/admission/form_config` 🔓 Public

**Get admission form configuration (classes, categories, blood groups, etc.)**

**Response:**

```json
{
  "status": "success",
  "data": {
    "gender_list": [
      null
    ],
    "class_list": null,
    "category_list": "string",
    "blood_group_list": "string",
    "house_list": "string",
    "custom_fields": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/admission/sections` 🔓 Public

**Get sections for a given class**

**Query Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `class_id` | string | No |  |

**Response:**

```json
{
  "status": "success",
  "data": "string",
  "message": null,
  "timestamp": "string"
}
```

### GET `/admission/status` 🔓 Public

**Check admission status by reference number**

**Query Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `reference_no` | string | No |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "reference_no": "string",
    "firstname": "string",
    "lastname": "string",
    "form_status": "string",
    "paid_status": "string",
    "submitted_date": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/admission/submit` 🔓 Public

**Submit online admission form**

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `firstname` | string (max: 100) | Yes | |
| `dob` | string | Yes | |
| `class_id` | string | Yes | |
| `section_id` | string | Yes | |
| `gender` | string (Male, Female, Other) | Yes | |
| `email` | string | null | No | |
| `guardian_is` | string | null | No | |
| `guardian_name` | string | null | No | |
| `guardian_relation` | string | null | No | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "admission_id": "string",
    "reference_no": 1,
    "message": "Registration successful. Please note your reference number for further communication."
  },
  "message": "Admission form submitted successfully",
  "timestamp": "string"
}
```

---

## ApplyLeave

### GET `/apply_leave` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "results": null,
    "studentclasses": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/apply_leave/add` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `apply_date` | string | Yes | |
| `from_date` | string | Yes | |
| `to_date` | string | Yes | |
| `message` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "leave_id": "string"
  },
  "message": "Leave application submitted successfully",
  "timestamp": "string"
}
```

### GET `/apply_leave/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": null,
  "timestamp": "string"
}
```

### DELETE `/apply_leave/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Leave removed successfully",
  "timestamp": "string"
}
```

---

## Attendence

### GET `/attendence` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "attendence_type": null,
    "language": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/attendence/getAttendence` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `start` | string | No | Start date (Y-m-d). Defaults to first day of month. |
| `end` | string | No | End date (Y-m-d). Defaults to last day of month. |

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "title": null,
      "start": "string",
      "end": "string",
      "description": "string",
      "backgroundColor": "#fa8a00",
      "borderColor": "#fa8a00",
      "event_type": null
    }
  ],
  "message": null,
  "timestamp": "string"
}
```

### POST `/attendence/getdaysubattendence` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `date` | string | No | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "attendencetypeslist": null,
    "attendence": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Auth

### POST `/auth/changepass` 🔒 Requires Authentication

**Change user password**

CodeIgniter Route: POST /api/auth/changepass
Laravel Route: POST /api/auth/changepass

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `current_pass` | string | Yes | |
| `new_pass` | string | Yes | |
| `new_pass_confirmation` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Password changed successfully",
  "timestamp": "string"
}
```

### POST `/auth/login` 🔓 Public

**Login user and generate token**

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | string (max: 50) | Yes | |
| `password` | string (max: 50) | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "token": "string",
    "user": [
      null
    ]
  },
  "message": "Login successful",
  "timestamp": "string"
}
```

### POST `/auth/logout` 🔒 Requires Authentication

**Logout user and clear token**

CodeIgniter Route: POST /api/auth/logout
Laravel Route: POST /api/auth/logout

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Logged out successfully",
  "timestamp": "string"
}
```

---

## Book

### GET `/book` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Add Book",
    "title_list": "Book Details",
    "listbook": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/book/issue` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Add Book",
    "title_list": "Book Details",
    "bookList": null,
    "isCheck": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Calendar

### GET `/calendar` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "event_colors": [
      null
    ],
    "tasklist": {
      "current_page": 1,
      "data": [
        null
      ],
      "first_page_url": "string",
      "from": "integer",
      "last_page_url": "string",
      "last_page": 1,
      "links": [
        {
          "url": "string",
          "label": "string",
          "active": true
        }
      ],
      "next_page_url": "string",
      "path": "string",
      "per_page": 1,
      "prev_page_url": "string",
      "to": "integer",
      "total": 1
    },
    "title": "Event Calendar"
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/calendar/addtodo` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `task_title` | string (max: 255) | Yes | |
| `task_date` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Task created successfully",
  "timestamp": "string"
}
```

### GET `/calendar/getevents` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "title": "string",
      "start": "string",
      "end": "string",
      "description": "string",
      "id": "string",
      "backgroundColor": "string",
      "borderColor": "string",
      "event_type": "string"
    }
  ],
  "message": null,
  "timestamp": "string"
}
```

### POST `/calendar/markcomplete/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | string | No | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Marked as completed successfully",
  "timestamp": "string"
}
```

### GET `/calendar/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": null,
  "timestamp": "string"
}
```

### DELETE `/calendar/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Event deleted successfully",
  "timestamp": "string"
}
```

---

## Chat

### POST `/chat/getChatRecord` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `chat_connection_id` | integer | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "chatList": null,
    "chat_to_user": "string",
    "chat_connection_id": "string",
    "user_last_chat": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/chat/myuser` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "chat_user": null,
    "userList": [
      null
    ]
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/chat/newMessage` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `chat_connection_id` | string | Yes | |
| `chat_to_user` | string | Yes | |
| `message` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "last_insert_id": "string"
  },
  "message": "Message sent",
  "timestamp": "string"
}
```

---

## Content

### GET `/content/assignment` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title_list": "List of Assignment",
    "list": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/content/getsharelist` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "contents": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/content/list` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Downloads"
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/content/studymaterial` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title_list": "List of Study Material",
    "list": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/content/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Upload Content",
    "content": null,
    "superadmin_restriction": true
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Exam

### GET `/exam` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "class_id": "string",
    "section_id": "string",
    "examlist": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/exam/examresult` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "exam_result": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/exam/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "exam": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## ExamSchedule

### GET `/examschedule` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "examSchedule": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## General

### GET `/ping` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "message": "API is working",
  "timestamp": "string",
  "php_version": "string"
}
```

---

## Homework

### GET `/homework` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "created_by": "string",
    "evaluated_by": "string",
    "homeworklist": "string",
    "closedhomeworklist": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/homework/homework_detail/{id}/{status}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |
| `status` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "homework_status": "string",
    "homework_id": "string",
    "title": "Homework Evaluation",
    "result": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/homework/upload_docs` 🔒 Requires Authentication

**Request Body (`multipart/form-data`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `homework_id` | string | Yes | |
| `message` | string | Yes | |
| `file` | file (binary) (max: 10240) | No | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Homework submitted successfully",
  "timestamp": "string"
}
```

---

## Hostel

### GET `/hostel` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "listhostel": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## HostelRoom

### GET `/hostel/room` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "listroom": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Mark

### GET `/mark/marklist` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Student Details",
    "gradeList": null,
    "examSchedule": [
      {
        "exam_name": null,
        "exam_result": null
      }
    ],
    "student": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Notification

### GET `/notification` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "notificationlist": [
      "string"
    ]
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/notification/updatestatus` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `notification_id` | integer | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "notification": true
  },
  "message": "Status updated successfully",
  "timestamp": "string"
}
```

---

## OfflinePayment

### GET `/offlinepayment` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "student": null,
    "payment_list": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/offlinepayment/add` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `amount` | number | Yes | |
| `payment_mode` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": "string"
  },
  "message": "Payment request submitted successfully",
  "timestamp": "string"
}
```

---

## OnlineExam

### GET `/onlineexam` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "student": null,
    "examList": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/onlineexam/submit` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `onlineexam_id` | string | Yes | |
| `answers` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "result": {}
  },
  "message": "Exam submitted successfully",
  "timestamp": "string"
}
```

### GET `/onlineexam/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "result": null,
    "questions": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Route

### GET `/route` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "listroute": null
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/route/getbusdetail` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `vehrouteid` | integer | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": [
    "string"
  ],
  "message": null,
  "timestamp": "string"
}
```

---

## Subject

### GET `/subject` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "subjects": [
      {
        "id": "string",
        "name": "string",
        "type": "string",
        "code": "string"
      }
    ]
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/subject/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "subject": null
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Syllabus

### GET `/syllabus` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "this_week_start": "string",
    "this_week_end": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/syllabus/addmessage` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `syllabus_id` | string | Yes | |
| `message` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Message added successfully",
  "timestamp": "string"
}
```

### GET `/syllabus/download/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "attachment": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/syllabus/status` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "subjects_data": "string",
    "status": [
      null
    ]
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Teacher

### GET `/teacher` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "title": "Teachers",
    "teachers": "string",
    "class_id": "string",
    "section_id": "string",
    "user_id": 1,
    "role": "string",
    "teacherlist": "string",
    "genderList": [
      null
    ],
    "user_ratedstafflist": null,
    "reviews": "string",
    "comment": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### POST `/teacher/rating` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `staff_id` | string | Yes | |
| `comment` | string | Yes | |
| `rate` | number | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": null,
  "message": "Rating saved successfully",
  "timestamp": "string"
}
```

---

## Timeline

### POST `/timeline/add` 🔒 Requires Authentication

**Request Body (`application/json`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `timeline_title` | string (max: 255) | Yes | |
| `timeline_date` | string | Yes | |
| `student_id` | string | Yes | |

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": "string"
  },
  "message": "Timeline added successfully",
  "timestamp": "string"
}
```

---

## Timetable

### GET `/timetable` 🔒 Requires Authentication

---

## User

### GET `/user/dashboard` 🔒 Requires Authentication

**Get user dashboard data**

GET /api/user/dashboard

**Response:**

```json
{
  "status": "success",
  "data": {
    "attendence_percentage": 0.0,
    "studentsession_username": "string",
    "student_data": {
      "id": 1,
      "username": "string",
      "role": "string",
      "student_id": "string",
      "class": "string",
      "section": "string"
    },
    "low_attendance_limit": null
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/user/fees` 🔒 Requires Authentication

**Get user fees**

GET /api/user/fees

**Response:**

```json
{
  "status": "success",
  "data": {
    "sch_setting": null,
    "student": {
      "id": "string",
      "firstname": "string",
      "lastname": "string",
      "class": "string",
      "section": "string",
      "student_session_id": "string"
    },
    "payment_method": true
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/user/getfees` 🔒 Requires Authentication

**Get detailed fees**

GET /api/user/getfees

**Response:**

```json
{
  "status": "success",
  "data": {
    "sch_setting": null,
    "adm_auto_insert": null,
    "student": {
      "id": "string",
      "firstname": "string",
      "lastname": "string",
      "class": "string",
      "section": "string",
      "student_session_id": "string",
      "class_id": "string",
      "section_id": "string"
    },
    "payment_method": true,
    "student_due_fee": [
      "string"
    ],
    "transport_fees": [
      "string"
    ],
    "student_discount_fee": [
      "string"
    ]
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/user/profile` 🔒 Requires Authentication

**Get user profile**

GET /api/user/profile

**Response:**

```json
{
  "status": "success",
  "data": {
    "sch_setting": null,
    "student": {
      "id": "string",
      "admission_no": "string",
      "roll_no": "string",
      "firstname": "string",
      "middlename": "string",
      "lastname": "string",
      "fullname": "string",
      "gender": "string",
      "dob": "string",
      "religion": "string",
      "email": "string",
      "mobileno": "string",
      "admission_date": "string",
      "image": "string",
      "father_name": "string",
      "father_phone": "string",
      "mother_name": "string",
      "mother_phone": "string",
      "guardian_name": "string",
      "guardian_phone": "string",
      "guardian_relation": "string",
      "guardian_address": "string",
      "current_address": null,
      "category": "string",
      "class": "string",
      "section": "string",
      "student_session_id": "string",
      "class_id": "string",
      "section_id": "string"
    },
    "role": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

---

## VideoTutorial

### GET `/video_tutorial` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "student": null,
    "video_list": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

### GET `/video_tutorial/{id}` 🔒 Requires Authentication

**Body Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes |  |

**Response:**

```json
{
  "status": "success",
  "data": {
    "video": "string"
  },
  "message": null,
  "timestamp": "string"
}
```

---

## Visitor

### GET `/visitors` 🔒 Requires Authentication

**Response:**

```json
{
  "status": "success",
  "data": {
    "visitor_list": null
  },
  "message": null,
  "timestamp": "string"
}
```

---
