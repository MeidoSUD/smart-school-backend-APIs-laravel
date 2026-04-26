<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Offlinepayment extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $data['student'] = $this->student_model->get($student_id);
        $data['payment_list'] = $this->offlinepayment_model->getStudentPayments($student_current_class->student_session_id);

        return $this->api_success($data);
    }

    public function add()
    {
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean');
        $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            return $this->api_error('Validation failed');
        } else {
            $student_session_id = $this->session->userdata['current_class']['student_session_id'];

            $data = array(
                'student_session_id' => $student_session_id,
                'amount' => $this->input->post('amount'),
                'payment_mode' => $this->input->post('payment_mode'),
                'payment_date' => date('Y-m-d'),
                'status' => 'pending'
            );

            $result = $this->offlinepayment_model->add($data);

            return $this->api_success(['id' => $result], 'Payment request submitted successfully');
        }
    }
}
