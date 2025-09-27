<div class="main-container">

    <style>
        .custom-select2-container .select2-selection__rendered {
            line-height: 1.0;
        }

        /*
    .table {
        overflow: scro !important;
    } */

        .dataTables_filter label {
            float: right;
            text-align: right;
        }

        /* If using Bootstrap table classes */
        table {
            table-layout: auto;
        }

        td input.form-control,
        td select.form-control {
            height: auto;
            margin-top: 0 !important;
        }


        /* Apply overflow to the responsive container */
        .table-responsive {
            overflow-x: auto;
            position: relative;
        }

        .status {
            display: inline-block;
            /* padding: 8px 20px; */
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            border-radius: 5px;
            margin: 5px;
        }

        .custom-select2-dropdown {
            width: auto !important;
            min-width: 150px;

        }

        /* Custom Button Styles */

        /* White color */
        .custom-white {
            background-color: #fff;
            /* White background */
            color: #000;
            /* Black text */
            border: 1px solid #ddd;
            /* Light border */
        }

        /* Green color for Allocated */
        .custom-green {
            background-color: #A7FEA5;
            /* Green background */
            color: #000;
            /* White text */
            border: 1px solid #A7FEA5;
            /* Green border */
        }

        /* Orange color for No Work */
        .custom-orange {
            background-color: #FFE992;
            /* Orange background */
            color: #000;
            /* White text */
            border: 1px solidrgb(250, 209, 46);
            /* Orange border */
        }

        /* Red color for Shift Closed */
        .custom-red {
            background-color: rgb(250, 126, 126);
            /* Red background */
            color: #000;
            /* White text */
            border: 1px solid #dc3545;
            /* Red border */
        }

        /* Info color for Shift */
        .custom-info {
            background-color: #17a2b8;
            /* Light blue background */
            color: #000;
            /* White text */
            border: 1px solidrgb(100, 115, 255);
            /* Light blue border */
        }

        /* Yellow color for Late */
        .custom-warning {
            background-color: rgb(230, 196, 94);
            /* Yellow background */
            color: #000;
            /* White text */
            border: 1px solid #ffc107;
            /* Yellow border */
        }

        /* Optional: Hover effects */
        .custom-white:hover {
            background-color: rgb(221, 222, 222);
            /* Light gray background on hover */
        }

        .custom-green:hover {
            background-color: rgb(102, 204, 100);
            /* Darker green on hover */
        }

        .custom-orange:hover {
            background-color: rgb(234, 188, 52);
            /* Darker orange on hover */
        }

        .custom-red:hover {
            background-color: rgb(203, 67, 81);
            /* Darker red on hover */
        }

        .custom-info:hover {
            background-color: rgb(63, 194, 214);
            /* Darker blue on hover */
        }

        .custom-warning:hover {
            background-color: #e0a800;
            /* Darker yellow on hover */
        }
        .OT_List_Btn:hover {
         background-color: #69118bff;

        }


        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.39);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .preloader-content {
            text-align: center;
        }

        .preloader-content img {
            animation: spin 2s infinite linear;
        }

        .preloader-content p {
            font-size: 18px;
            font-weight: bold;
            color: rgb(105, 251, 0);
            /* Cotton plant theme color */
            margin-top: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .custom-btn {
            font-size: 0.75rem;
            /* Adjust as needed */
        }

        #Allocation_Table,td {
    font-size: 12px;
}


    </style>

