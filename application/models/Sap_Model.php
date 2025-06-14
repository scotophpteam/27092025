<?php

use PhpParser\Node\Expr\Print_;

 if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Sap_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

public function Machine_Work_Details($LocationCode, $CompanyCode, $Login_User, $Date, $Shift) {

    $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
            INNER JOIN UserDetails_Det B
                ON A.Ccode = B.Ccode
                AND A.Lcode = B.Lcode
                AND A.Sub_Department = B.Name
            WHERE B.UserID = '$Login_User'
                AND A.Date = '$Date'
                AND A.Shift = '$Shift'
                AND A.Ccode = '$CompanyCode'
                AND A.Lcode = '$LocationCode'
                AND A.Work_Type = 'Machine'
                AND A.Assign_Status = '1'";

    $query = $this->db->query($sql);

    if ($query->num_rows() > 0) {

        $Employee_Work_Data = $query->result();
        $Report_Data = [];

        foreach ($Employee_Work_Data as $Employee_Work_Datas) {

            $EmpName         = $Employee_Work_Datas->FirstName;
            $Sub_Section     = $Employee_Work_Datas->Sub_Section;
            $Wages           = $Employee_Work_Datas->Wages;
            $EmpNo           = $Employee_Work_Datas->EmpNo;
            $Department      = $Employee_Work_Datas->Department;
            $Sub_Department  = $Employee_Work_Datas->Sub_Department;
            $WorkArea        = $Employee_Work_Datas->WorkArea;
            $MachineMapName  = $Employee_Work_Datas->Machine_Id;

            $sqlMachine = "SELECT * FROM Web_Machine_Mapping_Mst
                           WHERE Lcode = '$LocationCode'
                             AND Ccode = '$CompanyCode'
                             AND Department = '$Sub_Department'
                             AND WorkArea = '$WorkArea'
                             AND Machine_Mapping = '$MachineMapName'";

            $QueryMachine = $this->db->query($sqlMachine);
            $MachineResults = $QueryMachine->result();

            if (count($MachineResults) == 0) {
                $Report_Data[] = [
                    'Ccode'             => $CompanyCode,
                    'Lcode'             => $LocationCode,
                    'Date'              => $Date,
                    'Shift'             => $Shift,
                    'Department'        => $Department,
                    'Sub_Department'    => $Sub_Department,
                    'WorkArea'         => $WorkArea,
                    'Employee_Division' => $Sub_Section,
                    'Wages'             => $Wages,
                    'Employee_Id'       => $EmpNo,
                    'Employee_Name'     => $EmpName,
                    'Machine_Id'        => '-',
                    'Frame'             => '-'
                ];
            } else {
                foreach ($MachineResults as $machine) {
                    $Report_Data[] = [
                        'Ccode'             => $CompanyCode,
                        'Lcode'             => $LocationCode,
                        'Date'              => $Date,
                        'Shift'             => $Shift,
                        'Department'        => $Department,
                        'Sub_Department'    => $Sub_Department,
                        'WorkArea'         => $WorkArea,
                        'Employee_Division' => $Sub_Section,
                        'Wages'             => $Wages,
                        'Employee_Id'       => $EmpNo,
                        'Employee_Name'     => $EmpName,
                        'Machine_Id'        => $machine->Machine_Id,
                        'Frame'             => $machine->Frame
                    ];
                }
            }
        }

        return $Report_Data;

    } else {

        $Message = [
            'Status' => 'Error',
            'Message' => 'Employee Machine Details Not Found..!'
        ];

        return $Message;
    }
}



}