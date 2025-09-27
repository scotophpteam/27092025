<?php

use PhpParser\Builder\Function_;

if (! defined('BASEPATH')) exit('No direct script access allowed');


class  OT_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    public function Contiune_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {
        $Sql = "SELECT
                    Work.EmpNo,
                    Work.WorkArea,
                    Work.Machine_Id, 
                    Work.Frame,
                    Work.Description,
                    Work.Closing_Status,
                    Work.FirstName,
                    -- Combine Work.Date with Updated_Time's time component
                    CASE
                        WHEN TRY_CONVERT(TIME, Work.Updated_Time, 120) IS NOT NULL
                        THEN CONVERT(VARCHAR(10), Work.Date, 120) + ' ' + CONVERT(VARCHAR(5), TRY_CONVERT(TIME, Work.Updated_Time, 120))
                        ELSE NULL
                    END AS Updated_Time,
                    Work.Date
                FROM Web_Employee_Work_Allocation_Mst AS Work
                INNER JOIN UserDetails_Det AS Login
                    ON Login.Lcode = Work.Lcode
                    AND Login.Name = Work.Sub_Department
                WHERE
                    Login.Ccode = '$CompanyCode'
                    AND Login.Lcode = '$LocationCode'
                    AND Login.UserID = '$Login_User'
                    AND Work.Date = '$Date'
                    AND Work.Shift = '$Shift'
                    AND Work.Work_Status = '1'
                    AND Work.Assign_Status = '1'
                    AND Work.Working_Type = 'OT'";

        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            $Details = $Query->result();
            $Data = [];

            $Sql_Shift = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $Shift_Data = $this->db->query($Sql_Shift)->row();

