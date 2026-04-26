<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Exam extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $stuid = $this->session->userdata('student');
        $stu_record = $this->student_model->getRecentRecord($stuid['student_id']);
        $data['class_id'] = $stu_record['class_id'];
        $data['section_id'] = $stu_record['section_id'];
        $exam_result = $this->examschedule_model->getExamByClassandSection($data['class_id'], $data['section_id']);
        $data['examlist'] = $exam_result;

        return $this->api_success($data);
    }

    public function view($id)
    {
        $data['title'] = 'Exam List';
        $exam = $this->exam_model->get($id);
        $data['exam'] = $exam;

        return $this->api_success($data);
    }

    public function getByFeecategory()
    {
        $feecategory_id = $this->input->get('feecategory_id');
        $data = $this->feetype_model->getTypeByFeecategory($feecategory_id);

        return $this->api_success($data);
    }

    public function getStudentCategoryFee()
    {
        $type = $this->input->post('type');
        $class_id = $this->input->post('class_id');
        $data = $this->exam_model->getTypeByFeecategory($type, $class_id);
        if (empty($data)) {
            $status = 'fail';
        } else {
            $status = 'success';
        }

        return $this->api_success($data, null, $status);
    }

    public function examSearch()
    {
        $data['title'] = 'Search exam';

        if ($this->input->server('REQUEST_METHOD') == "POST") {
            $search = $this->input->post('search');
            if ($search == "search_filter") {
                $data['exp_title'] = 'exam Result From ' . $this->input->post('date_from') . " To " . $this->input->post('date_to');
                $date_from = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_from')));
                $date_to = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_to')));
                $resultList = $this->exam_model->search("", $date_from, $date_to);
                $data['resultList'] = $resultList;
            } else {
                $data['exp_title'] = 'exam Result';
                $search_text = $this->input->post('search_text');
                $resultList = $this->exam_model->search($search_text, "", "");
                $data['resultList'] = $resultList;
            }

            return $this->api_success($data);
        } else {
            return $this->api_success($data);
        }
    }

    public function examresult()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_session_id = $student_current_class->student_session_id;
        $marks_division = $this->marksdivision_model->get();
        $data['marks_division'] = $marks_division;
        $data['exam_result'] = $this->examgroupstudent_model->searchStudentExams($student_session_id, true, true);
        $data['exam_grade'] = $this->grade_model->getGradeDetails();

        return $this->api_success($data);
    }
}
