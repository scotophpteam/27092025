<?php
$Session = $this->session->userdata('sess_array');

if (!empty($Session) && isset($Session['IsOnLogin']) && $Session['IsOnLogin'] === TRUE) {

    $CompanyCode = $Session['Ccode'];
    $LocationCode = $Session['Lcode'];
    $UserRole = $Session['Designation'];

    if ($UserRole == 'Manager' ||  $UserRole == 'Super Admin') {
?>
        <style>
            #grade-legend p {
                margin: 5px 0;
                font-weight: bold;
            }
        </style>

        <div class="main-container">
            <div class="pd-ltr-20 xs-pd-20-10">
                <div class="min-height-200px">

                    <!-- Card Box for Form and Chart -->
                    <div class="pd-20 card-box mb-30">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Attendance Record Grade</h4>
                        </div>
                        <input type="hidden" id="Employee_Attendance_Record_Page" value="Employee_Attendance_Record_Page">

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Filter and Buttons Row -->
                            <div class="row align-items-end py-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select class="custom-select2 form-control" name="Department" id="Sub_Department">
                                            <!-- Department options loaded dynamically -->
                                        </select>
                                        <span class="text-danger"><?php echo form_error('Department'); ?></span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total Active Employees</label>
                                        <input type="text" class="form-control" id="Active_Employee_List" readonly>
                                    </div>
                                </div>

                                <div class="col-auto" style="margin-bottom: 25px;">
                                    <button type="button" class="btn btn-info btn-sm" id="Attendance_Sheet_Tables">View</button>
                                </div>

                                <div class="col-auto" style="margin-bottom: 25px; display:none" id="Attendance_Employee_Grade_Section">
                                    <button type="button" class="btn btn-warning btn-sm" id="Attendance_Employee_Grade_Dbtn">Download</button>
                                </div>
                            </div>

                            <!-- Chart and Legend -->
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <canvas id="myBarChart" width="400" height="200"></canvas>
                                </div>
                                <div class="col-md-3">
                                    <div id="grade-legend">
                                        <p><strong>A+</strong> – Excellent</p>
                                        <p><strong>A</strong> – Very Good</p>
                                        <p><strong>B+</strong> – Good</p>
                                        <p><strong>B</strong> – Average</p>
                                        <p><strong>C</strong> – Below Average</p>
                                        <p><strong>T</strong> – Trainee</p>
                                        <p><strong>N</strong> – New Joining</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Employee Attendance Grade List -->
                    <div class="card-box mb-30" id="Employee_Attendance_List_Grade">
                        <div class="pd-20">
                            <h4 class="text-black h5 text-center">Employee Attendance Grade</h4>
                        </div>

                        <div class="row justify-content-end py-3">
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-success btn-sm" id="AGradeGroup">A Grade Group</button>
                                <button type="button" class="btn btn-outline-warning btn-sm" id="BGradegroup">B Grade Group</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="CGradegroup">C Grade Group</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="TraineeGradegroup">Trainee Group</button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="NewGradegroup">New Group</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="Default">Default</button>
                            </div>
                        </div>

                        <div class="table-container">
                            <div style="overflow-x: auto; width: 100%;">
                                <table class="table table-responsive nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>J.Date</th>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>W.Months</th>
                                            <th>Status</th>
                                            <th>A.Percentage</th>
                                            <th>Employee A.Grade</th>
                                            <th>Last Grade</th>
                                            <th>Month Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody id="Attendance_Sheet_Table">
                                        <!-- Rows dynamically populated -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <script src="<?php echo base_url('assets/Script/Grade_Master.js') ?>"></script>

                </div>

            <?php
        } else if($UserRole == 'HRL'){

            

        }
        
        
        
        
        else {
            // Non-manager view
            ?>

                <div class="main-container">
                    <div class="pd-ltr-20 xs-pd-20-10">
                        <div class="min-height-200px">
                            <!-- Standard Actual Report Start -->
                            <div class="row">
                                <div class="col-md-12 mb-30">
                                    <div class="pd-20 card-box height-100-p">
                                        <input type="hidden" name="Page_Name" id="Page_Name" value="Home_Page_Dashboard">

                                        <div class="row g-3 mb-4">
                                            <div class="col-md-3">
                                                <label for="Date" class="form-label">Date</label>
                                                <input type="date" class="form-control" id="Date" name="Date">
                                            </div>

                                            <div class="col-md-3">
                                                <label for="Shift" class="form-label">Shift</label>
                                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                                    <!-- Add options dynamically -->
                                                </select>
                                                <span class="text-danger"><?php echo form_error('ShiftLeft'); ?></span>
                                            </div>
                                        </div>

                                        <!-- Summary Card -->
                                        <div class="row" id="Chart_View_Employee_Count" style="display: none;">
                                            <div class="col-12">
                                                <div class="card mb-4">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <canvas id="myBarChart" width="300" height="100"></canvas>

                                                            <!-- <div class="col-md-6 border-end pe-md-4">
                                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                                            <strong>Actual Comers</strong>
                                                            <span id="actual-comers" class="text-primary fw-bold">0</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                                            <strong>Late Comers</strong>
                                                            <span id="late-comers" class="text-warning fw-bold">0</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                                            <strong>Total Engaged Employees</strong>
                                                            <span id="total-engaged" class="text-success fw-bold">0</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 pe-md-4">
                                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                                            <strong>Per Shift Engaged Employee </strong>
                                                            <span id="per-shift-engaged" class="text-success fw-bold">0</span>
                                                        </div>
                                                    </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>


                                        <!-- Two-Column Tables -->
                                        <div class="row g-4">
                                            <!-- Left Column Table -->
                                            <div class="col-md-6">
                                                <form method="post" action="">
                                                    <h6 class="text-center mb-3">Standard vs Actual Engaged Employee</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered nowrap w-100" id="Standard_Actual_Table">
                                                            <thead>
                                                                <tr>
                                                                    <th>S.No</th>
                                                                    <th>Position</th>
                                                                    <th>Standard</th>
                                                                    <th>Actual</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Left table rows go here -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Right Column Table -->
                                            <div class="col-md-6">
                                                <form method="post" action="">
                                                    <h6 class="text-center mb-3">Actual Shift Present Employee</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered nowrap w-100" id="Sub_Division_Wise_Employee_List">
                                                            <thead>
                                                                <tr>
                                                                    <th>S.No</th>
                                                                    <th>Position</th>
                                                                    <th>Sub Division</th>
                                                                    <th>Emp Count</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Right table rows go here -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                    <img src="https://i.gifer.com/ZKZg.gif" alt="" width="60">

                </div> -->

                        <!-- External Script -->
                        <script src="<?php echo base_url('assets/Script/Standard_Actual.js'); ?>"></script>

                        <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script> -->

                <?php
            } // End of else (non-manager)
        } else {
            // Session not valid or not logged in
            echo '<p>Please login to access this page.</p>';
        }
                ?>