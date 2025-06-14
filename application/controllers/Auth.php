<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('Authorization_model');
        $this->load->model('Rights_Model');
        $this->load->helper('cookie');
    }

    public function index()
    {
        $this->load->view('Authorization/Login');
    }

    public function verify()
    {
        if ($this->input->post()) {
            $username = $this->input->post('UserName');
            $password = $this->input->post('Password');
            $remember = $this->input->post('Remember');

            $verify = $this->Authorization_model->Verify($username, $password);

            if ($verify === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid User Details Please Check']);
            } elseif ($verify === -1) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Password Please Check']);
            } else {
                $sess_array = [
                    'Ccode' => $verify->Ccode,
                    'Lcode' => $verify->Lcode,
                    'UserType' => $verify->UserType,
                    'Designation' => $verify->Designation,
                    'Department' => $verify->Name,
                    'UserName' => $username,
                    'IsOnLogin' => true
                ];

                $this->session->set_userdata('sess_array', $sess_array);
                $this->session->set_userdata('logged_in', true);

                if ($remember) {
                    set_cookie('UserName', $username, 86400);
                    set_cookie('Password', $password, 86400);
                } else {
                    delete_cookie('UserName');
                    delete_cookie('Password');
                }

                echo json_encode(['status' => 'success', 'message' => 'Welcome To ' . $verify->Lcode . '  ' . $verify->Name]);
            }
            exit;
        }
    }

    public function logout()
    {
        $this->session->set_flashdata('logout_message', 'Thank you for using E-Master. Have a good day!');
        $this->session->sess_destroy(); // Destroy session
        redirect(base_url()); // Redirect to login page
    }
}
