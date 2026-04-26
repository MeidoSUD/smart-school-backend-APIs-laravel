<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Visitors extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_session_id = $student_current_class->student_session_id;
        $data['visitor_list'] = $this->visitors_model->visitorbystudentid($student_session_id);

        return $this->api_success($data);
    }

    public function download($id)
    {
        $visitorlist = $this->visitors_model->visitors_list($id);
        $this->media_storage->filedownload($visitorlist['image'], "./uploads/front_office/visitors");
        return $this->api_success(['image' => $visitorlist['image']]);
    }
}
