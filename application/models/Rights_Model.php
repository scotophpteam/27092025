<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rights_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_user_type()
    {

        $sql = "SELECT * from Web_SideBar_Menu ";
        $sql1 = $this->db->query($sql);
        $q = $sql1->result();
        // print_r($q); exit;
        return $q;
    }

    public function get_sub_menus($Menus)
    {

        $sql = "SELECT * from Web_SubMenus";
        $sql1 = $this->db->query($sql);
        $q = $sql1->result();
        return $q;
    }

    public function user_rights_save($data)
    {

        $this->db->insert('Web_User_Rights_Mst', $data);
        if ($this->db->affected_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function get_rights_details($selectedUserType)
    {
         $Session = $this->session->userdata('sess_array');
		if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

        $this->db->select('*');
        $this->db->from('Web_UserPermission_Mst');
        $this->db->where('UserType', $selectedUserType);
        $this->db->where('Ccode', 'PRECOT');
        $query = $this->db->get();
        return $query->result();

        } else {
            redirect(base_url());
        }
    }


    public function save_screen_data($screenName, $checkboxValue, $data1)
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            // Important: 'false' disables escaping so brackets are not quoted
            $this->db->set("[$screenName]", $checkboxValue, false);
            $this->db->where('UserType', $data1);
            $this->db->update('Web_UserPermission_Mst');

            if ($this->db->affected_rows() > 0) {
                return 1; // Success
            } else {
                return 0; // Failure
            }
        } else {
            redirect(base_url());
        }
    }

}