<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Teacher extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Teachers';
        $data['teachers'] = $teachers = array();
        $data['class_id'] = $class_id = $this->current_classSection->class_id;
        $data['section_id'] = $section_id = $this->current_classSection->section_id;
        $data['resultlist'] = $this->subjecttimetable_model->getTeacherByClassandSection($class_id, $section_id);
        $subject = array();
        foreach ($data['resultlist'] as $value) {
            $teachers[$value->staff_id][] = $value;
        }
        $session_id = $this->session->userdata('student');
        $data['user_id'] = $session_id['id'];
        $data['role'] = $session_id['role'];

        $data['teacherlist'] = $teachers;
        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $user_ratedstafflist = $this->staff_model->get_RatedStaffByUser($session_id['id']);
        $data['user_ratedstafflist'] = $user_ratedstafflist;
        $get_ratingbystudent = $this->staff_model->get_ratingbyuser($data['user_id'], 'student');

        if ($data['role'] == "student") {
            foreach ($get_ratingbystudent as $value) {
                $data['reviews'][$value['staff_id']] = $value['rate'];
                $data['comment'][$value['staff_id']] = $value['comment'];
            }
        } elseif ($data['role'] == "parent") {
            $all_rating = $this->staff_model->all_rating();

            $data['rate_canview'] = 0;
            foreach ($all_rating as $value) {
                if ($value['total'] >= 3) {
                    $r = ($value['rate'] / $value['total']);

                    $data['avg_rate'][$value['staff_id']] = $r;
                    $data['rate_canview'] = 1;
                } else {
                    $data['avg_rate'][$value['staff_id']] = 0;
                }
                $data['reviews'][$value['staff_id']] = $value['total'];
            }
        }

        return $this->api_success($data);
    }

    public function getSubjctByClassandSection()
    {
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $data = $this->teachersubject_model->getSubjectByClsandSection($class_id, $section_id);

        return $this->api_success(['subjects' => $data]);
    }

    public function getSubjectTeachers()
    {
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $dt = $this->classsection_model->getDetailbyClassSection($class_id, $section_id);
        $data = $this->teachersubject_model->getDetailByclassAndSection($dt['id']);

        return $this->api_success(['teachers' => $data]);
    }

    public function view($id)
    {
        $data['title'] = 'Teacher List';
        $teacher = $this->teacher_model->get($id);
        $data['teacher'] = $teacher;

        return $this->api_success($data);
    }

    public function rating()
    {
        $this->form_validation->set_rules('comment', $this->lang->line('comment'), 'required');
        $this->form_validation->set_rules('rate', $this->lang->line('rating'), 'required');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'comment' => form_error('comment'),
                'rate' => form_error('rate'),
            );
            return $this->api_error('Validation failed', $msg);
        } else {
            $data['staff_id'] = $this->input->post('staff_id');
            $data['comment'] = $this->input->post('comment');
            $data['rate'] = $this->input->post('rate');
            $data['user_id'] = $this->input->post('user_id');
            $data['role'] = $this->input->post('role');
            $this->teacher_model->rating($data);
            return $this->api_success(null, $this->lang->line('rating_successfully_saved'));
        }
    }
}
