<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class User extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function unauthorized()
    {
        return $this->api_unauthorized('You are not authorized to access this resource');
    }

    public function choose()
    {
        if ($this->session->has_userdata('current_class')) {
            return $this->api_success(['redirect' => 'user/user/dashboard']);
        }

        $role = $this->customlib->getUserRole();
        $student_current_class = array();
        $default_login_student_id = "";

        if ($role == "student") {
            $student_id = $this->customlib->getStudentSessionUserID();
            $data['student_lists'] = $this->studentsession_model->searchMultiClsSectionByStudent($student_id);

            if (empty($data['student_lists'])) {
                $data['student_lists'] = $this->studentsession_model->getMultiClsSectionByStudentOldSession($student_id);
                $session = $this->session_model->get($data['student_lists'][0]->session_id);
                $session_array = array('session_id' => $session['id'], 'session' => $session['session']);
                $this->session->set_userdata('session_array', $session_array);
            }

            if ($data['student_lists'][0]->default_login) {
                $default_login_student_id = $data['student_lists'][0]->student_id;
                $student_current_class = array('session_id' => $data['student_lists'][0]->session_id, 'class_id' => $data['student_lists'][0]->class_id, 'section_id' => $data['student_lists'][0]->section_id, 'student_session_id' => $data['student_lists'][0]->student_session_id);
            }
        } elseif ($role == "parent") {
            $parent_id = $this->customlib->getUsersID();
            $data['student_lists'] = $this->student_model->getParentChilds($parent_id);
            if (!empty($data['student_lists'])) {
                if ($data['student_lists'][0]->default_login) {
                    $default_login_student_id = $data['student_lists'][0]->id;
                    $student_current_class = array('session_id' => $data['student_lists'][0]->session_id, 'class_id' => $data['student_lists'][0]->class_id, 'section_id' => $data['student_lists'][0]->section_id, 'student_session_id' => $data['student_lists'][0]->student_session_id);
                }
            }
        }

        if (!empty($student_current_class)) {
            $logged_In_User = $this->customlib->getLoggedInUserData();
            $logged_In_User['student_id'] = $default_login_student_id;
            $this->session->set_userdata('student', $logged_In_User);
            $this->session->set_userdata('current_class', $student_current_class);
            return $this->api_success(['redirect' => 'user/user/dashboard']);
        }

        $this->form_validation->set_rules('clschg', $this->lang->line('select_class'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $student_session_id = $this->input->post('clschg');
            $student = $this->student_model->getByStudentSession($student_session_id);
            $logged_In_User = $this->customlib->getLoggedInUserData();
            $logged_In_User['student_id'] = $student['id'];
            $this->session->set_userdata('student', $logged_In_User);
            $this->studentsession_model->updateById(array('id' => $student_session_id, 'default_login' => 1));
            $student_current_class = array('class_id' => $student['class_id'], 'section_id' => $student['section_id'], 'student_session_id' => $student['student_session_id']);
            $this->session->set_userdata('current_class', $student_current_class);
            return $this->api_success(['redirect' => 'user/user/dashboard']);
        }

        return $this->api_success([
            'student_lists' => $data['student_lists'] ?? [],
            'sch_setting' => $this->sch_setting_detail,
            'role' => $role
        ]);
    }

    public function fees()
    {
        $id = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();

        if ($this->sch_setting_detail->is_student_feature_lock) {
            $lock_grace_period = $this->sch_setting_detail->lock_grace_period;
            $date = date('Y-m-d', strtotime(date("Y-m-d")) - (86400 * $lock_grace_period));

            $category = $this->category_model->get();
            $data['categorylist'] = $category;
            $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
            $paymentoption = $this->customlib->checkPaypalDisplay();
            $data['paymentoption'] = $paymentoption;
            $data['payment_method'] = !empty($this->payment_method);

            $student_id = $id;
            $student = $this->student_model->getStudentByClassSectionID($student_current_class->class_id, $student_current_class->section_id, $student_id);
            $class_id = $student_current_class->class_id;
            $section_id = $student_current_class->section_id;
            $data['title'] = 'Student Details';
            $student_due_fee = $this->studentfeemaster_model->getDueFeesByStudent($student_current_class->student_session_id, $date);

            if (!empty($student_due_fee)) {
                foreach ($student_due_fee as $result_key => $result_value) {
                    if ($result_value->is_system == 0) {
                        $student_due_fee[$result_key]->{'amount'} = $result_value->fee_amount;
                    }
                    if ($result_value->amount > 0) {
                        if (isJSON($result_value->amount_detail)) {
                            $total_balance = 0;
                            $fee_paid = 0;
                            $fee_discount = 0;
                            $fee_fine = 0;
                            $fee_deposits = json_decode(($result_value->amount_detail));

                            foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                $fee_paid += $fee_deposits_value->amount;
                                $fee_discount += $fee_deposits_value->amount_discount;
                                $fee_fine += $fee_deposits_value->amount_fine;
                            }
                            $total_balance = ($result_value->amount + $result_value->fine_amount) - ($fee_paid + $fee_fine + $fee_discount);

                            if ($total_balance <= 0) {
                                unset($student_due_fee[$result_key]);
                            }
                        }
                    } else {
                        unset($student_due_fee[$result_key]);
                    }
                }
            }

            $data['student_due_fee'] = $student_due_fee;
            $data['student'] = $student;

            $transport_fees = [];
            $module = $this->module_model->getPermissionByModulename('transport');
            if ($module['is_active']) {
                $transport_fees = $this->studentfeemaster_model->getDueTransportFeeByStudent($student['student_session_id'], $student['route_pickup_point_id'], $date);
            }

            if (!empty($transport_fees)) {
                foreach ($transport_fees as $trans_fee_key => $trans_fee_value) {
                    if (isJSON($trans_fee_value->amount_detail)) {
                        $total_balance = 0;
                        $fee_paid = 0;
                        $fee_discount = 0;
                        $fee_fine = 0;
                        $trans_fee_deposits = json_decode(($trans_fee_value->amount_detail));

                        foreach ($trans_fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                            $fee_paid += $fee_deposits_value->amount;
                            $fee_discount += $fee_deposits_value->amount_discount;
                            $fee_fine += $fee_deposits_value->amount_fine;
                        }
                        $total_balance = ($trans_fee_value->fees + $trans_fee_value->fine_amount) - ($fee_paid + $fee_fine + $fee_discount);

                        if ($total_balance <= 0) {
                            unset($transport_fees[$trans_fee_key]);
                        }
                    }
                }
            }

            $data['transport_fees'] = $transport_fees;

            return $this->api_success([
                'student_due_fee' => $data['student_due_fee'],
                'transport_fees' => $data['transport_fees'],
                'student' => $data['student'],
                'payment_method' => $data['payment_method'],
                'categorylist' => $data['categorylist'],
                'adm_auto_insert' => $data['adm_auto_insert']
            ]);
        } else {
            return $this->api_success(['redirect' => 'user/user/dashboard']);
        }
    }

    public function profile()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $marks_division = $this->marksdivision_model->get();
        $student = $this->student_model->getStudentByClassSectionID($student_current_class->class_id, $student_current_class->section_id, $student_id);
        $superadmin_visible = $this->Setting_model->get();

        $data = array();
        $data['superadmin_restriction'] = $superadmin_visible[0]['superadmin_restriction'];
        $data['marks_division'] = $marks_division;

        if (!empty($student)) {
            $transport_fees = [];
            $student_session_id = $student_current_class->student_session_id;
            $gradeList = $this->grade_model->get();
            $student_due_fee = $this->studentfeemaster_model->getStudentFees($student_session_id);
            $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student_session_id);
            $data['student_discount_fee'] = $student_discount_fee;
            $data['student_due_fee'] = $student_due_fee;
            $timeline = $this->timeline_model->getStudentTimeline($student["id"], $status = 'yes');
            $data["timeline_list"] = $timeline;
            $data['sch_setting'] = $this->sch_setting_detail;
            $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
            $data['examSchedule'] = array();
            $data['exam_result'] = $this->examgroupstudent_model->searchStudentExams($student['student_session_id'], true, true);
            $ss = $this->grade_model->getGradeDetails();
            $data['exam_grade'] = $this->grade_model->getGradeDetails();
            $student_doc = $this->student_model->getstudentdoc($student_id);
            $data['student_doc'] = $student_doc;
            $data['student_doc_id'] = $student_id;
            $category_list = $this->category_model->get();
            $data['category_list'] = $category_list;
            $data['gradeList'] = $gradeList;
            $data['student'] = $student;

            $startmonth = $this->setting_model->getStartMonth();
            $monthlist = $this->customlib->getMonthNoDropdown($startmonth);
            $data["monthlist"] = $monthlist;

            $attendencetypes = $this->attendencetype_model->getAttType();
            $data['attendencetypeslist'] = $attendencetypes;

            $year = date("Y");
            $input = $this->setting_model->getCurrentSessionName();
            list($a, $b) = explode('-', $input);
            $start_year = $a;
            if (strlen($b) == 2) {
                $Next_year = substr($a, 0, 2) . $b;
            } else {
                $Next_year = $b;
            }

            $start_end_month = $this->startmonthandend();
            $session_year_start = date("Y-m-01", strtotime($start_year . '-' . $start_end_month[0] . '-01'));
            $session_year_end = date("Y-m-t", strtotime($Next_year . '-' . $start_end_month[1] . '-01'));

            $countAttendance = $this->countAttendance($session_year_start, $student['student_session_id']);

            $st = $start_year;
            $res = array();

            foreach ($monthlist as $key => $value) {
                $datemonth = $key;

                if ($datemonth < $this->sch_setting_detail->start_month) {
                    $st = $Next_year;
                }

                $date_each_month = date($st . '-' . $datemonth . '-01');
                $date_end = date('t', strtotime($date_each_month));
                for ($n = 1; $n <= $date_end; $n++) {
                    $att_date = sprintf("%02d", $n);
                    $attendence_array[] = $att_date;

                    $att_dates = $st . "-" . $datemonth . "-" . sprintf("%02d", $n);
                    $date_array[] = $att_dates;

                    $student_date_attendance = $this->stuattendence_model->studentattendance($att_dates, $student['student_session_id']);
                    $res[$att_dates] = [];
                    if ($student_date_attendance) {
                        $res[$att_dates] = $student_date_attendance;
                    }
                }
            }

            $data["session_year_start"] = $session_year_start;
            $data["session_year_end"] = $session_year_end;
            $data["countAttendance"] = $countAttendance;
            $data["resultlist"] = $res;
            $data["start_year"] = $start_year;
            $data["Next_year"] = $Next_year;

            $transport_fees = [];
            $module = $this->module_model->getPermissionByModulename('transport');
            if ($module['is_active']) {
                $transport_fees = $this->studentfeemaster_model->getStudentTransportFees($student_session_id, $student['route_pickup_point_id']);
            }

            $data['transport_fees'] = $transport_fees;

            if ($this->module_lib->hasModule('behaviour_records') && $this->studentmodule_lib->hasActive('behaviour_records')) {
                $this->load->model("studentincidents_model");
                $total_points = $this->studentincidents_model->totalpoints($student_id);
                $student['total_points'] = $total_points['totalpoints'];
            }

            $data['student'] = $student;
        } else {
            return $this->api_unauthorized();
        }

        if ($this->module_lib->hasModule('behaviour_records')) {
            $this->load->model("studentincidents_model");
            $data['assignstudent'] = $this->studentincidents_model->studentbehaviour($student_id);

            $this->load->model('studentbehaviour_model');
            $data['behavioursetting'] = $this->studentbehaviour_model->getsettings();
            $data['role'] = $this->customlib->getUserRole();
        }

        $unread_notifications = $this->notification_model->getUnreadStudentNotification();
        $notification_bydate = array();

        foreach ($unread_notifications as $unread_notifications_key => $unread_notifications_value) {
            if (date($this->customlib->getSchoolDateFormat()) >= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($unread_notifications_value->publish_date))) {
                $notification_bydate[] = $unread_notifications_value;
            }
        }

        $setting_data = $this->setting_model->get();
        $data['student_timeline'] = $setting_data[0]['student_timeline'];
        $data['unread_notifications'] = $notification_bydate;
        $login_id = $this->customlib->getStudentSessionUserID();
        $data['student_id'] = $login_id;

        if ($this->module_lib->hasModule('cbseexam')) {
            $this->load->model("cbseexam/cbseexam_exam_model");
            $this->load->model("cbseexam/cbseexam_grade_model");
            $this->load->model("cbseexam/cbseexam_assessment_model");

            $exam_list = $this->cbseexam_exam_model->getStudentExamByStudentSession($student_current_class->student_session_id);

            $student_exams = [];
            if (!empty($exam_list)) {
                foreach ($exam_list as $exam_key => $exam_value) {
                    $exam_subjects = $this->cbseexam_exam_model->getexamsubjects($exam_value->cbse_exam_id);
                    $exam_value->{"subjects"} = $exam_subjects;
                    $exam_value->{"grades"} = $this->cbseexam_grade_model->getGraderangebyGradeID($exam_value->cbse_exam_grade_id);
                    $exam_value->{"exam_assessments"} = $this->cbseexam_assessment_model->getWithAssessmentTypeByAssessmentID($exam_value->cbse_exam_assessment_id);
                    $exam_value->{"exam_subject_assessments"} = $this->cbseexam_assessment_model->getSubjectAssessmentsByExam($exam_subjects);

                    $cbse_exam_result = $this->cbseexam_exam_model->getStudentResultByExamId($exam_value->cbse_exam_id, [$exam_value->student_session_id]);

                    $students = [];
                    $student_rank = "";

                    if (!empty($cbse_exam_result)) {
                        foreach ($cbse_exam_result as $student_key => $student_value) {
                            $student_rank = $student_value->rank;

                            if (!empty($students)) {
                                if (!array_key_exists($student_value->subject_id, $students['subjects'])) {
                                    $new_subject = [
                                        'subject_id' => $student_value->subject_id,
                                        'subject_name' => $student_value->subject_name,
                                        'subject_code' => $student_value->subject_code,
                                        'exam_assessments' => [
                                            $student_value->cbse_exam_assessment_type_id => [
                                                'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                                'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                                'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                                'maximum_marks' => $student_value->maximum_marks,
                                                'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                                'marks' => $student_value->marks,
                                                'note' => $student_value->note,
                                                'is_absent' => $student_value->is_absent,
                                            ],
                                        ],
                                    ];

                                    $students['subjects'][$student_value->subject_id] = $new_subject;
                                } elseif (!array_key_exists($student_value->cbse_exam_assessment_type_id, $students['subjects'][$student_value->subject_id]['exam_assessments'])) {
                                    $new_assesment = [
                                        'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                        'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                        'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                        'maximum_marks' => $student_value->maximum_marks,
                                        'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                        'marks' => $student_value->marks,
                                        'note' => $student_value->note,
                                        'is_absent' => $student_value->is_absent,
                                    ];

                                    $students['subjects'][$student_value->subject_id]['exam_assessments'][$student_value->cbse_exam_assessment_type_id] = $new_assesment;
                                }
                            } else {
                                $students['subjects'] = [
                                    $student_value->subject_id => [
                                        'subject_id' => $student_value->subject_id,
                                        'subject_name' => $student_value->subject_name,
                                        'subject_code' => $student_value->subject_code,
                                        'exam_assessments' => [
                                            $student_value->cbse_exam_assessment_type_id => [
                                                'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                                'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                                'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                                'maximum_marks' => $student_value->maximum_marks,
                                                'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                                'marks' => $student_value->marks,
                                                'note' => $student_value->note,
                                                'is_absent' => $student_value->is_absent,
                                            ],
                                        ],
                                    ],
                                ];
                            }
                        }
                    }
                    $exam_value->{"rank"} = $student_rank;
                    $exam_value->{"exam_data"} = $students;
                }
            }

            $data['exams'] = $exam_list;
        }

        return $this->api_success($data);
    }

    public function dashboard()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $session_year_detail = sessionYearDetails($this->sch_setting_detail->session, $this->sch_setting_detail->start_month);

        $attendance_date = ['start' => $session_year_detail['month_start'], 'end' => $session_year_detail['month_end']];
        $student_total_attendances = $this->attendencetype_model->getStudentAttendenceRange($attendance_date, $student_current_class->student_session_id);

        $attendence_percentage = -1;

        if (!empty($student_total_attendances)) {
            $total_attendance_count = count($student_total_attendances);
            $absents = 0;
            foreach ($student_total_attendances as $attend_key => $attend_value) {
                ($attend_value->attendence_type_id == 4) ? $absents++ : "";
            }
            $total_presents = $total_attendance_count - $absents;
            $attendence_percentage = two_digit_float(($total_presents * 100) / $total_attendance_count);
        }

        $data = array();
        $student_id = $this->customlib->getStudentSessionUserID();
        $member_type = "student";
        $checkIsMember = $this->librarymember_model->checkIsMember($member_type, $student_id);

        $data['bookList'] = $checkIsMember;

        $class_id = $student_current_class->class_id;
        $section_id = $student_current_class->section_id;
        $homeworklist = $this->homework_model->getStudentHomeworkWithStatus($class_id, $section_id, $student_current_class->student_session_id);
        foreach ($homeworklist as $key => $homeworklist_value) {
            $homeworklist[$key]['status'] = '';
            $checkstatus = $this->homework_model->checkstatus($homeworklist_value['id'], $student_id);
            if ($checkstatus['record_count'] != 0) {
                $homeworklist[$key]['status'] = 'submitted';
            }
        }

        $data["homeworklist"] = $homeworklist;

        $user_role = $this->customlib->getUserRole();
        if ($user_role == 'student') {
            $student_id = $this->customlib->getStudentSessionUserID();
            $notifications = $this->notification_model->getNotificationForStudent($student_id);
        } elseif ($user_role == 'parent') {
            $student_id = $this->customlib->getUsersID();
            $notifications = $this->notification_model->getNotificationForParent($student_id);
        }
        $notification_bydate = array();
        foreach ($notifications as $key => $value) {
            if (strtotime(date('Y-m-d')) >= strtotime($value['publish_date'])) {
                $notification_bydate[] = $value;
            }
        }

        $data['notificationlist'] = $notification_bydate;

        $subjects = $this->syllabus_model->getmysubjects($student_current_class->class_id, $student_current_class->section_id);

        foreach ($subjects as $key => $value) {
            $show_status = 0;
            $teacher_summary = array();
            $lesson_result = array();
            $complete = 0;
            $incomplete = 0;
            $array[] = $value;
            $subject_details = $this->syllabus_model->get_subjectstatus($value->subject_group_subjects_id, $value->subject_group_class_sections_id);
            if ($subject_details[0]->total != 0) {
                $complete = ($subject_details[0]->complete / $subject_details[0]->total) * 100;
                $incomplete = ($subject_details[0]->incomplete / $subject_details[0]->total) * 100;
                if ($value->code == '') {
                    $lebel = $value->name;
                } else {
                    $lebel = $value->name . ' (' . $value->code . ')';
                }
                $data['subjects_data'][$value->subject_group_subjects_id] = array(
                    'lebel' => $lebel,
                    'complete' => round($complete),
                    'incomplete' => round($incomplete),
                    'id' => $value->subject_group_subjects_id . '_' . $value->code,
                    'total' => $subject_details[0]->total,
                    'name' => $value->name,
                    'graph_id' => $value->subject_group_subjects_id . time(),
                );
            } else {
                $data['subjects_data'][$value->subject_group_subjects_id] = array(
                    'lebel' => $value->name . ' (' . $value->code . ')',
                    'complete' => 0,
                    'incomplete' => 0,
                    'id' => $value->subject_group_subjects_id . '_' . $value->code,
                    'total' => 0,
                    'name' => $value->name,
                    'graph_id' => $value->subject_group_subjects_id . time(),
                );
            }
        }

        $days = $this->customlib->getDaysname();
        $days_record = array();
        foreach ($days as $day_key => $day_value) {
            $days_record[$day_key] = $this->subjecttimetable_model->getparentSubjectByClassandSectionDay($student_current_class->class_id, $student_current_class->section_id, $day_key);
        }
        $data['timetable'] = $days_record;
        $data['attendence_percentage'] = $attendence_percentage;

        $data['visitor_list'] = $this->visitors_model->visitorbystudentid($student_current_class->student_session_id);

        $data['studentsession_username'] = $this->customlib->getStudentSessionUserName();
        $data['student_data'] = $this->customlib->getLoggedInUserData();

        $setting_data = $this->setting_model->get();
        $data['low_attendance_limit'] = $setting_data[0]['low_attendance_limit'];

        $data['teachers'] = $teachers = array();
        $student_teacher = $this->subjecttimetable_model->getTeacherByClassandSection($student_current_class->class_id, $student_current_class->section_id);

        foreach ($student_teacher as $value) {
            $teachers[$value->staff_id][] = $value;
        }

        $data['teacherlist'] = $teachers;

        return $this->api_success($data);
    }

    public function changepass()
    {
        $role = $this->result["role"];
        if ($role == 'guest') {
            return $this->api_error('Guest users cannot change password here');
        }

        $this->form_validation->set_rules('current_pass', 'Current password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('new_pass', 'New password', 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', 'Confirm password', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $errors = array(
                'current_pass' => form_error('current_pass'),
                'new_pass' => form_error('new_pass'),
                'confirm_pass' => form_error('confirm_pass'),
            );
            return $this->api_error('Validation failed', $errors);
        } else {
            $sessionData = $this->session->userdata('student');
            $data_array = array(
                'current_pass' => ($this->input->post('current_pass')),
                'new_pass' => ($this->input->post('new_pass')),
                'user_id' => $sessionData['id'],
                'user_name' => $sessionData['username'],
            );
            $newdata = array(
                'id' => $sessionData['id'],
                'password' => $this->input->post('new_pass'),
            );
            $query1 = $this->user_model->checkOldPass($data_array);
            if ($query1) {
                $query2 = $this->user_model->saveNewPass($newdata);
                if ($query2) {
                    return $this->api_success(null, 'Password changed successfully');
                }
            } else {
                return $this->api_error('Invalid current password');
            }
        }
    }

    public function changeusername()
    {
        $sessionData = $this->customlib->getLoggedInUserData();

        $this->form_validation->set_rules('current_username', $this->lang->line('current_password'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('new_username', $this->lang->line('current_password'), 'trim|required|xss_clean|matches[confirm_username]');
        $this->form_validation->set_rules('confirm_username', $this->lang->line('confirm_username'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $errors = array(
                'current_username' => form_error('current_username'),
                'new_username' => form_error('new_username'),
                'confirm_username' => form_error('confirm_username'),
            );
            return $this->api_error('Validation failed', $errors);
        } else {
            $data_array = array(
                'username' => $this->input->post('current_username'),
                'new_username' => $this->input->post('new_username'),
                'role' => $sessionData['role'],
                'user_id' => $sessionData['id'],
            );
            $newdata = array(
                'id' => $sessionData['id'],
                'username' => $this->input->post('new_username'),
            );
            $is_valid = $this->user_model->checkOldUsername($data_array);

            if ($is_valid) {
                $is_exists = $this->user_model->checkUserNameExist($data_array);
                if (!$is_exists) {
                    $is_updated = $this->user_model->saveNewUsername($newdata);
                    if ($is_updated) {
                        return $this->api_success(null, $this->lang->line('username_changed_successfully'));
                    }
                } else {
                    return $this->api_error($this->lang->line('username_already_exists_please_choose_other'));
                }
            } else {
                return $this->api_error($this->lang->line('invalid_current_username'));
            }
        }
    }

    public function download($student_id, $id)
    {
        $student_doc = $this->student_model->studentdocbyid($id);
        if ($student_doc) {
            $this->media_storage->filedownload($student_doc['doc'], "./uploads/student_documents/$student_id/");
            return $this->api_success(['file' => $student_doc['doc']]);
        }
        return $this->api_not_found('Document not found');
    }

    public function user_language($lang_id)
    {
        $language_name = $this->db->select('languages.language,languages.is_rtl')->from('languages')->where('id', $lang_id)->get()->row_array();
        $student = $this->session->userdata('student');

        if (!empty($student)) {
            $this->session->unset_userdata('student');
        }
        $language_array = array('lang_id' => $lang_id, 'language' => $language_name['language']);

        if ($language_name['is_rtl'] == 1) {
            $student['is_rtl'] = 'enabled';
        } else {
            $student['is_rtl'] = 'disabled';
        }

        $student['language'] = $language_array;
        $this->session->set_userdata('student', $student);

        $session = $this->session->userdata('student');
        if ($session['role'] == 'student') {
            $id = $session['student_id'];
        } elseif ($session['role'] == 'guest') {
            $id = $session['guest_id'];
        }

        $data['lang_id'] = $lang_id;
        $language_result = $this->language_model->set_studentlang($id, $data);

        return $this->api_success(['language' => $language_name]);
    }

    public function change_currency()
    {
        $currency_id = $this->input->post('currency_id');
        $currency = $this->currency_model->get($currency_id);
        $logged_session = $this->session->userdata('student');

        if ($logged_session['role'] == "guest") {
            $user_id = $this->customlib->getUsersID();
            $this->load->model('guest_model');
            $update_data = array('id' => $user_id, 'currency_id' => $currency_id);
            $this->guest_model->add($update_data);
        } else {
            $user_id = $this->customlib->getUsersID();
            $update_data = array('id' => $user_id, 'currency_id' => $currency_id);
            $this->user_model->add($update_data);
        }

        $this->session->userdata['student']['currency_base_price'] = $currency->base_price;
        $this->session->userdata['student']['currency_symbol'] = $currency->symbol;
        $this->session->userdata['student']['currency'] = $currency_id;
        $this->session->userdata['student']['currency_name'] = $currency->short_name;

        return $this->api_success(['currency' => $currency], $this->lang->line('currency_changed_successfully'));
    }

    public function timeline_download($timeline_id, $doc)
    {
        $this->media_storage->filedownload($this->uri->segment(4), "./uploads/student_timeline");
        return $this->api_success(['timeline_id' => $timeline_id, 'doc' => $doc]);
    }

    public function view($id)
    {
        $data['title'] = 'Student Details';
        $student = $this->student_model->get($id);
        $student_due_fee = $this->studentfee_model->getDueFeeBystudent($student['class_id'], $student['section_id'], $id);
        $data['student_due_fee'] = $student_due_fee;
        $transport_fee = $this->studenttransportfee_model->getTransportFeeByStudent($student['student_session_id']);
        $data['transport_fee'] = $transport_fee;
        $examList = $this->examschedule_model->getExamByClassandSection($student['class_id'], $student['section_id']);
        $data['examSchedule'] = array();
        if (!empty($examList)) {
            $new_array = array();
            foreach ($examList as $ex_key => $ex_value) {
                $array = array();
                $x = array();
                $exam_id = $ex_value['exam_id'];
                $exam_subjects = $this->examschedule_model->getresultByStudentandExam($exam_id, $student['id']);
                foreach ($exam_subjects as $key => $value) {
                    $exam_array = array();
                    $exam_array['exam_schedule_id'] = $value['exam_schedule_id'];
                    $exam_array['exam_id'] = $value['exam_id'];
                    $exam_array['full_marks'] = $value['full_marks'];
                    $exam_array['passing_marks'] = $value['passing_marks'];
                    $exam_array['exam_name'] = $value['name'];
                    $exam_array['exam_type'] = $value['type'];
                    $exam_array['attendence'] = $value['attendence'];
                    $exam_array['get_marks'] = $value['get_marks'];
                    $x[] = $exam_array;
                }
                $array['exam_name'] = $ex_value['exam_name'];
                $array['exam_result'] = $x;
                $new_array[] = $array;
            }
            $data['examSchedule'] = $new_array;
        }
        $data['student'] = $student;

        return $this->api_success($data);
    }

    public function getfees()
    {
        $id = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();

        $category = $this->category_model->get();
        $data['categorylist'] = $category;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $paymentoption = $this->customlib->checkPaypalDisplay();
        $data['paymentoption'] = $paymentoption;
        $data['payment_method'] = !empty($this->payment_method);

        $student_id = $id;
        $student = $this->student_model->getStudentByClassSectionID($student_current_class->class_id, $student_current_class->section_id, $student_id);

        $class_id = $student_current_class->class_id;
        $section_id = $student_current_class->section_id;
        $data['title'] = 'Student Details';
        $student_due_fee = $this->studentfeemaster_model->getStudentFees($student_current_class->student_session_id);
        $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student_current_class->student_session_id);
        $data['student_discount_fee'] = $student_discount_fee;
        $data['student_due_fee'] = $student_due_fee;
        $data['student'] = $student;

        $transport_fees = [];
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $transport_fees = $this->studentfeemaster_model->getStudentTransportFees($student_current_class->student_session_id, $student['route_pickup_point_id']);
        }

        $data['transport_fees'] = $transport_fees;
        $student_processing_fee = $this->studentfeemaster_model->getStudentProcessingFees($student_current_class->student_session_id);

        $data['student_processing_fee'] = false;

        foreach ($student_processing_fee as $key => $processing_value) {
            if (!empty($processing_value->fees)) {
                $data['student_processing_fee'] = true;
            }
        }

        return $this->api_success($data);
    }

    public function getProcessingfees()
    {
        $id = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();

        $category = $this->category_model->get();
        $data['categorylist'] = $category;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $paymentoption = $this->customlib->checkPaypalDisplay();
        $data['paymentoption'] = $paymentoption;
        $data['payment_method'] = !empty($this->payment_method);

        $student_id = $id;
        $student = $this->student_model->getStudentByClassSectionID($student_current_class->class_id, $student_current_class->section_id, $student_id);
        $class_id = $student_current_class->class_id;
        $section_id = $student_current_class->section_id;
        $data['title'] = 'Student Details';
        $student_due_fee = $this->studentfeemaster_model->getStudentProcessingFees($student_current_class->student_session_id);
        $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student_current_class->student_session_id);
        $data['student_discount_fee'] = $student_discount_fee;
        $data['student_due_fee'] = $student_due_fee;
        $data['student'] = $student;
        $transport_fees = $this->studentfeemaster_model->getStudentTransportFees($student_current_class->student_session_id, $student['route_pickup_point_id']);

        $data['transport_fees'] = $transport_fees;

        return $this->api_success($data);
    }

    public function printFeesByGroupArray()
    {
        $data['sch_setting'] = $this->sch_setting_detail;
        $record = $this->input->post('data');
        $record_array = json_decode($record);
        $fees_array = array();
        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = $value->fee_groups_feetype_id;
            $fee_master_id = $value->fee_master_id;
            $fee_session_group_id = $value->fee_session_group_id;
            $fee_category = $value->fee_category;
            $trans_fee_id = $value->trans_fee_id;

            if ($fee_category == "transport") {
                $feeList = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                $feeList->fee_category = $fee_category;
            } else {
                $feeList = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                $feeList->fee_category = $fee_category;
            }

            $fees_array[] = $feeList;
        }
        $data['feearray'] = $fees_array;

        return $this->api_success($data);
    }

    public function getcollectfee()
    {
        $setting_result = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $record = $this->input->post('data');
        $record_array = json_decode($record);
        $fees_array = array();
        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = $value->fee_groups_feetype_id;
            $fee_master_id = $value->fee_master_id;
            $fee_session_group_id = $value->fee_session_group_id;
            $fee_category = $value->fee_category;
            $trans_fee_id = $value->trans_fee_id;

            if ($fee_category == "transport") {
                $feeList = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                $feeList->fee_category = $fee_category;
            } else {
                $feeList = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                $feeList->fee_category = $fee_category;
            }

            $fees_array[] = $feeList;
        }
        $data['feearray'] = $fees_array;

        return $this->api_success($data);
    }

    public function create_doc()
    {
        $this->form_validation->set_rules('first_title', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('first_doc', $this->lang->line('documents'), 'callback_handle_upload');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'first_title' => form_error('first_title'),
                'first_doc' => form_error('first_doc'),
            );
            return $this->api_error('Validation failed', $msg);
        } else {
            $student_id = $this->input->post('student_id');

            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $student_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    return $this->api_error('Error creating upload directory');
                }
                $img_name = $this->media_storage->fileupload("first_doc", $uploaddir);
                $first_title = $this->input->post('first_title');
                $data_img = array('student_id' => $student_id, 'title' => $first_title, 'doc' => $img_name);
                $this->student_model->adddoc($data_img);
            }

            return $this->api_success(null, $this->lang->line('success_message'));
        }
    }

    public function handle_upload()
    {
        $image_validate = $this->config->item('file_validate');
        $result = $this->filetype_model->get();
        if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {

            $file_type = $_FILES["first_doc"]['type'];
            $file_size = $_FILES["first_doc"]["size"];
            $file_name = $_FILES["first_doc"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['first_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload', 'File Type Not Allowed');
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload', 'Extension Not Allowed');
                return false;
            }

            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_upload', 'File size should be less than ' . number_format($result->file_size / 1048576, 2) . " MB");
                return false;
            }

            return true;
        }
        return true;
    }
}
