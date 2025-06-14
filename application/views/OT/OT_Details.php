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


            <input type="hidden" id="Page_Name" value="OT_Details">
            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee OT Details</h4>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2">

                        <!-- Date Field -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Date">Date</label>
                                <input type="date" class="form-control" id="Date" name="Date" value="">
                            </div>
                        </div>

                        <!-- Shift Dropdown -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Shift">Shift</label>
                                <select class="form-control custom-select2" name="Shift" id="Shift">
                                    <!-- Add shift options dynamically here -->
                                </select>
                            </div>
                        </div>

                        <!-- View Button -->
                        <div class="col-md-4 d-flex align-items-end" style="margin-bottom: 28px;">
                            <button type="button" class="btn btn-info btn-sm" id="OT_Employee_View">View</button>
                        </div>

                    </div>


                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                    </div>


                </form>

                <div class="container-fluid mt-3 table-container" id="OT_Contiune_Details_Section" style="display:none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="OT_Contiune_Details_Table">
                            <thead style="background-color: #519352; color: white;">
                                <tr>
                                    <th>#</th>
                                    <th class="Employee_Id">Employee Id</th>
                                    <th class="Employee_Name">Employee Name</th>
                                    <th class="WorkArea">Status</th>
                                    <th>IN</th>
                                    <th>OUT</th>
                                    <th>Working Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic rows go here -->
                            </tbody>
                        </table>
                    </div>


                </div>

            </div>


            <script src="<?php echo base_url('assets/Script/OT.js') ?>"></script>