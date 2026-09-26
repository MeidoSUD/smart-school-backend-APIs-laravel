# Smart School — Student Endpoints (Source-of-Truth for Laravel Mobile API)

**Source:** CodeIgniter student controllers in `codelignter/student cotroller/`
**Purpose:** Reference for the agent to rebuild these as Laravel REST APIs and integrate with the Flutter mobile app.

---
## 0. ACTUAL LARAVEL ROUTES (implemented — use these, NOT the logical paths below)

> All protected routes require `Authorization: Bearer <sanctum token>` (from `POST /api/auth/login`). Base URL: `<app>/api`.

| Feature | Method | Actual route | Notes / payload |
|---|---|---|---|
| Auth login | POST | `/api/auth/login` | `{username, password}` → `{token, user}` |
| Logout | POST | `/api/auth/logout` | |
| Change password | POST | `/api/auth/changepass` | `{current_password, new_password, confirm_password}` |
| Dashboard | GET | `/api/user/dashboard` | attendance %, books, homework, notifications, syllabus %, timetable, visitors, teachers |
| Profile | GET | `/api/user/profile` | student, fees, discount, timeline, exam results, grades, docs, attendance map, behaviour points, CBSE exam list |
| Multi-class list/select | GET/POST | `/api/user/choose` | GET lists `student_lists`; POST `{clschg: student_session_id}` |
| Semi-annual fees | GET | `/api/user/fees` · `/api/user/getfees` | due fees, transport fees, discount, processing fee |
| My documents | GET/POST | `/api/user/documents` · `POST /api/user/documents` | GET list; POST multipart `{first_title, first_doc}` |
| Download doc | GET | `/api/user/documents/download/{id}` | returns the actual file |
| Change username | POST | `/api/user/changeusername` | `{current_username, new_username, confirm_username}` |
| Set language | POST | `/api/user/language` | `{lang_id}` |
| Set currency | POST | `/api/user/currency` | `{currency_id}` |
| Exams list | GET | `/api/exam` | this class/section exams |
| Exam result | POST | `/api/exam/examresult` | `{student_session_id, exam_id}` |
| Exam detail | GET | `/api/exam/{id}` | |
| Exam schedule | GET | `/api/examschedule` | |
| Exam schedule subjects | POST | `/api/examschedule/getexamscheduledetail` | `{exam_id}` |
| Marks list | GET | `/api/mark/marklist` | per-exam subject marks + gradeList |
| Online exams | GET | `/api/onlineexam` | available |
| Online exam closed | GET | `/api/onlineexam/closed` | closed/expired |
| Online exam detail | GET | `/api/onlineexam/{id}` | |
| Start exam | POST | `/api/onlineexam/startexam` | `{examid}` |
| Submit exam | POST | `/api/onlineexam/submit` | answers payload |
| Attachment download | GET | `/api/onlineexam/downloadattachment/{doc}` | returns file |
| My subjects | GET | `/api/subject` · `/api/subject/{id}` | |
| Syllabus week dates | POST | `/api/syllabus/get_weekdates` | `{date}` |
| Syllabus progress | GET | `/api/syllabus/status` | subjects + lesson/topic completion % |
| Syllabus lesson detail | GET/POST | `/api/syllabus/get_subject_syllabus` / `/api/syllabus/get_subject_syllabus/{id}` | POST `{subject_syllabus_id}` |
| Syllabus check slot | POST | `/api/syllabus/check_subject_syllabus` | `{subject_group_subject_id, date, time_from, time_to, subject_group_class_section_id}` |
| Syllabus attachment | GET | `/api/syllabus/download/{id}` | returns file |
| Lecture video | GET | `/api/syllabus/lacture_video_download/{id}` | returns file |
| Syllabus comments | POST | `/api/syllabus/addmessage` · `/api/syllabus/getmessage` · `/api/syllabus/deletemessage` | add/get `subject_syllabus_id`+`message`; delete `fourm_id` |
| Study material | GET | `/api/content/studymaterial` | same pattern: `/assignment`, `/syllabus`, `/other` |
| Content download | GET | `/api/content/download/{file}` · `/api/content/list` · `/api/content/{id}` | |
| Timeline | GET/POST | `/api/timeline` | GET list; POST `{timeline_title, timeline_date, student_id, timeline_doc?}` |
| Timeline one/update/del | GET/PUT/DELETE | `/api/timeline/{id}` | PUT `{timeline_title, timeline_date, timeline_desc, timeline_doc?}` |
| Timeline download | GET | `/api/timeline/download/{id}` | returns file |
| Homework list | GET | `/api/homework` | open + closed |
| Homework detail | GET | `/api/homework/homework_detail/{id}/{status}` | |
| Submit homework | POST | `/api/homework/upload_docs` | multipart `{homework_id, message, file}` |
| Homework doc download | GET | `/api/homework/download/{id}` | returns teacher doc |
| My submission download | GET | `/api/homework/assigmnetDownload/{id}` | returns my file |
| Daily assignments | GET/POST | `/api/homework/dailyassignment` / `/api/homework/createdailyassignment` | POST `{title, subject, description, file?}` |
| Daily assignment one/upd/del | GET/PUT/DELETE | `/api/homework/getsingle_dailyassignment/{id}` · `/api/homework/updatedailyassignment` · `/api/homework/deletedailyassignment/{id}` | |
| Daily assignment file | GET | `/api/homework/dailyassigmnetdownload/{id}` | returns file |
| Attendance list | GET | `/api/attendence` · `/api/attendence/getAttendence?start=&end=` | |
| Attendance by date | POST | `/api/attendence/getdaysubattendence` | `{date}` |
| Notifications | GET | `/api/notification` | |
| Notification detail | GET | `/api/notification/{id}` | |
| Mark read / seen | POST | `/api/notification/read` (`{notice}`) · `/api/notification/updatestatus` (`{notification_id}`) | |
| Notification file | GET | `/api/notification/download/{id}` | returns file |
| Timetable | GET | `/api/timetable` | |
| Calendar events | GET | `/api/calendar` · `/api/calendar/getevents` | |
| Calendar tasks | POST | `/api/calendar/addtodo` · GET `/api/calendar/{id}` · POST `/api/calendar/markcomplete/{id}` · DELETE `/api/calendar/{id}` | |
| Chat contacts | GET | `/api/chat/myuser` · `/api/chat/mynewuser`(POST) · `/api/chat/searchuser`(POST `{keyword}`) · `/api/chat/adduser`(POST `{user_id,user_type}`) | |
| Chat messages | GET/POST | `/api/chat/getChatRecord`(POST) · `/api/chat/newMessage`(POST) · `/api/chat/chatUpdate`(POST) · `/api/chat/mychatnotification`(GET) | |
| Leave applications | GET/POST | `/api/apply_leave` · `/api/apply_leave/add` · GET/POST/DELETE `/api/apply_leave/{id}` · GET `/api/apply_leave/download/{id}` | (also under `/api/user/apply_leave/*`) |
| Library books | GET | `/api/book` · `/api/book/issue` | |
| Transport | GET/POST | `/api/route` · `/api/route/getbusdetail` | |
| Hostel | GET | `/api/hostel` · `/api/hostel/room` | |
| Teachers + rate | GET/POST | `/api/teacher` · `/api/teacher/rating` | rating `{staff_id, comment, rate}` |
| Behaviour | GET | `/api/behaviour` | `{incidents, total_points, behavioursetting, role}` |
| Behaviour comments | POST | `/api/behaviour/addmessage` (`{student_incident_id, comment}`) · `/api/behaviour/getmessage` (`{student_incident_id}`) · `/api/behaviour/delete_comment` (`{id}`) | |
| Student admission (public) | GET/POST | `/api/admission/*` | |
| Health | GET | `/api/ping` | |
| Marks (CI `Mark::index`) | GET | `/api/mark` | nested examlist/classlist/feecategorylist/examSchedule |
| Mark detail (CI `Mark::view`) | GET | `/api/mark/{id}` | |
| Attendance calendar events (CI `Attendence::getevents`) | GET | `/api/attendence/getevents` | |
| Fee types by category (CI `Exam::getByFeecategory`) | GET | `/api/exam/getByFeecategory` | `?feecategory_id=` |
| Category fee (CI `Exam::getStudentCategoryFee`) | POST | `/api/exam/getStudentCategoryFee` | `{type, class_id}` |
| Exam search (CI `Exam::examSearch`) | GET/POST | `/api/exam/examSearch` | POST `{search, date_from, date_to}` or `{search_text}` |
| Subjects by class/section | GET/POST | `/api/subject/getSubjctByClassandSection` | `{class_id, section_id}` |
| Teacher subjects/teachers | POST | `/api/teacher/getSubjctByClassandSection` · `/api/teacher/getSubjectTeachers` | `{class_id, section_id}` |
| Teacher detail | GET | `/api/teacher/{id}` | |
| Visitor file | GET | `/api/visitors/download/{id}` | returns file |
| Chat landing | GET | `/api/chat` | `{title}` |
| Content index | GET | `/api/content` | `{title, title_list, list, ght, classlist}` |
| Timeline download alias (CI `user/timeline_download`) | GET | `/api/user/timeline_download/{id}/{doc?}` | same file as `/api/timeline/download/{id}` |

