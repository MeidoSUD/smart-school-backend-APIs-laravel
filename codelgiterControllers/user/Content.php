<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Content extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    function list()
    {
        return $this->api_success(['title' => 'Downloads']);
    }

    public function getsharelist()
    {
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $role = $this->customlib->getUserRole();
        if ($role == "student") {
            $m = $this->sharecontent_model->getStudentsharelist($this->customlib->getStudentSessionUserID(), $student_current_class->class_id, $student_current_class->section_id);
        } elseif ($role == "parent") {
            $m = $this->sharecontent_model->getParentsharelist($this->customlib->getUsersID(), $student_current_class->class_id, $student_current_class->section_id);
        }

        $superadmin_visible = $this->Setting_model->get();
        $superadmin_restriction = $superadmin_visible[0]['superadmin_restriction'];

        $m = json_decode($m);

        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $viewbtn = '';
                $title = $value->title;
                $row = array();
                $row[] = $title;
                $viewbtn = site_url('user/content/view/') . $value->id;
                $row[] = $this->customlib->dateformat($value->share_date);
                $row[] = $this->customlib->dateformat($value->valid_upto);

                if ($superadmin_restriction == 'disabled' && $value->role_id == 7) {
                    $row[] = '';
                } else {
                    $row[] = $value->name . ' ' . $value->surname . ' (' . $value->employee_id . ')';
                }

                $row[] = $viewbtn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw" => intval($m->draw),
            "recordsTotal" => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data" => $dt_data,
        );

        return $this->api_success($json_data);
    }

    public function view($id)
    {
        $data['title'] = 'Upload Content';
        $data['title_list'] = 'Upload Content List';
        $data['content'] = $this->sharecontent_model->getShareContentWithDocuments($id);
        $superadmin_visible = $this->Setting_model->get();
        $data['superadmin_restriction'] = $superadmin_visible[0]['superadmin_restriction'];

        return $this->api_success($data);
    }

    public function index()
    {
        $data['title'] = 'Upload Content';
        $data['title_list'] = 'Upload Content List';
        $list = $this->content_model->get();
        $data['list'] = $list;
        $ght = $this->customlib->getcontenttype();
        $data['ght'] = $ght;
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        return $this->api_success($data);
    }

    public function download($file)
    {
        $this->media_storage->filedownload($this->uri->segment(7), "./uploads/school_content/material");
        return $this->api_success(['file' => $file]);
    }

    public function assignment()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student = $this->student_model->get($student_id);
        $data['title_list'] = 'List of Assignment';
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $list = $this->content_model->getListByCategoryforUser($student_current_class->class_id, $student_current_class->section_id, "assignments");
        $data['list'] = $list;

        return $this->api_success($data);
    }

    public function studymaterial()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student = $this->student_model->get($student_id);
        $data['title_list'] = 'List of Assignment';
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $list = $this->content_model->getListByCategoryforUser($student_current_class->class_id, $student_current_class->section_id, "study_material");
        $data['list'] = $list;

        return $this->api_success($data);
    }

    public function syllabus()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student = $this->student_model->get($student_id);
        $data['title_list'] = 'List of Syllabus';
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $list = $this->content_model->getListByCategoryforUser($student_current_class->class_id, $student_current_class->section_id, "syllabus");
        $data['list'] = $list;

        return $this->api_success($data);
    }

    public function other()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        $student = $this->student_model->get($student_id);
        $data['title_list'] = 'List of Other Download';
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $list = $this->content_model->getListByCategoryforUser($student_current_class->class_id, $student_current_class->section_id, "other_download");
        $data['list'] = $list;

        return $this->api_success($data);
    }
}
