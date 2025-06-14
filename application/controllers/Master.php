<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Master extends CI_Controller
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
        $this->load->model('Master_Model');
        $this->load->model('Work_Model');

    }


    public function Shifts(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $UserName =  $Session['UserName'];

        $this->data['Shifts'] = $Shifts = $this->Work_Model->Shifts($CompanyCode,$LocationCode);

        if(isset($Shifts) && !empty($Shifts)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}


    public function Department(){

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $UserName =  $Session['UserName'];

        $this->data['Department'] = $Department = $this->Master_Model->Department($CompanyCode,$LocationCode,$UserName);

        if(isset($Department) && !empty($Department)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }

    }



    public function Work_Area(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $LoginUser =  $Session['UserName'];

        $Department = $this->input->post('Department');

        $this->data['Work_Areas'] = $Work_Areas = $this->Master_Model->Work_Areas($CompanyCode,$LocationCode,$Department,$LoginUser);

        if(isset($Work_Areas) && !empty($Work_Areas)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}

public function JobCards(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $Login_User =  $Session['UserName'];

        $Department = $this->input->post('Department');
        $Work_Area = $this->input->post('Work_Area');

        $this->data['JobCards'] = $JobCards = $this->Master_Model->JobCards($CompanyCode,$LocationCode,$Department,$Work_Area,$Login_User);

        if(isset($JobCards) && !empty($JobCards)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}



public function Wages(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $UserName =  $Session['UserName'];

        $Department = $this->input->post('Department');

        $this->data['Wages'] = $Wages = $this->Master_Model->Wages($CompanyCode,$LocationCode,$Department);

        if(isset($Wages) && !empty($Wages)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}


public function Employee_List(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $Login_User =  $Session['UserName'];

        $Date = $this->input->post('Date');
        $Shift = $this->input->post('Assigning_Shift');
        $Department = $this->input->post('Department');
        $Employee_Type = $this->input->post('Employee_Type');
        $Work_Area = $this->input->post('WorkArea');
        $JobCard = $this->input->post('JobCard');


        $this->data['Employee_List'] = $Employee_List = $this->Master_Model->Employee_List($CompanyCode,$LocationCode,$Date,$Shift,$Department,$Employee_Type,$Work_Area,$JobCard,$Login_User);
        $this->data['Machines'] = $Machines = $this->Master_Model->Machines($CompanyCode,$LocationCode,$Date,$Shift,$Department,$Work_Area,$JobCard);

        if(isset($Employee_List) && !empty($Employee_List)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}

public function Machine_Frames(){

     $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $LocationCode =   $Session['Lcode'];
        $CompanyCode =   $Session['Ccode'];
        $UserName =  $Session['UserName'];

        //  $Date = '2025-01-24';

        $Date = $this->input->post('Date');
        $Shift = $this->input->post('Shift');
        $Department = $this->input->post('Department');
        $Employee_Type = $this->input->post('Employee_Type');
        $Work_Area = $this->input->post('Work_Area');
        $JobCard = $this->input->post('JobCardNo');
        $Machine_Id = $this->input->post('Machine_Id');



        $this->data['Machine_Frames'] = $Machine_Frames = $this->Master_Model->Machine_Frames($CompanyCode,$LocationCode,$Date,$Shift,$Department,$Work_Area,$JobCard,$Machine_Id);

        if(isset($Machine_Frames) && !empty($Machine_Frames)){

            echo json_encode($this->data);

        } else {

            echo json_encode(array('error' => 'No Data Found'));

        }

    }  else {

        redirect(base_url(), 'refresh');

    }
}


    public function Assgined_Employee_Id(){


        $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $UserName =  $Session['UserName'];
            $Shift = $this->input->post('Shift');
            $Date = $this->input->post('Date');
            $Department = $this->input->post('Department');

            $this->data['Assgined_Employee_Id'] = $Assgined_Employee_Id = $this->Work_Model->Assgined_Employee_Id($CompanyCode,$LocationCode,$Date,$Shift,$Department);

            if($Assgined_Employee_Id != FALSE){

                echo json_encode($Assgined_Employee_Id);

            } else {

                $Reponse = array (
                    'status' => 'error',
                    'mesaage' => 'Employee Details Not Found!!.',
                );

                echo json_encode($Reponse);

            }

        } else {
            redirect(base_url(), 'refresh');
        }

    }


        public function Machine_Master(){


         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

             $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $UserName =  $Session['UserName'];

           $this->data['Favicon'] = 'Precot | Machine Master';

            $this->load->view('Frontend/Header',$this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Master/Machine_Master', $this->data);
            $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url(), 'refresh');
        }

    }

     public function Machine_Sub_Department()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $CompanyCode =  $Session['Ccode'];
        $LocationCode =  $Session['Lcode'];
        $Login_User =  $Session['UserName'];

        $this->data['Machine_Sub_Department'] = $Machine_Sub_Department = $this->Master_Model->Machine_Sub_Department($CompanyCode,$LocationCode,$Login_User);

        if(isset($Machine_Sub_Department)){
            echo  json_encode($this->data);
        }

        } else {
            redirect(base_url());
        }
    }


    public function Department_Work_Areas()
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Department']=$Department = $this->input->post('Department');

            $this->data['Work_Areas']= $Work_Areas = $this->Master_Model->Work_Area($CompanyCode,$LocationCode,$Department,$Login_User);

            if(isset($Work_Areas)){
                echo  json_encode($this->data);

           } else {
               redirect(base_url());
           }
      }
   }

   public function Machine_Id()
   {
    $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $CompanyCode =  $Session['Ccode'];
        $LocationCode =  $Session['Lcode'];
        $Login_User =  $Session['UserName'];

        $this->data['Department']=$Department = $this->input->post('Department');
        $this->data['WorkArea']=$WorkArea = $this->input->post('WorkArea');

        $this->data['Machine_Id']= $Machine_Id = $this->Master_Model->Machine_Id($CompanyCode,$LocationCode,$Department,$WorkArea);

        if(isset($Machine_Id)){
            echo  json_encode($this->data);

       } else {
           redirect(base_url());
       }
     }
   }

   public function Machine_Mapping_Update()
{
    $Session = $this->session->userdata('sess_array');
    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

        $Department = $this->input->post('Department');
        $WorkArea = $this->input->post('WorkArea');
        $Machine_Id_Array = $this->input->post('Machine_Id');
        $Machine_Group_Name = $this->input->post('Machine_Group_Name');
        $Machine_Frame_Name = $this->input->post('Machine_Frame_Name');

        if (!empty($Machine_Id_Array) && is_array($Machine_Id_Array)) {

            // Check if the frame already exists
            $frameExists = $this->Master_Model->is_frame_exist($Department, $WorkArea, $Machine_Frame_Name);

            if ($frameExists) {
                echo json_encode(['status' => 'warning', 'message' => 'This frame already exists for this Frame Type.']);
                return;
            }

            $result = $this->Master_Model->insert_machine_mapping(
                $CompanyCode,
                $LocationCode,
                $Department,
                $WorkArea,
                $Machine_Id_Array,
                $Machine_Group_Name,
                $Machine_Frame_Name,
                $Login_User
            );

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Machine Mapping inserted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'No Machine IDs provided']);
        }
    } else {
        redirect(base_url());
    }
}

    public function Standard_Actual(){


         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

             $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $UserName =  $Session['UserName'];

           $this->data['Favicon'] = 'Precot | Standard  & Actual Record';

            $this->load->view('Frontend/Header',$this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Master/Standard_Actual', $this->data);
            $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url(), 'refresh');
        }

    }


      public function Departments(){

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Departments'] = $Departments = $this->Master_Model->Departments($CompanyCode,$LocationCode,$Login_User);

            echo json_encode($this->data);

        } else {
            redirect(base_url());
        }

    }

    public function Sub_Departments(){

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];
            $Department = $this->input->post('Department');

            $this->data['Sub_Departments'] = $Sub_Departments = $this->Master_Model->Sub_Departments($CompanyCode,$LocationCode,$Login_User, $Department);

            echo json_encode($this->data);

        } else {
            redirect(base_url());
        }

    }

     public function Positions(){

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];
            $Department = $this->input->post('Department');
            $Sub_Department = $this->input->post('Sub_Department');

            $this->data['Position'] = $Position = $this->Master_Model->Position($CompanyCode,$LocationCode,$Login_User, $Department,$Sub_Department);

            echo json_encode($this->data);

        } else {
            redirect(base_url());
        }

    }


    public function Standard_Actual_List(){

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Standard_Actual_List'] = $Standard_Actual_List = $this->Master_Model->Standard_Actual_List($CompanyCode,$LocationCode,$Login_User,$Date,$Shift);

            echo json_encode($this->data);




        } else {
            redirect(base_url());
        }

    }


