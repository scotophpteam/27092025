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

    .OTEmployeeCheckbox {
        width: 15px;
        height: 15px;
        transform: scale(1.5);
        transform-origin: top left;
        cursor: pointer;
    }
</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <input type="hidden" id="Page_Name" value="Employee_Extra_Shift_Closing_Page">


            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Extra Hours Closing</h4>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date" value="">
                            </div>
                        </div>



                        <input type="hidden" id="Shift">


                        <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div> -->

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name">
                                    <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                                </select>

                                <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name">
                                    <option value=""></option>
                                    <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                                </select>
                            </div>
                        </div> -->


                    </div>

                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                    </div>

                    <div class="row justify-content-end" id="Shift_Closing_container">
                        <div class="col-auto">
                            <button type="button" class="btn btn-warning btn-sm"
                                id="Shift_Closing_Report">Download</button>

                        </div>
                    </div>

                </form>

                <div class="container-fluid mt-3 table-container" id="Shift_Closing_Section">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="Shift_Employee_List">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>#</th>
                                    <th class="Department">Sub Department</th>
                                    <th class="WorkArea">Position</th>
                                    <th class="JobCard">Position Id</th>
                                    <th class="Employee_Id">Employee Id</th>
                                    <th class="Employee_Name">Employee Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic rows go here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center bg-warning py-2 rounded mt-3 Selected_Count">
                        <p class="mb-0 fw-bold text-white">
                            Confirmed Employees: <span id="confirmedCount">0</span>
                        </p>
                    </div>

                    <div class="row justify-content-end py-4">
                        <div class="col-auto">
                            <button type="button" class="btn btn-success btn-sm" id="Shift_Employee_List_Update">
                                Employee Shift Closing
                            </button>
                        </div>
                    </div>
                </div>

            </div>


            <script src="<?php echo base_url('assets/Script/OT.js') ?>"></script>