<div class="main-container">
    <div class="pd-ltr-20 xs-pd-5-5">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->
            <div class="card-box mb-30">
                <div class="pd-5">
                    <h4 class="text-black h5 text-center">Machine Mapping Section</h4>
                </div>
                <div class="row py-2">
                    <!-- <div class="col-md-12 d-flex justify-content-end p-3">
                        <div class="btn-group" role="group" aria-label="Basic outlined example">

                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#bulkupload">Bulk Upload</button>&nbsp;

                        </div>
                    </div>   -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sub Department</label>
                            <select class="custom-select2 form-control" name="Department" id="Department">
                                <span class="text-danger"><?php echo form_error('Department'); ?></span>

                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>WorkArea</label>
                            <select class="custom-select2 form-control" name="Work_Area" id="Work_Area">
                                <span class="text-danger"><?php echo form_error('Department'); ?></span>

                            </select>
                        </div>
                    </div>


                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Machine ID</label>
                            <select id="Machine_Id" class="selectpicker form-control" data-size="5"
                                data-style="btn-outline-warning" multiple data-actions-box="true"
                                data-selected-text-format="count">
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Machine Group Name</label>
                            <input class=" form-control" type="text" name="Machine_Group_Name" id="Machine_Group_Name">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Frame</label>
                            <input class=" form-control" type="text" name="Machine_Frame_Name" id="Machine_Frame_Name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">

                            <button type="button" class="button btn-primary btn-sm" name="Standard_Update"
                                id="Machine_Mapping_Update" style="margin-top: 33px;">Add</button>
                        </div>
                    </div>
                </div>


            </div>

        </div>

        <script src="<?php echo base_url('assets/Script/Master.js') ?>"></script>