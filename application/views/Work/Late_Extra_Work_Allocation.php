<div class="main-container">

    <style>
        .custom-select2-container .select2-selection__rendered {
            line-height: 1.0;
        }

        /*
    .table {
        overflow: scro !important;
    } */

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

        .green {
            background-color: #A7FEA5;
            color: black;
        }

        .orange {
            background-color: #FFE992;
            color: black;
        }

        .red {
            background-color: rgb(250, 126, 126);
            color: black;
        }

        .white {
            background-color: white;
            color: black;
            border: 1px solid #ccc;
        }

        .custom-white {
            background-color: rgb(247, 76, 76);
            /* White background */
            color: #ffff;
            /* Black text */
            border: 1px solid #ddd;
            /* Light border */
        }

        .custom-white:hover {
            background-color: rgb(255, 51, 51);
            /* Light gray background on hover */
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
    </style>
    
    <?php  if($Location_Code == 'PRECOT - A' || $Location_Code == 'PRECOT - C' || $Location_Code == 'PRECOT - D' || $Location_Code == 'PRECOT - K'  ) {?>


    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Late Employee Work Assignment</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date">
                            </div>
                        </div>

                        <div class="col-md-2" id="Shift_Div">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    <!-- PHP code to dynamically load shift options should go here -->
                                </select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>

                        </div>

                        <div class="col-md-2" id="Sub_Section_Div">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Assign Type</label>
                                <select class="custom-select2 form-control" name="Assign_Type" id="Assign_Type">
                                    <option value=""></option>
                                    <option value="LATE" selected>LATE</option>
                                    <option value="EXTRA">EXTRA</option>
                                    <span class="text-danger"><?php echo form_error('Assign_Type'); ?></span>
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

                        <input type="Hidden" value="Late_Extra_Assign" id="Allocation_Screen_Type">

                    </div>
                </form>


                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                    </div>



                    <div id="Allocation_Table_Container">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Allocation List</h4>
                        </div>

                        <div class="table-container" id="Allocation_Details_Color_Details">
                            <div class="row py-3">

                                <button type="button" class="btn btn-sm custom-white" id="unAllocatedBtn">
                                    Un Allocated: <span id="unAllocatedCount">0</span>
                                </button>

                            </div>

                            <table class="table table-responsive" id="Allocation_Table">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Employee Name</th>
                                        <th>Position</th>
                                        <th>Sider</th>
                                        <th>Machine</th>
                                        <th>Actions</th>
                                        <th>Employee Id</th>
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
                    <h4 class="text-black h5 text-center">Late Employee Work Assignment</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date">
                            </div>
                        </div>

                        <div class="col-md-2" id="Shift_Div">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    <!-- PHP code to dynamically load shift options should go here -->
                                </select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>

                        </div>

                        <div class="col-md-2" id="Sub_Section_Div">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Assign Type</label>
                                <select class="custom-select2 form-control" name="Assign_Type" id="Assign_Type">
                                    <option value=""></option>
                                    <option value="LATE" selected>LATE</option>
                                    <option value="EXTRA">EXTRA</option>
                                    <span class="text-danger"><?php echo form_error('Assign_Type'); ?></span>
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

                        <input type="Hidden" value="Late_Extra_Assign" id="Allocation_Screen_Type">

                    </div>
                </form>


                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                    </div>



                    <div id="Allocation_Table_Container">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Allocation List</h4>
                        </div>

                        <div class="table-container" id="Allocation_Details_Color_Details">
                            <div class="row py-3">

                                <button type="button" class="btn btn-sm custom-white" id="unAllocatedBtn">
                                    Un Allocated: <span id="unAllocatedCount">0</span>
                                </button>

                            </div>

                            <table class="table table-responsive" id="Allocation_Table">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Sub Department</th>
                                        <th>Position</th>
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

    <?php  } ?>
          


            <!-- Include the script for handling work allocation -->
            <script src="<?php echo base_url('assets/Script/Late_Extra.js') ?>"></script>