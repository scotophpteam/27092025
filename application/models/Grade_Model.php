

<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Grade_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Get_Sub_Department($CompanyCode, $LocationCode, $Login_User)
    {

        $Sql = "SELECT  DISTINCT DeptName,DeptGrp FROM Employee_Mst WHERE LocCode = '$LocationCode' AND CompCode = '$CompanyCode' AND  DeptName  != 'Admin' AND IsActive = 'Yes' AND DeptName  != '' AND DeptName != '-Select-'";
        $Query = $this->db->query($Sql);

        if ($Query->num_rows() > 0) {
            return $Query->result();
        } else {
            return 0;
        }
    }


    public function Active_Employee_List($Ccode, $Lcode, $Department, $Employee_Status)
    {

        if ($Employee_Status == 'Active') {
            $IsActive = 'Yes';
        } elseif ($Employee_Status == '') {
            $IsActive = '';
        }

        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.Attn_Date,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth
    FROM LogTime_Days LD
    WHERE LD.Attn_Date >= DATEADD(MONTH, -6, CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE))
      AND LD.Attn_Date < CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE)
      AND LD.CompCode = '$Ccode'
      AND LD.LocCode = '$Lcode'
    GROUP BY LD.ExistingCode, LD.Attn_Date, FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        YearMonth,
        SUM(CASE WHEN TotalWorkingDays > 0 THEN (CAST(Present AS FLOAT) / TotalWorkingDays) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY ExistingCode, YearMonth
),
Latest6MonthsSummary AS (
    SELECT
        MP.ExistingCode,
        ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_6,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_5,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_4,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_3,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
        MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1
    FROM MonthlyPercentages MP
    GROUP BY MP.ExistingCode
)