> **File downloads.** All `download*` endpoints return the actual file (`response()->download`). Student-uploaded files live in private storage (`storage/app/uploads/...`); staff/school-uploaded files live in `public/uploads/...`; the API resolves both. The old JSON-filename-only responses have been replaced.

---

> **T-3.7 CORRECTION NOTE (2026-09-25).** Sections 2–20 below use *logical* `/api/student/...` paths from the original migration draft — **those paths do NOT exist**. The real routes are ONLY the ones in Section 0 above. Logical → actual mapping for the most-cited ones:
> | Logical path in §§2–20 | Actual route (§0) |
> |---|---|
> | `GET /api/student/exams` | `GET /api/exam` |
> | `GET /api/student/exam-results` | `POST /api/exam/examresult` |
> | `GET /api/student/exam-schedule` | `GET /api/examschedule` |
> | `GET /api/student/marks` | `GET /api/mark/marklist` |
> | `GET /api/student/subjects` | `GET /api/subject` |
> | `GET /api/student/syllabus...` | `GET /api/syllabus`, `POST /api/syllabus/get_weekdates`, `GET /api/syllabus/status` |
> | `GET /api/student/materials` | `GET /api/content/studymaterial` (+ `/assignment`, `/syllabus`, `/other`) |
> | `GET /api/student/timeline` | `GET /api/timeline` |
> | `GET /api/student/homework` | `GET /api/homework` |
> | `GET /api/student/attendance` | `GET /api/attendence/getAttendence` |
> | `GET /api/student/notifications` | `GET /api/notification` |
> | `GET /api/student/timetable` | `GET /api/timetable` |
> | `GET /api/student/calendar/...` | `/api/calendar`, `/api/calendar/getevents` |
> | `GET /api/student/chat/...` | `/api/chat/myuser`, `/api/chat/getChatRecord`, ... |
> | `GET /api/student/leave-applications` | `GET /api/apply_leave` |
> | `GET /api/student/fees` | `GET /api/user/getfees` (+ `/api/user/fees`) |
> | `GET /api/student/books` | `GET /api/book` |
> | `GET /api/student/online-exams` | `GET /api/onlineexam` |
> | `GET /api/student/transport` | `GET /api/route` |
> | `GET /api/student/hostels` | `GET /api/hostel` |
> | `GET /api/student/teachers` | `GET /api/teacher` |
> | `GET /api/student/dashboard` / `profile` | `GET /api/user/dashboard` / `GET /api/user/profile` |

