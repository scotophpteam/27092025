<?php

use PhpParser\Node\Expr\Print_;

if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Late_Extra_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

//   public function Late_And_Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
// {

//     if($LocationCode == 'PRECOT - A'){

//             $Sql_Vaild = "SELECT * 
//         FROM UserDetails_Det AS Login 
//         INNER JOIN Web_Employee_Work_Allocation_Mst AS Work 
//             ON Login.Lcode = Work.Lcode 
//             AND Login.Ccode = Work.Ccode 
//             AND Login.Name = Work.Sub_Department
//         WHERE Work.Lcode = '$LocationCode'
//             AND Login.Ccode = '$CompanyCode' 
//             AND Work.Date = '$Date'
//             AND Work.Shift = '$Shift'
//             AND Login.UserID = '$Login_User'";

//     $Query_Vaild = $this->db->query($Sql_Vaild);

//     if ($Query_Vaild->num_rows() > 0) {
//         $sql = "SELECT * 
//             FROM Shift_Mst 
//             WHERE CompCode = '$CompanyCode' 
//                 AND LocCode = '$LocationCode' 
//                 AND ShiftDesc = '$Shift'";

//         $shift_Data = $this->db->query($sql)->row();

//         if ($shift_Data) {
//             if ($Shift == 'SHIFT2') {
//                 $Shift_Pounch_Start = '17:16';
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             } else {
//                 $Shift_Pounch_Start = $shift_Data->EndIN;
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             }

//             $From_Shift_Date_Convert = $Date;
//             $To_Shift_Date_Convert = $Date;

//             if ($shift_Data->StartIN_Days == 1) {
//                 $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             if ($shift_Data->EndOUT_Days == 1) {
//                 $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             $current_time = date('Y-m-d H:i:s');

//             $sql2 = "SELECT * 
//                 FROM UserDetails_Det Log
//                 INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
//                 INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
//                 WHERE Log.UserID = '$Login_User'
//                     AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
//                     AND Emp.CatName != 'STAFF'
//                     AND Time.CompCode = '$CompanyCode'
//                     AND Time.LocCode = '$LocationCode'
//                     AND Emp.WorkArea IS NOT NULL
//                     AND Emp.IsActive = 'Yes'
//                     AND NOT EXISTS (
//                         SELECT 1 
//                         FROM Web_Employee_Work_Allocation_Mst W 
//                         WHERE W.EmpNo = Emp.EmpNo 
//                             AND W.Lcode = '$LocationCode'
//                             AND W.Work_Status = '1' 
//                             AND W.Date = '$Date'
//                             AND W.Wages != 'STAFF'
//                             AND W.Ccode = '$CompanyCode'
//                             AND W.Shift = '$Shift')";

//             $log_Data = $this->db->query($sql2)->result();

//             foreach ($log_Data as $Employee_Data) {
//                 $EmpNo = $Employee_Data->EmpNo;

//                 $check1 = "SELECT 1 
//                     FROM Web_Late_Extra_Mst 
//                     WHERE Ccode = '$CompanyCode' 
//                         AND Lcode = '$LocationCode' 
//                         AND Shift = '$Shift' 
//                         AND Date = '$Date' 
//                         AND EmpNo = '$EmpNo'";

//                 $check2 = "SELECT 1 
//                     FROM Web_Employee_Work_Allocation_Mst 
//                     WHERE Date = '$Date' 
//                         AND Shift = '$Shift' 
//                         AND EmpNo = '$EmpNo' 
//                         AND Lcode = '$LocationCode'";

//                 $exists1 = $this->db->query($check1)->num_rows();
//                 $exists2 = $this->db->query($check2)->num_rows();

//                 if ($exists1 == 0 && $exists2 == 0) {
//                     $Allocation = array(
//                         'Ccode' => $CompanyCode,
//                         'Lcode' => $LocationCode,
//                         'Wages' => $Employee_Data->Wages,
//                         'FirstName' => $Employee_Data->FirstName,
//                         'EmpNo' => $EmpNo,
//                         'Shift' => $Shift,
//                         'Date' => $Date,
//                         'Job_Card_No' => '',
//                         'Department' => $Employee_Data->DeptGrp,
//                         'Sub_Department' => $Employee_Data->DeptName,
//                         'Sub_Section' => $Employee_Data->SubSection_Name,
//                         'WorkArea' => $Employee_Data->WorkArea,
//                         
//                         'OT_Confirmation' => '-',
//                         'Machine_Id' => '',
//                         'Working_Type' => 'LATE',
//                         'Machine_Name' => '-',
//                         'FrameType' => '-',
//                         'Frame' => '',
//                         'Type' => '',
//                         'Work_Type' => '',
//                         'Status_Updated' => '',
//                         'Work_Start' => '-',
//                         'Work_End' => '-',
//                         'Work_Duration' => '-',
//                         'Machine_EB_No' => '-',
//                         'Work_Status' => '1',
//                         'Assign_Status' => '0',
//                         'Closing_Status' => '0',
//                         'IsWork' => '0',
//                         'Edit_Reason' => '-',
//                         'Description' => '',
//                         'Created_By' => $Login_User,
//                         'Created_Time' => $current_time,
//                         'Updated_By' => '-',
//                         'Updated_Time' => '-',
//                     );

//                     $this->db->insert('Web_Late_Extra_Mst', $Allocation);
//                 }
//             }

