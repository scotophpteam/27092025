<div class="main-container">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="pd-20 card-box height-100-p">

                        <div class="pd-5">
                            <h4 class="text-black h5 text-center">Employee Requirement Master</h4>
                        </div>

                        <div class="row py-5">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Department</label>
                                    <select class="custom-select2 form-control" name="Department" id="Department">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Sub Department</label>
                                    <select class="custom-select2 form-control" name="Sub_Department"
                                        id="Sub_Department">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Position</label>
                                    <select class="custom-select2 form-control" name="WorkArea" id="WorkArea">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Shift</label>
                                    <select class="custom-select2 form-control" name="Shift" id="Shift">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>No Of Employees</label>
                                    <input type="number" class="form-control" name="Employee_Count" id="Employee_Count">

                                </div>
                            </div>

                            <div class="col-md-1">
                                <div class="form-group">

                                    <button type="button" class="button btn-primary btn-sm" name="Standard_Update"
                                        id="Standard_Update" style="margin-top: 33px;">Add</button>
                                </div>
                            </div>
                        </div>


                        <div style="overflow-x:auto;">
                            <div class="row" id="Standard_Actual_Table_Final">
                                <table class="table table-hover" id="Standard_Actual_Table_Final" style="width: 100%; ">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Department</th>
                                            <th>Sub Department</th>
                                            <th>Position</th>
                                            <th>Shift</th>
                                            <th>Standard</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table rows will go here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="Edit_Standard_Pop_Model" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                                        <input type="text" class="form-control" name="Sub_Department"
                                            id="Model_Sub_Department">
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
                                        <input type="number" class="form-control" name="Employee_Count"
                                            id="Model_Employee_Count">
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


        <script src=" <?php echo base_url('assets/Script/Standard_Actual.js') ?>">
        </script>