            foreach ($Details as $Detail) {
                $Employee_ID = $Detail->EmpNo;
                $Shift_Date_Convert = $Date;

                if ($Shift_Data) {
                    $Shift_Date_Conversion = ($Shift_Data->StartIN_Days == 1 && $Shift_Data->EndIN_Days == 1)
                        ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                        : $Shift_Date_Convert;
                } else {
                    $Shift_Date_Conversion = $Shift_Date_Convert;
                }

                $Sql_Punch_IN = "SELECT MachineID, FORMAT(MIN(TimeIN), 'HH:mm') AS TimeIN
                                 FROM LogTime_IN
                                 WHERE CompCode = '$CompanyCode'
                                   AND LocCode = '$LocationCode'
                                   AND MachineID = '$Employee_ID'
                                   AND CAST(TimeIN AS DATE) = '$Shift_Date_Conversion'
                                 GROUP BY MachineID";

                $Sql_Punch_OUT = "SELECT MachineID, FORMAT(MAX(TimeOUT), 'HH:mm') AS TimeOUT
                                  FROM LogTime_OUT
                                  WHERE CompCode = '$CompanyCode'
                                    AND LocCode = '$LocationCode'
                                    AND MachineID = '$Employee_ID'
                                    AND CAST(TimeOUT AS DATE) = '$Shift_Date_Conversion'
                                  GROUP BY MachineID;";






                $PunchInQuery = $this->db->query($Sql_Punch_IN);
                $PunchOutQuery = $this->db->query($Sql_Punch_OUT);

                $TimeIN = $PunchInQuery->num_rows() > 0 ? $PunchInQuery->row()->TimeIN : null;
                $TimeOUT = $PunchOutQuery->num_rows() > 0 ? $PunchOutQuery->row()->TimeOUT : null;
                $UpdatedTime = $Detail->Updated_Time ? substr($Detail->Updated_Time, 11, 5) : null;

                // Calculate Total_Working_Hours
                $Total_Working_Hours = '0:00';
                if ($TimeIN && $TimeOUT) {
                    $timeIn = new DateTime($TimeIN);
                    $timeOut = new DateTime($TimeOUT);
                    // Handle cases where TimeOUT is on the next day
                    if ($timeOut < $timeIn) {
                        $timeOut->modify('+1 day');
                    }
                    $interval = $timeIn->diff($timeOut);
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    $hours = ceil($minutes / 60);
                    $Total_Working_Hours = sprintf('%d:00', $hours);
                }

                // Calculate Time_Difference (UpdatedTime - TimeOUT)
                $Time_Difference = 'Invalid';
                if ($UpdatedTime && $TimeOUT) {
                    $updated = new DateTime($UpdatedTime);
                    $timeOut = new DateTime($TimeOUT);
                    $interval = $updated->diff($timeOut);
                    $totalMinutes = abs(($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i);
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    $Time_Difference = sprintf('%02d:%02d', $hours, $minutes);
                }

                $Data[] = [
                    'FirstName'          => $Detail->FirstName,
                    'EmpNo'              => $Detail->EmpNo,
                    'WorkArea'           => $Detail->WorkArea,
                    'Machine_Id'         => $Detail->Machine_Id,
                    'Frame'              => $Detail->Frame,
                    'Description'        => $Detail->Description,
                    'Closing_Status'     => $Detail->Closing_Status,
                    'TimeIN'             => $TimeIN,
                    'TimeOUT'            => $TimeOUT,
                    'UpdatedTime'        => $UpdatedTime,
                    'Total_Working_Hours' => $Total_Working_Hours
                ];
            }

            return $Data;
        } else {
            return 0;
        }
    }


    public function Get_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Login_User)
    {

        $sql = "SELECT Distinct Work.Sub_Section FROM Web_Employee_Work_Allocation_Mst Work INNER JOIN UserDetails_Det Login  ON Work.Lcode = Login.Lcode  AND Work.Ccode = Login.Ccode AND Work.Sub_Department = Login.Name WHERE Login.Ccode = '$CompanyCode' AND Login.Lcode = '$LocationCode' AND Work.Date = '$Date' AND Work.Shift = '$Shift' AND Login.UserID = '$Login_User'";
        $query = $this->db->query($sql);
        $Rows = $query->num_rows();

        if ($Rows > 0) {

            $Sub_Section = $query->result();
            return $Sub_Section;
        } else {

            $Status = [
                'Status' => 'Error',
                'Message' => 'Sub Section Details Not Fount.!',
            ];

            return $Status;
        }
    }


    public function Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date)
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {


            $Extra_Hours_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst Work
                INNER JOIN UserDetails_Det Login ON Work.Lcode = Login.Lcode
                    AND Work.Ccode = Login.Ccode AND Login.Name = Work.Sub_Department
                WHERE Work.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode'
                    AND Work.Date = '$Date'AND Work.Work_Status = '1'
                     AND Login.UserID = '$Login_User'";


            $query = $this->db->query($Extra_Hours_Sql);
            $Rows = $query->num_rows();

            if ($Rows > 0) {
                return $query->result();
            } else {
                return 0;
            }
        }
    }



    //     public function Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    // {


    //     $Session = $this->session->userdata('sess_array');
    //     if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

    //         $current_time = '2025-01-22 18:00:49.000';
    //         $Date = '2025-01-22';


    //         $sql2 = "WITH EmployeePunches AS (
    //             SELECT
    //                 Time.MachineID,
    //                 Emp.FirstName,
    //                 Emp.Wages,
    //                 Emp.WorkArea,
    //                 Emp.JobCardNo,
    //                 Emp.DeptName,
    //                 Emp.DeptGrp,
    //                 Emp.SubSection_Name,
    //                 Time.TimeOUT,
    //                 ROW_NUMBER() OVER (PARTITION BY Emp.MachineID ORDER BY Time.TimeOUT ASC) AS RowNum
    //             FROM
    //                 UserDetails_Det Log
    //             INNER JOIN
    //                 Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
    //             INNER JOIN
    //                 LogTimeLunch_OUT Time ON Time.MachineID = Emp.MachineID
    //             WHERE
    //                 Log.UserID = 'pappl'
    //                 AND CONVERT(DATE, Time.TimeOUT) = '$Date'
    //                 AND Emp.CatName != 'STAFF'
    //                 AND Time.CompCode = '$CompanyCode'
    //                 AND Time.LocCode = '$LocationCode'
    //                 AND Emp.IsActive = 'Yes'
    //                 AND Time.TimeOUT BETWEEN
    //                     DATEADD(HOUR, -3, '$current_time') AND '$current_time'
    //         )
    //         SELECT
    //             MachineID,
    //             FirstName,
    //             Wages,
    //             WorkArea,
    //             JobCardNo,
    //             DeptName,
    //             DeptGrp,
    //             SubSection_Name,
    //             TimeOUT
    //         FROM
    //             EmployeePunches
    //         WHERE
    //             RowNum = 1";


    //         $log_Data = $this->db->query($sql2)->result();


    //         $current_time = date('Y-m-d H:i:s');

    //         foreach ($log_Data as $Employee_Data) {
    //             $Employee_Id = $Employee_Data->MachineID;
    //             $Employee_WorkArea = $Employee_Data->WorkArea;
    //             $Employee_Department = $Employee_Data->DeptName;

    //             $existing_sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst
    //                         WHERE Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1'";

    //             if ($this->db->query($existing_sql)->num_rows() == 0) {

    //                 $Work_Check_Sql = "SELECT * FROM Web_Work_Area_Mst Work
    //                             INNER JOIN Web_Machine_Mst Machine
    //                                 ON Work.Ccode = Machine.Ccode
    //                                 AND Work.Lcode = Machine.Lcode
    //                                 AND Work.WorkArea = Machine.WorkArea
    //                                 AND Work.Department = Machine.Department
    //                             WHERE Machine.WorkArea = '$Employee_WorkArea'
    //                                 AND Work.Department = '$Employee_Department'";

    //                 $Work_Check_Query = $this->db->query($Work_Check_Sql);
    //                 $Work_Check_Rows = $Work_Check_Query->num_rows();

    //                 $allocations = [
    //                     'Ccode' => $CompanyCode,
    //                     'Lcode' => $LocationCode,
    //                     'Wages' => $Employee_Data->Wages,
    //                     'FirstName' => $Employee_Data->FirstName,
    //                     'EmpNo' => $Employee_Id,
    //                     'ExistingCode' => $Employee_Id,
    //                     'Shift' => '-',
    //                     'Date' => $Date,
    //                     'Job_Card_No' => $Employee_Data->JobCardNo,
    //                     'Department' => $Employee_Data->DeptGrp,
    //                     'Sub_Department' => $Employee_Data->DeptName,
    //                     'Sub_Section' => $Employee_Data->SubSection_Name,
    //                     'WorkArea' => $Employee_Data->WorkArea,
    //                     'Previous_Shift' => '-',
    //                     'OT_Confirmation' => '-',
    //                     'Working_Type' => 'EXTRA',
    //                     'Machine_Id' => '',
    //                     'Machine_Name' => '-',
    //                     'FrameType' => '-',
    //                     'Frame' => ($Work_Check_Rows == 0) ? 'Others' : '-',
    //                     'Type' => $Type,
    //                     'Screen_Type' => 'Shift_Employee_Screen',
    //                     'Work_Type' => ($Work_Check_Rows == 0) ? 'Others' : '-',
    //                     'Status_Updated' => ($Work_Check_Rows == 0) ? 'Others' : '-',
    //                     'Work_Start' => '-',
    //                     'Work_End' => '-',
    //                     'Work_Duration' => '-',
    //                     'Machine_EB_No' => '-',
    //                     'Work_Status' => '1',
    //                     'Assign_Status' => ($Work_Check_Rows == 0) ? '1' : '0',
    //                     'Closing_Status' => '0',
    //                     'IsWork' => '0',
    //                     'Edit_Reason' => '-',
    //                     'Description' => $Employee_Data->WorkArea,
    //                     'Created_By' => $Login_User,
    //                     'Created_Time' => $current_time,
    //                     'Updated_By' => '-',
    //                     'Updated_Time' => '-',
    //                 ];

    //                 $this->db->insert('Web_Extra_Work_Allocation_Mst', $allocations);
    //             }
    //         }

    //         $NoWork_Sql = "UPDATE Work
    //                         SET
    //                             Work.Sub_Department = Login.Name,
    //                             Work.WorkArea = '',
    //                             Work.Job_Card_No = ''
    //                         FROM Web_Extra_Work_Allocation_Mst AS Work
    //                         INNER JOIN UserDetails_Det AS Login
    //                             ON Work.Lcode = Login.Lcode
    //                             AND Work.Ccode = Login.Ccode
    //                         WHERE
    //                             Work.Date = '$Date'
    //                             AND Work.Work_Type = 'NoWork'
    //                             AND Work.Work_Status = '1'
    //                             AND Work.Assign_Status = '0'
    //                             AND Work.Lcode = '$LocationCode'
    //                             AND Login.Ccode = '$CompanyCode'
    //                             AND Login.UserID = '$Login_User'";

    //         $this->db->query($NoWork_Sql);

    //         $sql4 = "SELECT Work.Type AS Type,
    //                     Work.*,
    //                     CASE
    //                         WHEN Work.Assign_Status = '0' THEN 'Unassigned'
    //                         WHEN Work.Work_Type = 'NoWork' THEN 'NoWork'
    //                         ELSE 'Assigned'
    //                     END AS WorkStatus
    //                  FROM Web_Extra_Work_Allocation_Mst AS Work
    //                  INNER JOIN UserDetails_Det AS Login
    //                      ON Login.Lcode = Work.Lcode
    //                      AND Login.Name = Work.Sub_Department
    //                  WHERE Login.UserID = '$Login_User'
    //                    AND Work.Date = '$Date'
    //                    AND Work.Work_Status = '1'";

    //         $employee_data = $this->db->query($sql4)->result();
    //         $Un_Assigned_Data = [];
    //         $Assigned_Data = [];
    //         $No_Work_Data = [];

    //         foreach ($employee_data as $employee) {
    //             if ($employee->WorkStatus == 'Unassigned') {
    //                 $Un_Assigned_Data[] = $employee;
    //             } elseif ($employee->WorkStatus == 'Assigned') {
    //                 $Assigned_Data[] = $employee;
    //             } else {
    //                 $No_Work_Data[] = $employee;
    //             }
    //         }

    //         $NoWork_Employee_Sql1 = "SELECT Work.Type AS Type, Work.*
    //                     FROM Web_Extra_Work_Allocation_Mst AS Work
    //                     INNER JOIN UserDetails_Det AS Login
    //                         ON Login.Lcode = Work.Lcode
    //                         AND Login.Name = Work.Sub_Department
    //                     WHERE Login.UserID = '$Login_User'
    //                       AND Work.Date = '$Date'
    //                       AND Work.Work_Type = 'NoWork'
    //                       AND Work.Work_Status = '1'
    //                       AND Work.Assign_Status = '0'";

    //         $user_dept_sql = "SELECT Name FROM UserDetails_Det
    //                                   WHERE UserID = '$Login_User'
    //                                   AND Ccode = '$CompanyCode'
    //                                   AND Lcode = '$LocationCode'";
    //         $user_dept = $this->db->query($user_dept_sql)->row();
    //         $deptName = $user_dept ? $user_dept->Name : '';

    //         if ($deptName == 'HRD' || $deptName == 'Human Resource Services') {
    //             $NoWorkEmployeeList1 = $this->db->query($NoWork_Employee_Sql1)->result();
    //             $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data);
    //             return $All_Employee_List;
    //         } else {
    //             $NoWorkEmployeeList1 = $this->db->query($NoWork_Employee_Sql1)->result();
    //             $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data, $NoWorkEmployeeList1);
    //             return $All_Employee_List;
    //         }
    //     } else {
    //         redirect(base_url(), 'refresh');
    //     }
    // }



    public function Extra_Employee_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Assigning_Shift)
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
                     INNER JOIN LogTimeLunch_OUT Time ON Time.MachineID = Emp.MachineID
                     WHERE Log.UserID = '$Login_User'
                     AND Time.TimeOUT BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
                     AND Emp.CatName != 'STAFF'
                     AND Time.CompCode = '$CompanyCode'
                     AND Time.LocCode = '$LocationCode'
                     AND Emp.WorkArea IS NOT NULL
                     AND Emp.IsActive = 'Yes'
                     AND Time.MachineID NOT IN (
                         SELECT EmpNo
                         FROM Web_Extra_Work_Allocation_Mst
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

    public function Work_Type($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Department, $Work_Area, $JobCard)
    {


        $sql = "SELECT Machine_Id, Frame FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Department = '$Sub_Department'";
        $query = $this->db->query($sql);
        $Machine_Data = $query->result();

        $sql1 = "SELECT Machine_Id, Frame FROM Web_Employee_Work_Allocation_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND Date = '$Date' AND Sub_Department = '$Sub_Department' AND WorkArea = '$Work_Area' AND Work_Status = '1' AND Assign_Status = '1'";
        $query1 = $this->db->query($sql1);
        $Assigned_Machine_Date = $query1->result();

        // print_r($sql1);exit;

        $assigned_machines = [];
        foreach ($Assigned_Machine_Date as $assigned) {
            $assigned_machines[] = $assigned->Machine_Id . '-' . $assigned->Frame; // Combine Machine_Id and Frame for easy comparison
        }

        $Balance_Machines = [];
        foreach ($Machine_Data as $machine) {
            $machine_key = $machine->Machine_Id . '-' . $machine->Frame;
            if (!in_array($machine_key, $assigned_machines)) {
                $Balance_Machines[] = $machine;
            }
        }


        return $Balance_Machines;
    }



    public function Work_Areas($CompanyCode, $LocationCode, $Login_User, $Sub_Department)
    {

        $sql = "SELECT WorkArea FROM Web_Work_Area_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Sub_Department'";
        $query = $this->db->query($sql);
        $Work_Areas = $query->result();

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Work_Areas;
        } else {

            return 0;
        }
    }


    public function Job_Card_Nos($CompanyCode, $LocationCode, $Login_User, $Sub_Department, $WorkArea)
    {

        $sql = "SELECT JobCard_No FROM Web_JobCard_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Sub_Department' AND WorkArea = '$WorkArea'";
        $query = $this->db->query($sql);
        $Job_Card_Nos = $query->result();

        if ($query->num_rows() > 0) {

            return $Job_Card_Nos;
        } else {

            return 0;
        }
    }

        public function Assign($input_data, $CompanyCode, $LocationCode)
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
                    $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',

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
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                // echo '<pre>';
                                // print_r($Work_Allocation);
                                // exit;

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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

                                $Duplicate = $Frames[$index] ?? null;
                                $Frame_Data = $Frames[0] ?: $Duplicate;



                                $machine_data = $this->db->query("
            SELECT Machine_Name, Machine_Model 
            FROM Web_Machine_Mst 
            WHERE CCode = '$CompanyCode' 
              AND LCode = '$LocationCode' 
              AND WorkArea = '$Work_Area' 
              AND Machine_Id = '$Machine_datas'
        ")->result();

                                $Machine_Name = $machine_data[0]->Machine_Name ?? '-';
                                $Machine_Model = $machine_data[0]->Machine_Model ?? '-';

                                $Work_Allocation = [
                                    'Ccode'            => $CompanyCode,
                                    'Lcode'            => $LocationCode,
                                    'Wages'            => $Wages,
                                    'Machine_Id'       => $Machine_datas,
                                    'Machine_Name'     => $Machine_Name,
                                    'Machine_Model'    => $Machine_Model,
                                    'FirstName'        => $Employee_Name,
                                    'EmpNo'            => $Employee_Id,
                                    'ExistingCode'     => $ExistingCode,
                                    'Shift'            => $Shift,
                                    'Date'             => $Date,
                                    'Job_Card_No'      => $Job_Card_No,
                                    'Work_Type'        => 'Machine',
                                    'Status_Updated'   => 'Machine',
                                    'Department'       => $Department,
                                    'Sub_Department'   => $Sub_Department,
                                    'Sub_Section'      => $Sub_Section,
                                    'Previous_Shift'   => '-',
                                    'Screen_Type'      => $Allocation_Screen_Type,
                                    'OT_Confirmation'  => '-',
                                    'WorkArea'         => $Work_Area,
                                    'Frame'            => $Frame_Data,     // M1, M2, ...
                                    'FrameType'        => '',
                                    'Description'      => $Description,
                                    'Type'             => $Allocation_Type,
                                    'Work_Status'      => '1',
                                    'Assign_Status'    => '1',
                                    'Closing_Status'   => '0',
                                    'IsWork'           => '0',
                                    'Created_By'       => $Session['UserName'],
                                    'Created_Time'     => date('Y-m-d H:i:s'),
                                    'Updated_By'       => '-',
                                    'Updated_Time'     => '-',
                                ];

                                // Optional: Insert or show for testing


                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);
                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Work allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning work allocation'];
                                    $success = false;
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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
                }
            } else if ($LocationCode == 'PRECOT - C' || $LocationCode == 'PRECOT - D') {


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
                    $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];


                    $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                    $Query_Job = $this->db->query($sql_Job);
                    $Result_Job = $Query_Job->result();


                    // $Job_Card_No =  $Result_Job[0]->JobCard_No;


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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',

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
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                // echo '<pre>';
                                // print_r($Work_Allocation);
                                // exit;

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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

                        if (
                            $Sub_Department == 'Finishing' ||
                            $Sub_Department == 'Preparatory' ||
                            $Sub_Department == 'Spinning ' ||
                            $Sub_Department == 'Doubling & Winding' ||
                            $Sub_Department == 'Gassing' ||
                            $Sub_Department == 'TFO' ||
                            $Sub_Department == 'FINISHING' ||
                            $Sub_Department == 'PREPARATORY' ||
                            $Sub_Department == 'SPINNING' ||
                            $Sub_Department == 'DOUBLING & WINDING' ||
                            $Sub_Department == 'GASSING' ||
                            $Sub_Department == 'TFO'
                        ) {

                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Duplicate = $Frames[$index] ?? null;
                                $Frame_Data = $Frames[0] ?: $Duplicate;

                                if ($Frame_Data == 'Machine Wise') {

                                    // $Frame_Data = 'Machine Wise';

                                    $machine_data = $this->db->query("
            SELECT Machine_Name, Machine_Model 
            FROM Web_Machine_Mst 
            WHERE CCode = '$CompanyCode' 
              AND LCode = '$LocationCode' 
              AND WorkArea = '$Work_Area' 
              AND Machine_Id = '$Machine_datas'
        ")->result();

                                    $Machine_Name = $machine_data[0]->Machine_Name ?? '-';
                                    $Machine_Model = $machine_data[0]->Machine_Model ?? '-';

                                    $Work_Allocation = [
                                        'Ccode'            => $CompanyCode,
                                        'Lcode'            => $LocationCode,
                                        'Wages'            => $Wages,
                                        'Machine_Id'       => $Machine_datas,
                                        'Machine_Name'     => $Machine_Name,
                                        'Machine_Model'    => $Machine_Model,
                                        'FirstName'        => $Employee_Name,
                                        'EmpNo'            => $Employee_Id,
                                        'ExistingCode'     => $ExistingCode,
                                        'Shift'            => $Shift,
                                        'Date'             => $Date,
                                        'Job_Card_No'      => $Job_Card_No,
                                        'Work_Type'        => 'Machine',
                                        'Status_Updated'   => 'Machine',
                                        'Department'       => $Department,
                                        'Sub_Department'   => $Sub_Department,
                                        'Sub_Section'      => $Sub_Section,
                                        'Previous_Shift'   => '-',
                                        'Screen_Type'      => $Allocation_Screen_Type,
                                        'OT_Confirmation'  => '-',
                                        'WorkArea'         => $Work_Area,
                                        'Frame'            => $Frame_Data,     // M1, M2, ...
                                        'FrameType'        => '',
                                        'Description'      => $Description,
                                        'Type'             => $Allocation_Type,
                                        'Work_Status'      => '1',
                                        'Assign_Status'    => '1',
                                        'Closing_Status'   => '0',
                                        'IsWork'           => '0',
                                        'Created_By'       => $Session['UserName'],
                                        'Created_Time'     => date('Y-m-d H:i:s'),
                                        'Updated_By'       => '-',
                                        'Updated_Time'     => '-',
                                    ];

                                    // Optional: Insert or show for testing


                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);
                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'Work allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning work allocation'];
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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
                }
            } else if ($LocationCode == 'PRECOT - M') {

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
                    $Job_Card_No = $row['JobCardNo'];
                    $Description = $row['Description'];
                    $Allocation_Type = $row['Allocation_Type'];
                    $Allocation_Screen_Type = $row['Allocation_Screen_Type'];


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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',

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
                                    'Shift' => $Shift,
                                    'Date' => $Date,
                                    'Job_Card_No' => $Job_Card_No,
                                    'Work_Type' => 'Others',
                                    'Status_Updated' => 'Others',
                                    'Department' => $Department,
                                    'Sub_Department' => $Sub_Department,
                                    'Sub_Section' => $Sub_Section,
                                    'Screen_Type' => $Allocation_Screen_Type,
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                // echo '<pre>';
                                // print_r($Work_Allocation);
                                // exit;

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Multiple Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                $this->delete_existing_allocation($Shift, $Date, $Employee_Id);

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            } elseif ($Frame_Data == 'Trainee') {

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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

                        if ($Sub_Department == 'Finishing - PM1' || $Sub_Department == 'Finishing - PM2' || $Sub_Department == 'Preparatory - PM1' || $Sub_Department == 'Preparatory - PM2' || $Sub_Department == 'Spinning - PM1' || $Sub_Department == 'Spinning - PM2') {

                            foreach ($Machine_Id as $index => $Machine_datas) {

                                $Duplicate = $Frames[$index] ?? null;
                                $Frame_Data = $Frames[0] ?: $Duplicate;

                                if ($Frame_Data == 'Machine Wise') {

                                    $Frame_Data = 'Machine Wise';

                                    $machine_data = $this->db->query("
            SELECT Machine_Name, Machine_Model 
            FROM Web_Machine_Mst 
            WHERE CCode = '$CompanyCode' 
              AND LCode = '$LocationCode' 
              AND WorkArea = '$Work_Area' 
              AND Machine_Id = '$Machine_datas'
        ")->result();

                                    $Machine_Name = $machine_data[0]->Machine_Name ?? '-';
                                    $Machine_Model = $machine_data[0]->Machine_Model ?? '-';

                                    $Work_Allocation = [
                                        'Ccode'            => $CompanyCode,
                                        'Lcode'            => $LocationCode,
                                        'Wages'            => $Wages,
                                        'Machine_Id'       => $Machine_datas,
                                        'Machine_Name'     => $Machine_Name,
                                        'Machine_Model'    => $Machine_Model,
                                        'FirstName'        => $Employee_Name,
                                        'EmpNo'            => $Employee_Id,
                                        'ExistingCode'     => $ExistingCode,
                                        'Shift'            => $Shift,
                                        'Date'             => $Date,
                                        'Job_Card_No'      => $Job_Card_No,
                                        'Work_Type'        => 'Machine',
                                        'Status_Updated'   => 'Machine',
                                        'Department'       => $Department,
                                        'Sub_Department'   => $Sub_Department,
                                        'Sub_Section'      => $Sub_Section,
                                        'Previous_Shift'   => '-',
                                        'Screen_Type'      => $Allocation_Screen_Type,
                                        'OT_Confirmation'  => '-',
                                        'WorkArea'         => $Work_Area,
                                        'Frame'            => $Frame_Data,     // M1, M2, ...
                                        'FrameType'        => '',
                                        'Description'      => $Description,
                                        'Type'             => $Allocation_Type,
                                        'Work_Status'      => '1',
                                        'Assign_Status'    => '1',
                                        'Closing_Status'   => '0',
                                        'IsWork'           => '0',
                                        'Created_By'       => $Session['UserName'],
                                        'Created_Time'     => date('Y-m-d H:i:s'),
                                        'Updated_By'       => '-',
                                        'Updated_Time'     => '-',
                                    ];

                                    // Optional: Insert or show for testing


                                    $this->delete_existing_allocation($Shift, $Date, $Employee_Id);
                                    if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                        $allocation_details[] = ['status' => 'success', 'message' => 'Work allocation assigned successfully'];
                                    } else {
                                        $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning work allocation'];
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
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
                }
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    private function delete_existing_allocation($Shift, $Date, $Employee_Id)
    {
        $sql_check_existing = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE  Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1' ";
        $Query = $this->db->query($sql_check_existing);

        // print_r($sql_check_existing);exit;

        if ($Query->num_rows() > 0) {
            // Delete existing allocation
            $sql_delete = "DELETE FROM Web_Extra_Work_Allocation_Mst WHERE Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1'";
            $this->db->query($sql_delete);
        }
    }

    public function Edit($input_data, $CompanyCode, $LocationCode)
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {


            if($LocationCode == 'PRECOT - A'){

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


                $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                $Query_Job = $this->db->query($sql_Job);
                $Result_Job = $Query_Job->result();

                $Job_Card_No =  $Result_Job[0]->JobCard_No;

                $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages, DeptName, DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                if (empty($Employee_Data)) {
                    $success = false;
                    $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                }

                $Employee_Name = $Employee_Data[0]->FirstName;
                $ExistingCode = $Employee_Data[0]->ExistingCode;
                $Wages = $Employee_Data[0]->wages;
                $Department = $Employee_Data[0]->DeptGrp;
                $Sub_Section = $Employee_Data[0]->SubSection_Name;


                if ($Machine_Id == [''] || $Machine_Id == []) {

                    foreach ($Frames as $Frame_Data) {

                        if ($Frame_Data == 'NoWork') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Hours_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Others') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Multiple Trainee') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }
                } else {

                   if ($Sub_Department === 'Spinning-Prod' || $Sub_Department === 'Finishing-Prod' ||  $Sub_Department === 'Preparatory-Prod' || $Sub_Department == 'SPINNING-PROD' || $Sub_Department == 'FINISHING-PROD'  || $Sub_Department === 'PREPARATORY-PROD') {


                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


                            if ($Frame_Data == 'Machine Wise') {

                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = ''")->result();
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            } else {



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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {



                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


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
                                'WorkArea' => $Work_Area,
                                'Previous_Shift' => '-',
                                'Screen_Type' => $Allocation_Screen_Type,
                                'OT_Confirmation' => '-',
                                'Frame' => $Frame_Data,
                                'FrameType' => '',
                                'Description' => $Description,
                                'Type' => $Allocation_Type,
                                'Work_Status' => '1',
                                'Assign_Status' => '1',
                                'Closing_Status' => '0',
                                'IsWork' => '0',
                                'Created_By' => $Session['UserName'],
                                'Created_Time' => date('Y-m-d H:i:s'),
                                'Updated_By' => '-',
                                'Updated_Time' => '-',
                            ];




                            if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                            } else {
                                $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                $success = false;
                            }
                        }
                    }
                }
            }
            return $allocation_details;



        }  else if($LocationCode == 'PRECOT - C'){

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


                $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                $Query_Job = $this->db->query($sql_Job);
                $Result_Job = $Query_Job->result();

                $Job_Card_No =  $Result_Job[0]->JobCard_No;

                $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages, DeptName, DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                if (empty($Employee_Data)) {
                    $success = false;
                    $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                }

                $Employee_Name = $Employee_Data[0]->FirstName;
                $ExistingCode = $Employee_Data[0]->ExistingCode;
                $Wages = $Employee_Data[0]->wages;
                $Department = $Employee_Data[0]->DeptGrp;
                $Sub_Section = $Employee_Data[0]->SubSection_Name;


                if ($Machine_Id == [''] || $Machine_Id == []) {

                    foreach ($Frames as $Frame_Data) {

                        if ($Frame_Data == 'NoWork') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Hours_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Others') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Multiple Trainee') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }
                } else {

                        if ($Sub_Department === 'Spinning' || $Sub_Department === 'Finishing' ||  $Sub_Department === 'Preparatory' || $Sub_Department == 'SPINNING' || $Sub_Department == 'FINISHING'  || $Sub_Department === 'PREPARATORY') {


                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


                            if ($Frame_Data == 'Machine Wise') {

                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = ''")->result();
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            } else {



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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {



                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


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
                                'WorkArea' => $Work_Area,
                                'Previous_Shift' => '-',
                                'Screen_Type' => $Allocation_Screen_Type,
                                'OT_Confirmation' => '-',
                                'Frame' => $Frame_Data,
                                'FrameType' => '',
                                'Description' => $Description,
                                'Type' => $Allocation_Type,
                                'Work_Status' => '1',
                                'Assign_Status' => '1',
                                'Closing_Status' => '0',
                                'IsWork' => '0',
                                'Created_By' => $Session['UserName'],
                                'Created_Time' => date('Y-m-d H:i:s'),
                                'Updated_By' => '-',
                                'Updated_Time' => '-',
                            ];




                            if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                            } else {
                                $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                $success = false;
                            }
                        }
                    }
                }
            }
            return $allocation_details;



        }  else if($LocationCode == 'PRECOT - D'){

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


                $sql_Job = "SELECT * FROM Web_JobCard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Department = '$Sub_Department'  AND WorkArea = '$Work_Area'";
                $Query_Job = $this->db->query($sql_Job);
                $Result_Job = $Query_Job->result();

                $Job_Card_No =  $Result_Job[0]->JobCard_No;

                $Employee_Data = $this->db->query("SELECT FirstName, ExistingCode, wages, DeptName, DeptGrp,SubSection_Name FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ExistingCode = '$Employee_Id'")->result();
                if (empty($Employee_Data)) {
                    $success = false;
                    $allocation_details[] = ['status' => 'error', 'message' => "Employee $Employee_Id not found"];
                }

                $Employee_Name = $Employee_Data[0]->FirstName;
                $ExistingCode = $Employee_Data[0]->ExistingCode;
                $Wages = $Employee_Data[0]->wages;
                $Department = $Employee_Data[0]->DeptGrp;
                $Sub_Section = $Employee_Data[0]->SubSection_Name;


                if ($Machine_Id == [''] || $Machine_Id == []) {

                    foreach ($Frames as $Frame_Data) {

                        if ($Frame_Data == 'NoWork') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Hours_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Others') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Multiple Trainee') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date'  AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                                $Updated_Query = $this->db->query($Update);

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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];

                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        }
                    }
                } else {

                        if ($Sub_Department == 'TFO' || $Sub_Department == 'TFO' ||  $Sub_Department == 'DOUBLING' || $Sub_Department == 'Doubling' || $Sub_Department == 'GASSING'  || $Sub_Department == 'Gassing' || $Sub_Department == 'WINDING' || $Sub_Department == 'Winding') {


                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


                            if ($Frame_Data == 'Machine Wise') {

                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = ''")->result();
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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            } else {



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
                                    'Previous_Shift' => '-',
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
                                    'Created_By' => $Session['UserName'],
                                    'Created_Time' => date('Y-m-d H:i:s'),
                                    'Updated_By' => '-',
                                    'Updated_Time' => '-',
                                ];




                                if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {



                        $Update = "UPDATE Web_Extra_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date'  AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


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
                                'WorkArea' => $Work_Area,
                                'Previous_Shift' => '-',
                                'Screen_Type' => $Allocation_Screen_Type,
                                'OT_Confirmation' => '-',
                                'Frame' => $Frame_Data,
                                'FrameType' => '',
                                'Description' => $Description,
                                'Type' => $Allocation_Type,
                                'Work_Status' => '1',
                                'Assign_Status' => '1',
                                'Closing_Status' => '0',
                                'IsWork' => '0',
                                'Created_By' => $Session['UserName'],
                                'Created_Time' => date('Y-m-d H:i:s'),
                                'Updated_By' => '-',
                                'Updated_Time' => '-',
                            ];




                            if ($this->db->insert('Web_Extra_Work_Allocation_Mst', $Work_Allocation)) {
                                $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                            } else {
                                $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                $success = false;
                            }
                        }
                    }
                }
            }
            return $allocation_details;
        } 

    }


    }

    // public function Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date)
    // {

    //     $All_Employee_Close_Check_Sql = "SELECT DISTINCT Work.EmpNo,Work.Sub_Department,Work.WorkArea,Work.Job_Card_No,Work.EmpNo,Work.FirstName FROM Web_Extra_Work_Allocation_Mst Work
    //                                       INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
    //                                      AND Login.Ccode = Work.Ccode
    //                                      AND Login.Name = Work.Sub_Department
    //                                      WHERE login.UserID = '$Login_User'
    //                                      AND Work.Date = '$Date'
    //                                      AND Work.WorK_Status = '1'
    //                                       AND Work.Closing_Status = '0'";

    //     $All_Employee_Close_Check_Query = $this->db->query($All_Employee_Close_Check_Sql);
    //     $All_Employee_Close_Check_Result  = $All_Employee_Close_Check_Query->result();

    //     //  print_r($All_Employee_Close_Check_Sql);exit;

    //     if ($All_Employee_Close_Check_Query->num_rows() > 0) {

    //         return $All_Employee_Close_Check_Result;
    //     } else {
    //         return $allocation_details = [
    //             'status' => 'error',
    //             'message' => 'Work Allocation Details Not Found!!'
    //         ];
    //     }
    // }



       public function Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date)
    {

        $All_Employee_Close_Check_Sql = "SELECT DISTINCT Work.EmpNo,Work.Sub_Department,Work.WorkArea,Work.Job_Card_No,Work.EmpNo,Work.FirstName FROM Web_Extra_Work_Allocation_Mst Work
                                          INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                         AND Login.Ccode = Work.Ccode
                                         AND Login.Name = Work.Sub_Department
                                         WHERE login.UserID = '$Login_User'
                                         AND Work.Date = '$Date'
                                         AND Work.WorK_Status = '1'
                                          AND Work.Closing_Status = '0'
                                          AND Work.Lcode = '$LocationCode'
                                          AND Work.Ccode = '$CompanyCode'";

        $All_Employee_Close_Check_Query = $this->db->query($All_Employee_Close_Check_Sql);
        $All_Employee_Close_Check_Result  = $All_Employee_Close_Check_Query->result();

        //  print_r($All_Employee_Close_Check_Sql);exit;

        if ($All_Employee_Close_Check_Query->num_rows() > 0) {

            return $All_Employee_Close_Check_Result;
        } else {
            return $allocation_details = [
                'status' => 'error',
                'message' => 'Work Allocation Details Not Found!!'
            ];
        }
    }


    public function Employee_Shift_Closings($inputData)
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            $CompanyCode = $Session['Ccode'];
            $LocationCode = $Session['Lcode'];
            $Login_User = $Session['UserName'];

            foreach ($inputData['Employees'] as $Details) {

                $Employee_Id = $Details['EmployeeId'];
                $Sub_Department = $Details['Department'];
                $WorkArea = $Details['Work_Area'];
                $jobCardNo = $Details['JobCardNo'];
                $OTStatus = $Details['OTConfirm'];
                $Supervisor_Name = $Details['Supervisor_Name'];
                $Date = $Details['Date'];

                if ($OTStatus == 1) {

                    $this->db->select('*');
                    $this->db->from('Web_Extra_Work_Allocation_Mst');
                    $this->db->where([
                        'Date' => $Date,
                        'EmpNo' => $Employee_Id,
                        'Sub_Department' => $Sub_Department,
                        'WorkArea' => $WorkArea,
                        'Job_Card_No' => $jobCardNo
                    ]);
                    $query = $this->db->get();
                    $Employee_Work_Data = $query->result();

                    if (!empty($Employee_Work_Data)) {
                        // Update the status of existing records
                        $this->db->where([
                            'EmpNo' => $Employee_Id,
                            'Sub_Department' => $Sub_Department,
                            'Date' => $Date,
                            'Work_Status' => '1',
                            'Ccode' => $CompanyCode,
                            'Lcode' => $LocationCode
                        ]);
                        $this->db->update('Web_Extra_Work_Allocation_Mst', [
                            'Status_Updated' => 'Closed',
                            'Supervisor_Name' => $Supervisor_Name,
                            'Closing_Status' => '1',
                            'Updated_By' => $Login_User,
                            'Updated_Time' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            // Check if any rows were affected
            if ($this->db->affected_rows() > 0) {
                return 1; // Success
            } else {
                return 0; // No rows affected
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }


//    public function Get_OT_Extra_Hours_List_Employee($CompanyCode, $LocationCode, $Login_User, $Date, $Type)
// {
//     if ($Type == 'EXTRA') {

//         function convertHoursMinutesToDecimal($timeStr)
//         {
//             if ($timeStr == 'Invalid Time' || empty($timeStr)) {
//                 return '0';
//             }

//             list($hours, $minutes) = explode(':', $timeStr);
//             $hours = intval($hours);
//             $minutes = intval($minutes);

//             if ($minutes == 0) {
//                 return (string)$hours;
//             } elseif ($minutes >= 15 && $minutes <= 44) {
//                 return number_format($hours + 0.5, 1);
//             } elseif ($minutes >= 45) {
//                 return (string)($hours + 1);
//             } else {
//                 return (string)$hours;
//             }
//         }


//         $current_Date = $Date; // Safe fallback for use in subqueries

//         $Sql = "WITH PunchTimes AS (
//                 SELECT
//                     Work.EmpNo,
//                     Work.WorkArea,
//                     TRY_CONVERT(DATETIME, Work.Updated_Time, 120) AS Updated_Time,
//                     Work.Closing_Status,
//                     Work.FirstName,
//                     Time.TimeOUT,
//                     ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT ASC) AS RowAsc,
//                     ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT DESC) AS RowDesc
//                 FROM
//                     Web_Extra_Work_Allocation_Mst Work
//                 INNER JOIN
//                     UserDetails_Det Login ON Login.Lcode = Work.Lcode
//                         AND Login.Ccode = Work.Ccode
//                         AND Login.Name = Work.Sub_Department
//                 INNER JOIN
//                     LogTimeLunch_OUT AS Time ON Time.MachineID = Work.EmpNo
//                         AND TRY_CONVERT(DATE, Time.TimeOUT, 120) = '$Date'
//                 WHERE
//                     Login.UserID = '$Login_User'
//                     AND TRY_CONVERT(DATE, Work.Date, 120) = '$Date'
//                     AND Work.Ccode = '$CompanyCode'
//                     AND Work.Lcode = '$LocationCode'
//                     AND Work.Work_Status = '1'
//                     AND Work.Assign_Status = '1'
//                     AND (Work.Closing_Status = '1' OR Work.Closing_Status = '0')
//             ),
//             OnDutyStatus AS (
//                 SELECT DISTINCT TokenNo, 1 AS Updated_Status
//                 FROM OnDuty_Mst
//                 WHERE TRY_CONVERT(DATE, Entry_Date, 120) = '$current_Date'
//             )

//             SELECT
//                 p.EmpNo,
//                 p.WorkArea,
//                 CONVERT(VARCHAR(5), p.Updated_Time, 108) AS Updated_Time,
//                 p.Closing_Status,
//                 p.FirstName,
//                 CONVERT(VARCHAR(5), MIN(p.TimeOUT), 108) AS IN_Time,
//                 CONVERT(VARCHAR(5), MAX(p.TimeOUT), 108) AS OUT_Time,
//                 CASE
//                     WHEN MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END) IS NOT NULL
//                          AND MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END) IS NOT NULL
//                     THEN
//                         CONVERT(VARCHAR(5),
//                             DATEDIFF(SECOND,
//                                 MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END),
//                                 MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END)) / 3600
//                         ) + ':' +
//                         RIGHT('0' + CONVERT(VARCHAR(2),
//                             (DATEDIFF(SECOND,
//                                 MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END),
//                                 MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END)) % 3600) / 60
//                         ), 2)
//                     ELSE 'Invalid Time'
//                 END AS TotalWorkingHours,
//                 ISNULL(ods.Updated_Status, 0) AS Updated_Status,
//                 DATEDIFF(MINUTE, MAX(p.Updated_Time), MAX(p.TimeOUT)) AS OUT_Updated_Diff
//             FROM
//                 PunchTimes p
//             LEFT JOIN
//                 OnDutyStatus ods ON p.EmpNo = ods.TokenNo
//             GROUP BY
//                 p.EmpNo, p.WorkArea, p.Updated_Time, p.Closing_Status, p.FirstName, ods.Updated_Status;
//         ";

//         $Query = $this->db->query($Sql);

//         if ($Query->num_rows() > 0) {

//             $Result = [];

//             foreach ($Query->result() as $Row) {

//                 $extraHoursDecimal = convertHoursMinutesToDecimal($Row->TotalWorkingHours);
//                 $Employee_ID = $Row->EmpNo;

//                 // Check if already exists in OnDuty_Mst with Input = E-Master
//                 $Sql_Verify = "SELECT * FROM OnDuty_Mst 
//                                WHERE CompCode = '$CompanyCode' 
//                                  AND LocCode = '$LocationCode' 
//                                  AND TokenNo = '$Employee_ID' 
//                                  AND Entry_Date = '$Date' 
//                                  AND Input = 'E-Master'";
//                 $Query_Verify = $this->db->query($Sql_Verify);
//                 $Entry_Status = ($Query_Verify->num_rows() > 0) ? '1' : '0';

//                 $Result[] = [
//                     'Employee_ID'       => $Row->EmpNo,
//                     'Employee_Name'     => $Row->FirstName,
//                     'IN_Time'           => $Row->IN_Time ?? '',
//                     'OUT_Time'          => $Row->OUT_Time ?? '',
//                     'Extra_Hours'       => $extraHoursDecimal,
//                     'Closing_Status'    => $Row->Closing_Status,
//                     'Updated_Time'      => $Row->Updated_Time,
//                     'OUT_Updated_Diff'  => $Row->OUT_Updated_Diff ?? 0,
//                     'Entry_Status'      => $Entry_Status
//                 ];
//             }

//             return $Result;
//         } else {
//             return 0;
//         }
//     }
// }


public function Get_OT_Extra_Hours_List_Employee($CompanyCode, $LocationCode, $Login_User, $Date, $Type)
{
    if ($Type != 'EXTRA') return 0;

    // Helper function to convert HH:MM to decimal hours
    function convertHoursMinutesToDecimal($timeStr)
    {
        if ($timeStr == 'Invalid Time' || empty($timeStr)) return '0';

        list($hours, $minutes) = explode(':', $timeStr);
        $hours = intval($hours);
        $minutes = intval($minutes);

        if ($minutes == 0) {
            return (string)$hours;
        } elseif ($minutes >= 15 && $minutes <= 44) {
            return number_format($hours + 0.5, 1);
        } elseif ($minutes >= 45) {
            return (string)($hours + 1);
        } else {
            return (string)$hours;
        }
    }

    $Sql = "WITH FirstIN AS (
            SELECT MachineID AS EmpNo, MIN(TimeIN) AS FirstPunch
            FROM LogTime_IN
            WHERE TRY_CONVERT(DATE, TimeIN, 120) = '$Date'
            GROUP BY MachineID
        ),
        LastOUT AS (
            SELECT MachineID AS EmpNo, MAX(TimeOUT) AS LastPunch
            FROM LogTime_OUT
            WHERE TRY_CONVERT(DATE, TimeOUT, 120) = '$Date'
            GROUP BY MachineID
        ),
        WorkEmployees AS (
            SELECT
                Work.EmpNo,
                Work.WorkArea,
                TRY_CONVERT(DATETIME, Work.Updated_Time, 120) AS Updated_Time,
                Work.Closing_Status,
                Work.FirstName
            FROM Web_Extra_Work_Allocation_Mst Work
            INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                AND Login.Ccode = Work.Ccode
                AND Login.Name = Work.Sub_Department
            WHERE
                Login.UserID = '$Login_User'
                AND TRY_CONVERT(DATE, Work.Date, 120) = '$Date'
                AND Work.Ccode = '$CompanyCode'
                AND Work.Lcode = '$LocationCode'
                AND Work.Work_Status = '1'
                AND Work.Assign_Status = '1'
                AND Work.Closing_Status IN ('0', '1')
        ),
        OnDutyStatus AS (
            SELECT DISTINCT TokenNo, 1 AS Updated_Status
            FROM OnDuty_Mst
            WHERE TRY_CONVERT(DATE, Entry_Date, 120) = '$Date'
        )

        SELECT
            w.EmpNo,
            w.WorkArea,
            CONVERT(VARCHAR(5), w.Updated_Time, 108) AS Updated_Time,
            w.Closing_Status,
            w.FirstName,
            CONVERT(VARCHAR(5), f.FirstPunch, 108) AS IN_Time,
            CONVERT(VARCHAR(5), o.LastPunch, 108) AS OUT_Time,
            CASE
                WHEN f.FirstPunch IS NOT NULL AND o.LastPunch IS NOT NULL THEN
                    CONVERT(VARCHAR(5), DATEDIFF(SECOND, f.FirstPunch, o.LastPunch) / 3600) + ':' +
                    RIGHT('0' + CONVERT(VARCHAR(2), (DATEDIFF(SECOND, f.FirstPunch, o.LastPunch) % 3600) / 60), 2)
                ELSE 'Invalid Time'
            END AS TotalWorkingHours,
            ISNULL(ods.Updated_Status, 0) AS Updated_Status,
            ISNULL(DATEDIFF(MINUTE, w.Updated_Time, o.LastPunch), 0) AS OUT_Updated_Diff
        FROM
            WorkEmployees w
        LEFT JOIN FirstIN f ON w.EmpNo = f.EmpNo
        LEFT JOIN LastOUT o ON w.EmpNo = o.EmpNo
        LEFT JOIN OnDutyStatus ods ON w.EmpNo = ods.TokenNo
    ";

    $Query = $this->db->query($Sql);

    if ($Query->num_rows() <= 0) return 0;

    $Result = [];

    foreach ($Query->result() as $Row) {
        $extraHoursDecimal = convertHoursMinutesToDecimal($Row->TotalWorkingHours);
        $Employee_ID = $Row->EmpNo;

        // Check if already exists in OnDuty_Mst with Input = E-Master
        $Sql_Verify = "
            SELECT 1 FROM OnDuty_Mst 
            WHERE CompCode = '$CompanyCode' 
              AND LocCode = '$LocationCode' 
              AND TokenNo = '$Employee_ID' 
              AND Entry_Date = '$Date' 
              AND Input = 'E-Master'
        ";
        $Query_Verify = $this->db->query($Sql_Verify);
        $Entry_Status = ($Query_Verify->num_rows() > 0) ? '1' : '0';

        $Result[] = [
            'Employee_ID'       => $Row->EmpNo,
            'Employee_Name'     => $Row->FirstName,
            'IN_Time'           => $Row->IN_Time ?? '',
            'OUT_Time'          => $Row->OUT_Time ?? '',
            'Extra_Hours'       => $extraHoursDecimal,
            'Closing_Status'    => $Row->Closing_Status,
            'Updated_Time'      => $Row->Updated_Time,
            'OUT_Updated_Diff'  => $Row->OUT_Updated_Diff ?? 0,
            'Entry_Status'      => $Entry_Status
        ];
    }

    return $Result;
}






    public function OT_Extra_Hours_Entry($CompanyCode, $LocationCode, $Login_User, $Input_data)
    {
        $Employee_Data = $Input_data['Employees'];

        foreach ($Employee_Data as $Ot_Details) {
            $Employee_ID = $Ot_Details['EmpNo'];
            $Employee_Name = $Ot_Details['Employee_Name'];
            $FirstPunchIn = $Ot_Details['FirstPunchIn'];
            $LastPunchOut = $Ot_Details['LastPunchOut'];
            $EMaster_Time = $Ot_Details['EMaster_Time'];
            $TotalWorking_Hours = $Ot_Details['TotalWorking_Hours'];
            $Extra_Hours = (int)$Ot_Details['Extra_Hours'];
            $Date = $Ot_Details['Date'];
            $Supervisor_ID = $Ot_Details['Supervisor_Name'];
            $Date_Convertion = date('d/m/Y', strtotime($Date));

            $current_Date = date('Y/m/d');
            $Current_Date_Time = date('Y-m-d H:i:s');

            // Check if Supervisor exists
            $Sql_Sup = "SELECT FirstName FROM Employee_Mst WHERE LocCode = '$LocationCode' AND CompCode = '$CompanyCode' AND EmpNo = '$Supervisor_ID' AND IsActive = 'Yes'";
            $Query_Sup = $this->db->query($Sql_Sup);
            $Result_Sup = $Query_Sup->row();

            if ($Result_Sup) {
                $Supervisor_Name = $Result_Sup->FirstName;

                // Check for duplicate entry
                $Sql_Duplicate = "SELECT * FROM OnDuty_Mst WHERE LocCode= '$LocationCode' AND CompCode = '$CompanyCode' AND TokenNo = '$Employee_ID' AND Entry_Date = '$Date'";
                $Query_Duplicated = $this->db->query($Sql_Duplicate);

                if ($Query_Duplicated->num_rows() == 0) {

                    $Extra_Hours_Entry = [
                        'CompCode' => $CompanyCode,
                        'LocCode' => $LocationCode,
                        'TransID' => '-',
                        'TokenNo' => $Employee_ID,
                        'EmpName' => $Employee_Name,
                        'ONDutyFromDate' => $Date_Convertion,
                        'ONDutyToDate' => $Date_Convertion,
                        'Days' => 1,
                        'FromArea' => NULL,
                        'ToArea' => NULL,
                        'Reason' => 'Extra Hours',
                        'VechileNo' => NULL,
                        'TravelCharge' => NULL,
                        'ONDutyReturnDate' => NULL,
                        'Status' => 0,
                        'FDate' => $FirstPunchIn,
                        'TDate' => $LastPunchOut,
                        'Applied_on' => $current_Date,
                        'Total_hrs' => $Extra_Hours,
                        'WorkType' => 'Extra Hours',
                        'Session' => 'Both',
                        'Input' => 'E-Master',
                        'Supervisor_ID' => $Supervisor_ID,
                        'Supervisor_Name' => $Supervisor_Name,
                        'Entry_Date' => $Date,
                        'Created_By' => $Login_User,
                        'Created_Time' => $Current_Date_Time,
                        'Approved_By' => '-',
                        'Approved_Time' => '-'
                    ];

                    $this->db->insert('OnDuty_Mst', $Extra_Hours_Entry);
                } else {

                    return 0;
                }
            }
        }
        return 1;
    }


    // public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    // {
    //     // Get initial shift employees (closed and assigned)
    //     $Sql_First = "SELECT
    //                     Work.EmpNo,
    //                     Work.Closing_Status,
    //                     Work.FirstName,
    //                     Work.Updated_Time
    //                 FROM Web_Employee_Work_Allocation_Mst AS Work
    //                 INNER JOIN UserDetails_Det AS Login
    //                     ON Login.Lcode = Work.Lcode
    //                     AND Login.Name = Work.Sub_Department
    //                 WHERE
    //                     Login.Ccode = '$CompanyCode'
    //                     AND Login.Lcode = '$LocationCode'
    //                     AND Login.UserID = '$Login_User'
    //                     AND Work.Date = '$Date'
    //                     AND Work.Shift = '$Shift'
    //                     AND Work.Work_Status = '1'
    //                     AND Work.Assign_Status = '1'
    //                     AND Work.Closing_Status = '1'";

    //     $Query_First = $this->db->query($Sql_First);

    //     if ($Query_First->num_rows() <= 0) {
    //         return 0;
    //     }

    //     // Determine next shift
    //     $Next_Shift = ($Shift == 'SHIFT1') ? 'SHIFT2' : (($Shift == 'SHIFT2') ? 'SHIFT3' : 'SHIFT3');

    //     // Get next shift employees
    //     $Sql_Second = "SELECT
    //                     Work.EmpNo,
    //                     Work.Closing_Status,
    //                     Work.FirstName,
    //                     Work.Updated_Time
    //                 FROM Web_Employee_Work_Allocation_Mst AS Work
    //                 INNER JOIN UserDetails_Det AS Login
    //                     ON Login.Lcode = Work.Lcode
    //                     AND Login.Name = Work.Sub_Department
    //                 WHERE
    //                     Login.Ccode = '$CompanyCode'
    //                     AND Login.Lcode = '$LocationCode'
    //                     AND Login.UserID = '$Login_User'
    //                     AND Work.Date = '$Date'
    //                     AND Work.Shift = '$Next_Shift'
    //                     AND Work.Work_Status = '1'
    //                     AND Work.Assign_Status = '1'
    //                     AND Work.Closing_Status = '1'";

    //     $Query_Second = $this->db->query($Sql_Second);

    //     if ($Query_Second->num_rows() <= 0) {
    //         return 0;
    //     }

    //     $Shift_Date_Convert = $Date;



    //     // Only needed for SHIFT1
    //     if ($Shift == 'SHIFT1') {

    //         print_r('coming');

    //         $Shift_First = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = '$Shift'")->row();

    //         $Shift_Second = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = 'SHIFT2'")->row();

    //     $Shift_Date_Conversion_First = $Date;
    //     $Shift_Date_Conversion_Second = $Date;


    //     } else if($Shift == 'SHIFT2'){

    //          $Shift_First = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = '$Shift'")->row();

    //         $Shift_Second = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = 'SHIFT3'")->row();

    //     $Shift_Date_Conversion_First = $Date;
    //     $Shift_Date_Conversion_Second = date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'));



    //     } else if($Shift == 'SHIFT3'){

    //          $Shift_First = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = '$Shift'")->row();

    //         $Shift_Second = $this->db->query("SELECT * FROM Shift_Mst 
    //                                             WHERE CompCode = '$CompanyCode' 
    //                                               AND LocCode = '$LocationCode' 
    //                                               AND ShiftDesc = 'SHIFT1'")->row();

    //         $Shift_Date_Conversion_First = $Date;
    //     $Shift_Date_Conversion_Second = $Date;



    //     }   

    //     // Get Shift Master Data (for OT calculation)
    //     $Shift_Data = $this->db->query("SELECT * FROM Shift_Mst 
    //                                     WHERE CompCode = '$CompanyCode' 
    //                                       AND LocCode = '$LocationCode' 
    //                                       AND ShiftDesc = '$Shift'")->row();

    //     $Details = $Query_Second->result();
    //     $Data = [];
    //     $serial = 1;

    //     foreach ($Details as $Detail) {
    //         $Employee_ID = $Detail->EmpNo;
    //         $Partial_Close_Time = $Detail->Updated_Time;

    //         // Format updated time
    //         $Partial_Close_Time_Final = '';
    //         if (!empty($Partial_Close_Time) && $Partial_Close_Time !== '-') {
    //             try {
    //                 $time = new DateTime($Partial_Close_Time);
    //                 $Partial_Close_Time_Final = $time->format('h:i A');
    //             } catch (Exception $e) {
    //                 $Partial_Close_Time_Final = '';
    //             }
    //         }

    //              print_r('not-coming');

    //         // Fetch Punch IN and OUT
    //         $Sql_Punch_IN = "SELECT FORMAT(MIN(TimeIN), 'HH:mm') AS TimeIN
    //                          FROM LogTime_IN
    //                          WHERE CompCode = '$CompanyCode'
    //                            AND LocCode = '$LocationCode'
    //                            AND MachineID = '$Employee_ID'
    //                            AND CAST(TimeIN AS DATE) = '$Shift_Date_Conversion_First'";
    //         $Sql_Punch_OUT = "SELECT FORMAT(MAX(TimeOUT), 'HH:mm') AS TimeOUT
    //                           FROM LogTime_OUT
    //                           WHERE CompCode = '$CompanyCode'
    //                             AND LocCode = '$LocationCode'
    //                             AND MachineID = '$Employee_ID'
    //                             AND CAST(TimeOUT AS DATE) = '$Shift_Date_Conversion_Second'";

    //         $PunchIn = $this->db->query($Sql_Punch_IN)->row();
    //         $PunchOut = $this->db->query($Sql_Punch_OUT)->row();

    //                         echo '<pre>';
    //                 print_r($Sql_Punch_IN);
    //                 echo '<pre>';
    //                 print_r($Sql_Punch_OUT);

    //         $TimeIN = '';
    //         $TimeOUT = '';

    //         if ($PunchIn && $PunchIn->TimeIN) {
    //             $inObj = DateTime::createFromFormat('H:i', $PunchIn->TimeIN);
    //             $TimeIN = $inObj ? $inObj->format('h:i A') : '';
    //         }

    //         if ($PunchOut && $PunchOut->TimeOUT) {
    //             $outObj = DateTime::createFromFormat('H:i', $PunchOut->TimeOUT);
    //             $TimeOUT = $outObj ? $outObj->format('h:i A') : '';
    //         }

    //         // Calculate Working Hours
    //         $WorkingHours = 0;
    //         $TotalWorkingFormatted = '';
    //         $minutes = 0;

    //         if ($TimeIN && $TimeOUT) {
    //             try {
    //                 $in = new DateTime($PunchIn->TimeIN);
    //                 $out = new DateTime($PunchOut->TimeOUT);
    //                 if ($out < $in) $out->modify('+1 day');
    //                 $diff = $in->diff($out);
    //                 $minutes = $diff->h * 60 + $diff->i;
    //                 $WorkingHours = ($diff->i >= 30) ? $diff->h + 1 : $diff->h;
    //                 $TotalWorkingFormatted = sprintf('%02d:%02d', $diff->h, $diff->i);
    //             } catch (Exception $e) {}
    //         }

    //         // Time difference between Updated and OUT time
    //         $UpdatedTime = (!empty($Partial_Close_Time) && $Partial_Close_Time !== '-') ? substr($Partial_Close_Time, 11, 5) : '';
    //         $Difference = '';
    //         $Difference_Status = 3;

    //         if ($UpdatedTime && $PunchOut && $PunchOut->TimeOUT) {
    //             try {
    //                 $updated = new DateTime($UpdatedTime);
    //                 $last = new DateTime($PunchOut->TimeOUT);
    //                 $interval = $updated->diff($last);
    //                 $totalMin = abs($interval->h * 60 + $interval->i);
    //                 $hours = floor($totalMin / 60);
    //                 $mins = $totalMin % 60;
    //                 $Difference = sprintf('%02d:%02d', $hours, $mins);
    //                 $Difference_Status = ($totalMin >= 45) ? 1 : 0;
    //             } catch (Exception $e) {}
    //         }

    //         // OT Hour Calculation
    //         $OTHours = 0;
    //         $OTFormatted = '';
    //         $RoundedOT = 0;

    //         if ($TimeIN && $TimeOUT && $Shift_Data) {
    //             try {
    //                 $ShiftStart = new DateTime($Shift_Data->StartTime);
    //                 $ShiftEnd = new DateTime($Shift_Data->EndTime);
    //                 if ($ShiftEnd < $ShiftStart) $ShiftEnd->modify('+1 day');
    //                 $shiftDuration = $ShiftStart->diff($ShiftEnd);
    //                 $shiftMinutes = $shiftDuration->h * 60 + $shiftDuration->i;

    //                 $OT_Minutes = max(0, $minutes - $shiftMinutes);
    //                 $OTHours = floor($OT_Minutes / 60);
    //                 $OTMins = $OT_Minutes % 60;
    //                 $OTFormatted = sprintf('%02d:%02d', $OTHours, $OTMins);

    //                 // Rounding logic
    //                 if ($OTMins >= 31) {
    //                     $RoundedOT = $OTHours + 1;
    //                 } elseif ($OTMins >= 16) {
    //                     $RoundedOT = $OTHours + 0.5;
    //                 } else {
    //                     $RoundedOT = $OTHours;
    //                 }
    //             } catch (Exception $e) {}
    //         }

    //         $ClosingStatus = ($Detail->Closing_Status == '1') ? 'Closed' : 'Not Closed';

    //         $Data[] = [
    //             'EmpNo'             => $Detail->EmpNo,
    //             'Employee_Name'     => $Detail->FirstName,
    //             'Status'            => $ClosingStatus,
    //             'IN_Time'           => $TimeIN,
    //             'OUT_Time'          => $TimeOUT,
    //             'Updated_Time'      => $Partial_Close_Time_Final,
    //             'W_Hours'           => $TotalWorkingFormatted,
    //             'OT_Hours'          => $OTFormatted,
    //             'OT_Hours_Actual'   => $RoundedOT,
    //             'Difference'        => $Difference,
    //             'Difference_Status' => $Difference_Status,
    //             'Verify'            => $WorkingHours
    //         ];

    //         $serial++;
    //     }

    //     return $Data;
    // }



    // public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    // {
    //     $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

    //     $sql = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department,Work.Updated_Time
    //         FROM Web_Employee_Work_Allocation_Mst AS Work
    //         INNER JOIN UserDetails_Det AS Login
    //             ON Login.Lcode = Work.Lcode
    //             AND Login.Name = Work.Sub_Department
    //         WHERE Login.Ccode = '$CompanyCode'
    //           AND Login.Lcode = '$LocationCode'
    //           AND Login.UserID = '$Login_User'
    //           AND Work.Date = '$Date'
    //           AND Work.Shift = '$Next_Shift'
    //           AND Work.Work_Status = '1'
    //           --AND Work.Assign_Status = '1'
    //           --AND Work.Closing_Status = '1'
    //           AND Work.Working_Type = 'OT'";

    //     //   echo '<pre>';
    //     //   print_r($sql);
    //     //   exit;

    //     $query = $this->db->query($sql);
    //     if ($query->num_rows() === 0) return [];

    //     $results = [];
    //     $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
    //     $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

    //     // Get shift start and end time
    //     $Shift_Sql = "SELECT StartTime, EndTime 
    //               FROM Shift_Mst 
    //               WHERE CompCode = '$CompanyCode' 
    //                 AND LocCode = '$LocationCode' 
    //                 AND ShiftDesc = '$Shift'";
    //     $Shift_Data = $this->db->query($Shift_Sql)->row();


    //     $Shift_Start_Time = $Shift_Data->StartTime ?? '00:00:00';
    //     $Shift_End_Time   = $Shift_Data->EndTime ?? '00:00:00';

    //     foreach ($query->result() as $row) {

    //         $Employee_ID = $row->EmpNo;
    //         $Employee_Name = $row->FirstName;
    //         $Sub_Department = $row->Sub_Department;
    //         $Shift_Closing_Time = date('h:i A', strtotime($row->Updated_Time));;

    //         // OUT time range
    //         if (strtoupper($Next_Shift) === 'SHIFT3') {
    //             $out_start = "$Date 13:00:00";
    //             $out_end   = "$nextDate 10:00:00";
    //         } else {
    //             $out_start = "$Date 06:00:00";
    //             $out_end   = "$nextDate 04:00:00";
    //         }

    //         // Get OUT time
    //         $Sql_OUT = "SELECT TOP 1 TimeOUT
    //                 FROM LogTime_OUT
    //                 WHERE MachineID = '$Employee_ID'
    //                   AND Compcode = '$CompanyCode'
    //                   AND LocCode = '$LocationCode'
    //                   AND TimeOUT BETWEEN '$out_start' AND '$out_end'
    //                 ORDER BY TimeOUT DESC";
    //         $out_query = $this->db->query($Sql_OUT);

    //         $OUTTime = '';
    //         $OUTDateTime = null;
    //         if ($out_query->num_rows() > 0) {
    //             $OUTDateTime = new DateTime($out_query->row()->TimeOUT);
    //             $OUTTime = $OUTDateTime->format('h:i A');
    //         }

    //         // IN time range
    //         $in_start = "$Date 02:00:00";
    //         $in_end   = "$nextDate 02:00:00";

    //         $Sql_IN = "SELECT TOP 1 TimeIN
    //                FROM LogTime_IN
    //                WHERE MachineID = '$Employee_ID'
    //                  AND Compcode = '$CompanyCode'
    //                  AND LocCode = '$LocationCode'
    //                  AND TimeIN BETWEEN '$in_start' AND '$in_end'
    //                ORDER BY TimeIN ASC";
    //         $in_query = $this->db->query($Sql_IN);

    //         // Fallback for SHIFT3
    //         if ($in_query->num_rows() === 0 && strtoupper($Next_Shift) === 'SHIFT3') {
    //             $prev_in_start = "$previousDate 22:00:00";
    //             $prev_in_end   = "$Date 01:00:00";

    //             $Sql_Pre_IN = "SELECT TOP 1 TimeIN
    //                        FROM LogTime_IN
    //                        WHERE MachineID = '$Employee_ID'
    //                          AND Compcode = '$CompanyCode'
    //                          AND LocCode = '$LocationCode'
    //                          AND TimeIN BETWEEN '$prev_in_start' AND '$prev_in_end'
    //                        ORDER BY TimeIN ASC";
    //             $prev_in_query = $this->db->query($Sql_Pre_IN);
    //             if ($prev_in_query->num_rows() > 0) {
    //                 $in_query = $prev_in_query;
    //             }
    //         }

    //         $INTime = '';
    //         $INDateTime = null;
    //         if ($in_query->num_rows() > 0) {
    //             $INDateTime = new DateTime($in_query->row()->TimeIN);
    //             $INTime = $INDateTime->format('h:i A');
    //         }

    //         // Calculate OT
    //         $decimalOT = 0.0;

    //         if ($INDateTime && $OUTDateTime) {
    //             $ShiftStart = new DateTime("$Date $Shift_Start_Time");
    //             $ShiftEnd = new DateTime("$Date $Shift_End_Time");
    //             if ($ShiftEnd <= $ShiftStart) $ShiftEnd->modify('+1 day');

    //             $workedMinutes = round(($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60, 2);
    //             $shiftMinutes  = round(($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60, 2);

    //             $otMinutes = max(0, $workedMinutes - $shiftMinutes);
    //             $otHours   = (int)floor($otMinutes / 60);
    //             $otRemMin  = round($otMinutes - ($otHours * 60));

    //             // Convert remaining minutes to decimal
    //             if ($otRemMin >= 11 && $otRemMin <= 20) {
    //                 $minuteDecimal = 0.25;
    //             } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
    //                 $minuteDecimal = 0.50;
    //             } elseif ($otRemMin >= 41) {
    //                 $minuteDecimal = 1.0;
    //             } else {
    //                 $minuteDecimal = 0.0;
    //             }

    //             $decimalOT = $otHours + $minuteDecimal;
    //         }

    //         $Date_Check = date('d/m/Y', strtotime($Date));

    //         $Sql_Verify = "SELECT * FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND TranDate = '$Date_Check'
    //                    AND Update_Status = 'E-Master' AND Status = '0' AND TokenNo = '$Employee_ID'";
    //         $Query_Verify = $this->db->query($Sql_Verify);


    //         if ($Query_Verify->num_rows() > 0) {

    //             $Verify_Result = $Query_Verify->result();

    //             $Sql_Updated = "SELECT OTHrs FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND TranDate = '$Date_Check'
    //                    AND Update_Status = 'E-Master' AND TokenNo = '$Employee_ID'";

    //             $Query_Updated = $this->db->query($Sql_Updated)->result();



    //             $results[] = [

    //                 'Date' => date('d/m/Y', strtotime($Date)),
    //                 'Employee_ID'  => $Employee_ID,
    //                 'Employee_Name' => $Employee_Name,
    //                 'In_Time' => $INTime,
    //                 'Out_Time' => $OUTTime,
    //                 'E_Master_Closing' => $Shift_Closing_Time,
    //                 'OT_Hour' => $Query_Updated[0]->OTHrs,
    //                 'Updated_Status' => '1'

    //             ];
    //         } else {

    //             $results[] = [

    //                 'Date' => date('d/m/Y', strtotime($Date)),
    //                 'Employee_ID'  => $Employee_ID,
    //                 'Employee_Name' => $Employee_Name,
    //                 'In_Time' => $INTime,
    //                 'Out_Time' => $OUTTime,
    //                 'E_Master_Closing' => $Shift_Closing_Time,
    //                 'OT_Hour' => number_format($decimalOT, 2),
    //                 'Updated_Status' => '0'

    //             ];
    //         }
    //     }

    //     return $results;
    // }



// public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
// {
//     $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

//     $sql = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department, Work.Updated_Time,Work.Closing_Status
//             FROM Web_Employee_Work_Allocation_Mst AS Work
//             INNER JOIN UserDetails_Det AS Login
//                 ON Login.Lcode = Work.Lcode
//                 AND Login.Name = Work.Sub_Department
//             WHERE Login.Ccode = '$CompanyCode'
//               AND Login.Lcode = '$LocationCode'
//               AND Login.UserID = '$Login_User'
//               AND Work.Date = '$Date'
//               AND Work.Shift = '$Shift'
//               AND Work.Work_Status = '1'";

//     // $Sql_Next_Shift  = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department, Work.Updated_Time
//     //         FROM Web_Employee_Work_Allocation_Mst AS Work
//     //         INNER JOIN UserDetails_Det AS Login
//     //             ON Login.Lcode = Work.Lcode
//     //             AND Login.Name = Work.Sub_Department
//     //         WHERE Login.Ccode = '$CompanyCode'
//     //           AND Login.Lcode = '$LocationCode'
//     //           AND Login.UserID = '$Login_User'
//     //           AND Work.Date = '$Date'
//     //           AND Work.Shift = '$Next_Shift'
//     //           AND Work.Work_Status = '1'";

//     // $Query_Next_Shift = $this->db->query($Sql_Next_Shift);
              


//     $query = $this->db->query($sql);
//     if ($query->num_rows() === 0) return [];

//     $results = [];
//     $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
//     $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

//     $Shift_Sql = "SELECT StartTime, EndTime 
//                   FROM Shift_Mst 
//                   WHERE CompCode = '$CompanyCode' 
//                     AND LocCode = '$LocationCode' 
//                     AND ShiftDesc = '$Shift'";
//     $Shift_Data = $this->db->query($Shift_Sql)->row();
//     $Shift_Start_Time = $Shift_Data->StartTime ?? '00:00:00';
//     $Shift_End_Time   = $Shift_Data->EndTime ?? '00:00:00';

//     foreach ($query->result() as $row) {
//         $Employee_ID = $row->EmpNo;
//         $Employee_Name = $row->FirstName;
//         $Shift_Closing_Time_Str = $row->Updated_Time;
//         $Shift_Closing_Status = $row->Closing_Status;

//         // Safely format shift closing time
//         $Shift_Closing_Time = '';
//         if (!empty($Shift_Closing_Time_Str) && $Shift_Closing_Time_Str !== '-' && strtotime($Shift_Closing_Time_Str) !== false) {
//             $Shift_Closing_Time = date('h:i A', strtotime($Shift_Closing_Time_Str));
//         }

//         // OUT time range
//         if (strtoupper($Next_Shift) === 'SHIFT3') {
//             $out_start = "$Date 13:00:00";
//             $out_end   = "$nextDate 10:00:00";
//         } else {
//             $out_start = "$Date 06:00:00";
//             $out_end   = "$nextDate 04:00:00";
//         }

//         $Sql_OUT = "SELECT TOP 1 TimeOUT FROM LogTime_OUT
//                     WHERE MachineID = '$Employee_ID'
//                       AND Compcode = '$CompanyCode'
//                       AND LocCode = '$LocationCode'
//                       AND TimeOUT BETWEEN '$out_start' AND '$out_end'
//                     ORDER BY TimeOUT DESC";
//         $out_query = $this->db->query($Sql_OUT);
//         $OUTTime = '';
//         $OUTDateTime = null;
//         if ($out_query->num_rows() > 0) {
//             $OUTDateTime = new DateTime($out_query->row()->TimeOUT);
//             $OUTTime = $OUTDateTime->format('h:i A');
//         }

//         // IN time
//         $in_start = "$Date 02:00:00";
//         $in_end   = "$nextDate 02:00:00";
//         $Sql_IN = "SELECT TOP 1 TimeIN FROM LogTime_IN
//                    WHERE MachineID = '$Employee_ID'
//                      AND Compcode = '$CompanyCode'
//                      AND LocCode = '$LocationCode'
//                      AND TimeIN BETWEEN '$in_start' AND '$in_end'
//                    ORDER BY TimeIN ASC";
//         $in_query = $this->db->query($Sql_IN);

//         if ($in_query->num_rows() === 0 && strtoupper($Next_Shift) === 'SHIFT3') {
//             $prev_in_start = "$previousDate 22:00:00";
//             $prev_in_end   = "$Date 01:00:00";
//             $Sql_Pre_IN = "SELECT TOP 1 TimeIN FROM LogTime_IN
//                            WHERE MachineID = '$Employee_ID'
//                              AND Compcode = '$CompanyCode'
//                              AND LocCode = '$LocationCode'
//                              AND TimeIN BETWEEN '$prev_in_start' AND '$prev_in_end'
//                            ORDER BY TimeIN ASC";
//             $prev_in_query = $this->db->query($Sql_Pre_IN);
//             if ($prev_in_query->num_rows() > 0) {
//                 $in_query = $prev_in_query;
//             }
//         }

//         $INTime = '';
//         $INDateTime = null;
//         if ($in_query->num_rows() > 0) {
//             $INDateTime = new DateTime($in_query->row()->TimeIN);
//             $INTime = $INDateTime->format('h:i A');
//         }

//         // Total Working Hours
//         $Total_Working_Hours = 0.00;
//         $workingMinutes = 0.0;
//         if ($INDateTime && $OUTDateTime) {
//             $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
//             $Total_Working_Hours = round($workingMinutes / 60.0, 2);
//         }

//         // OT Closing Time Difference
//         $OT_Closing_Diff = 0;
//         if ($OUTDateTime && !empty($Shift_Closing_Time_Str) && $Shift_Closing_Time_Str !== '-' && strtotime($Shift_Closing_Time_Str) !== false) {
//             try {
//                 $ShiftClosingDateTime = new DateTime($Shift_Closing_Time_Str);
//                 $diffMinutes = abs(($OUTDateTime->getTimestamp() - $ShiftClosingDateTime->getTimestamp()) / 60.0);
//                 $OT_Closing_Diff = ($diffMinutes > 45) ? 1 : 0;
//             } catch (Exception $e) {
//                 $OT_Closing_Diff = 0;
//             }
//         }

//         // Calculate OT
//         $decimalOT = 0.00;
//         if ($INDateTime && $OUTDateTime) {
//             $ShiftStart = new DateTime("$Date $Shift_Start_Time");
//             $ShiftEnd = new DateTime("$Date $Shift_End_Time");
//             if ($ShiftEnd <= $ShiftStart) $ShiftEnd->modify('+1 day');

//             $shiftMinutes = ($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60.0;
//             $otMinutes = max(0, $workingMinutes - $shiftMinutes);

//             $otHours = floor($otMinutes / 60.0);
//             $otRemMin = $otMinutes - ($otHours * 60.0);

//             if ($otRemMin >= 11 && $otRemMin <= 20) {
//                 $minuteDecimal = 0.25;
//             } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
//                 $minuteDecimal = 0.50;
//             } elseif ($otRemMin >= 41) {
//                 $minuteDecimal = 1.0;
//             } else {
//                 $minuteDecimal = 0.0;
//             }

//             $decimalOT = round($otHours + $minuteDecimal, 2);
//         }

//         // Final result
//         $Date_Check = date('d/m/Y', strtotime($Date));
//         $Sql_Verify = "SELECT * FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
//                        AND TranDate = '$Date_Check' AND Update_Status = 'E-Master' AND Status = '0'
//                        AND TokenNo = '$Employee_ID'";
//         $Query_Verify = $this->db->query($Sql_Verify);

//         if ($Query_Verify->num_rows() > 0) {
//             $Query_Updated = $this->db->query(
//                 "SELECT OTHrs FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
//                  AND TranDate = '$Date_Check' AND Update_Status = 'E-Master' AND TokenNo = '$Employee_ID'"
//             )->result();

//             $results[] = [
//                 'Date' => date('d/m/Y', strtotime($Date)),
//                 'Employee_ID' => $Employee_ID,
//                 'Employee_Name' => $Employee_Name,
//                 'In_Time' => $INTime,
//                 'Out_Time' => $OUTTime,
//                 'E_Master_Closing' => $Shift_Closing_Time,
//                 'OT_Hour' => $Query_Updated[0]->OTHrs,
//                 'Total_Working_Hours' => number_format($Total_Working_Hours, 2),
//                 'OT_Closing_Diff' => $OT_Closing_Diff,
//                 'Closing_Status' => $Shift_Closing_Status,
//                 'Updated_Status' => '1'
//             ];
//         } else {
//             $results[] = [
//                 'Date' => date('d/m/Y', strtotime($Date)),
//                 'Employee_ID' => $Employee_ID,
//                 'Employee_Name' => $Employee_Name,
//                 'In_Time' => $INTime,
//                 'Out_Time' => $OUTTime,
//                 'E_Master_Closing' => $Shift_Closing_Time,
//                 'OT_Hour' => number_format($decimalOT, 2),
//                 'Total_Working_Hours' => number_format($Total_Working_Hours, 2),
//                 'OT_Closing_Diff' => $OT_Closing_Diff,
//                 'Closing_Status' => $Shift_Closing_Status,
//                 'Updated_Status' => '0'
//             ];
//         }
//     }

//     return $results;
// }



// current pge working
// public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
// {
//     $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');


//         $sql = "SELECT Work.EmpNo, 
//                 Work.FirstName, 
//                 Work.Sub_Department, 
//                 Work.Updated_Time, 
//                 Work.Closing_Status,
//                 Emp.Wages,
//                 Emp.OTEligible
//             FROM 
//                 Web_Employee_Work_Allocation_Mst AS Work
//             INNER JOIN 
//                 UserDetails_Det AS Login 
//                 ON Login.Lcode = Work.Lcode 
//                 AND Login.Name = Work.Sub_Department
//             INNER JOIN 
//                 (
//                     SELECT 
//                         EmpNo, 
//                         MAX(Wages) AS Wages, 
//                         MAX(OTEligible) AS OTEligible,
//                         LocCode, CompCode
//                     FROM 
//                         Employee_Mst
//                     GROUP BY EmpNo, LocCode, CompCode
//                 ) AS Emp
//                 ON Emp.LocCode = Work.Lcode 
//                 AND Emp.CompCode = Work.Ccode 
//                 AND Emp.EmpNo = Work.EmpNo
//             WHERE 
//                 Login.Ccode = '$CompanyCode'
//                 AND Login.Lcode = '$LocationCode'
//                 AND Login.UserID = '$Login_User'
//                 AND Work.Date = '$Date'
//                 AND Work.Shift = '$Shift'
//                 AND Emp.OTEligible = 'Yes'";


//     $query = $this->db->query($sql);
//     if ($query->num_rows() === 0) return [];

//     $results = [];
//     $employees = $query->result();

//     $employeeIDs = array_map(function($e) { return "'{$e->EmpNo}'"; }, $employees);

//     $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
//     $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

//     $Shift_Sql = "SELECT StartTime, EndTime 
//                   FROM Shift_Mst 
//                   WHERE CompCode = '$CompanyCode' 
//                     AND LocCode = '$LocationCode' 
//                     AND ShiftDesc = '$Shift'";
//     $Shift_Data = $this->db->query($Shift_Sql)->row();
//     $Shift_Start_Time = $Shift_Data->StartTime ?? '00:00:00';
//     $Shift_End_Time   = $Shift_Data->EndTime ?? '00:00:00';

//     // OUT time range
//     $out_start = (strtoupper($Next_Shift) === 'SHIFT3') ? "$Date 13:00:00" : "$Date 06:00:00";
//     $out_end   = (strtoupper($Next_Shift) === 'SHIFT3') ? "$nextDate 10:00:00" : "$nextDate 04:00:00";

//     $Sql_OUT_All = "SELECT MachineID, MAX(TimeOUT) as TimeOUT
//                     FROM LogTime_OUT
//                     WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                       AND Compcode = '$CompanyCode'
//                       AND LocCode = '$LocationCode'
//                       AND TimeOUT BETWEEN '$out_start' AND '$out_end'
//                     GROUP BY MachineID";
                    
//     $OUT_Log = $this->db->query($Sql_OUT_All)->result();
//     $OUT_Map = [];
//     foreach ($OUT_Log as $row) {
//         $OUT_Map[$row->MachineID] = new DateTime($row->TimeOUT);
//     }

//     // IN time range
//     $in_start = "$Date 02:00:00";
//     $in_end   = "$nextDate 02:00:00";

//     $Sql_IN_All = "SELECT MachineID, MIN(TimeIN) as TimeIN
//                    FROM LogTime_IN
//                    WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                      AND Compcode = '$CompanyCode'
//                      AND LocCode = '$LocationCode'
//                      AND TimeIN BETWEEN '$in_start' AND '$in_end'
//                    GROUP BY MachineID";
//     $IN_Log = $this->db->query($Sql_IN_All)->result();
//     $IN_Map = [];
//     foreach ($IN_Log as $row) {
//         $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
//     }

//     // Backup IN for SHIFT3
//     if (strtoupper($Next_Shift) === 'SHIFT3') {

//         $prev_in_start = "$previousDate 00:00:00";
//         $prev_in_end   = "$Date 01:00:00";

//         $Sql_Pre_IN = "SELECT MachineID, MIN(TimeIN) as TimeIN
//                        FROM LogTime_IN
//                        WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                          AND Compcode = '$CompanyCode'
//                          AND LocCode = '$LocationCode'
//                          AND TimeIN BETWEEN '$prev_in_start' AND '$prev_in_end'
//                        GROUP BY MachineID";

//         $Prev_IN_Log = $this->db->query($Sql_Pre_IN)->result();
//         foreach ($Prev_IN_Log as $row) {
//             if (!isset($IN_Map[$row->MachineID])) {
//                 $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
//             }
//         }
//     }




//     // OT Verification
//     $Date_Check = date('d/m/Y', strtotime($Date));
//     $Sql_Verify_All = "SELECT TokenNo, OTHrs FROM OTHours
//                        WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
//                          AND TranDate = '$Date_Check' AND Update_Status = 'E-Master' AND Status = '0'
//                          AND TokenNo IN (" . implode(',', $employeeIDs) . ")";
//     $Verify_Log = $this->db->query($Sql_Verify_All)->result();
//     $Verify_Map = [];
//     foreach ($Verify_Log as $row) {
//         $Verify_Map[$row->TokenNo] = $row->OTHrs;
//     }

//     foreach ($employees as $row) {
//         $Employee_ID = $row->EmpNo;
//         $Employee_Name = $row->FirstName;
//         $Shift_Closing_Time_Str = $row->Updated_Time;
//         $Shift_Closing_Status = $row->Closing_Status;

//         $Shift_Closing_Time = '';
//         if (!empty($Shift_Closing_Time_Str) && $Shift_Closing_Time_Str !== '-' && strtotime($Shift_Closing_Time_Str) !== false) {
//             $Shift_Closing_Time = date('h:i A', strtotime($Shift_Closing_Time_Str));
//         }

//         $INDateTime = $IN_Map[$Employee_ID] ?? null;
//         $OUTDateTime = $OUT_Map[$Employee_ID] ?? null;

//         $INTime = $INDateTime ? $INDateTime->format('h:i A') : '';
//         $OUTTime = $OUTDateTime ? $OUTDateTime->format('h:i A') : '';

//         $Total_Working_Hours = 0.00;
//         $workingMinutes = 0.0;
//         if ($INDateTime && $OUTDateTime) {
//             $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
//             $Total_Working_Hours = round($workingMinutes / 60.0, 2);
//         }

//         $OT_Closing_Diff = 0;
//         if ($OUTDateTime && !empty($Shift_Closing_Time_Str) && strtotime($Shift_Closing_Time_Str) !== false) {
//             try {
//                 $ShiftClosingDateTime = new DateTime($Shift_Closing_Time_Str);
//                 $diffMinutes = abs(($OUTDateTime->getTimestamp() - $ShiftClosingDateTime->getTimestamp()) / 60.0);
//                 $OT_Closing_Diff = ($diffMinutes > 45) ? 1 : 0;
//             } catch (Exception $e) {
//                 $OT_Closing_Diff = 0;
//             }
//         }

//         $decimalOT = 0.00;
//         if ($INDateTime && $OUTDateTime) {
//             $ShiftStart = new DateTime("$Date $Shift_Start_Time");
//             $ShiftEnd = new DateTime("$Date $Shift_End_Time");
//             if ($ShiftEnd <= $ShiftStart) $ShiftEnd->modify('+1 day');

//             $shiftMinutes = ($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60.0;
//             $otMinutes = max(0, $workingMinutes - $shiftMinutes);

//             $otHours = floor($otMinutes / 60.0);
//             $otRemMin = $otMinutes - ($otHours * 60.0);

//             if ($otRemMin >= 11 && $otRemMin <= 20) {
//                 $minuteDecimal = 0.25;
//             } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
//                 $minuteDecimal = 0.50;
//             } elseif ($otRemMin >= 41) {
//                 $minuteDecimal = 1.0;
//             } else {
//                 $minuteDecimal = 0.0;
//             }

//             $decimalOT = round($otHours + $minuteDecimal, 2);
//         }

//         $results[] = [
//             'Date' => $Date_Check,
//             'Employee_ID' => $Employee_ID,
//             'Employee_Name' => $Employee_Name,
//             'In_Time' => $INTime,
//             'Out_Time' => $OUTTime,
//             'E_Master_Closing' => $Shift_Closing_Time,
//             'OT_Hour' => isset($Verify_Map[$Employee_ID]) ? $Verify_Map[$Employee_ID] : number_format($decimalOT, 2),
//             'Total_Working_Hours' => number_format($Total_Working_Hours, 2),
//             'OT_Closing_Diff' => $OT_Closing_Diff,
//             'Closing_Status' => $Shift_Closing_Status,
//             'Updated_Status' => isset($Verify_Map[$Employee_ID]) ? '1' : '0'
//         ];
//     }

//     return $results;
// }


public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
{
    $shiftPriority = ['SHIFT1' => 1, 'SHIFT2' => 2, 'SHIFT3' => 3];
    $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

    // Fetch all shifts for employees on given date
    $sql_all_shifts = "
        SELECT Work.EmpNo, Work.Shift
        FROM Web_Employee_Work_Allocation_Mst AS Work
        INNER JOIN UserDetails_Det AS Login 
          ON Login.Lcode = Work.Lcode 
          AND Login.Name = Work.Sub_Department
        WHERE Login.Ccode = '$CompanyCode'
          AND Login.Lcode = '$LocationCode'
          AND Login.UserID = '$Login_User'
          AND Work.Date = '$Date'
    ";
    $all_shifts_result = $this->db->query($sql_all_shifts)->result();

    $empShiftMap = [];
    foreach ($all_shifts_result as $row) {
        $empShiftMap[$row->EmpNo][] = $row->Shift;
    }

    // Fetch employees who are OT eligible for given shift
    $sql = "SELECT Work.EmpNo, 
               Work.FirstName, 
               Work.Sub_Department, 
               Work.Updated_Time, 
               Work.Closing_Status,
               Emp.Wages,
               Emp.OTEligible
        FROM Web_Employee_Work_Allocation_Mst AS Work
        INNER JOIN UserDetails_Det AS Login 
          ON Login.Lcode = Work.Lcode 
          AND Login.Name = Work.Sub_Department
        INNER JOIN (
            SELECT EmpNo, MAX(Wages) AS Wages, MAX(OTEligible) AS OTEligible, LocCode, CompCode
            FROM Employee_Mst
            GROUP BY EmpNo, LocCode, CompCode
        ) AS Emp 
          ON Emp.LocCode = Work.Lcode 
         AND Emp.CompCode = Work.Ccode 
         AND Emp.EmpNo = Work.EmpNo
        WHERE Login.Ccode = '$CompanyCode'
          AND Login.Lcode = '$LocationCode'
          AND Login.UserID = '$Login_User'
          AND Work.Date = '$Date'
          AND Work.Shift = '$Shift'
          AND Emp.OTEligible = 'Yes'
    ";
    $query = $this->db->query($sql);
    if ($query->num_rows() === 0) {
        return [];
    }

    $employees = $query->result();

    // Filter to get only employees whose assigned shift has highest priority
    $filteredEmployees = [];
    foreach ($employees as $emp) {
        $empNo = $emp->EmpNo;
        if (!isset($empShiftMap[$empNo])) {
            continue;
        }
        $assignedShifts = $empShiftMap[$empNo];
        $highestPriorityShift = null;
        $highestPriorityValue = PHP_INT_MAX;
        foreach ($assignedShifts as $sh) {
            if (isset($shiftPriority[$sh]) && $shiftPriority[$sh] < $highestPriorityValue) {
                $highestPriorityValue = $shiftPriority[$sh];
                $highestPriorityShift = $sh;
            }
        }
        if ($Shift === $highestPriorityShift) {
            $filteredEmployees[] = $emp;
        }
    }

    if (empty($filteredEmployees)) {
        return [];
    }

    $employeeIDs = array_map(function ($e) {
        return "'" . $e->EmpNo . "'";
    }, $filteredEmployees);

    $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
    // $previousDate is not used in your code, you had it earlier; keep if needed
    // $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

    // Get shift start and end times
    $Shift_Sql = "
        SELECT StartTime, EndTime 
        FROM Shift_Mst 
        WHERE CompCode = '$CompanyCode' 
          AND LocCode = '$LocationCode' 
          AND ShiftDesc = '$Shift'
    ";
    $Shift_Data = $this->db->query($Shift_Sql)->row();
    $Shift_Start_Time = isset($Shift_Data->StartTime) ? $Shift_Data->StartTime : '00:00:00';
    $Shift_End_Time   = isset($Shift_Data->EndTime)   ? $Shift_Data->EndTime   : '00:00:00';

    // OUT punch window logic
    if ($Shift === 'SHIFT2') {
        $out_start = "$Date 13:00:00";
        $out_end   = "$nextDate 10:00:00";
    } elseif ($Shift === 'SHIFT1') {
        $out_start = "$Date 07:00:00";
    $out_end   = "$Date 23:59:59";
    } else { // SHIFT3
        $out_start = "$nextDate 00:30:00";
        $out_end   = "$nextDate 09:00:00";
    }

    $Sql_OUT_All = "
        SELECT MachineID, MAX(TimeOUT) AS TimeOUT
        FROM LogTime_OUT
        WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
          AND Compcode = '$CompanyCode'
          AND LocCode = '$LocationCode'
          AND TimeOUT BETWEEN '$out_start' AND '$out_end'
        GROUP BY MachineID
    ";
    $OUT_Log = $this->db->query($Sql_OUT_All)->result();
    $OUT_Map = [];
    foreach ($OUT_Log as $row) {
        $OUT_Map[$row->MachineID] = new DateTime($row->TimeOUT);
    }

    // IN punch window logic
    if ($Shift === 'SHIFT3') {
        $in_start = "$nextDate 00:30:00";
        $in_end   = "$nextDate 09:00:00";
    } elseif ($Shift === 'SHIFT2') {
        $in_start = "$Date 13:00:00";
        $in_end   = "$nextDate 02:00:00";
    } else { // SHIFT1
        $in_start = "$Date 07:00:00";
        $in_end   = "$Date 23:59:59";
    }

    $Sql_IN_All = "SELECT MachineID, MIN(TimeIN) AS EarliestTimeIN
        FROM LogTime_IN
        WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
          AND Compcode = '$CompanyCode'
          AND LocCode = '$LocationCode'
          AND TimeIN BETWEEN '$in_start' AND '$in_end'
        GROUP BY MachineID
    ";
    $IN_Log = $this->db->query($Sql_IN_All)->result();
    $IN_Map = [];
    foreach ($IN_Log as $row) {
        $IN_Map[$row->MachineID] = new DateTime($row->EarliestTimeIN);
    }

    // Verified OT from OTHours
    $Date_Check = date('d/m/Y', strtotime($Date));
    $Sql_Verify_All = "SELECT TokenNo, OTHrs
        FROM OTHours
        WHERE Ccode = '$CompanyCode'
          AND Lcode = '$LocationCode'
          AND TranDate = '$Date_Check'
          AND Update_Status = 'E-Master'
          AND Status = '0'
          AND TokenNo IN (" . implode(',', $employeeIDs) . ")
    ";
    $Verify_Log = $this->db->query($Sql_Verify_All)->result();
    $Verify_Map = [];
    foreach ($Verify_Log as $row) {
        $Verify_Map[$row->TokenNo] = $row->OTHrs;
    }

    // Final result assembly
    $results = [];
    foreach ($filteredEmployees as $row) {
        $Employee_ID = $row->EmpNo;
        $Employee_Name = $row->FirstName;
        $Shift_Closing_Time_Str = $row->Updated_Time;
        $Shift_Closing_Status = $row->Closing_Status;

        $Shift_Closing_Time = '';
        if (!empty($Shift_Closing_Time_Str) && $Shift_Closing_Time_Str !== '-' && strtotime($Shift_Closing_Time_Str) !== false) {
            $Shift_Closing_Time = date('h:i A', strtotime($Shift_Closing_Time_Str));
        }

        $INDateTime  = isset($IN_Map[$Employee_ID]) ? $IN_Map[$Employee_ID] : null;
        $OUTDateTime = isset($OUT_Map[$Employee_ID]) ? $OUT_Map[$Employee_ID] : null;

        $INTime  = $INDateTime  ? $INDateTime->format('h:i A')  : '';
        $OUTTime = $OUTDateTime ? $OUTDateTime->format('h:i A') : '';

        $Total_Working_Hours = 0.00;
        $decimalOT = 0.00;

        if ($INDateTime && $OUTDateTime) {
            // adjust if IN is after OUT (cross midnight)
            if ($INDateTime > $OUTDateTime) {
                $INDateTime->modify('-1 day');
            }

            $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
            $Total_Working_Hours = round($workingMinutes / 60.0, 2);

            $ShiftStart = new DateTime("$Date $Shift_Start_Time");
            $ShiftEnd   = new DateTime("$Date $Shift_End_Time");
            if ($ShiftEnd <= $ShiftStart) {
                $ShiftEnd->modify('+1 day');
            }

            $shiftMinutes = ($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60.0;
            $otMinutes = max(0, $workingMinutes - $shiftMinutes);

            $otHours   = floor($otMinutes / 60.0);
            $otRemMin  = $otMinutes - ($otHours * 60.0);

            if ($otRemMin >= 11 && $otRemMin <= 20) {
                $minuteDecimal = 0.25;
            } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
                $minuteDecimal = 0.50;
            } elseif ($otRemMin >= 41) {
                $minuteDecimal = 1.0;
            } else {
                $minuteDecimal = 0.0;
            }

            $decimalOT = round($otHours + $minuteDecimal, 2);

            // Max OT Caps based on Shift
            if (($Shift == 'SHIFT1' || $Shift == 'SHIFT2') && $Total_Working_Hours > 7 && $decimalOT > 8) {
                $decimalOT = 8.50;
            } elseif ($Shift == 'SHIFT3' && $Total_Working_Hours > 6 && $decimalOT > 7) {
                $decimalOT = 7.00;
            }
        }

        $OT_Closing_Diff = 0;
        if ($INDateTime && $OUTDateTime) {
            // Adjust if IN > OUT (date crossover)
            if ($INDateTime > $OUTDateTime) {
                $INDateTime->modify('-1 day');
            }

            $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
            $Total_Working_Hours = round($workingMinutes / 60.0, 2); // just for display

            // Determine OT threshold per shift
            if ($Shift == 'SHIFT3') {
                $thresholdMinutes = 450; // 7.5 hours
            } else {
                $thresholdMinutes = 510; // 8.5 hours
            }

            $otMinutes = max(0, $workingMinutes - $thresholdMinutes);

            if ($otMinutes >= 30) {
                $decimalOT = floor($otMinutes / 60) + 1.0; // Round up to next hour
            } else {
                $decimalOT = 0.0;
            }
        }


        $results[] = [
            'Date'                => $Date_Check,
            'Employee_ID'         => $Employee_ID,
            'Employee_Name'       => $Employee_Name,
            'In_Time'             => $INTime,
            'Out_Time'            => $OUTTime,
            'E_Master_Closing'    => $Shift_Closing_Time,
            'OT_Hour'             => isset($Verify_Map[$Employee_ID]) ? $Verify_Map[$Employee_ID] : number_format($decimalOT, 2),
            'Total_Working_Hours' => floor($Total_Working_Hours),
            'OT_Closing_Diff'      => $OT_Closing_Diff,
            'Closing_Status'      => $Shift_Closing_Status,
            'Updated_Status'      => isset($Verify_Map[$Employee_ID]) ? '1' : '0'
        ];
    } // end foreach filteredEmployees

    return $results;
}











// public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type){


//             $Sql = "SELECT Work.EmpNo, 
//                 Work.FirstName, 
//                 Work.Sub_Department, 
//                 Work.Updated_Time, 
//                 Work.Closing_Status,
//                 Emp.Wages,
//                 Emp.OTEligible
//             FROM 
//                 Web_Employee_Work_Allocation_Mst AS Work
//             INNER JOIN 
//                 UserDetails_Det AS Login 
//                 ON Login.Lcode = Work.Lcode 
//                 AND Login.Name = Work.Sub_Department
//             INNER JOIN 
//                 (
//                     SELECT 
//                         EmpNo, 
//                         MAX(Wages) AS Wages, 
//                         MAX(OTEligible) AS OTEligible,
//                         LocCode, CompCode
//                     FROM 
//                         Employee_Mst
//                     GROUP BY EmpNo, LocCode, CompCode
//                 ) AS Emp
//                 ON Emp.LocCode = Work.Lcode 
//                 AND Emp.CompCode = Work.Ccode 
//                 AND Emp.EmpNo = Work.EmpNo
//             WHERE 
//                 Login.Ccode = '$CompanyCode'
//                 AND Login.Lcode = '$LocationCode'
//                 AND Login.UserID = '$Login_User'
//                 AND Work.Date = '$Date'
//                 AND Work.Shift = '$Shift'
//                 AND Emp.OTEligible = 'Yes'";


//     $query = $this->db->query($Sql);

//     if($query->num_rows() > 0){

//         $Sql_Shift = "SELECT * FROM Shift_Mst WHERE ComCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
//         $Query_Shift = $this->db->query($Sql_Shift);

//         $Shift_Start_Time = $Query_Shift->StartTime;
//         $Shift_End_Time = $Query_Shift->StartTime;
//         $Shift_Start_IN = $Query_Shift->StartIN_Days;
//         $Shift_End_IN = $Query_Shift->EndIN_Days;

//         //Convertion Of Shift and Date 

//        if ($Shift_Start_IN == 1 && $Shift_End_IN == 1) {

//        $Shift_Conversion_Date = date('Y-m-d', strtotime($Date . ' +1 day'));

//         } else {

//              $Shift_Conversion_Date = $Date;
//         }



//         // Check Current Date Punching List LogTimeIN

//         $Sql_LogTimeIN = "SELECT  MIN(TimeIN) AS FirstPunchTime FROM LogTimeIN WHERE ComCode = '$CompanyCode' AND LocCode = '$LocationCode' AND CONVERT(DATE,TimeIN,105) = '$Shift_Conversion_Date'";
//         $Query_LogTimeIN = $this->db->query($Sql_LogTimeIN);

//         // Check Current Date Punching List LogTimeOUT

//         $Sql_LogTimeOUT = "SELECT  MAX(TimeOUT) AS LastPunchTime FROM LogTimeOUT WHERE ComCode = '$CompanyCode' AND LocCode = '$LocationCode' AND CONVERT(DATE,TimeOUT,105) = '$Shift_Conversion_Date'";
//         $Query_LogTimeOUT = $this->db->query($Sql_LogTimeOUT);

//         // OT Hours Calculation Section 

//     }





// }

// public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
// {
//     $Sql = "SELECT 
//             Work.EmpNo, 
//             Work.FirstName, 
//             Work.Sub_Department, 
//             Work.Updated_Time, 
//             Work.Closing_Status,
//             Emp.Wages,
//             Emp.OTEligible
//         FROM 
//             Web_Employee_Work_Allocation_Mst AS Work
//         INNER JOIN 
//             UserDetails_Det AS Login 
//             ON Login.Lcode = Work.Lcode 
//             AND Login.Name = Work.Sub_Department
//         INNER JOIN 
//             (
//                 SELECT 
//                     EmpNo, 
//                     MAX(Wages) AS Wages, 
//                     MAX(OTEligible) AS OTEligible,
//                     LocCode, 
//                     CompCode
//                 FROM Employee_Mst
//                 GROUP BY EmpNo, LocCode, CompCode
//             ) AS Emp
//             ON Emp.LocCode = Work.Lcode 
//             AND Emp.CompCode = Work.Ccode 
//             AND Emp.EmpNo = Work.EmpNo
//         WHERE 
//             Login.Ccode = '$CompanyCode'
//             AND Login.Lcode = '$LocationCode'
//             AND Login.UserID = '$Login_User'
//             AND Work.Date = '$Date'
//             AND Work.Shift = '$Shift'
//             AND Emp.OTEligible = 'Yes'";

//     $query = $this->db->query($Sql);

//     if ($query->num_rows() > 0) {
//         $employeeData = $query->row();

//         // Get shift details
//         $Sql_Shift = "SELECT * 
//             FROM Shift_Mst 
//             WHERE CompCode = '$CompanyCode' 
//               AND LocCode = '$LocationCode' 
//               AND ShiftDesc = '$Shift'
//         ";
//         $Query_Shift = $this->db->query($Sql_Shift);

//         if ($Query_Shift->num_rows() > 0) {
//             $Shift_Row = $Query_Shift->row();

//             $Shift_Start_Time = $Shift_Row->StartTime;
//             $Shift_End_Time   = $Shift_Row->EndTime;
//             $Shift_Start_IN   = $Shift_Row->StartIN_Days;
//             $Shift_End_IN     = $Shift_Row->EndIN_Days;
//             $Shift_Total_Hrs  = $Shift_Row->Total_Hrs; // e.g. 8.5 or 7.0

//             // Adjust shift date if it crosses to next day
//             $Shift_Conversion_Date = ($Shift_Start_IN == 1 && $Shift_End_IN == 1)
//                 ? date('Y-m-d', strtotime($Date . ' +1 day'))
//                 : $Date;

//             // Get all punch-ins
//             $Sql_AllPunchesIN = "SELECT TimeIN 
//                 FROM LogTime_IN 
//                 WHERE CompCode = '$CompanyCode' 
//                   AND LocCode = '$LocationCode' 
//                   AND MachineID = '{$employeeData->EmpNo}'
//                   AND CONVERT(DATE, TimeIN, 105) = '$Shift_Conversion_Date'
//                 ORDER BY TimeIN ASC
//             ";
//             $Query_PunchesIN = $this->db->query($Sql_AllPunchesIN);
//             $PunchesIN = [];
//             foreach ($Query_PunchesIN->result() as $row) {
//                 $PunchesIN[] = $row->TimeIN;
//             }

//             // Get all punch-outs
//             $Sql_AllPunchesOUT = "SELECT TimeOUT 
//                 FROM LogTime_OUT 
//                 WHERE CompCode = '$CompanyCode' 
//                   AND LocCode = '$LocationCode' 
//                   AND MachineID = '{$employeeData->EmpNo}'
//                   AND CONVERT(DATE, TimeOUT, 105) = '$Shift_Conversion_Date'
//                 ORDER BY TimeOUT ASC
//             ";
//             $Query_PunchesOUT = $this->db->query($Sql_AllPunchesOUT);
//             $PunchesOUT = [];
//             foreach ($Query_PunchesOUT->result() as $row) {
//                 $PunchesOUT[] = $row->TimeOUT;
//             }

//             // Initialize
//             $WorkingHours     = 0.0;
//             $OTHours          = 0.0;
//             $ShiftWorkingTime = (float)$Shift_Total_Hrs; // Already float
//             $FirstPunch       = $PunchesIN[0] ?? null;
//             $LastPunch        = end($PunchesOUT) ?: null;

//             // Calculate shift seconds if not defined
//             if (!empty($Shift_Total_Hrs)) {
//                 $ShiftSeconds = (float)$Shift_Total_Hrs * 3600;
//             } else {
//                 $ShiftStart = new DateTime($Shift_Conversion_Date . ' ' . $Shift_Start_Time);
//                 $ShiftEnd   = new DateTime($Shift_Conversion_Date . ' ' . $Shift_End_Time);
//                 if ($ShiftEnd < $ShiftStart) {
//                     $ShiftEnd->modify('+1 day');
//                 }
//                 $ShiftSeconds = $ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp();
//                 $ShiftWorkingTime = round($ShiftSeconds / 3600, 2);
//             }

//             // Calculate working hours
//             if ($FirstPunch && $LastPunch) {
//                 $start = new DateTime($FirstPunch);
//                 $end   = new DateTime($LastPunch);
//                 $ActualWorkedSeconds = $end->getTimestamp() - $start->getTimestamp();
//                 $ActualWorkedHours = round($ActualWorkedSeconds / 3600, 2); // as float

//                 if ($ActualWorkedHours >= $ShiftWorkingTime) {
//                     // Worked full shift
//                     $WorkingHours = $ShiftWorkingTime;
//                     $OTHours = 0.0;
//                 } else {
//                     // Worked less than shift
//                     $WorkingHours = $ActualWorkedHours;
//                     $OTHours = 0.0; // can be calculated if needed
//                 }
//             }

//             // Final Output Array
//             $result = [
//                 'EmpNo'            => $employeeData->EmpNo,
//                 'FirstName'        => $employeeData->FirstName,
//                 'WorkingHours'     => $WorkingHours,
//                 'OTHours'          => $OTHours,
//                 'ShiftWorkingTime' => $ShiftWorkingTime,
//                 'ShiftStart'       => $Shift_Start_Time,
//                 'ShiftEnd'         => $Shift_End_Time,
//                 'FirstPunch'       => $FirstPunch,
//                 'LastPunch'        => $LastPunch,
//                 'PunchesIN'        => $PunchesIN,
//                 'PunchesOUT'       => $PunchesOUT,
//             ];



//             // Output for testing
//             echo '<pre>';
//             print_r($result);
//             exit();
//         }
//     }

//     return false; // No eligible data
// }


// public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
// {
//     $nextShift = ($Shift === 'SHIFT1') ? 'SHIFT2'
//                : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

//     // Fetch employees eligible for OT
//     $sql = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department, Work.Updated_Time,
//                    Work.Closing_Status, Emp.Wages, Emp.OTEligible
//             FROM Web_Employee_Work_Allocation_Mst AS Work
//             INNER JOIN UserDetails_Det AS Login
//                 ON Login.Lcode = Work.Lcode AND Login.Name = Work.Sub_Department
//             INNER JOIN (
//                 SELECT EmpNo, MAX(Wages) AS Wages, MAX(OTEligible) AS OTEligible,
//                        LocCode, CompCode
//                 FROM Employee_Mst
//                 GROUP BY EmpNo, LocCode, CompCode
//             ) AS Emp
//                 ON Emp.LocCode = Work.Lcode
//                    AND Emp.CompCode = Work.Ccode
//                    AND Emp.EmpNo = Work.EmpNo
//             WHERE Login.Ccode = '$CompanyCode'
//               AND Login.Lcode = '$LocationCode'
//               AND Login.UserID = '$Login_User'
//               AND Work.Date = '$Date'
//               AND Work.Shift = '$Shift'
//               AND Emp.OTEligible = 'Yes'";

//     $query = $this->db->query($sql);
//     if ($query->num_rows() === 0) {
//         return [];
//     }

//     $employees = $query->result();
//     $employeeIDs = array_map(function($e) {
//         return "'{$e->EmpNo}'";
//     }, $employees);

//     $nextDate     = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
//     $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

//     // Get shift definition: times & windows
//     $Shift_Sql = "SELECT StartTime, EndTime,
//                          StartIN, StartIN_Days, EndIN, EndIN_Days,
//                          StartOUT, StartOUT_Days, EndOUT, EndOUT_Days
//                   FROM Shift_Mst
//                   WHERE CompCode = '$CompanyCode'
//                     AND LocCode = '$LocationCode'
//                     AND ShiftDesc = '$Shift'";
//     $Shift_Data = $this->db->query($Shift_Sql)->row();
//     if (!$Shift_Data) {
//         // handle missing shift definition
//         // you can throw error or return empty
//         return [];
//     }

//     // Build IN time window using StartIN / EndIN
//     $StartIN_Date = (new DateTime($Date))
//                         ->modify("+{$Shift_Data->StartIN_Days} day")
//                         ->format('Y-m-d');
//     $EndIN_Date   = (new DateTime($Date))
//                         ->modify("+{$Shift_Data->EndIN_Days} day")
//                         ->format('Y-m-d');

//     $in_start = "$StartIN_Date {$Shift_Data->StartIN}:00";
//     $in_end   = "$EndIN_Date   {$Shift_Data->EndIN}:00";

//     // Query earliest IN in that window
//     $Sql_IN_All = "SELECT MachineID, MIN(TimeIN) AS TimeIN
//                    FROM LogTime_IN
//                    WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                      AND Compcode = '$CompanyCode'
//                      AND LocCode = '$LocationCode'
//                      AND TimeIN BETWEEN '$in_start' AND '$in_end'
//                    GROUP BY MachineID";
//     $IN_Log = $this->db->query($Sql_IN_All)->result();
//     $IN_Map = [];
//     foreach ($IN_Log as $row) {
//         $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
//     }

//     // Fallback / extra IN for SHIFT3 if regular window has no IN
//     if (strtoupper($Shift) === 'SHIFT3') {
//         // Early catch: from previous day 00:00 up to in_start (or maybe some buffer after)
//         $fallback_start = "$previousDate 00:00:00";
//         $fallback_end   = $in_end;  // or perhaps "$Date {$Shift_Data->StartIN}:00" or slightly after

//         $Sql_Pre_IN = "SELECT MachineID, MIN(TimeIN) AS TimeIN
//                        FROM LogTime_IN
//                        WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                          AND Compcode = '$CompanyCode'
//                          AND LocCode = '$LocationCode'
//                          AND TimeIN BETWEEN '$fallback_start' AND '$fallback_end'
//                        GROUP BY MachineID";
//         $Prev_IN_Log = $this->db->query($Sql_Pre_IN)->result();
//         foreach ($Prev_IN_Log as $row) {
//             if (!isset($IN_Map[$row->MachineID])) {
//                 $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
//             }
//         }
//     }

//     // Build OUT time window using StartOUT / EndOUT
//     $StartOUT_Date = (new DateTime($Date))
//                         ->modify("+{$Shift_Data->StartOUT_Days} day")
//                         ->format('Y-m-d');
//     $EndOUT_Date   = (new DateTime($Date))
//                         ->modify("+{$Shift_Data->EndOUT_Days} day")
//                         ->format('Y-m-d');

//     $out_start = "$StartOUT_Date {$Shift_Data->StartOUT}:00";
//     $out_end   = "$EndOUT_Date   {$Shift_Data->EndOUT}:00";

//     $Sql_OUT_All = "SELECT MachineID, MAX(TimeOUT) AS TimeOUT
//                     FROM LogTime_OUT
//                     WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
//                       AND Compcode = '$CompanyCode'
//                       AND LocCode = '$LocationCode'
//                       AND TimeOUT BETWEEN '$out_start' AND '$out_end'
//                     GROUP BY MachineID";
//     $OUT_Log = $this->db->query($Sql_OUT_All)->result();
//     $OUT_Map = [];
//     foreach ($OUT_Log as $row) {
//         $OUT_Map[$row->MachineID] = new DateTime($row->TimeOUT);
//     }

//     // OT verification / already computed OT hours from table
//     $Date_Check = date('d/m/Y', strtotime($Date));
//     $Sql_Verify_All = "SELECT TokenNo, OTHrs FROM OTHours
//                        WHERE Ccode = '$CompanyCode'
//                          AND Lcode = '$LocationCode'
//                          AND TranDate = '$Date_Check'
//                          AND Update_Status = 'E-Master'
//                          AND Status = '0'
//                          AND TokenNo IN (" . implode(',', $employeeIDs) . ")";
//     $Verify_Log = $this->db->query($Sql_Verify_All)->result();
//     $Verify_Map = [];
//     foreach ($Verify_Log as $row) {
//         $Verify_Map[$row->TokenNo] = $row->OTHrs;
//     }

//     $results = [];

//     foreach ($employees as $rowEmp) {
//         $EmpID = $rowEmp->EmpNo;
//         $EmpName = $rowEmp->FirstName;
//         $ClosingTimeStr = $rowEmp->Updated_Time;
//         $ClosingStatus = $rowEmp->Closing_Status;

//         $INDateTime  = $IN_Map[$EmpID]  ?? null;
//         $OUTDateTime = $OUT_Map[$EmpID] ?? null;

//         $INTime  = $INDateTime  ? $INDateTime->format('H:i') : '';
//         $OUTTime = $OUTDateTime ? $OUTDateTime->format('H:i') : '';

//         // Format closing time
//         $ClosingTime = '';
//         if (!empty($ClosingTimeStr) && $ClosingTimeStr !== '-' && strtotime($ClosingTimeStr) !== false) {
//             $ClosingTime = date('h:i A', strtotime($ClosingTimeStr));
//         }

//         // Total working minutes/hours
//         $Total_Working_Hours = 0.00;
//         $workingMinutes = 0.0;
//         if ($INDateTime && $OUTDateTime) {
//             $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
//             $Total_Working_Hours = round($workingMinutes / 60.0, 2);
//         }

//         // Shift start / end
//         $ShiftStart = new DateTime("$Date {$Shift_Data->StartTime}");
//         $ShiftEnd   = new DateTime("$Date {$Shift_Data->EndTime}");
//         if ($ShiftEnd <= $ShiftStart) {
//             // crosses midnight
//             $ShiftEnd->modify('+1 day');
//         }

//         $shiftMinutes = ($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60.0;

//         $otMinutes = max(0.0, $workingMinutes - $shiftMinutes);

//         // Explicit conversion to int for hours portion
//         $otHoursInt = (int) floor($otMinutes / 60.0);

//         // Remainder minutes (float)
//         $otRemMin = $otMinutes - ($otHoursInt * 60.0);

//         // Determine decimal fraction
//         $minuteDecimal = 0.0;
//         if ($otRemMin >= 11 && $otRemMin <= 20) {
//             $minuteDecimal = 0.25;
//         } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
//             $minuteDecimal = 0.50;
//         } elseif ($otRemMin >= 41) {
//             $minuteDecimal = 1.0;
//         }

//         $decimalOT = round($otHoursInt + $minuteDecimal, 2);

//         // OT closing diff
//         $OT_Closing_Diff = 0;
//         if ($OUTDateTime && !empty($ClosingTimeStr) && strtotime($ClosingTimeStr) !== false) {
//             $ClosingDT = new DateTime($ClosingTimeStr);
//             $diffMins = abs($OUTDateTime->getTimestamp() - $ClosingDT->getTimestamp()) / 60.0;
//             $diffMinsInt = (int) round($diffMins);
//             $OT_Closing_Diff = ($diffMinsInt > 45) ? 1 : 0;
//         }

//         // SHIFT1, SHIFT2 logic: check only certain LogTimeIN / OUT depending on shift
//         // If you need to modify behavior:
//         // For SHIFT1: only use SHIFT1's IN & OUT (already by selecting on Work.Shift = SHIFT1).
//         // For SHIFT2: you said you want to check SHIFT1 & SHIFT2 rows? You can adjust $Sql_IN_All and $Sql_OUT_All before this loop
//         // but here just returning computed values.

//         $results[] = [
//             'Date' => $Date_Check,
//             'Employee_ID' => $EmpID,
//             'Employee_Name' => $EmpName,
//             'In_Time' => $INTime,
//             'Out_Time' => $OUTTime,
//             'E_Master_Closing' => $ClosingTime,
//             'OT_Hour' => $Verify_Map[$EmpID] ?? number_format($decimalOT, 2),
//             'Total_Working_Hours' => number_format($Total_Working_Hours, 2),
//             'OT_Closing_Diff' => $OT_Closing_Diff,
//             'Closing_Status' => $ClosingStatus,
//             'Updated_Status' => isset($Verify_Map[$EmpID]) ? '1' : '0'
//         ];
//     }

//     return $results;
// }



















    public function OT_Details_Entry($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type, $Employee_Id, $Employee_Name, $IN_Time, $IN_Out, $Actual_WHours, $Emaster_UpdatedTime, $Difference, $Final_OTHours, $Supervisor)
    {
        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {


            // Get Employee Info
            $EmployeeQuery = $this->db->query("SELECT * FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND EmpNo = '$Employee_Id' AND IsActive = 'Yes'");
            if ($EmployeeQuery->num_rows() === 0) {
                return array('status' => 'error', 'message' => 'Employee not found or inactive.');
            }

            $Employee = $EmployeeQuery->row();
            $Employee_FirstName = $Employee->FirstName;
            $Employee_Sub_Department = $Employee->DeptName;
            $Employee_Sub_Division = $Employee->SubCatName;
            $Employee_Existing_Code = $Employee->ExistingCode;

            // Get Shift Info
            $shift_Data = $this->db->query("SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'")->row();
            if (!$shift_Data) {
                return array('status' => 'error', 'message' => 'Shift not found.');
            }

            // Adjust date if EndIN_Days is 1
            $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                ? date('Y-m-d', strtotime($Date . ' +1 day'))
                : $Date;

            $End_Date = $Shift_Date_Conversion;
            if ($shift_Data->EndIN_Days == 1) {
                $End_Date = date('Y-m-d', strtotime($Shift_Date_Conversion . ' +1 day'));
            }

            // Fetch Punch IN/OUT
            $Sql_Get = "
            WITH FirstIN AS (
                SELECT MachineID AS Existing_Code, MIN(TimeIN) AS TimeIN
                FROM LogTime_IN
                WHERE CONVERT(date, TimeIN, 103) = '$Shift_Date_Conversion'
                AND MachineID = '$Employee_Existing_Code'
                GROUP BY MachineID
            ),
            LastOUT AS (
                SELECT MachineID AS Existing_Code, MAX(TimeOUT) AS TimeOUT
                FROM LogTime_OUT
                WHERE CONVERT(date, TimeOUT, 103) = '$End_Date'
                AND MachineID = '$Employee_Existing_Code'
                GROUP BY MachineID
            )
            SELECT fi.Existing_Code, fi.TimeIN, lo.TimeOUT
            FROM FirstIN fi
            LEFT JOIN LastOUT lo ON lo.Existing_Code = fi.Existing_Code";

            $Punching_Timings = $this->db->query($Sql_Get)->row();


            $Date = date('d/m/Y', strtotime($Date));

            // Check for duplicate entry
            $Sql_Dup = "SELECT * FROM OTHours
                        WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
                        AND TokenNo = '$Employee_Id' AND ShiftName = '$Shift' AND TranDate = '$Date' AND Update_Status = 'E-Master'";
            $Sql_Dup_Query = $this->db->query($Sql_Dup);



            if ($Sql_Dup_Query->num_rows() > 0) {
                return array('status' => 'error', 'message' => 'Attendance has already been updated. Please contact the administrator.');
            }

            // Insert data
            $Manual_OT_Entry = array(
                'CCode'         => $CompanyCode,
                'LCode'         => $LocationCode,
                'TranDate'      => $Date,
                'ShiftName'     => $Shift,
                'EmpName'       => $Employee_FirstName,
                'Dept'          => $Employee_Sub_Department,
                'TokenNo'       => $Employee_Id,
                'InTime'        => $IN_Time,
                'OutTime'       => $IN_Out,
                'OTHrs'         => $Final_OTHours,
                'Status'        => '0',
                'Subsection'    => $Employee_Sub_Division,
                'Update_Status' => 'E-Master',
                'CreateOn'      => date('Y-m-d H:i:s'),
                'CreateBy'      => $Supervisor,
            );

            $this->db->insert('OTHours', $Manual_OT_Entry);

            if ($this->db->affected_rows() > 0) {
                return array('status' => 'success', 'message' => 'Attendance recorded successfully');
            } else {
                return array('status' => 'error', 'message' => 'Failed to record attendance');
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function No_Work_Employees($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    {

        $Sql = "SELECT
                Work.EmpNo,
                Work.Closing_Status,
                Work.FirstName,
                Work.Updated_Time
            FROM Web_Employee_Work_Allocation_Mst AS Work
            INNER JOIN UserDetails_Det AS Login
                ON Login.Lcode = Work.Lcode
                AND Login.Name = Work.Sub_Department
            WHERE
                Login.Ccode = '$CompanyCode'
                AND Login.Lcode = '$LocationCode'
                AND Login.UserID = '$Login_User'
                AND Work.Date = '$Date'
                AND Work.Shift = '$Shift'
                AND Work.Work_Status = '1'
                AND Work.Assign_Status = '0'
                AND Closing_Status = '1'
                AND Work.Work_Type = 'NoWork'";


        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            $Details = $Query->result();
            $Data = [];

            $Sql_Shift = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $Shift_Data = $this->db->query($Sql_Shift)->row();

            $serial = 1;

            foreach ($Details as $Detail) {

                $UpdatedTime = $Detail->Updated_Time;
                $Employee_ID = $Detail->EmpNo;
                $Shift_Date_Convert = $Date;


                if ($Shift_Data) {
                    $Shift_Date_Conversion = ($Shift_Data->StartIN_Days == 1 && $Shift_Data->EndIN_Days == 1)
                        ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                        : $Shift_Date_Convert;
                } else {
                    $Shift_Date_Conversion = $Shift_Date_Convert;
                }

                $Sql_Punch_IN = "SELECT FORMAT(MIN(TimeIN), 'HH:mm') AS TimeIN
                             FROM LogTime_IN
                             WHERE CompCode = '$CompanyCode'
                               AND LocCode = '$LocationCode'
                               AND MachineID = '$Employee_ID'
                               AND CAST(TimeIN AS DATE) = '$Shift_Date_Conversion'";

                $Sql_Punch_OUT = "SELECT FORMAT(MAX(TimeOUT), 'HH:mm') AS TimeOUT
                              FROM LogTime_OUT
                              WHERE CompCode = '$CompanyCode'
                                AND LocCode = '$LocationCode'
                                AND MachineID = '$Employee_ID'
                                AND CAST(TimeOUT AS DATE) = '$Shift_Date_Conversion'";

                $PunchIn = $this->db->query($Sql_Punch_IN)->row();
                $PunchOut = $this->db->query($Sql_Punch_OUT)->row();

                $TimeIN = $PunchIn && $PunchIn->TimeIN ? $PunchIn->TimeIN : '';
                $TimeOUT = $PunchOut && $PunchOut->TimeOUT ? $PunchOut->TimeOUT : '';

                $WorkingHours = '0';
                $TotalWorkingFormatted = '';
                if ($TimeIN && $TimeOUT) {
                    $in = new DateTime($TimeIN);
                    $out = new DateTime($TimeOUT);
                    if ($out < $in) {
                        $out->modify('+1 day');
                    }
                    $diff = $in->diff($out);
                    $minutes = $diff->h * 60 + $diff->i;
                    $WorkingHours = ($diff->i >= 30) ? $diff->h + 1 : $diff->h;
                    $TotalWorkingFormatted = sprintf('%d:%02d', $diff->h, $diff->i);
                }

                $UpdatedTime = (!empty($Partial_Close_Time) && $Partial_Close_Time !== '-') ? substr($Partial_Close_Time, 11, 5) : '';
                $Difference = '';
                $Difference_Status = 3;

                if ($UpdatedTime && $TimeOUT) {
                    try {
                        $updated = new DateTime($UpdatedTime);
                        $last = new DateTime($TimeOUT);
                        $interval = $updated->diff($last);
                        $totalMin = abs($interval->h * 60 + $interval->i);
                        $hours = floor($totalMin / 60);
                        $mins = $totalMin % 60;
                        $Difference = sprintf('%02d:%02d', $hours, $mins);
                        $Difference_Status = ($totalMin >= 45) ? 1 : 0;
                    } catch (Exception $e) {
                        $Difference = '';
                        $Difference_Status = 3;
                    }
                }

                $ClosingStatus = ($Detail->Closing_Status == '1') ? 'Closed' : 'Not Closed';
                $Verify = $WorkingHours;

                $Data[] = [
                    'Serial'       => $serial,
                    'EmpNo'             => $Detail->EmpNo,
                    'Employee_Name'     => $Detail->FirstName,
                    'Status'            => $ClosingStatus,
                    'IN_Time'           => $TimeIN,
                    'IN_OUT'            => $TimeOUT,
                    'W_Hours'           => $WorkingHours,
                    'Updated_Time'      => $UpdatedTime,
                ];

                $serial++;
            }

            return $Data;
        } else {
            return 0;
        }
    }

    public function No_Work_Employees_Update($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type, $Employee_Id, $Employee_Name, $IN_Time, $IN_Out, $Attendance, $Supervisor)
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            // print_r($Shift);


            // $Shift_Array = ['SHIFT1', 'SHIFT2', 'SHIFT3'];

            // $currentIndex = array_search($Shift, $Shift_Array);
            // $previousIndex = ($currentIndex - 1 + count($Shift_Array)) % count($Shift_Array);
            // $Shift = $Shift_Array[$previousIndex];


            $Sql = "SELECT * FROM Employee_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND EmpNo = '$Employee_Id' AND IsActive = 'Yes'";
            $Query = $this->db->query($Sql);

            if ($Query->num_rows() > 0) {

                $Employee = $Query->row();

                $Employee_Wages = $Employee->Wages;
                $Employee_Sub_Division = $Employee->SubCatName;
                $Employee_Department = $Employee->DeptGrp;
                $Employee_Sub_Department = $Employee->DeptName;
                $Employee_FirstName = $Employee->FirstName;
                $Employee_Existing_Code = $Employee->ExistingCode;
                $Employee_Postion = $Employee->WorkArea;
                $Employee_Postion_ID = $Employee->JObCardNo;

                $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
                $shift_Data = $this->db->query($sql1)->row();

                if ($shift_Data) {
                    $Shift_Pounch_Start = $shift_Data->StartIN;
                    $Shift_Pounch_End = $shift_Data->EndIN;
                    $StartTime = $shift_Data->StartTime;
                    $EndTime = $shift_Data->EndTime;
                    $Shift_Date_Convert = $Date;

                    $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                        ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 day'))
                        : $Shift_Date_Convert;

                    $Start_Date = date('Y-m-d', strtotime($Shift_Date_Convert . ' ' . $Shift_Pounch_Start));
                    $End_Date = date('Y-m-d', strtotime($Shift_Date_Convert . ' ' . $Shift_Pounch_End));

                    if ($shift_Data->EndIN_Days == 1) {
                        $End_Date = date('Y-m-d', strtotime($End_Date . ' +1 day'));
                    }
                }



                $Sql_Dup = "SELECT * FROM Web_Manual_Attendance_Mst
                        WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
                        AND Employee_ID = '$Employee_Id' AND Shift = '$Shift' AND Date = '$Date'";
                $Query_Dup = $this->db->query($Sql_Dup);

                if ($Query_Dup->num_rows() > 0) {
                    return array('status' => 'error', 'message' => 'Attendance has already been updated. Please contact the administrator.');
                } else {
                    $Manual_Attendance_Entry = array(
                        'Ccode' => $CompanyCode,
                        'Lcode' => $LocationCode,
                        'Department' => $Employee_Department,
                        'Sub_Department' => $Employee_Sub_Department,
                        'Wages' => $Employee_Wages,
                        'Sub_Division' => $Employee_Sub_Division,
                        'Postion' => $Employee_Postion,
                        'Postion_ID' => $Employee_Postion_ID,
                        'Employee_Id' => $Employee_Id,
                        'Existing_Code' => $Employee_Existing_Code,
                        'Employee_Name' => $Employee_FirstName,
                        'Date' => $Date,
                        'Shift' => $Shift,
                        'Starting_Date' => $Start_Date,
                        'Ending_Date' => $End_Date,
                        'Working_Type' => '',
                        'Attendance_Status' => 'Absent',
                        'TimeIN' => $IN_Time,
                        'TimeOUT' => $IN_Out,
                        'Manual_TimeIN' => $IN_Time,
                        'Manual_TimeOUT' => $IN_Out,
                        'From_Time' => $StartTime,
                        'To_Time' => $EndTime,
                        'Actual_Working_Duration' => '0',
                        'Total_Working_Duration' => '0',
                        'Total_OT_Hours' => '0',
                        'Approved_Status' => '0',
                        'Input_Type' => 'E-Master',
                        'TMS_Status' => 'Not-Approved',
                        'Supervisor' => $Supervisor,
                        'ApprovedBy' => '',
                        'ApprovedTime' => '',
                        'CreatedBy' => $Login_User,
                        'CreatedTime' => date('Y-m-d H:i:s'),
                        'UpdatedBy' => '-',
                        'UpdatedTime' => '-'
                    );


                    $this->db->insert('Web_Manual_Attendance_Mst', $Manual_Attendance_Entry);

                    if ($this->db->affected_rows() > 0) {
                        return array('status' => 'success', 'message' => 'Attendance recorded successfully');
                    } else {
                        return array('status' => 'error', 'message' => 'Failed to record attendance');
                    }
                }
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }
}
