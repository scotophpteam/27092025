<div class="main-container">

    <style>
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

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">

            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Leave Apply</h4>
                </div>

                <input type="hidden" id="Employee_Leave_Apply_Screen" value="Employee_Leave_Apply_Screen">

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="Employee_Id">Employee Name</label>
                                <select class="custom-select2 form-control" name="Employee_Id" id="Employee_Id">
                                    <option value=""></option>
                                    <!-- PHP code to dynamically load employee options should go here -->
                                </select>
                                <span class="text-danger" id="Employee_Id_error"></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="From_Date">From Date</label>
                                <input type="date" class="form-control" id="From_Date" name="From_Date">
                                <span class="text-danger" id="From_Date_error"></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="To_Date">To Date</label>
                                <input type="date" class="form-control" id="To_Date" name="To_Date">
                                <span class="text-danger" id="To_Date_error"></span>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="Remarks">Remarks</label>
                                <input type="text" class="form-control" id="Remarks" name="Remarks">
                                <span class="text-danger" id="Remarks_error"></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group d-flex gap-2" style="margin-top: 33px;">
                                <button type="button" class="btn btn-warning btn-sm" name="Employee_Leave_Apply" id="Employee_Leave_Apply">
                                    Apply
                                </button>&nbsp;
                                <a class="btn btn-secondary btn-sm" href="<?php echo base_url('Management/Employee') ?>">
                                    Back
                                </a>
                            </div>
                        </div>

                        <!-- <div class="col-md-1">
                            <div class="form-group">
                                <a class="btn btn-secondary btn-sm" style="margin-top: 33px;" href="<?php echo base_url('Management/Employee') ?>">Back</a>
                            </div>
                        </div> -->


                    </div>

                </form>

                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">
                    </div>
                </div>

            </div>
            </form>






        </div>


        <!-- Include the script for handling work allocation -->
        <script src="<?php echo base_url('assets/Script/Management.js') ?>"></script>