<div class="main-container">


<style>
    #preloader {
  display: none; /* Hidden by default */
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8); /* semi-transparent white */
  z-index: 9999; /* on top of everything */
  text-align: center;
}

.spinner {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 40px;
  height: 40px;
  margin: -20px 0 0 -20px;
  border: 4px solid #ccc;
  border-top-color: #1d72b8;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Spinner animation */
@keyframes spin {
  to { transform: rotate(360deg); }
}

</style>

    <input type="hidden" value="Employee_Position_Report_Page" id="Page_Name">

    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <!-- Bootstrap TouchSpin Start -->

            <div class="row">
                <div class="col-md-12 mb-30">
                    <div class="pd-20 card-box height-100-p">

                        <div class="pd-5">
                            <h4 class="text-black h5 text-center">Employee Position Report</h4>
                        </div>

                        <div class="row py-5">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" class="form-control" name="From_Date" id="From_Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" class="form-control" name="To_Date" id="To_Date">
                                </div>
                            </div>
                            <!--
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Grade</label>
                                    <select class="custom-select2 form-control" name="Grade" id="Grade">
                                        <option value="All">All</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                    </select>
                                </div>
                            </div>
                            -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Employee Id</label>
                                    <select class="custom-select2 form-control" name="Employee_Id" id="Employee_Id">
                                        <!-- Options to be populated -->
                                    </select>
                                </div>
                            </div> &nbsp;&nbsp;&nbsp;

                            <div class="col-md-4">
                                <div class="form-group">
                                    <button type="button" class="button btn-primary btn-sm" name="Position_Report_View_Button" id="Position_Report_View_Button" style="margin-top: 33px;">View</button>
                                    <button type="button" class="button btn-warning btn-sm" name="Overal_Report_Down_Button" id="Overal_Report_Down_Button" style="margin-top: 33px; display:none">Overal Report</button>
                                    <button type="button" class="button btn-warning btn-sm" name="Short_Report_Down_Button" id="Short_Report_Down_Button" style="margin-top: 33px; display:none">SAP Upload Report</button>
                                </div>
                            </div>

                            
                        </div>


                        <!-- Report Table -->
                        <div class="row" id="Employee_Position_Report_Tables" style="display: none; overflow-x:auto;">
                            <table class="table table-hover" id="Employee_Position_Report_Table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>Total</th>
                                        <th>DAY-1</th>
                                        <th>DAY-2</th>
                                        <th>DAY-3</th>
                                        <th>DAY-4</th>
                                        <th>DAY-5</th>
                                        <th>DAY-6</th>
                                        <th>DAY-7</th>
                                        <th>DAY-8</th>
                                        <th>DAY-9</th>
                                        <th>DAY-10</th>
                                        <th>DAY-11</th>
                                        <th>DAY-12</th>
                                        <th>DAY-13</th>
                                        <th>DAY-14</th>
                                        <th>DAY-15</th>
                                        <th>DAY-16</th>
                                        <th>DAY-17</th>
                                        <th>DAY-18</th>
                                        <th>DAY-19</th>
                                        <th>DAY-20</th>
                                        <th>DAY-21</th>
                                        <th>DAY-22</th>
                                        <th>DAY-23</th>
                                        <th>DAY-24</th>
                                        <th>DAY-25</th>
                                        <th>DAY-26</th>
                                        <th>DAY-27</th>
                                        <th>DAY-28</th>
                                        <th>DAY-29</th>
                                        <th>DAY-30</th>
                                        <th>DAY-31</th>
                                    </tr>
                                </thead>
    <tbody id="Employee_Position_Report_Table_Tbody"></tbody>
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

        <script src="<?php echo base_url('assets/Script/Incentive.js') ?>"></script>

