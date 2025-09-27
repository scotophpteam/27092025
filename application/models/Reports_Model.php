<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Reports_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Assigned_List($CompanyCode, $LocationCode, $Login_User, $Shift, $Date)
    {
        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Assign_Status = '1'";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        // $Department = $Employee_Work_Data[0]->Department;
        // $JobCardNo = $Employee_Work_Data[0]->Job_Card_No;

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }


    public function Late_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        $sql = "SELECT
                A.*,
                A.Type AS WorkType,
                B.UserID, B.UserName, B.Type AS UserType, B.Name
                FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN UserDetails_Det B
                ON A.Ccode = B.Ccode
                AND A.Lcode = B.Lcode
                AND A.Sub_Department = B.Name
                WHERE
                B.UserID = '$Login_User' AND
                A.Lcode = '$LocationCode' AND
                A.Ccode = '$CompanyCode' AND
                A.Date = '$Date' AND
                (A.Type = 'EXTRA' OR A.Type = 'LATE')";
        $query = $this->db->query($sql);
        $Late_Employee_List = $query->result();


        if ($query->num_rows() > 0) {

            return  $Late_Employee_List;
        } else {

            return FALSE;
        }
    }

    public function Shift_Closing_Report_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        $sql = "SELECT DISTINCT A.EmpNo,A.Department,A.Sub_Department,A.WorkArea,A.Job_Card_No,A.FirstName,A.Closing_Status,A.Ccode,A.Lcode,A.Date,A.Shift FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Work_Status = '1' AND  (A.Closing_Status = '1' OR A.Closing_Status = '0')";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }



    public function NoWork_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {


        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Work_Type = 'NoWork'";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }


    public function Work_Allocation_Sub_Section_Wise($CompanyCode, $LocationCode, $Login_User, $Shift, $Date, $Sub_Section)
    {


        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Sub_Section = '$Sub_Section'
                    AND A.Assign_Status = '1'";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        // $Department = $Employee_Work_Data[0]->Department;
        // $JobCardNo = $Employee_Work_Data[0]->Job_Card_No;

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }


    public function NoWork_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section)
    {

        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Sub_Section = '$Sub_Section'
                    AND A.Work_Type = 'NoWork'";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }


    public function Late_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section)
    {

        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Lcode = '$LocationCode' AND
                    A.Ccode = '$CompanyCode' AND
                    A.Sub_Section = '$Sub_Section' AND
                    A.Date = '$Date' AND
                    (A.Type = 'EXTRA' OR
                    A.Type = 'LATE') ";
        $query = $this->db->query($sql);
        $Late_Employee_List = $query->result();


        if ($query->num_rows() > 0) {

            return  $Late_Employee_List;
        } else {

            return 0;
        }
    }


    public function Shift_Closing_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section)
    {

        $sql = "SELECT DISTINCT A.EmpNo,A.Department,A.Sub_Department,A.WorkArea,A.Job_Card_No,A.FirstName,A.Closing_Status,A.Ccode,A.Lcode,A.Date,A.Shift FROM Web_Employee_Work_Allocation_Mst A
                INNER JOIN
                    UserDetails_Det B ON A.Ccode = B.Ccode AND A.Lcode = B.Lcode  AND A.Sub_Department = B.Name
                WHERE
                    B.UserID = '$Login_User' AND
                    A.Date = '$Date'
                    AND A.Shift = '$Shift'
                    AND A.Ccode = '$CompanyCode'
                    AND A.Lcode = '$LocationCode'
                    AND A.Sub_Section = '$Sub_Section'
                    AND A.Work_Status = '1' AND  (A.Closing_Status = '1' OR A.Closing_Status = '0')";

        $query = $this->db->query($sql);
        $Employee_Work_Data = $query->result();

        if ($query->num_rows() > 0) {

            return $Employee_Work_Data;
        } else {

            return 0;
        }
    }

    public function Extra_Hours_Employee_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Type)
    {


//    if ($Type == 'EXTRA') {

//         // Helper to convert HH:MM to decimal hours (e.g., 4:30 -> 4.5)
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

//         $Sql = "
//             WITH PunchTimes AS (
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


    public function OT_Hours_Employee_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    {

        // $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

        // $sql = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department,Work.Updated_Time
        //     FROM Web_Employee_Work_Allocation_Mst AS Work
        //     INNER JOIN UserDetails_Det AS Login
        //         ON Login.Lcode = Work.Lcode
        //         AND Login.Name = Work.Sub_Department
        //     WHERE Login.Ccode = '$CompanyCode'
        //       AND Login.Lcode = '$LocationCode'
        //       AND Login.UserID = '$Login_User'
        //       AND Work.Date = '$Date'
        //       AND Work.Shift = '$Next_Shift'
        //       AND Work.Work_Status = '1'
        //       AND Work.Assign_Status = '1'
        //       AND Work.Closing_Status = '1'
        //       AND Work.Working_Type = 'OT'";

        // $query = $this->db->query($sql);
        // if ($query->num_rows() === 0) return [];

        // $results = [];
        // $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
        // $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

        // // Get shift start and end time
        // $Shift_Sql = "SELECT StartTime, EndTime 
        //           FROM Shift_Mst 
        //           WHERE CompCode = '$CompanyCode' 
        //             AND LocCode = '$LocationCode' 
        //             AND ShiftDesc = '$Shift'";
        // $Shift_Data = $this->db->query($Shift_Sql)->row();


        // $Shift_Start_Time = $Shift_Data->StartTime ?? '00:00:00';
        // $Shift_End_Time   = $Shift_Data->EndTime ?? '00:00:00';

        // foreach ($query->result() as $row) {

        //     $Employee_ID = $row->EmpNo;
        //     $Employee_Name = $row->FirstName;
        //     $Sub_Department = $row->Sub_Department;
        //     $Shift_Closing_Time = date('h:i A', strtotime($row->Updated_Time));;

        //     // OUT time range
        //     if (strtoupper($Next_Shift) === 'SHIFT3') {
        //         $out_start = "$Date 13:00:00";
        //         $out_end   = "$nextDate 10:00:00";
        //     } else {
        //         $out_start = "$Date 06:00:00";
        //         $out_end   = "$nextDate 04:00:00";
        //     }

        //     // Get OUT time
        //     $Sql_OUT = "SELECT TOP 1 TimeOUT
        //             FROM LogTime_OUT
        //             WHERE MachineID = '$Employee_ID'
        //               AND Compcode = '$CompanyCode'
        //               AND LocCode = '$LocationCode'
        //               AND TimeOUT BETWEEN '$out_start' AND '$out_end'
        //             ORDER BY TimeOUT DESC";
        //     $out_query = $this->db->query($Sql_OUT);

        //     $OUTTime = '';
        //     $OUTDateTime = null;
        //     if ($out_query->num_rows() > 0) {
        //         $OUTDateTime = new DateTime($out_query->row()->TimeOUT);
        //         $OUTTime = $OUTDateTime->format('h:i A');
        //     }

        //     // IN time range
        //     $in_start = "$Date 02:00:00";
        //     $in_end   = "$nextDate 02:00:00";

        //     $Sql_IN = "SELECT TOP 1 TimeIN
        //            FROM LogTime_IN
        //            WHERE MachineID = '$Employee_ID'
        //              AND Compcode = '$CompanyCode'
        //              AND LocCode = '$LocationCode'
        //              AND TimeIN BETWEEN '$in_start' AND '$in_end'
        //            ORDER BY TimeIN ASC";
        //     $in_query = $this->db->query($Sql_IN);

        //     // Fallback for SHIFT3
        //     if ($in_query->num_rows() === 0 && strtoupper($Next_Shift) === 'SHIFT3') {
        //         $prev_in_start = "$previousDate 22:00:00";
        //         $prev_in_end   = "$Date 01:00:00";

        //         $Sql_Pre_IN = "SELECT TOP 1 TimeIN
        //                    FROM LogTime_IN
        //                    WHERE MachineID = '$Employee_ID'
        //                      AND Compcode = '$CompanyCode'
        //                      AND LocCode = '$LocationCode'
        //                      AND TimeIN BETWEEN '$prev_in_start' AND '$prev_in_end'
        //                    ORDER BY TimeIN ASC";
        //         $prev_in_query = $this->db->query($Sql_Pre_IN);
        //         if ($prev_in_query->num_rows() > 0) {
        //             $in_query = $prev_in_query;
        //         }
        //     }

        //     $INTime = '';
        //     $INDateTime = null;
        //     if ($in_query->num_rows() > 0) {
        //         $INDateTime = new DateTime($in_query->row()->TimeIN);
        //         $INTime = $INDateTime->format('h:i A');
        //     }

        //     // Calculate OT
        //     $decimalOT = 0.0;

        //     if ($INDateTime && $OUTDateTime) {
        //         $ShiftStart = new DateTime("$Date $Shift_Start_Time");
        //         $ShiftEnd = new DateTime("$Date $Shift_End_Time");
        //         if ($ShiftEnd <= $ShiftStart) $ShiftEnd->modify('+1 day');

        //         $workedMinutes = round(($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60, 2);
        //         $shiftMinutes  = round(($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60, 2);

        //         $otMinutes = max(0, $workedMinutes - $shiftMinutes);
        //         $otHours   = (int)floor($otMinutes / 60);
        //         $otRemMin  = round($otMinutes - ($otHours * 60));

        //         // Convert remaining minutes to decimal
        //         if ($otRemMin >= 11 && $otRemMin <= 20) {
        //             $minuteDecimal = 0.25;
        //         } elseif ($otRemMin >= 21 && $otRemMin <= 40) {
        //             $minuteDecimal = 0.50;
        //         } elseif ($otRemMin >= 41) {
        //             $minuteDecimal = 1.0;
        //         } else {
        //             $minuteDecimal = 0.0;
        //         }

        //         $decimalOT = $otHours + $minuteDecimal;
        //     }

        //     $Date_Check = date('d/m/Y', strtotime($Date));

        //     $Sql_Verify = "SELECT * FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND TranDate = '$Date_Check'
        //                AND Update_Status = 'E-Master' AND Status = '0' AND TokenNo = '$Employee_ID'";
        //     $Query_Verify = $this->db->query($Sql_Verify);


        //     if ($Query_Verify->num_rows() > 0) {

        //         $Verify_Result = $Query_Verify->result();

        //         $Sql_Updated = "SELECT OTHrs FROM OTHours WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND TranDate = '$Date_Check'
        //                AND Update_Status = 'E-Master' AND TokenNo = '$Employee_ID'";

        //         $Query_Updated = $this->db->query($Sql_Updated)->result();


        //         $results[] = [

        //             'Date' => date('d/m/Y', strtotime($Date)),
        //             'Shift' => $Shift,
        //             'Next_Shift' => $Next_Shift,
        //             'Employee_ID'  => $Employee_ID,
        //             'Employee_Name' => $Employee_Name,
        //             'In_Time' => $INTime,
        //             'Out_Time' => $OUTTime,
        //             'E_Master_Closing' => $Shift_Closing_Time,
        //             'OT_Hour' => $Query_Updated[0]->OTHrs,
        //             'Updated_Status' => '1'

        //         ];
        //     } else {

        //         $results[] = [

        //             'Date' => date('d/m/Y', strtotime($Date)),
        //             'Shift' => $Shift,
        //             'Next_Shift' => $Next_Shift,
        //             'Employee_ID'  => $Employee_ID,
        //             'Employee_Name' => $Employee_Name,
        //             'In_Time' => $INTime,
        //             'Out_Time' => $OUTTime,
        //             'E_Master_Closing' => $Shift_Closing_Time,
        //             'OT_Hour' => number_format($decimalOT, 2),
        //             'Updated_Status' => '0'

        //         ];
        //     }
        // }

        // return $results;




        $Next_Shift = ($Shift === 'SHIFT1') ? 'SHIFT2' : (($Shift === 'SHIFT2') ? 'SHIFT3' : 'SHIFT1');

    $sql = "SELECT Work.EmpNo, Work.FirstName, Work.Sub_Department, Work.Updated_Time, Work.Closing_Status
            FROM Web_Employee_Work_Allocation_Mst AS Work
            INNER JOIN UserDetails_Det AS Login
                ON Login.Lcode = Work.Lcode
                AND Login.Name = Work.Sub_Department
            WHERE Login.Ccode = '$CompanyCode'
              AND Login.Lcode = '$LocationCode'
              AND Login.UserID = '$Login_User'
              AND Work.Date = '$Date'
              AND Work.Shift = '$Shift'
              AND Work.Work_Status = '1'";

    $query = $this->db->query($sql);
    if ($query->num_rows() === 0) return [];

    $results = [];
    $employees = $query->result();

    $employeeIDs = array_map(function($e) { return "'{$e->EmpNo}'"; }, $employees);

    $nextDate = (new DateTime($Date))->modify('+1 day')->format('Y-m-d');
    $previousDate = (new DateTime($Date))->modify('-1 day')->format('Y-m-d');

    $Shift_Sql = "SELECT StartTime, EndTime 
                  FROM Shift_Mst 
                  WHERE CompCode = '$CompanyCode' 
                    AND LocCode = '$LocationCode' 
                    AND ShiftDesc = '$Shift'";
    $Shift_Data = $this->db->query($Shift_Sql)->row();
    $Shift_Start_Time = $Shift_Data->StartTime ?? '00:00:00';
    $Shift_End_Time   = $Shift_Data->EndTime ?? '00:00:00';

    // OUT time range
    $out_start = (strtoupper($Next_Shift) === 'SHIFT3') ? "$Date 13:00:00" : "$Date 06:00:00";
    $out_end   = (strtoupper($Next_Shift) === 'SHIFT3') ? "$nextDate 10:00:00" : "$nextDate 04:00:00";

    $Sql_OUT_All = "SELECT MachineID, MAX(TimeOUT) as TimeOUT
                    FROM LogTime_OUT
                    WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
                      AND Compcode = '$CompanyCode'
                      AND LocCode = '$LocationCode'
                      AND TimeOUT BETWEEN '$out_start' AND '$out_end'
                    GROUP BY MachineID";
    $OUT_Log = $this->db->query($Sql_OUT_All)->result();
    $OUT_Map = [];
    foreach ($OUT_Log as $row) {
        $OUT_Map[$row->MachineID] = new DateTime($row->TimeOUT);
    }

    // IN time range
    $in_start = "$Date 02:00:00";
    $in_end   = "$nextDate 02:00:00";

    $Sql_IN_All = "SELECT MachineID, MIN(TimeIN) as TimeIN
                   FROM LogTime_IN
                   WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
                     AND Compcode = '$CompanyCode'
                     AND LocCode = '$LocationCode'
                     AND TimeIN BETWEEN '$in_start' AND '$in_end'
                   GROUP BY MachineID";
    $IN_Log = $this->db->query($Sql_IN_All)->result();
    $IN_Map = [];
    foreach ($IN_Log as $row) {
        $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
    }

    // Backup IN for SHIFT3
    if (strtoupper($Next_Shift) === 'SHIFT3') {
        $prev_in_start = "$previousDate 22:00:00";
        $prev_in_end   = "$Date 01:00:00";

        $Sql_Pre_IN = "SELECT MachineID, MIN(TimeIN) as TimeIN
                       FROM LogTime_IN
                       WHERE MachineID IN (" . implode(',', $employeeIDs) . ")
                         AND Compcode = '$CompanyCode'
                         AND LocCode = '$LocationCode'
                         AND TimeIN BETWEEN '$prev_in_start' AND '$prev_in_end'
                       GROUP BY MachineID";
        $Prev_IN_Log = $this->db->query($Sql_Pre_IN)->result();
        foreach ($Prev_IN_Log as $row) {
            if (!isset($IN_Map[$row->MachineID])) {
                $IN_Map[$row->MachineID] = new DateTime($row->TimeIN);
            }
        }
    }

    // OT Verification
    $Date_Check = date('d/m/Y', strtotime($Date));
    $Sql_Verify_All = "SELECT TokenNo, OTHrs FROM OTHours
                       WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode'
                         AND TranDate = '$Date_Check' AND Update_Status = 'E-Master' AND Status = '0'
                         AND TokenNo IN (" . implode(',', $employeeIDs) . ")";
    $Verify_Log = $this->db->query($Sql_Verify_All)->result();
    $Verify_Map = [];
    foreach ($Verify_Log as $row) {
        $Verify_Map[$row->TokenNo] = $row->OTHrs;
    }

    foreach ($employees as $row) {
        $Employee_ID = $row->EmpNo;
        $Employee_Name = $row->FirstName;
        $Shift_Closing_Time_Str = $row->Updated_Time;
        $Shift_Closing_Status = $row->Closing_Status;

        $Shift_Closing_Time = '';
        if (!empty($Shift_Closing_Time_Str) && $Shift_Closing_Time_Str !== '-' && strtotime($Shift_Closing_Time_Str) !== false) {
            $Shift_Closing_Time = date('h:i A', strtotime($Shift_Closing_Time_Str));
        }

        $INDateTime = $IN_Map[$Employee_ID] ?? null;
        $OUTDateTime = $OUT_Map[$Employee_ID] ?? null;

        $INTime = $INDateTime ? $INDateTime->format('h:i A') : '';
        $OUTTime = $OUTDateTime ? $OUTDateTime->format('h:i A') : '';

        $Total_Working_Hours = 0.00;
        $workingMinutes = 0.0;
        if ($INDateTime && $OUTDateTime) {
            $workingMinutes = ($OUTDateTime->getTimestamp() - $INDateTime->getTimestamp()) / 60.0;
            $Total_Working_Hours = round($workingMinutes / 60.0, 2);
        }

        $OT_Closing_Diff = 0;
        if ($OUTDateTime && !empty($Shift_Closing_Time_Str) && strtotime($Shift_Closing_Time_Str) !== false) {
            try {
                $ShiftClosingDateTime = new DateTime($Shift_Closing_Time_Str);
                $diffMinutes = abs(($OUTDateTime->getTimestamp() - $ShiftClosingDateTime->getTimestamp()) / 60.0);
                $OT_Closing_Diff = ($diffMinutes > 45) ? 1 : 0;
            } catch (Exception $e) {
                $OT_Closing_Diff = 0;
            }
        }

        $decimalOT = 0.00;
        if ($INDateTime && $OUTDateTime) {
            $ShiftStart = new DateTime("$Date $Shift_Start_Time");
            $ShiftEnd = new DateTime("$Date $Shift_End_Time");
            if ($ShiftEnd <= $ShiftStart) $ShiftEnd->modify('+1 day');

            $shiftMinutes = ($ShiftEnd->getTimestamp() - $ShiftStart->getTimestamp()) / 60.0;
            $otMinutes = max(0, $workingMinutes - $shiftMinutes);

            $otHours = floor($otMinutes / 60.0);
            $otRemMin = $otMinutes - ($otHours * 60.0);

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
        }

        $results[] = [
            'Date' => $Date_Check,
            'Employee_ID' => $Employee_ID,
            'Employee_Name' => $Employee_Name,
            'In_Time' => $INTime,
            'Out_Time' => $OUTTime,
            'E_Master_Closing' => $Shift_Closing_Time,
            'OT_Hour' => isset($Verify_Map[$Employee_ID]) ? $Verify_Map[$Employee_ID] : number_format($decimalOT, 2),
            'Total_Working_Hours' => number_format($Total_Working_Hours, 2),
            'OT_Closing_Diff' => $OT_Closing_Diff,
            'Closing_Status' => $Shift_Closing_Status,
            'Updated_Status' => isset($Verify_Map[$Employee_ID]) ? '1' : '0'
        ];
    }

    return $results;
    
    }

        public function Get_OT_Employee_List($CompanyCode, $LocationCode, $Login_User,$Date, $Shift)
{
    $Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst Work
            INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                AND Login.Ccode = Work.Ccode
                AND Login.Name = Work.Sub_Department
            WHERE Login.UserID = '$Login_User'
                AND Work.Lcode = '$LocationCode'
                AND Work.Ccode = '$CompanyCode'
                AND Work.Date = '$Date'
                AND Work.Shift = '$Shift'
                AND Work.WorK_Status = '1'
                AND Work.Working_Type = 'OT'
                AND Work.Work_Type != 'NoWork'";
                // print_r($Sql);exit;
    $Query = $this->db->query($Sql);

    if ($Query->num_rows() > 0) {
        return $Query->result();
    } else {
        return 0;
    }
}
}
