<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Grade_Master extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('Master_Model');
        $this->load->model('Employee_Model');
        $this->load->model('Grade_Model');
    }


    public function index()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $this->data['Favicon'] = 'Precot | Employee Status';


            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            // $this->data['Sub_Dep'] = $Sub_Dep = $this->Master_Model->Sub_Depart($Ccode, $Lcode);

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Grade_Master/Employee_Status', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url());
        }
    }



    public function Get_Sub_Department()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | ';

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];


            $this->data['Get_Sub_Department'] = $Get_Sub_Department = $this->Grade_Model->Get_Sub_Department($CompanyCode, $LocationCode, $Login_User);

            if ($Get_Sub_Department == 0) {

                $Response = [
                    'Status' => 'Error',
                    'Message' => 'Sub Department Details Not Found.'
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

    public function Active()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');
            $Employee_Status = $this->input->post('Employee_Status');

            $this->data['Active_Employee_List'] = $Employee_List = $this->Grade_Model->Active_Employee_List($Ccode, $Lcode, $Department, $Employee_Status);

            // echo '<pre>';
            // print_r($Employee_List);
            // exit;

            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function InActive()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');
            $Employee_Status = $this->input->post('Employee_Status');
            $FromDate = $this->input->post('FromDate');
            $ToDate = $this->input->post('ToDate');

            $this->data['InActiveEmployee_List'] = $Employee_List = $this->Grade_Model->InActive_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $FromDate, $ToDate);

            // echo"<pre>";
            // print_r($Employee_List);
            // exit;

            echo json_encode($this->data);
        } else {

            redirect(base_url());
        }
    }

    public function Trainee()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');
            $Employee_Status = $this->input->post('Employee_Status');
            $TaineeMonth = $this->input->post('TaineeMonth');

            // echo"<pre>";
            // print_r($TaineeMonth);
            // exit;

            $this->data['Trainee_Employee_List'] = $Trainee_Employee_List = $this->Grade_Model->Trainee_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $TaineeMonth);

            // echo"<pre>";
            // print_r($Trainee_Employee_List);
            // exit;

            echo json_encode($this->data);
        } else {

            redirect(base_url());
        }
    }


    public function OnRoll()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];
            //
            $Department = $this->input->post('Department');
            $Employee_Status = $this->input->post('Employee_Status');
            $FromDate = $this->input->post('FromDate');
            $ToDate = $this->input->post('ToDate');

            // Fetch the employees based on the criteria
            $this->data['OnRoll_Employee_List'] = $OnRoll_Employee_List = $this->Grade_Model->OnRoll_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $FromDate, $ToDate);


            // echo"<pre>";
            // print_r($OnRoll_Employee_List);
            // exit;

            if (empty($OnRoll_Employee_List)) {
                // Return a message if no employees found
                $this->data['message'] = "No employees found matching the criteria.";
            }

            // Return the data to the view
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }


    // ----------------------------------------------- Attendance Sheet List -----------------------------------------------//


    public function Attendance_Sheet()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $this->data['Favicon'] = 'Precot | Employee Attendace Grade';

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            if ($_POST) {

                $Department =  $this->input->post('Department');
                $this->data['Attendance_List'] =  $Attendance_List = $this->Grade_Model->Attendance_List($Ccode, $Lcode, $Department);
                echo json_encode($this->data);
                exit;
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Grade_Master/Attenadance_Grate', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url());
        }
    }

    //   ---------------------------------------------------------------- Employee Work Rating  --------------------------------------------------------------------//


    public function Rating()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->load->view('Frontend/Header');
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Grade_Master/WorkRating', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url());
        }
    }




    // InActive Employee List

    public function Last_thirdy()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['Last_thirdy'] = $Last_thirdy = $this->Grade_Model->Last_thirdy($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function Last_Sixty()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['Last_Sixty'] = $Last_Sixty = $this->Grade_Model->Last_Sixty($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }


    public function Last_Ninety()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['Last_Ninety'] = $Last_Ninety = $this->Grade_Model->Last_Ninety($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }


    public function Last_One_Twenty()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['Last_One_Twenty'] = $Last_One_Twenty = $this->Grade_Model->Last_One_Twenty($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }



    // OnRoll Employee List


    public function On_Last_thirdy()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['On_Last_thirdy'] = $On_Last_thirdy = $this->Grade_Model->On_Last_thirdy($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function On_Last_Sixty()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['On_Last_Sixty'] = $On_Last_Sixty = $this->Grade_Model->On_Last_Sixty($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function On_Last_Ninety()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['On_Last_Ninety'] = $On_Last_Ninety = $this->Grade_Model->On_Last_Ninety($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function On_One_Twenty()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );
            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $Department = $this->input->post('Department');

            $this->data['On_One_Twenty'] = $On_One_Twenty = $this->Grade_Model->On_One_Twenty($Ccode, $Lcode, $Department);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function Last_6_Month_Trainee_Incomplete()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Last_6_Month_Trainee_Incomplete'] = $Last_6_Month_Trainee_Incomplete = $this->Grade_Model->Last_6_Month_Trainee_Incomplete($Ccode, $Lcode);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function Last_12_Month_Trainee_Incomplete()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Last_12_Month_Trainee_Incomplete'] = $Last_12_Month_Trainee_Incomplete = $this->Grade_Model->Last_12_Month_Trainee_Incomplete($Ccode, $Lcode);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function Above_6_Month_Trainee_Incomplete()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Above_6_Month_Trainee_Incomplete'] = $Above_6_Month_Trainee_Incomplete = $this->Grade_Model->Above_6_Month_Trainee_Incomplete($Ccode, $Lcode);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }


    public function Above_12_Month_Trainee_Incomplete()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Above_12_Month_Trainee_Incomplete'] = $Above_12_Month_Trainee_Incomplete = $this->Grade_Model->Above_12_Month_Trainee_Incomplete($Ccode, $Lcode);
            echo json_encode($this->data);
        } else {
            redirect(base_url());
        }
    }

    public function Chart_Fro_Attendance_Grade()
    {
        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Attendance_List'] =  $Attendance_List = $this->Grade_Model->Chart_Fro_Attendance_Grade($Ccode, $Lcode);
            echo json_encode($this->data);
            exit;
        }
    }

    public function Get_Employee_Count()
    {

        $username = $this->session->userdata('sess_array');
        if (!empty($username) && isset($username['IsOnLogin']) && $username['IsOnLogin'] === TRUE) {


            $this->data = array(
                'Ccode' => $username['Ccode'],
                'Lcode' => $username['Lcode'],
                'UserName' => $username['UserName'],
            );

            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];

            $this->data['Get_Employee_Count'] =  $Get_Employee_Count = $this->Employee_Model->Get_Employee_Count($Ccode, $Lcode);
            echo json_encode($this->data);
            exit;
        }
    }
}