public function Insert_Standard_Actual() {

    $Session = $this->session->userdata('sess_array');
    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

        $Department = $this->input->post('Department');
        $Sub_Department = $this->input->post('Sub_Department');
        $Work_Area = $this->input->post('WorkArea');
        $Employee_Count = $this->input->post('Employee_Count');
        $Shift = $this->input->post('Shift');

        if (empty($Department) || empty($Sub_Department) || empty($Work_Area) || empty($Employee_Count)) {
            $this->data['Insert_Standard_Actual'] = $Message = [
                'Status' => 'Error',
                'Message' => 'All fields are required. Please verify the data you entered.'
            ];
            echo json_encode($Message);
            return;
        }

        $Insert_Standard_Actual = $this->Master_Model->Insert_Standard_Actual(
            $CompanyCode,
            $LocationCode,
            $Login_User,
            $Department,
            $Sub_Department,
            $Work_Area,
            $Employee_Count,
            $Shift
        );

        $this->data['Standard_Master_List'] = $Standard_Master_List = $this->Master_Model->Standard_Master_List($CompanyCode,$LocationCode,$Login_User);


        // print_r($Insert_Standard_Actual);
        // exit;

        if ($Insert_Standard_Actual == 2) {
             $this->data['Insert_Standard_Actual'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Already Standard Add For This Position So Only Updated For Employee Count'
            ];
        } else if($Insert_Standard_Actual == 1) {
            $this->data['Insert_Standard_Actual'] = $Message = [
                'Status' => 'Success',
                'Message' => 'Standard Actual details have been updated successfully!'
            ];
        } else{

             $this->data['Insert_Standard_Actual'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Update Standard Actual Details.'
            ];

        }

        echo json_encode($this->data);

    } else {
        redirect(base_url());
    }
}

