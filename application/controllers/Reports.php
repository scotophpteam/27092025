<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


require 'vendor/autoload.php';
// use PHPUnit\Util\Printer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Reports extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->model('Master_Model');
        $this->load->model('Work_Model');
        $this->load->model('Reports_Model');
        $this->load->model('Employee_Model');
        $this->load->model('Grade_Model');
        $this->load->model('OT_Model');
        $this->load->model('Incentive_Model');
    }

    public function Work_Allocation()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data['Favicon'] = 'Precot | Work Allocation Report';

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');


                $this->data['Assigned_List'] = $Assigned_List = $this->Reports_Model->Assigned_List($CompanyCode, $LocationCode, $Login_User, $Shift, $Date);
                echo json_encode($this->data);
                exit;
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Reports/Work_Allocation_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function Work_Allocation_Sub_Section_Wise()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


            $CompanyCode = $Session['Ccode'];
            $LocationCode = $Session['Lcode'];
            $Login_User = $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');


            $this->data['Work_Allocation_Sub_Section_Wise'] = $Assigned_ListWork_Allocation_Sub_Section_Wise = $this->Reports_Model->Work_Allocation_Sub_Section_Wise($CompanyCode, $LocationCode, $Login_User, $Shift, $Date, $Sub_Section);

            if ($Assigned_ListWork_Allocation_Sub_Section_Wise == 0) {

                $this->data['Work_Allocation_Sub_Section_Wise'] = $Message = [
                    'Status' => 'Error',
                    'Message' => 'Work Allocation Details Not Found Please Check.'
                ];
            } else {

                echo json_encode($this->data);
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }






    public function Work_Allocation_Report_Download()
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['Assigned_List'] = $Assigned_List = $this->Reports_Model->Assigned_List($CompanyCode, $LocationCode, $Login_User, $Shift, $Date);

                if ($Assigned_List == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Employee Work Allocation Report';
                    $sheet->mergeCells('A1:K1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('K2', 'SHIFT: ' . $Shift)
                        ->setCellValue('K3', 'DATE: ' . $Date);

                    $sheet->getStyle('K2:K3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Description')
                        ->setCellValue('N6', 'Machine_Name')
                        ->setCellValue('O6', 'Machine_Model')
                        ->setCellValue('P6', 'Machine_Id')
                        ->setCellValue('Q6', 'Frame')
                        ->setCellValue('R6', 'FrameType')
                        ->setCellValue('S6', 'Created_By')
                        ->setCellValue('T6', 'Created_Time');

                    $sheet->getStyle('A6:T6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Assigned_List as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Description)
                            ->setCellValue('N' . $rowNumber, $data->Machine_Name)
                            ->setCellValue('O' . $rowNumber, $data->Machine_Model)
                            ->setCellValue('P' . $rowNumber, $data->Machine_Id)
                            ->setCellValue('Q' . $rowNumber, $data->Frame)
                            ->setCellValue('R' . $rowNumber, $data->FrameType)
                            ->setCellValue('S' . $rowNumber, $data->Created_By)
                            ->setCellValue('T' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    foreach (range('A', 'T') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/Employee_Work_Allocation_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }



    public function Work_Allocation_Report_Download_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Sub_Section = $this->input->post('Sub_Section');

                $this->data['Work_Allocation_Sub_Section_Wise'] = $Assigned_ListWork_Allocation_Sub_Section_Wise = $this->Reports_Model->Work_Allocation_Sub_Section_Wise($CompanyCode, $LocationCode, $Login_User, $Shift, $Date, $Sub_Section);

                if ($Assigned_ListWork_Allocation_Sub_Section_Wise == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Employee Work Allocation Report';
                    $sheet->mergeCells('A1:K1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('K2', 'SHIFT: ' . $Shift)
                        ->setCellValue('K3', 'DATE: ' . $Date);

                    $sheet->getStyle('K2:K3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Description')
                        ->setCellValue('N6', 'Machine_Name')
                        ->setCellValue('O6', 'Machine_Model')
                        ->setCellValue('P6', 'Machine_Id')
                        ->setCellValue('Q6', 'Frame')
                        ->setCellValue('R6', 'FrameType')
                        ->setCellValue('S6', 'Created_By')
                        ->setCellValue('T6', 'Created_Time');

                    $sheet->getStyle('A6:T6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Assigned_ListWork_Allocation_Sub_Section_Wise as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Description)
                            ->setCellValue('N' . $rowNumber, $data->Machine_Name)
                            ->setCellValue('O' . $rowNumber, $data->Machine_Model)
                            ->setCellValue('P' . $rowNumber, $data->Machine_Id)
                            ->setCellValue('Q' . $rowNumber, $data->Frame)
                            ->setCellValue('R' . $rowNumber, $data->FrameType)
                            ->setCellValue('S' . $rowNumber, $data->Created_By)
                            ->setCellValue('T' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    foreach (range('A', 'T') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/Employee_Work_Allocation_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }





    public function No_Work_Employee()
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $this->data = array(
                'Ccode' =>  $Session['Ccode'],
                'Lcode' =>  $Session['Lcode'],
                'UserName' =>  $Session['UserName'],
            );

            $this->data['Date'] = null;
            $this->data['Department'] = null;
            $this->data['Shift'] = null;
            $this->data['getStudent_List'] = null;

            $this->data['Favicon'] = 'Precot | No Work Report';


            if ($_POST) {

                $this->data['Date'] = $Date = $this->input->post('Date');
                $this->data['Department'] = $Department = $this->input->post('Department');
                $this->data['Shift'] = $Shift = $this->input->post('Shift');
                $this->data['No_Work_Employee'] = $No_Work_Employee = $this->Reports_Model->No_Work_Employee($Date, $Department, $Shift);
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Reports/No_Work_Employee', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    public function NoWork_Employee_List_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode = $Session['Lcode'];
            $CompanyCode = $Session['Ccode'];
            $Login_User = $Session['UserName'];
            $Date =  $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');

            $this->data['NoWork_Employee_List_Sub_Section'] = $NoWork_Employee_List_Sub_Section = $this->Reports_Model->NoWork_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

            if ($NoWork_Employee_List_Sub_Section == 0) {
                $this->data['NoWork_Employee_List_Sub_Section'] = $Message = array(
                    'Status' => 'error',
                    'Message' => 'Employee No Work Report Not Found Server!!.'
                );
                echo json_encode($this->data);
            } else {

                echo json_encode($this->data);
            }
        }
    }


    public function No_Work_Report_Download_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {
                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Sub_Section = $this->input->post('Sub_Section');

                $this->data['NoWork_Employee_List_Sub_Section'] = $NoWork_Employee_List_Sub_Section = $this->Reports_Model->NoWork_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

                if ($NoWork_Employee_List_Sub_Section == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'No Work Employee Report';
                    $sheet->mergeCells('A1:M1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('M2', 'SHIFT: ' . $Shift)
                        ->setCellValue('M3', 'DATE: ' . $Date);

                    $sheet->getStyle('M2:M3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Created_By')
                        ->setCellValue('N6', 'Created_Time');

                    $sheet->getStyle('A6:N6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($NoWork_Employee_List_Sub_Section as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Created_By)
                            ->setCellValue('N' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    foreach (range('A', 'N') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/No_Work_Employee_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }











    public function Late_Employee_List_Download()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['Late_Employee_List'] = $Late_Employee_List = $this->Reports_Model->Late_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($Late_Employee_List == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    // Ensure required classes are imported
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Late And Extra Work Employee Report';
                    $sheet->mergeCells('A1:N1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('N2', 'SHIFT: ' . $Shift)
                        ->setCellValue('N3', 'DATE: ' . $Date);

                    // Apply bold to SHIFT and DATE rows
                    $sheet->getStyle('N2:N3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    // Fix headers
                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Description')
                        ->setCellValue('N6', 'Machine_Name')
                        ->setCellValue('O6', 'Machine_Model')
                        ->setCellValue('P6', 'Machine_Id')
                        ->setCellValue('Q6', 'Frame')
                        ->setCellValue('R6', 'FrameType')
                        ->setCellValue('S6', 'Created_By')
                        ->setCellValue('T6', 'Created_Time');

                    $sheet->getStyle('A6:T6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Late_Employee_List as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)  // Corrected this line
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Description)
                            ->setCellValue('N' . $rowNumber, $data->Machine_Name)
                            ->setCellValue('O' . $rowNumber, $data->Machine_Model)
                            ->setCellValue('P' . $rowNumber, $data->Machine_Id)
                            ->setCellValue('Q' . $rowNumber, $data->Frame)
                            ->setCellValue('R' . $rowNumber, $data->FrameType)
                            ->setCellValue('S' . $rowNumber, $data->Created_By)
                            ->setCellValue('T' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    // Set the column widths to auto size based on content
                    foreach (range('A', 'T') as $col) {  // Updated column range to 'T'
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/Late_Employee_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }



    public function Shift_Closing_Reports()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode = $Session['Ccode'];
            $LocationCode = $Session['Lcode'];
            $Login_User = $Session['UserName'];

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');


            $this->data['Favicon'] = 'Precot | Work Shift Closing Report';

            if ($_POST) {

                $Shift = $this->input->post('Shift');
                $Date = $this->input->post('Date');
                // $Shift = 'SHIFT2';
                $Department = $this->input->post('Department');
                $Work_Area = $this->input->post('Work_Area');
                $JobCard = $this->input->post('JobCardNo');

                $this->data['Assigned_List'] = $Assigned_List = $this->Reports_Model->Assigned_List($Shift, $Date, $Department, $Work_Area, $JobCard);
                echo json_encode($this->data);
                exit;
            }

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Reports/Shift_Closing_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    public function Shift_Closing_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['Shift_Closing_Report_Download'] = $Shift_Closing_Report_Download = $this->Reports_Model->Shift_Closing_Report_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($Shift_Closing_Report_Download == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Employee Shift Closing Report Not Found Server!!.'
                    );
                    echo json_encode($Reponse);
                } else {

                    echo json_encode($this->data);
                }
            }
        } else {
            redirect(base_url());
        }
    }





    public function Shift_Closing_List_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Sub_Section = $this->input->post('Sub_Section');

                $this->data['Shift_Closing_List_Sub_Section'] = $Shift_Closing_List_Sub_Section = $this->Reports_Model->Shift_Closing_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

                if ($Shift_Closing_List_Sub_Section == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Employee Shift Closing Report Not Found Server!!.'
                    );
                    echo json_encode($Reponse);
                } else {

                    echo json_encode($this->data);
                }
            }
        } else {
            redirect(base_url());
        }
    }




    public function Shift_Closing_Report_Download_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Sub_Section = $this->input->post('Sub_Section');

                $this->data['Shift_Closing_List_Sub_Section'] = $Shift_Closing_List_Sub_Section = $this->Reports_Model->Shift_Closing_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

                if ($Shift_Closing_List_Sub_Section == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Employee Shift Closing Report not found!'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Employee Shift Closing Report';
                    $sheet->mergeCells('A1:K1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('K2', 'SHIFT: ' . $Shift)
                        ->setCellValue('K3', 'DATE: ' . $Date);

                    // Apply bold to SHIFT and DATE rows
                    $sheet->getStyle('K2:K3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    // Header columns
                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')  // Corrected to D6 for Sub Department
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'Closing_Status');  // Corrected header for Closing_Status

                    $sheet->getStyle('A6:K6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Shift_Closing_List_Sub_Section as $data) {

                        // Data population
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->Closing_Status);  // Corrected column reference for Closing_Status


                        // Set text based on Closing_Status
                        if ($data->Closing_Status == 0) {
                            // Apply orange background for NOT CLOSE
                            $sheet->getStyle('K' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID);
                            $sheet->getStyle('K' . $rowNumber)->getFill()->getStartColor()->setRGB('FFA500'); // Orange
                            $sheet->setCellValue('K' . $rowNumber, 'NOT CLOSE'); // Set text as "NOT CLOSE"
                        } elseif ($data->Closing_Status == 1) {
                            // Apply green background for CLOSE
                            $sheet->getStyle('K' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID);
                            $sheet->getStyle('K' . $rowNumber)->getFill()->getStartColor()->setRGB('008000'); // Green
                            $sheet->setCellValue('K' . $rowNumber, 'CLOSE'); // Set text as "CLOSE"
                        }

                        $rowNumber++;
                    }

                    // Set the column widths to auto size based on content (limited to used columns)
                    foreach (range('A', 'K') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/shift/Shift_Closing_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports/shift')) {
                        mkdir('assets/reports/shift', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }


    public function Shift_Closing_Report_Download()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['Shift_Closing_Report_Download'] = $Shift_Closing_Report_Download = $this->Reports_Model->Shift_Closing_Report_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($Shift_Closing_Report_Download == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Employee Shift Closing Report not found!'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Employee Shift Closing Report';
                    $sheet->mergeCells('A1:K1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('K2', 'SHIFT: ' . $Shift)
                        ->setCellValue('K3', 'DATE: ' . $Date);

                    // Apply bold to SHIFT and DATE rows
                    $sheet->getStyle('K2:K3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    // Header columns
                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')  // Corrected to D6 for Sub Department
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'Closing_Status');  // Corrected header for Closing_Status

                    $sheet->getStyle('A6:K6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Shift_Closing_Report_Download as $data) {

                        // Data population
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->Closing_Status);  // Corrected column reference for Closing_Status


                        // Set text based on Closing_Status
                        if ($data->Closing_Status == 0) {
                            // Apply orange background for NOT CLOSE
                            $sheet->getStyle('K' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID);
                            $sheet->getStyle('K' . $rowNumber)->getFill()->getStartColor()->setRGB('FFA500'); // Orange
                            $sheet->setCellValue('K' . $rowNumber, 'NOT CLOSE'); // Set text as "NOT CLOSE"
                        } elseif ($data->Closing_Status == 1) {
                            // Apply green background for CLOSE
                            $sheet->getStyle('K' . $rowNumber)->getFill()->setFillType(Fill::FILL_SOLID);
                            $sheet->getStyle('K' . $rowNumber)->getFill()->getStartColor()->setRGB('008000'); // Green
                            $sheet->setCellValue('K' . $rowNumber, 'CLOSE'); // Set text as "CLOSE"
                        }

                        $rowNumber++;
                    }

                    // Set the column widths to auto size based on content (limited to used columns)
                    foreach (range('A', 'K') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/shift/Shift_Closing_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports/shift')) {
                        mkdir('assets/reports/shift', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }



    public function Late_Report()
    {


        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $CompanyCode =  $Session['Ccode'];
            $LocationCode =  $Session['Lcode'];
            $Login_User =  $Session['UserName'];

            $this->data['Favicon'] = 'Precot | Late Employee Report';

            $this->load->view('Frontend/Header', $this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Reports/Late_Employee_Report', $this->data);
            $this->load->view('Frontend/Footer');
        } else {
            redirect(base_url());
        }
    }


    public function Late_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode = $Session['Lcode'];
            $CompanyCode = $Session['Ccode'];
            $Login_User = $Session['UserName'];
            $Date =  $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Late_Employee_List'] = $Late_Employee_List = $this->Reports_Model->Late_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if ($Late_Employee_List == FALSE) {

                $Reponse = array(
                    'status' => 'error',
                    'mesaage' => 'Work Allocation Details Not Found!!.',
                );

                echo json_encode($Reponse);
            } else {

                echo json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }


    public function Late_Employee_List_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode = $Session['Lcode'];
            $CompanyCode = $Session['Ccode'];
            $Login_User = $Session['UserName'];
            $Date =  $this->input->post('Date');
            $Shift = $this->input->post('Shift');
            $Sub_Section = $this->input->post('Sub_Section');

            $this->data['Late_Employee_List_Sub_Section'] = $Late_Employee_List_Sub_Section = $this->Reports_Model->Late_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

            if ($Late_Employee_List_Sub_Section == 0) {
                $this->data['NoWork_Employee_List_Sub_Section'] = $Message = array(
                    'Status' => 'error',
                    'Message' => 'Employee Late And Extra Report Not Found Server!!.'
                );
                echo json_encode($this->data);
            } else {

                echo json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }



    public function Late_Employee_List_Download_Sub_Section()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Sub_Section = $this->input->post('Sub_Section');

                $this->data['Late_Employee_List_Sub_Section'] = $Late_Employee_List_Sub_Section = $this->Reports_Model->Late_Employee_List_Sub_Section($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Sub_Section);

                if ($Late_Employee_List_Sub_Section == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    // Ensure required classes are imported
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Late And Extra Work Employee Report';
                    $sheet->mergeCells('A1:N1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('N2', 'SHIFT: ' . $Shift)
                        ->setCellValue('N3', 'DATE: ' . $Date);

                    // Apply bold to SHIFT and DATE rows
                    $sheet->getStyle('N2:N3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    // Fix headers
                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Description')
                        ->setCellValue('N6', 'Machine_Name')
                        ->setCellValue('O6', 'Machine_Model')
                        ->setCellValue('P6', 'Machine_Id')
                        ->setCellValue('Q6', 'Frame')
                        ->setCellValue('R6', 'FrameType')
                        ->setCellValue('S6', 'Created_By')
                        ->setCellValue('T6', 'Created_Time');

                    $sheet->getStyle('A6:T6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Late_Employee_List_Sub_Section as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)  // Corrected this line
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Description)
                            ->setCellValue('N' . $rowNumber, $data->Machine_Name)
                            ->setCellValue('O' . $rowNumber, $data->Machine_Model)
                            ->setCellValue('P' . $rowNumber, $data->Machine_Id)
                            ->setCellValue('Q' . $rowNumber, $data->Frame)
                            ->setCellValue('R' . $rowNumber, $data->FrameType)
                            ->setCellValue('S' . $rowNumber, $data->Created_By)
                            ->setCellValue('T' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    // Set the column widths to auto size based on content
                    foreach (range('A', 'T') as $col) {  // Updated column range to 'T'
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/Late_Employee_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }






    public function NoWork_Employee_List()
    {

        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

            $LocationCode = $Session['Lcode'];
            $CompanyCode = $Session['Ccode'];
            $Login_User = $Session['UserName'];
            $Date =  $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['NoWork_Employee_List'] = $NoWork_Employee_List = $this->Reports_Model->NoWork_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if ($NoWork_Employee_List == FALSE) {

                $Reponse = array(
                    'status' => 'error',
                    'mesaage' => 'Work Allocation Details Not Found!!.',
                );

                echo json_encode($Reponse);
            } else {

                echo json_encode($this->data);
            }
        } else {

            redirect(base_url(), 'refresh');
        }
    }



    public function No_Work_Report_Download()
    {
        $Session = $this->session->userdata('sess_array');
        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($_POST) {
                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');

                $this->data['No_Work_Report_Download'] = $No_Work_Report_Download = $this->Reports_Model->NoWork_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if ($No_Work_Report_Download == 0) {
                    $Reponse = array(
                        'status' => 'error',
                        'message' => 'Work Allocation Details Not Found!!.'
                    );
                    echo json_encode($Reponse);
                } else {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'No Work Employee Report';
                    $sheet->mergeCells('A1:M1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('M2', 'SHIFT: ' . $Shift)
                        ->setCellValue('M3', 'DATE: ' . $Date);

                    $sheet->getStyle('M2:M3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    $sheet->setCellValue('A6', 'Ccode')
                        ->setCellValue('B6', 'Lcode')
                        ->setCellValue('C6', 'Department')
                        ->setCellValue('D6', 'Sub Department')
                        ->setCellValue('E6', 'WorkArea')
                        ->setCellValue('F6', 'Job_Card_No')
                        ->setCellValue('G6', 'Date')
                        ->setCellValue('H6', 'Shift')
                        ->setCellValue('I6', 'EmpNo')
                        ->setCellValue('J6', 'FirstName')
                        ->setCellValue('K6', 'ExistingCode')
                        ->setCellValue('L6', 'Work_Type')
                        ->setCellValue('M6', 'Created_By')
                        ->setCellValue('N6', 'Created_Time');

                    $sheet->getStyle('A6:N6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($No_Work_Report_Download as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                            ->setCellValue('B' . $rowNumber, $data->Lcode)
                            ->setCellValue('C' . $rowNumber, $data->Department)
                            ->setCellValue('D' . $rowNumber, $data->Sub_Department)
                            ->setCellValue('E' . $rowNumber, $data->WorkArea)
                            ->setCellValue('F' . $rowNumber, $data->Job_Card_No)
                            ->setCellValue('G' . $rowNumber, $data->Date)
                            ->setCellValue('H' . $rowNumber, $data->Shift)
                            ->setCellValue('I' . $rowNumber, $data->EmpNo)
                            ->setCellValue('J' . $rowNumber, $data->FirstName)
                            ->setCellValue('K' . $rowNumber, $data->ExistingCode)
                            ->setCellValue('L' . $rowNumber, $data->Work_Type)
                            ->setCellValue('M' . $rowNumber, $data->Created_By)
                            ->setCellValue('N' . $rowNumber, $data->Created_Time);

                        $rowNumber++;
                    }

                    foreach (range('A', 'N') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/No_Work_Employee_Report_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);

                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }


    public function Employee_Punching_List_Download()
    {
        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

            if ($this->input->post()) {

                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
                $Punching_Type = $this->input->post('Punching_Type');

                $this->data['Employee_Punching_List'] = $Employee_Punching_List = $this->Employee_Model->Employee_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift, $Punching_Type);

                if ($Employee_Punching_List == 0) {
                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];
                    echo json_encode($Response);
                    exit;
                } else {

                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $mainHeading = 'Employee LogIn Details';
                    $sheet->mergeCells('A1:G1');
                    $sheet->setCellValue('A1', $mainHeading);
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getRowDimension('1')->setRowHeight(30);

                    $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                        ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                        ->setCellValue('G2', 'SHIFT: ' . $Shift)
                        ->setCellValue('G3', 'DATE: ' . $Date);

                    $sheet->getStyle('G2:G3')->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
                    $sheet->getStyle('A2:A3')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                    ]);

                    $sheet->setCellValue('A6', 'Sub Department')
                        ->setCellValue('B6', 'Wages')
                        ->setCellValue('C6', 'Sub Division')
                        ->setCellValue('D6', 'Position')
                        ->setCellValue('E6', 'Employee ID')
                        ->setCellValue('F6', 'Employee Name')
                        ->setCellValue('G6', 'Punching Type');

                    $sheet->getStyle('A6:G6')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $rowNumber = 7;
                    foreach ($Employee_Punching_List as $data) {
                        $sheet->setCellValue('A' . $rowNumber, $data->DeptName)
                            ->setCellValue('B' . $rowNumber, $data->Wages)
                            ->setCellValue('C' . $rowNumber, $data->SubSection_Name)
                            ->setCellValue('D' . $rowNumber, $data->WorkArea)
                            ->setCellValue('E' . $rowNumber, $data->MachineID)
                            ->setCellValue('F' . $rowNumber, $data->FirstName)
                            ->setCellValue('G' . $rowNumber, $data->Get_Type);
                        $rowNumber++;
                    }



                    foreach (range('A', 'N') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $currentDate = date('Y-m-d');
                    $file_path = 'assets/reports/Employee_LogIn_Details_' . $currentDate . '.xlsx';

                    if (!file_exists('assets/reports')) {
                        mkdir('assets/reports', 0777, true);
                    }

                    $writer->save($file_path);

                    echo json_encode(['file_url' => base_url($file_path)]);
                    exit;
                }
            }
        } else {
            redirect(base_url());
        }
    }


public function Employee_Punching_List_Download_Login()
{
    $Session = $this->session->userdata('sess_array');

    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

        if ($this->input->post()) {

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $Get_Punching_List = $this->Employee_Model->Get_Punching_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if (empty($Get_Punching_List)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Shift Not Starting Employee Details Not Found..'
                ]);
                exit;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $mainHeading = 'Employee Punching LogIn Details';
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', $mainHeading);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension('1')->setRowHeight(30);

            $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                ->setCellValue('I2', 'SHIFT: ' . $Shift)
                ->setCellValue('I3', 'DATE: ' . $Date);

            $sheet->getStyle('I2:I3')->applyFromArray([
                'font' => ['bold' => true],
            ]);
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
            ]);

            $sheet->setCellValue('A6', 'Sub Department')
                ->setCellValue('B6', 'Wages')
                ->setCellValue('C6', 'Sub Division')
                ->setCellValue('D6', 'Position')
                ->setCellValue('E6', 'Employee ID')
                ->setCellValue('F6', 'Employee Name')
                ->setCellValue('G6', 'Day In')
                ->setCellValue('H6', 'Break Out')
                ->setCellValue('I6', 'Break In')
                ->setCellValue('J6', 'Day Out');

            $sheet->getStyle('A6:J6')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $rowNumber = 7;
            foreach ($Get_Punching_List as $data) {
                $sheet->setCellValue('A' . $rowNumber, $data->Sub_Department)
                    ->setCellValue('B' . $rowNumber, $data->Category)
                    ->setCellValue('C' . $rowNumber, $data->SubSection_Name)
                    ->setCellValue('D' . $rowNumber, $data->WorkArea)
                    ->setCellValue('E' . $rowNumber, $data->MachineID)
                    ->setCellValue('F' . $rowNumber, $data->EmpName)
                    ->setCellValue('G' . $rowNumber, $data->Day_In)
                    ->setCellValue('H' . $rowNumber, $data->Break_Out)
                    ->setCellValue('I' . $rowNumber, $data->Break_IN)
                    ->setCellValue('J' . $rowNumber, $data->Day_Out);
                $rowNumber++;
            }

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $currentDate = date('Y-m-d');
            $directory = 'assets/reports';

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $file_path = $directory . '/Get_Punching_List_' . $currentDate . '.xlsx';
            $writer->save($file_path);

            echo json_encode(['file_url' => base_url($file_path)]);
            exit;
        }
    } else {
        redirect(base_url(), 'refresh');
    }
}



public function Employee_Punching_List_Download_Login_Det()
{
    $Session = $this->session->userdata('sess_array');

    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

        if ($this->input->post()) {

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $Get_Punching_List = $this->Employee_Model->Employee_Punching_List_Download_Login_Det($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

            if (empty($Get_Punching_List)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Shift Not Starting Employee Details Not Found..'
                ]);
                exit;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $mainHeading = 'Employee Punching LogIn Details';
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', $mainHeading);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension('1')->setRowHeight(30);

            $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                ->setCellValue('I2', 'SHIFT: ' . $Shift)
                ->setCellValue('I3', 'DATE: ' . $Date);

            $sheet->getStyle('I2:I3')->applyFromArray([
                'font' => ['bold' => true],
            ]);
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
            ]);

            $sheet->setCellValue('A6', 'Sub Department')
                ->setCellValue('B6', 'Wages')
                ->setCellValue('C6', 'Sub Division')
                ->setCellValue('D6', 'Position')
                ->setCellValue('E6', 'Employee ID')
                ->setCellValue('F6', 'Employee Name')
                ->setCellValue('G6', 'Day In')
                ->setCellValue('H6', 'Break Out')
                ->setCellValue('I6', 'Break In')
                ->setCellValue('J6', 'Day Out');

            $sheet->getStyle('A6:J6')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $rowNumber = 7;
            foreach ($Get_Punching_List as $data) {
                $sheet->setCellValue('A' . $rowNumber, $data->Sub_Department)
                    ->setCellValue('B' . $rowNumber, $data->Category)
                    ->setCellValue('C' . $rowNumber, $data->SubSection_Name)
                    ->setCellValue('D' . $rowNumber, $data->WorkArea)
                    ->setCellValue('E' . $rowNumber, $data->MachineID)
                    ->setCellValue('F' . $rowNumber, $data->EmpName)
                    ->setCellValue('G' . $rowNumber, $data->Day_In)
                    ->setCellValue('H' . $rowNumber, $data->Break_Out)
                    ->setCellValue('I' . $rowNumber, $data->Break_IN)
                    ->setCellValue('J' . $rowNumber, $data->Day_Out);
                $rowNumber++;
            }

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $currentDate = date('Y-m-d');
            $directory = 'assets/reports';

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $file_path = $directory . '/Get_Punching_List_' . $currentDate . '.xlsx';
            $writer->save($file_path);

            echo json_encode(['file_url' => base_url($file_path)]);
            exit;
        }
    } else {
        redirect(base_url(), 'refresh');
    }
}


    public function Download_Attendance_Grade()
    {
        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
            if ($this->input->post()) {
                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User = $Session['UserName'];

                $Department = $this->input->post('Sub_Department');

                $this->data['Attendance_List'] = $Employee_Punching_List = $this->Grade_Model->Attendance_List($CompanyCode, $LocationCode, $Department);

                if (empty($Employee_Punching_List)) {
                    $Response = [
                        'Status' => 'Error',
                        'Message' => 'Employee Details Not Found.'
                    ];
                    echo json_encode($Response);
                    return;
                }

                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $mainHeading = 'Employee Attendance Grade List';
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', $mainHeading);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);

                $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                    ->setCellValue('A3', 'LOCATION: ' . $LocationCode);

                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                ]);

                // Table headers
                $sheet->setCellValue('A6', 'Date Of Join')
                    ->setCellValue('B6', 'Employee ID')
                    ->setCellValue('C6', 'Employee Name')
                    ->setCellValue('D6', 'Working Months')
                    ->setCellValue('E6', 'Status')
                    ->setCellValue('F6', 'Attendance Percentage')
                    ->setCellValue('G6', 'Attendance Grade')
                    ->setCellValue('H6', 'Last Grade')
                    ->setCellValue('I6', 'Month Grade');

                $sheet->getStyle('A6:I6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Fill data
                $rowNumber = 7;
                foreach ($Employee_Punching_List as $data) {
                    $sheet->setCellValue('A' . $rowNumber, $data['doj'])
                        ->setCellValue('B' . $rowNumber, $data['ExistingCode'])
                        ->setCellValue('C' . $rowNumber, $data['FirstName'])
                        ->setCellValue('D' . $rowNumber, $data['WorkingMonths'])
                        ->setCellValue('E' . $rowNumber, $data['Status'])
                        ->setCellValue('F' . $rowNumber, $data['Average_Percentage'])
                        ->setCellValue('G' . $rowNumber, $data['Grade'])
                        ->setCellValue('H' . $rowNumber, $data['LastGrade'])
                        ->setCellValue('I' . $rowNumber, $data['GradeChangeDate']);
                    $rowNumber++;
                }

                // Auto-size columns
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $currentDate = date('Y-m-d_H-i-s');
                $fileName = 'Employee_Attendance_Grade_' . $currentDate . '.xlsx';
                $filePath = 'assets/reports/' . $fileName;

                if (!file_exists('assets/reports')) {
                    mkdir('assets/reports', 0777, true);
                }

                $writer->save($filePath);

                echo json_encode(['Status' => 'Success', 'file_url' => base_url($filePath)]);
                return;
            }
        } else {
            redirect(base_url());
        }
    }


  public function Extra_Hours_Employee_Download()
{
    $Session = $this->session->userdata('sess_array');

    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
        if ($this->input->post()) {
            $CompanyCode = $Session['Ccode'];
            $LocationCode = $Session['Lcode'];
            $Login_User = $Session['UserName'];
            $Date = $this->input->post('Date');
            $Type = $this->input->post('Type');

            $Extra_Hours_Employee_Download = $this->Reports_Model->Extra_Hours_Employee_Download($CompanyCode, $LocationCode, $Login_User, $Date, $Type);

            if (empty($Extra_Hours_Employee_Download)) {
                echo json_encode([
                    'Status' => 'Error',
                    'Message' => 'Employee Extra Hours Work Details Not Found.'
                ]);
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $mainHeading = 'Employee Extra Hours Work Details';
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', $mainHeading);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension('1')->setRowHeight(30);

            $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                  ->setCellValue('G2', 'LOCATION: ' . $LocationCode);

            $sheet->getStyle('A2:G3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
            ]);

            // Table headers
            $sheet->setCellValue('A3', 'Date')
                  ->setCellValue('B3', 'Employee ID')
                  ->setCellValue('C3', 'Employee Name')
                  ->setCellValue('D3', 'IN')
                  ->setCellValue('E3', 'OUT')
                  ->setCellValue('F3', 'E-Master Close')
                  ->setCellValue('G3', 'Working Hours');

            $sheet->getStyle('A3:G3')->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            ]);

            // Fill data
            $rowNumber = 4;
            foreach ($Extra_Hours_Employee_Download as $data) {
                $sheet->setCellValue('A' . $rowNumber, $Date)
                      ->setCellValue('B' . $rowNumber, $data['Employee_ID'])
                      ->setCellValue('C' . $rowNumber, $data['Employee_Name'])
                      ->setCellValue('D' . $rowNumber, $data['IN_Time'])
                      ->setCellValue('E' . $rowNumber, $data['OUT_Time'])
                      ->setCellValue('F' . $rowNumber, $data['Updated_Time'])
                      ->setCellValue('G' . $rowNumber, $data['Extra_Hours']);
                $rowNumber++;
            }

            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $Dates = date('d-m-Y', strtotime($Date));
            $fileName = 'Employee_Extra_Hours_' . $Dates . '.xlsx';
            $filePath = 'assets/reports/' . $fileName;

            if (!file_exists('assets/reports')) {
                mkdir('assets/reports', 0777, true);
            }

            $writer->save($filePath);

            echo json_encode([
                'Status' => 'Success',
                'file_url' => base_url($filePath),
                'file_Name' => $fileName
            ]);
            return;
        }
    } else {
        redirect(base_url());
    }
}



    public function OT_Hours_Employee_Download()
    {
        $Session = $this->session->userdata('sess_array');

        if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
            if ($this->input->post()) {
                $CompanyCode = $Session['Ccode'];
                $LocationCode = $Session['Lcode'];
                $Login_User   = $Session['UserName'];

                $Date  = $this->input->post('Date');
                $Type  = $this->input->post('Type');
                $Shift = $this->input->post('Shift');

                $OT_Hours_Employee_Download = $this->Reports_Model->OT_Hours_Employee_Download(
                    $CompanyCode,
                    $LocationCode,
                    $Login_User,
                    $Date,
                    $Shift,
                    $Type
                );

                if (empty($OT_Hours_Employee_Download)) {
                    echo json_encode(['Status' => 'Error', 'Message' => 'Employee Extra Hours Work Details Not Found.']);
                    return;
                }

                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Title Row
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'Employee OT Hours Work Details');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);

                // Info Row
                $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode);
                $sheet->setCellValue('J2', 'LOCATION: ' . $LocationCode);
                $sheet->getStyle('A2:J2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                // Header Row
                $sheet->setCellValue('A3', 'S.No')
                    ->setCellValue('B3', 'Working Date')
                    ->setCellValue('C3', 'Previous Shift')
                    ->setCellValue('D3', 'Continued Shift')
                    ->setCellValue('E3', 'Emp No')
                    ->setCellValue('F3', 'Employee Name')
                    ->setCellValue('G3', 'IN Time')
                    ->setCellValue('H3', 'OUT Time')
                    ->setCellValue('I3', 'E-Master Close')
                    ->setCellValue('J3', 'Working Hours');

                $sheet->getStyle('A3:J3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Data Rows
                $rowNumber = 4;
                $serial = 1;

                foreach ($OT_Hours_Employee_Download as $data) {
                    $sheet->setCellValue('A' . $rowNumber, $serial++)
                        ->setCellValue('B' . $rowNumber, $data['Date'] ?? '')
                        ->setCellValue('C' . $rowNumber, $data['Shift'] ?? '')
                        ->setCellValue('D' . $rowNumber, $data['Next_Shift'] ?? '')
                        ->setCellValue('E' . $rowNumber, $data['Employee_ID'] ?? '')
                        ->setCellValue('F' . $rowNumber, $data['Employee_Name'] ?? '')
                        ->setCellValue('G' . $rowNumber, $data['In_Time'] ?? '')
                        ->setCellValue('H' . $rowNumber, $data['Out_Time'] ?? '')
                        ->setCellValue('I' . $rowNumber, $data['E_Master_Closing'] ?? '')
                        ->setCellValue('J' . $rowNumber, number_format((float)($data['OT_Hour'] ?? 0), 2));
                    $rowNumber++;
                }

                // Auto-size columns
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Create folder if not exists
                $reportPath = 'assets/reports/';
                if (!file_exists($reportPath)) {
                    mkdir($reportPath, 0777, true);
                }

                $Dates = date('d-m-Y', strtotime($Date)); // Safe for filenames
                $fileName = 'Employee_OT_Hours_' . $Dates . '.xlsx';
                $filePath = $reportPath . $fileName;

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($filePath);

                echo json_encode([
                    'Status'   => 'Success',
                    'file_url' => base_url($filePath),
                    'file_Name' => $fileName
                ]);
                return;
            } else {
                redirect(base_url());
            }
        } else {
            redirect(base_url());
        }
    }

        public function OT_Employee()
{
    $Session = $this->session->userdata('sess_array');

    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

          $this->data['Favicon'] = 'Precot | OT Report';

        $this->load->view('Frontend/Header',$this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Reports/OT_Employee_List');
            $this->load->view('Frontend/Footer');  
    } else {
        redirect(base_url());
    }
}


