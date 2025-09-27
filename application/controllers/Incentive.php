<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Incentive extends CI_Controller
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
        $this->load->model('Employee_Model');
        $this->load->model('Incentive_Model');
    }


    public function index()
    {


        
    }


    public function Position_Report(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Position Report';

            
            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Incentive/Employee_Position_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }

    }

    public function Employee_Details(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
            $UserRole =  $Session['UserType'];

            $this->data['Employee_Details'] = $Employee_Details = $this->Employee_Model->Employee_Details($CompanyCode, $LocationCode, $Login_User, $UserRole);

            if (isset($Employee_Details) && !empty($Employee_Details)) {

                echo json_encode($this->data);

            } 

        } else {

            redirect(base_url(), 'refresh');
        }

    }

    public function Employee_Position_Details(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
            $UserRole =  $Session['UserType'];

            $From_Date = $this->input->post('From_Date');
            $To_Date = $this->input->post('To_Date');
            // $Position_Grade = $this->input->post('Position_Grade');
            $Employee_Id = $this->input->post('Employee_Id');

            // if($Login_User == )


            $this->data['Employee_Position_Details'] = $Employee_Position_Details = $this->Incentive_Model->Employee_Position_Details($CompanyCode, $LocationCode, $Login_User, $UserRole, $From_Date, $To_Date, $Employee_Id);

            if ($Employee_Position_Details != 0) {

                echo json_encode($this->data);

            } else {

                $Message = [
                    'Status' => 'Error',
                    'Message' => 'Employee Position Grade Details Not Found.'
                ];

                echo json_encode($Message);

            }

        } else {

            redirect(base_url(), 'refresh');
        }


    }

  

}