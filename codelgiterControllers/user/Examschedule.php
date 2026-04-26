<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ExamSchedule extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_session_id = $student_current_class->student_session_id;
        $examSchedule = $this->examgroupstudent_model->studentExams($student_session_id);
        $data['examSchedule'] = $examSchedule;

        return $this->api_success($data);
    }

    public function getexamscheduledetail()
    {
        $subjects = array();
        $exam_id = $this->input->post('exam_id');
        $subjects['subject_list'] = $this->batchsubject_model->getExamstudentSubjects($exam_id);

        return $this->api_success($subjects);
    }
}
