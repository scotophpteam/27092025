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


    public function Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            $current_time = '2025-01-22 18:00:49.000';
            // $Date = '2025-01-22';


            $sql2 = "WITH EmployeePunches AS (
                SELECT
                    Time.MachineID,
                    Emp.FirstName,
                    Emp.Wages,
                    Emp.WorkArea,
                    Emp.JobCardNo,
                    Emp.DeptName,
                    Emp.DeptGrp,
                    Emp.SubSection_Name,
                    Time.TimeOUT,
                    ROW_NUMBER() OVER (PARTITION BY Emp.MachineID ORDER BY Time.TimeOUT ASC) AS RowNum
                FROM
                    UserDetails_Det Log
                INNER JOIN
                    Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                INNER JOIN
                    LogTimeLunch_OUT Time ON Time.MachineID = Emp.MachineID
                WHERE
                    Log.UserID = 'pappl'
                    AND CONVERT(DATE, Time.TimeOUT) = '$Date'
                    AND Emp.CatName != 'STAFF'
                    AND Time.CompCode = '$CompanyCode'
                    AND Time.LocCode = '$LocationCode'
                    AND Emp.IsActive = 'Yes'
                    AND Time.TimeOUT BETWEEN
                        DATEADD(HOUR, -3, '$current_time') AND '$current_time'
            )
            SELECT
                MachineID,
                FirstName,
                Wages,
                WorkArea,
                JobCardNo,
                DeptName,
                DeptGrp,
                SubSection_Name,
                TimeOUT
            FROM
                EmployeePunches
            WHERE
                RowNum = 1";


            $log_Data = $this->db->query($sql2)->result();


            $current_time = date('Y-m-d H:i:s');

            foreach ($log_Data as $Employee_Data) {
                $Employee_Id = $Employee_Data->MachineID;
                $Employee_WorkArea = $Employee_Data->WorkArea;
                $Employee_Department = $Employee_Data->DeptName;

                $existing_sql = "SELECT * FROM Web_Extra_Work_Allocation_Mst
                            WHERE Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1'";

                if ($this->db->query($existing_sql)->num_rows() == 0) {

                    $Work_Check_Sql = "SELECT * FROM Web_Work_Area_Mst Work
                                INNER JOIN Web_Machine_Mst Machine
                                    ON Work.Ccode = Machine.Ccode
                                    AND Work.Lcode = Machine.Lcode
                                    AND Work.WorkArea = Machine.WorkArea
                                    AND Work.Department = Machine.Department
                                WHERE Machine.WorkArea = '$Employee_WorkArea'
                                    AND Work.Department = '$Employee_Department'";

                    $Work_Check_Query = $this->db->query($Work_Check_Sql);
                    $Work_Check_Rows = $Work_Check_Query->num_rows();

                    $allocations = [
                        'Ccode' => $CompanyCode,
                        'Lcode' => $LocationCode,
                        'Wages' => $Employee_Data->Wages,
                        'FirstName' => $Employee_Data->FirstName,
                        'EmpNo' => $Employee_Id,
                        'ExistingCode' => $Employee_Id,
                        'Shift' => '-',
                        'Date' => $Date,
                        'Job_Card_No' => $Employee_Data->JobCardNo,
                        'Department' => $Employee_Data->DeptGrp,
                        'Sub_Department' => $Employee_Data->DeptName,
                        'Sub_Section' => $Employee_Data->SubSection_Name,
                        'WorkArea' => $Employee_Data->WorkArea,
                        'Previous_Shift' => '-',
                        'OT_Confirmation' => '-',
                        'Working_Type' => 'EXTRA',
                        'Machine_Id' => '',
                        'Machine_Name' => '-',
                        'FrameType' => '-',
                        'Frame' => ($Work_Check_Rows == 0) ? 'Others' : '-',
                        'Type' => $Type,
                        'Screen_Type' => 'Shift_Employee_Screen',
                        'Work_Type' => ($Work_Check_Rows == 0) ? 'Others' : '-',
                        'Status_Updated' => ($Work_Check_Rows == 0) ? 'Others' : '-',
                        'Work_Start' => '-',
                        'Work_End' => '-',
                        'Work_Duration' => '-',
                        'Machine_EB_No' => '-',
                        'Work_Status' => '1',
                        'Assign_Status' => ($Work_Check_Rows == 0) ? '1' : '0',
                        'Closing_Status' => '0',
                        'IsWork' => '0',
                        'Edit_Reason' => '-',
                        'Description' => $Employee_Data->WorkArea,
                        'Created_By' => $Login_User,
                        'Created_Time' => $current_time,
                        'Updated_By' => '-',
                        'Updated_Time' => '-',
                    ];

                    $this->db->insert('Web_Extra_Work_Allocation_Mst', $allocations);
                }
            }

            $NoWork_Sql = "UPDATE Work
                            SET
                                Work.Sub_Department = Login.Name,
                                Work.WorkArea = '',
                                Work.Job_Card_No = ''
                            FROM Web_Extra_Work_Allocation_Mst AS Work
                            INNER JOIN UserDetails_Det AS Login
                                ON Work.Lcode = Login.Lcode
                                AND Work.Ccode = Login.Ccode
                            WHERE
                                Work.Date = '$Date'
                                AND Work.Work_Type = 'NoWork'
                                AND Work.Work_Status = '1'
                                AND Work.Assign_Status = '0'
                                AND Work.Lcode = '$LocationCode'
                                AND Login.Ccode = '$CompanyCode'
                                AND Login.UserID = '$Login_User'";

            $this->db->query($NoWork_Sql);

            $sql4 = "SELECT Work.Type AS Type,
                        Work.*,
                        CASE
                            WHEN Work.Assign_Status = '0' THEN 'Unassigned'
                            WHEN Work.Work_Type = 'NoWork' THEN 'NoWork'
                            ELSE 'Assigned'
                        END AS WorkStatus
                     FROM Web_Extra_Work_Allocation_Mst AS Work
                     INNER JOIN UserDetails_Det AS Login
                         ON Login.Lcode = Work.Lcode
                         AND Login.Name = Work.Sub_Department
                     WHERE Login.UserID = '$Login_User'
                       AND Work.Date = '$Date'
                       AND Work.Work_Status = '1'";

            $employee_data = $this->db->query($sql4)->result();
            $Un_Assigned_Data = [];
            $Assigned_Data = [];
            $No_Work_Data = [];

            foreach ($employee_data as $employee) {
                if ($employee->WorkStatus == 'Unassigned') {
                    $Un_Assigned_Data[] = $employee;
                } elseif ($employee->WorkStatus == 'Assigned') {
                    $Assigned_Data[] = $employee;
                } else {
                    $No_Work_Data[] = $employee;
                }
            }

            $NoWork_Employee_Sql1 = "SELECT Work.Type AS Type, Work.*
                        FROM Web_Extra_Work_Allocation_Mst AS Work
                        INNER JOIN UserDetails_Det AS Login
                            ON Login.Lcode = Work.Lcode
                            AND Login.Name = Work.Sub_Department
                        WHERE Login.UserID = '$Login_User'
                          AND Work.Date = '$Date'
                          AND Work.Work_Type = 'NoWork'
                          AND Work.Work_Status = '1'
                          AND Work.Assign_Status = '0'";

            $user_dept_sql = "SELECT Name FROM UserDetails_Det
                                      WHERE UserID = '$Login_User'
                                      AND Ccode = '$CompanyCode'
                                      AND Lcode = '$LocationCode'";
            $user_dept = $this->db->query($user_dept_sql)->row();
            $deptName = $user_dept ? $user_dept->Name : '';

            if ($deptName == 'HRD' || $deptName == 'Human Resource Services') {
                $NoWorkEmployeeList1 = $this->db->query($NoWork_Employee_Sql1)->result();
                $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data);
                return $All_Employee_List;
            } else {
                $NoWorkEmployeeList1 = $this->db->query($NoWork_Employee_Sql1)->result();
                $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data, $NoWorkEmployeeList1);
                return $All_Employee_List;
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

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

        // $Date = '2025-01-22';




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
                                'Shift' => '',
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
                        $Sub_Department === 'Spinning-Prod' || $Sub_Department === 'Finishing-Prod' || $Sub_Department == 'SPINNING-PROD' || $Sub_Department == 'FINISHING-PROD' ||  $Sub_Department === 'FINISHING - PM1' ||  $Sub_Department === 'FINISHING - PM2' ||  $Sub_Department === 'SPINNING - PM1' ||  $Sub_Department === 'SPINNING - PM2'
                        ||  $Sub_Department === 'Finishing - PM1' ||  $Sub_Department === 'Finishing - PM2'  ||  $Sub_Department === 'Spinning - PM1' ||  $Sub_Department === 'Spinning - PM2' || $Sub_Department === 'Preparatory-Prod' || $Sub_Department === 'PREPARATORY-PROD' || $Sub_Department == 'TFO'
                    ) {

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



                            $existing_combination = $this->db->query("SELECT * FROM Web_Extra_Work_Allocation_Mst WHERE ' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Machine_Id = '$Machine_datas' AND Frame = '$Frame_Data' AND Assign_Status = '1'");

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

                // $Date = '2025-01-22';

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

                    if (
                        $Sub_Department === 'Spinning-Prod' || $Sub_Department === 'Finishing-Prod' || $Sub_Department == 'SPINNING-PROD' || $Sub_Department == 'FINISHING-PROD' ||  $Sub_Department === 'FINISHING - PM1' ||  $Sub_Department === 'FINISHING - PM2' ||  $Sub_Department === 'SPINNING - PM1' ||  $Sub_Department === 'SPINNING - PM2'
                        ||  $Sub_Department === 'Finishing - PM1' ||  $Sub_Department === 'Finishing - PM2'  ||  $Sub_Department === 'Spinning - PM1' ||  $Sub_Department === 'Spinning - PM2' || $Sub_Department === 'Preparatory_Prod' || $Sub_Department === 'PREPARATORY' || $Sub_Department == 'TFO'
                    ) {


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
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        // $Date = '2025-01-22';

        $All_Employee_Close_Check_Sql = "SELECT DISTINCT Work.EmpNo,Work.Sub_Department,Work.WorkArea,Work.Job_Card_No,Work.EmpNo,Work.FirstName FROM Web_Extra_Work_Allocation_Mst Work
                                          INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                         AND Login.Ccode = Work.Ccode
                                         AND Login.Name = Work.Sub_Department
                                         WHERE login.UserID = '$Login_User'
                                         AND Work.Date = '$Date'
                                         AND Work.WorK_Status = '1'
                                          AND Work.Closing_Status = '0'";

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
            // $Date = '2025-01-22'; // Consider parameterizing this if it's dynamic

            foreach ($inputData['Employees'] as $Details) {

                $Employee_Id = $Details['EmployeeId'];
                $Sub_Department = $Details['Department'];
                $WorkArea = $Details['Work_Area'];
                $jobCardNo = $Details['JobCardNo'];
                $OTStatus = $Details['OTConfirm'];
                $Supervisor_Name = $Details['Supervisor_Name'];

                if ($OTStatus == 1) {

                    // Check if the extra work allocation exists
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
                            'Shift' => '-',
                            'Work_Status' => '1'
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


    public function Get_OT_Extra_Hours_List_Employee($CompanyCode, $LocationCode, $Login_User, $Date, $Type)
    {

        if ($Type == 'EXTRA') {

            $current_Date = date('Y/m/d');

            // $Date = '2025-01-22';

            $Sql = "WITH PunchTimes AS (
    SELECT
        Work.EmpNo,
        Work.WorkArea,
        TRY_CONVERT(DATETIME, Work.Updated_Time, 120) AS Updated_Time,
        Work.Closing_Status,
        Work.FirstName,
        Time.TimeOUT,
        ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT ASC) AS RowAsc,
        ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT DESC) AS RowDesc
    FROM
        Web_Extra_Work_Allocation_Mst Work
    INNER JOIN
        UserDetails_Det Login ON Login.Lcode = Work.Lcode
        AND Login.Ccode = Work.Ccode
        AND Login.Name = Work.Sub_Department
    INNER JOIN
        LogTimeLunch_OUT AS Time ON Time.MachineID = Work.EmpNo
        AND TRY_CONVERT(DATE, Time.TimeOUT, 120) = '$Date'
    WHERE
        Login.UserID = '$Login_User'
        AND TRY_CONVERT(DATE, Work.Date, 120) = '$Date'
        AND Work.Ccode = '$CompanyCode'
        AND Work.Lcode = '$LocationCode'
        AND Work.Work_Status = '1'
        AND Work.Assign_Status = '1'
        AND (Work.Closing_Status = '1' OR Work.Closing_Status = '0')
),
OnDutyStatus AS (
    SELECT
        DISTINCT TokenNo,
        1 AS Updated_Status
    FROM
        OnDuty_Mst
    WHERE
        TRY_CONVERT(DATE, Entry_Date, 120) = '$current_Date'
)
SELECT
    p.EmpNo,
    p.WorkArea,
    CONVERT(VARCHAR(5), p.Updated_Time, 108) AS Updated_Time,
    p.Closing_Status,
    p.FirstName,
    CONVERT(VARCHAR(5), MIN(p.TimeOUT), 108) AS FirstPunchIn,
    CONVERT(VARCHAR(5), MAX(p.TimeOUT), 108) AS LastPunchOut,
    CASE
        WHEN MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END) IS NOT NULL
             AND MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END) IS NOT NULL
        THEN
            CONVERT(VARCHAR(5),
                DATEDIFF(SECOND,
                    MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END),
                    MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END)) / 3600
            ) + ':' +
            RIGHT('0' + CONVERT(VARCHAR(2),
                (DATEDIFF(SECOND,
                    MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END),
                    MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END)) % 3600) / 60
            ), 2)
        ELSE 'Invalid Time'
    END AS TotalWorkingHours,
    ISNULL(ods.Updated_Status, 0) AS Updated_Status
