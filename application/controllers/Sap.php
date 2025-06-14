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

class Sap extends CI_Controller
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
        $this->load->model('Sap_Model');

    }


    public function index(){


        $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {

           $this->data['Favicon'] = 'Precot | Work Allocation';


            // $this->load->view('Frontend/Header',$this->data);
            // $this->load->view('Frontend/Sidebar');
            // $this->load->view('Work/Work_Allocation', $this->data);
            // $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url(), 'refresh');
        }

    }

    public function Machine_Work_Details(){

        $Session = $this->session->userdata('sess_array');
        if (!empty( $Session) && isset( $Session['IsOnLogin']) &&  $Session['IsOnLogin'] === TRUE) {


           $this->data['Favicon'] = 'Precot | Sap Machine Details';
           $LocationCode =   $Session['Lcode'];
           $CompanyCode =   $Session['Ccode'];
           $Login_User =  $Session['UserName'];


           if($_POST){

            $Date = $this->input->post('Date');
            $Shift = $this->input->post('Shift');

            $this->data['Machine_Work_Details'] = $Machine_Work_Details = $this->Sap_Model->Machine_Work_Details($LocationCode, $CompanyCode , $Login_User, $Date, $Shift);

            echo json_encode($this->data);
            exit;

           }


            $this->load->view('Frontend/Header',$this->data);
            $this->load->view('Frontend/Sidebar');
            $this->load->view('Sap/Machine_Work_Details', $this->data);
            $this->load->view('Frontend/Footer');

        } else {
            redirect(base_url(), 'refresh');
        }

    }

   public function Machine_Work_Details_Download() {

    $Session = $this->session->userdata('sess_array');
    if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

        $this->data['Favicon'] = 'Precot | Sap Machine Details';

        $LocationCode = $Session['Lcode'];
        $CompanyCode = $Session['Ccode'];
        $Login_User = $Session['UserName'];

        $Date = $this->input->post('Date');
        $Shift = $this->input->post('Shift');

        $Machine_Work_Details = $this->Sap_Model->Machine_Work_Details($LocationCode, $CompanyCode, $Login_User, $Date, $Shift);

        if (empty($Machine_Work_Details)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Employee Shift Closing Report not found!'
            ]);
            return;
        }

        // Load PhpSpreadsheet classes
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set main heading
        $mainHeading = 'Daily SAP Upload Machine Details';
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', $mainHeading);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Set metadata
        $sheet->setCellValue('A2', 'COMPANY: ' . $CompanyCode);
        $sheet->setCellValue('A3', 'LOCATION: ' . $LocationCode);
        $sheet->setCellValue('K2', 'SHIFT: ' . $Shift);
        $sheet->setCellValue('K3', 'DATE: ' . $Date);

        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);
        $sheet->getStyle('K2:K3')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Header row
        $sheet->setCellValue('A6', 'Ccode')
              ->setCellValue('B6', 'Lcode')
              ->setCellValue('C6', 'Department')
              ->setCellValue('D6', 'Sub Department')
              ->setCellValue('E6', 'WorkArea')
              ->setCellValue('F6', 'Date') // FIXED: F column missing previously
              ->setCellValue('G6', 'Shift')
              ->setCellValue('H6', 'EmpNo')
              ->setCellValue('I6', 'FirstName')
              ->setCellValue('J6', 'Machine_Id')
              ->setCellValue('K6', 'Frame');

        $sheet->getStyle('A6:K6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Fill data
        $rowNumber = 7;
        foreach ($Machine_Work_Details as $data) {
            $sheet->setCellValue('A' . $rowNumber, $data['Ccode'])
                  ->setCellValue('B' . $rowNumber, $data['Lcode'])
                  ->setCellValue('C' . $rowNumber, $data['Department'])
                  ->setCellValue('D' . $rowNumber, $data['Sub_Department'])
                  ->setCellValue('E' . $rowNumber, $data['WorkArea'])
                  ->setCellValue('F' . $rowNumber, $data['Date'])
                  ->setCellValue('G' . $rowNumber, $data['Shift'])
                  ->setCellValue('H' . $rowNumber, $data['Employee_Id'])
                  ->setCellValue('I' . $rowNumber, $data['Employee_Name'])
                  ->setCellValue('J' . $rowNumber, $data['Machine_Id'])
                  ->setCellValue('K' . $rowNumber, $data['Frame']);
            $rowNumber++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Prepare download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $currentDate = date('Y-m-d');
        $fileName = 'Daily_Sap_Machine_Details_' . $currentDate . '.xlsx';
        $fileDir = 'assets/reports/shift/';
        $filePath = $fileDir . $fileName;

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        $writer->save($filePath);

        echo json_encode([
            'status' => 'success',
            'file_url' => base_url($filePath)
        ]);
        return;

    } else {
        redirect(base_url(), 'refresh');
    }
}


}