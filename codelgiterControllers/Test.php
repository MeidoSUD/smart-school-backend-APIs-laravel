<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'controllers/api/Api_Controller.php');

class Test extends Api_Controller
{
    public function __construct()
    {
        $this->public_methods = ['ping', 'db', 'gentoken', 'whoami'];
        parent::__construct();
    }

    public function ping()
    {
        return $this->api_success([
            'message' => 'API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ], 'Ping successful');
    }

    public function db()
    {
        try {
            $query = $this->db->query("SELECT COUNT(*) as total FROM students");
            $result = $query->row();
            
            $query2 = $this->db->query("SELECT COUNT(*) as total FROM users WHERE token IS NOT NULL AND token != ''");
            $result2 = $query2->row();
            
            return $this->api_success([
                'total_students' => $result->total,
                'users_with_token' => $result2->total
            ]);
        } catch (Exception $e) {
            return $this->api_error('Database error: ' . $e->getMessage());
        }
    }

    public function gentoken()
    {
        $username = $this->input->get('username') ?: $this->input->post('username');
        $password = $this->input->get('password') ?: $this->input->post('password');
        
        if (empty($username) || empty($password)) {
            return $this->api_error('username and password are required');
        }
        
        $this->db->where('username', $username);
        $user = $this->db->get('users')->row();
        
        if (!$user) {
            return $this->api_error('User not found');
        }
        
        // Check password (try both MD5 and plain text)
        $valid = false;
        if ($user->password === md5($password)) {
            $valid = true;
        } elseif ($user->password === $password) {
            $valid = true;
        } elseif (password_verify($password, $user->password)) {
            $valid = true;
        }
        
        if (!$valid) {
            return $this->api_error('Invalid password');
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $this->db->where('id', $user->id);
        $this->db->update('users', ['token' => $token]);
        
        $this->api_logger->log('token_generated', 'Token generated for user: ' . $user->id);
        
        return $this->api_success([
            'token' => $token,
            'user_id' => $user->id,
            'role' => $user->role,
            'username' => $user->username
        ], 'Token generated successfully');
    }

    public function whoami()
    {
        if (!$this->user) {
            return $this->api_error('Not authenticated', null, 401);
        }
        
        $student = $this->session->userdata('student');
        
        return $this->api_success([
            'user_id' => $this->user->id,
            'role' => $this->user->role,
            'username' => $this->user->username,
            'student_session' => $student
        ]);
    }
}