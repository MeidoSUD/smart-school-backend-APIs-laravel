<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Book extends Api_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Add Book';
        $data['title_list'] = 'Book Details';
        $listbook = $this->book_model->listbook();
        $data['listbook'] = $listbook;

        return $this->api_success($data);
    }

    function issue()
    {
        $data['title'] = 'Add Book';
        $data['title_list'] = 'Book Details';
        $member_type = "student";
        $student_id = $this->customlib->getStudentSessionUserID();
        $checkIsMember = $this->librarymember_model->checkIsMember($member_type, $student_id);
        if (is_array($checkIsMember)) {
            $data['bookList'] = $checkIsMember;
            $data['isCheck'] = "1";
        } else {
            $data['isCheck'] = "0";
        }

        return $this->api_success($data);
    }
}