## 1. How the CI app works (important for the conversion)

- **No REST API.** Every method below is a web page (renders a view) or an AJAX JSON endpoint.
- **Auth:** CI uses `Student_Controller` which requires a logged-in session. The Laravel API must replace this with a **Bearer token** middleware.
- **Who am I?** Almost every endpoint needs:
  - `student_id` → `customlib->getStudentSessionUserID()`
  - current class/section → `customlib->getStudentCurrentClsSection()` => `{class_id, section_id, student_session_id}`
  - role (`student` or `parent`) → `customlib->getUserRole()`
- **Dates:** CI stores `Y-m-d` internally but formats per school setting. Laravel should accept `d-m-Y` and convert, or accept `Y-m-d` plainly for mobile.
- **Response convention (AJAX):** `{status: "success"|"fail", error: {field: msg}, message: "..."}` or `{status: 1, page: "<html>"}`. For mobile, drop the HTML `page` and return JSON data instead.

---

## 2. IMPORTANT — Exams

### 2.1 Exam List (Exams for my class/section)
| | |
|---|---|
| Source controller/method | `student/Exam.php::index()` |
| CI URL | `user/exam/index` |
| Mobile method | **GET** `/api/student/exams` (auth) |
| Logic | Uses current student's `class_id` + `section_id`, calls `examschedule_model->getExamByClassandSection(class_id, section_id)` |
| Response data | `examlist: [{id, name, ...}]` |

### 2.2 Exam Result / Marks
| | |
|---|---|
| Source | `student/Exam.php::examresult()` |
| Mobile method | **GET** `/api/student/exam-results` |
| Logic | `examgroupstudent_model->searchStudentExams(student_session_id, true, true)` |
| Extra data | `marks_division`, `exam_grade` (grade ranges for converting marks → grade) |

### 2.3 Exam Schedule
| | |
|---|---|
| Source | `student/Examschedule.php::index()` |
| Mobile method | **GET** `/api/student/exam-schedule` |
| Logic | `examgroupstudent_model->studentExams(student_session_id)` |
| Source detail | `Examschedule.php::getexamscheduledetail()` (POST `exam_id` → subjects for that exam). Mobile → **GET** `/api/student/exam-schedule/{exam_id}/subjects` |

