<div class="main-container">

    <input type="hidden" value="Employee_Position_Mapping_Page" id="Page_Name">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="pd-20 card-box height-100-p">

                        <div class="pd-5">
                            <h4 class="text-black h5 text-center">Employee Position Grade Mapping</h4>
                        </div>

                        <div class="row py-5">
                              <div class="col-md-3">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select class="custom-select2 form-control" name="Sub_Department" id="Sub_Department">
                                        <!-- Options to be populated -->
                                    </select>
                                </div>
                            </div> &nbsp; &nbsp; &nbsp; &nbsp;

                             <div class="col-md-4">
                                <div class="form-group">
                                    <button type="button" class="button btn-success btn-sm" name="Position_Update_Button" id="Position_Update_Button" style="margin-top: 33px; display:none;">Update</button>
                                </div>
                            </div>
                        
</div>

                        


                        <!-- Report Table -->
                        <div class="row" id="Employee_Position_Mapping_Section" style="display: none; overflow-x:auto;">
                            <table class="table table-hover" id="Employee_Position_Mapping_Table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Positiom</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                 <tbody id="Employee_Position_Mapping_Table_Tbody"></tbody>
                                    <!-- Table body rows go here -->
                                </tbody>
                            </table>
                            
                        </div>
                        

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="Edit_Standard_Pop_Model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Standard Actual Detail Edit</h5>
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
                                        <label for="Department">Department</label>
                                    </div>
                                </div>
                                <input type="hidden" class="form-control" name="Standard_ID" id="Model_Standard_ID">

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="Department" id="Model_Department">
                                        <!-- Options will go here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sub Department Row -->
                            <div class="row py-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Model_Sub_Department">Sub Department</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="Sub_Department" id="Model_Sub_Department">
                                        <!-- Options will go here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Position Row -->
                            <div class="row py-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="WorkArea">Position</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="WorkArea" id="Model_WorkArea">
                                        <!-- Options will go here -->
                                    </div>
                                </div>
                            </div>

                            <!-- No of Employees Row -->
                            <div class="row py-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Employee_Count">No Of Employees</label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="number" class="form-control" name="Employee_Count" id="Model_Employee_Count">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" id="Final_Standard_Edit_Btn" class="btn btn-primary">Save changes</button>
                    </div>

                </div>
            </div>
        </div>

        <script src="<?php echo base_url('assets/Script/Master.js') ?>"></script>

