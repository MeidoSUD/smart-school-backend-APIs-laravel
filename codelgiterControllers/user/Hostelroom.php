<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hostelroom extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $listroom = $this->hostelroom_model->listhostelroom();
        $data['listroom'] = $listroom;

        return $this->api_success($data);
    }
}
