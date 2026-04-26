<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Route extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $studentList = $this->student_model->get($student_id);
        $studentList['pickup_point'] = $this->pickuppoint_model->getPickupPointByRouteID($studentList['route_id']);
        $data['listroute'] = $studentList;

        return $this->api_success($data);
    }

    public function getbusdetail()
    {
        $vehrouteid = $this->input->post('vehrouteid');
        $result = $this->vehroute_model->getVechileDetailByVecRouteID($vehrouteid);

        return $this->api_success($result);
    }
}
