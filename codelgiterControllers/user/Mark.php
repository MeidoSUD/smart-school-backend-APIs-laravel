<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mark extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $session = $this->setting_model->getCurrentSession();
        $data['title'] = 'Exam Marks';
        $data['exam_id'] = "";
        $data['class_id'] = "";
        $data['section_id'] = "";
        $exam = $this->exam_model->get();
        $class = $this->class_model->get();
        $data['examlist'] = $exam;
        $data['classlist'] = $class;
        $feecategory = $this->feecategory_model->get();
        $data['feecategorylist'] = $feecategory;
        $stuid = $this->session->userdata('student');
        $stu_record = $this->student_model->getRecentRecord($stuid['student_id']);
        $data['class_id'] = $stu_record['class_id'];
        $data['section_id'] = $stu_record['section_id'];
        $reportcard = $this->examschedule_model->getExamByClassandSection($data['class_id'], $data['section_id']);
        $class_id = $stu_record['class_id'];
        $section_id = $stu_record['section_id'];
        foreach ($reportcard as $data) {
            $exam_id = $data['exam_id'];
        }
        $data['class_id'] = $stu_record['class_id'];
        $data['section_id'] = $stu_record['section_id'];
        $examSchedule = $this->examschedule_model->getDetailbyClsandSection($class_id, $section_id, $exam_id);
        $studentList = $this->student_model->searchByClassSection($class_id, $section_id);
        $data['examSchedule'] = array();
        if (!empty($examSchedule)) {
            $new_array = array();
            $data['examSchedule']['status'] = "yes";
            foreach ($studentList as $stu_key => $stu_value) {
                $array = array();
                $array['student_id'] = $stu_value['id'];
                $array['firstname'] = $stu_value['firstname'];
                $array['lastname'] = $stu_value['lastname'];
                $array['admission_no'] = $stu_value['admission_no'];
                $array['dob'] = $stu_value['dob'];
                $array['father_name'] = $stu_value['father_name'];
                $x = array();
                foreach ($examSchedule as $ex_key => $ex_value) {
                    $exam_array = array();
                    $exam_array['exam_schedule_id'] = $ex_value['id'];
                    $exam_array['exam_id'] = $ex_value['exam_id'];
                    $exam_array['full_marks'] = $ex_value['full_marks'];
                    $exam_array['passing_marks'] = $ex_value['passing_marks'];
                    $exam_array['exam_name'] = $ex_value['name'];
                    $exam_array['exam_type'] = $ex_value['type'];
                    $student_exam_result = $this->examresult_model->get_result($ex_value['id'], $stu_value['id']);
                    if (empty($student_exam_result)) {
                        $data['examSchedule']['status'] = "no";
                    } else {
                        $exam_array['attendence'] = $student_exam_result->attendence;
                        $exam_array['get_marks'] = $student_exam_result->get_marks;
                    }
                    $x[] = $exam_array;
                }
                $array['exam_array'] = $x;
                $new_array[] = $array;
            }
            $data['examSchedule']['result'] = $new_array;
        } else {
            $s = array('status' => 'no');
            $data['examSchedule'] = $s;
        }

        return $this->api_success($data);
    }

    public function marklist()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student = $this->student_model->get($student_id);
        $class_id = $student['class_id'];
        $section_id = $student['section_id'];
        $data['title'] = 'Student Details';
        $gradeList = $this->grade_model->get();
        $data['gradeList'] = $gradeList;
        $student_due_fee = $this->studentfee_model->getDueFeeBystudent($student['class_id'], $student['section_id'], $student_id);
        $data['student_due_fee'] = $student_due_fee;
        $transport_fee = $this->studenttransportfee_model->getTransportFeeByStudent($student['student_session_id']);
        $data['transport_fee'] = $transport_fee;
        $examList = $this->examschedule_model->getExamByClassandSection($student['class_id'], $student['section_id']);
        $data['examSchedule'] = array();
        if (!empty($examList)) {
            $new_array = array();
            $data['examSchedule']['status'] = "yes";
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
                $array['exam_name'] = $ex_value['name'];
                $array['exam_result'] = $x;
                $new_array[] = $array;
            }
            $data['examSchedule'] = $new_array;
        }
        $data['student'] = $student;

        return $this->api_success($data);
    }

    public function view($id)
    {
        $data['title'] = 'Mark List';
        $mark = $this->mark_model->get($id);
        $data['mark'] = $mark;

        return $this->api_success($data);
    }
}
