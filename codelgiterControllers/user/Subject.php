<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Subject extends Api_Controller
{
    public function __construct()
    {
        $this->public_methods = [];
        parent::__construct();
        
        $this->load->model('setting_model');
    }

    public function index()
    {
        try {
            // Get student session data
            $student = $this->session->userdata('student');
            
            if (empty($student)) {
                return $this->api_unauthorized('Student session not found. Please login again.');
            }
            
            $student_id = $student['student_id'] ?? null;
            
            if (empty($student_id)) {
                return $this->api_error('Student ID not found in session.');
            }
            
            // Get current session
            $current_session = $this->setting_model->getCurrentSession();
            
            // Get student's class and section
            $this->db->select('class_id, section_id');
            $this->db->where('student_id', $student_id);
            $this->db->where('session_id', $current_session);
            $this->db->limit(1);
            $stu_record = $this->db->get('student_session')->row_array();
            
            if (empty($stu_record)) {
                return $this->api_success(['subjects' => []], 'No enrollment found for current session');
            }
            
            // Fetch subjects using the proper model
            $this->load->model('subjectgroup_model');
            $subjects = $this->subjectgroup_model->getAllsubjectByClassSection($stu_record['class_id'], $stu_record['section_id']);
            
            return $this->api_success(['subjects' => $subjects]);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        try {
            $this->db->where('id', $id);
            $subject = $this->db->get('subjects')->row_array();
            
            if (empty($subject)) {
                return $this->api_not_found('Subject not found');
            }
            
            return $this->api_success(['subject' => $subject]);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }

    public function getSubjctByClassandSection()
    {
        try {
            $class_id = $this->input->post('class_id') ?: $this->input->get('class_id');
            $section_id = $this->input->post('section_id') ?: $this->input->get('section_id');
            
            if (empty($class_id) || empty($section_id)) {
                return $this->api_error('class_id and section_id are required');
            }
            
            $current_session = $this->setting_model->getCurrentSession();
            
            $this->db->select('teacher_subjects.*, staff.name as teacher_name, staff.surname, subjects.name, subjects.type, subjects.code');
            $this->db->from('teacher_subjects');
            $this->db->join('subjects', 'subjects.id = teacher_subjects.subject_id');
            $this->db->join('staff', 'staff.id = teacher_subjects.teacher_id');
            $this->db->join('class_sections', 'class_sections.id = teacher_subjects.class_section_id');
            $this->db->where('class_sections.class_id', $class_id);
            $this->db->where('class_sections.section_id', $section_id);
            $this->db->where('teacher_subjects.session_id', $current_session);
            
            $subjects = $this->db->get()->result_array();
            
            return $this->api_success(['subjects' => $subjects]);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }
}