//             $Sql4 = "SELECT * 
//                 FROM Web_Late_Extra_Mst Late 
//                 INNER JOIN UserDetails_Det Login ON Login.Ccode = Late.Ccode 
//                     AND Login.Lcode = Late.Lcode 
//                     AND Login.Name = Late.Sub_Department 
//                 WHERE Late.Date = '$Date' 
//                     AND Late.Shift = '$Shift' 
//                     AND Late.Ccode = '$CompanyCode' 
//                     AND Late.Lcode = '$LocationCode' 
//                     AND Late.Assign_Status = '0' 
//                     AND Late.Work_Status = '1' 
//                     AND Login.UserID = '$Login_User'";

//             $query4 = $this->db->query($Sql4);
//             return $query4->result();
//         }

//         return [];
//     } else {
//         return [
//             'Status' => 'Error',
//             'Message' => 'Please complete the shift allocation first, then assign the late employees accordingly.'
//         ];
//     }

//     } else if($LocationCode == 'PRECOT - C'){


//             $Sql_Vaild = "SELECT * 
//         FROM UserDetails_Det AS Login 
//         INNER JOIN Web_Employee_Work_Allocation_Mst AS Work 
//             ON Login.Lcode = Work.Lcode 
//             AND Login.Ccode = Work.Ccode 
//             AND Login.Name = Work.Sub_Department
//         WHERE Work.Lcode = '$LocationCode'
//             AND Login.Ccode = '$CompanyCode' 
//             AND Work.Date = '$Date'
//             AND Work.Shift = '$Shift'
//             AND Login.UserID = '$Login_User'";

//     $Query_Vaild = $this->db->query($Sql_Vaild);

//     if ($Query_Vaild->num_rows() > 0) {
//         $sql = "SELECT * 
//             FROM Shift_Mst 
//             WHERE CompCode = '$CompanyCode' 
//                 AND LocCode = '$LocationCode' 
//                 AND ShiftDesc = '$Shift'";

//         $shift_Data = $this->db->query($sql)->row();

//         if ($shift_Data) {
//             if ($Shift == 'SHIFT2') {
//                 $Shift_Pounch_Start = '17:16';
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             } else {
//                 $Shift_Pounch_Start = $shift_Data->EndIN;
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             }

//             $From_Shift_Date_Convert = $Date;
//             $To_Shift_Date_Convert = $Date;

//             if ($shift_Data->StartIN_Days == 1) {
//                 $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             if ($shift_Data->EndOUT_Days == 1) {
//                 $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             $current_time = date('Y-m-d H:i:s');

//             $sql2 = "SELECT * 
//                 FROM UserDetails_Det Log
//                 INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
//                 INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
//                 WHERE Log.UserID = '$Login_User'
//                     AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
//                     AND Emp.CatName != 'STAFF'
//                     AND Time.CompCode = '$CompanyCode'
//                     AND Time.LocCode = '$LocationCode'
//                     AND Emp.WorkArea IS NOT NULL
//                     AND Emp.IsActive = 'Yes'
//                     AND NOT EXISTS (
//                         SELECT 1 
//                         FROM Web_Employee_Work_Allocation_Mst W 
//                         WHERE W.EmpNo = Emp.EmpNo 
//                             AND W.Lcode = '$LocationCode'
//                             AND W.Work_Status = '1' 
//                             AND W.Date = '$Date'
//                             AND W.Wages != 'STAFF'
//                             AND W.Ccode = '$CompanyCode'
//                             AND W.Shift = '$Shift')";

//             $log_Data = $this->db->query($sql2)->result();

//             foreach ($log_Data as $Employee_Data) {
//                 $EmpNo = $Employee_Data->EmpNo;

//                 $check1 = "SELECT 1 
//                     FROM Web_Late_Extra_Mst 
//                     WHERE Ccode = '$CompanyCode' 
//                         AND Lcode = '$LocationCode' 
//                         AND Shift = '$Shift' 
//                         AND Date = '$Date' 
//                         AND EmpNo = '$EmpNo'";

//                 $check2 = "SELECT 1 
//                     FROM Web_Employee_Work_Allocation_Mst 
//                     WHERE Date = '$Date' 
//                         AND Shift = '$Shift' 
//                         AND EmpNo = '$EmpNo' 
//                         AND Lcode = '$LocationCode'";

//                 $exists1 = $this->db->query($check1)->num_rows();
//                 $exists2 = $this->db->query($check2)->num_rows();

//                 if ($exists1 == 0 && $exists2 == 0) {
//                     $Allocation = array(
//                         'Ccode' => $CompanyCode,
//                         'Lcode' => $LocationCode,
//                         'Wages' => $Employee_Data->Wages,
//                         'FirstName' => $Employee_Data->FirstName,
//                         'EmpNo' => $EmpNo,
//                         'Shift' => $Shift,
//                         'Date' => $Date,
//                         'Job_Card_No' => '',
//                         'Department' => $Employee_Data->DeptGrp,
//                         'Sub_Department' => $Employee_Data->DeptName,
//                         'Sub_Section' => $Employee_Data->SubSection_Name,
//                         'WorkArea' => $Employee_Data->WorkArea,
//                         
//                         'OT_Confirmation' => '-',
//                         'Machine_Id' => '',
//                         'Working_Type' => 'LATE',
//                         'Machine_Name' => '-',
//                         'FrameType' => '-',
//                         'Frame' => '',
//                         'Type' => '',
//                         'Work_Type' => '',
//                         'Status_Updated' => '',
//                         'Work_Start' => '-',
//                         'Work_End' => '-',
//                         'Work_Duration' => '-',
//                         'Machine_EB_No' => '-',
//                         'Work_Status' => '1',
//                         'Assign_Status' => '0',
//                         'Closing_Status' => '0',
//                         'IsWork' => '0',
//                         'Edit_Reason' => '-',
//                         'Description' => '',
//                         'Created_By' => $Login_User,
//                         'Created_Time' => $current_time,
//                         'Updated_By' => '-',
//                         'Updated_Time' => '-',
//                     );

