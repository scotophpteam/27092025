<!-- <div class="pre-loader">
    <div class="pre-loader-box">
        <div class="loader-logo"></div>
        <div class='circle' id='circle'></div>
    </div>
</div> -->

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Bootstrap TouchSpin Start -->
            <div class="pd-20 card-box mb-30">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row py-3">



                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="custom-select2 form-control" name="Department" id="Department">
                                    <span class="text-danger"><?php echo form_error('Department'); ?></span>

                                </select>
                            </div>
                        </div>


                    </div>

                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-info btn-sm" id="Attendance_Sheet_Tables">View</button>
                        </div>
                    </div>
                </form>
            </div>


            <div class="card-box mb-30" id="Employee_Attendance_List_Grade">
                <div class="pd-20">
                    <h4 class="text-black h5 text-center">Attendance Sheet List</h4>
                </div>
                <div class="table-container">
                    <table class="table hover multiple-select-row data-table-export nowrap table-responsive" style="width:100%">
                        <thead>
                            <tr>
                                <th class="table-plus datatable-nosort">#</th>
                                <th>Joining Date</th>
                                <th>Employee Id</th>
                                <th>Employee Name</th>
                                <th>Working Months</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Employee Grade</th>
                            </tr>
                        </thead>
                        <tbody id="Attendance_Sheet_Table">

                        </tbody>
                    </table>
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-success btn-sm" id="updateBtn">Update</button>
                        </div>
                    </div>
                </div>


            </div>

            <script src="<?php echo base_url('assets/Script/Master.js') ?>"></script>
            <script src="<?php echo base_url('assets/Script/Grade_Master.js') ?>"></script>



            