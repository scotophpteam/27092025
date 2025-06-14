<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->model('Admin_Model');
    }

    public function index()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Admin Reports';
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    public function Unit_Details()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Admin Unit Reports';

            if ($_POST) {

                $Date = $this->input->post('Date');

                $this->data['Unit_Details'] = $Unit_Details = $this->Admin_Model->Unit_Details($Date, $LocationCode, $CompanyCode);

                echo json_encode($this->data);
                exit();
            }


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Admin/Shift_Work_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {

            redirect(base_url(), 'refresh');
        }
    }
}
