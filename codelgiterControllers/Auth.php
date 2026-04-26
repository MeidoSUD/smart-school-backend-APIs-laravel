<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Auth extends Api_Controller
{
    public function __construct()
    {
        $this->public_methods = ['login'];
        parent::__construct();
    }

    /**
     * API Login
     * POST: /api/auth/login
     */
    public function login()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $errors = array(
                'username' => form_error('username'),
                'password' => form_error('password'),
            );
            return $this->api_error('Validation failed', $errors);
        }

        $login_post = array(
            'username' => $this->input->post('username'),
            'password' => $this->input->post('password'),
        );

        $login_details = $this->user_model->checkLogin($login_post);

        if ($login_details) {
            $user = $login_details[0];

            if ($user->is_active == "yes") {
                // Generate secure random token
                $token = bin2hex(random_bytes(32));
                
                // Save token to database
                $this->user_model->updateToken($user->id, $token);

                // Get full user profile data
                $result = [];
                if ($user->role == "student") {
                    $result = $this->user_model->read_user_information($user->id);
                    $result = $result[0];
                    // Add default class/section for students
                    $defaultclass = $this->user_model->get_studentdefaultClass($user->id);
                    $result->class_id = $defaultclass['class_id'] ?? null;
                    $result->section_id = $defaultclass['section_id'] ?? null;
                    $result->student_session_id = $defaultclass['student_session_id'] ?? null;
                } else if ($user->role == "parent") {
                    $result = $this->user_model->checkLoginParent($login_post);
                    $result = $result[0];
                }

                $response_data = [
                    'token' => $token,
                    'user' => $result
                ];

                return $this->api_success($response_data, 'Login successful');
            } else {
                return $this->api_error('Your account is disabled, please contact administrator.', null, 403);
            }
        } else {
            return $this->api_error('Invalid username or password', null, 401);
        }
    }

    /**
     * API Logout
     * POST: /api/auth/logout
     */
    public function logout()
    {
        if ($this->user) {
            $this->user_model->updateToken($this->user->id, null);
            return $this->api_success(null, 'Logged out successfully');
        }
        return $this->api_error('Not logged in');
    }
}
