<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Notification extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Notifications';
        $user_role = $this->customlib->getUserRole();
        if ($user_role == 'student') {
            $student_id = $this->customlib->getStudentSessionUserID();
            $notifications = $this->notification_model->getNotificationForStudent($student_id);
        } elseif ($user_role == 'parent') {
            $student_id = $this->customlib->getUsersID();
            $notifications = $this->notification_model->getNotificationForParent($student_id);
        }
        $notification_bydate = array();
        foreach ($notifications as $key => $value) {
            if (strtotime(date('Y-m-d')) >= strtotime($value['publish_date'])) {
                $notification_bydate[] = $value;
            }
        }

        $data['notificationlist'] = $notification_bydate;

        return $this->api_success($data);
    }

    public function updatestatus()
    {
        $notification_id = $this->input->post('notification_id');

        $user_role = $this->customlib->getUserRole();
        if ($user_role == 'student') {
            $student_id = $this->customlib->getStudentSessionUserID();
            $result = $this->notification_model->updateStatus($notification_id, $student_id);
        } elseif ($user_role == 'parent') {
            $parent_id = $this->customlib->getUsersID();
            $result = $this->notification_model->updateStatusforParent($notification_id, $parent_id);
        }

        return $this->api_success(['notification' => $result], 'Status updated successfully');
    }

    public function read()
    {
        $notification_id = $this->input->post('notice');
        if ($notification_id != "") {
            $student_id = $this->customlib->getStudentSessionUserID();
            $result = $this->notification_model->updateStatusforStudent($notification_id, $student_id);
            return $this->api_success(['notification' => $result], $this->lang->line('delete_message'));
        }

        return $this->api_error($this->lang->line('something_went_wrong'));
    }

    public function download($id)
    {
        $notification = $this->notification_model->notification($id);
        $this->media_storage->filedownload($notification['attachment'], "uploads/notice_board_images");
        return $this->api_success(['attachment' => $notification['attachment']]);
    }

    public function notification()
    {
        $settingresult = $this->setting_model->getSetting();
        $superadmin_restriction = $settingresult->superadmin_restriction;

        $message_id = $this->input->post('message_id');
        $notificationlist = $this->notification_model->notification($message_id);

        if ($superadmin_restriction == 'disabled') {
            $staff = $this->staff_model->get($notificationlist['staff_id']);
            if ($staff['role_id'] != 7) {
                $notificationlist['created_by'] = ($notificationlist['surname'] != "") ? $notificationlist["name"] . " " . $notificationlist["surname"] . "  (" . $notificationlist["employee_id"] . ")" : $notificationlist["name"] . " (" . $notificationlist['employee_id'] . ")";
            } else {
                $notificationlist['created_by'] = '';
            }
        } else {
            $notificationlist['created_by'] = ($notificationlist['surname'] != "") ? $notificationlist["name"] . " " . $notificationlist["surname"] . "  (" . $notificationlist["employee_id"] . ")" : $notificationlist["name"] . " (" . $notificationlist['employee_id'] . ")";
        }

        $data['notificationlist'] = $notificationlist;

        return $this->api_success($data);
    }
}