public function Get_OT_Employee_List(){
     $Session = $this->session->userdata('sess_array');
      if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {
        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

          $this->data['Favicon'] = 'Precot | OT Report';

        if ($this->input->post()) {

                $Date = $this->input->post('Date');
                $Shift = $this->input->post('Shift');
             
                $this->data['Get_OT_Employee_List'] = $Get_OT_Employee_List = $this->Reports_Model->Get_OT_Employee_List($CompanyCode, $LocationCode, $Login_User, $Date, $Shift);

                if($Get_OT_Employee_List == 0){

                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Shift Not Starting OT Employee Details Not Found..'
                    ]);

                } else {

                    echo json_encode($this->data);

                }
                
            }
    } else {
        redirect(base_url());
    }


}


public function OT_Employee_List_Report_Download()
{
    $Session = $this->session->userdata('sess_array');

    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $CompanyCode = $Session['Ccode'];
        $LocationCode = $Session['Lcode'];
        $Login_User = $Session['UserName'];

        if ($this->input->post()) {

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');
         $Get_OT_Employee_List = $this->Reports_Model->Get_OT_Employee_List($CompanyCode, $LocationCode, $Login_User,$Date, $Shift);
         if (empty($Get_OT_Employee_List)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Shift Not Starting Employee Details Not Found..'
                ]);
                exit;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $mainHeading = 'OT Employee List';
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', $mainHeading);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension('1')->setRowHeight(30);

            $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode)
                ->setCellValue('A3', 'LOCATION: ' . $LocationCode)
                ->setCellValue('I2', 'SHIFT: ' . $Shift)
                ->setCellValue('I3', 'DATE: ' . $Date);

            $sheet->getStyle('I2:I3')->applyFromArray([
                'font' => ['bold' => true],
            ]);
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
            ]);

            $sheet->setCellValue('A2', 'Ccode')
                ->setCellValue('B2', 'Lcode')
                ->setCellValue('C2', 'Sub Department')
                ->setCellValue('D2', 'Position')
                ->setCellValue('E2', 'EmpNo')
                ->setCellValue('F2', 'First Name')
                ->setCellValue('G2', 'Previous Shift')
                ->setCellValue('H2', 'Frame')
                ->setCellValue('I2', 'Machine_Id');
                
            $sheet->getStyle('A2:I2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $rowNumber = 7;
            foreach ($Get_OT_Employee_List as $data) {
                $sheet->setCellValue('A' . $rowNumber, $data->Ccode)
                ->setCellValue('B' . $rowNumber, $data->Lcode)
                 ->setCellValue('C' . $rowNumber, $data->Sub_Department)
                ->setCellValue('D' . $rowNumber, $data->WorkArea)
                    ->setCellValue('E' . $rowNumber, $data->EmpNo)
                    ->setCellValue('F' . $rowNumber, $data->FirstName)
                    ->setCellValue('G' . $rowNumber, $data->Previous_Shift)
                    ->setCellValue('H' . $rowNumber, $data->Frame)
                    ->setCellValue('I' . $rowNumber, $data->Machine_Id);
                $rowNumber++;
            }

            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $currentDate = date('Y-m-d');
            $directory = 'assets/reports';

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $file_path = $directory . '/OT_Employee_List_' . $currentDate . '.xlsx';
            $writer->save($file_path);

            echo json_encode(['file_url' => base_url($file_path)]);
            exit;
        }
    } else {
        redirect(base_url(), 'refresh');
    }
}


