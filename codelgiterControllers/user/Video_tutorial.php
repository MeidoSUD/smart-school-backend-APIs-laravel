<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Video_tutorial extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_id = $this->customlib->getStudentSessionUserID();
        $data['student'] = $this->student_model->get($student_id);
        $data['video_list'] = $this->videotutorial_model->getStudentVideos($student_current_class->class_id, $student_current_class->section_id);

        return $this->api_success($data);
    }

    public function view($id)
    {
        $data['video'] = $this->videotutorial_model->getVideo($id);

        return $this->api_success($data);
    }
}
