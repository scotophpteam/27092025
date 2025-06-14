<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Home_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Trainee_Employee_List($Ccode, $Lcode)
    {

        $sql = "SELECT
            EmpNo,
            FirstName,
            DOJ,
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
            AND DATEDIFF(MONTH, DOJ, GETDATE()) >= 1
        ORDER BY DOJ DESC
    ";



        $query = $this->db->query($sql);

        return $query->result_array();
    }
}