SELECT
    EM.DeptName,
    CONVERT(VARCHAR, EM.DOJ, 105) AS DOJ,
    EM.FirstName,
    EM.MachineID,
    EM.EmployeeMobile,
    EM.IsActive,
    DATEDIFF(YEAR, EM.DOJ, GETDATE()) AS ExperienceYears,
    DATEDIFF(MONTH, EM.DOJ, GETDATE()) % 12 AS ExperienceMonths,
    CONCAT(DATEDIFF(YEAR, EM.DOJ, GETDATE()), ' Years ', DATEDIFF(MONTH, EM.DOJ, GETDATE()) % 12, ' Months') AS ExperienceFormatted,

    L6.Percentage_Month_6,
    L6.Percentage_Month_5,
    L6.Percentage_Month_4,
    L6.Percentage_Month_3,
    L6.Percentage_Month_2,
    L6.Percentage_Month_1,
    L6.Average_Percentage,

    -- Attendance Grade
    CASE
        WHEN DATEDIFF(MONTH, EM.DOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, EM.DOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN L6.Average_Percentage BETWEEN 80 AND 90 THEN 'A+'
                WHEN L6.Average_Percentage BETWEEN 70 AND 79 THEN 'A'
                WHEN L6.Average_Percentage BETWEEN 60 AND 69 THEN 'B+'
                WHEN L6.Average_Percentage BETWEEN 50 AND 59 THEN 'B'
                WHEN L6.Average_Percentage BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade

FROM Employee_Mst EM
INNER JOIN Latest6MonthsSummary L6 ON EM.ExistingCode = L6.ExistingCode
WHERE EM.CompCode = '$Ccode'
  AND EM.LocCode = '$Lcode'
  AND EM.DeptName = '$Department'
  AND EM.IsActive = 'Yes'
";


        // print_r($sql);
        // exit;

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function InActive_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $FromDate, $ToDate)
    {
        $IsActive = ($Employee_Status == 'Active') ? 'Yes' : 'No';
        $sql = "SELECT
                DeptName,
                FORMAT(TRY_CONVERT(DATE, DOJ), 'dd-MM-yyyy') AS DOJ,
                FORMAT(TRY_CONVERT(DATE, DOR), 'dd-MM-yyyy') AS DOR,
                FirstName,
                MachineID,
                EmployeeMobile,
                IsActive,
                DOR,
                DATEDIFF(YEAR, TRY_CONVERT(DATE, DOJ), ISNULL(TRY_CONVERT(DATE, DOR), GETDATE())) AS ExperienceYears,
                DATEDIFF(MONTH, TRY_CONVERT(DATE, DOJ), ISNULL(TRY_CONVERT(DATE, DOR), GETDATE())) % 12 AS ExperienceMonths,
                CONCAT(
                    DATEDIFF(YEAR, TRY_CONVERT(DATE, DOJ), ISNULL(TRY_CONVERT(DATE, DOR), GETDATE())),
                    ' Years ',
                    DATEDIFF(MONTH, TRY_CONVERT(DATE, DOJ), ISNULL(TRY_CONVERT(DATE, DOR), GETDATE())) % 12,
                    ' Months'
                ) AS ExperienceFormatted
            FROM Employee_Mst
            WHERE
                CompCode = '$Ccode'
                AND LocCode = '$Lcode'
                AND DeptName = '$Department'
                AND IsActive = '$IsActive'
                AND TRY_CONVERT(DATE, DOR, 103) >= '$FromDate'
                AND TRY_CONVERT(DATE, DOR, 103) <= '$ToDate' ";

        // print_r($sql);exit;

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    public function Trainee_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $TraineeMonth)
    {
        // Determine the `IsActive` value based on `Employee_Status`
        $IsActive = ($Employee_Status == 'Active') ? 'Yes' : 'No';

        // Get the current date
        $Date = date("Y-m-d");

        // SQL query to calculate experience
        $sql = "
            SELECT *
            FROM (
                SELECT
                    MachineID,
                    DOJ,
                    IsActive,
                    CompCode,
                    LocCode,
                    DeptName,
                    FirstName,
                    EmployeeMobile,
                    -- Calculate months of activity
                    DATEDIFF(MONTH, DOJ, '$Date') AS Months_Active,
                    -- Calculate years of experience
                    DATEDIFF(YEAR, DOJ, '$Date') AS ExperienceYears,
                    -- Calculate remaining months of experience
                    DATEDIFF(MONTH, DOJ, '$Date') % 12 AS ExperienceMonths,
                    -- Format the experience as 'X Years Y Months'
                    CONCAT(
                        DATEDIFF(YEAR, DOJ, '$Date'),
                        ' Years ',
                        DATEDIFF(MONTH, DOJ, '$Date') % 12,
                        ' Months'
                    ) AS ExperienceFormatted
                FROM Employee_Mst
            ) AS temp
            WHERE
                Months_Active >= 1
                AND Months_Active <= ?
                AND temp.IsActive = ?
                AND temp.CompCode = ?
                AND temp.LocCode = ?
                AND temp.DeptName = ?
            ORDER BY DOJ DESC";

        // echo"<pre>";
        // print_r($sql);
        // exit;



        // Execute the query with parameterized values
        $query = $this->db->query($sql, [$TraineeMonth, 'Yes', $Ccode, $Lcode, $Department]);

        // Return the results as an associative array
        return $query->result_array();
    }


    public function OnRoll_Employee_List($Ccode, $Lcode, $Department, $Employee_Status, $FromDate, $ToDate)
    {
        $sql = "SELECT
                CompCode,
                LocCode,
                DeptName,
                IsActive,
                CONVERT(VARCHAR(10), DOJ, 120) AS DOJ,
                FirstName,
                MachineID,
                EmployeeMobile,
                DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
                CONCAT(FLOOR(DATEDIFF(DAY, DOJ, GETDATE()) / 365), ' Years ', FLOOR((DATEDIFF(DAY, DOJ, GETDATE()) % 365) / 30), ' Months') AS ExperienceFormatted,
                CONVERT(VARCHAR(10), DATEADD(MONTH, 6, DOJ), 120) AS SixMonthsAfterDOJ
            FROM
                Employee_Mst
            WHERE
                CompCode = '$Ccode'
                AND LocCode = '$Lcode'
                AND DeptName = '$Department'
                AND IsActive = 'Yes'
                AND TRY_CONVERT(DATE, DOR, 103) >= '$FromDate'
                AND TRY_CONVERT(DATE, DOR, 103) <= '$ToDate'
                AND DATEADD(MONTH, 6, DOJ) <= GETDATE()
            ORDER BY
                DOJ DESC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    // --------------------------------------------Attendance - Sheet  - Section  -----------------------------------------------//


    public function Attendance_List($Ccode, $Lcode, $Department)
    {

        $sql = "WITH AttendanceData AS (
    SELECT
        ExistingCode,
        FirstName,
        TRY_CONVERT(DATE, doj, 103) AS ValidDOJ,
        FORMAT(Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN Present = '1' AND Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days
    WHERE Attn_Date >= DATEADD(MONTH, -6, DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1))
      AND Attn_Date < DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1)
      AND CompCode = '$Ccode'
      AND LocCode = '$Lcode'
      AND DeptName = '$Department'
    GROUP BY ExistingCode, FirstName, doj, FORMAT(Attn_Date, 'yyyy-MM')
),

MonthlyGrades AS (
    SELECT
        AD.ExistingCode,
        AD.FirstName,
        AD.ValidDOJ,
        AD.YearMonth,
        CASE
            WHEN AD.ValidDOJ IS NULL THEN 'Invalid Date'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 3 THEN 'N'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 6 THEN 'T'
            ELSE
                CASE
                    WHEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) BETWEEN 80 AND 100 THEN 'A+'
                    WHEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) BETWEEN 70 AND 79 THEN 'A'
                    WHEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) BETWEEN 60 AND 69 THEN 'B+'
                    WHEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) BETWEEN 50 AND 59 THEN 'B'
                    WHEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) BETWEEN 1 AND 49 THEN 'C'
                    ELSE 'No Grade'
                END
        END AS MonthlyGrade,
        AD.Present,
        AD.TotalWorkingDays
    FROM AttendanceData AD
),

