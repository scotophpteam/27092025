$(document).ready(function () {


  $(document).ajaxStart(function () {
    $("#preloader").fadeIn();
  });

  $(document).ajaxStop(function () {
    $("#preloader").fadeOut();
  });

  $(document).ajaxStart(function () {
    $("body").css("overflow", "hidden");
  });

  $(document).ajaxStop(function () {
    $("body").css("overflow", "auto");
  });

  var table = $("#Allocation_Table").DataTable({
    // DataTable configurations
    paging: false,
    lengthChange: false,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: true,
  });

  $("#Allocation_Table_Container").hide();

  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);


  $.ajax({
    url: baseurl + "Master/Supervisor_List",
    type: "POST",
    success: function (response) {
      var Response_Data = JSON.parse(response);
      var Supervisor_List = Response_Data.Supervisor_List;

      if (Supervisor_List.Status == "Error") {
        // swal({
        //   type: "warning",
        //   title: "Warning",
        //   text: Supervisor_List.Message,
        // });
      } else {
        var Supervisor = {};

        for (var i = 0; i < Supervisor_List.length; i++) {
          var DName = Supervisor_List[i];
          Supervisor[DName.EmpNo] = DName.FirstName;
        }

        $("#Supervisor_Name").empty();

        $.each(Supervisor, function (key, value) {
          $("#Supervisor_Name").append(
            $("<option></option>")
              .attr("value", key)
              .text(key + "  " + value)
          );
        });
      }
    },
  });

  $.ajax({
    url: baseurl + "Work/Shifts",
    type: "POST",
    success: function (response) {
      var responseData = JSON.parse(response);
      var Shifts = responseData.Shifts;
      var Shift = {};

      Shifts.forEach(function (DName) {
        Shift[DName.ShiftDesc] = DName.ShiftDesc;
      });

      $.each(Shift, function (index, value) {
        $("#Shift").append(
          $("<option></option>").attr("value", value).text(value)
        );
      });

      $("#Shift option:first").prop("selected", true);

      $.ajax({
        url: baseurl + "Work/Get_Sub_Section",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (reponse) {
          var Response_Data = JSON.parse(reponse);
          var Get_Sub_Section = Response_Data.Get_Sub_Section;

          var Get_Sub_Sections = {};

          for (var i = 0; i < Get_Sub_Section.length; i++) {
            var DName = Get_Sub_Section[i];
            Get_Sub_Sections[DName.Sub_Section] = DName.Sub_Section;
          }

          $("#Sub_Section").empty();

          $("#Sub_Section").append("<option value='All' selected>All</option>");

          $.each(Get_Sub_Sections, function (key, value) {
            $("#Sub_Section").append(
              $("<option></option>").attr("value", key).text(value)
            );
          });
        },
      });

      $.ajax({

        url: baseurl + "Work/Late_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {

          const Response_Data = JSON.parse(response);

          const Shift_Employee_List = Response_Data.Late_And_Extra_Employee_List;
          const User_Department = Response_Data.User_Department;
          const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

          if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
            const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
            $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
          }

          if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              let shiftLabel = "";
              if (item.Working_Type === "OT") {
                if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
              }

              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });




          } else {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });

          }

        },
      });




      $("#Assign_Type").on("change", function () {

        var Assign_Type = $("#Assign_Type").val();

        if (Assign_Type == 'EXTRA') {

          $("#Shift_Div").hide();
          $("#Sub_Section_Div").hide();


        } else {

          $("#Shift_Div").show();
          $("#Sub_Section_Div").show();

        }


      })

      $("#Allocation_Table tbody").on("change", ".Frame", function () {


        const $row = $(this).closest("tr");
        const department = $row.find(".Department").val();
        const workArea = $row.find(".WorkArea").val();
        const jobCardSelect = $row.find(".JobCardNo").val();
        const frameSelect = $row.find(".Frame").val();
        const machineSelect = $row.find(".Machine_Id");
        const description = $row.find(".Description");

        if (!frameSelect || frameSelect === ",") {
          machineSelect.prop("disabled", true).empty();
          description.prop("disabled", true).val("");
          $row.find(".Frame").val("").trigger("change");
          return;
        }

        if (frameSelect == "Machine Wise") {


          if (frameSelect.includes("Others")) {
            machineSelect.prop("disabled", false).val("");
            description.val(workArea);
          } else if (frameSelect.includes("NoWork")) {
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", false).val("");
          } else if (frameSelect.includes("Multiple Trainee")) {
            description.val("");
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", false).val("");
          } else if (frameSelect.includes("Machine")) {
            description.val("");
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", false).val("");
          } else {
            description.val("");
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", false).val("");
          }

          $.ajax({
            url: baseurl + "Work/Only_Machine_Id",
            method: "POST",
            data: {
              Department: department,
              WorkArea: workArea,
              jobCardSelect,
              frameSelect,
            },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Machines = Response_Data.Only_Machine_Id || [];
              machineSelect.empty();
              Machines.forEach((machine) => {
                machineSelect.append(
                  `<option value="${machine.Machine_Id}">${machine.Machine_Id}</option>`
                );
              });
            },
            error: function () {
              swal({
                type: "error",
                title: "Error",
                text: "Failed to fetch data for the selected Work Area.",
              });
              // machineSelect.empty();
              // resetFrameSelect($row);
            },
          });
        } else {


          if (frameSelect.includes("Others")) {
            machineSelect.prop("disabled", true).val("");
            description.val(workArea);
          } else if (frameSelect.includes("NoWork")) {
            machineSelect.prop("disabled", true).val("");
            description.prop("disabled", true).val("");
          } else if (frameSelect.includes("Multiple Trainee")) {
            description.val("");
            machineSelect.prop("disabled", true).val("");
            description.prop("disabled", true).val("");
          } else if (frameSelect.includes("Machine")) {
            description.val("");
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", true).val("");
          } else {
            description.val("");
            machineSelect.prop("disabled", false).val("");
            description.prop("disabled", false).val("");
          }

          if (!$row.data("frameMachineMap")) {
            $row.data("frameMachineMap", {});
          }
          let frameMachineMap = $row.data("frameMachineMap");

          if (
            department == "FINISHING - PM1" ||
            department == "FINISHING - PM2" ||
            department == "Finishing - PM1" ||
            department == "Finishing - PM2" ||
            department == "Preparatory"
          ) {
            machineSelect.empty();

            // resetFrameSelect($row);

            $.ajax({
              url: baseurl + "Work/Only_Machine_Id",
              method: "POST",
              data: {
                Department: department,
                WorkArea: workArea,
                jobCardSelect,
                frameSelect,
              },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Machines = Response_Data.Only_Machine_Id || [];
                machineSelect.empty();
                Machines.forEach((machine) => {
                  machineSelect.append(
                    `<option value="${machine.Machine_Id}">${machine.Machine_Id}</option>`
                  );
                });
              },
              error: function () {
                swal({
                  type: "error",
                  title: "Error",
                  text: "Failed to fetch data for the selected Work Area.",
                });
                machineSelect.empty();
                resetFrameSelect($row);
              },
            });
          } else {
            $.ajax({
              url: baseurl + "Work/Machine_Ids",
              method: "POST",
              data: {
                Department: department,
                WorkArea: workArea,
                jobCardSelect,
                frameSelect,
              },
              success: function (response) {

                const Response_Data = JSON.parse(response);
                const Machines = Response_Data.Machine_Ids || [];

                Object.keys(frameMachineMap).forEach((frame) => {
                  if (!frameSelect.includes(frame)) {
                    delete frameMachineMap[frame];
                  }
                });

                frameSelect.forEach((frame) => {
                  frameMachineMap[frame] = Machines.map(
                    (machine) => machine.Machine_Id
                  );
                });

                let finalMachineIds = new Set();
                Object.values(frameMachineMap).forEach((machineList) => {
                  machineList.forEach((machineId) =>
                    finalMachineIds.add(machineId)
                  );
                });

                machineSelect.empty();
                finalMachineIds.forEach((machineId) => {
                  machineSelect.append(
                    `<option value="${machineId}" selected>${machineId}</option>`
                  );
                });

                $row.data("frameMachineMap", frameMachineMap);
              },
              error: function () {
                swal({
                  type: "error",
                  title: "Error",
                  text: "Failed to fetch data for the selected Work Area.",
                });
                machineSelect.empty();
              },
            });
          }
        }
      });

      function resetFrameSelect($row) {
        const frameSelect = $row.find(".Frame");
        const selectedValue = frameSelect.val();

        frameSelect.empty();
        frameSelect.append("<option value=''></option>");
        frameSelect.append("<option value='Others'>Others</option>");
        frameSelect.append(
          "<option value='Multiple Trainee'>Multiple Trainee</option>"
        );
        frameSelect.append("<option value='Trainee'>Trainee</option>");
        frameSelect.append("<option value='NoWork'>NoWork</option>");

        if (selectedValue) {
          frameSelect.val(selectedValue);
        }
      }

      $("#Allocation_Table tbody").on("change", ".Department", function () {
        const $row = $(this).closest("tr"); // Get the closest row
        const Department = $row.find(".Department").val(); // Get the department from the current row

        // Make the AJAX call to fetch work areas
        $.ajax({
          url: baseurl + "Work/Work_Areas",
          method: "POST",
          data: { Department: Department },
          success: function (response) {
            const Response_Data = JSON.parse(response);
            const Work_Areas = Response_Data.Work_Areas;

            // Clear and append work areas to the WorkArea dropdown of the current row only
            const workAreaSelect = $row.find(".WorkArea");
            workAreaSelect.empty(); // Clear the WorkArea dropdown for the current row

            $.each(Work_Areas, function (index, workArea) {
              workAreaSelect.append(
                `<option value="${workArea.WorkArea}" selected>${workArea.WorkArea}</option>`
              );
            });

            const WorkArea = workAreaSelect.val(); // Get the selected WorkArea value from the current row
            const JobCardNo = $row.find(".JobCardNo");
            const frameSelect = $row.find(".Frame");
            const Date = $("#Date").val();
            const Shift = $("#Shift").val();
            const description = $row.find(".Description");

            // Clear the JobCardNo and Frame dropdowns before adding new options
            JobCardNo.empty();
            frameSelect.val("");
            description.val("");

            // Append default options to the frameSelect
            frameSelect.append("<option value=''></option>");
            frameSelect.append("<option value='Others'>Others</option>");
            frameSelect.append(
              "<option value='Multiple Trainee'>Multiple Trainee</option>"
            );
            frameSelect.append("<option value='Trainee'>Trainee</option>");
            frameSelect.append("<option value='NoWork'>NoWork</option>");

            // First AJAX call to fetch JobCardNos
            $.ajax({
              url: baseurl + "Work/Job_Card_Nos",
              method: "POST",
              data: {
                Department,
                WorkArea,
                Date,
                Shift,
              },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Job_Card_Nos = Response_Data.Job_Card_Nos || [];

                // Append JobCardNos to the JobCardNo dropdown
                $.each(Job_Card_Nos, function (index, work) {
                  JobCardNo.append(
                    `<option value="${work.JobCard_No}">${work.JobCard_No}</option>`
                  );
                });

                // Second AJAX call to fetch Work Type and Frames
                $.ajax({
                  url: baseurl + "Work/Work_Type",
                  method: "POST",
                  data: {
                    Department,
                    WorkArea,
                    JobCardNo: JobCardNo.val(),
                    Date,
                    Shift,
                  },
                  success: function (response) {
                    const Response_Data = JSON.parse(response);
                    const Work_Type = Response_Data.Work_Type || [];

                    frameSelect.empty();
                    frameSelect.append("<option value=''></option>");

                    if (Work_Type.length === 0) {
                      frameSelect.append(
                        "<option value='Others'>Others</option>"
                      );
                      frameSelect.append(
                        "<option value='Multiple Trainee'>Multiple Trainee</option>"
                      );
                      frameSelect.append(
                        "<option value='Trainee'>Trainee</option>"
                      );
                      frameSelect.append(
                        "<option value='NoWork'>NoWork</option>"
                      );
                    } else {
                      $.each(Work_Type, function (index, work) {
                        if (work.Frame !== "-") {
                          frameSelect.append(
                            `<option value="${work.Frame}">${work.Frame}</option>`
                          );
                        }
                      });

                      const hasEmptyFrame = Work_Type.some(
                        (work) => work.Frame === "" || work.Machine !== ""
                      );

                      if (hasEmptyFrame) {
                        frameSelect.append("<option value=''></option>");
                        frameSelect.append(
                          "<option value='Machine Wise'>Machine Wise</option>"
                        );
                        frameSelect.append(
                          "<option value='Others'>Others</option>"
                        );
                        frameSelect.append(
                          "<option value='Multiple Trainee'>Multiple Trainee</option>"
                        );
                        frameSelect.append(
                          "<option value='Trainee'>Trainee</option>"
                        );
                        frameSelect.append(
                          "<option value='NoWork'>NoWork</option>"
                        );
                      }
                    }
                  },
                  error: function () {
                    swal({
                      type: "error",
                      title: "Error",
                      text: "Failed to fetch work types for the selected Work Area.",
                    });
                  },
                });
              },
              error: function () {
                swal({
                  type: "error",
                  title: "Error",
                  text: "Failed to fetch Job Card Nos for the selected Work Area.",
                });
              },
            });
          },
        });
      });

      $("#Allocation_Table tbody").on("change", ".WorkArea", function () {
        const $row = $(this).closest("tr");
        const Department = $row.find(".Department").val();
        const WorkArea = $(this).val();
        const JobCardNo = $row.find(".JobCardNo");
        const frameSelect = $row.find(".Frame");
        const Date = $("#Date").val();
        const Shift = $("#Shift").val();
        const description = $row.find(".Description");
        const Machine_Id = $row.find(".Machine_Id");

        // Clear the JobCardNo and Frame dropdowns before adding new options
        JobCardNo.empty();
        frameSelect.val("");
        description.val("");
        Machine_Id.empty();

        // Append default options to the frameSelect
        frameSelect.append("<option value=''></option>");
        frameSelect.append("<option value='Others'>Others</option>");
        frameSelect.append(
          "<option value='Multiple Trainee'>Multiple Trainee</option>"
        );
        frameSelect.append("<option value='Trainee'>Trainee</option>");
        frameSelect.append("<option value='NoWork'>NoWork</option>");

        // First AJAX call to fetch JobCardNos
        $.ajax({
          url: baseurl + "Work/Job_Card_Nos",
          method: "POST",
          data: {
            Department,
            WorkArea,
            Date,
            Shift,
          },
          success: function (response) {
            const Response_Data = JSON.parse(response);
            const Job_Card_Nos = Response_Data.Job_Card_Nos || [];

            // Append JobCardNos to the JobCardNo dropdown
            $.each(Job_Card_Nos, function (index, work) {
              JobCardNo.append(
                `<option value="${work.JobCard_No}">${work.JobCard_No}</option>`
              );
            });

            // Second AJAX call to fetch Work Type and Frames
            $.ajax({
              url: baseurl + "Work/Work_Type",
              method: "POST",
              data: {
                Department,
                WorkArea,
                JobCardNo: JobCardNo.val(),
                Date,
                Shift,
              },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Type = Response_Data.Work_Type || [];

                frameSelect.empty();
                frameSelect.append("<option value=''></option>");
                frameSelect.append("<option value='Others'>Others</option>");
                frameSelect.append(
                  "<option value='Multiple Trainee'>Multiple Trainee</option>"
                );
                frameSelect.append("<option value='Trainee'>Trainee</option>");
                frameSelect.append("<option value='NoWork'>NoWork</option>");

                if (Work_Type.length === 0) {
                  frameSelect.empty();
                  frameSelect.append("<option value=''></option>");
                  frameSelect.append(
                    "<option value='Others' selected>Others</option>"
                  );
                  frameSelect.append(
                    "<option value='Multiple Trainee'>Multiple Trainee</option>"
                  );
                  frameSelect.append(
                    "<option value='Trainee'>Trainee</option>"
                  );
                  frameSelect.append("<option value='NoWork'>NoWork</option>");
                  description.val(WorkArea);
                } else {
                  const uniqueFrames = [
                    ...new Set(Work_Type.map((work) => work.Frame)),
                  ];

                  uniqueFrames
                    .filter((frame) => frame !== "-")
                    .forEach((frame) => {
                      frameSelect.append(
                        `<option value="${frame}">${frame}</option>`
                      );
                    });

                  const hasEmptyFrame = Work_Type.some(
                    (work) => work.Frame === ""
                  );

                  if (hasEmptyFrame) {
                    frameSelect.empty();
                    frameSelect.append("<option value=''></option>");
                    frameSelect.append(
                      "<option value='Machine Wise'>Machine Wise</option>"
                    );
                    frameSelect.append(
                      "<option value='Others'>Others</option>"
                    );
                    frameSelect.append(
                      "<option value='Multiple Trainee'>Multiple Trainee</option>"
                    );
                    frameSelect.append(
                      "<option value='Trainee'>Trainee</option>"
                    );
                    frameSelect.append(
                      "<option value='NoWork'>NoWork</option>"
                    );
                  }
                }
              },
              error: function () {
                swal({
                  type: "error",
                  title: "Error",
                  text: "Failed to fetch work types for the selected Work Area.",
                });
              },
            });
          },
          error: function () {
            swal({
              type: "error",
              title: "Error",
              text: "Failed to fetch Job Card Nos for the selected Work Area.",
            });
          },
        });
      });

      $("#Allocation_Table tbody").on("click", ".Assign-btn", function () {

        var Assign_Type = $("#Assign_Type").val();

        if (Assign_Type == '' || Assign_Type == null) {

          alert('Please Select Assign Type...');

        }


        if (Assign_Type == 'EXTRA') {


          const $row = $(this).closest("tr");
          const Frames = $row.find(".Frame").val();
          const FrameType = $row.find(".FrameType").val();
          const Machine_Id = $row.find(".Machine_Id").val(); // Get the value of Machine_Id (not the jQuery object)
          const Department = $row.find(".Department").val();
          const JobCardNo = "";
          const WorkArea = $row.find(".WorkArea").val();
          const Date = $("#Date").val();
          const Shift = $("#Shift").val();
          const Description = $row.find(".Description").val();
          const EmployeeId = $row.find(".Employee_Id").text();
          const Allocation_Type = $("#Assign_Type").val();
          const Allocation_Screen_Type = $("#Allocation_Screen_Type").val();
          const Supervisor = $("#Supervisor_Name").val();

          var allocationData = {
            Date: Date,
            Department: Department,
            Shift: Shift,
            Work_Area: WorkArea,
            JobCardNo: JobCardNo,
            EmployeeId: EmployeeId,
            Machine_Id: Machine_Id, // Just the value
            FrameType: FrameType, // Just the value
            Frames: Frames, // Just the value
            Description: Description,
            Allocation_Type: Allocation_Type,
            Allocation_Screen_Type,
            Supervisor,
          };

          var Row_Data = {
            Allocations: [allocationData],
          };

          $.ajax({
            url: baseurl + "Work/Late_Extra_Save",
            method: "POST",
            data: JSON.stringify(Row_Data),
            Date,
            Shift,
            Allocation_Type,
            contentType: "application/json",
            success: function (response) {
              //  $("#Allocation_Table tbody").empty();
              // $("#Allocation_Table_Container").hide();

              var Sub_Section = $("#Sub_Section").val();

              if (Sub_Section == "All") {
                $.ajax({
                  url: baseurl + "Work/Late_Employee_List",
                  type: "POST",
                  data: {
                    Date: $("#Date").val(),
                    Shift: $("#Shift").val(),
                    Type: $("#Type").val(),
                  },
                  success: function (response) {

                    var options =
                      "<option value=''></option>" +
                      "<option value='LATE'>LATE</option>" +
                      "<option value='EXTRA'>EXTRA</option>";

                    $("#Assign_Type").empty();
                    $("#Assign_Type").append(options);

                    const Response_Data = JSON.parse(response);

                    const Shift_Employee_List =
                      Response_Data.Late_And_Extra_Employee_List;
                    const User_Department = Response_Data.User_Department;

                    const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

                    if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
                      const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
                      $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
                    }

                    if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        let shiftLabel = "";
                        if (item.Working_Type === "OT") {
                          if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                          else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                          else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
                        }

                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });




                    } else {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });

                    }

                  },
                });
              } else {
                $.ajax({
                  url: baseurl + "Work/Seperated_Sub_Section",
                  type: "POST",
                  data: {
                    Date: $("#Date").val(),
                    Shift: $("#Shift").val(),
                    Sub_Section: $("#Sub_Section").val(), // Corrected this line
                  },
                  success: function (response) {
                    const Response_Data = JSON.parse(response);

                    const Shift_Employee_List =
                      Response_Data.Late_And_Extra_Employee_List;
                    const User_Department = Response_Data.User_Department;

                    const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

                    if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
                      const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
                      $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
                    }

                    if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        let shiftLabel = "";
                        if (item.Working_Type === "OT") {
                          if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                          else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                          else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
                        }

                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });




                    } else {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });

                    }
                  },
                });
              }
            },
          });




        } else if (Assign_Type == 'LATE') {


          const $row = $(this).closest("tr");
          const Frames = $row.find(".Frame").val();
          const FrameType = $row.find(".FrameType").val();
          const Machine_Id = $row.find(".Machine_Id").val(); // Get the value of Machine_Id (not the jQuery object)
          const Department = $row.find(".Department").val();
          const JobCardNo = "";
          const WorkArea = $row.find(".WorkArea").val();
          const Date = $("#Date").val();
          const Shift = $("#Shift").val();
          const Description = $row.find(".Description").val();
          const EmployeeId = $row.find(".Employee_Id").text();
          const Allocation_Type = $("#Assign_Type").val();
          const Allocation_Screen_Type = $("#Allocation_Screen_Type").val();
          const Supervisor = $("#Supervisor_Name").val();

          var allocationData = {
            Date: Date,
            Department: Department,
            Shift: Shift,
            Work_Area: WorkArea,
            JobCardNo: JobCardNo,
            EmployeeId: EmployeeId,
            Machine_Id: Machine_Id, // Just the value
            FrameType: FrameType, // Just the value
            Frames: Frames, // Just the value
            Description: Description,
            Allocation_Type: Allocation_Type,
            Allocation_Screen_Type,
            Supervisor,
          };

          var Row_Data = {
            Allocations: [allocationData],
          };

          $.ajax({
            url: baseurl + "Work/Late_Extra_Save",
            method: "POST",
            data: JSON.stringify(Row_Data),
            Date,
            Shift,
            Allocation_Type,
            contentType: "application/json",
            success: function (response) {
              //  $("#Allocation_Table tbody").empty();
              // $("#Allocation_Table_Container").hide();

              var Sub_Section = $("#Sub_Section").val();

              if (Sub_Section == "All") {
                $.ajax({
                  url: baseurl + "Work/Late_Employee_List",
                  type: "POST",
                  data: {
                    Date: $("#Date").val(),
                    Shift: $("#Shift").val(),
                    Type: $("#Type").val(),
                  },
                  success: function (response) {

                    var options =
                      "<option value=''></option>" +
                      "<option value='LATE' SELECTED>LATE</option>" +
                      "<option value='EXTRA'>EXTRA</option>";

                    $("#Assign_Type").empty();
                    $("#Assign_Type").append(options);

                    const Response_Data = JSON.parse(response);

                    const Shift_Employee_List =
                      Response_Data.Late_And_Extra_Employee_List;
                    const User_Department = Response_Data.User_Department;

                    const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

                    if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
                      const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
                      $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
                    }

                    if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        let shiftLabel = "";
                        if (item.Working_Type === "OT") {
                          if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                          else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                          else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
                        }

                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });




                    } else {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });

                    }
                  },
                });
              } else {
                $.ajax({
                  url: baseurl + "Work/Seperated_Sub_Section",
                  type: "POST",
                  data: {
                    Date: $("#Date").val(),
                    Shift: $("#Shift").val(),
                    Sub_Section: $("#Sub_Section").val(), // Corrected this line
                  },
                  success: function (response) {
                    const Response_Data = JSON.parse(response);

                    const Shift_Employee_List =
                      Response_Data.Late_And_Extra_Employee_List;
                    const User_Department = Response_Data.User_Department;

                    const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

                    if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
                      const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
                      $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
                    }

                    if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        let shiftLabel = "";
                        if (item.Working_Type === "OT") {
                          if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                          else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                          else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
                        }

                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });




                    } else {


                      if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: 'Employee Details Not Found Please Check It...!',
                        });
                        $("#Allocation_Table tbody").empty();
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      const uniqueEmpMap = new Map();
                      Shift_Employee_List.forEach((item) => {
                        if (item.Work_Status == 1) {
                          if (!uniqueEmpMap.has(item.EmpNo)) {
                            uniqueEmpMap.set(item.EmpNo, { ...item });
                          } else {
                            const existing = uniqueEmpMap.get(item.EmpNo);
                            if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                              existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                            }
                          }
                        }
                      });

                      const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

                      $("#Allocation_Table tbody").empty();
                      table.clear().draw();

                      if (filteredShiftEmployeeList.length === 0) {
                        swal({
                          type: "warning",
                          title: "Warning",
                          text: "Shift Not Starting Details Not Found!",
                        });
                        $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
                        return;
                      }

                      $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

                      let continuousIndex = 1;
                      const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

                      filteredShiftEmployeeList.forEach((item) => {
                        const departmentOptions = uniqueDepartments
                          .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                          .join("");

                        const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

                        const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                          ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                          : "";

                        const frameOptions = item.Assign_Status == 1
                          ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                          : "";

                        const rowBackgroundColor =
                          item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                            item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                            ? "background-color: #A7FEA5;"
                            : item.Status_Updated === "NoWork"
                              ? "background-color: #FFE992;"
                              : item.Status_Updated === "Closed"
                                ? "background-color: rgb(250, 126, 126);"
                                : "";

                        const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
                        const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

                        const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

                        $("#Allocation_Table tbody").append(row);
                        table.row.add($(row)).draw();
                        continuousIndex++;
                      });

                      $.ajax({
                        url: baseurl + "Work/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (_, workArea) {
                              if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                                workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                              }
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const frameSelect = $row.find(".Frame");

                            let options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            const addedFrames = new Set();

                            $.each(Work_Type, function (_, work) {
                              if (work.Frame && !addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame);
                              }
                            });

                            frameSelect.append(options);
                          },
                        });
                      });

                      $("#Allocation_Table tbody .custom-select2").select2({
                        placeholder: "",
                        allowClear: true,
                        width: "150px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container",
                      });

                    }
                  },
                });
              }
            },
          });




        }




      });
    },
  });


  $("#Date").on("change", function () {


    $.ajax({
      url: baseurl + "Work/Get_Sub_Section",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (reponse) {
        var Response_Data = JSON.parse(reponse);
        var Get_Sub_Section = Response_Data.Get_Sub_Section;

        var Get_Sub_Sections = {};

        for (var i = 0; i < Get_Sub_Section.length; i++) {
          var DName = Get_Sub_Section[i];
          Get_Sub_Sections[DName.Sub_Section] = DName.Sub_Section;
        }

        $("#Sub_Section").empty();

        $("#Sub_Section").append("<option value='All' selected>All</option>");

        $.each(Get_Sub_Sections, function (key, value) {
          $("#Sub_Section").append(
            $("<option></option>").attr("value", key).text(value)
          );
        });
      },
    });

    $.ajax({
      url: baseurl + "Work/Late_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (response) {
        const Response_Data = JSON.parse(response);

        const Shift_Employee_List = Response_Data.Late_And_Extra_Employee_List;
        const User_Department = Response_Data.User_Department;

        const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

        if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
          const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
          $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
        }

        if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


          if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: 'Employee Details Not Found Please Check It...!',
            });
            $("#Allocation_Table tbody").empty();
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          const uniqueEmpMap = new Map();
          Shift_Employee_List.forEach((item) => {
            if (item.Work_Status == 1) {
              if (!uniqueEmpMap.has(item.EmpNo)) {
                uniqueEmpMap.set(item.EmpNo, { ...item });
              } else {
                const existing = uniqueEmpMap.get(item.EmpNo);
                if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                  existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                }
              }
            }
          });

          const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

          $("#Allocation_Table tbody").empty();
          table.clear().draw();

          if (filteredShiftEmployeeList.length === 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Shift Not Starting Details Not Found!",
            });
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

          let continuousIndex = 1;
          const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

          filteredShiftEmployeeList.forEach((item) => {
            let shiftLabel = "";
            if (item.Working_Type === "OT") {
              if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
              else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
              else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
            }

            const departmentOptions = uniqueDepartments
              .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
              .join("");

            const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

            const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
              ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
              : "";

            const frameOptions = item.Assign_Status == 1
              ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
              : "";

            const rowBackgroundColor =
              item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                ? "background-color: #A7FEA5;"
                : item.Status_Updated === "NoWork"
                  ? "background-color: #FFE992;"
                  : item.Status_Updated === "Closed"
                    ? "background-color: rgb(250, 126, 126);"
                    : "";

            const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
            const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

            const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

            $("#Allocation_Table tbody").append(row);
            table.row.add($(row)).draw();
            continuousIndex++;
          });

          $.ajax({
            url: baseurl + "Work/Work_Areas",
            method: "POST",
            data: { Department: $(".Department").val() },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Work_Areas = Response_Data.Work_Areas;

              $("#Allocation_Table tbody tr").each(function () {
                const workAreaSelect = $(this).find(".WorkArea");
                $.each(Work_Areas, function (_, workArea) {
                  if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                    workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                  }
                });
              });
            },
          });

          $("#Allocation_Table tbody tr").each(function () {
            const $row = $(this);
            const Department = $row.find(".Department").val();
            const WorkArea = $row.find(".WorkArea").val();
            const JobCardNo = "";
            const Date = $("#Date").val();
            const Shift = $("#Shift").val();

            $.ajax({
              url: baseurl + "Work/Work_Type",
              method: "POST",
              data: { Department, WorkArea, JobCardNo, Date, Shift },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Type = Response_Data.Work_Type;
                const frameSelect = $row.find(".Frame");

                let options =
                  "<option value=''></option>" +
                  "<option value='Others'>Others</option>" +
                  "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                  "<option value='Trainee'>Trainee</option>" +
                  "<option value='NoWork'>NoWork</option>";

                const addedFrames = new Set();

                $.each(Work_Type, function (_, work) {
                  if (work.Frame && !addedFrames.has(work.Frame)) {
                    options += `<option value="${work.Frame}">${work.Frame}</option>`;
                    addedFrames.add(work.Frame);
                  }
                });

                frameSelect.append(options);
              },
            });
          });

          $("#Allocation_Table tbody .custom-select2").select2({
            placeholder: "",
            allowClear: true,
            width: "150px",
            dropdownCssClass: "custom-select2-dropdown",
            containerCssClass: "custom-select2-container",
          });




        } else {


          if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: 'Employee Details Not Found Please Check It...!',
            });
            $("#Allocation_Table tbody").empty();
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          const uniqueEmpMap = new Map();
          Shift_Employee_List.forEach((item) => {
            if (item.Work_Status == 1) {
              if (!uniqueEmpMap.has(item.EmpNo)) {
                uniqueEmpMap.set(item.EmpNo, { ...item });
              } else {
                const existing = uniqueEmpMap.get(item.EmpNo);
                if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                  existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                }
              }
            }
          });

          const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

          $("#Allocation_Table tbody").empty();
          table.clear().draw();

          if (filteredShiftEmployeeList.length === 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Shift Not Starting Details Not Found!",
            });
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

          let continuousIndex = 1;
          const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

          filteredShiftEmployeeList.forEach((item) => {
            const departmentOptions = uniqueDepartments
              .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
              .join("");

            const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

            const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
              ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
              : "";

            const frameOptions = item.Assign_Status == 1
              ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
              : "";

            const rowBackgroundColor =
              item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                ? "background-color: #A7FEA5;"
                : item.Status_Updated === "NoWork"
                  ? "background-color: #FFE992;"
                  : item.Status_Updated === "Closed"
                    ? "background-color: rgb(250, 126, 126);"
                    : "";

            const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
            const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

            const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

            $("#Allocation_Table tbody").append(row);
            table.row.add($(row)).draw();
            continuousIndex++;
          });

          $.ajax({
            url: baseurl + "Work/Work_Areas",
            method: "POST",
            data: { Department: $(".Department").val() },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Work_Areas = Response_Data.Work_Areas;

              $("#Allocation_Table tbody tr").each(function () {
                const workAreaSelect = $(this).find(".WorkArea");
                $.each(Work_Areas, function (_, workArea) {
                  if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                    workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                  }
                });
              });
            },
          });

          $("#Allocation_Table tbody tr").each(function () {
            const $row = $(this);
            const Department = $row.find(".Department").val();
            const WorkArea = $row.find(".WorkArea").val();
            const JobCardNo = "";
            const Date = $("#Date").val();
            const Shift = $("#Shift").val();

            $.ajax({
              url: baseurl + "Work/Work_Type",
              method: "POST",
              data: { Department, WorkArea, JobCardNo, Date, Shift },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Type = Response_Data.Work_Type;
                const frameSelect = $row.find(".Frame");

                let options =
                  "<option value=''></option>" +
                  "<option value='Others'>Others</option>" +
                  "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                  "<option value='Trainee'>Trainee</option>" +
                  "<option value='NoWork'>NoWork</option>";

                const addedFrames = new Set();

                $.each(Work_Type, function (_, work) {
                  if (work.Frame && !addedFrames.has(work.Frame)) {
                    options += `<option value="${work.Frame}">${work.Frame}</option>`;
                    addedFrames.add(work.Frame);
                  }
                });

                frameSelect.append(options);
              },
            });
          });

          $("#Allocation_Table tbody .custom-select2").select2({
            placeholder: "",
            allowClear: true,
            width: "150px",
            dropdownCssClass: "custom-select2-dropdown",
            containerCssClass: "custom-select2-container",
          });

        }
      },
    });

  })


  $("#Shift").on("change", function () {




    $.ajax({
      url: baseurl + "Work/Get_Sub_Section",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (reponse) {
        var Response_Data = JSON.parse(reponse);
        var Get_Sub_Section = Response_Data.Get_Sub_Section;

        var Get_Sub_Sections = {};

        for (var i = 0; i < Get_Sub_Section.length; i++) {
          var DName = Get_Sub_Section[i];
          Get_Sub_Sections[DName.Sub_Section] = DName.Sub_Section;
        }

        $("#Sub_Section").empty();

        $("#Sub_Section").append("<option value='All' selected>All</option>");

        $.each(Get_Sub_Sections, function (key, value) {
          $("#Sub_Section").append(
            $("<option></option>").attr("value", key).text(value)
          );
        });
      },
    });




    $.ajax({
      url: baseurl + "Work/Late_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (response) {
        const Response_Data = JSON.parse(response);

        const Shift_Employee_List = Response_Data.Late_And_Extra_Employee_List;
        const User_Department = Response_Data.User_Department;

        const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

        if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
          const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
          $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
        }

        if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


          if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: 'Employee Details Not Found Please Check It...!',
            });
            $("#Allocation_Table tbody").empty();
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          const uniqueEmpMap = new Map();
          Shift_Employee_List.forEach((item) => {
            if (item.Work_Status == 1) {
              if (!uniqueEmpMap.has(item.EmpNo)) {
                uniqueEmpMap.set(item.EmpNo, { ...item });
              } else {
                const existing = uniqueEmpMap.get(item.EmpNo);
                if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                  existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                }
              }
            }
          });

          const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

          $("#Allocation_Table tbody").empty();
          table.clear().draw();

          if (filteredShiftEmployeeList.length === 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Shift Not Starting Details Not Found!",
            });
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

          let continuousIndex = 1;
          const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

          filteredShiftEmployeeList.forEach((item) => {
            let shiftLabel = "";
            if (item.Working_Type === "OT") {
              if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
              else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
              else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
            }

            const departmentOptions = uniqueDepartments
              .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
              .join("");

            const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

            const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
              ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
              : "";

            const frameOptions = item.Assign_Status == 1
              ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
              : "";

            const rowBackgroundColor =
              item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                ? "background-color: #A7FEA5;"
                : item.Status_Updated === "NoWork"
                  ? "background-color: #FFE992;"
                  : item.Status_Updated === "Closed"
                    ? "background-color: rgb(250, 126, 126);"
                    : "";

            const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
            const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

            const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

            $("#Allocation_Table tbody").append(row);
            table.row.add($(row)).draw();
            continuousIndex++;
          });

          $.ajax({
            url: baseurl + "Work/Work_Areas",
            method: "POST",
            data: { Department: $(".Department").val() },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Work_Areas = Response_Data.Work_Areas;

              $("#Allocation_Table tbody tr").each(function () {
                const workAreaSelect = $(this).find(".WorkArea");
                $.each(Work_Areas, function (_, workArea) {
                  if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                    workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                  }
                });
              });
            },
          });

          $("#Allocation_Table tbody tr").each(function () {
            const $row = $(this);
            const Department = $row.find(".Department").val();
            const WorkArea = $row.find(".WorkArea").val();
            const JobCardNo = "";
            const Date = $("#Date").val();
            const Shift = $("#Shift").val();

            $.ajax({
              url: baseurl + "Work/Work_Type",
              method: "POST",
              data: { Department, WorkArea, JobCardNo, Date, Shift },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Type = Response_Data.Work_Type;
                const frameSelect = $row.find(".Frame");

                let options =
                  "<option value=''></option>" +
                  "<option value='Others'>Others</option>" +
                  "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                  "<option value='Trainee'>Trainee</option>" +
                  "<option value='NoWork'>NoWork</option>";

                const addedFrames = new Set();

                $.each(Work_Type, function (_, work) {
                  if (work.Frame && !addedFrames.has(work.Frame)) {
                    options += `<option value="${work.Frame}">${work.Frame}</option>`;
                    addedFrames.add(work.Frame);
                  }
                });

                frameSelect.append(options);
              },
            });
          });

          $("#Allocation_Table tbody .custom-select2").select2({
            placeholder: "",
            allowClear: true,
            width: "150px",
            dropdownCssClass: "custom-select2-dropdown",
            containerCssClass: "custom-select2-container",
          });




        } else {


          if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: 'Employee Details Not Found Please Check It...!',
            });
            $("#Allocation_Table tbody").empty();
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          const uniqueEmpMap = new Map();
          Shift_Employee_List.forEach((item) => {
            if (item.Work_Status == 1) {
              if (!uniqueEmpMap.has(item.EmpNo)) {
                uniqueEmpMap.set(item.EmpNo, { ...item });
              } else {
                const existing = uniqueEmpMap.get(item.EmpNo);
                if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                  existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                }
              }
            }
          });

          const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

          $("#Allocation_Table tbody").empty();
          table.clear().draw();

          if (filteredShiftEmployeeList.length === 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Shift Not Starting Details Not Found!",
            });
            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
            return;
          }

          $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

          let continuousIndex = 1;
          const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

          filteredShiftEmployeeList.forEach((item) => {
            const departmentOptions = uniqueDepartments
              .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
              .join("");

            const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

            const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
              ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
              : "";

            const frameOptions = item.Assign_Status == 1
              ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
              : "";

            const rowBackgroundColor =
              item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                ? "background-color: #A7FEA5;"
                : item.Status_Updated === "NoWork"
                  ? "background-color: #FFE992;"
                  : item.Status_Updated === "Closed"
                    ? "background-color: rgb(250, 126, 126);"
                    : "";

            const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
            const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

            const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

            $("#Allocation_Table tbody").append(row);
            table.row.add($(row)).draw();
            continuousIndex++;
          });

          $.ajax({
            url: baseurl + "Work/Work_Areas",
            method: "POST",
            data: { Department: $(".Department").val() },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Work_Areas = Response_Data.Work_Areas;

              $("#Allocation_Table tbody tr").each(function () {
                const workAreaSelect = $(this).find(".WorkArea");
                $.each(Work_Areas, function (_, workArea) {
                  if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                    workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                  }
                });
              });
            },
          });

          $("#Allocation_Table tbody tr").each(function () {
            const $row = $(this);
            const Department = $row.find(".Department").val();
            const WorkArea = $row.find(".WorkArea").val();
            const JobCardNo = "";
            const Date = $("#Date").val();
            const Shift = $("#Shift").val();

            $.ajax({
              url: baseurl + "Work/Work_Type",
              method: "POST",
              data: { Department, WorkArea, JobCardNo, Date, Shift },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Type = Response_Data.Work_Type;
                const frameSelect = $row.find(".Frame");

                let options =
                  "<option value=''></option>" +
                  "<option value='Others'>Others</option>" +
                  "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                  "<option value='Trainee'>Trainee</option>" +
                  "<option value='NoWork'>NoWork</option>";

                const addedFrames = new Set();

                $.each(Work_Type, function (_, work) {
                  if (work.Frame && !addedFrames.has(work.Frame)) {
                    options += `<option value="${work.Frame}">${work.Frame}</option>`;
                    addedFrames.add(work.Frame);
                  }
                });

                frameSelect.append(options);
              },
            });
          });

          $("#Allocation_Table tbody .custom-select2").select2({
            placeholder: "",
            allowClear: true,
            width: "150px",
            dropdownCssClass: "custom-select2-dropdown",
            containerCssClass: "custom-select2-container",
          });

        }
      },
    });
  });


  $("#Sub_Section").on("change", function () {

    $("#Allocation_Table tbody").empty();
    $("#Allocation_Table_Container").hide();

    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {

      $.ajax({
        url: baseurl + "Work/Late_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {

          const Response_Data = JSON.parse(response);
          const Shift_Employee_List = Response_Data.Late_And_Extra_Employee_List;
          const User_Department = Response_Data.User_Department;

          const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

          if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
            const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
            $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
          }

          if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              let shiftLabel = "";
              if (item.Working_Type === "OT") {
                if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
              }

              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });




          } else {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });

          }
        },
      });



    } else {

      $.ajax({
        url: baseurl + "Work/Late_Seperated_Sub_Section",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Sub_Section: $("#Sub_Section").val(), // Corrected this line
        },
        success: function (response) {

          const Response_Data = JSON.parse(response);

          const Shift_Employee_List = Response_Data.Seperated_Sub_Section;
          const User_Department = Response_Data.User_Department;

          const Late_And_Extra_Employee_Count = Response_Data.Late_And_Extra_Employee_Count;

          if (Late_And_Extra_Employee_Count && Late_And_Extra_Employee_Count.Late_Comers !== undefined) {
            const lateComersCount = Late_And_Extra_Employee_Count.Late_Comers;
            $('#unAllocatedBtn').text('Late Punched Employee : ' + lateComersCount);
          }

          if (Response_Data.Location_Code == 'PRECOT - A' || Response_Data.Location_Code == 'PRECOT - C' || Response_Data.Location_Code == 'PRECOT - D') {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              let shiftLabel = "";
              if (item.Working_Type === "OT") {
                if (item.Previous_Shift === "SHIFT1") shiftLabel = "S1";
                else if (item.Previous_Shift === "SHIFT2") shiftLabel = "S2";
                else if (item.Previous_Shift === "SHIFT3") shiftLabel = "S3";
              }

              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="display: none;"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });




          } else {


            if (Shift_Employee_List.Status === "Error" || Shift_Employee_List == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: 'Employee Details Not Found Please Check It...!',
              });
              $("#Allocation_Table tbody").empty();
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            const uniqueEmpMap = new Map();
            Shift_Employee_List.forEach((item) => {
              if (item.Work_Status == 1) {
                if (!uniqueEmpMap.has(item.EmpNo)) {
                  uniqueEmpMap.set(item.EmpNo, { ...item });
                } else {
                  const existing = uniqueEmpMap.get(item.EmpNo);
                  if (Array.isArray(existing.Machine_Id) && Array.isArray(item.Machine_Id)) {
                    existing.Machine_Id = Array.from(new Set([...existing.Machine_Id, ...item.Machine_Id]));
                  }
                }
              }
            });

            const filteredShiftEmployeeList = Array.from(uniqueEmpMap.values());

            $("#Allocation_Table tbody").empty();
            table.clear().draw();

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });
              $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").hide();
              return;
            }

            $("#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details").show();

            let continuousIndex = 1;
            const uniqueDepartments = [...new Set(User_Department.map((dept) => dept.Sub_Department))];

            filteredShiftEmployeeList.forEach((item) => {
              const departmentOptions = uniqueDepartments
                .map((dept) => `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""}>${dept}</option>`)
                .join("");

              const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;

              const machineOptions = item.Assign_Status == 1 && Array.isArray(item.Machine_Id)
                ? item.Machine_Id.map((machine) => `<option value="${machine}" selected>${machine}</option>`).join("")
                : "";

              const frameOptions = item.Assign_Status == 1
                ? `<option value="${item.Frame}" selected>${item.Frame}</option>`
                : "";

              const rowBackgroundColor =
                item.Status_Updated === "Machine" || item.Status_Updated === "Others" ||
                  item.Status_Updated === "Multiple Trainee" || item.Status_Updated === "Trainee"
                  ? "background-color: #A7FEA5;"
                  : item.Status_Updated === "NoWork"
                    ? "background-color: #FFE992;"
                    : item.Status_Updated === "Closed"
                      ? "background-color: rgb(250, 126, 126);"
                      : "";

              const assignButtonVisibility = item.Assign_Status == 1 || item.Closing_Status == "1" ? "display: none;" : "display: inline;";
              const editButtonVisibility = item.Assign_Status == 1 && item.Closing_Status != "1" ? "display: inline;" : "display: none;";

              const row = `<tr>
      <td style="${rowBackgroundColor}">${continuousIndex}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
      <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo}">${item.EmpNo}</td>
      <td style="${rowBackgroundColor}">${item.FirstName}</td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px;">${frameOptions}</select></td>
      <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px;">${machineOptions}</select></td>
      <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ''}" style="width: 200px;"></td>
      <td>
        <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
        <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
      </td>
      <td style="${rowBackgroundColor}">${item.Type}</td>
    </tr>`;

              $("#Allocation_Table tbody").append(row);
              table.row.add($(row)).draw();
              continuousIndex++;
            });

            $.ajax({
              url: baseurl + "Work/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (_, workArea) {
                    if (!workAreaSelect.find(`option[value="${workArea.WorkArea}"]`).length) {
                      workAreaSelect.append(`<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`);
                    }
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = "";
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "Work/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const frameSelect = $row.find(".Frame");

                  let options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  const addedFrames = new Set();

                  $.each(Work_Type, function (_, work) {
                    if (work.Frame && !addedFrames.has(work.Frame)) {
                      options += `<option value="${work.Frame}">${work.Frame}</option>`;
                      addedFrames.add(work.Frame);
                    }
                  });

                  frameSelect.append(options);
                },
              });
            });

            $("#Allocation_Table tbody .custom-select2").select2({
              placeholder: "",
              allowClear: true,
              width: "150px",
              dropdownCssClass: "custom-select2-dropdown",
              containerCssClass: "custom-select2-container",
            });

          }
        },
      });

    }


  });






});