FROM
    PunchTimes p
LEFT JOIN
    OnDutyStatus ods ON p.EmpNo = ods.TokenNo
GROUP BY
    p.EmpNo, p.WorkArea, p.Updated_Time, p.Closing_Status, p.FirstName, ods.Updated_Status;
";


            // echo '<pre>';
            // print_r($Sql);
            // exit();

            $Query = $this->db->query($Sql);

            if ($Query->num_rows() > 0) {
                return $Query->result();
            } else {
                return 0;
            }
        }
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
                }
            }
        }
        return 1;
    }


    public function OT_Employee_Details($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
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
                AND Work.Assign_Status = '1'
                AND Work.Working_Type = 'OT'";

        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            $Details = $Query->result();
            $Data = [];

            $Sql_Shift = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $Shift_Data = $this->db->query($Sql_Shift)->row();

            $serial = 1;

            foreach ($Details as $Detail) {
                $Employee_ID = $Detail->EmpNo;
                $Shift_Date_Convert = $Date;

                // ✅ Safely handle Updated_Time
                $Partial_Close_Time = $Detail->Updated_Time;
                if (!empty($Partial_Close_Time) && $Partial_Close_Time !== '-') {
                    try {
                        $time = new DateTime($Partial_Close_Time);
                        $Partial_Close_Time_Final = $time->format('H:i');
                    } catch (Exception $e) {
                        $Partial_Close_Time_Final = '';
                    }
                } else {
                    $Partial_Close_Time_Final = '';
                }

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
                    'Employee Id'       => $serial,
                    'EmpNo'             => $Detail->EmpNo,
                    'Employee Name'     => $Detail->FirstName,
                    'Status'            => $ClosingStatus,
                    'IN Time'           => $TimeIN,
                    'IN OUT'            => $TimeOUT,
                    'Updated_Time'      => $Partial_Close_Time_Final,
                    'W.Hours'           => $WorkingHours,
                    'Diffrence'         => $Difference,
                    'Diffrence_Status'  => $Difference_Status,
                    'Verify'            => $Verify
                ];

                $serial++;
            }

            return $Data;
        } else {
            return 0;
        }
    }


    public function OT_Details_Entry($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type, $Employee_Id, $Employee_Name, $IN_Time, $IN_Out, $Actual_WHours, $Emaster_UpdatedTime, $Difference, $Final_OTHours, $Supervisor)
    {


        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {


            $Shift_Array = ['SHIFT1', 'SHIFT2', 'SHIFT3'];

            $currentIndex = array_search($Shift, $Shift_Array);
            $previousIndex = ($currentIndex - 1 + count($Shift_Array)) % count($Shift_Array);
            $Shift = $Shift_Array[$previousIndex];


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

                $Sql_Get = "WITH FirstIN AS (
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
                        SELECT
                            fi.Existing_Code,
                            fi.TimeIN,
                            lo.TimeOUT
                        FROM
                            FirstIN fi
                        LEFT JOIN
                            LastOUT lo ON lo.Existing_Code = fi.Existing_Code";


                // print_r($Sql_Get);exit;

                $Query_Get_Punch = $this->db->query($Sql_Get);
                $Punching_Timings = $Query_Get_Punch->result();

                $TimeIN = null;
                $TimeOUT = null;
                $Manual_TimeOUT = null;

                if (!empty($Punching_Timings) && isset($Punching_Timings[0])) {
                    $TimeIN = $Punching_Timings[0]->TimeIN;
                    $TimeOUT = $Punching_Timings[0]->TimeOUT;

                    if ($TimeIN) {
                        $startTimestamp = strtotime($TimeIN);
                        $totalSeconds = floatval($Final_OTHours) * 3600;
                        $manualTimeOutTimestamp = $startTimestamp + $totalSeconds;
                        $Manual_TimeOUT = date("Y-m-d H:i:s", $manualTimeOutTimestamp);
                    }
                }

                $Actual_Working_Duration = 0;

                if (!empty($TimeIN) && !empty($TimeOUT)) {
                    $in = new DateTime($TimeIN);
                    $out = new DateTime($TimeOUT);

                    if ($out < $in) {
                        $out->modify('+1 day');
                    }

                    $interval = $in->diff($out);
                    $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);

                    $fullHours = floor($hours);
                    $minutes = round(($hours - $fullHours) * 60); // round to nearest minute

                    if ($minutes >= 45) {
                        // Round up to next full hour
                        $Actual_Working_Duration = number_format($fullHours + 1, 2, '.', '');
                    } else {
                        // Show as H.MM (e.g., 7.30 for 7h 18m)
                        $Actual_Working_Duration = floatval("{$fullHours}." . str_pad($minutes, 2, '0', STR_PAD_LEFT));
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
                        'Working_Type' => 'Both',
                        'Attendance_Status' => 'Present',
                        'TimeIN' => $TimeIN,
                        'TimeOUT' => $TimeOUT,
                        'Manual_TimeIN' => $TimeIN,
                        'Manual_TimeOUT' => $Manual_TimeOUT,
                        'From_Time' => $StartTime,
                        'To_Time' => $EndTime,
                        'Actual_Working_Duration' => $Actual_Working_Duration,
                        'Total_Working_Duration' => $Actual_WHours,
                        'Total_OT_Hours' => $Final_OTHours,
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