public function Standard_Actual_Report(){

    $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Standard_Master_List'] = $Standard_Master_List = $this->Master_Model->Standard_Master_List($CompanyCode,$LocationCode,$Login_User);

           if($Standard_Master_List == 0){

            $this->data['Insert_Standard_Actual'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Fatch Standard Actual Details.'
            ];

            echo json_encode($this->data);

           } else {

            echo json_encode($this->data);
           }

        } else {
            redirect(base_url());
        }

}

public function Standard_Edit_List(){

    $Session = $this->session->userdata('sess_array');
    if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];
            $Standard_ID = $this->input->post('Standard_ID');

            $this->data['Standard_Edit_List'] = $Standard_Edit_List = $this->Master_Model->Standard_Edit_List($CompanyCode,$LocationCode,$Login_User,$Standard_ID);

           if($Standard_Edit_List == 0){

            $this->data['Standard_Edit_List'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Fatch Standard Details.'
            ];

           } else {

            echo json_encode($this->data);
           }

        } else {
            redirect(base_url());
        }

}

        public function Standard_Edit(){

            $Session = $this->session->userdata('sess_array');
           if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];
            $Standard_ID = $this->input->post('Standard_ID');
            $Employee_Count = $this->input->post('Employee_Count');

            $this->data['Standard_Edit'] = $Standard_Edit = $this->Master_Model->Standard_Edit($CompanyCode,$LocationCode,$Login_User,$Standard_ID,$Employee_Count);
            $this->data['Standard_Master_List'] = $Standard_Master_List = $this->Master_Model->Standard_Master_List($CompanyCode,$LocationCode,$Login_User);

           if($Standard_Edit == 0){

            $this->data['Standard_Edit'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Fatch Standard Details.'
            ];

           echo json_encode($this->data);

           } else {

            echo json_encode($this->data);
           }

        } else {
            redirect(base_url());
        }

        }


        public function Standard_Delete(){

            $Session = $this->session->userdata('sess_array');
            if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];
            $Standard_ID = $this->input->post('Standard_ID');

            $this->data['Standard_Delete'] = $Standard_Delete = $this->Master_Model->Standard_Delete($CompanyCode,$LocationCode,$Login_User,$Standard_ID);
            $this->data['Standard_Master_List'] = $Standard_Master_List = $this->Master_Model->Standard_Master_List($CompanyCode,$LocationCode,$Login_User);

           if($Standard_Delete == 0){

            $this->data['Standard_Edit_List'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Fatch Standard Details.'
            ];

           } else {

            echo json_encode($this->data);
           }

        } else {
            redirect(base_url());
        }

        }

        public function Supervisor_List(){

             $Session = $this->session->userdata('sess_array');
            if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Supervisor_List'] = $Supervisor_List = $this->Master_Model->Supervisor_List($CompanyCode,$LocationCode,$Login_User);
            // print_r($Supervisor_List);exit;

           if($Supervisor_List == 0){

            $this->data['Supervisor_List'] = $Message = [
                'Status' => 'Error',
                'Message' => 'Faild To Fatch Supervisor Details.'
            ];

                echo json_encode($this->data);

           } else {

            echo json_encode($this->data);
           }

        } else {
            redirect(base_url());
        }

        }


        public function Sub_Section_Employee_Count(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Sub_Section_Employee_Count'] = $Sub_Section_Employee_Count = $this->Master_Model->Sub_Section_Employee_Count($CompanyCode, $LocationCode, $Login_User,$Date,$Shift);
            // print_r($Supervisor_List);exit;

            if ($Sub_Section_Employee_Count == 0) {

                $this->data['Sub_Section_Employee_Count'] = $Message = [
                    'Status' => 'Error',
                    'Message' => 'Faild To Fatch Supervisor Details.'
                ];

                echo json_encode($this->data);
            } else {

                echo json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }

        }


        public function Employee_Home_Page(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Employee_Home_Page'] = $Employee_Home_Page = $this->Master_Model->Employee_Home_Page($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if ($Employee_Home_Page == 0) {

                $this->data['Sub_Section_Employee_Count'] = $Message = [
                    'Status' => 'Error',
                    'Message' => 'Faild To Fatch Supervisor Details.'
                ];

                echo json_encode($this->data);
            } else {

                echo json_encode($this->data);
            }
        } else {
            redirect(base_url());
        }

        }



}