<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentfee extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('smsgateway');
    }

    public function index()
    {
        $data['title'] = 'student fee';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        return $this->api_success($data);
    }

    public function search()
    {
        $data['title'] = 'Student Search';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        if ($this->input->server('REQUEST_METHOD') == "GET") {
            return $this->api_success($data);
        } else {
            $class = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $search = $this->input->post('search');
            $search_text = $this->input->post('search_text');

            if (isset($search)) {
                if ($search == 'search_filter') {
                    $resultlist = $this->student_model->searchByClassSection($class, $section);
                    $data['resultlist'] = $resultlist;
                } else if ($search == 'search_full') {
                    $resultlist = $this->student_model->searchFullText($search_text);
                    $data['resultlist'] = $resultlist;
                }
            }

            return $this->api_success($data);
        }
    }

    public function feesearch()
    {
        $data['title'] = 'student fee';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $feecategory = $this->feecategory_model->get();
        $data['feecategorylist'] = $feecategory;

        $this->form_validation->set_rules('feecategory_id', 'Fee Category', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            return $this->api_success($data);
        } else {
            $data['student_due_fee'] = array();
            $feecategory_id = $this->input->post('feecategory_id');
            $feetype_id = $this->input->post('feetype_id');
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $student_due_fee = $this->studentfee_model->getDueStudentFees($feetype_id, $class_id, $section_id);
            $data['student_due_fee'] = $student_due_fee;

            return $this->api_success($data);
        }
    }

    public function reportbyname()
    {
        $data['title'] = 'student fee';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        if ($this->input->server('REQUEST_METHOD') == "GET") {
            return $this->api_success($data);
        } else {
            $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
            $this->form_validation->set_rules('student_id', 'Student', 'trim|required|xss_clean');

            if ($this->form_validation->run() == false) {
                $errors = array(
                    'section_id' => form_error('section_id'),
                    'class_id' => form_error('class_id'),
                    'student_id' => form_error('student_id'),
                );
                return $this->api_error('Validation failed', $errors);
            } else {
                $data['student_due_fee'] = array();
                $class_id = $this->input->post('class_id');
                $section_id = $this->input->post('section_id');
                $student_id = $this->input->post('student_id');
                $student_due_fee = $this->studentfee_model->getDueFeeBystudent($class_id, $section_id, $student_id);
                $data['student'] = $this->student_model->getRecentRecord($student_id);
                $data['student_due_fee'] = $student_due_fee;
                $data['class_id'] = $class_id;
                $data['section_id'] = $section_id;
                $data['student_id'] = $student_id;

                return $this->api_success($data);
            }
        }
    }

    public function reportbyclass()
    {
        $data['title'] = 'student fee';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        if ($this->input->server('REQUEST_METHOD') == "GET") {
            return $this->api_success($data);
        } else {
            $student_fees_array = array();
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $student_result = $this->student_model->searchByClassSection($class_id, $section_id);
            $data['student_due_fee'] = array();

            if (!empty($student_result)) {
                foreach ($student_result as $key => $student) {
                    $student_array = array();
                    $student_array['student_detail'] = $student;
                    $student_session_id = $student['student_session_id'];
                    $student_id = $student['id'];
                    $student_due_fee = $this->studentfee_model->getDueFeeBystudentSection($class_id, $section_id, $student_session_id);
                    $student_array['fee_detail'] = $student_due_fee;
                    $student_fees_array[$student['id']] = $student_array;
                }
            }

            $data['class_id'] = $class_id;
            $data['section_id'] = $section_id;
            $data['student_fees_array'] = $student_fees_array;

            return $this->api_success($data);
        }
    }

    public function view($id)
    {
        $data['title'] = 'studentfee List';
        $studentfee = $this->studentfee_model->get($id);
        $data['studentfee'] = $studentfee;

        return $this->api_success($data);
    }

    public function deleteFee()
    {
        $id = $this->input->post('feeid');
        $this->studentfee_model->remove($id);

        return $this->api_success(null, 'Fee deleted successfully');
    }

    public function addfee($id)
    {
        $data['title'] = 'Student Detail';
        $student = $this->student_model->get($id);
        $data['student'] = $student;
        $student_due_fee = $this->studentfee_model->getDueFeeBystudent($student['class_id'], $student['section_id'], $id);
        $data['student_due_fee'] = $student_due_fee;
        $transport_fee = $this->studenttransportfee_model->getTransportFeeByStudent($student['student_session_id']);
        $data['transport_fee'] = $transport_fee;

        return $this->api_success($data);
    }

    public function deleteTransportFee()
    {
        $id = $this->input->post('feeid');
        $this->studenttransportfee_model->remove($id);

        return $this->api_success(null, 'Transport fee deleted successfully');
    }

    public function add_Ajaxfee()
    {
        $this->form_validation->set_rules('fee_master_id', 'Fee Master', 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_session_id', 'Student', 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_discount', 'Discount', 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_fine', 'Fine', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'amount' => form_error('amount'),
                'fee_master_id' => form_error('fee_master_id'),
                'student_session_id' => form_error('student_session_id'),
                'amount_discount' => form_error('amount_discount'),
                'amount_fine' => form_error('amount_fine'),
            );
            return $this->api_error('Validation failed', $data);
        } else {
            $data = array(
                'amount' => $this->input->post('amount'),
                'date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'student_session_id' => $this->input->post('student_session_id'),
                'amount_discount' => $this->input->post('amount_discount'),
                'amount_fine' => $this->input->post('amount_fine'),
                'description' => $this->input->post('description'),
                'feemaster_id' => $this->input->post('fee_master_id'),
            );

            $inserted_id = $this->studentfee_model->add($data);
            $result = $this->smsgateway->StudentAddFeesMSG($inserted_id);

            return $this->api_success(['id' => $inserted_id], 'Fee added successfully');
        }
    }

    public function add_AjaxTransportfee()
    {
        $this->form_validation->set_rules('student_session_id', 'Student', 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_fine', 'Fine', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'transport_amount' => form_error('amount'),
                'transport_student_session_id' => form_error('student_session_id'),
                'transport_amount_discount' => form_error('amount_discount'),
                'transport_amount_fine' => form_error('amount_fine'),
            );
            return $this->api_error('Validation failed', $data);
        } else {
            $data = array(
                'amount' => $this->input->post('amount'),
                'date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'student_session_id' => $this->input->post('student_session_id'),
                'amount_discount' => "",
                'amount_fine' => $this->input->post('amount_fine'),
                'description' => $this->input->post('description'),
            );

            $inserted_id = $this->studenttransportfee_model->add($data);

            return $this->api_success(['id' => $inserted_id], 'Transport fee added successfully');
        }
    }

    public function searchpayment()
    {
        $data['title'] = 'Search With Payment ID';

        if ($this->input->server('REQUEST_METHOD') == "POST") {
            $search = $this->input->post('search');
            if ($search == "search_filter") {
                $data['exp_title'] = 'Transaction';
                $paymentid = $this->input->post('paymentid');
                $expenseList = $this->studenttransportfee_model->getfeeByID($paymentid);
                $feeList = $this->studentfee_model->getFeeByInvoice($paymentid);
                $data['expenseList'] = $expenseList;
                $data['feeList'] = $feeList;
            }

            return $this->api_success($data);
        } else {
            return $this->api_success($data);
        }
    }
}
