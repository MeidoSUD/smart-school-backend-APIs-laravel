<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Admission extends Api_Controller
{
    public $sch_setting_detail;

    public function __construct()
    {
        $this->public_methods = ['index', 'form_config', 'submit', 'status', 'classes', 'sections'];
        parent::__construct();
        
        $this->load->model("onlinestudent_model");
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * GET /api/admission
     * Check if online admission is enabled
     */
    public function index()
    {
        $form_status = $this->setting_model->getOnlineAdmissionStatus();
        
        $data = [
            'enabled' => (bool) $form_status->online_admission,
            'instructions' => $this->sch_setting_detail->online_admission_instruction ?? '',
            'conditions' => $this->sch_setting_detail->online_admission_conditions ?? '',
            'amount' => $this->sch_setting_detail->online_admission_amount ?? 0,
            'payment_enabled' => ($this->sch_setting_detail->online_admission_payment ?? 'no') === 'yes',
        ];
        
        return $this->api_success($data);
    }

    /**
     * GET /api/admission/form_config
     * Get form fields configuration and available options
     */
    public function form_config()
    {
        $fields = get_onlineadmission_editable_fields();
        $inserted_fields = $this->onlinestudent_model->getformfields();
        
        $genderList = $this->customlib->getGender();
        $classlist = $this->class_model->getAll();
        $category = $this->category_model->get();
        $bloodgroup = $this->blood_group;
        $houses = $this->student_model->gethouselist();
        $custom_fields = $this->customfield_model->getByBelong('students');
        
        $data = [
            'fields' => $fields,
            'enabled_fields' => $inserted_fields,
            'gender_list' => $genderList,
            'class_list' => $classlist,
            'category_list' => $category,
            'blood_group_list' => $bloodgroup,
            'house_list' => $houses,
            'custom_fields' => $custom_fields,
        ];
        
        return $this->api_success($data);
    }

    /**
     * GET /api/admission/classes
     * Get available classes for admission
     */
    public function classes()
    {
        $classlist = $this->class_model->getAll();
        return $this->api_success($classlist);
    }

    /**
     * GET /api/admission/sections
     * Get sections for a specific class
     */
    public function sections()
    {
        $class_id = $this->input->get('class_id');
        
        if (!$class_id) {
            return $this->api_error('class_id is required');
        }
        
        $sections = $this->section_model->getClassBySectionAll($class_id);
        return $this->api_success($sections);
    }

    /**
     * POST /api/admission/submit
     * Submit admission form
     */
    public function submit()
    {
        $form_status = $this->setting_model->getOnlineAdmissionStatus();
        
        if (!$form_status->online_admission) {
            return $this->api_error('Online admission is currently disabled');
        }
        
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
        
        if ($this->customlib->getfieldstatus('student_email')) {
            $this->form_validation->set_rules('email', 'Email', array(
                'trim', 'valid_email', 'required',
                array('check_student_email_exists', array($this->onlinestudent_model, 'check_student_email_exists'))
            ));
        }
        
        if ($this->customlib->getfieldstatus('if_guardian_is')) {
            $this->form_validation->set_rules('guardian_is', 'Guardian', 'trim|required|xss_clean');
            $this->form_validation->set_rules('guardian_name', 'Guardian Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('guardian_relation', 'Guardian Relation', 'trim|required|xss_clean');
        }
        
        if ($this->form_validation->run() == false) {
            $errors = array();
            foreach ($this->form_validation->error_array() as $field => $error) {
                $errors[$field] = $error;
            }
            return $this->api_error('Validation failed', $errors);
        }
        
        $firstname = $this->input->post('firstname');
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        
        $data = array(
            'firstname' => $firstname,
            'class_section_id' => $section_id,
            'dob' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
            'gender' => $this->input->post('gender'),
        );
        
        $fields_map = array(
            'middlename', 'lastname', 'category', 'religion', 'cast', 'mobileno', 'email',
            'current_address', 'permanent_address', 'bank_account_no', 'bank_name', 'ifsc_code',
            'adhar_no', 'samagra_id', 'rte', 'previous_school', 'note'
        );
        
        foreach ($fields_map as $field) {
            $post_value = $this->input->post($field);
            if ($this->customlib->getfieldstatus($field) && $post_value) {
                $data[$field] = $post_value;
            }
        }
        
        if ($this->customlib->getfieldstatus('if_guardian_is')) {
            $data['guardian_is'] = $this->input->post('guardian_is');
            $data['guardian_name'] = $this->input->post('guardian_name');
            $data['guardian_relation'] = $this->input->post('guardian_relation');
            $data['guardian_phone'] = $this->input->post('guardian_phone');
            
            if ($this->customlib->getfieldstatus('guardian_occupation')) {
                $data['guardian_occupation'] = $this->input->post('guardian_occupation');
            }
            if ($this->customlib->getfieldstatus('guardian_email')) {
                $data['guardian_email'] = $this->input->post('guardian_email');
            }
            if ($this->customlib->getfieldstatus('guardian_address')) {
                $data['guardian_address'] = $this->input->post('guardian_address');
            }
        }
        
        $father_fields = array('father_name', 'father_phone', 'father_occupation');
        foreach ($father_fields as $field) {
            $post_value = $this->input->post($field);
            if ($this->customlib->getfieldstatus($field) && $post_value) {
                $data[$field] = $post_value;
            }
        }
        
        $mother_fields = array('mother_name', 'mother_phone', 'mother_occupation');
        foreach ($mother_fields as $field) {
            $post_value = $this->input->post($field);
            if ($this->customlib->getfieldstatus($field) && $post_value) {
                $data[$field] = $post_value;
            }
        }
        
        if ($this->customlib->getfieldstatus('is_student_house')) {
            $data['school_house_id'] = $this->input->post('school_house_id');
        }
        if ($this->customlib->getfieldstatus('is_blood_group')) {
            $data['blood_group'] = $this->input->post('blood_group');
        }
        
        do {
            $reference_no = mt_rand(100000, 999999);
            $refence_status = $this->onlinestudent_model->checkreferenceno($reference_no);
        } while ($refence_status);
        
        $data['reference_no'] = $reference_no;
        
        $custom_field_post = $this->input->post("custom_fields[students]");
        $custom_value_array = array();
        
        if (!empty($custom_field_post)) {
            foreach ($custom_field_post as $key => $value) {
                $check_field_type = $this->input->post("custom_fields[students][" . $key . "]");
                $field_value = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                
                $array_custom = array(
                    'belong_table_id' => 0,
                    'custom_field_id' => $key,
                    'field_value' => $field_value,
                );
                $custom_value_array[] = $array_custom;
            }
        }
        
        $insert_id = $this->onlinestudent_model->add($data);
        
        if (!empty($custom_value_array)) {
            $this->customfield_model->onlineadmissioninsertRecord($custom_value_array, $insert_id);
        }
        
        $response = array(
            'admission_id' => $insert_id,
            'reference_no' => $reference_no,
            'message' => 'Registration successful. Please note your reference number for further communication.'
        );
        
        return $this->api_success($response, 'Admission form submitted successfully');
    }

    /**
     * GET /api/admission/status
     * Check admission status by reference number
     */
    public function status()
    {
        $reference_no = $this->input->get('reference_no');
        
        if (!$reference_no) {
            return $this->api_error('reference_no is required');
        }
        
        $admission = $this->onlinestudent_model->getAdmissionData($reference_no);
        
        if (!$admission) {
            return $this->api_error('No admission found with this reference number', null, 404);
        }
        
        $data = array(
            'reference_no' => $admission['reference_no'],
            'firstname' => $admission['firstname'],
            'lastname' => $admission['lastname'],
            'form_status' => $admission['form_status'],
            'paid_status' => $admission['paid_status'],
            'submitted_date' => $admission['submit_date'],
        );
        
        return $this->api_success($data);
    }
}