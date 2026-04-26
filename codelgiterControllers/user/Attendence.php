<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Attendence extends Api_Controller
{
    public function __construct()
    {
        $this->public_methods = [];
        parent::__construct();
    }

    public function index()
    {
        try {
            $setting = $this->db->get('settings')->row();
            
            $data = [
                'attendence_type' => $setting->attendence_type ?? 'day',
                'language' => $this->session->userdata('language')
            ];
            
            return $this->api_success($data);
            
        } catch (Exception $e) {
            return $this->api_error($e->getMessage());
        }
    }

    public function getdaysubattendence()
    {
        try {
            $date = $this->input->post('date') ?: $this->input->get('date');
            $date = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
            
            $attendencetypes = $this->db->get('attendence_type')->result();
            
            $student = $this->session->userdata('student');
            if (empty($student)) {
                return $this->api_error('Student session not found');
            }
            
            $student_session_id = $student['student_session_id'] ?? null;
            $class_id = $student['class_id'] ?? null;
            $section_id = $student['section_id'] ?? null;
            
            if (!$student_session_id || !$class_id || !$section_id) {
                return $this->api_error('Student enrollment not found');
            }
            
            $day = date('l', strtotime($date));
            
            // Get attendance for this day
            $this->db->select('student_attendences.*, attendence_type.type as attendence_type');
            $this->db->from('student_attendences');
            $this->db->join('attendence_type', 'attendence_type.id = student_attendences.attendence_type_id');
            $this->db->where('student_attendences.student_session_id', $student_session_id);
            $this->db->where('student_attendences.date', $date);
            $attendance = $this->db->get()->result();
            
            $result = [
                'attendencetypeslist' => $attendencetypes,
                'attendence' => $attendance
            ];
            
            return $this->api_success($result);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }

    public function getAttendence()
    {
        try {
            $start = $this->input->get('start') ?: date('Y-m-01');
            $end = $this->input->get('end') ?: date('Y-m-t');
            
            $student = $this->session->userdata('student');
            if (empty($student)) {
                return $this->api_error('Student session not found');
            }
            
            $student_session_id = $student['student_session_id'] ?? null;
            
            if (!$student_session_id) {
                return $this->api_error('Student enrollment not found');
            }
            
            $this->db->select('student_attendences.date, attendence_type.type, student_attendences.remark');
            $this->db->from('student_attendences');
            $this->db->join('attendence_type', 'attendence_type.id = student_attendences.attendence_type_id');
            $this->db->where('student_attendences.student_session_id', $student_session_id);
            $this->db->where('student_attendences.date >=', $start);
            $this->db->where('student_attendences.date <=', $end);
            $this->db->order_by('student_attendences.date', 'asc');
            $attendance = $this->db->get()->result();
            
            $eventdata = [];
            foreach ($attendance as $row) {
                $color = '#27ab00'; // default green
                if ($row->type == 'Absent') {
                    $color = '#fa2601';
                } elseif ($row->type == 'Late') {
                    $color = '#ffeb00';
                } elseif ($row->type == 'Holiday') {
                    $color = '#a7a7a7';
                } elseif ($row->type == 'Half Day') {
                    $color = '#fa8a00';
                }
                
                $eventdata[] = [
                    'title' => $this->lang->line(strtolower($row->type)) ?? $row->type,
                    'start' => $row->date,
                    'end' => $row->date,
                    'description' => $row->remark,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'event_type' => $row->type,
                ];
            }
            
            return $this->api_success($eventdata);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }

    public function getevents()
    {
        try {
            $this->db->select('calendar_events.*');
            $this->db->from('calendar_events');
            $this->db->where('calendar_events.status', 'yes');
            $this->db->where('calendar_events.event_type !=', 'private');
            $result = $this->db->get()->result_array();
            
            $eventdata = [];
            foreach ($result as $value) {
                $eventdata[] = [
                    'title' => $value['event_title'],
                    'start' => $value['start_date'],
                    'end' => $value['end_date'],
                    'description' => $value['event_description'],
                    'id' => $value['id'],
                    'backgroundColor' => $value['event_color'],
                    'borderColor' => $value['event_color'],
                    'event_type' => $value['event_type'],
                ];
            }
            
            return $this->api_success($eventdata);
            
        } catch (Exception $e) {
            return $this->api_error('Error: ' . $e->getMessage());
        }
    }
}