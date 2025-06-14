<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class IT extends CI_Controller
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
    }

    public function Update_Location()
    {
        if ($this->input->post()) {


            $Location = $this->input->post('LocationCode');
            $Company = $this->input->post('CompanyCode');

            $Session = $this->session->userdata('sess_array');

            if (!empty($Session) && isset($Session['Ccode'], $Session['Lcode'])) {
                if ($Company === $Session['Ccode'] && $Location === $Session['Lcode']) {
                    echo json_encode([
                        'status' => 'no_change',
                        'message' => 'Company and location are already set to the selected values.'
                    ]);
                    return;
                }

                $Session['Ccode'] = $Company;
                $Session['Lcode'] = $Location;
                $this->session->set_userdata('sess_array', $Session);

                $Session = $this->session->userdata('sess_array');

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Location and company updated successfully.',
                   
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Session not found or invalid.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data posted.'
            ]);
        }
    }

}