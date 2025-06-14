<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Errors extends CI_Controller
{

    public function error_404()
    {
        $data['title'] = "404 Page Not Found";
        $this->output->set_status_header(404);
        $this->load->view('errors/custom_404', $data);
    }
}
