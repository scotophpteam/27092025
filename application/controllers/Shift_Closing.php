<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Shift_Closing extends CI_Controller
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
        $this->load->model('Shift_Closing_Model');
    }


    public function index()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Shift Closing';

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Work/Shift_Closing', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Depertment()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $UserName =  $Session['UserName'];

            $this->data['Departments'] = $Departments = $this->Shift_Closing_Model->Departments($CompanyCode, $LocationCode, $UserName);

            if (isset($Departments) && !empty($Departments)) {
                echo json_encode($this->data);
            } else {
                echo json_encode(array('error' => 'No Data Found'));
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }



    public function Shifts()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $UserName =  $Session['UserName'];

            $this->data['Shifts'] = $Shifts = $this->Shift_Closing_Model->Shifts($CompanyCode, $LocationCode);

            if (isset($Shifts) && !empty($Shifts)) {

                echo json_encode($this->data);
            } else {

                echo json_encode(array('error' => 'No Data Found'));
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }


    public function Allocation_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Allocation_List'] = $Allication_List =  $this->Shift_Closing_Model->Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if ($Allication_List) {

                echo json_encode($this->data);
            } else {
                echo json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }

    public function Employee_Shift_Closings()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $inputData = json_decode($this->input->raw_input_stream, true);

            $this->data['Employee_Shift_Closings'] = $Employee_Shift_Closings = $this->Shift_Closing_Model->Employee_Shift_Closings($inputData);

          echo json_encode($this->data);
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function Assigned_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');


            $this->data['Assigned_Employee_List'] = $Assigned_Employee_List = $this->Shift_Closing_Model->Assigned_Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Login_User);

            if ($Assigned_Employee_List == 0) {

                $Reponse = array(
                    'status' => 'error',
                    'mesaage' => 'Employee Partial Work Not Closed.',
                );

                echo json_encode($Reponse);
            } else {

                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function Partial_Closing()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Employee_Id = $this->input->post('Employee_Id');
            $SupervisorName = $this->input->post('Supervisor');
            $Reason = $this->input->post("Reason");

            $this->data['Partial_Closing'] = $Partial_Closing = $this->Shift_Closing_Model->Partial_Closing($CompanyCode, $LocationCode, $Date, $Shift, $Employee_Id, $SupervisorName, $Reason);

            if ($Partial_Closing == 1) {

                $this->data['Assigned_Employee_List'] = $Assigned_Employee_List = $this->Shift_Closing_Model->Assigned_Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Login_User);

                echo json_encode($this->data);

            } else {

                $Reponse = array(

                    'status' => 'error',
                    'mesaage' => 'Details Not Updated Please Contact Support Team.',

                );

                echo json_encode($Reponse);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function Seperated_Assigned_Employee_List(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');


            $this->data['Seperated_Assigned_Employee_List'] = $Seperated_Assigned_Employee_List = $this->Shift_Closing_Model->Seperated_Assigned_Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Login_User, $Sub_Section);

            if ($Seperated_Assigned_Employee_List == 0) {

                $Reponse = array(
                    'status' => 'error',
                    'mesaage' => 'Employee Partial Work Not Closed.',
                );

                echo json_encode($Reponse);
            } else {

                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }

    }
}
