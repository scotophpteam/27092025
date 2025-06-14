<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Work extends CI_Controller
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
        $this->load->model('Late_Extra_Model');
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


    public function Shifts()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $UserName =  $Session['UserName'];

            $this->data['Shifts'] = $Shifts = $this->Work_Model->Shifts($CompanyCode, $LocationCode);

            if (isset($Shifts) && !empty($Shifts)) {

                echo json_encode($this->data);
            } else {

                echo json_encode(array('error' => 'No Data Found'));
            }
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


            $this->data['Get_Sub_Section'] = $Get_Sub_Section = $this->Work_Model->Get_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Login_User);

            if (isset($Get_Sub_Section) && !empty($Get_Sub_Section)) {

                echo json_encode($this->data);
            } else {

                echo json_encode(array('error' => 'No Data Found'));
            }
        } else {

            redirect(base_url(), 'refresh');
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



    public function Shift_Close()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Work Allocation';


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Alloction/Index', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Shift_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');

            $this->data['Shift_Employee_List'] = $Shift_Employee_List = $this->Work_Model->Shift_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type);
            $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode, $LocationCode, $Login_User);
            $this->data['Work_Allocation_Details_Count'] = $Work_Allocation_Details_Count = $this->Work_Model->Work_Allocation_Details_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);
            $this->data['Get_Allocated_Machine_ID'] = $Get_Allocated_Machine_ID = $this->Work_Model->Get_Allocated_Machine_ID($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type);



            if (isset($Shift_Employee_List)) {
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

            $this->data['Work_Type'] = $Work_Type = $this->Work_Model->Work_Type($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorArea, $JobCardNo);
            // $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode,$LocationCode,$Login_User);



            if (isset($Work_Type)) {
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

    public function Frame()
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
            $Machine_Id = $this->input->post('Machine_Id');

            $this->data['Frame'] = $Frame = $this->Work_Model->Frame($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorkArea, $JobCardNo, $Machine_Id);

            if (isset($Frame)) {
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


    public function Save()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $input_data = json_decode($this->input->raw_input_stream, true);

            // print_r($input_data);exit;

            $this->data['Work_Allocation'] = $Work_Allocation = $this->Work_Model->Assign($input_data, $CompanyCode, $LocationCode);


            foreach ($input_data['Allocations'] as $row) {
                $Department = $row['Department'];
                $Date = $row['Date'];
                $Shift = $row['Shift'];
                $Work_Area = $row['Work_Area'];
                $Employee_Id = $row['EmployeeId'];
                $Frame = $row['Frames'];
                // $FrameType = $row['FrameType'];
                $Machine_Id = $row['Machine_Id'];
                $Job_Card_No = $row['JobCardNo'];
                $Type = $row['Allocation_Type'];
            }

            echo json_encode(1);
        } else {
            redirect(base_url());
        }
    }

    public function Edit()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $input_data = json_decode($this->input->raw_input_stream, true);

            $this->data['Edit'] = $Work_Allocation = $this->Work_Model->Edit($input_data, $CompanyCode, $LocationCode);


            foreach ($input_data['Allocations'] as $row) {
                $Department = $row['Department'];
                $Date = $row['Date'];
                $Shift = $row['Shift'];
                $Work_Area = $row['Work_Area'];
                $Employee_Id = $row['EmployeeId'];
                $Frame = $row['Frames'];
                $Machine_Id = $row['Machine_Id'];
                // $Job_Card_No = $row['JobCardNo'] ?: '';
                $Type = $row['Allocation_Type'];
            }


            if (isset($Work_Allocation)) {

                echo  json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }
    }



    public function Previous_Allocation()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Type = $this->input->post('Type');

            $this->data['Previous_Allocation'] = $Previous_Allocation = $this->Work_Model->Previous_Allocation($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type);

            if (isset($Previous_Allocation)) {
                echo  json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }
    }

    public function Late_Extra()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $this->data['Favicon'] = 'Precot | Late And Extra Work Allocation';
            $this->data['Department'] = $Department = $Session['Department'];

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Work/Late_Extra_Work_Allocation', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    public function Late_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Late_And_Extra_Employee_List'] = $Late_And_Extra_Employee_List = $this->Late_Extra_Model->Late_And_Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);
            $this->data['Late_And_Extra_Employee_Count'] = $Late_And_Extra_Employee_Count = $this->Late_Extra_Model->Late_And_Extra_Employee_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);
            $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode, $LocationCode, $Login_User);

            if (isset($Late_And_Extra_Employee_List)) {
                echo  json_encode($this->data);
            }

        } else {
            redirect(base_url());
        }
    }


    public function Late_Extra_Save()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $input_data = json_decode($this->input->raw_input_stream, true);

            $this->data['Work_Allocation'] = $Work_Allocation = $this->Late_Extra_Model->Assign($input_data, $CompanyCode, $LocationCode);


            foreach ($input_data['Allocations'] as $row) {
                $Department = $row['Department'];
                $Date = $row['Date'];
                $Shift = $row['Shift'];
                $Work_Area = $row['Work_Area'];
                $Employee_Id = $row['EmployeeId'];
                $Frame = $row['Frames'];
                // $FrameType = $row['FrameType'];
                $Machine_Id = $row['Machine_Id'];
                $Job_Card_No = $row['JobCardNo'];
                $Type = $row['Allocation_Type'];
            }

            echo json_encode(1);
        } else {
            redirect(base_url());
        }
    }


    public function Late_Seperated_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');


            $this->data['Seperated_Sub_Section'] = $Seperated_Sub_Section = $this->Late_Extra_Model->Seperated_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Section, $Login_User);
            $this->data['User_Department'] = $User_Department = $this->Work_Model->User_Department($CompanyCode, $LocationCode, $Login_User);

            if (isset($Seperated_Sub_Section) && !empty($Seperated_Sub_Section)) {

                echo json_encode($this->data);
            } else {

                $Reponse = array(
                    'status' => 'error',
                    'message' => 'Employee Details Not Found.',
                );

                echo  json_encode($Reponse);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }



    public function Partial_Close()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Partial Close Employees';


            if ($_POST) {

                $CompanyCode =  $Session['Ccode'];
                $LocationCode =  $Session['Lcode'];
                $UserName =  $Session['UserName'];
                $Shift = $this->input->post('Shift');
                $Date = $this->input->post('Date');
                $Department = $this->input->post('Department');
                $Employee_Id = $this->input->post('Employee_Id');
                $Reason = $this->input->post('Reason');

                $this->data['Partial_Close_Work'] = $Partial_Close_Work = $this->Work_Model->Partial_Close_Work($CompanyCode, $LocationCode, $Date, $Shift, $Department, $Employee_Id, $Reason);

                if ($Partial_Close_Work == TRUE) {

                    $Reponse = array(
                        'status' => 'success',
                        'mesaage' => 'Employee Partial Work Closed.',
                    );

                    echo json_encode($Reponse);
                } else {

                    $Reponse = array(
                        'status' => 'error',
                        'mesaage' => 'Employee Partial Work Not Closed.',
                    );

                    echo json_encode($Reponse);
                }

                exit;
            }


            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Work/Partial_Closing', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    //   public function Late_And_Extra_Employee_List(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //     $Date = $this->input->post('Date');
    //     $Assigning_Shift = $this->input->post('Assigning_Shift');

    //     $this->data['Late_And_Extra_Employee_List'] = $Late_And_Extra_Employee_List = $this->Work_Model->Late_And_Extra_Employee_List($CompanyCode,$LocationCode,$Login_User,$Date,$Assigning_Shift);

    //     if(isset($Late_And_Extra_Employee_List)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    //   public function Late_And_Extra_Employee_WorkArea(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];
    //     $Sub_Department = $this->input->post('Sub_Department');

    //     $this->data['Late_And_Extra_Employee_WorkArea'] = $Late_And_Extra_Employee_WorkArea = $this->Work_Model->Late_And_Extra_Employee_WorkArea($CompanyCode,$LocationCode,$Login_User,$Sub_Department);

    //     if(isset($Late_And_Extra_Employee_WorkArea)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    //  public function Late_And_Extra_Employee_JobCardNo(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //    $WorkArea =  $this->input->post('Work_Area');

    //     $this->data['Late_And_Extra_Employee_JobCardNo'] = $Late_And_Extra_Employee_JobCardNo = $this->Work_Model->Late_And_Extra_Employee_JobCardNo($CompanyCode,$LocationCode,$Login_User,$WorkArea);

    //     if(isset($Late_And_Extra_Employee_JobCardNo)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }



    //  public function Late_And_Extra_Employee_Frames(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //    $WorkArea =  $this->input->post('Work_Area');
    //    $Job_Card_No = $this->input->post('JobCard_no');

    //     $this->data['Late_And_Extra_Employee_Frames'] = $Late_And_Extra_Employee_Frames = $this->Work_Model->Late_And_Extra_Employee_Frames($CompanyCode,$LocationCode,$Login_User,$WorkArea,$Job_Card_No);

    //     if(isset($Late_And_Extra_Employee_Frames)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    // public function Late_Employee_Frame(){

    //      $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //     $Date = $this->input->post('Date');
    //     $Shift = $this->input->post('Shift');
    //     $Department = $this->input->post('Department');
    //     $WorkArea = $this->input->post('WorkArea');
    //     $JobCardNo = $this->input->post('JobCardNo');


    //     $this->data['Late_Employee_Frame'] = $Late_Employee_Frame = $this->Work_Model->Late_Employee_Frame($CompanyCode,$LocationCode,$Login_User,$WorkArea,$Department,$Date,$Shift);

    //     if(isset($Late_Employee_Frame)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    // public function Late_Employee_Machine_Id(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //     $Date = $this->input->post('Date');
    //     $Shift = $this->input->post('Shift');
    //     $Department = $this->input->post('Department');
    //     $WorkArea = $this->input->post('WorkArea');
    //     $Frame = $this->input->post('Frame');


    //     $this->data['Late_Employee_Machine_Id'] = $Late_Employee_Machine_Id = $this->Work_Model->Late_Employee_Machine_Id($CompanyCode,$LocationCode,$Login_User,$WorkArea,$Department,$Frame);

    //     if(isset($Late_Employee_Machine_Id)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }

    // public function Late_And_Extra_Employee_Sub_Department(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];

    //     $this->data['Late_And_Extra_Employee_Sub_Department'] = $Late_And_Extra_Employee_Sub_Department = $this->Work_Model->Late_And_Extra_Employee_Sub_Department($CompanyCode,$LocationCode,$Login_User);

    //     if(isset($Late_And_Extra_Employee_Sub_Department)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }

    // public function Sepereted_Employee_List(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];
    //     $Date = $this->input->post('Date');
    //     $Shift = $this->input->post('Shift');
    //     $Sub_Section = $this->input->post('Sub_Section');

    //     $this->data['Sepereted_Employee_List'] = $Sepereted_Employee_List = $this->Work_Model->Sepereted_Employee_List($CompanyCode,$LocationCode,$Login_User,$Shift,$Date,$Sub_Section);

    //     if(isset($Sepereted_Employee_List)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    // public function Late_Employee_Sub_Section_Wise(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];
    //     $Date = $this->input->post('Date');
    //     $Shift = $this->input->post('Shift');
    //     $Sub_Section = $this->input->post('Sub_Section');

    //     $this->data['Late_Employee_Sub_Section_Wise'] = $Late_Employee_Sub_Section_Wise = $this->Work_Model->Late_Employee_Sub_Section_Wise($CompanyCode,$LocationCode,$Login_User,$Shift,$Date,$Sub_Section);

    //     if(isset($Late_Employee_Sub_Section_Wise)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }


    // public function Sub_Section_Assgined_Employee_Id(){

    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

    //     $CompanyCode =  $Session['Ccode'];
    //     $LocationCode =  $Session['Lcode'];
    //     $Login_User =  $Session['UserName'];
    //     $Date = $this->input->post('Date');
    //     $Shift = $this->input->post('Shift');
    //     $Sub_Section = $this->input->post('Sub_Section');
    //     $Sub_Department = $this->input->post('Department');

    //     $this->data['Sub_Section_Assgined_Employee_Id'] = $Sub_Section_Assgined_Employee_Id = $this->Work_Model->Sub_Section_Assgined_Employee_Id($CompanyCode,$LocationCode,$Login_User,$Shift,$Date,$Sub_Section,$Sub_Department);

    //     if(isset($Sub_Section_Assgined_Employee_Id)){
    //         echo  json_encode($this->data);
    //     }

    //     } else {
    //         redirect(base_url());
    //     }

    // }





}
