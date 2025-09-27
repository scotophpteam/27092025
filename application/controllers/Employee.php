<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Employee extends CI_Controller
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
        $this->load->model('Master_Model');
    }


    public function index()
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Work Allocation';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Work/Work_Allocation', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Punching_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Punching Report';

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Attendance/Employee_Punching_List', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Employee_Attendance()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Punching Report';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Punching_Type = $this->input->post('Punching_Type');

                $this->data['Employee_Punching_List'] = $Employee_Punching_List = $this->Employee_Model->Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type);

                if ($Employee_Punching_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {

                    echo json_encode($this->data);
                }
                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Employee/Employee_Attendance_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Employee_Punching_List_Download()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Punching Report';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Punching_Type = $this->input->post('Punching_Type');

                $this->data['Employee_Punching_List'] = $Employee_Punching_List = $this->Employee_Model->Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type);

                if ($Employee_Punching_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {
                }
                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Employee/Employee_Attendance_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Attendance_Entry()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Manual Attendance';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Punching_Type = $this->input->post('Punching_Type');

                $this->data['Employee_Punching_List'] = $Employee_Punching_List = $this->Employee_Model->Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type);

                if ($Employee_Punching_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {
                }
                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Attendance/Attendance_Entry', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Shift_Details()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | ';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];


            $this->data['Shift_Details'] = $Shift_Details = $this->Master_Model->Shift_Details($CompanyCode, $LocationCode, $Login_User);

            if ($Shift_Details == 0) {

                $Response = [
                    'Status' => 'Error',
                    'Message' => 'Shift Details Not Found.'
                ];

                echo json_encode($Response);
            } else {

                echo json_encode($this->data);
            }
            exit();
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Shift_Timings()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | ';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Shift = $this->input->post('Shift');

                $this->data['Shift_Timings'] = $Shift_Timings = $this->Master_Model->Shift_Timings($CompanyCode, $LocationCode, $Login_User, $Shift);

                if ($Shift_Timings == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Shift Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {

                    echo json_encode($this->data);
                }
                exit();
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Shift_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = '';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['Shift_Employee_List'] = $Shift_Employee_List = $this->Employee_Model->Shift_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($Shift_Employee_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {

                    echo json_encode($this->data);
                }
                exit();
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Manual_Attendance_Entry()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = '';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Working_Type = $this->input->post('Working_Type');
                $Employee_Id = $this->input->post('Employee_Id');
                $Punching_Type = $this->input->post('Punching_Type');
                $From_Time = $this->input->post('From_Time');
                $To_Time = $this->input->post('To_Time');
                $Total_Working_Hour = $this->input->post('Total_Working_Hour');
                $Total_OT_Hour = $this->input->post('Total_OT_Hour');
                $Supervisor = $this->input->post('Supervisor');

                $this->data['Manual_Attendance_Entry'] = $Manual_Attendance_Entry = $this->Employee_Model->Manual_Attendance_Entry($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Working_Type, $Employee_Id, $Punching_Type, $From_Time, $To_Time, $Total_Working_Hour, $Total_OT_Hour, $Supervisor);

                echo json_encode($Manual_Attendance_Entry);
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    public function Get_Punching_List(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = '';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                
                $this->data['Get_Punching_List'] = $Get_Punching_List = $this->Employee_Model->Get_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if($Get_Punching_List == 0){

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Shift Not Starting Employee Details Not Found..'
                    ]);

                } else {

                    echo json_encode($this->data);

                }
                
            }
        } else {
            redirect(base_url(), 'refresh');
        }

    }

    public function Details_Punching_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee Punching Details';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Punching_Type = $this->input->post('Punching_Type');

                $this->data['Employee_Punching_List'] = $Employee_Punching_List = $this->Employee_Model->Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type);

                if ($Employee_Punching_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];

                    echo json_encode($Response);
                } else {
                }
                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Attendance/Employee_Punching_Details', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


       public function Get_Punching_List_Details(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = '';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                
                $this->data['Get_Punching_List_Details'] = $Get_Punching_List_Details = $this->Employee_Model->Employee_Punching_List_Download_Login_Det($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if($Get_Punching_List_Details == 0){

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Shift Not Starting Employee Details Not Found..'
                    ]);

                } else {

                    echo json_encode($this->data);

                }
                
            }
        } else {
            redirect(base_url(), 'refresh');
        }

    }
}