GradeChanges AS (
    SELECT
        mg.*,
        LAG(mg.MonthlyGrade) OVER (PARTITION BY mg.ExistingCode ORDER BY mg.YearMonth) AS PrevGrade,
        CASE
            WHEN LAG(mg.MonthlyGrade) OVER (PARTITION BY mg.ExistingCode ORDER BY mg.YearMonth) IS NULL THEN 0
            WHEN mg.MonthlyGrade <> LAG(mg.MonthlyGrade) OVER (PARTITION BY mg.ExistingCode ORDER BY mg.YearMonth) THEN 1
            ELSE 0
        END AS GradeChangedFlag
    FROM MonthlyGrades mg
),

LastGradeChange AS (
    SELECT
        ExistingCode,
        MAX(CASE WHEN GradeChangedFlag = 1 THEN YearMonth ELSE NULL END) AS GradeChangeDate,
        MAX(CASE WHEN GradeChangedFlag = 1 THEN PrevGrade ELSE NULL END) AS LastGrade
    FROM GradeChanges
    GROUP BY ExistingCode
),

LastKnownGrade AS (
    SELECT
        ExistingCode,
        MAX(YearMonth) AS LastGradeMonth
    FROM MonthlyGrades
    GROUP BY ExistingCode
),

LastKnownGradeDetails AS (
    SELECT
        mg.ExistingCode,
        mg.MonthlyGrade AS LastGrade,
        mg.YearMonth AS LastGradeMonth
    FROM MonthlyGrades mg
    INNER JOIN LastKnownGrade lkg
        ON mg.ExistingCode = lkg.ExistingCode
        AND mg.YearMonth = lkg.LastGradeMonth
),

FinalSummary AS (
    SELECT
        AD.ExistingCode,
        AD.FirstName,
        AD.ValidDOJ AS doj,
        CASE
            WHEN AD.ValidDOJ IS NULL THEN 'Invalid Date'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 3 THEN 'N'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 6 THEN 'T'
            ELSE 'Exp'
        END AS Status,
        DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) AS WorkingMonths,

        -- Monthly Percentages
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_6,
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_5,
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_4,
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_3,
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_2,
        MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0) ELSE 0 END) AS Percentage_Month_1,

        -- Average Percentage over 6 months
        ROUND(
            SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
                     THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0
        ) AS Average_Percentage,

        -- Final Grade
        CASE
            WHEN AD.ValidDOJ IS NULL THEN 'Invalid Date'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 3 THEN 'N'
            WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 6 THEN 'T'
            ELSE
                CASE
                    WHEN ROUND(SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0) BETWEEN 80 AND 100 THEN 'A+'
                    WHEN ROUND(SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0) BETWEEN 70 AND 79 THEN 'A'
                    WHEN ROUND(SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0) BETWEEN 60 AND 69 THEN 'B+'
                    WHEN ROUND(SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0) BETWEEN 50 AND 59 THEN 'B'
                    WHEN ROUND(SUM(CASE WHEN AD.YearMonth BETWEEN FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') AND FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) * 100 / 6, 0) BETWEEN 1 AND 49 THEN 'C'
                    ELSE 'No Grade'
                END
        END AS Grade
    FROM AttendanceData AD
    GROUP BY AD.ExistingCode, AD.FirstName, AD.ValidDOJ
)

SELECT
    fs.ExistingCode,
    fs.FirstName,
    fs.doj,
    fs.Status,
    fs.WorkingMonths,
    fs.Percentage_Month_6,
    fs.Percentage_Month_5,
    fs.Percentage_Month_4,
    fs.Percentage_Month_3,
    fs.Percentage_Month_2,
    fs.Percentage_Month_1,
    fs.Average_Percentage,
    fs.Grade,
    -- Prefer last grade from a grade change if exists; else last known grade
    COALESCE(lgc.LastGrade, lkgd.LastGrade) AS LastGrade,
    -- Prefer grade change date if exists; else last grade month
    COALESCE(lgc.GradeChangeDate, lkgd.LastGradeMonth) AS GradeChangeDate
FROM FinalSummary fs
LEFT JOIN LastGradeChange lgc ON fs.ExistingCode = lgc.ExistingCode
LEFT JOIN LastKnownGradeDetails lkgd ON fs.ExistingCode = lkgd.ExistingCode
ORDER BY fs.ExistingCode;
";

        $query = $this->db->query($sql);
        return $query->result_array();
    }




    // Inactive Employee List
    public function Last_thirdy($Ccode, $Lcode, $Department)
    {
        // Get the first and last day of the month 6 months before the current date
        $six_months_ago_first_day = date('01/m/Y', strtotime('-6 months'));
        $six_months_ago_last_day = date('t/m/Y', strtotime('-6 months'));

        // SQL query to calculate attendance grade for employees who resigned in the last 6 months
        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.FirstName,
        TRY_CONVERT(DATE, LD.doj, 103) AS ValidDOJ,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days LD
    INNER JOIN Employee_Mst EM ON LD.ExistingCode = EM.ExistingCode
    WHERE
        LD.Attn_Date >= DATEADD(MONTH, -6, CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE))
        AND LD.Attn_Date < CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE)
        AND LD.CompCode = 'PRECOT'
        AND LD.LocCode = 'PRECOT - A'
        AND EM.IsActive = 'No'
        AND EM.DeptName = 'Electrical'
    GROUP BY
        LD.ExistingCode,
        LD.FirstName,
        LD.doj,
        FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth,
        SUM(CASE WHEN Present > 0 THEN CAST(Present AS FLOAT) / NULLIF(TotalWorkingDays, 0) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth
),
LatestEmployeeList AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        ROW_NUMBER() OVER (PARTITION BY ExistingCode ORDER BY YearMonth DESC) AS rn
    FROM AttendanceData
)
SELECT
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ AS doj,
    EM.DOR,
    EM.EmployeeMobile,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE 'Exp'
    END AS Status,
    DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) AS WorkingMonths,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_6,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_5,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_4,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_3,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1,
    ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 80 AND 90 THEN 'A+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 70 AND 79 THEN 'A'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 60 AND 69 THEN 'B+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 50 AND 59 THEN 'B'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade
