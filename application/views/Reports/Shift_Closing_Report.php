<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">


            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30 py-5">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Shift Closing Report</h4>
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
                            <button type="button" class="btn btn-warning btn-sm" id="Shift_Closing_Report_Down_Btn">Download</button>&nbsp;
                            <button type="button" class="btn btn-info btn-sm" id="Shift_Closing_Report_View">View</button>
                        </div>
                    </div>



                    <div id="Shift_Closing_List_Table_Container" class="container-fluid mt-3">
                        <table class="table table-responsive" id="Shift_Closing_List_Table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Sub Department</th>
                                    <th>Work Area</th>
                                    <th>Job Card No</th>
                                    <th>Closing Status</th>





                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>

                    </div>

            </div>

        </div>

        </form>




        <script src="<?php echo base_url('assets/Script/Report.js') ?>"></script>