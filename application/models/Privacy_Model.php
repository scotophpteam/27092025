<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Privacy_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    public function Get_New_Menu_ID()
    {
        $this->db->select('Menu_ID');
        $this->db->from('Web_Menu_Mst');
        $this->db->like('Menu_ID', 'MH', 'after');
        $this->db->order_by('Menu_ID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $last_id = $query->row()->Menu_ID;

            $num = (int)substr($last_id, 2); // Get numeric part after 'MH'
            $new_id = 'MH' . str_pad($num + 1, 2, '0', STR_PAD_LEFT);

            return $new_id;
        } else {
            return 'MH001';
        }
    }


    public function Get_New_SubMenu_ID()
    {
        $this->db->select('Sub_Menu_ID');
        $this->db->from('Web_SubMenu_Mst');
        $this->db->like('Sub_Menu_ID', 'SM', 'after');
        $this->db->order_by('Sub_Menu_ID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $last_id = $query->row()->Sub_Menu_ID;

            $num = (int)substr($last_id, 2); // Get numeric part after 'MH'
            $new_id = 'SM' . str_pad($num + 1, 2, '0', STR_PAD_LEFT);

            return $new_id;
        } else {
            return 'SM001';
        }
    }



   public function Insert_Menu($CompanyCode, $LocationCode, $Login_User, $Menu_ID, $Menu_Name)
{
    $Insert_Menu = [
        'Ccode' => '',
        'Lcode' => '',
        'Menu_ID' => $Menu_ID,
        'Menu' => $Menu_Name,
        'CreatedBy' => $Login_User,
        'CreatedTime' => date('Y-m-d H:i:s'),
        'UpdatedBy' => '',
        'UpdatedTime' => '',
    ];

    $this->db->insert('Web_Menu_Mst', $Insert_Menu);

    if ($this->db->affected_rows() > 0) {
        return 1;
    } else {
        return 0;
    }
}

public function Edit_Menu($CompanyCode, $LocationCode, $Login_User, $Menu_ID, $Menu_Name) {

    $Updated_Date = date('Y-m-d H:i:s');

    $Sql = "UPDATE Web_Menu_Mst  
            SET  
                Menu = '$Menu_Name', 
               
                UpdatedBy = '$Login_User',  
                UpdatedTime = '$Updated_Date'   
            WHERE   Menu_ID = '$Menu_ID'";
    $result = $this->db->query($Sql);

    if ($result) {
        return array('status' => 'success', 'message' => 'Menu updated successfully.');
    } else {
        return array('status' => 'error', 'message' => 'Menu update failed.');
    }
}

