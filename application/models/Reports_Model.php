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


        $current_Date = date('Y/m/d');

        $Date = '2025-01-22';

        $Sql = "WITH PunchTimes AS (
            SELECT
                Work.EmpNo,
                Work.WorkArea,
                -- Convert Updated_Time to TIME and combine with Work.Date
                CASE
                    WHEN TRY_CONVERT(TIME, Work.Updated_Time, 120) IS NOT NULL
                    THEN CONVERT(DATETIME, CONVERT(VARCHAR(10), TRY_CONVERT(DATE, Work.Date, 120), 120) + ' ' + CONVERT(VARCHAR(5), TRY_CONVERT(TIME, Work.Updated_Time, 120)), 120)
                    ELSE NULL
                END AS Updated_Time,
                Work.Closing_Status,
                Work.FirstName,
                Time.TimeOUT,
                ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT ASC) AS RowAsc,
                ROW_NUMBER() OVER (PARTITION BY Work.EmpNo ORDER BY Time.TimeOUT DESC) AS RowDesc,
                TRY_CONVERT(DATE, Work.Date, 120) AS WorkDate
            FROM
                Web_Extra_Work_Allocation_Mst Work
            INNER JOIN
                UserDetails_Det Login ON Login.Lcode = Work.Lcode
                AND Login.Ccode = Work.Ccode
                AND Login.Name = Work.Sub_Department
            INNER JOIN
                LogTimeLunch_OUT AS Time ON Time.MachineID = Work.EmpNo
                AND TRY_CONVERT(DATE, Time.TimeOUT, 120) = TRY_CONVERT(DATE, Work.Date, 120)
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
                TokenNo,
                1 AS Updated_Status,
                TRY_CONVERT(DATE, Entry_Date, 120) AS EntryDate
            FROM
                OnDuty_Mst
            WHERE
                TRY_CONVERT(DATE, Entry_Date, 120) = TRY_CONVERT(DATE, '$Date', 120)
            GROUP BY
                TokenNo,
                TRY_CONVERT(DATE, Entry_Date, 120)
        )
        SELECT
            p.EmpNo,
            p.WorkArea,
            CONVERT(VARCHAR(10), p.WorkDate, 120) AS WorkDate,
            CONVERT(VARCHAR(5), MAX(p.Updated_Time), 108) AS Updated_Time,
            p.Closing_Status,
            p.FirstName,
            CONVERT(VARCHAR(5), MIN(p.TimeOUT), 108) AS FirstPunchIn,
            CONVERT(VARCHAR(5), MAX(p.TimeOUT), 108) AS LastPunchOut,
            CASE
                WHEN MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END) IS NOT NULL
                     AND MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END) IS NOT NULL
                THEN
                    CONVERT(VARCHAR(5),
                        CEILING(DATEDIFF(MINUTE,
                            MIN(CASE WHEN p.RowAsc = 1 THEN p.TimeOUT END),
                            MAX(CASE WHEN p.RowDesc = 1 THEN p.TimeOUT END)) / 60.0)
                    ) + ':00'
                ELSE '0:00'
            END AS TotalWorkingHours,
            ISNULL(ods.Updated_Status, 0) AS Updated_Status,
            CASE
                WHEN MAX(p.Updated_Time) IS NOT NULL AND MAX(p.TimeOUT) IS NOT NULL
                THEN
                    RIGHT('0' + CONVERT(VARCHAR(2),
                        ABS(DATEDIFF(MINUTE, MAX(p.Updated_Time), MAX(p.TimeOUT))) / 60
                    ), 2) + ':' +
                    RIGHT('0' + CONVERT(VARCHAR(2),
                        ABS(DATEDIFF(MINUTE, MAX(p.Updated_Time), MAX(p.TimeOUT))) % 60
                    ), 2)
                ELSE 'Invalid'
            END AS TimeDifference
        FROM
            PunchTimes p
        LEFT JOIN
            OnDutyStatus ods ON p.EmpNo = ods.TokenNo
                AND p.WorkDate = ods.EntryDate
        GROUP BY
            p.EmpNo, p.WorkArea, p.WorkDate, p.Closing_Status, p.FirstName, ods.Updated_Status;";

        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }


    public function OT_Hours_Employee_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
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

                $Shift_Array = ['SHIFT1', 'SHIFT2', 'SHIFT3'];

                $currentIndex = array_search($Shift, $Shift_Array);
                $previousIndex = ($currentIndex - 1 + count($Shift_Array)) % count($Shift_Array);
                $Previous_Shift = $Shift_Array[$previousIndex];

                $Data[] = [
                    'Date'              => $Date,
                    'Previous Shift'    => $Previous_Shift,
                    'Current Shift'     => $Shift,
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
}
