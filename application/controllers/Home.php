<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class Home extends CI_Controller
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
                $this->load->model('Master_Model');

    }

    public function index()
    {

         $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' =>  $Session['Ccode'],
                'Lcode' =>  $Session['Lcode'],
                'UserName' =>  $Session['UserName'],
            );

            $this->data['Favicon'] = 'Precot | Home';

            $this->data['Designation'] = $Session['Designation'];

            $this->data['Location_Code'] = $Location_Code = $this->Master_Model->Location_Code();


            $Ccode = $this->data['Ccode'];
            $Lcode = $this->data['Lcode'];



            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Frontend/Dashboard', $this->data);
            $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url());
        }
    }

}