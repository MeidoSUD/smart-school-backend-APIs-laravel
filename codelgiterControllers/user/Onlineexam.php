<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Onlineexam extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_id = $this->customlib->getStudentSessionUserID();
        $data['student'] = $this->student_model->get($student_id);
        $data['examList'] = $this->onlineexam_model->getStudentExamList($student_current_class->class_id, $student_current_class->section_id);

        return $this->api_success($data);
    }

    public function exam_detail($id)
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $data['result'] = $this->onlineexam_model->getExamDetail($id);
        $data['questions'] = $this->onlineexamquestion_model->getExamQuestions($id);

        return $this->api_success($data);
    }

    public function submit()
    {
        $this->form_validation->set_rules('onlineexam_id', 'Exam', 'trim|required|xss_clean');
        $this->form_validation->set_rules('answers', 'Answers', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            return $this->api_error('Validation failed');
        } else {
            $answers = json_decode($this->input->post('answers'), true);
            $onlineexam_id = $this->input->post('onlineexam_id');
            $student_id = $this->customlib->getStudentSessionUserID();

            $data = array(
                'onlineexam_id' => $onlineexam_id,
                'student_id' => $student_id,
                'answers' => json_encode($answers),
                'submitted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->onlineexam_model->submitExam($data);

            return $this->api_success(['result' => $result], 'Exam submitted successfully');
        }
    }
}