public function Employee_Position_Overal_Report_Down()
{
    $Session = $this->session->userdata('sess_array');
    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $LocationCode = $Session['Lcode'];
        $CompanyCode = $Session['Ccode'];
        $Login_User = $Session['UserName'];
        $UserRole =  $Session['UserType'];

        $From_Date = $this->input->post('From_Date'); // e.g., 2025-09-01
        $To_Date = $this->input->post('To_Date');     // e.g., 2025-09-30
        $Employee_Id = $this->input->post('Employee_Id');

        // Get employee data
        $this->data['Employee_Position_Details'] = $Employee_Position_Details = $this->Incentive_Model->Employee_Position_Details($CompanyCode, $LocationCode, $Login_User,$UserRole, $From_Date, $To_Date, $Employee_Id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $mainHeading = 'Employee Position Report';
        $sheet->mergeCells('A1:Z1');
        $sheet->setCellValue('A1', $mainHeading);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Header Row
        $headerRow = 2;
        $col = 'A';

        $staticHeaders = ['EmpNo', 'FirstName', 'A', 'B', 'C', 'Total'];
        foreach ($staticHeaders as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $col++;
        }

        // Save start column after static headers
        $dateStartCol = $col;

        // Add actual date headers (e.g., 01-09-2025 to 30-09-2025)
        $currentDate = strtotime($From_Date);
        $endDate = strtotime($To_Date);
        $dateHeaders = []; // holds ['formatted_date' => 'DAY-n']

        $dayNumber = 1;
        while ($currentDate <= $endDate) {
            $formattedDate = date('d-m-Y', $currentDate); // e.g., 01-09-2025
            $dayKey = 'DAY-' . $dayNumber;

            $sheet->setCellValue($col . $headerRow, $formattedDate);
            $dateHeaders[$formattedDate] = $dayKey;

            $col++;
            $currentDate = strtotime('+1 day', $currentDate);
            $dayNumber++;
        }

        // Get last used column for styling/autosize
        $lastColIndex = ord('A') + count($staticHeaders) + count($dateHeaders) - 1;
        $lastCol = chr($lastColIndex);

        // Style header
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Fill data rows
        $rowNumber = $headerRow + 1;
        foreach ($Employee_Position_Details as $data) {
            $col = 'A';
            $sheet->setCellValue($col++ . $rowNumber, $data->EmpNo);
            $sheet->setCellValue($col++ . $rowNumber, $data->FirstName);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_A_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_B_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_C_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Grade_Day_Count);

            // Insert each date value using 'DAY-n' keys
            $dayIndex = 1;
            foreach ($dateHeaders as $formattedDate => $dayKey) {
                $sheet->setCellValue($col++ . $rowNumber, isset($data->$dayKey) ? $data->$dayKey : '');
                $dayIndex++;
            }

            $rowNumber++;
        }

        // Autosize columns
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Save the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $currentDateString = date('Y-m-d');
        $directory = 'assets/reports';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $file_path = $directory . '/OT_Employee_List_' . $currentDateString . '.xlsx';
        $writer->save($file_path);

        echo json_encode(['file_url' => base_url($file_path)]);
        exit;

    } else {
        redirect(base_url(), 'refresh');
    }
}


