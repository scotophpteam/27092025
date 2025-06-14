<div class="main-container">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="pd-20 card-box height-100-p">

                        <input type="hidden" id="Add_Sub_Menu_Section_Page" value="Add_Sub_Menu_Section_Page">

                        <div class=" pd-5">
                            <h4 class="text-black h5 text-center">User Permission Add Sub Menu Master</h4>
                        </div>

                        <div class="row py-5">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Menu ID</label>
                                    <input type="text" class="form-control" id="SubMenu_ID" value="<?php echo $Get_New_SubMenu_ID;  ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Menu</label>
                                    <select name="Menu_Name" id="Menu_Name" class="form-control custom-select2"></select>


                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sub Menu</label>
                                    <input type="text" class="form-control" id="Sub_Menu">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">

                                    <button type="button" class="button btn-primary btn-sm" name="Insert_Sub_Menu_Btn"
                                        id="Insert_Sub_Menu_Btn" style="margin-top: 33px;">Save</button>
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
                                            <th>Sub Menu ID</th>
                                            <th>Sub Menu</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $I = 1;
                                        foreach ($Get_SubMenus as $Get_Menus_Rows) { ?>
                                            <tr>
                                                <td><?php echo $I; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Menu_ID; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Menu; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Sub_Menu_ID; ?></td>
                                                <td><?php echo $Get_Menus_Rows->Sub_Menu; ?></td>
                                                <td>
                                                    <i class="fas fa-edit Menu-Edit-Btn action-icon edit-menu"
                                                        data-id="<?php echo $Get_Menus_Rows->Menu_ID; ?>"
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
        </div>

        <script src=" <?php echo base_url('assets/Script/Privacy.js') ?>"></script>