//                     $this->db->insert('Web_Late_Extra_Mst', $Allocation);
//                 }
//             }

//             $Sql4 = "SELECT * 
//                 FROM Web_Late_Extra_Mst Late 
//                 INNER JOIN UserDetails_Det Login ON Login.Ccode = Late.Ccode 
//                     AND Login.Lcode = Late.Lcode 
//                     AND Login.Name = Late.Sub_Department 
//                 WHERE Late.Date = '$Date' 
//                     AND Late.Shift = '$Shift' 
//                     AND Late.Ccode = '$CompanyCode' 
//                     AND Late.Lcode = '$LocationCode' 
//                     AND Late.Assign_Status = '0' 
//                     AND Late.Work_Status = '1' 
//                     AND Login.UserID = '$Login_User'";

//             $query4 = $this->db->query($Sql4);
//             return $query4->result();
//         }

//         return [];
//     } else {
//         return [
//             'Status' => 'Error',
//             'Message' => 'Please complete the shift allocation first, then assign the late employees accordingly.'
//         ];
//     }


//     } else if($LocationCode == 'PRECOT - D'){

//             $Sql_Vaild = "SELECT * 
//         FROM UserDetails_Det AS Login 
//         INNER JOIN Web_Employee_Work_Allocation_Mst AS Work 
//             ON Login.Lcode = Work.Lcode 
//             AND Login.Ccode = Work.Ccode 
//             AND Login.Name = Work.Sub_Department
//         WHERE Work.Lcode = '$LocationCode'
//             AND Login.Ccode = '$CompanyCode' 
//             AND Work.Date = '$Date'
//             AND Work.Shift = '$Shift'
//             AND Login.UserID = '$Login_User'";

//     $Query_Vaild = $this->db->query($Sql_Vaild);

//     if ($Query_Vaild->num_rows() > 0) {
//         $sql = "SELECT * 
//             FROM Shift_Mst 
//             WHERE CompCode = '$CompanyCode' 
//                 AND LocCode = '$LocationCode' 
//                 AND ShiftDesc = '$Shift'";

//         $shift_Data = $this->db->query($sql)->row();