FROM LatestEmployeeList LE
INNER JOIN MonthlyPercentages MP ON LE.ExistingCode = MP.ExistingCode
INNER JOIN Employee_Mst EM ON LE.ExistingCode = EM.ExistingCode
WHERE LE.rn = 1
GROUP BY
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ,
    EM.DOR,
    EM.EmployeeMobile";

        // Execute the query and return the result
        $query = $this->db->query($sql);
        return $query->result_array();
    }







    public function Last_Sixty($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of -2 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.FirstName,
        TRY_CONVERT(DATE, LD.doj, 103) AS ValidDOJ,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days LD
    INNER JOIN Employee_Mst EM ON LD.ExistingCode = EM.ExistingCode
    WHERE
        LD.Attn_Date >= DATEADD(DAY, -60, CAST(GETDATE() AS DATE))
        AND LD.Attn_Date < CAST(GETDATE() AS DATE)
        AND LD.CompCode = '$Ccode'
        AND LD.LocCode = '$Lcode'
        AND EM.IsActive = 'No'
        AND EM.DeptName = '$Department'
    GROUP BY
        LD.ExistingCode,
        LD.FirstName,
        LD.doj,
        FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth,
        SUM(CASE WHEN Present > 0 THEN CAST(Present AS FLOAT) / NULLIF(TotalWorkingDays, 0) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth
),
LatestEmployeeList AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        ROW_NUMBER() OVER (PARTITION BY ExistingCode ORDER BY YearMonth DESC) AS rn
    FROM AttendanceData
)
SELECT
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ AS doj,
    EM.DOR,
    EM.EmployeeMobile,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE 'Exp'
    END AS Status,
    DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) AS WorkingMonths,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1,
    ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 80 AND 90 THEN 'A+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 70 AND 79 THEN 'A'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 60 AND 69 THEN 'B+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 50 AND 59 THEN 'B'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade
FROM LatestEmployeeList LE
INNER JOIN MonthlyPercentages MP ON LE.ExistingCode = MP.ExistingCode
INNER JOIN Employee_Mst EM ON LE.ExistingCode = EM.ExistingCode
WHERE LE.rn = 1
GROUP BY
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ,
    EM.DOR,
    EM.EmployeeMobile;
";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function Last_Ninety($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of -3 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.FirstName,
        TRY_CONVERT(DATE, LD.doj, 103) AS ValidDOJ,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days LD
    INNER JOIN Employee_Mst EM ON LD.ExistingCode = EM.ExistingCode
    WHERE
        LD.Attn_Date >= DATEADD(DAY, -90, CAST(GETDATE() AS DATE))
        AND LD.Attn_Date < CAST(GETDATE() AS DATE)
        AND LD.CompCode = '$Ccode'
        AND LD.LocCode = '$Lcode'
        AND EM.IsActive = 'No'
        AND EM.DeptName = '$Department'
    GROUP BY
        LD.ExistingCode,
        LD.FirstName,
        LD.doj,
        FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth,
        SUM(CASE WHEN Present > 0 THEN CAST(Present AS FLOAT) / NULLIF(TotalWorkingDays, 0) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth
),
LatestEmployeeList AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        ROW_NUMBER() OVER (PARTITION BY ExistingCode ORDER BY YearMonth DESC) AS rn
    FROM AttendanceData
)
SELECT
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ AS doj,
    EM.DOR,
    EM.EmployeeMobile,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE 'Exp'
    END AS Status,
    DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) AS WorkingMonths,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1,
    ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 80 AND 90 THEN 'A+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 70 AND 79 THEN 'A'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 60 AND 69 THEN 'B+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 50 AND 59 THEN 'B'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade
