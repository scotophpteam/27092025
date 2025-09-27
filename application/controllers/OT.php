<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class OT extends CI_Controller
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
        $this->load->model('Home_Model');
        $this->load->model('Work_Model');
        $this->load->model('OT_Model');
    }


    public function index()
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Work Allocation';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('OT/OT_Details', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Details()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Employee OT Details';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('OT/OT_Details', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Contiune_Employee_List()
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

                $this->data['Contiune_Employee_List'] = $Contiune_Employee_List = $this->OT_Model->Contiune_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($Contiune_Employee_List == 0) {

                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'OT Working Employee Details Not Found.'
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

    public function Allocation()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Extra Work Allocation';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('OT/Work_Allocation', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Get_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');


            $this->data['Get_Sub_Section'] = $Get_Sub_Section = $this->OT_Model->Get_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Login_User);

            if (isset($Get_Sub_Section) && !empty($Get_Sub_Section)) {

                echo json_encode($this->data);
            } else {

                echo json_encode(array('error' => 'No Data Found'));
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }




    public function Extra_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');


            $this->data['Extra_Employee_List'] = $Extra_Employee_List = $this->OT_Model->Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date);
            $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode, $LocationCode, $Login_User);

            if (isset($Extra_Employee_List)) {
                echo  json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }
    }



    public function Work_Areas()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Department = $this->input->post('Department');


            $this->data['Work_Areas'] = $Work_Areas = $this->Work_Model->Work_Areas($CompanyCode, $LocationCode, $Login_User, $Department);

            if (isset($Work_Areas)) {
                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }

    public function Work_Type()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Department = $this->input->post('Department');
            $WorArea = $this->input->post('WorkArea');
            $JobCardNo = $this->input->post('JobCardNo');

            $this->data['Work_Type'] = $Work_Type = $this->OT_Model->Work_Type($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorArea, $JobCardNo);
            // $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode,$LocationCode,$Login_User);



            if (isset($Work_Type)) {
                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }

    public function Only_Machine_Id()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Department = $this->input->post('Department');
            $WorkArea = $this->input->post('WorkArea');
            $JobCardNo = $this->input->post('JobCardNo');
            $Frame = $this->input->post('frameSelect');

            $this->data['Only_Machine_Id'] = $Only_Machine_Id = $this->Work_Model->Only_Machine_Id($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorkArea, $JobCardNo, $Frame);

            if (isset($Only_Machine_Id)) {
                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }

    public function Machine_Ids()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Department = $this->input->post('Department');
            $WorkArea = $this->input->post('WorkArea');
            $JobCardNo = $this->input->post('JobCardNo');
            $Frame = $this->input->post('frameSelect');

            $this->data['Machine_Ids'] = $Machine_Ids = $this->Work_Model->Machine_Ids($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorkArea, $JobCardNo, $Frame);

            if (isset($Machine_Ids)) {
                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }

    public function Job_Card_Nos()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Department = $this->input->post('Department');
            $WorkArea = $this->input->post('WorkArea');


            $this->data['Job_Card_Nos'] = $Job_Card_Nos = $this->Work_Model->Job_Card_Nos($CompanyCode, $LocationCode, $Login_User, $Department, $WorkArea);

            if (isset($Job_Card_Nos)) {
                echo  json_encode($this->data);
            }
        } else {

            redirect(base_url());
        }
    }


    public function Extra_Save()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $input_data = json_decode($this->input->raw_input_stream, true);


            $this->data['Work_Allocation'] = $Work_Allocation = $this->Work_Model->Assign($input_data, $CompanyCode, $LocationCode);

            echo json_encode(1);
        } else {
            redirect(base_url());
        }
    }

    public function Seperated_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');


            $this->data['Seperated_Sub_Section'] = $Seperated_Sub_Section = $this->Work_Model->Seperated_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Section, $Login_User);
            $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode, $LocationCode, $Login_User);

            if (isset($Seperated_Sub_Section) && !empty($Seperated_Sub_Section)) {

                echo json_encode($this->data);
            } else {

                echo json_encode(array('error' => 'No Data Found'));
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    // public function Save()
    // {

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //         $CompanyCode =  $Session['Ccode'];
    //         $LocationCode =  $Session['Lcode'];
    //         $Login_User =  $Session['UserName'];

    //         $input_data = json_decode($this->input->raw_input_stream, true);

    //         // print_r($input_data);exit;

    //         $this->data['Work_Allocation'] = $Work_Allocation = $this->OT_Model->Assign($input_data, $CompanyCode, $LocationCode);

    //         echo json_encode(1);

    //     } else {
    //         redirect(base_url());
    //     }
    // }

    public function Edit()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $input_data = json_decode($this->input->raw_input_stream, true);

            
            $this->data['Edit'] = $Work_Allocation = $this->OT_Model->Edit($input_data, $CompanyCode, $LocationCode);


            if (isset($Work_Allocation)) {

                echo  json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }
    }



    public function Partial_Closing()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Extra Work Partial Closing';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('OT/Partial_Closing', $this->data);
            $this->load->view('Frontend/Footer');
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

            $this->data['Allocation_List'] = $Allication_List =  $this->OT_Model->Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

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

            $this->data['Employee_Shift_Closings'] = $Employee_Shift_Closings = $this->OT_Model->Employee_Shift_Closings($inputData);

            echo json_encode($this->data);
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function OT_Extra_Hours()
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | OT - Extra Hours Entry';

            if ($this->input->method() === 'post') {

                $CompanyCode =  $Session['Ccode'];
                $LocationCode =  $Session['Lcode'];
                $Login_User =  $Session['UserName'];

                $Input_data = json_decode($this->input->raw_input_stream, true);

                $this->data['OT_Extra_Hours_Entry'] = $OT_Extra_Hours_Entry = $this->OT_Model->OT_Extra_Hours_Entry($CompanyCode, $LocationCode, $Login_User, $Input_data);

                echo json_encode($this->data);
                exit();
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('OT/Employee_OT_Extra_Hours', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    public function Get_OT_Extra_Hours_List_Employee()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Type = $this->input->post('Type');

            $this->data['Get_OT_Extra_Hours_List_Employee'] = $Get_OT_Extra_Hours_List_Employee = $this->OT_Model->Get_OT_Extra_Hours_List_Employee($CompanyCode, $LocationCode, $Login_User, $Date, $Type);

            echo json_encode($this->data);
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function OT_Employee_Details()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');

            $this->data['OT_Employee_Details'] = $OT_Employee_Details = $this->OT_Model->OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type);

            echo json_encode($this->data);

        } else {

            redirect(base_url(), 'refresh');
        }
    }


    public function OT_Details_Entry()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Type = $this->input->post('Type');
                $Employee_Id = $this->input->post('EmpNo');
                $Employee_Name = $this->input->post('EmployeeName');
                $IN_Time = $this->input->post('InTime');
                $IN_Out = $this->input->post('InOut');
                $Actual_WHours = $this->input->post('Actual_WHours');
                $Emaster_UpdatedTime = $this->input->post('UpdatedTime');
                $Difference = $this->input->post('Difference');
                $Final_OTHours = $this->input->post('Final_ExtraHours');
                $Supervisor = $this->input->post('Supervisor');

                $this->data['OT_Details_Entry'] = $OT_Details_Entry = $this->OT_Model->OT_Details_Entry($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type, $Employee_Id, $Employee_Name, $IN_Time, $IN_Out, $Actual_WHours, $Emaster_UpdatedTime, $Difference, $Final_OTHours, $Supervisor);
                echo json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }


    public function No_Work_Employees()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');

            $this->data['No_Work_Employees'] = $No_Work_Employees = $this->OT_Model->No_Work_Employees($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type);



            echo json_encode($this->data);
        } else {

            redirect(base_url(), 'refresh');
        }
    }

    public function No_Work_Employee_Update()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            if($this->input->post()){

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');

            $Employee_Id = $this->input->post('EmployeeID');
            $Employee_Name = $this->input->post('Employee_Name');
            $IN_Time = $this->input->post('IN_Time');
            $IN_Out = $this->input->post('IN_OUT');
            $Attendance = $this->input->post('Attendance');
            $Supervisor = $this->input->post('Supervisor');

            $this->data['No_Work_Employees_Update'] = $No_Work_Employees_Update = $this->OT_Model->No_Work_Employees_Update($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type, $Employee_Id, $Employee_Name, $IN_Time, $IN_Out, $Attendance, $Supervisor);
            echo json_encode($this->data);

            }


        } else {
            redirect(base_url());
        }
    }
}