public function Delete_Menu($LocationCode,$CompanyCode,$Login_User,$Menu_ID){
    $Sql = "DELETE FROM Web_Menu_Mst WHERE Menu_ID = '$Menu_ID'";

    $result = $this->db->query($Sql);
    return $result;

}
  public function Insert_Sub_Menu($CompanyCode, $LocationCode, $Login_User,$Menu_Id,$SubMenu_ID, $Menu_Name,$Sub_Menu)
    {
        $Insert_SubMenu = [
            'Ccode' => '',
            'Lcode' => '',
            'Menu_ID' =>$Menu_Id,
            'Sub_Menu_ID' => $SubMenu_ID,
            'Menu' => $Menu_Name,
            'Sub_Menu' => $Sub_Menu,
            'CreatedBy' => $Login_User,
            'CreatedTime' => date('Y-m-d H:i:s'),
            'UpdatedBy' => '',
            'UpdatedTime' => '',
        ];
       
        $this->db->insert('Web_SubMenu_Mst', $Insert_SubMenu);

        if ($this->db->affected_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function Get_Menu_ID($CompanyCode, $LocationCode, $Login_User, $Menu_Name)
{
    $Sql = "SELECT Menu_ID FROM Web_Menu_Mst where Menu = '$Menu_Name'";
    $Query = $this->db->query($Sql);

    if ($Query->num_rows() > 0) {
            return $Query->row_array();
        } else {
            return 0;
        }
}

public function Edit_SubMenu($CompanyCode, $LocationCode, $Login_User, $Menu_ID, $Menu_Name, $SubMenu_Name, $SubMenu_ID) {
    $Updated_Date = date('Y-m-d H:i:s');

    $Sql = "UPDATE Web_SubMenu_Mst  
            SET  
                Menu = '$Menu_Name',
                Sub_Menu = '$SubMenu_Name',  
                UpdatedBy = '$Login_User',  
                UpdatedTime = '$Updated_Date'   
            WHERE Menu_ID = '$Menu_ID' AND Sub_Menu_ID = '$SubMenu_ID'";

    $result = $this->db->query($Sql);

    if ($result) {
        return array('status' => 'success', 'message' => 'Submenu updated successfully.');
    } else {
        return array('status' => 'error', 'message' => 'Submenu update failed.');
    }
}


public function Delete_SubMenu($LocationCode,$CompanyCode,$Login_User,$SubMenu_Id){
    $Sql = "DELETE FROM Web_SubMenu_Mst WHERE Sub_Menu_ID = '$SubMenu_Id'";
    
    $result = $this->db->query($Sql);
    return $result;
}

    public function Get_Menus($CompanyCode, $LocationCode, $Login_User)
    {

        $Sql = "SELECT Menu,Menu_ID FROM Web_Menu_Mst ";
        $Query = $this->db->query($Sql);
        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }

    public function Get_SubMenu($CompanyCode, $LocationCode, $Login_User)
    {

        $Sql = "SELECT * FROM Web_SubMenu_Mst ";
        $Query = $this->db->query($Sql);
        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }


    public function Get_SubMenus($CompanyCode, $LocationCode, $Menu, $UserRole, $Login_User)
    {
        $sql = "SELECT Sub_Menu, Sub_Menu_ID FROM Web_SubMenu_Mst WHERE Menu = '$Menu'";
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $subMenus = $query->result();

            $sqlPrivacy = "SELECT Sub_Menu FROM Web_Privacy_Mst
                           WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
                           AND Menu = '$Menu' AND UserRole = '$UserRole' AND Status = 'Yes'";
            $queryPrivacy = $this->db->query($sqlPrivacy);

            $existingSubMenus = [];
            if ($queryPrivacy->num_rows() > 0) {
                foreach ($queryPrivacy->result() as $row) {
                    $existingSubMenus[] = $row->Sub_Menu;
                }
            }

            $insertData = [];

            foreach ($subMenus as $row) {
                if (!in_array($row->Sub_Menu, $existingSubMenus)) {
                    $insertData[] = [
                        'Ccode' => $CompanyCode,
                        'Lcode' => $LocationCode,
                        'UserRole' => $UserRole,
                        'Menu' => $Menu,
                        'Menu_ID' => '',
                        'Sub_Menu' => $row->Sub_Menu,
                        'SubMenu_ID' => $row->Sub_Menu_ID,
                        'Screen' => '',
                        'Edit' => '',
                        'Remove' => '',
                        'Status' => 'Yes',
                        'CreatedBy' => $Login_User,
                        'CreatedTime' => date('Y-m-d H:i:s'),
                        'UpdatedBy' => '',
                        'UpdatedTime' => ''
                    ];
                }
            }

            if (!empty($insertData)) {
                $this->db->insert_batch('Web_Privacy_Mst', $insertData);
            }

            // Now fetch and return all the privacy settings for the given parameters
            $sqlInserted = "SELECT * FROM Web_Privacy_Mst
                            WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
                            AND Menu = '$Menu' AND UserRole = '$UserRole' AND Status = 'Yes'";
            $queryInserted = $this->db->query($sqlInserted);

            return $queryInserted->result();
        } else {
            return 0;
        }
    }


   public function Update_Permission($CompanyCode, $LocationCode, $Login_User, $inputData)
{
    $Menu       = $inputData['Menu'];
    $UserRole   = $inputData['UserRole'];
    $CompanyCode = 'PRECOT';   // hardcoded for now
    $LocationCode = $inputData['Location'];
    $Rights     = $inputData['Rights'];

    foreach ($Rights as $right) {
        $SubMenu = $right['Sub_Menu'];
        $Screen  = $right['Screen'];
        $Edit    = $right['Edit'];
        $Delete  = $right['Delete'];
        $Url     = isset($right['Url']) ? $right['Url'] : '';   // ✅ include Url

        $Sql = "SELECT * FROM Web_Privacy_Mst
                WHERE Ccode = '$CompanyCode'
                  AND Lcode = '$LocationCode'
                  AND Menu = '$Menu'
                  AND Sub_Menu = '$SubMenu'
                  AND UserRole = '$UserRole'
                  AND Status = 'Yes'";

        $Query = $this->db->query($Sql);
        $Current_Date = date('Y-m-d H:i:s');

        if ($Query->num_rows() > 0) {
            // ✅ Update including Url
            $Update = "UPDATE Web_Privacy_Mst SET
                        Screen = '$Screen',
                        Edit = '$Edit',
                        Remove = '$Delete',
                        Url = '$Url',
                        UpdatedBy = '$Login_User',
                        UpdatedTime = '$Current_Date'
                       WHERE Ccode = '$CompanyCode'
                         AND Lcode = '$LocationCode'
                         AND Menu = '$Menu'
                         AND Sub_Menu = '$SubMenu'
                         AND UserRole = '$UserRole'
                         AND Status = 'Yes'";
            $this->db->query($Update);
        } else {
            // ✅ Insert including Url
            $Insert = "INSERT INTO Web_Privacy_Mst (
                            Ccode, Lcode, Menu, Sub_Menu, Url, UserRole, Screen, Edit, Remove, CreatedBy, CreatedTime, Status
                       ) VALUES (
                            '$CompanyCode', '$LocationCode', '$Menu', '$SubMenu', '$Url', '$UserRole',
                            '$Screen', '$Edit', '$Delete', '$Login_User', '$Current_Date', 'Yes'
                       )";
            $this->db->query($Insert);
        }
    }

    return 1;
}




    public function Submenu($CompanyCode,$LocationCode,$Login_User,$Menu){

         $Sql = "SELECT Sub_Menu FROM Web_SubMenu_Mst Where Menu ='$Menu'";
        $Query = $this->db->query($Sql);
        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
        
    }
   
}