<?php  if($Location_Code == 'PRECOT - A' || $Location_Code == 'PRECOT - C' || $Location_Code == 'PRECOT - D' || $Location_Code == 'PRECOT - K'  ) {?>

    <div class="pd-ltr-20 xs-pd-20-10">

        <div class="min-height-200px">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Work Assignment</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    <!-- PHP code to dynamically load shift options should go here -->
                                </select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>
                            <input type="hidden" value="SHIFT" id="Type">
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name">
                                    <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                                </select>
                            </div>
                        </div>

                        <input type="Hidden" value="Shift_Employee_Screen" id="Allocation_Screen_Type">

                    </div>
                </form>


                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" width="60">
                    </div>



                    <div id="Allocation_Table_Container">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Allocation List</h4>
                        </div>

                        <div class="row justify-content-end py-3">
                            <div class="col-auto">
                                <button type="button" class="btn btn-info btn-sm" id="Previous-Date-Allocation">Previous
                                    Allocation</button>
                            </div>
                        </div>




                        <div class="table-container" id="Allocation_Details_Color_Details" style="display:none;">
                            <div class="row py-3">
                                <button type="button" class="btn btn-sm custom-white custom-btn" id="unAllocatedBtn">
                                    Un Allocated: <span id="unAllocatedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-green custom-btn" id="allocatedBtn">
                                    Allocated: <span id="allocatedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-orange custom-btn" id="noWorkBtn">
                                    No Work: <span id="noWorkCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-red custom-btn" id="shiftClosedBtn">
                                    Partial Shift Closed: <span id="shiftClosedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-info custom-btn" id="shiftBtn">
                                    Shift Punched Employee: <span id="shiftCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-warning custom-btn" id="lateBtn">
                                    Late Punched Employee: <span id="lateCount">0</span>
                                </button>
                               
                            </div>
                            <div class="row py-3">
                                   <button type="button" class="btn btn-sm custom-btn OT_List_Btn" id="OT_List_Btn" style="background-color: #c78bc2ff;">
                                    OT Employee: <span id="OT_List_Btn">0</span>
                                </button>&nbsp;
                                <button type="button" class="btn btn-sm custom-btn All_List_Btn" id="All_Employee_List_Btn" style="background-color: #aae5ecff;">
                                    All Employee List : <span id="All_Employee_List"></span>
                                </button>
                            </div>
                            <div class="row py-3">
                                <input type="text" class="form-control form-control-sm col-auto" id="Total_Machine_Count" style="width: 200px;" placeholder="Total" readonly> &nbsp;
                                <input type="text" class="form-control form-control-sm col-auto" id="Allocated_Machine_Count" style="width: 200px;" placeholder="Allocated" readonly> &nbsp;
                                <input type="text" class="form-control form-control-sm col-auto" id="Un_Allocated_Machine_Count" style="width: 200px;" placeholder="Unallocated" readonly> &nbsp;

                                <select class="form-control form-control-sm custom-select2 col-auto" name="Balance_MachineID" id="Balance_MachineID" style="width: 300px;">
                                    <option value="">-- Select Unallocated Machine --</option>
                                </select>
                            </div>





                            <table class="table table-responsive table-sm" id="Allocation_Table">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Employee Name</th>
                                        <th>P.S</th>
                                        <th>Position</th>
                                        <th>Sider</th>
                                        <th>Machine</th>
                                        <th>Actions</th>
                                        <th>Employee Id</th>
                                        <th>Type</th> 
                                        <th>Description</th>

                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Work Allocation Upload Modal -->
                <div class="modal fade" id="bulkupload" tabindex="-1" aria-labelledby="bulkUploadLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="bulkUploadLabel">Bulk Upload</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="bulkUploadForm" method="POST"
                                    action="<?php echo base_url('Upload/work_allocation_upload'); ?>"
                                    enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="fileInput">Choose File</label>
                                        <input type="file" name="fileInput" class="form-control" id="fileInput"
                                            accept=".csv, .xlsx" required>
                                    </div>
                                    <div class="form-group">
                                        <small class="form-text text-muted">
                                            Supported formats: CSV, Excel. Please ensure the file is properly formatted.
                                        </small>
                                    </div>
                                    <div id="uploadStatus" class="text-success" style="display: none;">File uploaded
                                        successfully!</div>
                                </form>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" form="bulkUploadForm" class="btn btn-primary">Upload</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



<?php } else if($Location_Code == 'PRECOT - M') {?>

<div class="pd-ltr-20 xs-pd-20-10">

        <div class="min-height-200px">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Work Assignment</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    <!-- PHP code to dynamically load shift options should go here -->
                                </select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>
                            <input type="hidden" value="SHIFT" id="Type">
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name">
                                    <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                                </select>
                            </div>
                        </div>

                        <input type="Hidden" value="Shift_Employee_Screen" id="Allocation_Screen_Type">

                    </div>
                </form>


                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" width="60">

                    </div>



                    <div id="Allocation_Table_Container">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Allocation List</h4>
                        </div>

                        <div class="row justify-content-end py-3">
                            <div class="col-auto">
                                <button type="button" class="btn btn-info btn-sm" id="Previous-Date-Allocation">Previous
                                    Allocation</button>
                            </div>
                        </div>




                        <div class="table-container" id="Allocation_Details_Color_Details">
                            <div class="row py-3">
                                <button type="button" class="btn btn-sm custom-white custom-btn" id="unAllocatedBtn">
                                    Un Allocated: <span id="unAllocatedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-green custom-btn" id="allocatedBtn">
                                    Allocated: <span id="allocatedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-orange custom-btn" id="noWorkBtn">
                                    No Work: <span id="noWorkCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-red custom-btn" id="shiftClosedBtn">
                                    Partial Shift Closed: <span id="shiftClosedCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-info custom-btn" id="shiftBtn">
                                    Shift Punched Employee: <span id="shiftCount">0</span>
                                </button> &nbsp;
                                <button type="button" class="btn btn-sm custom-warning custom-btn" id="lateBtn">
                                    Late Punched Employee: <span id="lateCount">0</span>
                                </button>
                            </div>
                            <div class="row py-3">
                                <input type="text" class="form-control form-control-sm col-auto" id="Total_Machine_Count" style="width: 200px;" placeholder="Total"> &nbsp;
                                <input type="text" class="form-control form-control-sm col-auto" id="Allocated_Machine_Count" style="width: 200px;" placeholder="Allocated"> &nbsp;
                                <input type="text" class="form-control form-control-sm col-auto" id="Un_Allocated_Machine_Count" style="width: 200px;" placeholder="Unallocated"> &nbsp;

                                <select class="form-control form-control-sm custom-select2 col-auto" name="Balance_MachineID" id="Balance_MachineID" style="width: 300px;">
                                    <option value="">-- Select Unallocated Machine --</option>
                                </select>
                            </div>





                            <table class="table table-responsive" id="Allocation_Table">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Sub Department</th>
                                        <th>Position</th>
                                        <th>Type</th>
                                        <th>Employee Id</th>
                                        <th>Employee Name</th>
                                        <th>Sider</th>
                                        <th>Machine</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Work Allocation Upload Modal -->
                <div class="modal fade" id="bulkupload" tabindex="-1" aria-labelledby="bulkUploadLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="bulkUploadLabel">Bulk Upload</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="bulkUploadForm" method="POST"
                                    action="<?php echo base_url('Upload/work_allocation_upload'); ?>"
                                    enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="fileInput">Choose File</label>
                                        <input type="file" name="fileInput" class="form-control" id="fileInput"
                                            accept=".csv, .xlsx" required>
                                    </div>
                                    <div class="form-group">
                                        <small class="form-text text-muted">
                                            Supported formats: CSV, Excel. Please ensure the file is properly formatted.
                                        </small>
                                    </div>
                                    <div id="uploadStatus" class="text-success" style="display: none;">File uploaded
                                        successfully!</div>
                                </form>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" form="bulkUploadForm" class="btn btn-primary">Upload</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


    <?php  }?>



            <!-- Include the script for handling work allocation -->
            <script src="<?php echo base_url('assets/Script/Work.js') ?>"></script>