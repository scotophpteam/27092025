<div class="main-container">

    <style>
        .custom-select2-container .select2-selection__rendered {
            line-height: 1.0;
        }

        .table-responsive {
            overflow-x: auto;
            position: relative;
        }

        .status {
            display: inline-block;
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



        .input-error {
            border: 2px solid red !important;
        }

        .error-text {
            color: red;
            font-size: 12px;
        }
    </style>

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Manual Attendance Entry</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="Manual_Attendance_Entry_Screen" value="Manual_Attendance_Entry_Screen">

                    <div class="row py-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="Date" class="form-control" id="Date" name="Date" >
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift"></select>
                                <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                            </div>
                        </div>



                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Working Type</label>
                                <select class="custom-select2 form-control" name="Working_Type" id="Working_Type">
                                    <option value="First Half">First Half</option>
                                    <option value="Second Half">Second Half</option>
                                    <option value="Both" SELECTED>Both</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Type'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Employee ID</label>
                                <select class="form-control custom-select2" name="Employee_Id" id="Employee_Id"></select>
                                <span class="text-danger"><?php echo form_error('Type'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Attendance Type</label>
                                <select class="custom-select2 form-control" name="Punching_Type" id="Punching_Type">
                                    <option value="Present" SELECTED>Present</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Type'); ?></span>
                            </div>
                        </div>



                        <div class="col-md-2">
                            <div class="form-group">
                                <label>From Time (24 Hrs)</label>
                                <input type="text" class="form-control" id="From_Time" name="From_Time" readonly>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>To Time (24 Hrs)</label>
                                <input type="text" class="form-control" id="To_Time" name="To_Time" readonly>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total Working Hr</label>
                                <input type="text" class="form-control" id="Total_Working_Hour" name="Total_Working_Hour" readonly>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total OT Hr</label>
                                <input type="number" class="form-control" id="Total_OT_Hour" name="Total_OT_Hour">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Supervisor Name</label>
                                <select class="custom-select2 form-control" name="Supervisor_Name" id="Supervisor_Name">
                                </select>
                                <span class="text-danger"><?php echo form_error('Supervisor_Name'); ?></span>
                            </div>
                        </div>
                        <!-- </div> -->
                    </div>
                    <!-- ✅ Button Centered Correctly -->
                    <div class="row" style="display: flex; justify-content: center;">
                        <div class="form-group mt-3">
                            <button type="button" class="btn btn-success btn-sm" id="Entry_Manual_Attendance">
                                Update
                            </button>
                        </div>
                    </div>



                </form>

                <!-- Include the script for handling work allocation -->
                <script src="<?php echo base_url('assets/Script/Employee.js') ?>"></script>

            </div>
        </div>