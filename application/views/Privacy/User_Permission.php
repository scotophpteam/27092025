<div class="main-container">


    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-100px">

            <input type="hidden" id="User_Persmission_Section_Page" value="User_Persmission_Section_Page">

            <div class="pd-5 card-box mb-30">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Select Location</label>
                                <select class="custom-select2 form-control" name="Location" id="Location">
                                    <option value=""></option>
                                    <option value="PRECOT - CO">PRECOT - CO</option>
                                    <option value="PRECOT - A">PRECOT - A</option>
                                    <option value="PRECOT - C">PRECOT - C</option>
                                    <option value="PRECOT - D">PRECOT - D</option>
                                    <option value="PRECOT - M">PRECOT - M</option>
                                    <option value="PRECOT - K">PRECOT - K</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Location'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Select User</label>
                                <select class="custom-select2 form-control" name="Users" id="Users">
                                    <option value=""></option>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Manager">Admin</option>
                                    <option value="HR">Supervisor</option>
                                     <option value="HRL">HRL</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Users'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Menu</label>
                                <select class="custom-select2 form-control" name="Menu" id="Menu">
                                    <option value=""></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('Menu'); ?></span>
                            </div>

                        </div>
                        <div class="col-auto" style="margin-top: 35px;">
                            <button type="button" class="btn btn-warning btn-sm" id="User_Right_Button">Update</button>
                        </div>

                        <div id="Privacy_Table_Section" style="width: 100%; overflow-x: auto; display:none" class="py-5">
                            <table class="table table-bordered nowrap" id="Privacy_Table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="table-plus datatable-nosort">#</th>
                                        <th>Sub Menus</th>
                                        <th>Screen</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                         <!-- <th>URL</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>




                        <!-- Add more cards below by copying a block -->

                    </div>
                </form>

                <!-- Preloader -->
                <div class="min-height-200px">
                    <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                        <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">
                    </div>
                </div>

            </div>

        </div>

        <script src="<?php echo base_url('assets/Script/Privacy.js') ?>"></script>