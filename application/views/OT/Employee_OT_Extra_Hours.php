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

    input.Extra_Hours {
        display: block;
        visibility: visible;
        position: relative;
    }

    .input-error {
        border: 2px solid red !important;
    }

    .error-text {
        color: red;
        font-size: 12px;
    }
</style>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <input type="hidden" id="Page_Name" value="Employee_OT_Extra_Hours_Page">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Consolidation OT-Extra Hours Report</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" id="Date" name="Date" value="">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="custom-select2 form-control" name="Type" id="Type">
                                    <option value="EXTRA">EXTRA</option>
                                    <option value="OT">OT</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Type'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-2" id="Shift_Previous">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift"></select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name"></select>
                                <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3 d-flex align-items-end" style="margin-bottom: 27.5px;">
                            <button type="button" class="btn btn-info btn-sm" id="OT_Extra_Hours_Employee_View">View</button>
                            &nbsp;
                            <button type="button" class="btn btn-warning btn-sm" id="OT_Extra_Hours_Employee_Update" style="display:none">Update</button>
                            &nbsp;
                            <button type="button" class="btn btn-success btn-sm" id="OT_Extra_Hours_Employee_Download" style="display:none">Download</button>
                            &nbsp;
                            <button type="button" class="btn btn-success btn-sm" id="OT_Hours_Employee_Download" style="display:none">Download</button>
                        </div>
                    </div>

                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">
                    </div>
                </form>

                <!-- EXTRA Hours Section -->
                <div class="container-fluid mt-3 table-container" id="OT_Extra_Hours_Employee_Update_Section" style="display:none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="OT_Extra_Hours_Employee_List">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>#</th>
                                    <th class="Employee_Id">Employee Id</th>
                                    <th class="Employee_Name">Employee Name</th>
                                    <th class="WorkArea">Status</th>
                                    <th class="WorkArea">IN Time</th>
                                    <th class="WorkArea">IN OUT</th>
                                    <th>W.Hours</th>
                                    <th class="WorkArea">E-Master</th>
                                    <th>Difference</th>
                                    <th>Verify</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic rows go here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- OT Hours Section -->
                <div class="container-fluid mt-3 table-container" id="OT_Hours_Employee_Update_Section" style="display:none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="OT_Hours_Employee_List">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>#</th>
                                    <th class="Employee_Id">Employee Id</th>
                                    <th class="Employee_Name">Employee Name</th>
                                    <th class="WorkArea">Status</th>
                                    <th class="WorkArea">IN Time</th>
                                    <th class="WorkArea">IN OUT</th>
                                    <th>W.Hours</th>
                                    <th class="WorkArea">E-Master</th>
                                    <th>Difference</th>
                                    <th>Verify</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic rows go here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('assets/Script/OT.js') ?>"></script>