<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-5 card-box mb-30">

                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Employee Work Assignment Report</h4>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-2 align-items-end">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" id="Date" name="Date" value="">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Shift</label>
                                <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    <span class="text-danger"><?php echo form_error('Shift'); ?></span>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Employee Division</label>
                                <select class="custom-select2 form-control" name="Sub_Section" id="Sub_Section">
                                    <span class="text-danger"><?php echo form_error('Sub_Section'); ?></span>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex gap-2" style="margin-bottom: 28px;">
                            <button type="button" class="btn btn-warning btn-sm" id="Work_Allocation_Report_Down_Btn">Download</button> &nbsp;
                            <button type="button" class="btn btn-info btn-sm" id="Work_Allocation_Report_View">View</button>
                        </div>
                    </div>




                    <div class="row justify-content-end">

                    </div>
                </form>



                <div class="table-container py-5" id="Work_Allocation_List_Container" class="container-fluid mt-3">
                    <div class="table-responsive">
                        <table class="table" id="Work_Allocation_List">
                            <thead style="background-color: #519352">
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee Id</th>
                                    <th>Employee Name</th>
                                    <th>Work Area</th>
                                    <th>Machine Id</th>
                                    <th>Frame Type</th>
                                    <th>Frame</th>
                                    <th>Work</th>

                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table rows here -->
                            </tbody>
                        </table>
                    </div>
                </div>



            </div>

            <script src="<?php echo base_url('assets/Script/Report.js') ?>"></script>