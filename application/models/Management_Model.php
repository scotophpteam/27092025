<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Management_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Employee_List($CompanyCode, $LocationCode, $Login_User)
    {

        $Sql = "SELECT EMP.EmpNo,EMP.FirstName FROM Employee_Mst AS EMP INNER JOIN UserDetails_Det AS LOG ON EMP.CompCode = LOG.Ccode AND EMP.LocCode = LOG.Lcode AND EMP.DeptName = LOG.Name WHERE EMP.LocCode = '$LocationCode' AND LOG.ccode = '$CompanyCode' AND LOG.UserID = '$Login_User' AND EMP.IsActive = 'Yes' AND CatNAme != 'STAFF'";
        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {

            return $Query->result();

        } else {

            return 0;
        }
    }


    public function Leave_Apply($CompanyCode, $LocationCode, $Login_User, $Applied_Date, $Employee_ID, $From_Date, $To_Date, $Remarks)
    {
        $Sql = "SELECT * FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND EmpNo = '$Employee_ID' AND IsActive = 'Yes'";
        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {

            $Sql_Check = "SELECT * FROM Web_Employee_Leave_Apply_Mst WHERE EmpNo = '$Employee_ID' AND Applied_Date = '$Applied_Date'";
            $Query_Check = $this->db->query($Sql_Check);

            if ($Query_Check->num_rows() > 0) {

                return 2;

            } else {

                $Employee_Data = $Query->row();
                $Applied_Leave_Days = (strtotime($To_Date) - strtotime($From_Date)) / (60 * 60 * 24) + 1;

                $Insert_Data = [
                    'Ccode' => $CompanyCode,
                    'Lcode' => $LocationCode,
                    'EmpNo' => $Employee_Data->EmpNo,
                    'Existing_Code' => $Employee_Data->EmpNo,
                    'FirstName' => $Employee_Data->FirstName,
                    'Department' => $Employee_Data->DeptGrp,
                    'Sub_Department' => $Employee_Data->DeptName,
                    'Wages' => $Employee_Data->Wages,
                    'Sub_Division' => $Employee_Data->SubSection_Name,
                    'Position' => $Employee_Data->WorkArea,
                    'Position_ID' => $Employee_Data->JObCardNo,
                    'Applied_Date' => $Applied_Date,
                    'From_Date' => $From_Date,
                    'To_Date' => $To_Date,
                    'Applied_Leave_Days' => $Applied_Leave_Days,
                    'Remarks' => $Remarks,
                    'Applied_Status' => 'Pending',
                    'Approved_Status' => 'Pending',
                    'CreatedBy' => $Login_User,
                    'CreatedTime' => date('Y-m-d H:i:s'),
                    'UpdatedBy' => '',
                    'UpdatedTime' => ''
                ];

                $this->db->insert('Web_Employee_Leave_Apply_Mst', $Insert_Data);

                if($this->db->affected_rows() > 0 ){

                    return 1;

                }
            }
        }
    }

    public function Leave_Apply_List($CompanyCode, $LocationCode, $Login_User, $From_Date, $To_Date)
    {

        if (empty($To_Date)) {
            $To_Date = date('Y-m-d'); // current date in YYYY-MM-DD
        }
        
        $Sql = "SELECT * FROM Web_Employee_Leave_Apply_Mst 
                WHERE Applied_Date BETWEEN '$From_Date' AND '$To_Date'";

        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }
}