FROM LatestEmployeeList LE
INNER JOIN MonthlyPercentages MP ON LE.ExistingCode = MP.ExistingCode
INNER JOIN Employee_Mst EM ON LE.ExistingCode = EM.ExistingCode
WHERE LE.rn = 1
GROUP BY
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ,
    EM.DOR,
    EM.EmployeeMobile;";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function Last_One_Twenty($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of -4 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.FirstName,
        TRY_CONVERT(DATE, LD.doj, 103) AS ValidDOJ,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days LD
    INNER JOIN Employee_Mst EM ON LD.ExistingCode = EM.ExistingCode
    WHERE
        LD.Attn_Date >= DATEADD(DAY, -120, CAST(GETDATE() AS DATE))
        AND LD.Attn_Date < CAST(GETDATE() AS DATE)
        AND LD.CompCode = '$Ccode'
        AND LD.LocCode = '$Lcode'
        AND EM.IsActive = 'No'
        AND EM.DeptName = '$Department'
    GROUP BY
        LD.ExistingCode,
        LD.FirstName,
        LD.doj,
        FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth,
        SUM(CASE WHEN Present > 0 THEN CAST(Present AS FLOAT) / NULLIF(TotalWorkingDays, 0) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth
),
LatestEmployeeList AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        ROW_NUMBER() OVER (PARTITION BY ExistingCode ORDER BY YearMonth DESC) AS rn
    FROM AttendanceData
)
SELECT
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ AS doj,
    EM.DOR,
    EM.EmployeeMobile,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE 'Exp'
    END AS Status,
    DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) AS WorkingMonths,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1,
    ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 80 AND 90 THEN 'A+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 70 AND 79 THEN 'A'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 60 AND 69 THEN 'B+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 50 AND 59 THEN 'B'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade
