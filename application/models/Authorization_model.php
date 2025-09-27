<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Authorization_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    public function Verify($username, $password)
    {
        $sql = "
        SELECT A.Ccode, A.Lcode, A.Designation, B.Type, B.Name, A.UserType, A.Password
        FROM UserDetails A
        INNER JOIN UserDetails_Det B ON B.UserID = A.UserID
        WHERE A.UserID = '$username'
        ---AND A.Designation = 'HR'
        AND A.Ccode = 'PRECOT'
        AND A.isActive = 'Yes'
        ---ND A.UserType = 'HR User'";




        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            $User = $query->row();
            $storedEncodedPassword = $User->Password;

            $decodedPassword = base64_decode($storedEncodedPassword);

            if ($decodedPassword == $password) {

                return $User;
            } else {

                return  -1;
            }
        } else {

            return 0;
        }
    }
}
