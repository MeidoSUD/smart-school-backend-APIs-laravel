<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Timetable extends Api_Controller
{
    public function __construct()
    {
        $this->public_methods = [];
        parent::__construct();
    }

    public function index()
    {
        try {
            $student = $this->session->userdata('student');
            if (empty($student)) {
                return $this->api_error('Student session not found');
            }
            
            $class_id = $student['class_id'] ?? null;
            $section_id = $student['section_id'] ?? null;
            $student_session_id = $student['student_session_id'] ?? null;
            
            if (!$class_id || !$section_id) {
                return $this->api_error('Student enrollment not found');
            }
            
            $current_session = $this->setting_model->getCurrentSession();
            
            // Get class_sections id
            $this->db->select('id');
            $this->db->where('class_id', $class_id);
            $this->db->where('section_id', $section_id);
            $class_section = $this->db->get('class_sections')->row();
            
            if (!$class_section) {
                return $this->api_success(['timetable' => []], 'No timetable found');
            }
            
            $class_section_id = $class_section->id;
            
            // Get timetable
            $this->db->select('class_timetable.*, subjects.name as subject_name, subjects.code as subject_code, staff.name as teacher_name');
            $this->db->from('class_timetable');
            $this->db->join('subjects', 'subjects.id = class_timetable.subject_id', 'left');
            $this->db->join('staff', 'staff.id = class_timetable.staff_id', 'left');
            $this->db->where('class_timetable.class_section_id', $class_section_id);
            $this->db->where('class_timetable.session_id', $current_session);
            $this->db->order_by('class_timetable.day', 'asc');
            $this->db->order_by('class_timetable.time_from', 'asc');
            
            $timetable = $this->db->get()->result_array();
            
            // Group by day
            $result = [];
            foreach ($timetable as $row) {
                $day = $row['day'];
                if (!isset($result[$day])) {
                    $result[$day] = [];
                }
                $result[$day][] = [
                    'id' => $row['id'],
                    'subject' => $row['subject_name'] ?? 'N/A',
                    'subject_code' => $row['subject_code'] ?? '',
                    'teacher' => $row['teacher_name'] ?? 'N/A',
                    'time_from' => $row['time_from'],
                    'time_to' => $row['time_to'],
                    'room' => $row['room_no'] ?? '',
                    'day' => $row['day']
                ];
            }
            
            return $this->api_success(['timetable' => $result]);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }
}