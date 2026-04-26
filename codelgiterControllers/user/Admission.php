<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Admission extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("onlinestudent_model");
    }

    /**
     * GET /api/user/admission
     * List all admissions (admin)
     */
    public function index()
    {
        $status = $this->input->get('status');
        $class_id = $this->input->get('class_id');
        $search = $this->input->get('search');
        
        $where = array();
        
        if ($status) {
            $where['form_status'] = $status;
        }
        
        if ($class_id) {
            $where['class_id'] = $class_id;
        }
        
        $admissions = $this->onlinestudent_model->get(null, null, $where);
        
        $result = array();
        foreach ($admissions as $row) {
            $result[] = $this->format_admission($row);
        }
        
        if ($search) {
            $result = array_filter($result, function($item) use ($search) {
                return stripos($item['firstname'], $search) !== false ||
                       stripos($item['reference_no'], $search) !== false ||
                       stripos($item['father_name'], $search) !== false;
            });
        }
        
        return $this->api_success(array_values($result));
    }

    /**
     * GET /api/user/admission/view/{id}
     * Get admission details
     */
    public function view()
    {
        $id = $this->uri->segment(4);
        
        if (!$id) {
            return $this->api_error('Admission ID is required');
        }
        
        $admission = $this->onlinestudent_model->get($id);
        
        if (!$admission) {
            return $this->api_error('Admission not found', null, 404);
        }
        
        return $this->api_success($this->format_admission($admission));
    }

    /**
     * POST /api/user/admission/update
     * Update admission status
     */
    public function update_post()
    {
        $id = $this->input->post('id');
        $form_status = $this->input->post('form_status');
        $admission_no = $this->input->post('admission_no');
        $remark = $this->input->post('remark');
        
        if (!$id) {
            return $this->api_error('ID is required');
        }
        
        if (!$form_status) {
            return $this->api_error('form_status is required');
        }
        
        $valid_statuses = array('submitted', 'approved', 'rejected');
        if (!in_array($form_status, $valid_statuses)) {
            return $this->api_error('Invalid status. Use: ' . implode(', ', $valid_statuses));
        }
        
        $update_data = array(
            'id' => $id,
            'form_status' => $form_status,
        );
        
        if ($admission_no) {
            $update_data['admission_no'] = $admission_no;
        }
        
        if ($remark) {
            $update_data['note'] = $remark;
        }
        
        $this->onlinestudent_model->edit($update_data);
        
        return $this->api_success(null, 'Admission updated successfully');
    }

    /**
     * POST /api/user/admission/delete
     * Delete admission
     */
    public function delete_post()
    {
        $id = $this->input->post('id');
        
        if (!$id) {
            return $this->api_error('ID is required');
        }
        
        $this->db->where('id', $id);
        $this->db->delete('online_admissions');
        
        return $this->api_success(null, 'Admission deleted successfully');
    }

    /**
     * GET /api/user/admission/statistics
     * Get admission statistics
     */
    public function statistics()
    {
        $this->db->select('form_status, COUNT(*) as count');
        $this->db->group_by('form_status');
        $query = $this->db->get('online_admissions');
        $status_counts = $query->result_array();
        
        $stats = array(
            'total' => 0,
            'submitted' => 0,
            'approved' => 0,
            'rejected' => 0,
        );
        
        foreach ($status_counts as $row) {
            $stats[$row['form_status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }
        
        return $this->api_success($stats);
    }

    private function format_admission($row)
    {
        return array(
            'id' => $row['id'],
            'reference_no' => $row['reference_no'],
            'admission_no' => $row['admission_no'],
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'],
            'lastname' => $row['lastname'],
            'dob' => $row['dob'],
            'gender' => $row['gender'],
            'email' => $row['email'],
            'mobileno' => $row['mobileno'],
            'class' => $row['class'] ?? '',
            'section' => $row['section'] ?? '',
            'category' => $row['category'] ?? '',
            'religion' => $row['religion'],
            'father_name' => $row['father_name'],
            'father_phone' => $row['father_phone'],
            'father_occupation' => $row['father_occupation'],
            'mother_name' => $row['mother_name'],
            'mother_phone' => $row['mother_phone'],
            'mother_occupation' => $row['mother_occupation'],
            'guardian_is' => $row['guardian_is'],
            'guardian_name' => $row['guardian_name'],
            'guardian_relation' => $row['guardian_relation'],
            'guardian_phone' => $row['guardian_phone'],
            'guardian_email' => $row['guardian_email'],
            'current_address' => $row['current_address'],
            'permanent_address' => $row['permanent_address'],
            'bank_account_no' => $row['bank_account_no'],
            'bank_name' => $row['bank_name'],
            'ifsc_code' => $row['ifsc_code'],
            'blood_group' => $row['blood_group'],
            'house_name' => $row['house_name'],
            'form_status' => $row['form_status'],
            'paid_status' => $row['paid_status'],
            'submitted_date' => $row['submit_date'],
            'created_at' => $row['created_at'],
        );
    }
}