//         if ($shift_Data) {
//             if ($Shift == 'SHIFT2') {
//                 $Shift_Pounch_Start = '17:16';
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             } else {
//                 $Shift_Pounch_Start = $shift_Data->EndIN;
//                 $Shift_Pounch_Ends = $shift_Data->EndTime;
//                 $endTime = new DateTime($Shift_Pounch_Ends);
//                 $endTime->modify('-1 hour');
//                 $Shift_Pounch_End = $endTime->format('H:i');
//             }

//             $From_Shift_Date_Convert = $Date;
//             $To_Shift_Date_Convert = $Date;

//             if ($shift_Data->StartIN_Days == 1) {
//                 $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             if ($shift_Data->EndOUT_Days == 1) {
//                 $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
//             }

//             $current_time = date('Y-m-d H:i:s');

//             $sql2 = "SELECT * 
//                 FROM UserDetails_Det Log
//                 INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
//                 INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
//                 WHERE Log.UserID = '$Login_User'
//                     AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
//                     AND Emp.CatName != 'STAFF'
//                     AND Time.CompCode = '$CompanyCode'
//                     AND Time.LocCode = '$LocationCode'
//                     AND Emp.WorkArea IS NOT NULL
//                     AND Emp.IsActive = 'Yes'
//                     AND NOT EXISTS (
//                         SELECT 1 
//                         FROM Web_Employee_Work_Allocation_Mst W 
//                         WHERE W.EmpNo = Emp.EmpNo 
//                             AND W.Lcode = '$LocationCode'
//                             AND W.Work_Status = '1' 
//                             AND W.Date = '$Date'
//                             AND W.Wages != 'STAFF'
//                             AND W.Ccode = '$CompanyCode'
//                             AND W.Shift = '$Shift')";

//             $log_Data = $this->db->query($sql2)->result();

//             foreach ($log_Data as $Employee_Data) {
//                 $EmpNo = $Employee_Data->EmpNo;

//                 $check1 = "SELECT 1 
//                     FROM Web_Late_Extra_Mst 
//                     WHERE Ccode = '$CompanyCode' 
//                         AND Lcode = '$LocationCode' 
//                         AND Shift = '$Shift' 
//                         AND Date = '$Date' 
//                         AND EmpNo = '$EmpNo'";

//                 $check2 = "SELECT 1 
//                     FROM Web_Employee_Work_Allocation_Mst 
//                     WHERE Date = '$Date' 
//                         AND Shift = '$Shift' 
//                         AND EmpNo = '$EmpNo' 
//                         AND Lcode = '$LocationCode'";

//                 $exists1 = $this->db->query($check1)->num_rows();
//                 $exists2 = $this->db->query($check2)->num_rows();

//                 if ($exists1 == 0 && $exists2 == 0) {

//                     $Allocation = array(
//                         'Ccode' => $CompanyCode,
//                         'Lcode' => $LocationCode,
//                         'Wages' => $Employee_Data->Wages,
//                         'FirstName' => $Employee_Data->FirstName,
//                         'EmpNo' => $EmpNo,
//                         'Shift' => $Shift,
//                         'Date' => $Date,
//                         'Job_Card_No' => '',
//                         'Department' => $Employee_Data->DeptGrp,
//                         'Sub_Department' => $Employee_Data->DeptName,
//                         'Sub_Section' => $Employee_Data->SubSection_Name,
//                         'WorkArea' => $Employee_Data->WorkArea,
//                         
//                         'OT_Confirmation' => '-',
//                         'Machine_Id' => '',
//                         'Working_Type' => 'LATE',
//                         'Machine_Name' => '-',
//                         'FrameType' => '-',
//                         'Frame' => '',
//                         'Type' => '',
//                         'Work_Type' => '',
//                         'Status_Updated' => '',
//                         'Work_Start' => '-',
//                         'Work_End' => '-',
//                         'Work_Duration' => '-',
//                         'Machine_EB_No' => '-',
//                         'Work_Status' => '1',
//                         'Assign_Status' => '0',
//                         'Closing_Status' => '0',
//                         'IsWork' => '0',
//                         'Edit_Reason' => '-',
//                         'Description' => '',
//                         'Created_By' => $Login_User,
//                         'Created_Time' => $current_time,
//                         'Updated_By' => '-',
//                         'Updated_Time' => '-',
//                     );

//                     $this->db->insert('Web_Late_Extra_Mst', $Allocation);
//                 }
//             }

//             $Sql4 = "SELECT * 
//                 FROM Web_Late_Extra_Mst Late 
//                 INNER JOIN UserDetails_Det Login ON Login.Ccode = Late.Ccode 
//                     AND Login.Lcode = Late.Lcode 
//                     AND Login.Name = Late.Sub_Department 
//                 WHERE Late.Date = '$Date' 
//                     AND Late.Shift = '$Shift' 
//                     AND Late.Ccode = '$CompanyCode' 
//                     AND Late.Lcode = '$LocationCode' 
//                     AND Late.Assign_Status = '0' 
//                     AND Late.Work_Status = '1' 
//                     AND Login.UserID = '$Login_User'";

//             $query4 = $this->db->query($Sql4);
//             return $query4->result();
//         }

//         return [];
//     } else {
//         return [
//             'Status' => 'Error',
//             'Message' => 'Please complete the shift allocation first, then assign the late employees accordingly.'
//         ];
//     }

//     } 


// }


public function Late_And_Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
{
    if (in_array($LocationCode, ['PRECOT - A', 'PRECOT - C', 'PRECOT - D'])) {

        $Sql_Vaild = "SELECT 1 
            FROM UserDetails_Det AS Login 
            INNER JOIN Web_Employee_Work_Allocation_Mst AS Work 
                ON Login.Lcode = Work.Lcode 
                AND Login.Ccode = Work.Ccode 
                AND Login.Name = Work.Sub_Department
            WHERE Work.Lcode = '$LocationCode'
                AND Login.Ccode = '$CompanyCode' 
                AND Work.Date = '$Date'
                AND Work.Shift = '$Shift'
                AND Login.UserID = '$Login_User'";

        $Query_Vaild = $this->db->query($Sql_Vaild);

        if ($Query_Vaild->num_rows() > 0) {

            $sql = "SELECT * 
                FROM Shift_Mst 
                WHERE CompCode = '$CompanyCode' 
                    AND LocCode = '$LocationCode' 
                    AND ShiftDesc = '$Shift'";

            $shift_Data = $this->db->query($sql)->row();

            if ($shift_Data) {
                $Shift_Pounch_Start = ($Shift == 'SHIFT2') ? '17:16' : $shift_Data->EndIN;
                $Shift_Pounch_Ends  = $shift_Data->EndTime;
                $endTime = new DateTime($Shift_Pounch_Ends);
                $endTime->modify('-1 hour');
                $Shift_Pounch_End = $endTime->format('H:i');

                $From_Shift_Date_Convert = $Date;
                $To_Shift_Date_Convert = $Date;

                if ($shift_Data->StartIN_Days == 1) {
                    $From_Shift_Date_Convert = date('Y-m-d', strtotime("$Date +1 days"));
                }
                if ($shift_Data->EndOUT_Days == 1) {
                    $To_Shift_Date_Convert = date('Y-m-d', strtotime("$Date +1 days"));
                }

                $current_time = date('Y-m-d H:i:s');

                $sql2 = "SELECT Emp.*, Log.UserID 
                    FROM UserDetails_Det Log
                    INNER JOIN Employee_Mst Emp 
                        ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                    INNER JOIN LogTime_IN Time 
                        ON Time.MachineID = Emp.MachineID
                    LEFT JOIN Web_Employee_Work_Allocation_Mst W 
                        ON W.EmpNo = Emp.EmpNo AND W.Date = '$Date' 
                            AND W.Shift = '$Shift' AND W.Lcode = '$LocationCode' AND W.Ccode = '$CompanyCode'
                    LEFT JOIN Web_Late_Extra_Mst LEM 
                        ON LEM.EmpNo = Emp.EmpNo AND LEM.Date = '$Date' 
                            AND LEM.Shift = '$Shift' AND LEM.Lcode = '$LocationCode' AND LEM.Ccode = '$CompanyCode'
                    WHERE Log.UserID = '$Login_User'
                        AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' 
                        AND '$To_Shift_Date_Convert $Shift_Pounch_End'
                        AND Emp.CatName = 'WORKER'
                        AND Time.CompCode = '$CompanyCode'
                        AND Time.LocCode = '$LocationCode'
                        AND Emp.WorkArea IS NOT NULL
                        AND Emp.IsActive = 'Yes'
                        AND W.EmpNo IS NULL
                        AND LEM.EmpNo IS NULL";

                $log_Data = $this->db->query($sql2)->result();

                foreach ($log_Data as $Employee_Data) {
                    $EmpNo = $Employee_Data->EmpNo;

                    $Allocation = array(
                        'Ccode' => $CompanyCode,
                        'Lcode' => $LocationCode,
                        'Wages' => $Employee_Data->Wages,
                        'FirstName' => $Employee_Data->FirstName,
                        'EmpNo' => $EmpNo,
                        'Shift' => $Shift,
                        'Date' => $Date,
                        'Job_Card_No' => '',
                        'Department' => $Employee_Data->DeptGrp,
                        'Sub_Department' => $Employee_Data->DeptName,
                        'Sub_Section' => $Employee_Data->SubSection_Name,
                        'WorkArea' => $Employee_Data->WorkArea,
                        
                        'OT_Confirmation' => '-',
                        'Machine_Id' => '',
                        'Working_Type' => 'LATE',
                        'Machine_Name' => '-',
                        'FrameType' => '-',
                        'Frame' => '',
                        'Type' => '',
                        'Work_Type' => '',
                        'Status_Updated' => '',
                        'Work_Start' => '-',
                        'Work_End' => '-',
                        'Work_Duration' => '-',
                        'Machine_EB_No' => '-',
                        'Work_Status' => '1',
                        'Assign_Status' => '0',
                        'Closing_Status' => '0',
                        'IsWork' => '0',
                        'Edit_Reason' => '-',
                        'Description' => '',
                        'Created_By' => $Login_User,
                        'Created_Time' => $current_time,
                        'Updated_By' => '-',
                        'Updated_Time' => '-',
                    );

                    $this->db->insert('Web_Late_Extra_Mst', $Allocation);
                }

                $Sql4 = "SELECT * 
                    FROM Web_Late_Extra_Mst Late 
                    INNER JOIN UserDetails_Det Login 
                        ON Login.Ccode = Late.Ccode 
                        AND Login.Lcode = Late.Lcode 
                        AND Login.Name = Late.Sub_Department 
                    WHERE Late.Date = '$Date' 
                        AND Late.Shift = '$Shift' 
                        AND Late.Ccode = '$CompanyCode' 
                        AND Late.Lcode = '$LocationCode' 
                        AND Late.Assign_Status = '0' 
                        AND Late.Work_Status = '1' 
                        AND Login.UserID = '$Login_User'";

                return $this->db->query($Sql4)->result();
            }

            return [];
        } else {
            return [
                'Status' => 'Error',
                'Message' => 'Please complete the shift allocation first, then assign the late employees accordingly.'
            ];
        }
    }
}







    public function Assign($input_data, $CompanyCode, $LocationCode,$Login_User)
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {


            if ($LocationCode == 'PRECOT - A') {

                $success = true;
                $allocation_details = [];

                foreach ($input_data['Allocations'] as $row) {

                    $Sub_Department = $row['Department'];
                    $Shift = $row['Shift'];
                    $Date = $row['Date'];
                    $Work_Area = $row['Work_Area'];
                    $Employee_Id = $row['EmployeeId'];
                    $Frames = $row['Frames'];
                    $Machine_Id = $row['Machine_Id'];
                    // $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];
                    $Supervisor_Name = $row['Supervisor'];

                      function getPreviousShift($currentShift, $shiftMaster)
                    {
                        $shiftKeys = array_keys($shiftMaster);
                        if (in_array($currentShift, $shiftKeys)) {
                            $currentIndex = array_search($currentShift, $shiftKeys);
                            $previousIndex = ($currentIndex - 1) < 0 ? count($shiftKeys) - 1 : $currentIndex - 1;
                            return $shiftKeys[$previousIndex];
                        }
                        return null;
                    }



                    $Shift_Master = [

                        'SHIFT1' => 'SHIFT1',
                        'SHIFT2' => 'SHIFT2',
                        'SHIFT3' => 'SHIFT3',

                    ];

                    $Current_Shift = $Shift;

                    $Previous_Shift = getPreviousShift($Current_Shift, $Shift_Master);


                  
                    // Check Previous Allocation 
                   if ($Shift == 'SHIFT1') {
                        $Previous_Date = date('Y-m-d', strtotime($Date . ' -1 days'));
                        $Sql_OT_PreviousDay = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                            INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                            WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                            AND Work.Date = '$Previous_Date' AND Work.EmpNo = '$Employee_Id'
                            AND Work.Shift = 'SHIFT3' AND Work.Work_Status = '1'
                            AND Login.UserID = '$Login_User'";
                        $this->db->query($Sql_OT_PreviousDay);
                    }

                    $Sql_OT = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                        INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                        WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                        AND Work.Date = '$Date' AND Work.EmpNo = '$Employee_Id'
                        AND Work.Shift = '$Previous_Shift' AND Work.Work_Status = '1'
                        AND Login.UserID = '$Login_User'";

                    $Query_OT = $this->db->query($Sql_OT);

                    if($Allocation_Type == 'EXTRA'){


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                     'Working_Type' => 'EXTRA',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                     'Working_Type' => 'EXTRA',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => ''
                                    
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                     'Working_Type' => 'EXTRA',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department === 'Spinning-Prod' || $Sub_Department === 'Finishing-Prod' ||  $Sub_Department === 'Preparatory-Prod' || $Sub_Department == 'SPINNING-PROD' || $Sub_Department == 'FINISHING-PROD'  || $Sub_Department === 'PREPARATORY-PROD') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                         'Working_Type' => 'EXTRA',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                         'Working_Type' => 'EXTRA',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    } else if($Allocation_Type == 'LATE'){


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department === 'Spinning-Prod' || $Sub_Department === 'Finishing-Prod' ||  $Sub_Department === 'Preparatory-Prod' || $Sub_Department == 'SPINNING-PROD' || $Sub_Department == 'FINISHING-PROD'  || $Sub_Department === 'PREPARATORY-PROD') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : '' 
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    }

                    
                }
            } else if ($LocationCode == 'PRECOT - C' ) {


                $success = true;
                $allocation_details = [];

                foreach ($input_data['Allocations'] as $row) {

                    $Sub_Department = $row['Department'];
                    $Shift = $row['Shift'];
                    $Date = $row['Date'];
                    $Work_Area = $row['Work_Area'];
                    $Employee_Id = $row['EmployeeId'];
                    $Frames = $row['Frames'];
                    $Machine_Id = $row['Machine_Id'];
                    // $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];
                    $Supervisor_Name = $row['Supervisor'];


                      function getPreviousShift($currentShift, $shiftMaster)
                    {
                        $shiftKeys = array_keys($shiftMaster);
                        if (in_array($currentShift, $shiftKeys)) {
                            $currentIndex = array_search($currentShift, $shiftKeys);
                            $previousIndex = ($currentIndex - 1) < 0 ? count($shiftKeys) - 1 : $currentIndex - 1;
                            return $shiftKeys[$previousIndex];
                        }
                        return null;
                    }



                    $Shift_Master = [

                        'SHIFT1' => 'SHIFT1',
                        'SHIFT2' => 'SHIFT2',
                        'SHIFT3' => 'SHIFT3',

                    ];

                    $Current_Shift = $Shift;

                    $Previous_Shift = getPreviousShift($Current_Shift, $Shift_Master);


                  
                    // Check Previous Allocation 
                   if ($Shift == 'SHIFT1') {
                        $Previous_Date = date('Y-m-d', strtotime($Date . ' -1 days'));
                        $Sql_OT_PreviousDay = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                            INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                            WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                            AND Work.Date = '$Previous_Date' AND Work.EmpNo = '$Employee_Id'
                            AND Work.Shift = 'SHIFT3' AND Work.Work_Status = '1'
                            AND Login.UserID = '$Login_User'";
                        $this->db->query($Sql_OT_PreviousDay);
                    }

                    $Sql_OT = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                        INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                        WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                        AND Work.Date = '$Date' AND Work.EmpNo = '$Employee_Id'
                        AND Work.Shift = '$Previous_Shift' AND Work.Work_Status = '1'
                        AND Login.UserID = '$Login_User'";

                    $Query_OT = $this->db->query($Sql_OT);

                    if($Allocation_Type == 'EXTRA'){



                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department === 'Spinning' || $Sub_Department === 'Finishing' ||  $Sub_Department === 'Preparatory' || $Sub_Department == 'SPINNING' || $Sub_Department == 'FINISHING'  || $Sub_Department === 'PREPARATORY') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                         'Working_Type' => 'EXTRA',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    } else if($Allocation_Type == 'LATE'){


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department === 'Spinning' || $Sub_Department === 'Finishing' ||  $Sub_Department === 'Preparatory' || $Sub_Department == 'SPINNING' || $Sub_Department == 'FINISHING'  || $Sub_Department === 'PREPARATORY') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    }
                }
            } else if($LocationCode == 'PRECOT - D'){



                $success = true;
                $allocation_details = [];

                foreach ($input_data['Allocations'] as $row) {

                    $Sub_Department = $row['Department'];
                    $Shift = $row['Shift'];
                    $Date = $row['Date'];
                    $Work_Area = $row['Work_Area'];
                    $Employee_Id = $row['EmployeeId'];
                    $Frames = $row['Frames'];
                    $Machine_Id = $row['Machine_Id'];
                    // $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];
                    $Supervisor_Name = $row['Supervisor'];


                      function getPreviousShift($currentShift, $shiftMaster)
                    {
                        $shiftKeys = array_keys($shiftMaster);
                        if (in_array($currentShift, $shiftKeys)) {
                            $currentIndex = array_search($currentShift, $shiftKeys);
                            $previousIndex = ($currentIndex - 1) < 0 ? count($shiftKeys) - 1 : $currentIndex - 1;
                            return $shiftKeys[$previousIndex];
                        }
                        return null;
                    }



                    $Shift_Master = [

                        'SHIFT1' => 'SHIFT1',
                        'SHIFT2' => 'SHIFT2',
                        'SHIFT3' => 'SHIFT3',

                    ];

                    $Current_Shift = $Shift;

                    $Previous_Shift = getPreviousShift($Current_Shift, $Shift_Master);


                  
                    // Check Previous Allocation 
                   if ($Shift == 'SHIFT1') {
                        $Previous_Date = date('Y-m-d', strtotime($Date . ' -1 days'));
                        $Sql_OT_PreviousDay = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                            INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                            WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                            AND Work.Date = '$Previous_Date' AND Work.EmpNo = '$Employee_Id'
                            AND Work.Shift = 'SHIFT3' AND Work.Work_Status = '1'
                            AND Login.UserID = '$Login_User'";
                        $this->db->query($Sql_OT_PreviousDay);
                    }

                    $Sql_OT = "SELECT 1 FROM Web_Employee_Work_Allocation_Mst Work
                        INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                        WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                        AND Work.Date = '$Date' AND Work.EmpNo = '$Employee_Id'
                        AND Work.Shift = '$Previous_Shift' AND Work.Work_Status = '1'
                        AND Login.UserID = '$Login_User'";

                    $Query_OT = $this->db->query($Sql_OT);

                    

                    if($Allocation_Type == 'EXTRA'){



                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Working_Type' => '',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                     'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                     'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department == 'TFO' || $Sub_Department == 'tfo' || $Sub_Department == 'Tfo' ||  $Sub_Department == 'DOUBLING' || $Sub_Department == 'Doubling' || $Sub_Department == 'GASSING'  || $Sub_Department == 'Gassing' || $Sub_Department == 'WINDING' || $Sub_Department == 'Winding') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => '',
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                         'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => '',
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'EXTRA',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    } else if($Allocation_Type == 'LATE'){


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department == 'TFO' || $Sub_Department == 'tfo' || $Sub_Department == 'Tfo' ||  $Sub_Department == 'DOUBLING' || $Sub_Department == 'Doubling' || $Sub_Department == 'GASSING'  || $Sub_Department == 'Gassing' || $Sub_Department == 'WINDING' || $Sub_Department == 'Winding') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                        'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                        'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                    'Working_Type' => $Query_OT->num_rows() == 1 ? 'OT' : 'LATE',
                                    'Previous_Shift' => $Query_OT->num_rows() == 1 ? $Previous_Shift : ''
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }


                    }

            }
        }
            
        
            
            else if ($LocationCode == 'PRECOT - M') {

                $success = true;
                $allocation_details = [];

                foreach ($input_data['Allocations'] as $row) {

                    $Sub_Department = $row['Department'];
                    $Shift = $row['Shift'];
                    $Date = $row['Date'];
                    $Work_Area = $row['Work_Area'];
                    $Employee_Id = $row['EmployeeId'];
                    $Frames = $row['Frames'];
                    $Machine_Id = $row['Machine_Id'];
                    // $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];
                    $Supervisor_Name = $row['Supervisor'];


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    $Job_Card_No =  $Result_Job[0]->JobCard_No;


                    $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages,DeptName,DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                    if (empty($Employee_Data)) {
                        $success = false;
                        $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                    }

                    $Employee_Name = $Employee_Data[0]->FirstName;
                    $ExistingCode = $Employee_Data[0]->ExistingCode;
                    $Wages = $Employee_Data[0]->wages;
                    $Department = $Employee_Data[0]->DeptGrp;
                    $Sub_Section = $Employee_Data[0]->SubSection_Name;



                    if ($Machine_Id == []) {

                        foreach ($Frames as $Frame_Data) {

                            if ($Frame_Data == 'NoWork') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'NoWork',
                                    'Status_Updated' => 'NoWork',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '0',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',

                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Others') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee' || $Frame_Data == 'MULTIPLE TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Multiple Trainee',
                                    'Status_Updated' => 'Multiple Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => '',
                                    'Machine_Name' => '-',
                                    'Machine_Model' => '-',
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Trainee',
                                    'Status_Updated' => 'Trainee',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '1',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {

                        if ($Sub_Department == 'Finishing - PM1' || $Sub_Department == 'Finishing - PM2' || $Sub_Department == 'Preparatory - PM1' || $Sub_Department == 'Preparatory - PM2' || $Sub_Department == 'Spinning - PM1' || $Sub_Department == 'Spinning - PM2') {


                            foreach ($Machine_Id as $index => $Machine_datas) {

                                // exit;
                                // for example  $Frame_Data = S1 again next time   $Frame_Data  this  variable  data set for $Duplicated variable

                                $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines



                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';


                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                } else {


                                    $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                                    $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines

                                    $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_datas'")->result();
                                    $Machine_Name = $machine_data[0]->Machine_Name;
                                    $Machine_Model = $machine_data[0]->Machine_Model;


                                    $Work_Allocation = [
                                        'Ccode' => $CompanyCode,
                                        'Lcode' => $LocationCode,
                                        'Wages' => $Wages,
                                        'Machine_Id' => $Machine_datas,
                                        'Machine_Name' => $Machine_Name,
                                        'Machine_Model' => $Machine_Model,
                                        'FirstName' => $Employee_Name,
                                        'EmpNo' => $Employee_Id,
                                        'ExistingCode' => $ExistingCode,
                                        'Shift' => $Shift,
                                        'Date' => $Date,
                                        'Job_Card_No' => $Job_Card_No,
                                        'Work_Type' => 'Machine',
                                        'Status_Updated' => 'Machine',
                                        'Department' => $Department,
                                        'Sub_Department' => $Sub_Department,
                                        'Sub_Section' => $Sub_Section,
                                        
                                        'Screen_Type' => $Allocation_Screen_Type,
                                        'OT_Confirmation' => '-',
                                        'WorkArea' => $Work_Area,
                                        'Frame' => $Frame_Data,
                                        'FrameType' => '',
                                        'Description' => $Description,
                                        'Type' => $Allocation_Type,
                                        'Work_Status' => '1',
                                        'Assign_Status' => '1',
                                        'Closing_Status' => '0',
                                        'IsWork' => '0',
                                        'Created_By' => $Supervisor_Name,
                                        'Created_Time' => date('Y-m-d H:i:s'),
                                        'Updated_By' => '-',
                                        'Updated_Time' => '-',
                                    ];

                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                    if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                        $success = false;
                                    }
                                }
                            }
                        } else {



                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Frame_Data = '';


                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = '$Frame_Data'")->result();
                                $Machine_Name = $machine_data[0]->Machine_Name ?: '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?: '-';



                                $existing_combination = $this->db->query("SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

                                if ($existing_combination->num_rows() > 0) {
                                    $allocation_details[] = ['status' => 'skip', 'message' => 'Duplicate allocation found, skipping insertion'];
                                    continue;
                                }

                                $Work_Allocation = [
                                    'Ccode' => $CompanyCode,
                                    'Lcode' => $LocationCode,
                                    'Wages' => $Wages,
                                    'Machine_Id' => $Machine_datas,
                                    'Machine_Name' => $Machine_Name,
                                    'Machine_Model' => $Machine_Model,
                                    'FirstName' => $Employee_Name,
                                    'EmpNo' => $Employee_Id,
                                    'ExistingCode' => $ExistingCode,
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Machine',
                                    'Status_Updated' => 'Machine',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    
                                    'OT_Confirmation' => '-',
                                    'WorkArea' => $Work_Area,
                                    'Frame' => $Frame_Data,
                                    'FrameType' => '',
                                    'Description' => $Description,
                                    'Type' => $Allocation_Type,
                                    'Work_Status' => '1',
                                    'Assign_Status' => '1',
                                    'Closing_Status' => '0',
                                    'IsWork' => '0',
                                    'Created_By' => $Supervisor_Name,
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];



                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    private function delete_existing_allocation($Shift, $Date, $Employee_Id)
    {
        $sql_check_existing = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1' AND Assign_Status = '0' ";
        $Query = $this->db->query($sql_check_existing);

        $Sql4 = "UPDATE Web_Late_Extra_Mst SET Assign_Status = '1' WHERE EmpNo = '$Employee_Id' AND Shift = '$Shift' AND Date = '$Date' AND Assign_Status = '0' AND Work_Status = '1'";
        $query1 = $this->db->query($Sql4);

        if ($Query->num_rows() > 0) {
            // Delete existing allocation
            $sql_delete = "DELETE FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1' AND Assign_Status = '0' ";
            $this->db->query($sql_delete);
        }
    }


    public function Seperated_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Section, $Login_User)
    {

        $sql4 = "SELECT *,
                            CASE WHEN Work.Assign_Status = '0' THEN 'Unassigned'
                                 WHEN Work.Work_Type = 'NoWork' THEN 'NoWork'
                                 ELSE 'Assigned' END AS WorkStatus
                    FROM Web_Late_Extra_Mst Work
                    INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode AND Login.Name = Work.Sub_Department
                    WHERE Login.UserID = '$Login_User'
                    AND Work.Date = '$Date'
                    AND Work.Shift = '$Shift'
                    AND Work.Sub_Section = '$Sub_Section'
                    AND Work.Work_Status = '1'
                    AND Work.Assign_Status = '0'";

        $All_Employee_List = $this->db->query($sql4)->result();


        return $All_Employee_List;
    }

    public function Late_And_Extra_Employee_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Assigning_Shift)
    {
        $sql = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Assigning_Shift'";
        $shift_Data = $this->db->query($sql)->row();

        if ($shift_Data) {
            $Shift_Pounch_Start = $shift_Data->EndIN;
            $Shift_Pounch_End = $shift_Data->StartOUT;

            $From_Shift_Date_Convert = $Date;
            $To_Shift_Date_Convert = $Date;

            if ($shift_Data->StartIN_Days == 1) {
                $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
            }

            if ($shift_Data->EndOUT_Days == 1) {
                $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
            }

            $sql2 = "SELECT COUNT(DISTINCT Time.MachineID) AS Count
                     FROM UserDetails_Det Log
                     INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                     INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                     WHERE Log.UserID = '$Login_User'
                     AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
                     AND Emp.CatName = 'WORKER'
                     AND Time.CompCode = '$CompanyCode'
                     AND Time.LocCode = '$LocationCode'
                     AND Emp.WorkArea IS NOT NULL
                     AND Emp.IsActive = 'Yes'
                     AND Time.MachineID NOT IN (
                         SELECT EmpNo
                         FROM Web_Employee_Work_Allocation_Mst
                         WHERE Lcode = '$LocationCode'
                         AND Work_Status = '1'
                         AND CONVERT(varchar, Date, 103) = CONVERT(varchar, '$Date', 103)
                         AND Wages != 'STAFF'
                         AND Ccode = '$CompanyCode'
                         AND Shift = '$Assigning_Shift'
                         AND Date = '$Date'
                     )";


            $result = $this->db->query($sql2)->row();
            $Late_Employee_Count = $result ? $result->Count : 0;

            return [
                'Late_Comers' => (int)$Late_Employee_Count,
            ];
        }
    }
}
