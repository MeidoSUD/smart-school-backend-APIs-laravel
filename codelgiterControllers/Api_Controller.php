<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api_Controller extends CI_Controller
{
    protected $sch_setting_detail;
    protected $payment_method;
    protected $result;

    protected $user = null;
    protected $public_methods = [];

    public function __construct()
    {
        // 1. CORS Headers - Allow Flutter/Postman access
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // Handle pre-flight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // 2. Start output buffering to catch any accidental HTML output
        if (ob_get_level() == 0) {
            ob_start();
        }
        
        // 3. Initialize Stateless Session BEFORE parent constructor to intercept autoloaded libraries
        $this->session = new MockSession();
        
        parent::__construct();
        
        // Ensure singleton also uses MockSession
        $CI =& get_instance();
        $CI->session = $this->session;
        
        // Set error handler
        set_error_handler(array($this, 'handleApiError'));
        register_shutdown_function(array($this, 'handleShutdown'));
        
        // Load Core Dependencies
        $this->load->database();
        $this->load->helper(array('language', 'custom_helper', 'directory', 'customfield', 'custom', 'mime'));
        $this->load->model(array(
            'setting_model', 
            'paymentsetting_model', 
            'language_model', 
            'student_edit_field_model', 
            'marksdivision_model', 
            'offlinePayment_model', 
            'module_model', 
            'user_model',
            'student_model'
        ));
        
        // Parse JSON request body
        $content_type = $this->input->get_request_header('Content-Type');
        if ($content_type && stripos($content_type, 'application/json') !== false) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            if ($json_data) {
                $_POST = array_merge($_POST, $json_data);
            }
        }
        
        // Create a dummy customlib if it's not loaded
        if (!isset($this->customlib)) {
            $this->customlib = new DummyCustomLib();
        }
        
        // Load non-session-dependent libraries
        $this->load->library(array('media_storage'));
        
        $this->payment_method = $this->paymentsetting_model->getActiveMethod();
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->config->load('mailsms');
        
        // Check if current method is public
        $current_method = $this->router->fetch_method();
        if (in_array($current_method, $this->public_methods)) {
            return;
        }
        
        // Token-based authentication (NO session cookie for API)
        $this->user = $this->_authenticate_token();
        
        if (!$this->user) {
            $this->api_unauthorized('Unauthorized access');
            exit;
        }
        
        // If authenticated, setup dummy session data for model compatibility
        $this->_setup_user_session();
    }
    
    private function _authenticate_token()
    {
        $auth_header = $this->input->get_request_header('Authorization');
        
        if (empty($auth_header)) {
            // Check for token in GET/POST as fallback
            $token = $this->input->get_post('token');
        } else {
            // Header format: Bearer <token>
            if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
                $token = $matches[1];
            } else {
                $token = $auth_header;
            }
        }
        
        if (empty($token)) {
            return null;
        }
        
        $user = $this->user_model->getUserByToken($token);
        
        if ($user) {
            $this->api_logger->log('success', 'User authenticated: ' . $user->id . ' (' . $user->role . ')');
        }
        
        return $user ?: null;
    }
    
    private function _setup_user_session()
    {
        if (!$this->user) {
            return;
        }
        
        // Set user session data for model compatibility
        $session_data = array(
            'id' => $this->user->id,
            'username' => $this->user->username,
            'role' => $this->user->role,
            'user_id' => $this->user->id,
            'is_logged_in' => true
        );
        
        $this->session->set_userdata($session_data);
        
        // Set role-specific session
        $current_session_id = $this->setting_model->getCurrentSession();
        
        if ($this->user->role == 'student') {
            $this->db->select('student_session.id as student_session_id, student_session.class_id, student_session.section_id, students.id as student_id');
            $this->db->from('student_session');
            $this->db->join('students', 'students.id = student_session.student_id');
            $this->db->where('students.id', $this->user->user_id);
            $this->db->where('student_session.session_id', $current_session_id);
            $this->db->limit(1);
            $stu_record = $this->db->get()->row_array();
            
            if ($stu_record) {
                $this->session->set_userdata('student', $stu_record);
                $this->session->set_userdata('current_class', array(
                    'class_id' => $stu_record['class_id'],
                    'section_id' => $stu_record['section_id'],
                    'student_session_id' => $stu_record['student_session_id']
                ));
            }
        } elseif ($this->user->role == 'parent') {
            $this->db->select('student_session.id as student_session_id, student_session.class_id, student_session.section_id, students.id as student_id');
            $this->db->from('student_guardians');
            $this->db->join('student_session', 'student_session.student_id = student_guardians.student_id');
            $this->db->join('students', 'students.id = student_guardians.student_id');
            $this->db->where('student_guardians.guardian_id', $this->user->id);
            $this->db->where('student_session.session_id', $current_session_id);
            $this->db->limit(1);
            $child = $this->db->get()->row_array();
            
            if ($child) {
                $this->session->set_userdata('student', $child);
                $this->session->set_userdata('current_class', array(
                    'class_id' => $child['class_id'],
                    'section_id' => $child['section_id'],
                    'student_session_id' => $child['student_session_id']
                ));
            }
            $this->session->set_userdata('parent', array('guardian_id' => $this->user->id));
        }
    }
    
    public function handleApiError($errno, $errstr, $errfile, $errline)
    {
        if (isset($this->api_logger) && $this->api_logger !== null) {
            $this->api_logger->log('error', "PHP Error: $errstr in $errfile:$errline");
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(500)
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $errstr
            ]));
        return true;
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();
        $types = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE];
        
        if ($error && in_array($error['type'], $types)) {
            $this->api_logger->log('fatal', "Fatal: {$error['message']} in {$error['file']}:{$error['line']}");
        }
    }

    protected function api_response($status, $data = null, $message = null, $status_code = 200)
    {
        $response = array(
            'status' => $status,
        );

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        // Log response
        $log_msg = "Response [$status_code]: " . ($message ?: $status);
        if ($status == 'error') {
            $this->api_logger->log('error', $log_msg . " | Data: " . json_encode($data));
        } else {
            $this->api_logger->log('success', $log_msg);
        }

        // Clear buffer and send JSON
        ob_clean();
        
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($response));

        // Use _display() to send output immediately and stop execution
        $this->output->_display();
        exit;
    }

    protected function api_success($data = null, $message = null, $status_code = 200)
    {
        return $this->api_response('success', $data, $message, $status_code);
    }

    protected function api_error($message = null, $errors = null, $status_code = 400)
    {
        $response = array(
            'status' => 'error',
            'message' => $message
        );

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // Log error
        $this->api_logger->log('error', "Error [$status_code]: $message | Errors: " . json_encode($errors));

        ob_clean();
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($response));
            
        $this->output->_display();
        exit;
    }

    protected function api_unauthorized($message = 'Unauthorized access')
    {
        return $this->api_error($message, null, 401);
    }

    protected function api_not_found($message = 'Resource not found')
    {
        return $this->api_error($message, null, 404);
    }

    protected function api_server_error($message = 'Internal server error')
    {
        return $this->api_error($message, null, 500);
    }
}

