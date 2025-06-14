<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">


            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30 py-5">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Late & Extra Employee Report</h4>
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
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                </select>
                                <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex gap-2" style="margin-bottom: 28px;">
                            <button type="button" class="btn btn-warning btn-sm" id="Late_Employee_Report_Dwon_Btn">Download</button> &nbsp;
                            <button type="button" class="btn btn-info btn-sm" id="Late_Employee_Report_view">View</button>
                        </div>
                    </div>



                    <div id="Late_Extra_Employee_Section" class="container-fluid mt-3 py-5">
                        <table class="table nowrap table-responsive" id="Late_Extra_Employee_Table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Work Area</th>
                                    <th>Type</th>
                                    <th>Work</th>
                                    <th>Frame Type</th>
                                    <th>Frame</th>
                                    <th>Description</th>
                                    <th>Status</th>



                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>

                    </div>


            </div>

            </form>


        </div>


        <script src="<?php echo base_url('assets/Script/Report.js') ?>"></script>