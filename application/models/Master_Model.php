<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Master_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    public function Location_Code()
    {

        $sql = "SELECT LocCode FROM Location_Mst";
        $query = $this->db->query($sql);
        $User_Department = $query->result();

        if ($query->num_rows() > 0) {

            return $User_Department;
        } else {

            return 0;
        }
    }

    public function Department($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT Distinct Name AS Department from  UserDetails_Det where Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND UserID = '$Login_User'";
        $query = $this->db->query($sql);
        $User_Department = $query->result();

        if ($query->num_rows() > 0) {

            return $User_Department;
        } else {

            return 0;
        }
    }

    public function Work_Areas($CompanyCode, $LocationCode, $Department, $Login_User)
    {

        $sql = "SELECT WorkArea FROM Web_Work_Area_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Department'";
        $query = $this->db->query($sql);
        $Work_Areas = $query->result();

        if ($query->num_rows() > 0) {

            return $Work_Areas;
        } else {

            return 0;
        }
    }


    public function JobCards($CompanyCode, $LocationCode, $Department, $Work_Area, $Login_User)
    {

        $sql = "SELECT JobCard_No FROM Web_JobCard_Mst WHERE CCode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Department'  AND WorkArea = '$Work_Area' ORDER BY WorkArea ASC";
        $query = $this->db->query($sql);

        $Row = $query->result();

        if ($query->num_rows() > 0) {
            return $Row;
        } else {
            return false;
        }
    }



    public function Wages($CompanyCode, $LocationCode, $Department)
    {

        //    $sql = "SELECT wages,
        //        CASE
        //            WHEN wages IN ('A2') THEN 'Permanent'
        //            WHEN wages IN ('A3') THEN 'Contract'
        //            WHEN wages IN ('OSP') THEN 'OSP'
        //            WHEN wages IN ('BALE PRESS') THEN 'BALE PRESS'
        //            WHEN wages IN ('SCHEME') THEN 'SCHEME'

        //        END AS wage_category
        //             FROM Employee_Mst
        //             WHERE CompCode = '$CompanyCode'
        //             AND LocCode = '$LocationCode'
        //             AND DeptName = '$Department'
        //             AND wages IN ('A2', 'A3', 'M2', 'M3', 'M4', 'CONTRACT-SSI', 'CONTRACT-BE', 'CONTRACT-SRF', 'CONTRACT', 'APPRENTICE', 'LOADING', 'OTHERS', 'SCHEME', 'FNG LOADING', 'EXTERNAL SCHEME' , 'OSP','BALE PRESS')
        //             ORDER BY wages ASC;
        //             ";

        $sql = "SELECT distinct wages from Employee_Mst where CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND Wages!='A3' AND CatName != 'Staff' AND DeptName= '$Department'";



        $query = $this->db->query($sql);
        $Row = $query->result();

        if ($query->num_rows() > 0) {
            return $Row;
        } else {
            return false;
        }
    }

    public function Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Department, $Employee_Type, $Work_Area, $JobCard, $Login_User)
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            // Step 1: Get Shift Data
            $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $shift_Data = $this->db->query($sql1)->row();

            if ($shift_Data) {

                $Shift_Pounch_Start = $shift_Data->StartTime;
                $Shift_Pounch_End = $shift_Data->EndTime;
                $Shift_Date_Convert = $Date;
                $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                    ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                    : $Shift_Date_Convert;

                // Step 2: Get Employees Data
                $sql2 = "SELECT DISTINCT
    Emp.MachineID, Emp.FirstName, Emp.Wages, Emp.WorkArea, Emp.JobCardNo, Emp.DeptName,
    Time.TimeIN,
    CASE WHEN EXISTS (
        SELECT 1
        FROM Web_Employee_Work_Allocation_Mst Web
        WHERE Web.EmpNo = Emp.MachineID
          AND Web.Date = '$Date'
          AND Web.Shift = '$Shift'
          AND Web.Ccode = '$CompanyCode'
          AND Web.Lcode = '$LocationCode'
    ) THEN 'Yes' ELSE 'No' END AS AllocationExists
FROM UserDetails_Det Log
INNER JOIN Employee_Mst Emp ON Log.Lcode = Emp.LocCode
INNER JOIN LogTime_IN Time ON Time.MachineID = Emp.MachineID AND
Emp.DeptName = Log.Name
WHERE Log.UserID = '$Login_User'
  AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start' AND '$Shift_Date_Conversion $Shift_Pounch_End'
  AND Emp.CatName != 'STAFF'
  AND Time.CompCode = '$CompanyCode'
  AND Time.LocCode = '$LocationCode'
  AND Emp.WorkArea IS NOT NULL
  AND Emp.IsActive = 'Yes'";


                $log_Data = $this->db->query($sql2)->result();

                // Step 3: Prepare Data for Insertions
                $allocations = [];
                $current_time = date('Y-m-d H:i:s');

                foreach ($log_Data as $Employee_Data) {
                    if (empty($Employee_Data->AllocationExists)) { // If not already allocated
                        $allocations[] = [
                            'Ccode' => $CompanyCode,
                            'Lcode' => $LocationCode,
                            'Wages' => $Employee_Data->Wages,
                            'FirstName' => $Employee_Data->FirstName,
                            'EmpNo' => $Employee_Data->MachineID,
                            'Shift' => $Shift,
                            'Date' => $Date,
                            'Job_Card_No' => $Employee_Data->JobCardNo,
                            'Department' => $Employee_Data->DeptName,
                            'WorkArea' => $Employee_Data->WorkArea,
                            'Machine_Id' => '-',
                            'Machine_Name' => '-',
                            'FrameType' => '-',
                            'Frame' => '-',
                            'Type' => $Type,
                            'Work_Type' => '-',
                            'Work_Start' => '-',
                            'Work_End' => '-',
                            'Work_Duration' => '-',
                            'Machine_EB_No' => '-',
                            'Work_Status' => '1',
                            'Assign_Status' => '0',
                            'Closing_Status' => '0',
                            'IsWork' => '0',
                            'Edit_Reason' => '-',
                            'Created_By' => $Login_User,
                            'Created_Time' => $current_time,
                            'Updated_By' => '-',
                            'Updated_Time' => '-',
                        ];
                    }
                }

                // Insert all allocations at once
                if (!empty($allocations)) {
                    $this->db->insert_batch('Web_Employee_Work_Allocation_Mst', $allocations);
                }

                // Step 4: Get Employee Lists in one go
                $sql4 = "SELECT Work.*,
                            CASE WHEN Work.Assign_Status = '0' THEN 'Unassigned'
                                 WHEN Work.Work_Type = 'NoWork' THEN 'NoWork'
                                 ELSE 'Assigned' END AS WorkStatus
                    FROM Web_Employee_Work_Allocation_Mst Work
                    WHERE Work.Date = '$Date'
                    AND Work.Shift = '$Shift'
                    AND Work.Work_Status = '1'";

                $employee_data = $this->db->query($sql4)->result();

                // Separate the employees into three categories
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

                // Combine all employee data
                $All_Employee_List = array_merge($Assigned_Data, $Un_Assigned_Data, $No_Work_Data);

                return $All_Employee_List;
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Machines($CompanyCode, $LocationCode, $Date, $Shift, $Department, $Work_Area, $JobCard)
    {


        $sql = "SELECT Machine_Id, Frame FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Department = '$Department'";
        $query = $this->db->query($sql);
        $Machine_Data = $query->result();

        // print_r($sql);exit;

        $sql1 = "SELECT Machine_Id, Frame FROM Web_Employee_Work_Allocation_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Work_Status = '1' AND Assign_Status = '1'";
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





    public function Machine_Frames($CompanyCode, $LocationCode, $Date, $Shift, $Department, $Work_Area, $JobCard, $Machine_Id)
    {


        // $Date = '2025-01-24';

        foreach ($Machine_Id as $Machine_Ids) {

            $sql = "SELECT Machine_Id, Frame FROM Web_Machine_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_Ids'";
            $query = $this->db->query($sql);
            $Machine_Data = $query->result();

            //  print_r($sql);exit;

            $sql1 = "SELECT Machine_Id, Frame FROM Web_Employee_Work_Allocation_Mst WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND Date = '$Date' AND Shift = '$Shift' AND Department = '$Department' AND WorkArea = '$Work_Area' AND Machine_Id = '$Machine_Ids' AND Work_Status = '1' AND Assign_Status = '1'";
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

            return $Balance_Machines;
        }
    }


    public function Machine_Master($CompanyCode, $LocationCode)
    {

        $sql = "SELECT * FROM Web_Machine_Mapping_Mst where Ccode = '$CompanyCode' AND Lcode = '$LocationCode'";
        $query = $this->db->query($sql);
        $Row = $query->result();

        return $Row;
    }

    public function Standard_Actual_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {
        $sql = "SELECT DISTINCT
                    Stand.Department, Stand.Sub_Department, Stand.Position, Stand.Position_Id, Stand.Employee_Count
                FROM
                    UserDetails_Det Login
                INNER JOIN
                    Web_Standard_Mst Stand ON Login.Ccode = Stand.Ccode
                    AND Login.Lcode = Stand.Lcode
                    AND Login.Name = Stand.Sub_Department
                WHERE Login.UserID = '$Login_User'
                    AND Stand.Ccode = '$CompanyCode'
                    AND Stand.Lcode = '$LocationCode'
                    AND Stand.Shift = '$Shift'";


        $query = $this->db->query($sql);
        $Standard_Data = $query->result();


        $Sql_Shift = "SELECT * FROM Shift_Mst Where CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc != 'GENERAL'";
        $query_Shift = $this->db->query($Sql_Shift);
        $Shift_Rows = $query_Shift->num_rows();

        $result = [];

        foreach ($Standard_Data as $Data) {

            $Department = $Data->Department;
            $Sub_Department = $Data->Sub_Department;
            $Position = $Data->Position;
            $Position_Id = $Data->Position_Id;
            $Standard_Count = $Data->Employee_Count;

            $Employee_Details_Sql = "SELECT DISTINCT EmpNo
                                    FROM Web_Employee_Work_Allocation_Mst
                                    WHERE Ccode = '$CompanyCode'
                                    AND Lcode = '$LocationCode'
                                    AND Date = '$Date'
                                    AND Shift = '$Shift'
                                    AND Sub_Department = '$Sub_Department'
                                    AND WorkArea = '$Position'
                                    AND Assign_Status = '1'
                                    AND Work_Status = '1'";

                                                        

            $employee_query = $this->db->query($Employee_Details_Sql);
            $Actual_Count = $employee_query->num_rows();

            // Calculate NeedCount or ExcessCount based on the difference between Standard and Actual
            $Status = 'OK';
            $needCount = 0;
            $excessCount = 0;

            if ($Actual_Count > $Standard_Count) {
                $Status = 'Excess';
                $excessCount = $Actual_Count - $Standard_Count;  // Excess is the difference
            } elseif ($Actual_Count < $Standard_Count) {
                $Status = 'Need';
                $needCount = $Standard_Count - $Actual_Count; // Need is the difference
            }

            // Build the Status string conditionally
            $statusString = $Status;
            if ($Status != 'OK') {
                if ($needCount > 0 && $excessCount > 0) {
                    $statusString .= ' (NeedCount: ' . $needCount . ', ExcessCount: ' . $excessCount . ')';
                } elseif ($needCount > 0) {
                    $statusString .= ' (NeedCount: ' . $needCount . ')';
                } elseif ($excessCount > 0) {
                    $statusString .= ' (ExcessCount: ' . $excessCount . ')';
                }
            }

            $result[] = [
                'Department' => $Department,
                'Sub_Department' => $Sub_Department,
                'Position' => $Position,
                'Position_Id' => $Position_Id,
                'Standard' => $Standard_Count,
                'Actual' => $Actual_Count,
                'Status' => $statusString
            ];
        }

        return $result;
    }




    public function Departments($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT DISTINCT DeptGrp FROM  UserDetails_Det login INNER JOIN Employee_Mst Emp ON Login.Ccode = Emp.CompCode AND Login.Ccode = Emp.CompCode AND Login.Name = Emp.DeptName
    WHERE Login.Lcode = '$LocationCode'
    AND Login.Ccode ='$CompanyCode'
    AND Login.UserID = '$Login_User'
     AND DeptGrp IS NOT NULL;";

        $query = $this->db->query($sql);
        $Row = $query->num_rows();

        if ($Row > 0) {

            return $query->result();
        } else {

            $Message = [
                'Status' => 'Error',
                'Message' => 'Department Details Not Fount!..'
            ];

            return $Message;
        }
    }


    public function Sub_Departments($CompanyCode, $LocationCode, $Login_User, $Department)
    {

        $sql = "SELECT DISTINCT Emp.DeptName FROM  UserDetails_Det login INNER JOIN Employee_Mst Emp ON Login.Ccode = Emp.CompCode AND Login.Ccode = Emp.CompCode AND Login.Name = Emp.DeptName
    WHERE Login.Lcode = '$LocationCode'
    AND Login.Ccode ='$CompanyCode'
    AND Login.UserID = '$Login_User'
     AND Emp.DeptName IS NOT NULL;";

        $query = $this->db->query($sql);
        $Row = $query->num_rows();

        if ($Row > 0) {

            return $query->result();
        } else {

            $Message = [
                'Status' => 'Error',
                'Message' => 'Sub Department Details Not Fount!..'
            ];

            return $Message;
        }
    }


    public function Position($CompanyCode, $LocationCode, $Login_User, $Department, $Sub_Department)
    {

        $sql = "SELECT DISTINCT Work.WorkArea FROM  UserDetails_Det login INNER JOIN Web_Work_Area_Mst Work ON Login.Ccode = Work.Ccode AND Login.Ccode = Work.Ccode AND Login.Name = Work.Department
    WHERE Login.Lcode = '$LocationCode'
    AND Login.Ccode ='$CompanyCode'
    AND Login.UserID = '$Login_User'
    AND Work.Department = '$Sub_Department'
    AND Login.Name = '$Sub_Department'
    AND Work.WorkArea IS NOT NULL;";

        $query = $this->db->query($sql);
        $Row = $query->num_rows();

        if ($Row > 0) {

            return $query->result();
        } else {

            $Message = [
                'Status' => 'Error',
                'Message' => 'Sub Department Details Not Fount!..'
            ];

            return $Message;
        }
    }

    public function Insert_Standard_Actual($CompanyCode, $LocationCode, $Login_User, $Department, $Sub_Department, $Work_Area, $Employee_Count, $Shift)
    {

        $sql = "SELECT JobCard_No FROM Web_JobCard_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Sub_Department' AND WorkArea = '$Work_Area'";
        $query = $this->db->query($sql);
        $result = $query->result();

        $current_time = date('Y-m-d H:i:s');

        $Check_sql = "SELECT * FROM Web_Standard_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Department' AND Sub_Department = '$Sub_Department' AND Position = '$Work_Area' AND Shift = '$Shift'";
        $Check_Query = $this->db->query($Check_sql);
        $Check_Rows = $Check_Query->num_rows();

        if ($Check_Rows > 0) {
            return 2;
        } else {
            $this->db->select('Standard_ID');
            $this->db->order_by('Standard_ID', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get('Web_Standard_Mst');

            if ($query->num_rows() > 0) {
                $last_standard_id = $query->row()->Standard_ID;
                preg_match('/\d+/', $last_standard_id, $matches);
                $last_number = (int) $matches[0];
                $next_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
                $next_standard_id = 'SD' . $next_number;
            } else {
                $next_standard_id = 'SD0001';
            }

            $Standard_Data = [
                'Ccode' =>  $CompanyCode,
                'Lcode' => $LocationCode,
                'Standard_ID' => $next_standard_id,
                'Department' => $Department,
                'Shift' => $Shift,
                'Sub_Department' => $Sub_Department,
                'Position' => $Work_Area,
                'Position_Id' => '-',
                'Employee_Count' => $Employee_Count,
                'Status' => 'Active',
                'Created_By' => $Login_User,
                'Created_Time' => $current_time,
                'Updated_By' => '-',
                'Updated_Time' => '-'
            ];

            $this->db->insert('Web_Standard_Mst', $Standard_Data);

            if ($this->db->affected_rows() > 0) {
                return 1;
            } else {
                return 0;
            }
        }
    }


    public function Standard_Master_List($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT DISTINCT Stand.Standard_ID,Stand.Department, Stand.Sub_Department, Stand.Position,Stand.Employee_Count,Stand.Status, Stand.Shift FROM UserDetails_Det Login INNER JOIN Web_Standard_Mst Stand ON Login.Ccode = Stand.Ccode AND Login.Lcode = Stand.Lcode AND Login.Name = Stand.Sub_Department AND Login.UserID = '$Login_User' AND Login.Ccode = '$CompanyCode' AND  Login.Lcode = '$LocationCode' AND Login.UserID = '$Login_User'";
        $query = $this->db->query($sql);
        $result = $query->result();

        // print_r($sql);exit;

        $Check_Rows = $query->num_rows();

        if ($Check_Rows > 0) {

            return $result;
        } else {

            return 0;
        }
    }

    public function Standard_Edit_List($CompanyCode, $LocationCode, $Login_User, $Standard_ID)
    {

        $sql = "SELECT Standard_ID,Department,Sub_Department,Position,Employee_Count FROM  Web_Standard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Standard_ID = '$Standard_ID'";
        $query = $this->db->query($sql);
        $result = $query->result();

        $Check_Rows = $query->num_rows();

        if ($Check_Rows > 0) {

            return $result;
        } else {

            return 0;
        }
    }

    public function Standard_Edit($CompanyCode, $LocationCode, $Login_User, $Standard_ID, $Employee_Count)
    {

        $Sql = "UPDATE Web_Standard_Mst SET Employee_Count = '$Employee_Count' WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Standard_ID = '$Standard_ID'";

        $query = $this->db->query($Sql);

        if ($query) {
            return 1;
        } else {
            return 0;
        }
    }

    public function Standard_Delete($CompanyCode, $LocationCode, $Login_User, $Standard_ID)
    {

        $sql = "DELETE FROM Web_Standard_Mst WHERE Lcode = '$LocationCode' AND Ccode = '$CompanyCode' AND Standard_ID = '$Standard_ID'";
        $query = $this->db->query($sql);

        if ($query) {
            return 1;
        } else {
            return 0;
        }
    }

    public function Supervisor_List($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT Emp.EmpNo,Emp.FirstName FROM UserDetails_Det Login INNER JOIN Employee_Mst Emp ON Login.Lcode = Emp.LocCode AND Login.Ccode = Emp.CompCode AND Login.Name = Emp.DeptName AND Emp.wages = 'STAFF' AND Login.Lcode = '$LocationCode' AND Login.Ccode = '$CompanyCode' AND Login.UserID = '$Login_User'";
        $query = $this->db->query($sql);
        $result = $query->result();


        $Check_Rows = $query->num_rows();

        if ($Check_Rows > 0) {

            return $result;
        } else {

            return 0;
        }
    }



    public function Machine_Sub_Department($CompanyCode, $LocationCode, $Login_User)
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

    public function Work_Area($CompanyCode, $LocationCode, $Department, $Login_User)
    {

        $sql = "SELECT WorkArea FROM Web_Work_Area_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Department'";
        $query = $this->db->query($sql);
        $Work_Areas = $query->result();

        if ($query->num_rows() > 0) {

            return $Work_Areas;
        } else {
            return 0;
        }
    }

    public function Machine_Id($CompanyCode, $LocationCode, $Department, $WorkArea)
    {
        $sql = "SELECT Machine_Code FROM Web_Machines_Mst WHERE Ccode = '$CompanyCode' AND Lcode = '$LocationCode' AND Department = '$Department' AND WorkArea ='$WorkArea'";
        $query = $this->db->query($sql);
        $Rows = $query->num_rows();
        if ($Rows > 0) {
            return $query->result();
        } else {
            return 0;
        }
    }

    public function is_frame_exist($Department, $WorkArea, $Machine_Frame_Name)
    {
        $this->db->where('Department', $Department);
        $this->db->where('WorkArea', $WorkArea);
        $this->db->where('Frame', $Machine_Frame_Name);
        $query = $this->db->get('Web_Machine_Mapping_Mst');
        return $query->num_rows() > 0;
    }

    public function insert_machine_mapping($CompanyCode, $LocationCode, $Department, $WorkArea, $Machine_Id_Array, $Machine_Group_Name, $Machine_Frame_Name, $Created_By)
    {
        foreach ($Machine_Id_Array as $machineId) {
            $data = array(
                'Ccode'             => $CompanyCode,
                'Lcode'             => $LocationCode,
                'Department'        => $Department,
                'Department_code'   => "-",
                'WorkArea'          => $WorkArea,
                'Job_Card_No'       => "-",
                'Machine_Code'      => $machineId,
                'Machine_Name'      => "-",
                'Frame_Type'        => "Multiple",
                'Input_Method'      => "Offline",
                'Frame'             => $Machine_Frame_Name,
                'Unit'              => "-",
                'Machine_Model'     => "-",
                'Machine_Mapping'   => $Machine_Group_Name,
                'Machine_Id'        => $machineId,
                'Status'            => "Active",
                'Host_IP'           => "NULL",
                'Host_Name'         => "NULL",
                'Created_By'        => $Created_By,
                'Created_Time'      => date('Y-m-d H:i:s'),
                'Updated_By'        => "-",
                'Updated_Time'      => "-"
            );

            $this->db->insert('Web_Machine_Mapping_Mst', $data);
        }

        return true;
    }


    public function Shift_Details($CompanyCode, $LocationCode, $Login_User)
    {

        $sql = "SELECT ShiftDesc, StartTime, EndTime FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc != 'GENERAL'";
        $shift_Data = $this->db->query($sql)->result();

        if ($this->db->query($sql)->num_rows() > 0) {

            return $shift_Data;
        } else {

            return 0;
        }
    }

    public function Shift_Timings($CompanyCode, $LocationCode, $Login_User, $Shift)
    {

        $sql = "SELECT ShiftDesc, StartTime, EndTime FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift' AND ShiftDesc != 'GENERAL'";
        $shift_Data = $this->db->query($sql)->result();

        if ($this->db->query($sql)->num_rows() > 0) {

            return $shift_Data;
        } else {

            return 0;
        }
    }

    public function Sub_Section_Employee_Count($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {


        $sql1 = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
        $shift_Data = $this->db->query($sql1)->row();

        if ($shift_Data) {

            $Shift_Pounch_Start = $shift_Data->StartIN;
            $Shift_Pounch_End = $shift_Data->EndIN;
            $Shift_Date_Convert = $Date;

            // print_r($Shift_Pounch_End);exit;

            $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                : $Shift_Date_Convert;

            $sql2 = "SELECT
                       Emp.SubSection_Name,
     -- Pick anyEmp, representative WorkArea
     Emp.WorkArea,
    COUNT(*) AS SubSection_Count
                FROM
                    UserDetails_Det Log
                INNER JOIN
                    Employee_Mst Emp ON Log.Lcode = Emp.LocCode AND Log.Name = Emp.DeptName
                INNER JOIN
                    LogTime_IN Time ON Time.MachineID = Emp.MachineID
                WHERE
                    Log.UserID = '$Login_User'
                    AND Time.TimeIN BETWEEN '$Shift_Date_Conversion $Shift_Pounch_Start'
                                        AND '$Shift_Date_Conversion $Shift_Pounch_End'
                    AND Emp.CatName != 'STAFF'
                    AND Time.CompCode = '$CompanyCode'
                    AND Time.LocCode = '$LocationCode'
                    AND Emp.IsActive = 'Yes'
                GROUP BY
                    Emp.SubSection_Name, Emp.WorkArea";



            $log_Data = $this->db->query($sql2)->result();

            return $log_Data;
        }
    }

    public function Employee_Home_Page($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {
        $Late_Employee_Count = 0;
        $Shift_Employee_Count = 0;

        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
            $sql_Shift = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
            $shift_Data = $this->db->query($sql_Shift)->row();

            $Login_Details = "SELECT * FROM UserDetails_Det WHERE UserID = 'pappl'";
            $Query_Details =  $this->db->query($Login_Details);
            $Details = $Query_Details->row();

            $Sub_Department = $Details->Name;


            if ($shift_Data) {
                $Shift_Pounch_Start = $shift_Data->StartIN;
                $Shift_Pounch_End = $shift_Data->EndIN;
                $Shift_Date_Convert = $Date;

                $Shift_Date_Conversion = ($shift_Data->StartIN_Days == 1 && $shift_Data->EndIN_Days == 1)
                    ? date('Y-m-d', strtotime($Shift_Date_Convert . ' +1 days'))
                    : $Shift_Date_Convert;

                $sql2 = "SELECT COUNT(DISTINCT Time.MachineID) AS Count
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

                $result = $this->db->query($sql2)->row();
                $Shift_Employee_Count = $result ? $result->Count : 0;
            }
        }

        $sql_Late = "SELECT * FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc = '$Shift'";
        $shift_Data = $this->db->query($sql_Late)->row();

        if ($shift_Data) {

            $Shift_Pounch_Start = $shift_Data->StartTime;
            // $Shift_Pounch_End = $shift_Data->StartOUT;
            $Shift_Pounch_End = $shift_Data->StartOUT;

            $From_Shift_Date_Convert = $Date;
            $To_Shift_Date_Convert = $Date;

            if ($shift_Data->StartIN_Days == 1) {
                $From_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
            }

            if ($shift_Data->EndOUT_Days == 1) {
                $To_Shift_Date_Convert = date('Y-m-d', strtotime($Date . ' +1 days'));
            }

            $sql3 = "SELECT *
                 FROM Web_Late_Extra_Mst Late
                 INNER JOIN UserDetails_Det Login ON Login.Ccode = Late.Ccode
                     AND Login.Lcode = Late.Lcode
                     AND Login.Name = Late.Sub_Department
                 WHERE Late.Date = '$Date'
                   AND Late.Shift = '$Shift'
                   AND Late.Ccode = '$CompanyCode'
                   AND Late.Lcode = '$LocationCode'
                   AND Late.Work_Status = '1'
                   AND Login.UserID = '$Login_User'";




            $query3 = $this->db->query($sql3);
            $Late_Employee_Count = $query3->num_rows();
        }





        $Sql_Total_Allocated = "SELECT *
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
        AND Work.Assign_Status = '1'";

        $Sql_Late_Total_Allocated = "SELECT *
    FROM Web_Employee_Work_Allocation_Mst AS Work
    INNER JOIN UserDetails_Det AS Login
        ON Login.Lcode = Work.Lcode
        AND Login.Name = Work.Sub_Department
    WHERE
        Login.Ccode = '$CompanyCode'
        AND Login.Lcode = '$LocationCode'
        AND Login.UserID = '$Login_User'
        AND Work.Date = '$Date'
        AND Work.Type = 'LATE'
        AND Work.Shift = '$Shift'
        AND Work.Work_Status = '1'
        AND Work.Assign_Status = '1'";

        $Total_Allocated_Result = $this->db->query($Sql_Total_Allocated)->num_rows();
        $Late_Total_Allocated_Result = $this->db->query($Sql_Late_Total_Allocated)->num_rows();



        return [
            'Late_Comers' => (int)$Late_Employee_Count,
            'Actual_Comers' => (int)$Shift_Employee_Count,
            'Total_Allocated_Count' => $Total_Allocated_Result,
            'Late_Total_Allocated_Count' => $Late_Total_Allocated_Result
        ];
    }
}
