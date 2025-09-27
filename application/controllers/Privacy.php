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
    

    $this->data['Insert_Menu'] = $Insert_Menu = $this->Privacy_Model->Insert_Menu(
        $CompanyCode,
        $LocationCode,
        $Login_User,
        $Menu_ID,
        $Menu_Name,
       
    );

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


    
    public function Edit_Menu(){
         $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
         if($this->input->post()){

                $Menu_ID = $this->input->post('Menu_ID');
                $Menu_Name = $this->input->post('Menu_Name');
                
                

                $this->data['Edit_Menu'] = $Edit_Menu = $this->Privacy_Model->Edit_Menu($CompanyCode, $LocationCode, $Login_User,$Menu_ID, $Menu_Name);

                echo json_encode($Edit_Menu);

                exit();
            }

        }else {

            redirect(base_url(), 'refresh');
        }

    }

    public function Delete_Menu(){
         $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
            $Menu_ID = $this->input->post('MenuId');
            $this->data['Delete_Menu'] = $Delete_Menu = $this->Privacy_Model->Delete_Menu($LocationCode,$CompanyCode,$Login_User,$Menu_ID);
        }else {

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

                $SubMenu_ID = $this->input->post('SubMenu_ID');
                $Menu_Name = $this->input->post('Menu_Name');
                $Sub_Menu = $this->input->post('Sub_Menu');

                $Menu_Data = $this->Privacy_Model->Get_Menu_ID($LocationCode,$CompanyCode, $Login_User,$Menu_Name);
                $Menu_Id = isset($Menu_Data['Menu_ID']) ? $Menu_Data['Menu_ID'] : null;

                $this->data['Insert_Sub_Menu'] = $Insert_Sub_Menu = $this->Privacy_Model->Insert_Sub_Menu($CompanyCode, $LocationCode, $Login_User,$Menu_Id, $SubMenu_ID, $Menu_Name,$Sub_Menu);
                echo json_encode($Insert_Sub_Menu);

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

       public function Edit_SubMenu(){
         $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
         if($this->input->post()){

                $Menu_ID = $this->input->post('Menu_ID');
                $Menu_Name = $this->input->post('Menu_Name');
                 $SubMenu_ID = $this->input->post('SubMenu_ID');
                  $SubMenu_Name = $this->input->post('SubMenu_Name');

                $this->data['Edit_SubMenu'] = $Edit_SubMenu = $this->Privacy_Model->Edit_SubMenu($CompanyCode, $LocationCode, $Login_User,$Menu_ID, $Menu_Name,$SubMenu_Name,$SubMenu_ID);

                echo json_encode($Edit_SubMenu);

                exit();

            }

        }else {

            redirect(base_url(), 'refresh');
        }

    }

     public function Delete_SubMenu(){
         $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode =   $Session['Lcode'];
            $CompanyCode =   $Session['Ccode'];
            $Login_User =  $Session['UserName'];
            $SubMenu_Id = $this->input->post('SubMenu_Id');
            $this->data['Delete_SubMenu'] = $Delete_SubMenu = $this->Privacy_Model->Delete_SubMenu($LocationCode,$CompanyCode,$Login_User,$SubMenu_Id);
        }else {

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