public function Employee_Position_Short_Report_Down()
{
    $Session = $this->session->userdata('sess_array');
    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $LocationCode = $Session['Lcode'];
        $CompanyCode = $Session['Ccode'];
        $Login_User = $Session['UserName'];
        $UserRole =  $Session['UserType'];

        $From_Date = $this->input->post('From_Date'); // e.g., 2025-09-01
        $To_Date = $this->input->post('To_Date');     // e.g., 2025-09-30
        $Employee_Id = $this->input->post('Employee_Id');

        // Fetch employee data
        $this->data['Employee_Position_Details'] = $Employee_Position_Details = $this->Incentive_Model->Employee_Position_Details($CompanyCode, $LocationCode, $Login_User,$UserRole, $From_Date, $To_Date, $Employee_Id);

        // Initialize spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header row (starts at Row 1)
        $headerRow = 1;
        $col = 'A';

        // Static headers
        $staticHeaders = ['EmpNo', 'FirstName', 'A', 'B', 'C', 'Total'];
        foreach ($staticHeaders as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $col++;
        }

        // Add dynamic date headers (from From_Date to To_Date)
        $currentDate = strtotime($From_Date);
        $endDate = strtotime($To_Date);
        $dateHeaders = [];

        

        // Last column for autosizing/styling
        $lastCol = chr(ord('A') + count($staticHeaders) + count($dateHeaders) - 1);

        // Style header row
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Fill data rows
        $rowNumber = $headerRow + 1;
        foreach ($Employee_Position_Details as $data) {
            $col = 'A';
            $sheet->setCellValue($col++ . $rowNumber, $data->EmpNo);
            $sheet->setCellValue($col++ . $rowNumber, $data->FirstName);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_A_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_B_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Total_C_Count);
            $sheet->setCellValue($col++ . $rowNumber, $data->Grade_Day_Count);

           

            $rowNumber++;
        }

        // Autosize all columns
        foreach (range('A', $col) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Save the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $currentDateString = date('Ymd_His');
        $directory = 'assets/reports';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'Employee_Position_Report_' . $currentDateString . '.xlsx';
        $file_path = $directory . '/' . $filename;

        $writer->save($file_path);

        // Return file URL
        echo json_encode(['file_url' => base_url($file_path)]);
        exit;

    } else {
        redirect(base_url(), 'refresh');
    }
}




}