FROM LatestEmployeeList LE
INNER JOIN MonthlyPercentages MP ON LE.ExistingCode = MP.ExistingCode
INNER JOIN Employee_Mst EM ON LE.ExistingCode = EM.ExistingCode
WHERE LE.rn = 1
GROUP BY
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ,
    EM.DOR,
    EM.EmployeeMobile;";
        $query = $this->db->query($sql);
        return $query->result_array();
    }



    // OnRoll Employee List


    public function On_Last_thirdy($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of last month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "SELECT
                        FirstName,
                        MachineID,
                        EmployeeMobile,
                        ExistingCode,
                        CONVERT(VARCHAR(10), DOJ, 120) AS DOJ,
                        CONVERT(VARCHAR(10), DOR, 120) AS DOR,
                        IsActive,
                        DeptName,
                        DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
                        CONCAT(FLOOR(DATEDIFF(DAY, DOJ, GETDATE()) / 365), ' Years ', FLOOR((DATEDIFF(DAY, DOJ, GETDATE()) % 365) / 30), ' Months') AS ExperienceFormatted
                    FROM Employee_Mst
                    WHERE
                        CompCode = '$Ccode'
                        AND LocCode = '$Lcode'
                        AND DeptName = '$Department'
                        AND Convert(Date,DOR,103) >= Convert(Date,'$first_day_last_month',103)
                        AND Convert(Date,DOR,103) <= Convert(Date,'$last_day_last_month',103)
                        AND IsActive = 'Yes'
                    ORDER BY DOR DESC";

        // print_r($sql);exit;

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    public function On_Last_Sixty($Ccode, $Lcode, $Department)
    {


        $first_day_last_month = date('01/m/Y', strtotime('first day of -2 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "SELECT
                        FirstName,
                        MachineID,
                        EmployeeMobile,
                        ExistingCode,
                        CONVERT(VARCHAR(10), DOJ, 120) AS DOJ,  -- Format DOJ as YYYY-MM-DD
                        CONVERT(VARCHAR(10), DOR, 120) AS DOR,  -- Format DOR as YYYY-MM-DD
                        IsActive,
                        DeptName,
                        DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,  -- Calculate months of active experience
                        CONCAT(FLOOR(DATEDIFF(DAY, DOJ, GETDATE()) / 365), ' Years ', FLOOR((DATEDIFF(DAY, DOJ, GETDATE()) % 365) / 30), ' Months') AS ExperienceFormatted
                    FROM Employee_Mst
                    WHERE
                        CompCode = '$Ccode'
                        AND LocCode = '$Lcode'
                        AND DeptName = '$Department'
                        AND Convert(Date,DOR,103) >= Convert(Date,'$first_day_last_month',103)
                        AND Convert(Date,DOR,103) <= Convert(Date,'$last_day_last_month',103)
                        AND IsActive = 'Yes'
                    ORDER BY DOR DESC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function On_Last_Ninety($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of -3 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "SELECT
                        FirstName,
                        MachineID,
                        EmployeeMobile,
                        ExistingCode,
                        CONVERT(VARCHAR(10), DOJ, 120) AS DOJ,  -- Format DOJ as YYYY-MM-DD
                        CONVERT(VARCHAR(10), DOR, 120) AS DOR,  -- Format DOR as YYYY-MM-DD
                        IsActive,
                        DeptName,
                        DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,  -- Calculate months of active experience
                        CONCAT(FLOOR(DATEDIFF(DAY, DOJ, GETDATE()) / 365), ' Years ', FLOOR((DATEDIFF(DAY, DOJ, GETDATE()) % 365) / 30), ' Months') AS ExperienceFormatted
                    FROM Employee_Mst
                    WHERE
                        CompCode = '$Ccode'
                        AND LocCode = '$Lcode'
                        AND DeptName = '$Department'
                        AND Convert(Date,DOR,103) >= Convert(Date,'$first_day_last_month',103)
                        AND Convert(Date,DOR,103) <= Convert(Date,'$last_day_last_month',103)
                        AND IsActive = 'Yes'
                    ORDER BY DOR DESC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    public function On_One_Twenty($Ccode, $Lcode, $Department)
    {

        $first_day_last_month = date('01/m/Y', strtotime('first day of -4 month'));
        $last_day_last_month = date('t/m/Y', strtotime('last day of last month'));

        $sql = "SELECT
                        FirstName,
                        MachineID,
                        EmployeeMobile,
                        ExistingCode,
                        CONVERT(VARCHAR(10), DOJ, 120) AS DOJ,  -- Format DOJ as YYYY-MM-DD
                        CONVERT(VARCHAR(10), DOR, 120) AS DOR,  -- Format DOR as YYYY-MM-DD
                        IsActive,
                        DeptName,
                        DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,  -- Calculate months of active experience
                        CONCAT(FLOOR(DATEDIFF(DAY, DOJ, GETDATE()) / 365), ' Years ', FLOOR((DATEDIFF(DAY, DOJ, GETDATE()) % 365) / 30), ' Months') AS ExperienceFormatted
                    FROM Employee_Mst
                    WHERE
                        CompCode = '$Ccode'
                        AND LocCode = '$Lcode'
                        AND DeptName = '$Department'
                        AND Convert(Date,DOR,103) >= Convert(Date,'$first_day_last_month',103)
                        AND Convert(Date,DOR,103) <= Convert(Date,'$last_day_last_month',103)
                        AND IsActive = 'Yes'
                    ORDER BY DOR DESC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function Last_6_Month_Trainee_Incomplete($Ccode, $Lcode)
    {
        $sql = "SELECT
            EmpNo,
            FirstName,
            DOJ,
            Category,
            Duration,
            DeptName,

            DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
            DATEDIFF(YEAR, DOJ, GETDATE()) AS ExperienceYears,
            DATEDIFF(MONTH, DOJ, GETDATE()) % 12 AS ExperienceMonths,

            CONCAT(
                DATEDIFF(YEAR, DOJ, GETDATE()),
                ' Years ',
                DATEDIFF(MONTH, DOJ, GETDATE()) % 12,
                ' Months'
            ) AS ExperienceFormatted,

            DATEDIFF(MONTH, Duration, GETDATE()) AS DurationMonths,

            CONCAT(
                DATEDIFF(YEAR, Duration, GETDATE()),
                ' Years ',
                DATEDIFF(MONTH, Duration, GETDATE()) % 12,
                ' Months'
            ) AS DurationFormatted
        FROM Employee_Mst
        WHERE
            Category = 'Traniee'
            AND CompCode = '$Ccode'
            AND LocCode = '$Lcode'
            AND IsActive = 'Yes'
            AND DATEDIFF(MONTH, DOJ, GETDATE()) <= 6
        ORDER BY DOJ DESC


";
        $query = $this->db->query($sql);
        return $query->result();
    }



    public function Last_12_Month_Trainee_Incomplete($Ccode, $Lcode)
    {
        $sql = "SELECT
            EmpNo,
            FirstName,
            DOJ,
            Category,
            Duration,
            DeptName,

            DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
            DATEDIFF(YEAR, DOJ, GETDATE()) AS ExperienceYears,
            DATEDIFF(MONTH, DOJ, GETDATE()) % 12 AS ExperienceMonths,

            CONCAT(
                DATEDIFF(YEAR, DOJ, GETDATE()),
                ' Years ',
                DATEDIFF(MONTH, DOJ, GETDATE()) % 12,
                ' Months'
            ) AS ExperienceFormatted,

            DATEDIFF(MONTH, Duration, GETDATE()) AS DurationMonths,

            CONCAT(
                DATEDIFF(YEAR, Duration, GETDATE()),
                ' Years ',
                DATEDIFF(MONTH, Duration, GETDATE()) % 12,
                ' Months'
            ) AS DurationFormatted
        FROM Employee_Mst
        WHERE
            Category = 'Traniee'
            AND CompCode = '$Ccode'
            AND LocCode = '$Lcode'
            AND IsActive = 'Yes'
            AND DATEDIFF(MONTH, DOJ, GETDATE()) <= 12
        ORDER BY DOJ DESC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function Above_6_Month_Trainee_Incomplete($Ccode, $Lcode)
    { {
            $sql = "SELECT
                EmpNo,
                FirstName,
                DOJ,
                Category,
                Duration,
                DeptName,

                DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
                DATEDIFF(YEAR, DOJ, GETDATE()) AS ExperienceYears,
                DATEDIFF(MONTH, DOJ, GETDATE()) % 12 AS ExperienceMonths,

                CONCAT(
                    DATEDIFF(YEAR, DOJ, GETDATE()),
                    ' Years ',
                    DATEDIFF(MONTH, DOJ, GETDATE()) % 12,
                    ' Months'
                ) AS ExperienceFormatted,

                DATEDIFF(MONTH, Duration, GETDATE()) AS DurationMonths,

                CONCAT(
                    DATEDIFF(YEAR, Duration, GETDATE()),
                    ' Years ',
                    DATEDIFF(MONTH, Duration, GETDATE()) % 12,
                    ' Months'
                ) AS DurationFormatted
            FROM Employee_Mst
            WHERE
                Category = 'Traniee'
                AND CompCode = '$Ccode'
                AND LocCode = '$Lcode'
                AND IsActive = 'Yes'
                AND DATEDIFF(MONTH, DOJ, GETDATE()) >= 6
            ORDER BY DOJ DESC";
            $query = $this->db->query($sql);
            return $query->result();
        }
    }

    public function Above_12_Month_Trainee_Incomplete($Ccode, $Lcode)
    { {
            $sql = "SELECT
                EmpNo,
                FirstName,
                DOJ,
                Category,
                Duration,
                DeptName,

                DATEDIFF(MONTH, DOJ, GETDATE()) AS Months_Active,
                DATEDIFF(YEAR, DOJ, GETDATE()) AS ExperienceYears,
                DATEDIFF(MONTH, DOJ, GETDATE()) % 12 AS ExperienceMonths,

                CONCAT(
                    DATEDIFF(YEAR, DOJ, GETDATE()),
                    ' Years ',
                    DATEDIFF(MONTH, DOJ, GETDATE()) % 12,
                    ' Months'
                ) AS ExperienceFormatted,

                DATEDIFF(MONTH, Duration, GETDATE()) AS DurationMonths,

                CONCAT(
                    DATEDIFF(YEAR, Duration, GETDATE()),
                    ' Years ',
                    DATEDIFF(MONTH, Duration, GETDATE()) % 12,
                    ' Months'
                ) AS DurationFormatted
            FROM Employee_Mst
            WHERE
                Category = 'Traniee'
                AND CompCode = '$Ccode'
                AND LocCode = '$Lcode'
                AND IsActive = 'Yes'
                AND DATEDIFF(MONTH, DOJ, GETDATE()) >= 12
            ORDER BY DOJ DESC";
            $query = $this->db->query($sql);
            return $query->result();
        }
    }


    public function Chart_Fro_Attendance_Grade($Ccode, $Lcode)
    {

        //         $sql = "WITH AttendanceData AS (
        //     SELECT
        //         ExistingCode,
        //         FirstName,
        //         TRY_CONVERT(DATE, doj, 103) AS ValidDOJ,
        //         FORMAT(Attn_Date, 'yyyy-MM') AS YearMonth,
        //         SUM(CASE WHEN Present = '1' AND Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        //         COUNT(CASE WHEN Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
        //     FROM LogTime_Days
        //     WHERE Attn_Date >= DATEADD(MONTH, -6, CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE))
        //     AND Attn_Date < CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE)
        //     AND CompCode = '$Ccode'
        //     AND LocCode = '$Lcode'
        //     GROUP BY ExistingCode, FirstName, doj, FORMAT(Attn_Date, 'yyyy-MM')
        // )
        // SELECT
        //     AD.ExistingCode,
        //     AD.FirstName,
        //     AD.ValidDOJ AS doj,
        //     CASE
        //         WHEN AD.ValidDOJ IS NULL THEN 'Invalid Date'
        //         WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 3 THEN 'N' -- New (less than 3 months)
        //         WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 6 THEN 'T' -- Trainee (between 3 and 6 months)
        //         ELSE 'Exp' -- Experienced (6 months or more)
        //     END AS Status,
        //     DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) AS WorkingMonths,

        //     -- Percentage for the last 6 months
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_6,
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_5,
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_4,
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_3,
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_2,
        //     MAX(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //              THEN ROUND(CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) * 100, 0)
        //              ELSE 0 END) AS Percentage_Month_1,

        //     -- Calculating the Average Percentage over the last 6 months
        //     ROUND( (
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //         SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                  THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //     ) * 100 / 6, 0 ) AS Average_Percentage,

        //     -- New Grade Column based on Average Percentage for Exp employees
        //     CASE
        //         WHEN AD.ValidDOJ IS NULL THEN 'Invalid Date'
        //         WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 3 THEN 'N'  -- New
        //         WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) < 6 THEN 'T'  -- Trainee
        //         WHEN DATEDIFF(MONTH, AD.ValidDOJ, GETDATE()) >= 6 THEN
        //             CASE
        //                 WHEN ROUND( (
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //                 ) * 100 / 6, 0 ) BETWEEN 80 AND 90 THEN 'A+'
        //                 WHEN ROUND( (
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //                 ) * 100 / 6, 0 ) BETWEEN 70 AND 80 THEN 'A'
        //                 WHEN ROUND( (
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //                 ) * 100 / 6, 0 ) BETWEEN 60 AND 70 THEN 'B+'
        //                 WHEN ROUND( (
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //                 ) * 100 / 6, 0 ) BETWEEN 50 AND 60 THEN 'B'
        //                 WHEN ROUND( (
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END) +
        //                     SUM(CASE WHEN AD.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM')
        //                              THEN CAST(AD.Present AS FLOAT) / NULLIF(AD.TotalWorkingDays, 0) ELSE 0 END)
        //                 ) * 100 / 6, 0 ) BETWEEN 1 AND 50 THEN 'C'
        //                 ELSE 'No Grade'
        //             END
        //     END AS Grade
        // FROM AttendanceData AD
        // GROUP BY
        //     AD.ExistingCode,
        //     AD.FirstName,
        //     AD.ValidDOJ
        // ";

        $sql = "WITH AttendanceData AS (
    SELECT
        LD.ExistingCode,
        LD.FirstName,
        TRY_CONVERT(DATE, LD.doj, 103) AS ValidDOJ,
        FORMAT(LD.Attn_Date, 'yyyy-MM') AS YearMonth,
        SUM(CASE WHEN LD.Present = '1' AND LD.Wh_Count != '1' THEN 1 ELSE 0 END) AS Present,
        COUNT(CASE WHEN LD.Wh_Count != '1' THEN 1 ELSE NULL END) AS TotalWorkingDays
    FROM LogTime_Days LD
    INNER JOIN Employee_Mst EM ON LD.ExistingCode = EM.ExistingCode
    WHERE LD.Attn_Date >= DATEADD(MONTH, -6, CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE))
        AND LD.Attn_Date < CAST(DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1) AS DATE)
        AND LD.CompCode = '$Ccode'
        AND LD.LocCode = '$Lcode'
        AND EM.IsActive = 'Yes'
    GROUP BY LD.ExistingCode, LD.FirstName, LD.doj, FORMAT(LD.Attn_Date, 'yyyy-MM')
),
MonthlyPercentages AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        YearMonth,
        SUM(CASE WHEN Present > 0 THEN CAST(Present AS FLOAT) / NULLIF(TotalWorkingDays, 0) * 100 ELSE 0 END) AS MonthlyPercentage
    FROM AttendanceData
    GROUP BY ExistingCode, FirstName, ValidDOJ, YearMonth
),
LatestEmployeeList AS (
    SELECT
        ExistingCode,
        FirstName,
        ValidDOJ,
        ROW_NUMBER() OVER (PARTITION BY ExistingCode ORDER BY YearMonth DESC) AS rn
    FROM AttendanceData
)
SELECT
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ AS doj,
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE 'Exp'
    END AS Status,
    DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) AS WorkingMonths,

    -- Last 6 Months Attendance %
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -6, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_6,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -5, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_5,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -4, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_4,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -3, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_3,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -2, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_2,
    MAX(CASE WHEN MP.YearMonth = FORMAT(DATEADD(MONTH, -1, GETDATE()), 'yyyy-MM') THEN ROUND(MP.MonthlyPercentage, 0) ELSE 0 END) AS Percentage_Month_1,

    -- Average Percentage
    ROUND(AVG(MP.MonthlyPercentage), 0) AS Average_Percentage,

    -- Grade
    CASE
        WHEN LE.ValidDOJ IS NULL THEN 'Invalid Date'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 3 THEN 'N'
        WHEN DATEDIFF(MONTH, LE.ValidDOJ, GETDATE()) < 6 THEN 'T'
        ELSE
            CASE
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 80 AND 90 THEN 'A+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 70 AND 79 THEN 'A'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 60 AND 69 THEN 'B+'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 50 AND 59 THEN 'B'
                WHEN ROUND(AVG(MP.MonthlyPercentage), 0) BETWEEN 1 AND 49 THEN 'C'
                ELSE 'C'
            END
    END AS Grade
FROM LatestEmployeeList LE
INNER JOIN MonthlyPercentages MP ON LE.ExistingCode = MP.ExistingCode
WHERE LE.rn = 1
GROUP BY
    LE.ExistingCode,
    LE.FirstName,
    LE.ValidDOJ
";

        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