### 2.4 Marks List (per exam → subject marks)
| | |
|---|---|
| Source | `student/Mark.php::marklist()` — this is the real mobile one. The `index()` loops all students (admin view; skip for mobile). |
| Mobile method | **GET** `/api/student/marks` |
| Logic | For each exam of the student's class/section, `examschedule_model->getresultByStudentandExam(exam_id, student_id)` |
| Response shape | `examSchedule: [ { exam_name, exam_result: [ { exam_schedule_id, exam_id, full_marks, passing_marks, exam_name, exam_type, attendence, get_marks } ] } ]`, plus `gradeList` |

### 2.5 CBSE Exams (optional module)
| | |
|---|---|
| Source | `student/cbse/Exam.php` |
| | |
| Method | `timetable()` |
| Mobile method | **GET** `/api/student/cbse-exams/timetable` |
| Logic | `cbseexam_exam_model->getStudentExamTimetable(student_session_id)` |
| | |
| Method | `result()` |
| Mobile method | **GET** `/api/student/cbse-exams/results` |
| Logic | `getStudentExamByStudentSession(student_session_id)`; each exam gets `subjects`, `grades`, `exam_assessments`, `exam_subject_assessments`, `rank`, and `exam_data` (subjects → assessments → marks). |
| Response notes | Heavy nested object. `cbse_student_subject_marks_id`, `maximum_marks`, `marks`, `note`, `is_absent` per assessment type. |

---

## 3. IMPORTANT — Lessons / Syllabus

### 3.1 My Subjects
| | |
|---|---|
| Source | `student/Subject.php::index()` |
| Mobile method | **GET** `/api/student/subjects` |
| Logic | `teachersubject_model->getSubjectByClsandSection(class_id, section_id)` |
| Response | `subjectlist: [{id, name, code, type}]` |

### 3.2 Weekly Syllabus / Lesson Plan
| | |
|---|---|
| Source | `student/Syllabus.php::index()` / `get_weekdates()` |
| Mobile method | **GET** `/api/student/syllabus?week_start=YYYY-MM-DD` |
| Logic | `syllabus_model->get_studentsyllabus(student_current_class)`; week navigation via `date` param |
| Source detail | `get_subject_syllabus()` and `check_subject_syllabus(subject_group_subject_id, date, time_from, time_to, subject_group_class_section_id)` → lesson/topic rows for a subject+slot |
| Mobile method (detail) | **GET** `/api/student/syllabus/subject/{subject_group_subject_id}?date=&time_from=&time_to=` |

### 3.3 Syllabus Progress/Status (per subject with lessons + topics + completion %)
| | |
|---|---|
| Source | `student/Syllabus.php::status()` |
| Mobile method | **GET** `/api/student/syllabus/status` |
| Logic | `getmysubjects(class_id, section_id)`; per subject `get_subjectstatus(...)` → complete/incomplete %; `get_subjectsyllabussreport(...)` → lessons; `get_topicbylessonid(lesson_id)` → topics with `status` (1=complete) + `complete_date` |
| Response shape | `subjects_data: { [subject_group_subjects_id]: { lebel, complete, incomplete, id, total, name, lesson_summary: [ { name, topics: [{name,status,complete_date}], incomplete_percent, complete_percent } ] } }` |

### 3.4 Syllabus Discussion (comments on a topic)
| | |
|---|---|
| Source | `student/Syllabus.php::addmessage()` — **POST** `{subject_syllabus_id, message}` → adds comment as student |
| Mobile method | **POST** `/api/student/syllabus/{subject_syllabus_id}/comments` |
| | |
| Source | `getmessage()` — POST `subject_syllabus_id` → list of comments |
| Mobile method | **GET** `/api/student/syllabus/{subject_syllabus_id}/comments` |
| | |
| Source | `deletemessage()` — POST `fourm_id` |
| Mobile method | **DELETE** `/api/student/syllabus/comments/{fourm_id}` |

### 3.5 Downloads — Syllabus attachments & lecture videos
| | |
|---|---|
| Source | `student/Syllabus.php::download($id)` (attachment), `lacture_video_download($id)` |
| Mobile method | **GET** `/api/student/syllabus/{id}/attachment` · `/api/student/syllabus/{id}/lecture-video` |
| Logic | `lessonplan_model->getSyllabusById(id)` → `attachment` / `lacture_video` file from `uploads/syllabus_attachment/` |

### 3.6 Study Material (Download module > category = study_material)
| | |
|---|---|
| Source | `student/Content.php::studymaterial()` |
| Mobile method | **GET** `/api/student/materials?category=study_material` |
| Logic | `content_model->getListByCategoryforUser(class_id, section_id, "study_material")` |
| Same pattern | `assignment()` → category `assignments` · `syllabus()` → category `syllabus` · `other()` → category `other_download` |

