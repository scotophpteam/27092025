<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Work_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function Shifts($CompanyCode, $LocationCode)
    {

        $currentTime = date('H:i:s');
        $currentDateTime = new DateTime($currentTime);

        $sql = "SELECT ShiftDesc, StartTime, EndTime FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc != 'GENERAL' ORDER BY ShiftDesc ASC";
        $query = $this->db->query($sql);
        $shifts = $query->result();


        // if ($query->num_rows() > 0) {
        //     $matched = [];

        //     foreach ($shifts as $shift) {
        //         $startTime = new DateTime($shift->StartTime);
        //         $endTime = new DateTime($shift->EndTime);

        //         if ($endTime < $startTime) {
        //             if ($currentDateTime >= $startTime || $currentDateTime <= $endTime) {
        //                 $matched[] = $shift->ShiftDesc;
        //             }
        //         } else {
        //             if ($currentDateTime >= $startTime && $currentDateTime <= $endTime) {
        //                 $matched[] = $shift->ShiftDesc;
        //             }
        //         }
        //     }

        //     return (count($matched) > 0) ? $matched : false;

        // } else {
        //     return false;
        // }

        return $shifts;
    }

    public function Shift_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Type)
    {
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

                $Shift_Master = [
                    'SHIFT1' => 'SHIFT1',
                    'SHIFT2' => 'SHIFT2',
                    'SHIFT3' => 'SHIFT3',
                    'SHIFT4' => 'SHIFT4',
                ];

                $Current_Shift = $Shift;

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

                $Previous_Shift = ($Current_Shift == 'SHIFT1') ? '' : getPreviousShift($Current_Shift, $Shift_Master);

                $Employee_Shift_Sql = "SELECT EmpNo FROM Web_Employee_Work_Allocation_Mst Work
                    INNER JOIN UserDetails_Det Login
                        ON Work.Lcode = Login.Lcode
                        AND Work.Ccode = Login.Ccode
                        AND Login.Name = Work.Sub_Department
                    WHERE Work.Lcode = '$LocationCode'
                        AND Login.Ccode = '$CompanyCode'
                        AND Work.Date = '$Date'
                        AND Work.Shift = '$Previous_Shift'
                        AND Work.Work_Status = '1'
                        AND Login.UserID = '$Login_User'
                        AND Work.Closing_Status = '0'";

                $Employee_Shift_Query = $this->db->query($Employee_Shift_Sql);

                if ($Employee_Shift_Query->num_rows() > 0) {
                    return [
                        'Status' => 'Error',
                        'Message' => 'Kindly close the ' . $Previous_Shift . ' shift and verify it.'
                    ];
                } else {
                    $sql2 = "SELECT DISTINCT
                            Time.MachineID, Emp.FirstName, Emp.Wages, Emp.WorkArea, Emp.JobCardNo,
                            Emp.DeptName, Emp.DeptGrp, Emp.SubSection_Name
                        FROM UserDetails_Det Log
                        INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                        INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                        WHERE Log.UserID = '$Login_User'
                            AND CONVERT(DATE, Time.TimeIN) = '$Shift_Date_Conversion'
                            AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start' AND '$Shift_Date_Conversion $Shift_Pounch_End'
                            AND Emp.CatName != 'STAFF'
                            AND Time.CompCode = '$CompanyCode'
                            AND Time.LocCode = '$LocationCode'
                            AND Emp.IsActive = 'Yes'";




                    $log_Data = $this->db->query($sql2)->result();
                    $current_time = date('Y-m-d H:i:s');

                    foreach ($log_Data as $Employee_Data) {
                        $Employee_Id = $Employee_Data->MachineID;
                        $Employee_WorkArea = $Employee_Data->WorkArea;
                        $Employee_Department = $Employee_Data->DeptName;

                        $existing_sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst
                            WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id' AND Work_Status = '1'";

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
                                'Shift' => $Shift,
                                'Date' => $Date,
                                'Job_Card_No' => $Employee_Data->JobCardNo,
                                'Department' => $Employee_Data->DeptGrp,
                                'Sub_Department' => $Employee_Data->DeptName,
                                'Sub_Section' => $Employee_Data->SubSection_Name,
                                'WorkArea' => $Employee_Data->WorkArea,
                                'Previous_Shift' => '-',
                                'OT_Confirmation' => '-',
                                'Working_Type' => 'SHIFT',
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

                            $this->db->insert('Web_Employee_Work_Allocation_Mst', $allocations);
                        }
                    }

                    $NoWork_Sql = "UPDATE Work
                            SET
                                Work.Sub_Department = Login.Name,
                                Work.WorkArea = '',
                                Work.Job_Card_No = ''
                            FROM Web_Employee_Work_Allocation_Mst AS Work
                            INNER JOIN UserDetails_Det AS Login
                                ON Work.Lcode = Login.Lcode
                                AND Work.Ccode = Login.Ccode
                            WHERE
                                Work.Date = '$Date'
                                AND Work.Shift = '$Shift'
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
                     FROM Web_Employee_Work_Allocation_Mst AS Work
                     INNER JOIN UserDetails_Det AS Login
                         ON Login.Lcode = Work.Lcode
                         AND Login.Name = Work.Sub_Department
                     WHERE Login.UserID = '$Login_User'
                       AND Work.Date = '$Date'
                       AND Work.Shift = '$Shift'
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
                        FROM Web_Employee_Work_Allocation_Mst AS Work
                        INNER JOIN UserDetails_Det AS Login
                            ON Login.Lcode = Work.Lcode
                            AND Login.Name = Work.Sub_Department
                        WHERE Login.UserID = '$Login_User'
                          AND Work.Date = '$Date'
                          AND Work.Shift = '$Shift'
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
                }
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }






    public function User_Department($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT Distinct Name AS Sub_Department from  UserDetails_Det where Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND UserID = '$Login_User'";
        $query = $this->db->query($sql);
        $User_Sub_Department = $query->result();

        if ($query->num_rows() > 0) {

            return $User_Sub_Department;
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


    public function Seperated_Sub_Section($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Section, $Login_User)
    {

        // $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Section = '$Sub_Section'  AND Work_Status = '1' AND Assign_Status = '1'";
        // $query = $this->db->query($sql);
        // $Allocated_List = $query->result();

        // $sql2 = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Section  = '$Sub_Section' AND Work_Status = '1' AND Assign_Status = '0'";
        // $query2 = $this->db->query($sql2);
        // $Un_Allocated_List = $query2->result();

        // $Marge = array_merge($Allocated_List , $Un_Allocated_List);

        // return $Marge;

        $sql4 = "SELECT *,
                            CASE WHEN Work.Assign_Status = '0' THEN 'Unassigned'
                                 WHEN Work.Work_Type = 'NoWork' THEN 'NoWork'
                                 ELSE 'Assigned' END AS WorkStatus
                    FROM Web_Employee_Work_Allocation_Mst Work
                    INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode AND Login.Name = Work.Sub_Department
                    WHERE Login.UserID = '$Login_User'
                    AND Work.Date = '$Date'
                    AND Work.Shift = '$Shift'
                    AND Work.Sub_Section = '$Sub_Section'
                    AND Work.Work_Status = '1'";



        $employee_data = $this->db->query($sql4)->result();
        $Un_Assigned_Data = [];
        $Assigned_Data = [];
        $No_Work_Data = [];

        // Separate the employees into three categories
        foreach ($employee_data as $employee) {
            if ($employee->WorkStatus == 'Unassigned') {
                $Un_Assigned_Data[] = $employee;
            } elseif ($employee->WorkStatus == 'Assigned') {
                $Assigned_Data[] = $employee;
            } else {
                $No_Work_Data[] = $employee;
            }
        }

        $NoWork_Employee_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Work_Type = 'NoWork' AND Date = '$Date' AND Shift = '$Shift'";
        $NoWork_Employee_Query = $this->db->query($NoWork_Employee_Sql);
        $NoWorkEmployeeList = $NoWork_Employee_Query->result();

        // Combine all employee data
        $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data, $No_Work_Data, $NoWorkEmployeeList);

        return $All_Employee_List;
    }








    public function Work_Type($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Department, $Work_Area, $JobCard)
    {



        $sql = "SELECT Machine_Id, Frame FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Department = '$Sub_Department'";
        $query = $this->db->query($sql);
        $Machine_Data = $query->result();

        $sql1 = "SELECT Machine_Id, Frame FROM Web_Employee_Work_Allocation_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Department = '$Sub_Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$JobCard' AND Work_Status = '1' AND Assign_Status = '1'";
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


    public function Frame($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Department, $Work_Area, $JobCard, $Machine_Id)
    {
        $all_balance_machines = []; // This will store the results for all iterations

        foreach ($Machine_Id as $Machine_Ids) {

            $sql = "SELECT Machine_Id, Frame FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_Ids'";
            $query = $this->db->query($sql);
            $Machine_Data = $query->result();

            $query1 = $this->db->query($sql1);
            $Assigned_Machine_Date = $query1->result();

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

            $all_balance_machines = array_merge($all_balance_machines, $Balance_Machines);
        }

        return $all_balance_machines; // Return all machines after the loop finishes
    }


    public function Machine_Ids($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorkArea, $JobCardNo, $Frame)
    {
        try {
            if (empty($Frame) || !is_array($Frame)) {
                return 0;
            }

            $Machine_Data = [];

            foreach ($Frame as $Frames) {
                $sql = "SELECT Machine_Id, Frame
                    FROM Web_Machine_Mst
                    WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND Department = '$Department'
                    AND WorkArea = '$WorkArea'
                    AND Frame = '$Frames'";

                $query = $this->db->query($sql);

                if ($query->num_rows() > 0) {
                    $Machine_Data = array_merge($Machine_Data, $query->result());
                }
            }

            return empty($Machine_Data) ? 0 : $Machine_Data;
        } catch (Exception $e) {
            return 0;
        }
    }


    public function Only_Machine_Id($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Department, $WorkArea, $JobCardNo, $Frame)
    {

        $sql = "SELECT Machine_Id, Frame
                    FROM Web_Machine_Mst
                    WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND Department = '$Department'
                    AND WorkArea = '$WorkArea'";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $Machine_Data = $query->result();

            return $Machine_Data;
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

                            if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
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

                            if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
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

                            if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
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
                                    'Shift' => $Shift,
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
        } else {
            redirect(base_url(), 'refresh');
        }
    }



    private function delete_existing_allocation($Shift, $Date, $Employee_Id)
    {
        $sql_check_existing = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1' ";
        $Query = $this->db->query($sql_check_existing);

        // print_r($sql_check_existing);exit;

        if ($Query->num_rows() > 0) {
            // Delete existing allocation
            $sql_delete = "DELETE FROM Web_Employee_Work_Allocation_Mst WHERE Shift = '$Shift' AND Date = '$Date' AND EmpNo = '$Employee_Id' AND Work_Status = '1'";
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

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
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
                                    'Shift' => $Shift,
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

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Others') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
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

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Multiple Trainee') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
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

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'NoWork allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning NoWork allocation'];
                                    $success = false;
                                }
                            }
                        } elseif ($Frame_Data == 'Trainee' || $Frame_Data == 'TRAINEE') {

                            $Work_Duplicated_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Frame = '$Frame_Data' AND Work_Status = '1' AND Assign_Status = '1' AND EmpNo = '$Employee_Id' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Job_Card_No = '$Job_Card_No'";
                            $Work_Duplicated_Query = $this->db->query($Work_Duplicated_Sql);
                            $Work_Duplicated = $Work_Duplicated_Query->num_rows();

                            if ($Work_Duplicated > 0) {

                                $allocation_details[] = ['status' => 'error', 'message' => 'Allocated Details No Changes.!'];
                            } else {

                                $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
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

                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
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


                        $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


                            if ($Frame_Data == 'Machine Wise') {

                                $machine_data = $this->db->query("SELECT Machine_Name, Machine_Model FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Frame = ''")->result();
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




                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            } else {



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




                                if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Work_Allocation)) {
                                    $allocation_details[] = ['status' => 'success', 'message' => 'Machine allocation assigned successfully'];
                                } else {
                                    $allocation_details[] = ['status' => 'error', 'message' => 'Error assigning machine allocation'];
                                    $success = false;
                                }
                            }
                        }
                    } else {



                        $Update = "UPDATE Web_Employee_Work_Allocation_Mst SET Work_Status = '0', Assign_Status = '0' WHERE Date = '$Date' AND Shift = '$Shift' AND EmpNo = '$Employee_Id'";
                        $Updated_Query = $this->db->query($Update);


                        foreach ($Machine_Id as $index => $Machine_datas) {

                            $Duplicate = $Frames[$index] ?? null; // Get the frame value for the current index, or null if not set
                            $Frame_Data = $Frames[0] ?: $Duplicate; // Set $Frame_Data to "S1" for all machines


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
            return $allocation_details;
        } else {
            redirect(base_url(), 'refresh');
        }
    }





    public function Previous_Allocation($CompanyCode, $LocationCode, $Login_User, $Current_Date, $Shift, $Type)
    {

        $allocation_details = [];

        try {
            $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $query1 = $this->db->query($sql1);
            $shift_Data = $query1->num_rows();

            if ($shift_Data == 1) {
                $Shift_Row = $query1->result();
                $Shift_Pounch_Start = $Shift_Row[0]->StartIN;
                $Shift_Pounch_End = $Shift_Row[0]->EndIN;

                $Shift_Date_Convert = $Current_Date;
                $Shift_Date_Conversion = ($Shift_Row[0]->StartIN_Days == 1 && $Shift_Row[0]->EndIN_Days == 1)
                    ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                    : $Shift_Date_Convert;

                $sql2 = "SELECT DISTINCT
                        Time.MachineID, Emp.FirstName, Emp.Wages, Emp.WorkArea, Emp.JobCardNo, Emp.DeptName, Emp.SubSection_Name
                        FROM UserDetails_Det Log
                        INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode
                        INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                        WHERE Log.UserID = '$Login_User'
                        AND CONVERT(DATE, Time.TimeIN) = '$Shift_Date_Conversion'
                        AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start' AND '$Shift_Date_Conversion $Shift_Pounch_End'
                        AND Emp.CatName != 'STAFF'
                        AND Time.CompCode = '$CompanyCode'
                        AND Time.LocCode = '$LocationCode'
                        AND Emp.WorkArea IS NOT NULL
                        AND Emp.IsActive = 'Yes'";

                $query2 = $this->db->query($sql2);
                $log_Data = $query2->result();

                $Pervious_Date = date('Y-m-d', strtotime($Current_Date . ' -1 days'));

                $sql3 = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Date = '$Pervious_Date' AND Shift = '$Shift' AND Assign_Status = '1' AND Work_Status = '1' AND Closing_Status = '1'";
                $query3 = $this->db->query($sql3);
                $Previous_Day_Employee_List = $query3->result();

                if (!empty($log_Data)) {
                    foreach ($log_Data as $Present_Employee_List) {
                        if (isset($Present_Employee_List->MachineID)) {
                            $Present_Day_Employee = $Present_Employee_List->MachineID;

                            foreach ($Previous_Day_Employee_List as $Previous_Employee_List) {
                                $Previous_Day_Employee = $Previous_Employee_List->EmpNo;

                                if ($Present_Day_Employee == $Previous_Day_Employee) {
                                    $Delete = "DELETE FROM Web_Employee_Work_Allocation_Mst WHERE Date = '$Current_Date' AND Shift = '$Shift' AND EmpNo = '$Present_Day_Employee' AND Assign_Status = '0'";
                                    $Query = $this->db->query($Delete);

                                    $checkDuplicateMachine = "SELECT * FROM Web_Employee_Work_Allocation_Mst
                                                          WHERE Date = '$Current_Date'
                                                          AND Shift = '$Shift'
                                                          AND EmpNo = '{$Previous_Employee_List->EmpNo}'
                                                          AND Work_Status = '1'
                                                          AND Assign_Status = '1'";


                                    $duplicateQuery = $this->db->query($checkDuplicateMachine);
                                    $duplicateResult = $duplicateQuery->num_rows();

                                    if ($duplicateResult == 0) {

                                        $Current_Date_Converstion_Work_Allocation = [
                                            'Ccode'            => $CompanyCode,
                                            'Lcode'            => $LocationCode,
                                            'Wages'            => $Previous_Employee_List->Wages,
                                            'Department'       => $Previous_Employee_List->Department,
                                            'Sub_Department'       => $Previous_Employee_List->Sub_Department,
                                            'Sub_Section' => $Previous_Employee_List->Sub_Section,
                                            'WorkArea'         => $Previous_Employee_List->WorkArea,
                                            'Job_Card_No'      => $Previous_Employee_List->Job_Card_No,
                                            'Date'             => $Current_Date,
                                            'Screen_Type' => 'Previous-Btn-Clicked',
                                            'Shift'            => $Shift,
                                            'EmpNo'            => $Previous_Employee_List->EmpNo,
                                            'FirstName'        => $Previous_Employee_List->FirstName,
                                            'ExistingCode'     => $Previous_Employee_List->ExistingCode,
                                            'Type'             => $Previous_Employee_List->Type,
                                            'Work_Type'        => $Previous_Employee_List->Work_Type,
                                            'Status_Updated'        => $Previous_Employee_List->Work_Type,
                                            'Description'      => $Previous_Employee_List->Description,
                                            'Machine_Name'     => $Previous_Employee_List->Machine_Name,
                                            'Machine_Model'    => $Previous_Employee_List->Machine_Model,
                                            'Machine_Id'       => $Previous_Employee_List->Machine_Id,
                                            'Frame'            => $Previous_Employee_List->Frame,
                                            'FrameType'        => $Previous_Employee_List->FrameType,
                                            'Work_Status'      => $Previous_Employee_List->Work_Status,
                                            'Assign_Status'    => $Previous_Employee_List->Assign_Status,
                                            'IsWork'           => $Previous_Employee_List->IsWork,
                                            'Edit_Reason'      => '',
                                            'Closing_Status'   => '0',
                                            'Work_Start'       => $Previous_Employee_List->Work_Start,
                                            'Work_End'         => $Previous_Employee_List->Work_End,
                                            'Work_Duration'    => $Previous_Employee_List->Work_Duration,
                                            'Machine_EB_No'    => '',
                                            'Remarks'          => $Previous_Employee_List->Remarks,
                                            'Created_By'       => $Login_User,
                                            'Created_Time'     => date('Y-m-d H:i:s'),
                                            'Updated_By'       => '-',
                                            'Updated_Time'     => '-'
                                        ];

                                        if ($this->db->insert('Web_Employee_Work_Allocation_Mst', $Current_Date_Converstion_Work_Allocation)) {
                                            $allocation_details[] = ['status' => 'success', 'message' => 'Previous Day Work Assigned successfully'];
                                        } else {
                                            $allocation_details[] = ['status' => 'error', 'message' => 'Already Previous Day Work Assigned Made No Changes'];
                                        }
                                        // } else {
                                        //     $allocation_details[] = ['status' => 'error', 'message' => 'Already Previous Day Work Assigned Made No Changes'];
                                    }
                                }
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $allocation_details[] = ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
        }

        return $allocation_details;
    }



    public function Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        // $Date = '2025-02-04';

        $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst Work inner join UserDetails_Det Login ON Login.Lcode = Work.Lcode AND Login.Name =  Work.Sub_Department
            where login.UserID = '$Login_User'
            AND Work.Date = '$Date'
            AND Work.Shift = '$Shift'
            AND Work.WorK_Status = '1'
            AND Work.Closing_Status = '0'";

        // print_r($sql);exit;

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {

            $Assigned_Data = $query->result();

            return $Assigned_Data;
        } else {

            return 0;
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

                $date = $Details['Date'];
                $Sub_Department = $Details['Sub_Department'];
                $shift = $Details['Shift'];
                $subSub_Department = $Details['Work_Area'];
                $jobCardNo = $Details['JobCardNo'];
                $Employee_Id = $Details['EmployeeId'];
                $OTStatus = $Details['OTConfirm'];


                // echo '<pre>';
                // print_r($Employee_Id . '==> '.$OTStatus );

                if ($OTStatus == 1) {

                    $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Date = '$date' AND Shift = '$shift' AND EmpNo = '$Employee_Id' AND Sub_Department = '$Sub_Department' AND WorkArea = '$subSub_Department' AND Job_Card_No = '$jobCardNo'";
                    $query = $this->db->query($sql);
                    $Employee_Work_Data = $query->result();

                    // print_r($Employee_Work_Data);

                    foreach ($Employee_Work_Data as $item) {

                        $currentShift = $item->Shift;
                        $NextShift = $this->NextShift($currentShift);

                        $this->db->where([
                            'Date' => $date,
                            'Shift' => $shift,
                            'Assign_Status' => '1',
                            'Work_Status' => '1',
                            'EmpNo' => $Employee_Id
                        ]);

                        // $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst Where Date = '$date' AND Shift = '$shift' AND Assign_Status = '1' AND Work_Status = '1' AND EmpNo = '$Employee_Id' ";
                        // print_r($sql);exit;

                        $this->db->update('Web_Employee_Work_Allocation_Mst', [
                            'Closing_Status' => '1',
                            'Status_Updated' => 'Closed',
                            'Updated_By' => $Session['UserName'],
                            'Updated_Time' => date('Y-m-d H:i:s'),
                        ]);

                        $this->db->where([
                            'Date' => $date,
                            'Shift' => $NextShift,
                            // 'Machine_Id' => $item->Machine_Id,
                            // 'Frame' => $item->Frame,
                            'EmpNo' => $item->EmpNo
                        ]);

                        $existingRecord = $this->db->get('Web_Employee_Work_Allocation_Mst')->num_rows();

                        if ($existingRecord == 0) {

                            $workAllocation = [
                                'Ccode' => $item->Ccode,
                                'Lcode' => $item->Lcode,
                                'Machine_Id' => $item->Machine_Id,
                                'Machine_Name' => $item->Machine_Name,
                                'Machine_Model' => $item->Machine_Model,
                                'FirstName' => $item->FirstName,
                                'EmpNo' => $item->EmpNo,
                                'ExistingCode' => $item->ExistingCode,
                                'Shift' => $NextShift,
                                'Date' => $item->Date,
                                'Job_Card_No' => $item->Job_Card_No,
                                'Work_Type' => $item->Work_Type,
                                'Sub_Department' => $item->Sub_Department,
                                'Sub_Section' => $item->Sub_Section,
                                'WorkArea' => $item->WorkArea,
                                'Frame' => $item->Frame,
                                'FrameType' => $item->FrameType,
                                'Description' => $item->Description,
                                'Work_Start' => $item->Work_Start,
                                'Work_End' => $item->Work_End,
                                'Work_Duration' => $item->Work_Duration,
                                'Machine_EB_No' => $item->Machine_EB_No,
                                'Work_Status' => $item->Work_Status,
                                'Assign_Status' => $item->Assign_Status,
                                'Closing_Status' => '0',
                                'Remarks' => '',
                                'Type' => $item->Type,
                                'Wages' => $item->Wages,
                                'IsWork' => '0',
                                'Created_By' => $item->Created_By,
                                'Created_Time' => $item->Created_Time,
                                'Updated_By' => '-',
                                'Updated_Time' => '-',
                            ];

                            // Insert the new work allocation record
                            $this->db->insert('Web_Employee_Work_Allocation_Mst', $workAllocation);
                        }
                    }
                } else {

                    $this->db->where([
                        'Date' => $date,
                        'Shift' => $shift,
                        'Work_Status' => '1',
                    ]);

                    $this->db->update('Web_Employee_Work_Allocation_Mst', [
                        'Closing_Status' => '1',
                        'Status_Updated' => 'Closed',
                        'Updated_By' => $Session['UserName'],
                        'Updated_Time' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return 1;
        } else {
            redirect(base_url(), 'refresh');
        }
    }




    private function NextShift($currentShift)
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $sql_Shift = "SELECT ShiftDesc FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc != 'GENERAL'";
            $query_Shift = $this->db->query($sql_Shift);
            $Shifts = $query_Shift->result();

            $currentIndex = null;
            foreach ($Shifts as $index => $shift) {
                if ($shift->ShiftDesc == $currentShift) {
                    $currentIndex = $index;
                    break;
                }
            }

            // Ensure that $nextShift is set to a valid value
            if ($currentIndex !== null && isset($Shifts[$currentIndex + 1])) {
                $NextShift = $Shifts[$currentIndex + 1]->ShiftDesc;

                return $NextShift;
            } else {
                $NextShift = ''; // Set a default value if no next shift is found
            }
        } else {
            redirect(base_url());
        }
    }



    public function Assgined_Employee_Id($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Department)
    {

        $sql = "SELECT DISTINCT EmpNo, EmpNo, FirstName FROM Web_Employee_Work_Allocation_Mst Where Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Department = '$Sub_Department' AND Work_Status = '1' AND Assign_Status = '1' AND Closing_Status = '0'";
        $query = $this->db->query($sql);
        $Assigned_Employees = $query->result();

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Assigned_Employees;
        } else {

            return FALSE;
        }
    }


    public function Partial_Close_Work($CompanyCode, $LocationCode, $Date, $Shift, $Sub_Department, $Employee_Id, $Reason)
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            try {
                $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Department = '$Sub_Department' AND EmpNo = '$Employee_Id' AND Work_Status = '1' AND Assign_Status = '1' AND Closing_Status = '0'";
                $query = $this->db->query($sql);
                $Assigned_Employees = $query->result();

                if ($query->num_rows() > 0) {
                    $Current_Time = date('Y-m-d H:i:s');
                    $Login_User = $Session['UserName'];
                    $Employee_Id = $Assigned_Employees[0]->EmpNo;

                    $Work_Partial_Close = array(
                        'Ccode' => $Assigned_Employees[0]->Ccode,
                        'Lcode' => $Assigned_Employees[0]->Lcode,
                        'Department' => $Assigned_Employees[0]->Department,
                        'Sub_Department' => $Assigned_Employees[0]->Sub_Department,
                        'Sub_Section' => $Assigned_Employees[0]->Sub_Section,
                        'WorkArea' => $Assigned_Employees[0]->WorkArea,
                        'Job_Card_No' => $Assigned_Employees[0]->Job_Card_No,
                        'Date' => $Assigned_Employees[0]->Date,
                        'Shift' => $Assigned_Employees[0]->Shift,
                        'EmpNo' => $Assigned_Employees[0]->EmpNo,
                        'FirstName' => $Assigned_Employees[0]->FirstName,
                        'ExistingCode' => $Assigned_Employees[0]->ExistingCode,
                        'Reason' => $Reason,
                        'Closing_Status' => '1',
                        'Work_Start' => $Assigned_Employees[0]->Created_Time,
                        'Work_End' => $Current_Time,
                        'Work_Duration' => '',
                        'Remarks' => '',
                        'Created_By' => $Login_User,
                        'Created_Time' => $Current_Time,
                        'Updated_By' => '-',
                        'Updated_Time' => '-'
                    );

                    $this->db->insert('Web_Work_Partial_Close_Mst', $Work_Partial_Close);

                    if ($this->db->affected_rows() > 0) {
                        $sql1 = "UPDATE Web_Employee_Work_Allocation_Mst SET Status_Updated='Closed', Assign_Status = '1', Closing_Status = '1', Updated_By = '$Login_User', Updated_Time = '$Current_Time' WHERE EmpNo = '$Employee_Id'";
                        $query1 = $this->db->query($sql1);
                    }

                    return TRUE;
                } else {
                    return FALSE;
                }
            } catch (Exception $e) {
                return FALSE;
            }
        } else {
            return;
        }
    }




    public function Late_And_Extra_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Assigning_Shift)
    {
        $sql = "SELECT * FROM Shift_Mst
            WHERE CompCode = '$CompanyCode'
            AND LocCode = '$LocationCode'
            AND ShiftDesc = '$Assigning_Shift'";

        $shift_Data = $this->db->query($sql)->row();

        if ($shift_Data) {
            $Shift_Pounch_Start = $shift_Data->StartTime;
            $Shift_Pounch_End = $shift_Data->EndTime;

            $Shift_Date_Convert = $Date;
            $From_Shift_Date_Convert = $Date;
            $To_Shift_Date_Convert = $Date;

            if ($shift_Data->StartIN_Days == 1) {
                $From_Shift_Date_Convert = date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'));
            }
            if ($shift_Data->EndOUT_Days == 1) {
                $To_Shift_Date_Convert = date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'));
            }


            $sql2 = "SELECT DISTINCT
                    Time.MachineID, Emp.FirstName
                FROM UserDetails_Det Log
                INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                WHERE Log.UserID = '$Login_User'
                -- AND CONVERT(DATE, Time.TimeIN) = '$From_Shift_Date_Convert'
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
                    AND Shift = '$Assigning_Shift'
                    AND Date = '$Date'
                )";

            // print_r($sql2);exit;

            $log_Data = $this->db->query($sql2)->result();

            if ($this->db->query($sql2)->num_rows() > 0) {
                return $log_Data;
            } else {
                return ['status' => 'error', 'message' => 'Employee Name List Not Found Sever'];
            }
        }

        return ['status' => 'error', 'message' => 'Shift data not found'];
    }


    public function Late_And_Extra_Employee_WorkArea($CompanyCode, $LocationCode, $Login_User, $Sub_Department)
    {


        $sql = "SELECT Work.WorkArea from Web_Work_Area_Mst Work INNER JOIN UserDetails_Det Login ON Work.Ccode = Login.Ccode AND Login.Lcode = Work.Lcode AND Login.Name = Work.Department WHERE Login.UserID = '$Login_User' AND Login.Ccode = '$CompanyCode' AND Login.Lcode = '$LocationCode' AND Work.Department = '$Sub_Department'";
        $Query =  $this->db->query($sql);
        $WorkArea_Rows = $Query->num_rows();

        if ($WorkArea_Rows > 0) {

            return $Query->result();
        } else {

            $allocation_details = ['status' => 'error', 'message' => 'Work Area List Not Found Sever'];
            return $allocation_details;
        }
    }


    public function Late_And_Extra_Employee_JobCardNo($CompanyCode, $LocationCode, $Login_User, $WorkArea)
    {

        $sql = "SELECT Job.JobCard_No
FROM Web_JobCard_Mst Job
INNER JOIN Web_Work_Area_Mst Work
    ON Job.Ccode = Work.Ccode
    AND Job.Lcode = Work.Lcode
    AND Job.Department = Work.Department
    AND Job.WorkArea = Work.WorkArea
INNER JOIN UserDetails_Det Login
    ON Work.Ccode = Login.Ccode
    AND Work.Lcode = Login.Lcode
    AND Work.Department = Login.Name
WHERE Login.UserID = '$Login_User'
    AND Login.Ccode = '$CompanyCode'
    AND Login.Lcode = '$LocationCode'
    AND Job.WorkArea = '$WorkArea';";
        $Query =  $this->db->query($sql);
        $WorkArea_Rows = $Query->num_rows();


        if ($WorkArea_Rows > 0) {

            return $Query->result();
        } else {

            $allocation_details = ['status' => 'error', 'message' => 'Work Area List Not Found Sever'];
            return $allocation_details;
        }
    }

    public function Late_Employee_Frame($CompanyCode, $LocationCode, $Login_User, $WorkArea, $Department, $Date, $Shift)
    {


        $sql = "SELECT Machine_Id, Frame
                    FROM Web_Machine_Mst
                    WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND Department = '$Department'
                    AND WorkArea = '$WorkArea'";

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            $Machine_Data = $query->result();

            return $Machine_Data;
        } else {
            $allocation_details = [];
            return $allocation_details;
        }
    }


    public function Late_Employee_Machine_Id($CompanyCode, $LocationCode, $Login_User, $WorkArea, $Department, $Frame)
    {


        foreach ($Frame as $Frames) {

            if ($Frames == 'Machine Wise') {

                $sql = "SELECT Machine_Id
                    FROM Web_Machine_Mst
                    WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND Department = '$Department'
                    AND WorkArea = '$WorkArea'";


                $query = $this->db->query($sql);


                if ($query->num_rows() > 0) {
                    $Machine_Data = $query->result();

                    return $Machine_Data;
                } else {
                    $allocation_details = ['status' => 'error', 'message' => 'Work Area List Not Found Sever'];
                    return $allocation_details;
                }
            } else {

                $sql = "SELECT Machine_Id
                    FROM Web_Machine_Mst
                    WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND Department = '$Department'
                    AND WorkArea = '$WorkArea'
                    AND Frame = '$Frames'";

                $query = $this->db->query($sql);


                if ($query->num_rows() > 0) {
                    $Machine_Data = $query->result();

                    return $Machine_Data;
                } else {
                    $allocation_details = ['status' => 'error', 'message' => 'Work Area List Not Found Sever'];
                    return $allocation_details;
                }
            }
        }
    }


    public function Late_And_Extra_Employee_Sub_Department($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT Name as Sub_Department FROM UserDetails_Det WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND UserID = '$Login_User'";
        $Query = $this->db->query($sql);
        $Rows = $Query->num_rows();

        if ($Rows > 0) {

            return $Query->result();
        } else {

            $Status = [
                'Status' => 'Error',
                'Message' => 'This User Sub Departments Not Found!'
            ];

            return $Status;
        }
    }

    public function Sepereted_Employee_List($CompanyCode, $LocationCode, $Login_User, $Shift, $Date, $Sub_Section)
    {


        $All_Employee_Close_Check_Sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst Work
                                          INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                         AND Login.Ccode = Work.Ccode
                                         AND Login.Name = Work.Sub_Department
                                         WHERE login.UserID = '$Login_User'
                                         AND Work.Date = '$Date'
                                         AND Work.Shift = '$Shift'
                                         AND Work.WorK_Status = '1'
                                         AND Work.Sub_Section = '$Sub_Section'
                                         AND Work_Type != 'NoWork'
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

    public function Late_Employee_Sub_Section_Wise($CompanyCode, $LocationCode, $Login_User, $Assigning_Shift, $Date, $Sub_Section)
    {

        $sql = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Assigning_Shift'";
        $shift_Data = $this->db->query($sql)->row();

        if ($shift_Data) {

            $Shift_Pounch_Start = $shift_Data->StartTime;
            $Shift_Pounch_End = $shift_Data->EndTime;

            $Shift_Date_Convert = $Date;
            $From_Shift_Date_Convert = $Date;
            $To_Shift_Date_Convert = $Date;

            if ($shift_Data->StartIN_Days == 1) {

                $From_Shift_Date_Convert = date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'));
            } else if ($shift_Data->EndOUT_Days == 1) {

                $To_Shift_Date_Convert = date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'));
            }

            // Step 2: Get Employees Data
            $sql2 = "SELECT DISTINCT
                    Time.MachineID, Emp.FirstName
                    FROM UserDetails_Det Log
                    INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode
                    AND Log.Name = Emp.DeptName
                    INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID
                    WHERE Log.UserID = '$Login_User'
                    AND CONVERT(DATE, Time.TimeIN) = '$Shift_Date_Convert'
                    AND Time.TimeIN BETWEEN '$From_Shift_Date_Convert $Shift_Pounch_Start' AND '$To_Shift_Date_Convert $Shift_Pounch_End'
                    AND Emp.CatName != 'STAFF'
                    AND Time.CompCode = '$CompanyCode'
                    AND Time.LocCode = '$LocationCode'
                    AND Emp.SubSection_Name = '$Sub_Section'
                    AND Emp.WorkArea IS NOT NULL
                    AND Emp.IsActive = 'Yes'
                    AND Time. MachineID  NOT IN  (

                    SELECT EmpNo from Web_Employee_Work_Allocation_Mst Where Lcode = '$LocationCode'   AND Work_Status = '1' and CONVERT(varchar,Date,103) = CONVERT(vARCHAR,'$Date',103)
                    AND Wages != 'STAFF' AND Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND  Date = '$Date' AND Shift = '$Assigning_Shift' )";

            $log_Data = $this->db->query($sql2)->result();

            //    print_r( $sql2);exit;

            if ($this->db->query($sql2)->num_rows() > 0) {

                return  $log_Data;
            } else {

                $allocation_details = ['status' => 'error', 'message' => 'Employee Name List Not Found Sever'];
                return $allocation_details;
            }
        }
    }

    public function Sub_Section_Assgined_Employee_Id($CompanyCode, $LocationCode, $Login_User, $Shift, $Date, $Sub_Section, $Sub_Department)
    {

        $sql = "SELECT DISTINCT EmpNo, EmpNo, FirstName FROM Web_Employee_Work_Allocation_Mst Where Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Sub_Department = '$Sub_Department' AND Work_Status = '1' AND Assign_Status = '1' AND Closing_Status = '0' AND Sub_Section = '$Sub_Section'";
        $query = $this->db->query($sql);
        $Assigned_Employees = $query->result();

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Assigned_Employees;
        } else {

            return FALSE;
        }
    }

    public function Work_Allocation_Details_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {

        $Sql = "SELECT
    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '1'

                    THEN W.EmpNo
                    ELSE NULL
                  END) AS Work_Allocated_Count,

    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '0'
                         AND W.Work_Type != 'NoWork'
                    THEN W.EmpNo
                    ELSE NULL
                  END) AS Un_Allocated_Count,

    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '1'
                         AND W.Type = 'SHIFT'
                    THEN W.EmpNo
                    ELSE NULL
                  END) AS Shift_Count,

    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '1'
                         AND W.Type = 'LATE'
                    THEN W.EmpNo
                    ELSE NULL
                  END) AS Late_Count,

    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '1'
                         AND W.Closing_Status = '1'
                    THEN W.EmpNo
                    ELSE NULL
                  END) AS Shift_Closing_Count,

    COUNT(DISTINCT CASE
                    WHEN W.Work_Status = '1'
                         AND W.Assign_Status = '0'
                         AND W.Work_Type = 'NoWork'
                    THEN W.EmpNo
                    ELSE NULL
                  END) AS No_Work_Count

