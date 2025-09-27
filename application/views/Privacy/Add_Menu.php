<div class="main-container">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="pd-20 card-box height-100-p">

                        <input type="hidden" id="Add_Menu_Section_Page" value="Add_Menu_Section_Page">

                        <div class=" pd-5">
                            <h4 class="text-black h5 text-center">User Permission Add Menu Master</h4>
                        </div>

                        <div class="row py-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Menu ID</label>
                                    <input type="text" class="form-control" id="Menu_ID" value="<?php echo $Get_New_Menu_ID;  ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Menu</label>
                                    <input type="text" class="form-control" id="Menu_Name">

                                </div>
                            </div>
    
                            <div class="col-md-1">
                                <div class="form-group">

                                    <button type="button" class="button btn-primary btn-sm" name="Insert_Menu_Btn"
                                        id="Insert_Menu_Btn" style="margin-top: 33px;">Save</button>
                                </div>
                            </div>



                        </div>
                        <div style="overflow-x:auto;">
                            <div class="row">
                                <table class="table table-hover" id="Overall_Menu_Table" style="width: 100%; overflow-x: auto;">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Menu ID</th>
                                            <th>Menu Name</th>
                                            
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $I = 1;
                                        foreach ($Get_Menus as $Get_Menus_Rows) { ?>
                                            <tr>
                                                <td><?php echo $I; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Menu_ID; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Menu; ?></td>
                                                <td>
                                                    <i class="fas fa-edit Menu-Edit-Btn action-icon edit-menu"
                                                        data-id="<?php echo $Get_Menus_Rows->Menu_ID; ?>"
                                                        data-menu="<?php echo $Get_Menus_Rows->Menu; ?>"
                                                        
                                                        style="color: #28a745; font-size: 20px; cursor: pointer;"
                                                        title="Edit">
                                                    </i>
                                                    &nbsp;&nbsp;
                                                    <i class="fas fa-trash Menu-Delete-Btn action-icon delete-menu"
                                                        data-id="<?php echo $Get_Menus_Rows->Menu_ID; ?>"
                                                        style="color: rgb(255, 0, 0); font-size: 20px; cursor: pointer;"
                                                        title="Delete">
                                                    </i>
                                                </td>

                                            </tr>
                                        <?php
                                            $I++;
                                        } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>



                    </div>
                </div>

            </div>


            <div class="modal fade" id="Edit_Menu_Model" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Edit Menu</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container">
                                <!-- Department Row -->
                                <div class="row py-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="Model_MenuID">Menu ID</label>
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control" name="Standard_ID" id="Model_Standard_ID">

                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="Model_MenuID" id="Model_MenuID" readonly>
                                            <!-- Options will go here -->

                                        </div>
                                    </div>
                                </div>

                                <!-- Sub Department Row -->
                                <div class="row py-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="Model_Menu">Menu</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="Model_Menu"
                                                id="Model_Menu">
                                            <!-- Options will go here -->

                                        </div>
                                    </div>
                                </div>

                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" id="Final_Menu_Edit_Btn" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <script src=" <?php echo base_url('assets/Script/Privacy.js') ?>"></script>