class ApiEndpoints {
  // 💡 TIP: 'localhost' refers to the phone itself.
  // Use '10.0.2.2' for Android Emulator OR your machine's IP (e.g. 10.80.64.185) for physical devices.
//   static const String baseUrl = "http://10.80.64.185/smart-school/api";
  static const String baseUrl =
      "https://origin-person-specialties-puts.trycloudflare.com/api";

  // LMS Auth
  static const String lmsLogin = "$baseUrl/auth/login";
  static const String lmsLogout = "$baseUrl/auth/logout";
  static const String lmsChangePassword = "$baseUrl/auth/changepass";

  // LMS Dashboard & Profile
  static const String lmsDashboard = "$baseUrl/user/dashboard";
  static const String lmsProfile = "$baseUrl/user/profile";

  // LMS Fees
  static const String lmsFees = "$baseUrl/user/fees";
  static const String lmsGetFees = "$baseUrl/user/getfees";

  // LMS Attendance
  static const String lmsAttendance = "$baseUrl/attendence";
  static String lmsGetAttendance({String? start, String? end}) =>
      "$baseUrl/attendence/getAttendence?start=$start&end=$end";
  static const String lmsDayAttendance =
      "$baseUrl/attendence/getdaysubattendence";

  // LMS Marks
  static const String lmsMarks = "$baseUrl/mark/marklist";

  // LMS Homework
  static const String lmsHomework = "$baseUrl/homework";
  static String lmsHomeworkDetail(int id, int status) =>
      "$baseUrl/homework/homework_detail/$id/$status";
  static const String lmsHomeworkUpload = "$baseUrl/homework/upload_docs";

  // LMS Timetable
  static const String lmsTimetable = "$baseUrl/timetable";

  // LMS Subjects
  static const String lmsSubjects = "$baseUrl/subject";

  // LMS Syllabus
  static const String lmsSyllabus = "$baseUrl/syllabus";
  static const String lmsSyllabusStatus = "$baseUrl/syllabus/status";

  // LMS Teachers
  static const String lmsTeachers = "$baseUrl/teacher";
  static const String lmsTeacherRating = "$baseUrl/teacher/rating";

  // LMS Notifications
  static const String lmsNotifications = "$baseUrl/notification";
  static const String lmsNotificationStatus =
      "$baseUrl/notification/updatestatus";

  // LMS Chat
  static const String lmsChatMyUser = "$baseUrl/chat/myuser";
  static const String lmsChatRecord = "$baseUrl/chat/getChatRecord";
  static const String lmsNewMessage = "$baseUrl/chat/newMessage";

  // LMS Leave
  static const String lmsApplyLeave = "$baseUrl/apply_leave";
  static const String lmsAddLeave = "$baseUrl/apply_leave/add";

  // LMS Library
  static const String lmsBooks = "$baseUrl/book";
  static const String lmsBookIssue = "$baseUrl/book/issue";

  // LMS Transport
  static const String lmsRoute = "$baseUrl/route";

  // LMS Content
  static const String lmsContentList = "$baseUrl/content/list";
  static const String lmsContentAssignment = "$baseUrl/content/assignment";
  static const String lmsContentStudyMaterial =
      "$baseUrl/content/studymaterial";

  // LMS Exams
  static const String lmsExams = "$baseUrl/exam";
  static const String lmsExamResult = "$baseUrl/exam/examresult";
  static const String lmsExamSchedule = "$baseUrl/examschedule";

  // LMS Visitors
  static const String lmsVisitors = "$baseUrl/visitors";

  // LMS Calendar
  static const String lmsCalendar = "$baseUrl/calendar";
  static const String lmsCalendarEvents = "$baseUrl/calendar/getevents";

  // LMS Timeline
  static const String lmsTimelineAdd = "$baseUrl/timeline/add";

  // LMS Online Exam
  static const String lmsOnlineExam = "$baseUrl/onlineexam";
  static const String lmsOnlineExamSubmit = "$baseUrl/onlineexam/submit";

  // LMS Payment
  static const String lmsOfflinePayment = "$baseUrl/offlinepayment";
  static const String lmsAddOfflinePayment = "$baseUrl/offlinepayment/add";

  // LMS Video Tutorial
  static const String lmsVideoTutorial = "$baseUrl/video_tutorial";

  // LMS Hostel
  static const String lmsHostel = "$baseUrl/hostel";
}
