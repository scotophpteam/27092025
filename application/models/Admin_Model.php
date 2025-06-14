<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Admin_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }




public function Unit_Details($Date, $LocationCode, $CompanyCode)
{
    $Sql = "SELECT DISTINCT Department, Ccode, Lcode FROM Web_Work_Area_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode'";
    $Query = $this->db->query($Sql);
    $Details = $Query->result();

    $Result = [];

    foreach ($Details as $Detail) {

        $Sub_Department = $Detail->Department;
        $Location_Code = $Detail->Lcode;
        $Company_Code = $Detail->Ccode;

        $Admin_Shift_Sql = "SELECT ShiftDesc FROM Shift_Mst
                            WHERE CompCode = '$Company_Code'
                            AND LocCode = '$Location_Code'
                            AND ShiftDesc != 'GENTRAL'";
        $Admin_Shift_Query = $this->db->query($Admin_Shift_Sql);
        $Admin_Shift_Details = $Admin_Shift_Query->result();

        foreach ($Admin_Shift_Details as $Admin_Shift_Detail) {
            $Shift = $Admin_Shift_Detail->ShiftDesc;

            $Admin_Sql_Work = "SELECT * FROM Web_Employee_Work_Allocation_Mst
                               WHERE Date = '$Date'
                               AND Shift = '$Shift'
                               AND Sub_Department = '$Sub_Department'
                               AND Lcode = '$Location_Code'
                               AND Ccode = '$Company_Code'
                               AND Assign_Status = '1'
                               AND Closing_Status = '0'";
            $Admin_Query_Work = $this->db->query($Admin_Sql_Work);
            $Admin_Details_Row = $Admin_Query_Work->num_rows();

            if ($Admin_Details_Row > 0) {
                $Admin_Sql_Shift_Closing = "SELECT * FROM Web_Employee_Work_Allocation_Mst
                                            WHERE Date = '$Date'
                                            AND Shift = '$Shift'
                                            AND Sub_Department = '$Sub_Department'
                                            AND Lcode = '$Location_Code'
                                            AND Ccode = '$Company_Code'
                                            AND Assign_Status = '1'
                                            AND Closing_Status = '1'";
                $Admin_Query_Shift_Closing = $this->db->query($Admin_Sql_Shift_Closing);
                $Admin_Shift_Closing_Details_Row = $Admin_Query_Shift_Closing->num_rows();

                if ($Admin_Shift_Closing_Details_Row > 0) {
                    $Result[] = [
                        'Ccode' => $Company_Code,
                        'Lcode' => $Location_Code,
                        'Sub_Department' => $Sub_Department,
                        'Shift' => $Shift,
                        'Work_Assign_Status' => 'Allocated',
                        'Shift_Closing_Status' => 'Closed'
                    ];
                } else {
                    $Result[] = [
                        'Ccode' => $Company_Code,
                        'Lcode' => $Location_Code,
                        'Sub_Department' => $Sub_Department,
                        'Shift' => $Shift,
                        'Work_Assign_Status' => 'Allocated',
                        'Shift_Closing_Status' => 'Not Closed'
                    ];
                }
            } else {
                $Result[] = [
                    'Ccode' => $Company_Code,
                    'Lcode' => $Location_Code,
                    'Sub_Department' => $Sub_Department,
                    'Shift' => $Shift,
                    'Work_Assign_Status' => 'Not Allocated',
                    'Shift_Closing_Status' => ''
                ];
            }
        }
    }

    return $Result;
}


}