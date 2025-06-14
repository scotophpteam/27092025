<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Management extends CI_Controller
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
        $this->load->model('Management_Model');
    }


    public function Employee()
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Request';

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Management/Employee_Request', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Leave_Apply()
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Employee Leave Request';

            if ($this->input->post()) {

                $Applied_Date = $this->input->post('Applied_Date');
                $Employee_ID = $this->input->post('Employee_ID');
                $From_Date = $this->input->post('From_Date');
                $To_Date = $this->input->post('To_Date');
                $Remarks = $this->input->post('Remarks');

                $this->data['Leave_Apply'] = $Leave_Apply = $this->Management_Model->Leave_Apply($CompanyCode, $LocationCode, $Login_User, $Applied_Date, $Employee_ID, $From_Date, $To_Date, $Remarks);


                if ($Leave_Apply == 2) {

                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Already This Date Applied Leave For The Employee.',
                    );

                    echo  json_encode($Reponse);
                } else if ($Leave_Apply == 1) {


                    $Reponse = array(
                        'status' => 'success',
                        'message' => 'Employee Leave Applied Successfully..',
                    );

                    echo  json_encode($Reponse);
                }




                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Management/Employee_Leave_Apply', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];



            $this->data['Employee_List'] = $Employee_List = $this->Management_Model->Employee_List($CompanyCode, $LocationCode, $Login_User);

            if ($Employee_List == 0) {

                $Reponse = array(
                    'status' => 'error',
                    'message' => 'Employee Details Not Found.',
                );

                echo  json_encode($Reponse);
            } else {

                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function Leave_Apply_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Employee Leave Apply List';

            if ($this->input->post()) {

                $From_Date = $this->input->post('From_Date');
                $To_Date = $this->input->post('To_Date');

                $this->data['Leave_Apply_List'] = $Leave_Apply_List = $this->Management_Model->Leave_Apply_List($CompanyCode, $LocationCode, $Login_User, $From_Date ,$To_Date);

                if ($Leave_Apply_List == 0) {

                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Leave Apply Employee Details Not Found.',
                    );

                    echo  json_encode($Reponse);
                } else {

                    echo  json_encode($this->data);
                }

                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Management/Leave_Apply_List', $this->data);
            $this->load->view('Frontend/Footer');
        } else {

            redirect(base_url(), 'refresh');
        }
    }
}
