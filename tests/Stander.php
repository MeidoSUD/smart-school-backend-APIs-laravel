<?php

// if (!defined('BASEPATH')) {
//     exit('No direct script access allowed');
// }

class Stander extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Customlib');
        $this->sch_current_session = $this->setting_model->getCurrentSession();
        $this->staff_id            = $this->customlib->getStaffID();
        $this->load->library("datatables");
    }
    public function test()
    {
        $this->load->view('layout/header');
        $this->load->view('admin/stander/outcome');
        $this->load->view('layout/footer');
    }
    public function index()
    {
        if (!($this->rbac->hasPrivilege('manage_syllabus_status', 'can_view'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander');
        $this->session->set_userdata('sub_menu', 'admin/stander');
        $data                     = array();
        $class                    = $this->class_model->get();
        $data['classlist']        = $class;
        $data['class_id']         = "";
        $data['section_id']       = "";
        $data['subject_group_id'] = "";
        $data['subject_id']       = "";
        $data['subject_name']     = "";
        $data['domains']          = array();
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_group_id', $this->lang->line('subject_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
        } else {
            $data['class_id']               = $_POST['class_id'];
            $data['section_id']             = $_POST['section_id'];
            $data['subject_group_id']       = $_POST['subject_group_id'];
            $data['subject_id']             = $_POST['subject_id'];
            $subject_details                = $this->lessonplan_model->get_subjectNameBySubjectGroupSubjectId($_POST['subject_id']);
            $subject_group_class_sectionsId = $this->lessonplan_model->getsubject_group_class_sectionsId($_POST['class_id'], $_POST['section_id'], $_POST['subject_group_id']);
            $data['subject_name']           = $subject_details['name'] . " (" . $subject_details['code'] . ")";
            $domainlist                     = $this->lessonplan_model->getdomainBysubjectid($_POST['subject_id'], $subject_group_class_sectionsId['id']);

            foreach ($domainlist as $key => $value) {

                $data['domains'][$value['id']] = $value;
                $strands                        = $this->lessonplan_model->getstrandBydomainid($value['id'], $this->sch_current_session);
                foreach ($strands as $strand_key => $strand_value) {
                    $data['domains'][$value['id']]['strand'][] = $strand_value;
                }
            }
        }

        $data['status'] = array('1' => '<span class="label " style="background:#0e0e0e">' . $this->lang->line('complete') . '</span>', '0' => '<span class="label " style="background:#b3b3b3">' . $this->lang->line('incomplete') . '</span>');
        $this->load->view('layout/header');
        $this->load->view('admin/stander/index', $data);
        $this->load->view('layout/footer');
    }
    //-----------------------------------------------------------------------------------------------------------
    //load the outcome page with data 
    //-----------------------------------------------------------------------------------------------------------
    public function outcome()
    {
        if (!($this->rbac->hasPrivilege('outcome', 'can_view'))) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'stander_outcome');
        $this->session->set_userdata('sub_menu', 'admin/stander/outcome');
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $carray = array();
        $data['class_id'] = "";
        $data['section_id'] = "";
        $data['subject_group_id'] = "";
        $data['subject_id'] = "";

        $this->load->view('layout/header');
        $this->load->view('admin/stander/outcome', $data);
        $this->load->view('layout/footer');
    }
    //-----------------------------------------------------------------------------------------------------------
    //create new outcome and svae data  
    //-----------------------------------------------------------------------------------------------------------
    public function createoutcome()
    {
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_id', $this->lang->line('strand'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_id', $this->lang->line('stander'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('outcome_name', $this->lang->line('outcome_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('outcome_code', $this->lang->line('outcome_code'), 'trim|required|xss_clean');

        
        if ($this->form_validation->run() == false) {

            $msg = array(
                'class_id' => form_error('class_id'),
                'subject_id' => form_error('subject_id'),
                'domain_id' => form_error('domain_id'),
                'stander_id' => form_error('stander_id'),
                'strand_id' => form_error('strand_id'),
                'outcome_name' => form_error('outcome_name'),
                'outcome_code' => form_error('outcome_code'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        }  else {

                $data = array(
                    'stander_id' => $_POST['stander_id'],
                    'level' => $_POST['level'],
                    'outcome' => $_POST['outcome_name'],
                    'code' => $_POST['outcome_code'],
                    'session_id' => $this->sch_current_session,
                );
                $this->lessonplan_model->add_outcome($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }
    //--------------------------------------------------------------------------------------
    //get outcome list to print in datatable
    //--------------------------------------------------------------------------------------
    public function getoutcomelist()
    {
        $roles                       = $this->customlib->getUserData();
        $department = null;
        if ($roles['role'] === 'teacher') {
            $department = $roles['department'];
        }
        $result = $this->lessonplan_model->getoutcomelist($this->sch_current_session,$department);
        $m = json_decode($result);
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $outcome1 = '';
                $outcome = $this->lessonplan_model->getoutcomeBydomainid($value->stander_id, $this->sch_current_session);
                foreach ($outcome as $rl_value) {
                    $outcome1 .= $rl_value['outcome'] . '<br>';
                }

                if ($this->rbac->hasPrivilege('outcome', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "admin/stander/editoutcome/" . $value->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip'  title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                } else {
                    $editbtn = '';
                }
                if ($this->rbac->hasPrivilege('outcome', 'can_delete')) {
                    $deletebtn = "<a href='" . base_url() . "admin/stander/deleteoutcome/" . $value->id . "' onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . ");'  class='btn btn-default btn-xs'  title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>";
                } else {
                    $deletebtn = '';
                }

                $code = $value->subjects_code ? ' (' . $value->subjects_code . ')' : '';

                $dt_data[] = array(
                    $value->cname,
                    $value->subname . $code,
                    $value->domain_code.' '.$value->domainname,
                    $value->strand_code.' '.$value->strandname,
                    $value->stander_code.' '.$value->standername,
                    $value->code.' '.$value->outcome,
                    $value->level,
                    $editbtn . ' ' . $deletebtn
                );
            }
        }

        $json_data = array(
            "draw" => intval($m->draw),
            "recordsTotal" => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data" => $dt_data,
        );

        echo json_encode($json_data);
    }

    //-----------------------------------------------------------------------------------------
    //edit outcome data
    //-----------------------------------------------------------------------------------------
    public function editoutcome($id)
    {
        if (!($this->rbac->hasPrivilege('strand', 'can_edit'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander_outcome');
        $this->session->set_userdata('sub_menu', 'admin/stander/outcome');
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        

        $editresult = $this->lessonplan_model->getoutcome($this->sch_current_session, $id);
        //print_r($editresult);
        $data['class_id']                         = $editresult['class_id'];
        $data['subject_id'] = $editresult['subject_id'];
        $data['subject_name'] = $editresult['subject_name'];
        $data['domain_id']  = $editresult["domain_id"];
        $data['domain_name']  = $editresult["domain_name"];
        $data['strand_id']  = $editresult["strand_id"];
        $data['strand_name']  = $editresult["strand_name"];
        $data['stander_id'] = $editresult["stander_id"];
        $data['stander_name'] = $editresult["stander_name"];
        $data['outcome_name']    = $editresult["outcome"];
        $data['outcome_code']       = $editresult["code"];
        
        $data['outcome_id'] = $id; 
        //print_r($data);
        $this->load->view('layout/header');
        $this->load->view('admin/stander/editoutcome', $data);
        $this->load->view('layout/footer');
    }

    public function deleteoutcome($id)
    {
        if (!($this->rbac->hasPrivilege('outcome', 'can_delete'))) {
            access_denied();
        }
        $this->lessonplan_model->deleteoutcomebulk($id, $this->sch_current_session);
        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('delete_message'));
        redirect('admin/stander/outcome');
    }
    ///////////////////////---------------stander----------------/////////////////////////////

    //-----------------------------------------------------------------------------------------------------------
    //load the stander page with data 
    //-----------------------------------------------------------------------------------------------------------
    public function standers()
    {
        if (!($this->rbac->hasPrivilege('stander', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'stander_outcome');
        $this->session->set_userdata('sub_menu', 'admin/stander/standers');
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $carray = array();
        $data['class_id'] = "";
        $data['section_id'] = "";
        $data['subject_group_id'] = "";
        $data['subject_id'] = "";

        $this->load->view('layout/header');
        $this->load->view('admin/stander/stander', $data);
        $this->load->view('layout/footer');
    }
    //-----------------------------------------------------------------------------------------------------------
    //create new stander and svae data  
    //-----------------------------------------------------------------------------------------------------------
    public function createstander()
    {

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_id', $this->lang->line('strand'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_name', $this->lang->line('stander_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_code', $this->lang->line('stander_code'), 'trim|required|xss_clean');

        
        if ($this->form_validation->run() == false) {

            $msg = array(
                'class_id' => form_error('class_id'),
                'subject_id' => form_error('subject_id'),
                'domain_id' => form_error('domain_id'),
                'strand_id' => form_error('strand_id'),
                'stander_name' => form_error('stander_name'),
                'stander_code' => form_error('stander_code'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        }  else {

            
                $data = array(
                    'subject_id' => $_POST['subject_id'],
                    'domain_id' => $_POST['domain_id'],
                    'strand_id' => $_POST['strand_id'],
                    'stander' => $_POST['stander_name'],
                    'code'    => $_POST['stander_code'],
                    'session_id' => $this->sch_current_session,
                );
                $this->lessonplan_model->add_stander($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    //--------------------------------------------------------------------------------------
    //get stander list to print in datatable
    //--------------------------------------------------------------------------------------
    public function getstanderlist()
    {
        $roles                       = $this->customlib->getUserData();
        $department = null;
        if ($roles['role'] === 'teacher') {
            $department = $roles['department'];
        }
        $result = $this->lessonplan_model->getstanderlist($this->sch_current_session,$department);
        $m = json_decode($result);
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $stander1 = '';
                $stander = $this->lessonplan_model->getstanderBydomainid($value->domain_id, $this->sch_current_session);
                
                foreach ($stander as $rl_value) {
                    $stander1 .= $rl_value['stander'] . '<br>';
                }
                $editbtn = $this->rbac->hasPrivilege('stander', 'can_edit')
                //$editbtn = $this->rbac->hasPrivilege('stander')
                    ? "<a href='" . base_url() . "admin/stander/editstander/" . $value->id . "' class='btn btn-default btn-xs'><i class='fa fa-pencil'></i></a>"
                    : '';

                $deletebtn = $this->rbac->hasPrivilege('stander', 'can_delete')
                    ? "<a href='" . base_url() . "admin/stander/deletestander/" . $value->id . "' onclick='return confirm(\"Are you sure?\");' class='btn btn-default btn-xs'><i class='fa fa-trash'></i></a>"
                    : '';

                $code = $value->subjects_code ? ' (' . $value->subjects_code . ')' : '';

                $dt_data[] = array(
                    $value->cname,
                    $value->subname . $code,
                    $value->domain_code.' '.$value->domainname,
                    $value->strand_code.' ' .$value->strandname,
                    $value->code.' '.$value->stander,
                    $editbtn . ' ' . $deletebtn
                );
            }
        }

        $json_data = array(
            "draw" => intval($m->draw),
            "recordsTotal" => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data" => $dt_data,
        );

        echo json_encode($json_data);
    }

    //-----------------------------------------------------------------------------------------
    // get stander base on domain id
    //-----------------------------------------------------------------------------------------
    public function getstanderBydomainid($domain_id)
    {
        $subject_group_class_sectionsId = $this->lessonplan_model->getdomain_subjectId($_POST['class_id'], $_POST['section_id'], $_POST['subject_group_id'], $_POST['subject_id']);
        $data                           = $this->lessonplan_model->getstanderBydomainid($domain_id, $subject_group_class_sectionsId['id']);

        echo json_encode($data);
    }
    //-----------------------------------------------------------------------------------------
    // get strand base on domain id
    //-----------------------------------------------------------------------------------------
    public function get_strand_Bydomainid($domain_id)
    {
        $subject_group_class_sectionsId = $this->lessonplan_model->getdomain_subjectId($_POST['class_id'], $_POST['section_id'], $_POST['subject_group_id'], $_POST['subject_id']);
        $data                           = $this->lessonplan_model->get_strand_Bydomainid($domain_id, $subject_group_class_sectionsId['id']);

        echo json_encode($data);
    }

    //--------------------------------------------------------------------
    // delete stander
    //--------------------------------------------------------------------
    public function deletestander($id)
    {
        if (!($this->rbac->hasPrivilege('stander', 'can_delete'))) {
            access_denied();
        }
        $this->lessonplan_model->deletestanderbulk($id, $this->sch_current_session);
        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('delete_message'));
        redirect('admin/stander/standers');
    }

    ///////////////////////---------------domain----------------//////////////////////////////
  
    public function domain()
    {
        if (!($this->rbac->hasPrivilege('domain', 'can_view'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander_outcome');
        $this->session->set_userdata('sub_menu', 'admin/stander/domain');
        $class = $this->class_model->get();

        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $carray                   = array();
        $data['class_id']         = "";
        $data['section_id']       = "";
        $data['subject_group_id'] = "";
        $data['subject_id']       = "";
        // $domains                  =$this->lessonplan_model->getDomainList($this->sch_current_session);
        // $data['domain']           = $domains;
        // print_r($domains);
        // exit;
        $userdata                 = $this->customlib->getUserData();
        $role_id                  = $userdata["role_id"];
        $staff_id                 = $userdata["id"];

        $this->load->view('layout/header');
        $this->load->view('admin/stander/domain', $data);
        $this->load->view('layout/footer');
    }

    public function createdomain()
    {
        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', 'Subject', 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_name', 'Domain', 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_code', 'Code', 'trim|required|xss_clean');

        $validate = true;
        if ($this->form_validation->run() == false) {
            $msg = [
                'class_id' => form_error('class_id'),
                'subject_id' => form_error('subject_id'),
                'domain_name' => form_error('domain_name'),
                'doamin_code' => form_error('doamin_code')
            ];
            $array = ['status' => 'fail', 'error' => $msg];
        
        } else {
            
                $data = [
                    'class_id' => $this->input->post('class_id'),
                    'subject_id' => $this->input->post('subject_id'),
                    'name' => $this->input->post('domain_name'),
                    'code' => $this->input->post('domain_code'),
                    'session_id' => $this->sch_current_session,
                ];
                $this->lessonplan_model->add_domain($data);
            
            $array = ['status' => 'success', 'message' => 'Domains added successfully.'];
        }

        echo json_encode($array);
    }


    public function deletedomain($id)
    {
        if (!($this->rbac->hasPrivilege('domain', 'can_delete'))) {
            access_denied();
        }
        $this->lessonplan_model->deletedomain($id, $this->sch_current_session);
        redirect('admin/stander/domain');
    }

    public function deletedomainbulk($id)
    {
        if (!($this->rbac->hasPrivilege('domain', 'can_delete'))) {
            access_denied();
        }
        $this->lessonplan_model->deletedomainbulk($id, $this->sch_current_session);
        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('delete_message'));
        echo json_encode($array);
    }

    public function editdomain($id)
    {
        if (!($this->rbac->hasPrivilege('domain', 'can_edit'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander');
        $this->session->set_userdata('sub_menu', 'admin/stander/domain');
        $class             = $this->class_model->get();
        $data['classlist'] = $class;

        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }

        $carray = array();
        $result = $this->lessonplan_model->get($this->sch_current_session, '');
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $domain = $this->lessonplan_model->getdomain($value["subject_group_subject_id"], $value["subject_group_class_sections_id"], $this->sch_current_session);
                if ($domain != '') {
                    $domainname[$key] = $domain;
                }
            }
        }
        $data['result'] = $result;
        if (!empty($domainname)) {
            $data['domainname'] = $domainname;
        }

        $editresult = $this->lessonplan_model->getdomainbyidsession($this->sch_current_session, $id);
        $editdomain = $this->lessonplan_model->getdomain($this->sch_current_session);

        $data['editdomainname']                 = $editdomain;
        $data['class_id']                       = $editresult['classid'];
        $data['subject_id']                     = $editresult['subjectid'];
        $data['domain_name']                    = $editresult['domain_name'];
        $data['domain_code']                    = $editresult['domain_code'];
        $data['domain_id']                      = $id;
        $this->load->view('layout/header');
        $this->load->view('admin/stander/editdomain', $data);
        $this->load->view('layout/footer');
    }
    
    public function updatedomain()
    {
        $can_edit      = 1;
        $data['title'] = 'Add Library';
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_name', $this->lang->line('domain_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_code', $this->lang->line('domain_code'), 'trim|required|xss_clean');


        if (!empty($_POST['domain_delete'])) {
            if (count($all_domains) == count($_POST['domain_delete'])) {
                if (empty($_POST['strand'])) {
                    $validate = 0;
                }
            }
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'         => form_error('class_id'),
                'subject_id'       => form_error('subject_id'),
                'domain_name'       => form_error('domain_name'),
                'domain_code'       => form_error('domain_code'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
                if (isset($_POST['domain_name'])) {
                    $data = [
                        'class_id'                        => $this->input->post('class_id'),
                        'subject_id'                      => $this->input->post('subject_id'),
                        'name'                            => $this->input->post('domain_name'),
                        'code'                            => $this->input->post('domain_code'),
                        'session_id'                      => $this->sch_current_session,
                        'id'                              => $this->input->post('domain_id'),
                    ];

                    $this->lessonplan_model->add_domain($data);
                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
                }

        }
        echo json_encode($array);
    }

    //==================================strand Start===============================
    public function strand()
    {
        if (!($this->rbac->hasPrivilege('strand', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'stander');
        $this->session->set_userdata('sub_menu', 'admin/stander/strand');
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $carray                   = array();
        $data['class_id']         = "";
        $data['section_id']       = "";
        $data['subject_group_id'] = "";
        $data['subject_id']       = "";
        $this->load->view('layout/header');
        $this->load->view('admin/stander/strand', $data);
        $this->load->view('layout/footer');
    }
    public function getdomainBydomainid($domain_id)
    {
        $data = $this->lessonplan_model->getdomainBydomainid($domain_id);
        echo json_encode($data);
    }

    public function getdomainBysubjectidedit($sub_id)
    {
        $subject_group_class_sections_id = $_POST['subject_group_class_sections_id'];
        $data                            = $this->lessonplan_model->getdomainBysubjectidedit($sub_id, $subject_group_class_sections_id);
        echo json_encode($data);
    }

    public function createstrand()
    {
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_name', $this->lang->line('strand_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_code', $this->lang->line('strand_code'), 'trim|required|xss_clean');

        $validate = 1;
    
        if ($this->form_validation->run() == false) {

            $msg = array(
                'class_id'         => form_error('class_id'),
                'subject_id'       => form_error('subject_id'),
                'domain_id'        => form_error('domain_id'),
                'strand_name'        => form_error('strand_name'),
                'strand_code'        => form_error('strand_code'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        
        } else {
                $data = array(
                    'domain_id'  => $_POST['domain_id'],
                    'name'       => $_POST['strand_name'],
                    'code'       => $_POST['strand_code'],
                    'session_id' => $this->sch_current_session,
                );
                $this->lessonplan_model->add_strand($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function deletestrandbulk($id)
    {
        if (!($this->rbac->hasPrivilege('strand', 'can_delete'))) {
            access_denied();
        }
        $this->lessonplan_model->deletestrandbulk($id, $this->sch_current_session);
        redirect('admin/stander/strand');
    }

    public function editstrand($id)
    {
        if (!($this->rbac->hasPrivilege('strand', 'can_edit'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander');
        $this->session->set_userdata('sub_menu', 'admin/stander/strand');
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $carray = array();

        $result = $this->lessonplan_model->getstrand($this->sch_current_session, '');

        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $strand             = $this->lessonplan_model->getstrandBydomainid($value["domain_id"], $this->sch_current_session);
                $strandresult[$key] = $strand;
            }
        }

        $data['result'] = $result;
        if (!empty($strandresult)) {
            $data['strandresult'] = $strandresult;
        }

        $editresult                              = $this->lessonplan_model->getstrandbyidsession($this->sch_current_session, $id);
        $editstrand                              = $this->lessonplan_model->getstrand($this->sch_current_session);
        $data['strand_name']                     = $editresult["name"];
        $data['strand_code']                     = $editresult["code"];
        $data['strand_id']                       = $id;
        $data['editstrandname']                  = $editstrand;
        $data['class_id']                        = $editresult['classid'];
        $data['subject_id']                      = $editresult['subjectid'];

        $this->load->view('layout/header');
        $this->load->view('admin/stander/editstrand', $data);
        $this->load->view('layout/footer');
    }

    public function editstander($id)
    {
        if (!($this->rbac->hasPrivilege('stander', 'can_edit'))) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'stander');
        $this->session->set_userdata('sub_menu', 'admin/stander/stander');
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        foreach ($class as $class_key => $class_value) {
            $data['class_array'][] = $class_value['id'];
        }
        $editresult                              = $this->lessonplan_model->getstander($this->sch_current_session, $id);
        //print_r($editresult);
        //$editstander                              = $this->lessonplan_model->getstander($this->sch_current_session);
        $data['subject_id'] = $editresult['subject_id'];
        $data['subject_name'] = $editresult['subject_name'];
        $data['domain_id']  = $editresult["domain_id"];
        $data['domain_name']  = $editresult["domain_name"];
        $data['strand_id']  = $editresult["strand_id"];
        $data['strand_name']  = $editresult["strand_name"];
        $data['stander_name']                     = $editresult['stander'];
        $data['stander_code']                     = $editresult['code'];
        $data['class_id']                         = $editresult['class_id'];
        $data['stander_id']                       = $id;

        $this->load->view('layout/header');
        $this->load->view('admin/stander/editstander', $data);
        $this->load->view('layout/footer');
    }
    public function updatestrand()
    {
        $can_edit      = 1;
        $data['title'] = 'Add Library';
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_name', $this->lang->line('strand_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_code', $this->lang->line('strand_code'), 'trim|required|xss_clean');


        if (!empty($_POST['strand_delete'])) {
            if (count($all_strands) == count($_POST['strand_delete'])) {
                if (empty($_POST['strand'])) {
                    $validate = 0;
                }
            }
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'         => form_error('class_id'),
                'subject_id'       => form_error('subject_id'),
                'domain_id'       => form_error('domain_id'),
                'strand_name'       => form_error('strand_name'),
                'strand_code'       => form_error('strand_code'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            if (isset($_POST['strand_name'])) {
                $data = [
                    'domain_id'                      => $this->input->post('domain_id'),
                    'name'                            => $this->input->post('strand_name'),
                    'code'                            => $this->input->post('strand_code'),
                    'session_id'                      => $this->sch_current_session,
                    'id'                              => $this->input->post('strand_id'),
                ];

                $this->lessonplan_model->add_strand($data);
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            }
        }
        echo json_encode($array);
    }

    public function updatestander()
    {
        $can_edit      = 1;
        $data['title'] = 'Add Library';
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_id', $this->lang->line('strand'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_name', $this->lang->line('stander_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_code', $this->lang->line('stander_code'), 'trim|required|xss_clean');


        if (!empty($_POST['stander_delete'])) {
            if (count($all_standers) == count($_POST['stander_delete'])) {
                if (empty($_POST['stander'])) {
                    $validate = 0;
                }
            }
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'         => form_error('class_id'),
                'subject_id'       => form_error('subject_id'),
                'domain_id'       => form_error('domain_id'),
                'strand_id'       => form_error('strand_id'),
                'stander_name'       => form_error('stander_name'),
                'stander_code'       => form_error('stander_code'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            if (isset($_POST['stander_name'])) {
                $data = [
                    'domain_id'                      => $this->input->post('domain_id'),
                    'strand_id'                      => $this->input->post('strand_id'),
                    'stander'                            => $this->input->post('stander_name'),
                    'code'                            => $this->input->post('stander_code'),
                    'session_id'                      => $this->sch_current_session,
                    'id'                              => $this->input->post('stander_id'),
                ];

                $this->lessonplan_model->add_stander($data);
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            }
        }
        echo json_encode($array);
    }

    public function updateoutcome()
    {
        $can_edit      = 1;
        $data['title'] = 'Add Library';
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('domain_id', $this->lang->line('domain'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('strand_id', $this->lang->line('strand'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('stander_id', $this->lang->line('stander'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('outcome_level', $this->lang->line('outcome_level'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('outcome_name', $this->lang->line('outcome'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('outcome_code', $this->lang->line('outcome_code'), 'trim|required|xss_clean');


        if (!empty($_POST['outcome_delete'])) {
            if (count($all_outcomes) == count($_POST['outcome_delete'])) {
                if (empty($_POST['outcome'])) {
                    $validate = 0;
                }
            }
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'         => form_error('class_id'),
                'subject_id'       => form_error('subject_id'),
                'domain_id'       => form_error('domain_id'),
                'strand_id'       => form_error('strand_id'),
                'stander_id'       => form_error('stander_id'),
                'outcome_level'       => form_error('outcome_level'),
                'outcome_name'       => form_error('outcome_name'),
                'outcome_code'       => form_error('outcome_code'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            if (isset($_POST['outcome_name'])) {
                $data = [
                    'stander_id'                      => $this->input->post('stander_id'),
                    'outcome'                            => $this->input->post('outcome_name'),
                    'code'                            => $this->input->post('outcome_code'),
                    'session_id'                      => $this->sch_current_session,
                    'id'                              => $this->input->post('outcome_id'),
                ];

                $this->lessonplan_model->add_outcome($data);
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            }
        }
        echo json_encode($array);
    }

    public function changestrandStatus()
    {
        $id     = $this->input->post('id');
        $status = $this->input->post('status');
        $data   = array('id' => $id, 'complete_date' => '0000-00-00', 'status' => $status);
        $result = $this->lessonplan_model->changestrandStatus($data);

        if ($result) {
            $response = array('status' => 1, 'msg' => $this->lang->line('success_message'));
            echo json_encode($response);
        }
    }

    public function strand_completedate()
    {
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $msg = array(
                'date' => form_error('date'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $data = array(
                'complete_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'status'        => 1,
                'id'            => $_POST['id'],
            );

            $this->lessonplan_model->changestrandStatus($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    //==========================================Syllabus-Assign=========================


    public function get_strandbyid()
    {
        $this->lessonplan_model->getstrand($this->sch_current_session, '');
    }

    public function getdomainlist()
    {
        $roles                       = $this->customlib->getUserData();
        $department = null;
        if($roles['role'] === 'teacher')
        {
            $department = $roles['department'];
        }
        $result = $this->lessonplan_model->getdomainlist($this->sch_current_session , $department);
        $m = json_decode($result);
        $dt_data = array();
        //print_r($m);
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn = $this->rbac->hasPrivilege('domain', 'can_edit')
                ? "<a href='" . base_url() . "admin/stander/editdomain/" . $value->id . "' class='btn btn-default btn-xs' data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>"
                : '';

                $deletebtn = $this->rbac->hasPrivilege('domain', 'can_delete')
                ? "<a href='" . base_url() . "admin/stander/deletedomain/" . $value->id . "' onclick='return confirm(\"" . $this->lang->line('delete_confirm') . "\");' class='btn btn-default btn-xs' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>"
                : '';

                $code = $value->subject_code ? ' (' . $value->subject_code . ')' : '';

                $dt_data[] = array(
                    $value->class_name,
                    $value->subject_name . $code,
                    $value->code . ' ' . $value->name,
                    $editbtn . ' ' . $deletebtn
                );
            }
        }

        $json_data = array(
            "draw" => intval($m->draw),
            "recordsTotal" => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data" => $dt_data,
        );

        // Output JSON response
        echo json_encode($json_data);
    }


    public function getSubjectByClass()
    {
        $class_id = $this->$_POST['class_id'];
        $data = $this->lessonplan_model->getSubjectByClass($class_id);
        echo json_encode($data);
    }

    //--------------------------------------------------------------------------------------
    //get strand list to print in datatable
    //--------------------------------------------------------------------------------------
    public function getstrandlist()
    {
        $roles                       = $this->customlib->getUserData();
        $department = null;
        if ($roles['role'] === 'teacher') {
            $department = $roles['department'];
        }
        $result = $this->lessonplan_model->getstrandlist($this->sch_current_session,$department);
        $m = json_decode($result);
        $dt_data = array();
        
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $strand1 = '';
                $strand = $this->lessonplan_model->getstrandBydomainid($value->domain_id, $this->sch_current_session);
                foreach ($strand as $rl_value) {
                    $strand1 .= $rl_value['name'] . '<br>';
                }

                $editbtn = $this->rbac->hasPrivilege('strand', 'can_edit')
                ? "<a href='" . base_url() . "admin/stander/editstrand/" . $value->id . "' class='btn btn-default btn-xs'><i class='fa fa-pencil'></i></a>"
                : '';

                $deletebtn = $this->rbac->hasPrivilege('strand' , 'can_delete')
                ? "<a href='" . base_url() . "admin/stander/deletestrandbulk/" . $value->id . "' onclick='return confirm(\"Are you sure?\");' class='btn btn-default btn-xs'><i class='fa fa-trash'></i></a>"
                : '';

                $code = $value->subject_code ? ' (' . $value->subject_code . ')' : '';

                $dt_data[] = array(
                    $value->class_name,
                    $value->subject_name . $code,
                    $value->domain_name,
                    $value->code.' '.$value->strand_name,
                    $editbtn . ' ' . $deletebtn
                );
            }
        }

        $json_data = array(
            "draw" => intval($m->draw),
            "recordsTotal" => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data" => $dt_data,
        );
        // Output JSON response
        echo json_encode($json_data);
    }
}
