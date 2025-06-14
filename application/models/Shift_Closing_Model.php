<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


class  Shift_Closing_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    public function Departments($CompanyCode, $LocationCode, $UserName)
    {

        $sql = "SELECT Name FROM UserDetails_Det WHERE CCode = '$CompanyCode' AND LCode = '$LocationCode' AND UserName = '$UserName' AND Name != 'HR' ORDER BY Name ASC";
        $query = $this->db->query($sql);
        $Row = $query->result();

        if ($query->num_rows() > 0) {
            return $Row;
        } else {
            return false;
        }
    }


    public function Shifts($CompanyCode, $LocationCode)
    {

        $currentTime = date('H:i:s');
        $currentDateTime = new DateTime($currentTime);

        $sql = "SELECT ShiftDesc, StartTime, EndTime FROM Shift_Mst WHERE CompCode = '$CompanyCode' AND LocCode = '$LocationCode' AND ShiftDesc != 'GENERAL' ORDER BY ShiftDesc ASC";
        $query = $this->db->query($sql);
        $shifts = $query->result();



        return $shifts;
    }



    public function Allocation_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift)
    {


        $All_Employee_Close_Check_Sql = "SELECT DISTINCT Work.EmpNo,Work.Sub_Department,Work.WorkArea,Work.Job_Card_No,Work.EmpNo,Work.FirstName FROM Web_Employee_Work_Allocation_Mst Work
                                          INNER JOIN UserDetails_Det Login ON Login.Lcode = Work.Lcode
                                         AND Login.Ccode = Work.Ccode
                                         AND Login.Name = Work.Sub_Department
                                         WHERE login.UserID = '$Login_User'
                                         AND Work.Date = '$Date'
                                         AND Work.Shift = '$Shift'
                                         AND Work.WorK_Status = '1'
                                         -- AND Assign_Status = '1'
                                         ---AND Work_Type = 'NoWork'
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

            foreach ($inputData['Employees'] as $Details) {

                $date = $Details['Date'];
                $Sub_Department = $Details['Department'];
                $shift = $Details['Shift'];
                $WorkArea = $Details['Work_Area'];
                $jobCardNo = $Details['JobCardNo'];
                $Employee_Id = $Details['EmployeeId'];
                $OTStatus = $Details['OTConfirm'];
                $Supervisor_Name = $Details['Supervisor_Name'];


                // echo '<pre>';
                // print_r($Employee_Id . '==> '.$OTStatus );

                if ($OTStatus == 1) {

                    $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst WHERE Date = '$date' AND Shift = '$shift' AND EmpNo = '$Employee_Id' AND Sub_Department = '$Sub_Department' AND WorkArea = '$WorkArea' AND Job_Card_No = '$jobCardNo'";
                    $query = $this->db->query($sql);
                    $Employee_Work_Data = $query->result();

                    foreach ($Employee_Work_Data as $item) {

                        $currentShift = $item->Shift;
                        $NextShift = $this->NextShift($currentShift);

                        $this->db->where([
                            'Date' => $date,
                            'Shift' => $shift,
                            'Sub_Department' => $Sub_Department,
                            'WorkArea' => $WorkArea,
                            'Assign_Status' => '1',
                            'Work_Status' => '1',
                            'EmpNo' => $Employee_Id,

                        ]);

                        // $sql = "SELECT * FROM Web_Employee_Work_Allocation_Mst Where Date = '$date' AND Shift = '$shift' AND Assign_Status = '1' AND Work_Status = '1' AND EmpNo = '$Employee_Id' ";
                        // print_r($sql);exit;

                        $this->db->update('Web_Employee_Work_Allocation_Mst', [
                            'Closing_Status' => '1',
                            'Status_Updated' => 'Closed',
                            'Supervisor_Name' => $Supervisor_Name,
                            'Updated_By' => $Session['UserName'],
                            'Updated_Time' => date('Y-m-d H:i:s'),
                        ]);

                        $this->db->where([
                            'Date' => $date,
                            'Shift' => $NextShift,
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
                                'Previous_Shift' => $shift,
                                'OT_Confirmation' => 'Yes',
                                'Status_Updated' => $item->Work_Type,
                                'Date' => $item->Date,
                                'Job_Card_No' => $item->Job_Card_No,
                                'Work_Type' => $item->Work_Type,
                                'Department' => $item->Department,
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
                                'Working_Type' => 'OT',
                                'Supervisor_Name' => $Supervisor_Name,
                                'Remarks' => '',
                                'Type' => $item->Type,
                                'Wages' => $item->Wages,
                                'IsWork' => '0',
                                'Created_By' => $item->Created_By,
                                'Created_Time' => $item->Created_Time,
                                'Updated_By' => '-',
                                'Updated_Time' => '-',
                            ];

                            // print_r($workAllocation);exit;

                            // Insert the new work allocation record
                            $this->db->insert('Web_Employee_Work_Allocation_Mst', $workAllocation);
                        }
                    }
                } else {

                    $this->db->where([
                        'Sub_Department' => $Sub_Department,
                        // 'WorkArea' => $WorkArea,
                        'Date' => $date,
                        'Shift' => $shift,
                        'Work_Status' => '1',
                    ]);

                    $this->db->update('Web_Employee_Work_Allocation_Mst', [
                        'Status_Updated' => 'Closed',
                        'Supervisor_Name' => $Supervisor_Name,
                        'Closing_Status' => '1',
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



    public function Assigned_Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Login_User)
    {

        $sql = "SELECT  DISTINCT EmpNo, EmpNo, FirstName,Department,Sub_Department,WorkArea FROM Web_Employee_Work_Allocation_Mst Work inner join UserDetails_Det Login ON Login.Lcode = Work.Lcode AND Login.Name =  Work.Sub_Department
            where login.UserID = '$Login_User'
            AND Work.Date = '$Date'
            AND Work.Shift = '$Shift'
            AND Work.Ccode = '$CompanyCode'
            AND Login.Lcode = '$LocationCode'
            AND Work.WorK_Status = '1'
            AND Work.Assign_Status = '1'
            AND Work.Closing_Status = '0'";
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {

            return $query->result();

        } else {

            return 0;
        }
    }

    public function Partial_Closing($CompanyCode, $LocationCode, $Date, $Shift, $Employee_Id, $SupervisorName, $Reason)
    {

        $Current_Time = date('Y-m-d H:i:s');

        $Sql = "UPDATE Web_Employee_Work_Allocation_Mst
                SET Description = '$Reason',
                    Closing_Status = '1',
                    Updated_By = '$SupervisorName',
                    Updated_Time = '$Current_Time',
                    Status_Updated = 'Closed'
                WHERE Lcode = '$LocationCode'
                    AND Ccode = '$CompanyCode'
                    AND EmpNo = '$Employee_Id'
                    AND Work_Status = '1'
                    AND Closing_Status = '0'
                    AND Assign_Status = '1'";

        $query = $this->db->query($Sql);

        if ($this->db->affected_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }


    public function Seperated_Assigned_Employee_List($CompanyCode, $LocationCode, $Date, $Shift, $Login_User, $Sub_Section){

        $sql = "SELECT  DISTINCT EmpNo, EmpNo, FirstName,Department,Sub_Department,WorkArea,Job_Card_No FROM Web_Employee_Work_Allocation_Mst Work inner join UserDetails_Det Login ON Login.Lcode = Work.Lcode AND Login.Name =  Work.Sub_Department
        where login.UserID = '$Login_User'
        AND Work.Date = '$Date'
        AND Work.Shift = '$Shift'
        AND Work.Ccode = '$CompanyCode'
        AND Login.Lcode = '$LocationCode'
        AND Work.WorK_Status = '1'
        AND Work.Assign_Status = '1'
        AND Work.Sub_Section = '$Sub_Section'
        AND Work.Closing_Status = '0'";
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {

            return $query->result();
        } else {

            return 0;
        }
    }
}
