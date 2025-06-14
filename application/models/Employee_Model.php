<?php

use PhpParser\Builder\Function_;

if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Employee_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type)
    {
        if ($Punching_Type == 'SHIFT' || $Punching_Type == 'Shift') {

            $Session = $this->session->userdata('sess_array');

            if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

                $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
                $shift_Data = $this->db->query($sql1)->row();

                if ($shift_Data) {

                    $Shift_Pounch_Start = $shift_Data->StartIN;
                    $Shift_Pounch_End = $shift_Data->EndIN;
                    $Shift_Date_Convert = $Date;

                    $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                        ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                        : $Shift_Date_Convert;

                    $sql2 = "SELECT DISTINCT
                                Time.MachineID, Emp.FirstName, Emp.Wages, Emp.WorkArea, Emp.JobCardNo, Emp.DeptName, Emp.DeptGrp, Emp.SubSection_Name , Get_Type = 'SHIFT'
                            FROM UserDetails_Det Log
                            INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode
                            AND Log.Name = Emp.DeptName
                            INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                            WHERE Log.UserID = '$Login_User'
                            AND CONVERT(DATE, Time.TimeIN) = '$Shift_Date_Conversion'
                            AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start' AND '$Shift_Date_Conversion $Shift_Pounch_End'
                            AND Emp.CatName != 'STAFF'
                            AND Time.CompCode = '$CompanyCode'
                            AND Time.LocCode = '$LocationCode'
                            AND Emp.IsActive = 'Yes'";

                    $log_Data = $this->db->query($sql2)->result();


                    if ($this->db->query($sql2)->num_rows() > 0) {

                        return  $log_Data;
                    } else {

                        return 0;
                    }
                }
            }
        } else if ($Punching_Type == 'LATE' || $Punching_Type == 'Late') {

            $sql = "SELECT * FROM Shift_Mst
                    WHERE CompCode = '$CompanyCode'
                    AND LocCode = '$LocationCode'
                    AND ShiftDesc = '$Shift'";

            $shift_Data = $this->db->query($sql)->row();

            if ($shift_Data) {

                $Shift_Pounch_Start = $shift_Data->StartTime;
                $Shift_Pounch_End = $shift_Data->EndTime;

                $From_Shift_Date_Convert = $Date;
                $To_Shift_Date_Convert = $Date;

                if ($shift_Data->StartIN_Days == 1) {
                    $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
                }
                if ($shift_Data->EndOUT_Days == 1) {
                    $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
                }

                $sql2 = "SELECT DISTINCT
                            Time.MachineID, Emp.FirstName, Emp.WorkArea, Emp.DeptName, Emp.DeptGrp,
                            Emp.SubSection_Name, Emp.Wages, Emp.JobCardNo , Get_Type = 'LATE'
                        FROM UserDetails_Det Log
                        INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                        INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                        WHERE Log.UserID = '$Login_User'
                        AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
                        AND Emp.CatName != 'STAFF'
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
                            AND Shift = '$Shift'
                            AND Date = '$Date'
                        )";

                $log_Data = $this->db->query($sql2)->result();

                if ($this->db->query($sql2)->num_rows() > 0) {

                    return $log_Data;
                } else {

                    return 0;
                }
            }
        }
    }


    public function Shift_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $shift_Data = $this->db->query($sql1)->row();

            // if ($shift_Data) {

            //     $Shift_Pounch_Start = $shift_Data->StartIN;
            //     $Shift_Pounch_End = $shift_Data->EndIN;
            //     $Shift_Date_Convert = $Date;

            //     $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
            //         ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
            //         : $Shift_Date_Convert;

            //     $sql2 = "SELECT DISTINCT
            //                 Time.MachineID, Emp.FirstName
            //             FROM UserDetails_Det Log
            //             INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode
            //             AND Log.Name = Emp.DeptName
            //             INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
            //             WHERE Log.UserID = '$Login_User'
            //             AND CONVERT(DATE, Time.TimeIN) = '$Shift_Date_Conversion'
            //             AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start' AND '$Shift_Date_Conversion $Shift_Pounch_End'
            //             AND Emp.CatName != 'STAFF'
            //             AND Time.CompCode = '$CompanyCode'
            //             AND Time.LocCode = '$LocationCode'
            //             AND Emp.IsActive = 'Yes'";

            //     $log_Data = $this->db->query($sql2)->result();


            //     if ($this->db->query($sql2)->num_rows() > 0) {

            //         return  $log_Data;
            //     } else {

            //         return 0;
            //     }
            // }

            $Sql = "SELECT DISTINCT Work.EmpNo AS MachineID,Work.FirstName FROM Web_Employee_Work_Allocation_Mst Work
                                          INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                         AND Login.Ccode = Work.Ccode
                                         AND Login.Name = Work.Sub_Department
                                         WHERE login.UserID = '$Login_User'
                                         AND Work.Date = '$Date'
                                         AND Work.Shift = '$Shift'
                                         AND Work.WorK_Status = '1'
                                         AND Work.Assign_Status = '1'
                                          AND Work.Closing_Status = '1'
                                          AND Work.Ccode = '$CompanyCode'
                                          AND Work.Lcode = '$LocationCode'";

            $log_Data = $this->db->query($Sql)->result();


            $Sql_No = "SELECT DISTINCT Work.EmpNo AS MachineID,Work.FirstName FROM Web_Employee_Work_Allocation_Mst Work
                                        INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                       AND Login.Ccode = Work.Ccode
                                       AND Login.Name = Work.Sub_Department
                                       WHERE login.UserID = '$Login_User'
                                       AND Work.Date = '$Date'
                                       AND Work.Shift = '$Shift'
                                       AND Work.WorK_Status = '1'
                                       AND Work.Assign_Status = '0'
                                        AND Work.Closing_Status = '1'
                                        AND Work.Ccode = '$CompanyCode'
                                        AND Work.Lcode = '$LocationCode'";

            // print_r($Sql_No);
            // exit;

            $log_Data_No = $this->db->query($Sql_No)->result();

            if ($this->db->query($Sql)->num_rows() > 0) {

                $Compined = array_merge($log_Data, $log_Data_No);

                return  $Compined;
            } else {

                return 0;
            }
        }
    }

    public function Manual_Attendance_Entry($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Working_Type, $Employee_Id, $Punching_Type, $From_Time, $To_Time, $Total_Working_Hour, $Total_OT_Hour, $Supervisor)
    {
        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

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
                        $totalSeconds = floatval($Total_Working_Hour) * 3600;
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
                        'Working_Type' => $Working_Type,
                        'Attendance_Status' => $Punching_Type,
                        'TimeIN' => $TimeIN,
                        'TimeOUT' => $TimeOUT,
                        'Manual_TimeIN' => $TimeIN,
                        'Manual_TimeOUT' => $Manual_TimeOUT,
                        'From_Time' => $From_Time,
                        'To_Time' => $To_Time,
                        'Actual_Working_Duration' => $Actual_Working_Duration,
                        'Total_Working_Duration' => $Total_Working_Hour,
                        'Total_OT_Hours' => $Total_OT_Hour,
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









    public function Get_Punching_List($CompanyCode, $LocationCode, $Login_User, $From_Date, $Enter_Shift)
    {
        $To_Date = date('Y-m-d', strtotime($From_Date . ' +1 day'));


        $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Enter_Shift'";
        $shift_Data = $this->db->query($sql1)->row();

        if ($shift_Data) {

            $Shift_Pounch_Start = $shift_Data->StartIN;
            $Shift_Pounch_End = $shift_Data->EndIN;


            $Shift_Date_Convert = $From_Date;

            $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                : $Shift_Date_Convert;
            $From_Date = $Shift_Date_Conversion;


            $Shifts = [
                'PRECOT - A' => [
                    'SHIFT1' => ['07:00', '10:00', '10:59', '14:01', $From_Date],
                    'SHIFT2' => ['16:00', '18:00', '18:59', '22:31', $From_Date],
                    'SHIFT3' => ['00:30', '02:15', '02:59', '06:31', $Shift_Date_Conversion]
                ],
                'PRECOT - B' => [
                    'SHIFT1' => ['07:00', '10:00', '10:59', '14:01', $From_Date],
                    'SHIFT2' => ['16:00', '18:00', '18:59', '22:01', $From_Date],
                    'SHIFT3' => ['00:30', '02:15', '02:59', '06:01', $Shift_Date_Conversion]
                ],
                'PRECOT - C' => [
                    'SHIFT1' => ['07:00', '10:00', '10:59', '13:01', $From_Date],
                    'SHIFT2' => ['16:00', '18:00', '18:59', '21:31', $From_Date],
                    'SHIFT3' => ['23:59', '02:15', '02:59', '05:31', $Shift_Date_Conversion]
                ],
                'PRECOT - D' => [],
                'PRECOT - K' => [
                    'SHIFT1' => ['05:10', '07:30', '07:59', '09:31', $From_Date],
                    'SHIFT2' => ['13:30', '15:30', '19:29', '21:31', $From_Date],
                    'SHIFT3' => ['21:30', '23:30', '00:01', '02:01', $From_Date]
                ]
            ];

            if (!isset($Shifts[$LocationCode][$Enter_Shift])) {
                return [];
            }

            list($Search_From_Time, $Search_To_Time, $Break_From_Time, $Break_To_Time, $Break_Date) = $Shifts[$LocationCode][$Enter_Shift];

            $Search_From_DateTime = "$From_Date $Search_From_Time";
            $Search_To_DateTime = "$Shift_Date_Conversion $Search_To_Time";
            $Break_From_DateTime = "$Break_Date $Break_From_Time";
            $Break_To_DateTime = "$Break_Date $Break_To_Time";

            $Sql = "SELECT
                    E.Division AS Unit,
                    E.Wages AS Category,
                    E.DeptName AS Sub_Department,
                    E.EmpLevel,
                    E.MachineID,
                    E.oldEmpno AS OLD_EmpNo,
                    E.FirstName AS EmpName,
                    E.WorkArea,
                    E.SubSection_Name,
                    FORMAT(F.TimeIN, 'HH:mm tt') AS Day_In,
                    FORMAT(BO.TimeOUT, 'hh:mm tt') AS Break_Out,
                    FORMAT(BI.TimeIN, 'HH:mm tt') AS Break_IN
                FROM
                    Employee_Mst E
                INNER JOIN
                    UserDetails_Det Login
                    ON Login.Ccode = E.CompCode
                    AND Login.Lcode = E.LocCode
                    AND Login.Name = E.DeptName
                LEFT JOIN (
                    SELECT MachineID, MIN(TimeIN) AS TimeIN
                    FROM LogTime_IN
                    WHERE CompCode = '$CompanyCode'
                      AND LocCode = '$LocationCode'
                      AND TimeIN BETWEEN '$Search_From_DateTime' AND '$Search_To_DateTime'
                    GROUP BY MachineID
                ) AS F ON F.MachineID = E.MachineID
                LEFT JOIN (
                    SELECT MachineID, MIN(TimeOUT) AS TimeOUT
                    FROM LogTime_OUT
                    WHERE CompCode = '$CompanyCode'
                      AND LocCode = '$LocationCode'
                      AND TimeOUT BETWEEN '$Break_From_DateTime' AND '$Break_To_DateTime'
                    GROUP BY MachineID
                ) AS BO ON BO.MachineID = E.MachineID
                LEFT JOIN (
                    SELECT MachineID, MIN(TimeIN) AS TimeIN
                    FROM LogTime_IN
                    WHERE CompCode = '$CompanyCode'
                      AND LocCode = '$LocationCode'
                      AND TimeIN BETWEEN '$Break_From_DateTime' AND '$Break_To_DateTime'
                    GROUP BY MachineID
                ) AS BI ON BI.MachineID = E.MachineID
                WHERE
                    E.CompCode = '$CompanyCode'
                    AND E.LocCode = '$LocationCode'
                    AND Login.UserID = '$Login_User'
                    AND E.CatName = 'WORKER'
                    AND NOT (
                        F.TimeIN IS NULL
                        AND BO.TimeOUT IS NULL
                        AND BI.TimeIN IS NULL
                    )
                ORDER BY
                    E.Division, E.Wages, E.DeptName, E.MachineID";

            $Query = $this->db->query($Sql);

            // echo '<pre>';
            // print_r($Sql);
            // exit;

            if ($Query->num_rows() > 0) {
                return $Query->result();
            } else {
                return 0;
            }


        }



    }


    public function Get_Employee_Count($CompanyCode, $LocationCode){

        $Sql = "SELECT COUNT(DISTINCT EmpNo) AS Total_Active_Employee FROM Employee_Mst WHERE LocCode = '$LocationCode' AND CompCode = '$CompanyCode' AND IsActive = 'Yes'";
        $Query = $this->db->query($Sql);
        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }
}
