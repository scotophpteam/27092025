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
                                <p><strong>N</strong> – New Joinee</p>
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
                                    <th>Joining Date</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Working Months</th>
                                    <th>Status</th>
                                    <th>Attendance Percentage</th>
                                    <th>Employee Attendance Grade</th>
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