---

## 4. IMPORTANT — Timeline (student wall/activity)

| | |
|---|---|
| Source controller | `student/Timeline.php` |
| | |
| List/Read | CI `User.php::profile()` loads `timeline_model->getStudentTimeline(student_id, 'yes')`. Mobile → **GET** `/api/student/timeline` |
| Add | CI `Timeline.php::add()` — **POST** `{timeline_title, timeline_date, timeline_desc, timeline_doc(file), student_id}` → creates post with `status='yes'`, uploads doc to `uploads/student_timeline/`, returns the new id. Mobile → **POST** `/api/student/timeline` (multipart) |
| Get one | CI `getstudentsingletimeline()` — **POST** `{id}` → single timeline row. Mobile → **GET** `/api/student/timeline/{id}` |
| Update | CI `edit()` — **POST** `{id, timeline_title, timeline_date, timeline_desc, student_id, timeline_doc(file, optional)}`. Re-uploads doc or keeps old. Mobile → **PUT** `/api/student/timeline/{id}` |
| Delete | CI `delete_timeline()` — **POST** `{id}`; deletes stored doc then row. Mobile → **DELETE** `/api/student/timeline/{id}` |
| Download doc | `download($id)` → file from `uploads/student_timeline/`. Mobile → **GET** `/api/student/timeline/{id}/download` |
| Fields stored | `title, description, timeline_date, status, date, student_id, created_student_id, document` |

---

## 5. IMPORTANT — Homework

### 5.1 Homework List (with submitted status)
| | |
|---|---|
| Source | `student/Homework.php::index()` |
| Mobile method | **GET** `/api/student/homework` |
| Logic | `homework_model->getStudentHomeworkWithStatus(class_id, section_id, student_session_id)`; each row: `checkstatus(homework_id, student_id)` → if `record_count != 0` set `status='submitted'` |
| Closed homework | `getstudentclosedhomeworkwithstatus(...)` same pattern |

