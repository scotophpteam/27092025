<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Privacy extends CI_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->model('Privacy_Model');
     }


    public function index()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | User Privacy';

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Privacy/User_Permission', $this->data);
            $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url());
        }
    }





    public function Add_Menu()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Add New Manu';

            if($this->input->post()){

                $Menu_ID = $this->input->post('Menu_ID');
                $Menu_Name = $this->input->post('Menu_Name');

                $this->data['Insert_Menu'] = $Insert_Menu = $this->Privacy_Model->Insert_Menu($CompanyCode, $LocationCode, $Login_User,$Menu_ID, $Menu_Name);

                echo json_encode($Insert_Menu);

                exit();

            }


            $this->data['Get_New_Menu_ID'] = $Get_New_Menu_ID = $this->Privacy_Model->Get_New_Menu_ID();
            $this->data['Get_Menus'] = $Get_Menus = $this->Privacy_Model->Get_Menus($CompanyCode, $LocationCode, $Login_User);

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Privacy/Add_Menu', $this->data);
            $this->load->view('Frontend/Footer');
        } else {

            redirect(base_url(), 'refresh');
        }
    }


    public function Add_SubMenu()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Add New Sub Manu';

            if ($this->input->post()) {

                $Menu_ID = $this->input->post('Menu_ID');
                $Menu_Name = $this->input->post('Menu_Name');

                $this->data['Insert_Menu'] = $Insert_Menu = $this->Privacy_Model->Insert_Menu($CompanyCode, $LocationCode, $Login_User, $Menu_ID, $Menu_Name);

                echo json_encode($Insert_Menu);

                exit();
            }


            $this->data['Get_New_SubMenu_ID'] = $Get_New_SubMenu_ID = $this->Privacy_Model->Get_New_SubMenu_ID();
            $this->data['Get_SubMenus'] = $Get_SubMenus = $this->Privacy_Model->Get_SubMenu($CompanyCode, $LocationCode, $Login_User);

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Privacy/Add_SubMenu', $this->data);
            $this->load->view('Frontend/Footer');
        } else {

            redirect(base_url(), 'refresh');
        }
    }




    public function Get_Menus(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Get_Menus'] = $Get_Menus = $this->Privacy_Model->Get_Menus($CompanyCode, $LocationCode,$Login_User);

            if($Get_Menus == 0){

               echo  json_encode(0);

            } else {

                echo json_encode($this->data);

            }


        } else {
            redirect(base_url());
        }


    }




    public function Get_SubMenus()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            if($this->input->post()){


                $Menu = $this->input->post('Menu');
                $UserRole = $this->input->post('UserRole');
                $CompanyCode =  'PRECOT';
                $LocationCode =  $this->input->post('Location');

                $this->data['Get_SubMenus'] = $Get_SubMenus = $this->Privacy_Model->Get_SubMenus($CompanyCode, $LocationCode, $Menu, $UserRole,$Login_User);

                if ($Get_SubMenus == 0) {

                   echo  json_encode(0);
                } else {

                  echo   json_encode($this->data);
                }

            }


        } else {
            redirect(base_url());
        }
    }

    public function Update_Permission(){

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {



            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            // if ($this->input->post()) {

                $rawData = file_get_contents("php://input");
                $inputData = json_decode($rawData, true);

                if (!empty($inputData)) {

                    $Menu = $inputData['Menu'];
                    $UserRole = $inputData['UserRole'];
                    $CompanyCode = 'PRECOT';
                    $LocationCode = $inputData['Location'];

                    $this->data['Update_Permission'] = $Update_Permission = $this->Privacy_Model->Update_Permission($CompanyCode,$LocationCode,$Login_User,$inputData);

                    if ($Update_Permission == 0) {

                        echo json_encode(0);

                    } else {

                         $this->data['Get_SubMenus'] = $Get_SubMenus = $this->Privacy_Model->Get_SubMenus($CompanyCode, $LocationCode, $Menu, $UserRole, $Login_User);
                    echo json_encode($this->data);
                }




        } else {
            redirect(base_url());
        }


    }
}





    }