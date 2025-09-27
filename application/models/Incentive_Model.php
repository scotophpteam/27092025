

<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Incentive_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }



public function Employee_Position_Details($CompanyCode, $LocationCode, $Login_User, $UserRole, $From_Date, $To_Date, $Employee_Id) {

    if($UserRole == 'HRL'){

         $Sql = "WITH DedupedData AS (
                SELECT 
                    Work.EmpNo,
                    Work.FirstName,
                    Work.WorkArea,
                    Position.Grade,
                    Work.Date,
                    ROW_NUMBER() OVER (PARTITION BY Work.EmpNo, Work.Date ORDER BY Work.Date DESC) AS rn
                FROM 
                    UserDetails_Det AS Supervisor
                INNER JOIN 
                    Web_Employee_Work_Allocation_Mst AS Work 
                    ON Supervisor.Lcode = Work.Lcode 
                    AND Supervisor.Ccode = Work.Ccode 
                INNER JOIN 
                    Web_Work_Area_Mst AS Position 
                    ON Work.Lcode = Position.Lcode 
                    AND Position.Ccode = Work.Ccode 
                    AND Work.Sub_Department = Position.Department 
                    AND Work.WorkArea = Position.WorkArea
                WHERE 
                    Supervisor.Ccode = '$CompanyCode'
                    AND Work.Lcode = '$LocationCode'
                    AND Supervisor.UserID = '$Login_User'
                    AND Work.Date BETWEEN '$From_Date' AND '$To_Date'
                    AND (Work.EmpNo = '$Employee_Id' OR '$Employee_Id' = 'All')
                    AND Work.Assign_Status = '1'
                    AND Work.Work_Status = '1'
                    AND Work.Closing_Status = '1'
            ),
            PivotData AS (
                SELECT 
                    EmpNo,
                    FirstName,
                    WorkArea,
                    Grade,
                    Date
                FROM DedupedData
                WHERE rn = 1
            )

            SELECT 
                p.EmpNo,
                MAX(p.FirstName) AS FirstName,
                MAX(p.WorkArea) AS WorkArea,
                MAX(p.Grade) AS Grade,

                ISNULL(MAX(CASE WHEN DAY(p.Date) = 1 THEN p.Grade END), '') AS [DAY-1],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 2 THEN p.Grade END), '') AS [DAY-2],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 3 THEN p.Grade END), '') AS [DAY-3],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 4 THEN p.Grade END), '') AS [DAY-4],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 5 THEN p.Grade END), '') AS [DAY-5],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 6 THEN p.Grade END), '') AS [DAY-6],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 7 THEN p.Grade END), '') AS [DAY-7],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 8 THEN p.Grade END), '') AS [DAY-8],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 9 THEN p.Grade END), '') AS [DAY-9],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 10 THEN p.Grade END), '') AS [DAY-10],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 11 THEN p.Grade END), '') AS [DAY-11],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 12 THEN p.Grade END), '') AS [DAY-12],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 13 THEN p.Grade END), '') AS [DAY-13],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 14 THEN p.Grade END), '') AS [DAY-14],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 15 THEN p.Grade END), '') AS [DAY-15],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 16 THEN p.Grade END), '') AS [DAY-16],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 17 THEN p.Grade END), '') AS [DAY-17],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 18 THEN p.Grade END), '') AS [DAY-18],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 19 THEN p.Grade END), '') AS [DAY-19],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 20 THEN p.Grade END), '') AS [DAY-20],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 21 THEN p.Grade END), '') AS [DAY-21],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 22 THEN p.Grade END), '') AS [DAY-22],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 23 THEN p.Grade END), '') AS [DAY-23],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 24 THEN p.Grade END), '') AS [DAY-24],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 25 THEN p.Grade END), '') AS [DAY-25],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 26 THEN p.Grade END), '') AS [DAY-26],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 27 THEN p.Grade END), '') AS [DAY-27],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 28 THEN p.Grade END), '') AS [DAY-28],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 29 THEN p.Grade END), '') AS [DAY-29],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 30 THEN p.Grade END), '') AS [DAY-30],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 31 THEN p.Grade END), '') AS [DAY-31],

                COUNT(DISTINCT p.Date) AS Grade_Day_Count,

                SUM(CASE WHEN p.Grade = 'A' THEN 1 ELSE 0 END) AS Total_A_Count,
                SUM(CASE WHEN p.Grade = 'B' THEN 1 ELSE 0 END) AS Total_B_Count,
                SUM(CASE WHEN p.Grade = 'C' THEN 1 ELSE 0 END) AS Total_C_Count

            FROM 
                PivotData p
            GROUP BY 
                p.EmpNo
            ORDER BY 
                p.EmpNo";

    $Query = $this->db->query($Sql);

    if($Query->num_rows() > 0 ){
        return $Query->result();
    } else {
        return 0;
    }



    } else {

         $Sql = "WITH DedupedData AS (
                SELECT 
                    Work.EmpNo,
                    Work.FirstName,
                    Work.WorkArea,
                    Position.Grade,
                    Work.Date,
                    ROW_NUMBER() OVER (PARTITION BY Work.EmpNo, Work.Date ORDER BY Work.Date DESC) AS rn
                FROM 
                    UserDetails_Det AS Supervisor
                INNER JOIN 
                    Web_Employee_Work_Allocation_Mst AS Work 
                    ON Supervisor.Lcode = Work.Lcode 
                    AND Supervisor.Ccode = Work.Ccode 
                    AND Supervisor.Name = Work.Sub_Department
                INNER JOIN 
                    Web_Work_Area_Mst AS Position 
                    ON Work.Lcode = Position.Lcode 
                    AND Position.Ccode = Work.Ccode 
                    AND Work.Sub_Department = Position.Department 
                    AND Work.WorkArea = Position.WorkArea
                WHERE 
                    Supervisor.Ccode = '$CompanyCode'
                    AND Work.Lcode = '$LocationCode'
                    AND Supervisor.UserID = '$Login_User'
                    AND Work.Date BETWEEN '$From_Date' AND '$To_Date'
                    AND (Work.EmpNo = '$Employee_Id' OR '$Employee_Id' = 'All')
                    AND Work.Assign_Status = '1'
                    AND Work.Work_Status = '1'
                    AND Work.Closing_Status = '1'
            ),
            PivotData AS (
                SELECT 
                    EmpNo,
                    FirstName,
                    WorkArea,
                    Grade,
                    Date
                FROM DedupedData
                WHERE rn = 1
            )

            SELECT 
                p.EmpNo,
                MAX(p.FirstName) AS FirstName,
                MAX(p.WorkArea) AS WorkArea,
                MAX(p.Grade) AS Grade,

                ISNULL(MAX(CASE WHEN DAY(p.Date) = 1 THEN p.Grade END), '') AS [DAY-1],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 2 THEN p.Grade END), '') AS [DAY-2],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 3 THEN p.Grade END), '') AS [DAY-3],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 4 THEN p.Grade END), '') AS [DAY-4],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 5 THEN p.Grade END), '') AS [DAY-5],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 6 THEN p.Grade END), '') AS [DAY-6],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 7 THEN p.Grade END), '') AS [DAY-7],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 8 THEN p.Grade END), '') AS [DAY-8],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 9 THEN p.Grade END), '') AS [DAY-9],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 10 THEN p.Grade END), '') AS [DAY-10],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 11 THEN p.Grade END), '') AS [DAY-11],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 12 THEN p.Grade END), '') AS [DAY-12],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 13 THEN p.Grade END), '') AS [DAY-13],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 14 THEN p.Grade END), '') AS [DAY-14],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 15 THEN p.Grade END), '') AS [DAY-15],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 16 THEN p.Grade END), '') AS [DAY-16],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 17 THEN p.Grade END), '') AS [DAY-17],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 18 THEN p.Grade END), '') AS [DAY-18],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 19 THEN p.Grade END), '') AS [DAY-19],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 20 THEN p.Grade END), '') AS [DAY-20],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 21 THEN p.Grade END), '') AS [DAY-21],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 22 THEN p.Grade END), '') AS [DAY-22],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 23 THEN p.Grade END), '') AS [DAY-23],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 24 THEN p.Grade END), '') AS [DAY-24],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 25 THEN p.Grade END), '') AS [DAY-25],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 26 THEN p.Grade END), '') AS [DAY-26],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 27 THEN p.Grade END), '') AS [DAY-27],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 28 THEN p.Grade END), '') AS [DAY-28],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 29 THEN p.Grade END), '') AS [DAY-29],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 30 THEN p.Grade END), '') AS [DAY-30],
                ISNULL(MAX(CASE WHEN DAY(p.Date) = 31 THEN p.Grade END), '') AS [DAY-31],

                COUNT(DISTINCT p.Date) AS Grade_Day_Count,

                SUM(CASE WHEN p.Grade = 'A' THEN 1 ELSE 0 END) AS Total_A_Count,
                SUM(CASE WHEN p.Grade = 'B' THEN 1 ELSE 0 END) AS Total_B_Count,
                SUM(CASE WHEN p.Grade = 'C' THEN 1 ELSE 0 END) AS Total_C_Count

            FROM 
                PivotData p
            GROUP BY 
                p.EmpNo
            ORDER BY 
                p.EmpNo";

    $Query = $this->db->query($Sql);

    if($Query->num_rows() > 0 ){
        return $Query->result();
    } else {
        return 0;
    }

    }

   
}



 


}