### 5.2 Homework Detail
| | |
|---|---|
| Source | `student/Homework.php::homework_detail($id, $status)` |
| Mobile method | **GET** `/api/student/homework/{id}?status=` |
| Data | `homework_model->getRecord(id)`, `get_homeworkDocByIdStdid(id, student_id)` (student's submission docs), `getEvaluationReportForStudent(id, student_id)` (evaluation report), plus `created_by`/`evaluated_by` staff names, and `status` ('submitted' or ''). |
| Download teacher doc | `download($id)` → `/api/student/homework/{id}/document (GET)` |
| Download my submission | `assigmnetDownload($id)` → `/api/student/homework/{id}/submission (GET)` |

### 5.3 Submit Homework (upload answer + docs)
| | |
|---|---|
| Source | `student/Homework.php::upload_docs()` |
| CI method | **POST** (multipart) `{homework_id, message, file}` |
| Mobile method | **POST** `/api/student/homework/{homework_id}/submit` (form-data: `message`, `file`) |
| Logic | `check_assignment(homework_id, student_id)` → required-file flag; uploads to `uploads/homework/assignment/`; `homework_model->upload_docs({homework_id, student_id, message, docs})` |

### 5.4 Daily Assignment (student-created notes/assignments)
| | |
|---|---|
| List | `dailyassignment()` → `getdailyassignment(student_id, student_session_id)` · **GET** `/api/student/daily-assignments` |
| Subjects for form | `subjectgroup_model->getAllsubjectByClassSection(class_id, section_id)` · **GET** `/api/student/daily-assignments/meta` |
| Create | `createdailyassignment()` — **POST** multipart `{title, subject, description, file}` → **POST** `/api/student/daily-assignments` |
| Read one | `getsingledailyassignment(assignment_id)` · **GET** `/api/student/daily-assignments/{id}` |
| Update | `updatedailyassignment()` — **POST** `{assigment_id, title, subject, description, file?}` → **PUT** `/api/student/daily-assignments/{id}` |
| Delete | `deletedailyassignment($id)` (also removes attachment) → **DELETE** `/api/student/daily-assignments/{id}` |
| Download attachment | `dailyassigmnetdownload($id)` → **GET** `/api/student/daily-assignments/{id}/attachment` |

> File upload rules apply everywhere (`filetype_model->get()` gives allowed `file_extension`, `file_mime`, `file_size`).

---

## 6. Attendance

| | |
|---|---|
| Source | `student/Attendence.php` |
| | |
| Monthly/range calendar events | `getAttendence()` — GET `start`/`end` → list of `{title:'Present'|'Absent'|'Late'|'Late with excuse'|'Holiday'|'Half Day', start:date, end:date, description:remark, ...}`. Mobile → **GET** `/api/student/attendance?start=YYYY-MM-DD&end=YYYY-MM-DD` |
| Single day by subject | `getdaysubattendence()` — POST `date` → `studentsubjectattendence_model->studentAttendanceByDate(class_id, section_id, day, date, student_session_id)`. Mobile → **GET** `/api/student/attendance/{date}` |
| Day names | `customlib->getDaysname()` |
| Attendance types | `attendencetype_model->get()` |

---

## 7. Notifications

| | |
|---|---|
| Source | `student/Notification.php` |
| | |
| List | `index()` → `getNotificationForStudent(student_id)` (or ForParent). Filters `publish_date <= today`. Mobile → **GET** `/api/student/notifications` |
| Mark read | `read()` — POST `notice` → `updateStatusforStudent(notification_id, student_id)`. Mobile → **POST** `/api/student/notifications/{id}/read` |
| Update seen status | `updatestatus()` — POST `notification_id` → `updateStatus(...)`. Mobile → **POST** `/api/student/notifications/{id}/seen` |
| Single detail | `notification()` — POST `message_id` → `notification_model->notification(message_id)` + `created_by` staff. Mobile → **GET** `/api/student/notifications/{id}` |
| Attachment download | `download($id)` → file from `uploads/notice_board_images/` |

---

## 8. Timetable

| | |
|---|---|
| Source | `student/Timetable.php::index()` |
| Mobile method | **GET** `/api/student/timetable` |
| Logic | For each day of week, `subjecttimetable_model->getparentSubjectByClassandSectionDay(class_id, section_id, day_key)` |
| Response | `timetable: { [day]: [subject-schedule rows] }` |

---

## 9. Calendar / Events / To-do

| | |
|---|---|
| Source | `student/Calendar.php` |
| | |
| Events | `getevents()` → `calendar_model->getStudentEvents()` filtered by `event_for`/role; shape `{title, start, end, description, id, backgroundColor, event_type}`. Mobile → **GET** `/api/student/calendar/events` |
| To-do list | `index()` → `getTask(user_id, null, 10, offset)`. Mobile → **GET** `/api/student/calendar/tasks` |
| Create / edit task | `addtodo()` — POST `{task_title, task_date, eventid?}` (has eventid = update). Mobile → **POST** `/api/student/calendar/tasks` · **PUT** `/api/student/calendar/tasks/{id}` |
| Get single task | `gettaskbyid($id)` → **GET** `/api/student/calendar/tasks/{id}` |
| Mark complete | `markcomplete($id)` — POST `active` (`yes`/`no`) → **PUT** `/api/student/calendar/tasks/{id}/complete` |
| Delete | `delete_event($id)` → **DELETE** `/api/student/calendar/tasks/{id}` |

---

## 10. Chat (student messaging)

| | |
|---|---|
| Source | `student/Chat.php` |
| My chat user + contacts | `myuser()` → `chatuser_model->getMyID(student_id,'student')` + `myUser(...)`. Mobile → **GET** `/api/student/chat/contacts` |
| Conversation messages | `getChatRecord()` — POST `chat_connection_id` → `myChatAndUpdate(connection_id, chat_user_id)` + `user_last_chat`. Mobile → **GET** `/api/student/chat/{chat_connection_id}/messages` |
| Send message | `newMessage()` — POST `{chat_connection_id, chat_to_user, message, time}` → **POST** `/api/student/chat/{chat_connection_id}/messages` |
| Poll for updates | `chatUpdate()` — POST `{chat_connection_id, chat_to_user, last_chat_id}` → `getUpdatedchat(...)`. Mobile → **GET** `/api/student/chat/{chat_connection_id}/messages?after_id=` |
| Unread count | `mychatnotification()` → `getChatNotification(chat_user_id)`. Mobile → **GET** `/api/student/chat/notifications` |
| Search users | `searchuser()` — POST `keyword`. Mobile → **GET** `/api/student/chat/search?keyword=` |
| Add contact | `adduser()` — POST `{user_id, user_type}` (`Student`/`Staff`) → **POST** `/api/student/chat/contacts` |
| New users | `mynewuser()` — POST `users`. Mobile → **GET** `/api/student/chat/contacts/new` |

---

## 11. Leave Applications

| | |
|---|---|
| Source | `student/Apply_leave.php` |
| | |
| List my leaves | `index()` → `apply_leave_model->get_student(student_session_id)` + `searchMultiClsSectionByStudent(student_id)`. Mobile → **GET** `/api/student/leave-applications` |
| Get one | `get_details($id)` → **GET** `/api/student/leave-applications/{id}` (dates formatted `from_date, to_date, apply_date`) |
| Create / update | `add()` — POST `{apply_date, from_date, to_date, message, leave_id(''=create), files[](multipart)}`. Also triggers mail/sms `student_apply_leave`. Mobile → **POST** `/api/student/leave-applications` · **PUT** `/api/student/leave-applications/{id}` |
| Delete | `remove_leave($id)` (deletes docs) → **DELETE** `/api/student/leave-applications/{id}` |
| Download doc | `download($id)` → file in `uploads/student_leavedocuments/` |

---

## 12. Fees

| | |
|---|---|
| Source | `student/User.php` + `student/Studentfee.php` |
| | |
| Due fees | `User::getfees()` → `studentfeemaster_model->getStudentFees(student_session_id)` + `getStudentTransportFees(...)` + `getStudentProcessingFees(...)`. Mobile → **GET** `/api/student/fees` |
| Fee route (lock-check) | `User::fees()` — same data but only if `is_student_feature_lock`; uses `getDueFeesByStudent(student_session_id, grace_date)`. |
| Fee payment branch (`addfee`, `add_Ajaxfee`, `add_AjaxTransportfee`) | These are **admin** cash/offline entry. Keep mobile side **read-only**; payment flow = gateway controllers in `student/gateway/` |

---

## 13. Library — Books & Issue

| | |
|---|---|
| Source | `student/Book.php` |
| Book catalog | `index()` → `book_model->listbook()` · **GET** `/api/student/books` |
| My issued books | `issue()` → `librarymember_model->checkIsMember("student", student_id)` · **GET** `/api/student/books/issues` |
| (Create/Edit/Delete in this controller are admin screen — skip for mobile) |

---

## 14. Online Exams

| | |
|---|---|
| Source | `student/Onlineexam.php` |
| | |
| Available exams | `index()` → `onlineexam_model->getStudentexam(student_session_id)` · **GET** `/api/student/online-exams` |
| Closed exams | `getclosedexamlist()` → `getstudentclosedexamlist(...)` · **GET** `/api/student/online-exams/closed` |
| Exam view (my attempt/result) | `view($id)` → `examstudentsID(student_session_id, id)`, `getexamdetails(id)`. Mobile → **GET** `/api/student/online-exams/{id}` |
| Start exam / fetch questions | `getExamForm()` — POST `recordid`; returns `exam`, `duration`, `questions`, `question_status` (1=locked/expired), `total_question`; increments attempts. Mobile → **POST** `/api/student/online-exams/{id}/start` |
| Submit answers | `save()` — POST many `question_type_*`, `radio*`, `checkbox*`, `answer*`, `attachment*`, `onlineexam_student_id`, `question_id_*`. Saves through `onlineexamresult_model->add()` then `updateExamResult(...)`. Mobile → **POST** `/api/student/online-exams/{id}/submit` |
| Question types | `singlechoice`, `true_false`, `multichoice`, `descriptive` (descriptive can carry a file → `uploads/onlinexam_images/`) |
| Attachment download | `downloadattachment($doc)` |
| Result list | `getexamlist()` (serves DataTables; mobile can reuse `view`) |

---

## 15. Transport / Route

| | |
|---|---|
| Source | `student/Route.php` |
| My route + pickup point | `index()` → student row with `route_id` + `pickuppoint_model->getPickupPointByRouteID(route_id)`. Mobile → **GET** `/api/student/transport` |
| Bus detail | `getbusdetail()` — POST `vehrouteid` → `vehroute_model->getVechileDetailByVecRouteID(vehrouteid)`. Mobile → **GET** `/api/student/transport/bus/{vehroute_id}` |

---

## 16. Hostel

| | |
|---|---|
| Source | `student/Hostel.php` + `student/Hostelroom.php` |
| Hostels | `Hostel::index()` → `hostel_model->listhostel()` · **GET** `/api/student/hostels` |
| Student's hostel/room | Dashboard/profile data already includes hostel mapping. See `Hostelroom.php` for room list. |

---

## 17. Teachers

| | |
|---|---|
| Source | `student/Teacher.php` |
| Teachers of my class | `index()` → `subjecttimetable_model->getTeacherByClassandSection(class_id, section_id)` grouped by `staff_id`. Mobile → **GET** `/api/student/teachers` |
| My ratings | For students: `staff_model->get_ratingbyuser(user_id, 'student')` → `reviews[staff_id]=rate`, `comment[staff_id]=...` |
| Rate a teacher | `rating()` — POST `{staff_id, comment, rate, user_id, role}` → **POST** `/api/student/teachers/rate` (validate comment+rate required) |

---

## 18. Behaviour Reports (module `behaviour_records` — IMPLEMENTED)

| | |
|---|---|
| Source | `codelignter/student cotroller/behaviour/` + `User::profile()`: `studentincidents_model->studentbehaviour(student_id)` + `totalpoints(student_id)` |
| My incidents + total points | **GET** `/api/behaviour` → `{incidents:[{id, incident_id, title, description, point, assigned_at}], total_points, behavioursetting, role}` |
| Comments on incidents | `Studentincidentcomments.php`: **POST** `/api/behaviour/addmessage` (`{student_incident_id, comment}`) · **POST** `/api/behaviour/getmessage` (`{student_incident_id}`) · **POST** `/api/behaviour/delete_comment` (`{id}`) |
| Tables | `student_incidents`, `student_behaviour` (title/point/description), `student_incident_comments`, `behaviour_settings` — all exist in the live DB |

---

## 19. Dashboard (aggregate home screen for the app)

| | |
|---|---|
| Source | `student/User.php::dashboard()` |
| Mobile method | **GET** `/api/student/dashboard` |
| Includes | attendance % (session year), issued books, homework list w/ status, notifications (published), syllabus progress per subject, this week's timetable, visitor list, teachers grouped, `low_attendance_limit`, student session username/data |
| Profile | `User.php::profile()` → **GET** `/api/student/profile` returns student row, fees, discount fees, timeline list, exam results, grades, docs, per-month attendance map, behaviour points, unread notifications, CBSE exams (if enabled). |

---

## 20. Misc / Account (from `student/User.php` — all IMPLEMENTED)

| Change password | `changepass()` — validate current password, save new. **POST** `/api/auth/changepass` |
| Change username | `changeusername()` — checks old, uniqueness. **POST** `/api/user/changeusername` |
| Language | `user_language($lang_id)` — **POST** `/api/user/language` |
| Currency | `change_currency()` — **POST** `/api/user/currency` |
| Student docs list/upload | `create_doc()` (POST title+doc to `uploads/student_documents/{student_id}/`) · **GET** `/api/user/documents` · **POST** `/api/user/documents` (multipart `first_title`, `first_doc`) |
| Download student doc | `download($student_id, $id)` → **GET** `/api/user/documents/download/{id}` |
| Choose class (multi-class student/parent) | `choose()` — **POST** `/api/user/choose` (body `clschg` = student_session_id; sets default_login) |
| Class list for selection | **GET** `/api/user/choose` → `student_lists` |

---

## Status (implemented)

- **Implemented & live:** Dashboard/Profile, Exams, Exam Schedule, Marks, Online Exams (incl. closed list + attachment download), Subjects, Syllabus/Lessons (+comments, lecture videos), Study Material, Timeline, Homework (+daily assignments), Attendance, Notifications, Timetable, Calendar, Chat, Leave, Fees (read-only), Library, Transport, Hostel, Teachers, Behaviour records, Student documents, Change username/language/currency, Admission (public).
- **Not ported (optional/out of scope):** CBSE exams (`cbseexam` addon module — tables/schema not present in this DB), online payment gateways (`student/gateway/`) — keep fees read-only; behaviour *admin* assignment screens (staff web app only).
- **Download endpoints** return the actual file (not a JSON filename), resolved from private `storage/app/uploads` or `public/uploads`.

## Notes for the agent / conversion checklist

1. **Auth:** token (Laravel Sanctum/Passport) middleware; resolve the logged-in student from token, never trust client-sent `student_id`.
2. **Context resolution:** build a helper `currentStudentContext()` returning `{student_id, class_id, section_id, student_session_id}` exactly like `customlib->getStudentCurrentClsSection()`.
3. **Joint session model:** student may belong to multiple class/section combos (`student_sessions`); `choose()` handles default selection. The mobile app needs a student who can switch class.
4. **Files:** map `media_storage->fileupload/fileDownload/filedelete` to Laravel `Storage`; keep the same folders: `uploads/student_timeline/`, `uploads/homework/assignment/`, `uploads/student_leavedocuments/`, `uploads/notice_board_images/`, `uploads/syllabus_attachment/`, `uploads/student_documents/{student_id}/`, `uploads/onlinexam_images/`.
5. **Validation messages:** CI returns field-level `error` object → mirror as Laravel validation messages (`status: fail` + `error: {...}`).
6. **Dates:** CI uses `customlib->datetostrtotime()` on `d-m-Y`. Accept both formats in Laravel for safety.
7. **Responses:** always wrap in `{status, data, message}` for Flutter. Strip HTML `page` values the CI version returned; return raw JSON.
8. **Order to build (recommended):** Dashboard/Profile → Exams+Results → Homework → Syllabus/Lessons → Timetable → Attendance → Notifications → Timeline → Calendar → Chat → Leave → Fees (read) → Online Exams → Transport → Library → Hostel → Teachers → Behaviour.