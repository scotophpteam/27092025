<style>
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


<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-20 card-box mb-30">
                <input type="hidden" id="Employee_Grade_Page" value="Employee_Grade_Page">
                <div class="pd-20">
                    <h4 class="text-black h5 text-center">Employee Status</h4>
                </div>
                <!-- Button Groups for Date Filters -->
                <div class="row py-3" id="Day_Wise_Visible">
                    <div class="col-md-12 d-flex justify-content-end p-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary btn-sm" id="Last_30">Last 30 Days</button> &nbsp;
                            <button type="button" class="btn btn-warning btn-sm" id="Last_60">Last 60 Days</button> &nbsp;
                            <button type="button" class="btn btn-info btn-sm" id="Last_90">Last 90 Days</button> &nbsp;
                            <button type="button" class="btn btn-primary btn-sm" id="Last_120">Last 120 Days</button> &nbsp;

                        </div>
                    </div>
                </div>
                <!-- Button Groups for OnRoll Date Filters -->
                <div class="row py-3" id="OnRoll_Day_Wise_Visible">
                    <div class="col-md-12 d-flex justify-content-end p-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary btn-sm" id="On_Last_30">Last 30 Days</button> &nbsp;
                            <button type="button" class="btn btn-warning btn-sm" id="On_Last_60">Last 60 Days</button> &nbsp;
                            <button type="button" class="btn btn-info btn-sm" id="On_Last_90">Last 90 Days</button> &nbsp;
                            <button type="button" class="btn btn-primary btn-sm" id="On_Last_120">Last 120 Days</button> &nbsp;

                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2 align-items-end">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="custom-select2 form-control" name="Department" id="Sub_Department">
                                    <!-- Department options -->
                                </select>
                                <span class="text-danger"><?php echo form_error('Department'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="custom-select2 form-control" name="Employee_Status" id="Employee_Status">
                                    <option value=""></option>
                                    <option value="Active">Active</option>
                                    <option value="InActive">In-Active</option>
                                    <option value="Traniee">Trainee</option>
                                    <option value="OnRoll">OnRoll</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Type'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3" id="Inactive">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" class="form-control" name="Fdate" id="Fdate">
                                <span class="text-danger"><?php echo form_error('Fdate'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3" id="Inactive1">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" class="form-control" name="Tdate" id="Tdate">
                                <span class="text-danger"><?php echo form_error('Tdate'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-2" id="Trainee">
                            <div class="form-group">
                                <label>Month</label>
                                <input type="number" class="form-control" name="TaineeMonth" id="TaineeMonth">
                                <span class="text-danger"><?php echo form_error('TaineeMonth'); ?></span>
                            </div>
                        </div>



                        <div class="col-auto" style="margin-bottom: 25px;">
                            <button type="button" class="btn btn-info btn-sm" id="Employee_Statuss">View</button>
                        </div>
                    </div>

                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                    </div>


                </form>
                <div class="container-fluid mt-3" id="Card_Active">
                    <div class="py-3">
                        <h4 class="text-black h5 text-center" id="Table_Title_Active">Employee Active List</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="Active_Employee_Table">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Joining Date</th>
                                    <th>Experience</th>
                                    <th>Grade</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="Active_Employee">
                                <!-- Table content will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Inactive Employees Section -->
                <div class="container-fluid mt-3" id="Card_InActives">
                    <div class="py-3">
                        <h4 class="text-black h5 text-center" id="Table_Title_InActive">Employee Inactive List</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="Inactive_Employee_Table">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Joining Date</th>
                                    <th>Resigned Date</th>
                                    <th>Experience</th>
                                    <th>Grade</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="InActive_Employee">
                                <!-- Table rows will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Trainee Employees Section -->
                <div class="container-fluid mt-3" id="Card_Traniee">
                    <div class="py-3">
                        <h4 class="text-black h5 text-center" id="Table_Title_Trainee">Employee Trainee List</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="Trainee_Employee_Table">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Joining Date</th>
                                    <th>Experience</th>
                                    <th>Trainee</th>
                                    <th>Grade</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="TraineeEmployee">
                                <!-- Table content will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- OnRoll Employees Section -->
                <!-- Employee OnRoll Section -->
                <div class="container-fluid mt-3" id="Card_OnRoll">
                    <div class="py-3">
                        <h4 class="text-black h5 text-center" id="Table_Title_Card_OnRoll">Employee OnRoll List</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="OnRoll_Employee_Table">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Joining Date</th>
                                    <th>Experience</th>
                                    <th>Month</th>
                                    <th>Grade</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="OnrollEmployee">
                                <!-- Table content will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>




        </div>

        <script src="<?php echo base_url('assets/Script/Grade_Master.js') ?>"></script>