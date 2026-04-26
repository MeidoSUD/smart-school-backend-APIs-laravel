<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hostel extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $listhostel = $this->hostel_model->listhostel();
        $data['listhostel'] = $listhostel;
        $ght = $this->customlib->getHostaltype();
        $data['ght'] = $ght;

        return $this->api_success($data);
    }
}
