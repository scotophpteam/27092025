   <style>
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.39);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.preloader-content {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.39);
    display:
        flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.preloader-content {
    text-align: center;
}

.preloader-content img {
    animation: spin 2s infinite linear;
}

.preloader-content p {
    font-size: 18px;
    font-weight: bold;
    color: rgb(105, 251, 0);
    /* Cotton plant theme color */
    margin-top: 10px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
   </style>
   <div class="main-container">
       <div class="pd-ltr-20 xs-pd-20-10">
           <div class="min-height-200px">
               <!-- Bootstrap TouchSpin Start -->
               <div class="pd-5 card-box mb-30">

                   <div class="pd-5">
                       <h4 class="text-black h5 text-center">Unit Work Allocation - Shift Closing Details </h4>
                   </div>

                   <form method="POST" enctype="multipart/form-data">
                       <div class="row py-2">

                           <div class="col-md-3">
                               <div class="form-group">
                                   <label>Date</label>
                                   <input type="Date" class="form-control" id="Date" name="Date" value="">
                               </div>
                           </div>


                       </div>

                       <div id="preloader" style="display: none; text-align: center; padding: 10px;">
                           <img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." width="60">

                       </div>

                       <!-- <div class="row justify-content-end">
                           <div class="col-auto">
                               <button type="button" class="btn btn-warning btn-sm"
                                   id="Work_Allocation_Report_Down_Btn">Download</button>

                           </div>
                       </div> -->
                   </form>



                   <div class="table-container py-5" id="Admin_List_Container">
                       <div class="table-responsive">
                           <table class="table" id="Admin_Unit_List">
                               <thead style="background-color: #519352">
                                   <tr>
                                       <th>S.No</th>
                                       <th>Ccode</th>
                                       <th>Lcode</th>
                                       <th>Sub Departement</th>
                                       <th>SHIFT - I</th>
                                       <th>SHIFT - II</th>
                                       <th>SHIFT - III</th>
                                       <th>SHIFT - IV</th>



                                   </tr>
                               </thead>
                               <tbody>
                                   <!-- Table rows here -->
                               </tbody>
                           </table>
                       </div>
                   </div>



               </div>

               <script src="<?php echo base_url('assets/Script/Admin.js') ?>"></script>