/**
 * Stateless Session Mock for API
 */
class MockSession {
    private $data = [];

    public function set_userdata($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    public function userdata($key = NULL) {
        if ($key === NULL) {
            return $this->data;
        }
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }

    public function has_userdata($key) {
        return isset($this->data[$key]);
    }

    public function unset_userdata($key) {
        unset($this->data[$key]);
    }

    public function sess_destroy() {
        $this->data = [];
    }

    public function all_userdata() {
        return $this->data;
    }
}

/**
 * API Logger Library
 */
class Api_Logger
{
    private $log_file;
    
    public function __construct()
    {
        // Ensure directory exists
        $log_dir = FCPATH . 'api_logs';
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        
        $this->log_file = $log_dir . '/api_' . date('Y-m-d') . '.log';
    }
    
    public function log($type, $message)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $log_entry = date('H:i:s') . " | $ip | $method | $uri | " . strtoupper($type) . " | " . $message . PHP_EOL;
        @file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
}

/**
 * Dummy class to satisfy model dependencies
 */
class DummyCustomLib {
    public function getStaffID() { return null; }
    public function getTimeZone() { return 'UTC'; }
    public function getAppVersion() { return '1.0.0'; }
    public function getCurrencyFormat() { return '$'; }
    public function getUserData() { return array('role_id' => 0); }
}