FROM
    Web_Employee_Work_Allocation_Mst W
INNER JOIN
    UserDetails_Det U ON W.Lcode = U.Lcode
                     AND W.Ccode = U.Ccode
                     AND W.Sub_Department = U.Name
WHERE
    U.UserID = '$Login_User'
    AND W.Lcode = '$LocationCode'
    AND W.Ccode = '$CompanyCode'
    AND W.Date = '$Date'
    AND W.Shift = '$Shift'";

        $query = $this->db->query($Sql);
        $Employee_Count_Details = $query->result();

        // print_r($sql);exit;

        if ($query->num_rows() > 0) {

            return $Employee_Count_Details;
        } else {

            return FALSE;
        }
    }

    public function Get_Allocated_Machine_ID($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {
        // 1. Get Total, Allocated, and Unallocated Counts
        $Sql_Get_Counts = "SELECT
        (SELECT COUNT(DISTINCT M.Machine_Id)
         FROM Web_Machine_Mst AS M
         INNER JOIN UserDetails_Det AS Login
             ON M.Department = Login.Name
         WHERE M.Lcode = '$LocationCode'
           AND M.Ccode = '$CompanyCode'
           AND Login.Ccode = '$CompanyCode'
           AND Login.UserID = '$Login_User') AS Total_Machines,

        (SELECT COUNT(DISTINCT Work.Machine_Id)
         FROM UserDetails_Det AS LoginSub
         INNER JOIN Web_Employee_Work_Allocation_Mst AS Work
             ON LoginSub.Lcode = Work.Lcode
             AND LoginSub.Ccode = Work.Ccode
             AND LoginSub.Name = Work.Sub_Department
         WHERE Work.Lcode = '$LocationCode'
           AND LoginSub.Ccode = '$CompanyCode'
           AND LoginSub.UserID = '$Login_User'
           AND Work.Work_Type = 'Machine'
           AND Work.Work_Status = '1'
           AND Work.Assign_Status = '1'
           AND Work.Closing_Status = '0'
           AND Work.Date = '$Date') AS Allocated_Count,

        ((SELECT COUNT(DISTINCT M.Machine_Id)
          FROM Web_Machine_Mst AS M
          INNER JOIN UserDetails_Det AS Login
              ON M.Department = Login.Name
          WHERE M.Lcode = '$LocationCode'
            AND M.Ccode = '$CompanyCode'
            AND Login.Ccode = '$CompanyCode'
            AND Login.UserID = '$Login_User') -
         (SELECT COUNT(DISTINCT Work.Machine_Id)
          FROM UserDetails_Det AS LoginSub
          INNER JOIN Web_Employee_Work_Allocation_Mst AS Work
              ON LoginSub.Lcode = Work.Lcode
              AND LoginSub.Ccode = Work.Ccode
              AND LoginSub.Name = Work.Sub_Department
          WHERE Work.Lcode = '$LocationCode'
            AND LoginSub.Ccode = '$CompanyCode'
            AND LoginSub.UserID = '$Login_User'
            AND Work.Work_Type = 'Machine'
            AND Work.Work_Status = '1'
            AND Work.Assign_Status = '1'
            AND Work.Closing_Status = '0'
            AND Work.Date = '$Date')) AS Unallocated_Count";

        $query1 = $this->db->query($Sql_Get_Counts);
        $count_result = $query1->result();

        // 2. Get Unallocated (Balance) Machine IDs
        $Sql_Get_Unallocated_Machines = "
        SELECT M.Machine_Id, M.Frame
        FROM Web_Machine_Mst AS M
        INNER JOIN UserDetails_Det AS Login
            ON M.Department = Login.Name
        WHERE M.Lcode = '$LocationCode'
          AND M.Ccode = '$CompanyCode'
          AND Login.Ccode = '$CompanyCode'
          AND Login.UserID = '$Login_User'
          AND M.Machine_Id NOT IN (
              SELECT Work.Machine_Id
              FROM UserDetails_Det AS LoginSub
              INNER JOIN Web_Employee_Work_Allocation_Mst AS Work
                  ON LoginSub.Lcode = Work.Lcode
                  AND LoginSub.Ccode = Work.Ccode
                  AND LoginSub.Name = Work.Sub_Department
              WHERE Work.Lcode = '$LocationCode'
                AND LoginSub.Ccode = '$CompanyCode'
                AND LoginSub.UserID = '$Login_User'
                AND Work.Work_Type = 'Machine'
                AND Work.Work_Status = '1'
                AND Work.Assign_Status = '1'
                AND Work.Closing_Status = '0'
                AND Work.Date = '$Date'
          )
    ";

        $query2 = $this->db->query($Sql_Get_Unallocated_Machines);
        $unallocated_machines = $query2->result();

        // Return combined result
        if ($query1->num_rows() > 0) {
            return [
                'Counts' => $count_result[0],           // Object with Total, Allocated, Unallocated
                'Unallocated_Machines' => $unallocated_machines  // Array of machine objects
            ];
        } else {
            return 0;
        }
    }
}
