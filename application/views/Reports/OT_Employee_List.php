
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">


            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30 py-5">
                <input type="hidden" id="OT_Employee_List" value="OT_Employee_List">

                <div class="pd-5">
                    <h4 class="text-black h5 text-center">OT Employee List</h4>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2 align-items-end">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" id="Date" name="Date" value="">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Sel_Shift">
                                </select>
                            </div>
                        </div>

                        <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                </select>
                                <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                            </div>
                        </div> -->

                        <div class="col-md-4 d-flex gap-2" style="margin-bottom: 28px;">
                            <button type="button" class="btn btn-info btn-sm" id="OT_Employee_Report_View">View</button>&nbsp;
                             <button type="button" class="btn btn-warning btn-sm" id="OT_Employee_Report_Down">Download</button> &nbsp;
                        </div>
                    </div>

                    <div id="OT_Employee_Report_Section" class="container-fluid mt-3">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="OT_Employee_Report_Table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Position</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Previous Shift</th>
                                        <th>Frame</th>
                                        <th>Machine ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data rows go here -->
                                </tbody>
                            </table>
                        </div>
                    </div>


            </div>

            </form>

        </div>


        <script src="<?php echo base_url('assets/Script/Report.js') ?>"></script>



