$(document).ready(function () {

  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);



  var Page_Name = $("#Page_Name").val();

  if (Page_Name == 'OT_Details') {


    // $(document).ajaxStart(function () {
    //   $("#preloader").fadeIn();
    // });

    // $(document).ajaxStop(function () {
    //   $("#preloader").fadeOut();
    // });

    // $(document).ajaxStart(function () {
    //   $("body").css("overflow", "hidden");
    // });

    // $(document).ajaxStop(function () {
    //   $("body").css("overflow", "auto");
    // });


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

      }

    })

    $("#OT_Employee_View").on("click", function () {

      var Date = $("#Date").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + 'OT/Contiune_Employee_List',
        type: 'POST',
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Contiune_Employee_List = Response_Data.Contiune_Employee_List;

          if (Response_Data.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.Message,
            });

            $("#OT_Contiune_Details_Section").hide();
          } else {
            $("#OT_Contiune_Details_Section").show();

            var tableBody = $("#OT_Contiune_Details_Table tbody");
            tableBody.empty();

            Contiune_Employee_List.forEach(function (employee, index) {
              var badgeClass = employee.Closing_Status == 0 ? 'badge bg-success' : 'badge bg-warning';
              var badgeText = employee.Closing_Status == 0 ? 'Still Working' : 'OT Closed';

              var timeInBgColor = employee.TimeIN ? '#93FD95' : '#FD9393';
              var timeOutBgColor = employee.TimeOUT ? '#93FD95' : '#FD9393';
              var TotalWorkingHours = 'rgb(255, 210, 132)';

              var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${employee.EmpNo}</td>
                                    <td>${employee.FirstName}</td>
                                    <td><span class="${badgeClass}">${badgeText}</span></td>
                                    <td style="background-color: ${timeInBgColor}">${employee.TimeIN || ""}</td>
                                    <td style="background-color: ${timeOutBgColor}">${employee.TimeOUT || ""}</td>
                                    <td style="background-color: ${TotalWorkingHours}">${employee.Total_Working_Hours}</td>
                                </tr>
                            `;

              tableBody.append(row);
            });

            if ($.fn.DataTable.isDataTable('#OT_Contiune_Details_Table')) {
              $('#OT_Contiune_Details_Table tbody').DataTable().clear().destroy();
            }

            $('#OT_Contiune_Details_Table').DataTable({
              paging: false,
              searching: true,
              ordering: true,
              info: true,
            });
          }
        }




      })

    })





  } else if (Page_Name == 'Employee_Extra_Work_Allocation_Page') {

    // $(document).ajaxStart(function () {
    //   $("#preloader").fadeIn();
    // });

    // $(document).ajaxStop(function () {
    //   $("#preloader").fadeOut();
    // });

    // $(document).ajaxStart(function () {
    //   $("body").css("overflow", "hidden");
    // });

    // $(document).ajaxStop(function () {
    //   $("body").css("overflow", "auto");
    // });

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




    $.ajax({

      url: baseurl + "OT/Extra_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
      },
      success: function (response) {

        const Response_Data = JSON.parse(response);

        const Shift_Employee_List = Response_Data.Extra_Employee_List;
        const User_Department = Response_Data.User_Department;


        if (Shift_Employee_List.Status == "Error" || Shift_Employee_List == 0) {
          swal({
            type: "warning",
            title: "Warning",
            text: 'Extra Hours Employee Details Not Found!',
          });

          $("#Allocation_Table tbody").empty();
          $(
            "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
          ).hide();
        } else {


          const filteredShiftEmployeeList = Shift_Employee_List.filter(
            (item) => item.Work_Status == 1
          );

          $("#Allocation_Table tbody").empty();
          table.clear().draw(); // clears previous data

          if (filteredShiftEmployeeList.length === 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Shift Not Starting Details Not Found!",
            });

            $(
              "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
            ).hide();
          } else {
            $(
              "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
            ).show();

            let groupedByWages = {};
            let wageEmployeeCount = {};

            filteredShiftEmployeeList.forEach((item) => {
              const wage = item.Wages || "NULL";
              if (!groupedByWages[wage]) {
                groupedByWages[wage] = [];
                wageEmployeeCount[wage] = new Set();
              }
              groupedByWages[wage].push(item);
              wageEmployeeCount[wage].add(item.EmpNo);
            });

            const customOrder = [
              "PERMANENT WORKER",
              "CONTRACT WORKER",
              "OTHER WORKER",
              "OTHERS",
              "POOL",
              "ANCILLARY",
              "LOADING",
              "OSP",
              "A1",
              "A2",
              "A3",
              "SCHEME",
              "STAFF",
            ];

            let wageGroups = Object.keys(groupedByWages);

            wageGroups.sort((a, b) => {
              const indexA = customOrder.indexOf(a);
              const indexB = customOrder.indexOf(b);

              if (indexA === -1 && indexB === -1) {
                return a.localeCompare(b);
              } else if (indexA === -1) {
                return 1;
              } else if (indexB === -1) {
                return -1;
              }
              return indexA - indexB;
            });

            let continuousIndex = 1;

            wageGroups.forEach((wage) => {
              const employeeCount = wageEmployeeCount[wage].size;

              let groupedData = {};

              groupedByWages[wage].forEach((item) => {
                const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                if (!groupedData[key]) {
                  groupedData[key] = { ...item, Machine_Id: [], Frame: [] };
                }
                groupedData[key].Machine_Id.push(item.Machine_Id);
                groupedData[key].Frame.push(item.Frame);
              });

              const sortedEmployees = Object.values(groupedData).sort(
                (a, b) => {
                  const nameA = a.FirstName.toUpperCase();
                  const nameB = b.FirstName.toUpperCase();
                  return nameA.localeCompare(nameB);
                }
              );

              sortedEmployees.forEach((item, index) => {
                item.Frame = [...new Set(item.Frame)];

                const uniqueDepartments = [
                  ...new Set(
                    User_Department.map((dept) => dept.Sub_Department)
                  ),
                ];

                const departmentOptions = uniqueDepartments
                  .map(
                    (dept) =>
                      `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""
                      }>${dept}</option>`
                  )
                  .join("");

                const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;
                const jobCardOption = `<option value="${item.Job_Card_No}" selected>${item.Job_Card_No}</option>`;

                let machineOptions = "";
                let frameOptions = "";

                if (item.Assign_Status == 1) {
                  machineOptions = item.Machine_Id.map(
                    (machine) =>
                      `<option value="${machine}" selected>${machine}</option>`
                  ).join("");

                  frameOptions = item.Frame.map(
                    (frame) =>
                      `<option value="${frame}" selected>${frame}</option>`
                  ).join("");
                }

                const rowBackgroundColor =
                  item.Status_Updated === "Machine" ||
                    item.Status_Updated === "Others" ||
                    item.Status_Updated === "Multiple Trainee" ||
                    item.Status_Updated === "Trainee"
                    ? "background-color: #A7FEA5;"
                    : item.Status_Updated === "NoWork"
                      ? "background-color: #FFE992;"
                      : item.Status_Updated === "Closed"
                        ? "background-color: rgb(250, 126, 126);"
                        : "";

                const assignButtonVisibility =
                  item.Assign_Status == 1 || item.Closing_Status == "1"
                    ? "display: none;"
                    : "display: inline;";
                const editButtonVisibility =
                  item.Assign_Status == 1 && item.Closing_Status != "1"
                    ? "display: inline;"
                    : "display: none;";

                const row = `<tr>
                            <td style="${rowBackgroundColor}">${continuousIndex}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                            <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo
                  }">${item.EmpNo}</td>
                            <td style="${rowBackgroundColor}">${item.FirstName}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${machineOptions || ""
                  }</select></td>
                            <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ""
                  }" style="width: 200px; text-align: center;"></td>
                            <td>
                                <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
                                <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
                            </td>
                        </tr>`;

                $("#Allocation_Table tbody").append(row);
                continuousIndex++;
                table.row.add($(row)).draw();
              });
            });

            $.ajax({
              url: baseurl + "OT/Work_Areas",
              method: "POST",
              data: { Department: $(".Department").val() },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Work_Areas = Response_Data.Work_Areas;

                $("#Allocation_Table tbody tr").each(function () {
                  const workAreaSelect = $(this).find(".WorkArea");
                  $.each(Work_Areas, function (index, workArea) {
                    workAreaSelect.append(
                      `<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`
                    );
                  });
                });
              },
            });

            $("#Allocation_Table tbody tr").each(function () {
              const $row = $(this);
              const Department = $row.find(".Department").val();
              const WorkArea = $row.find(".WorkArea").val();
              const JobCardNo = $row.find(".JobCardNo").val();
              const Date = $("#Date").val();
              const Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "OT/Work_Type",
                method: "POST",
                data: { Department, WorkArea, JobCardNo, Date, Shift },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Type = Response_Data.Work_Type;
                  const machineSelect = $row.find(".Machine_Id");
                  const frameSelect = $row.find(".Frame");

                  var options =
                    "<option value=''></option>" +
                    "<option value='Others'>Others</option>" +
                    "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                    "<option value='Trainee'>Trainee</option>" +
                    "<option value='NoWork'>NoWork</option>";

                  var machineWiseAdded = false;
                  var addedFrames = new Set(); // To track unique frames

                  if (Work_Type.length > 0) {
                    $.each(Work_Type, function (index, work) {
                      if (work.Frame == "" && work.Machine_Id != "") {
                        if (!machineWiseAdded) {
                          options +=
                            "<option value='Machine Wise'>Machine Wise</option>";
                          machineWiseAdded = true;
                        }
                      } else {
                        // Add frame only if it's not already added (unique)
                        if (!addedFrames.has(work.Frame)) {
                          options += `<option value="${work.Frame}">${work.Frame}</option>`;
                          addedFrames.add(work.Frame); // Mark the frame as added
                        }
                      }
                    });
                    frameSelect.append(options);
                  } else {
                    frameSelect.append(options);
                  }
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
        }
      },
    });

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
          url: baseurl + "OT/Only_Machine_Id",
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
            url: baseurl + "OT/Only_Machine_Id",
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
            url: baseurl + "OT/Machine_Ids",
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
        url: baseurl + "OT/Work_Areas",
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
            url: baseurl + "OT/Job_Card_Nos",
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
                url: baseurl + "OT/Work_Type",
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
        url: baseurl + "OT/Job_Card_Nos",
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
            url: baseurl + "OT/Work_Type",
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







    $("#Date").on("change", function () {


      $.ajax({

        url: baseurl + "OT/Extra_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
        },
        success: function (response) {

          const Response_Data = JSON.parse(response);

          const Shift_Employee_List = Response_Data.Extra_Employee_List;
          const User_Department = Response_Data.User_Department;


          if (Shift_Employee_List.Status == "Error" || Shift_Employee_List == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: 'Extra Hours Employee Details Not Found!',
            });

            $("#Allocation_Table tbody").empty();
            $(
              "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
            ).hide();
          } else {


            const filteredShiftEmployeeList = Shift_Employee_List.filter(
              (item) => item.Work_Status == 1
            );

            $("#Allocation_Table tbody").empty();
            table.clear().draw(); // clears previous data

            if (filteredShiftEmployeeList.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Details Not Found!",
              });

              $(
                "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
              ).hide();
            } else {
              $(
                "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
              ).show();

              let groupedByWages = {};
              let wageEmployeeCount = {};

              filteredShiftEmployeeList.forEach((item) => {
                const wage = item.Wages || "NULL";
                if (!groupedByWages[wage]) {
                  groupedByWages[wage] = [];
                  wageEmployeeCount[wage] = new Set();
                }
                groupedByWages[wage].push(item);
                wageEmployeeCount[wage].add(item.EmpNo);
              });

              const customOrder = [
                "PERMANENT WORKER",
                "CONTRACT WORKER",
                "OTHER WORKER",
                "OTHERS",
                "POOL",
                "ANCILLARY",
                "LOADING",
                "OSP",
                "A1",
                "A2",
                "A3",
                "SCHEME",
                "STAFF",
              ];

              let wageGroups = Object.keys(groupedByWages);

              wageGroups.sort((a, b) => {
                const indexA = customOrder.indexOf(a);
                const indexB = customOrder.indexOf(b);

                if (indexA === -1 && indexB === -1) {
                  return a.localeCompare(b);
                } else if (indexA === -1) {
                  return 1;
                } else if (indexB === -1) {
                  return -1;
                }
                return indexA - indexB;
              });

              let continuousIndex = 1;

              wageGroups.forEach((wage) => {
                const employeeCount = wageEmployeeCount[wage].size;

                let groupedData = {};

                groupedByWages[wage].forEach((item) => {
                  const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                  if (!groupedData[key]) {
                    groupedData[key] = { ...item, Machine_Id: [], Frame: [] };
                  }
                  groupedData[key].Machine_Id.push(item.Machine_Id);
                  groupedData[key].Frame.push(item.Frame);
                });

                const sortedEmployees = Object.values(groupedData).sort(
                  (a, b) => {
                    const nameA = a.FirstName.toUpperCase();
                    const nameB = b.FirstName.toUpperCase();
                    return nameA.localeCompare(nameB);
                  }
                );

                sortedEmployees.forEach((item, index) => {
                  item.Frame = [...new Set(item.Frame)];

                  const uniqueDepartments = [
                    ...new Set(
                      User_Department.map((dept) => dept.Sub_Department)
                    ),
                  ];

                  const departmentOptions = uniqueDepartments
                    .map(
                      (dept) =>
                        `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""
                        }>${dept}</option>`
                    )
                    .join("");

                  const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;
                  const jobCardOption = `<option value="${item.Job_Card_No}" selected>${item.Job_Card_No}</option>`;

                  let machineOptions = "";
                  let frameOptions = "";

                  if (item.Assign_Status == 1) {
                    machineOptions = item.Machine_Id.map(
                      (machine) =>
                        `<option value="${machine}" selected>${machine}</option>`
                    ).join("");

                    frameOptions = item.Frame.map(
                      (frame) =>
                        `<option value="${frame}" selected>${frame}</option>`
                    ).join("");
                  }

                  const rowBackgroundColor =
                    item.Status_Updated === "Machine" ||
                      item.Status_Updated === "Others" ||
                      item.Status_Updated === "Multiple Trainee" ||
                      item.Status_Updated === "Trainee"
                      ? "background-color: #A7FEA5;"
                      : item.Status_Updated === "NoWork"
                        ? "background-color: #FFE992;"
                        : item.Status_Updated === "Closed"
                          ? "background-color: rgb(250, 126, 126);"
                          : "";

                  const assignButtonVisibility =
                    item.Assign_Status == 1 || item.Closing_Status == "1"
                      ? "display: none;"
                      : "display: inline;";
                  const editButtonVisibility =
                    item.Assign_Status == 1 && item.Closing_Status != "1"
                      ? "display: inline;"
                      : "display: none;";

                  const row = `<tr>
                            <td style="${rowBackgroundColor}">${continuousIndex}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                            <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo
                    }">${item.EmpNo}</td>
                            <td style="${rowBackgroundColor}">${item.FirstName}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${machineOptions || ""
                    }</select></td>
                            <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ""
                    }" style="width: 200px; text-align: center;"></td>
                            <td>
                                <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
                                <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
                            </td>
                        </tr>`;

                  $("#Allocation_Table tbody").append(row);
                  continuousIndex++;
                  table.row.add($(row)).draw();
                });
              });

              $.ajax({
                url: baseurl + "OT/Work_Areas",
                method: "POST",
                data: { Department: $(".Department").val() },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Work_Areas = Response_Data.Work_Areas;

                  $("#Allocation_Table tbody tr").each(function () {
                    const workAreaSelect = $(this).find(".WorkArea");
                    $.each(Work_Areas, function (index, workArea) {
                      workAreaSelect.append(
                        `<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`
                      );
                    });
                  });
                },
              });

              $("#Allocation_Table tbody tr").each(function () {
                const $row = $(this);
                const Department = $row.find(".Department").val();
                const WorkArea = $row.find(".WorkArea").val();
                const JobCardNo = $row.find(".JobCardNo").val();
                const Date = $("#Date").val();
                const Shift = $("#Shift").val();

                $.ajax({
                  url: baseurl + "OT/Work_Type",
                  method: "POST",
                  data: { Department, WorkArea, JobCardNo, Date, Shift },
                  success: function (response) {
                    const Response_Data = JSON.parse(response);
                    const Work_Type = Response_Data.Work_Type;
                    const machineSelect = $row.find(".Machine_Id");
                    const frameSelect = $row.find(".Frame");

                    var options =
                      "<option value=''></option>" +
                      "<option value='Others'>Others</option>" +
                      "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                      "<option value='Trainee'>Trainee</option>" +
                      "<option value='NoWork'>NoWork</option>";

                    var machineWiseAdded = false;
                    var addedFrames = new Set(); // To track unique frames

                    if (Work_Type.length > 0) {
                      $.each(Work_Type, function (index, work) {
                        if (work.Frame == "" && work.Machine_Id != "") {
                          if (!machineWiseAdded) {
                            options +=
                              "<option value='Machine Wise'>Machine Wise</option>";
                            machineWiseAdded = true;
                          }
                        } else {
                          // Add frame only if it's not already added (unique)
                          if (!addedFrames.has(work.Frame)) {
                            options += `<option value="${work.Frame}">${work.Frame}</option>`;
                            addedFrames.add(work.Frame); // Mark the frame as added
                          }
                        }
                      });
                      frameSelect.append(options);
                    } else {
                      frameSelect.append(options);
                    }
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
          }
        },
      });

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
            url: baseurl + "OT/Only_Machine_Id",
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
              url: baseurl + "OT/Only_Machine_Id",
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
              url: baseurl + "OT/Machine_Ids",
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
          url: baseurl + "OT/Work_Areas",
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
              url: baseurl + "OT/Job_Card_Nos",
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
                  url: baseurl + "OT/Work_Type",
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
          url: baseurl + "OT/Job_Card_Nos",
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
              url: baseurl + "OT/Work_Type",
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






    })

    $("#Allocation_Table tbody").on("click", ".Edit-btn", function () {


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
      const Allocation_Type = $("#Type").val();
      const Allocation_Screen_Type = $("#Allocation_Screen_Type").val();

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
      };

      var Row_Data = {
        Allocations: [allocationData],
      };

      $.ajax({
        url: baseurl + "OT/Edit",
        method: "POST",
        data: JSON.stringify(Row_Data),
        Date,
        Shift,
        Allocation_Type,
        contentType: "application/json",
        success: function (response) {
          var Response_Data = JSON.parse(response);

          if (
            Response_Data &&
            Array.isArray(Response_Data.Edit) &&
            Response_Data.Edit.length > 0
          ) {
            var Edit = Response_Data.Edit[0];

            if (Edit.status === "error") {
              swal({
                type: "warning",
                title: "Warning",
                text: Edit.message,
              });
            } else {
              swal({
                type: "success",
                title: "Success",
                text: "Operation completed successfully!",
              });


              $.ajax({

                url: baseurl + "OT/Extra_Employee_List",
                type: "POST",
                data: {
                  Date: $("#Date").val(),
                },
                success: function (response) {

                  const Response_Data = JSON.parse(response);

                  const Shift_Employee_List = Response_Data.Extra_Employee_List;
                  const User_Department = Response_Data.User_Department;


                  if (Shift_Employee_List.Status == "Error" || Shift_Employee_List == 0) {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: 'Extra Hours Employee Details Not Found!',
                    });

                    $("#Allocation_Table tbody").empty();
                    $(
                      "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                    ).hide();
                  } else {


                    const filteredShiftEmployeeList = Shift_Employee_List.filter(
                      (item) => item.Work_Status == 1
                    );

                    $("#Allocation_Table tbody").empty();
                    table.clear().draw(); // clears previous data

                    if (filteredShiftEmployeeList.length === 0) {
                      swal({
                        type: "warning",
                        title: "Warning",
                        text: "Shift Not Starting Details Not Found!",
                      });

                      $(
                        "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                      ).hide();
                    } else {
                      $(
                        "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                      ).show();

                      let groupedByWages = {};
                      let wageEmployeeCount = {};

                      filteredShiftEmployeeList.forEach((item) => {
                        const wage = item.Wages || "NULL";
                        if (!groupedByWages[wage]) {
                          groupedByWages[wage] = [];
                          wageEmployeeCount[wage] = new Set();
                        }
                        groupedByWages[wage].push(item);
                        wageEmployeeCount[wage].add(item.EmpNo);
                      });

                      const customOrder = [
                        "PERMANENT WORKER",
                        "CONTRACT WORKER",
                        "OTHER WORKER",
                        "OTHERS",
                        "POOL",
                        "ANCILLARY",
                        "LOADING",
                        "OSP",
                        "A1",
                        "A2",
                        "A3",
                        "SCHEME",
                        "STAFF",
                      ];

                      let wageGroups = Object.keys(groupedByWages);

                      wageGroups.sort((a, b) => {
                        const indexA = customOrder.indexOf(a);
                        const indexB = customOrder.indexOf(b);

                        if (indexA === -1 && indexB === -1) {
                          return a.localeCompare(b);
                        } else if (indexA === -1) {
                          return 1;
                        } else if (indexB === -1) {
                          return -1;
                        }
                        return indexA - indexB;
                      });

                      let continuousIndex = 1;

                      wageGroups.forEach((wage) => {
                        const employeeCount = wageEmployeeCount[wage].size;

                        let groupedData = {};

                        groupedByWages[wage].forEach((item) => {
                          const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                          if (!groupedData[key]) {
                            groupedData[key] = { ...item, Machine_Id: [], Frame: [] };
                          }
                          groupedData[key].Machine_Id.push(item.Machine_Id);
                          groupedData[key].Frame.push(item.Frame);
                        });

                        const sortedEmployees = Object.values(groupedData).sort(
                          (a, b) => {
                            const nameA = a.FirstName.toUpperCase();
                            const nameB = b.FirstName.toUpperCase();
                            return nameA.localeCompare(nameB);
                          }
                        );

                        sortedEmployees.forEach((item, index) => {
                          item.Frame = [...new Set(item.Frame)];

                          const uniqueDepartments = [
                            ...new Set(
                              User_Department.map((dept) => dept.Sub_Department)
                            ),
                          ];

                          const departmentOptions = uniqueDepartments
                            .map(
                              (dept) =>
                                `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""
                                }>${dept}</option>`
                            )
                            .join("");

                          const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;
                          const jobCardOption = `<option value="${item.Job_Card_No}" selected>${item.Job_Card_No}</option>`;

                          let machineOptions = "";
                          let frameOptions = "";

                          if (item.Assign_Status == 1) {
                            machineOptions = item.Machine_Id.map(
                              (machine) =>
                                `<option value="${machine}" selected>${machine}</option>`
                            ).join("");

                            frameOptions = item.Frame.map(
                              (frame) =>
                                `<option value="${frame}" selected>${frame}</option>`
                            ).join("");
                          }

                          const rowBackgroundColor =
                            item.Status_Updated === "Machine" ||
                              item.Status_Updated === "Others" ||
                              item.Status_Updated === "Multiple Trainee" ||
                              item.Status_Updated === "Trainee"
                              ? "background-color: #A7FEA5;"
                              : item.Status_Updated === "NoWork"
                                ? "background-color: #FFE992;"
                                : item.Status_Updated === "Closed"
                                  ? "background-color: rgb(250, 126, 126);"
                                  : "";

                          const assignButtonVisibility =
                            item.Assign_Status == 1 || item.Closing_Status == "1"
                              ? "display: none;"
                              : "display: inline;";
                          const editButtonVisibility =
                            item.Assign_Status == 1 && item.Closing_Status != "1"
                              ? "display: inline;"
                              : "display: none;";

                          const row = `<tr>
                            <td style="${rowBackgroundColor}">${continuousIndex}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                            <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo
                            }">${item.EmpNo}</td>
                            <td style="${rowBackgroundColor}">${item.FirstName}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${machineOptions || ""
                            }</select></td>
                            <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ""
                            }" style="width: 200px; text-align: center;"></td>
                            <td>
                                <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
                                <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
                            </td>
                        </tr>`;

                          $("#Allocation_Table tbody").append(row);
                          continuousIndex++;
                          table.row.add($(row)).draw();
                        });
                      });

                      $.ajax({
                        url: baseurl + "OT/Work_Areas",
                        method: "POST",
                        data: { Department: $(".Department").val() },
                        success: function (response) {
                          const Response_Data = JSON.parse(response);
                          const Work_Areas = Response_Data.Work_Areas;

                          $("#Allocation_Table tbody tr").each(function () {
                            const workAreaSelect = $(this).find(".WorkArea");
                            $.each(Work_Areas, function (index, workArea) {
                              workAreaSelect.append(
                                `<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`
                              );
                            });
                          });
                        },
                      });

                      $("#Allocation_Table tbody tr").each(function () {
                        const $row = $(this);
                        const Department = $row.find(".Department").val();
                        const WorkArea = $row.find(".WorkArea").val();
                        const JobCardNo = $row.find(".JobCardNo").val();
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "OT/Work_Type",
                          method: "POST",
                          data: { Department, WorkArea, JobCardNo, Date, Shift },
                          success: function (response) {
                            const Response_Data = JSON.parse(response);
                            const Work_Type = Response_Data.Work_Type;
                            const machineSelect = $row.find(".Machine_Id");
                            const frameSelect = $row.find(".Frame");

                            var options =
                              "<option value=''></option>" +
                              "<option value='Others'>Others</option>" +
                              "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                              "<option value='Trainee'>Trainee</option>" +
                              "<option value='NoWork'>NoWork</option>";

                            var machineWiseAdded = false;
                            var addedFrames = new Set(); // To track unique frames

                            if (Work_Type.length > 0) {
                              $.each(Work_Type, function (index, work) {
                                if (work.Frame == "" && work.Machine_Id != "") {
                                  if (!machineWiseAdded) {
                                    options +=
                                      "<option value='Machine Wise'>Machine Wise</option>";
                                    machineWiseAdded = true;
                                  }
                                } else {
                                  // Add frame only if it's not already added (unique)
                                  if (!addedFrames.has(work.Frame)) {
                                    options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                    addedFrames.add(work.Frame); // Mark the frame as added
                                  }
                                }
                              });
                              frameSelect.append(options);
                            } else {
                              frameSelect.append(options);
                            }
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
                  }
                },
              });





            }
          } else {
            swal({
              type: "error",
              title: "Error",
              text: "Invalid response data received.",
            });
          }
        },
      });
    });



    $("#Allocation_Table tbody").on("click", ".Assign-btn", function () {

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
      };

      var Row_Data = {
        Allocations: [allocationData],
      };

      $.ajax({
        url: baseurl + "OT/Extra_Save",
        method: "POST",
        data: JSON.stringify(Row_Data),
        Date,
        Shift,
        Allocation_Type,
        contentType: "application/json",

        success: function (response) {

          const Response_Data = JSON.parse(response);

          $.ajax({

            url: baseurl + "OT/Extra_Employee_List",
            type: "POST",
            data: {
              Date: $("#Date").val(),
            },
            success: function (response) {

              const Response_Data = JSON.parse(response);

              const Shift_Employee_List = Response_Data.Extra_Employee_List;
              const User_Department = Response_Data.User_Department;


              if (Shift_Employee_List.Status == "Error" || Shift_Employee_List == 0) {
                swal({
                  type: "warning",
                  title: "Warning",
                  text: 'Extra Hours Employee Details Not Found!',
                });

                $("#Allocation_Table tbody").empty();
                $(
                  "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                ).hide();
              } else {


                const filteredShiftEmployeeList = Shift_Employee_List.filter(
                  (item) => item.Work_Status == 1
                );

                $("#Allocation_Table tbody").empty();
                table.clear().draw(); // clears previous data

                if (filteredShiftEmployeeList.length === 0) {
                  swal({
                    type: "warning",
                    title: "Warning",
                    text: "Shift Not Starting Details Not Found!",
                  });

                  $(
                    "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                  ).hide();
                } else {
                  $(
                    "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                  ).show();

                  let groupedByWages = {};
                  let wageEmployeeCount = {};

                  filteredShiftEmployeeList.forEach((item) => {
                    const wage = item.Wages || "NULL";
                    if (!groupedByWages[wage]) {
                      groupedByWages[wage] = [];
                      wageEmployeeCount[wage] = new Set();
                    }
                    groupedByWages[wage].push(item);
                    wageEmployeeCount[wage].add(item.EmpNo);
                  });

                  const customOrder = [
                    "PERMANENT WORKER",
                    "CONTRACT WORKER",
                    "OTHER WORKER",
                    "OTHERS",
                    "POOL",
                    "ANCILLARY",
                    "LOADING",
                    "OSP",
                    "A1",
                    "A2",
                    "A3",
                    "SCHEME",
                    "STAFF",
                  ];

                  let wageGroups = Object.keys(groupedByWages);

                  wageGroups.sort((a, b) => {
                    const indexA = customOrder.indexOf(a);
                    const indexB = customOrder.indexOf(b);

                    if (indexA === -1 && indexB === -1) {
                      return a.localeCompare(b);
                    } else if (indexA === -1) {
                      return 1;
                    } else if (indexB === -1) {
                      return -1;
                    }
                    return indexA - indexB;
                  });

                  let continuousIndex = 1;

                  wageGroups.forEach((wage) => {
                    const employeeCount = wageEmployeeCount[wage].size;

                    let groupedData = {};

                    groupedByWages[wage].forEach((item) => {
                      const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                      if (!groupedData[key]) {
                        groupedData[key] = { ...item, Machine_Id: [], Frame: [] };
                      }
                      groupedData[key].Machine_Id.push(item.Machine_Id);
                      groupedData[key].Frame.push(item.Frame);
                    });

                    const sortedEmployees = Object.values(groupedData).sort(
                      (a, b) => {
                        const nameA = a.FirstName.toUpperCase();
                        const nameB = b.FirstName.toUpperCase();
                        return nameA.localeCompare(nameB);
                      }
                    );

                    sortedEmployees.forEach((item, index) => {
                      item.Frame = [...new Set(item.Frame)];

                      const uniqueDepartments = [
                        ...new Set(
                          User_Department.map((dept) => dept.Sub_Department)
                        ),
                      ];

                      const departmentOptions = uniqueDepartments
                        .map(
                          (dept) =>
                            `<option value="${dept}" ${dept === item.Sub_Department ? "selected" : ""
                            }>${dept}</option>`
                        )
                        .join("");

                      const workAreaOption = `<option value="${item.WorkArea}" selected>${item.WorkArea}</option>`;
                      const jobCardOption = `<option value="${item.Job_Card_No}" selected>${item.Job_Card_No}</option>`;

                      let machineOptions = "";
                      let frameOptions = "";

                      if (item.Assign_Status == 1) {
                        machineOptions = item.Machine_Id.map(
                          (machine) =>
                            `<option value="${machine}" selected>${machine}</option>`
                        ).join("");

                        frameOptions = item.Frame.map(
                          (frame) =>
                            `<option value="${frame}" selected>${frame}</option>`
                        ).join("");
                      }

                      const rowBackgroundColor =
                        item.Status_Updated === "Machine" ||
                          item.Status_Updated === "Others" ||
                          item.Status_Updated === "Multiple Trainee" ||
                          item.Status_Updated === "Trainee"
                          ? "background-color: #A7FEA5;"
                          : item.Status_Updated === "NoWork"
                            ? "background-color: #FFE992;"
                            : item.Status_Updated === "Closed"
                              ? "background-color: rgb(250, 126, 126);"
                              : "";

                      const assignButtonVisibility =
                        item.Assign_Status == 1 || item.Closing_Status == "1"
                          ? "display: none;"
                          : "display: inline;";
                      const editButtonVisibility =
                        item.Assign_Status == 1 && item.Closing_Status != "1"
                          ? "display: inline;"
                          : "display: none;";

                      const row = `<tr>
                            <td style="${rowBackgroundColor}">${continuousIndex}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                            <td style="${rowBackgroundColor}" class="Employee_Id" value="${item.EmpNo
                        }">${item.EmpNo}</td>
                            <td style="${rowBackgroundColor}">${item.FirstName}</td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                            <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${machineOptions || ""
                        }</select></td>
                            <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${item.Description || ""
                        }" style="width: 200px; text-align: center;"></td>
                            <td>
                                <button type="button" class='button btn-info Assign-btn form-control-sm' id='Assign-btn${continuousIndex}' style="${assignButtonVisibility}">Assign</button>
                                <button type="button" class='button btn-warning Edit-btn form-control-sm' id='Edit-btn${continuousIndex}' style="${editButtonVisibility}">Edit</button>
                            </td>
                        </tr>`;

                      $("#Allocation_Table tbody").append(row);
                      continuousIndex++;
                      table.row.add($(row)).draw();
                    });
                  });

                  $.ajax({
                    url: baseurl + "OT/Work_Areas",
                    method: "POST",
                    data: { Department: $(".Department").val() },
                    success: function (response) {
                      const Response_Data = JSON.parse(response);
                      const Work_Areas = Response_Data.Work_Areas;

                      $("#Allocation_Table tbody tr").each(function () {
                        const workAreaSelect = $(this).find(".WorkArea");
                        $.each(Work_Areas, function (index, workArea) {
                          workAreaSelect.append(
                            `<option value="${workArea.WorkArea}">${workArea.WorkArea}</option>`
                          );
                        });
                      });
                    },
                  });

                  $("#Allocation_Table tbody tr").each(function () {
                    const $row = $(this);
                    const Department = $row.find(".Department").val();
                    const WorkArea = $row.find(".WorkArea").val();
                    const JobCardNo = $row.find(".JobCardNo").val();
                    const Date = $("#Date").val();
                    const Shift = $("#Shift").val();

                    $.ajax({
                      url: baseurl + "OT/Work_Type",
                      method: "POST",
                      data: { Department, WorkArea, JobCardNo, Date, Shift },
                      success: function (response) {
                        const Response_Data = JSON.parse(response);
                        const Work_Type = Response_Data.Work_Type;
                        const machineSelect = $row.find(".Machine_Id");
                        const frameSelect = $row.find(".Frame");

                        var options =
                          "<option value=''></option>" +
                          "<option value='Others'>Others</option>" +
                          "<option value='Multiple Trainee'>Multiple Trainee</option>" +
                          "<option value='Trainee'>Trainee</option>" +
                          "<option value='NoWork'>NoWork</option>";

                        var machineWiseAdded = false;
                        var addedFrames = new Set(); // To track unique frames

                        if (Work_Type.length > 0) {
                          $.each(Work_Type, function (index, work) {
                            if (work.Frame == "" && work.Machine_Id != "") {
                              if (!machineWiseAdded) {
                                options +=
                                  "<option value='Machine Wise'>Machine Wise</option>";
                                machineWiseAdded = true;
                              }
                            } else {
                              // Add frame only if it's not already added (unique)
                              if (!addedFrames.has(work.Frame)) {
                                options += `<option value="${work.Frame}">${work.Frame}</option>`;
                                addedFrames.add(work.Frame); // Mark the frame as added
                              }
                            }
                          });
                          frameSelect.append(options);
                        } else {
                          frameSelect.append(options);
                        }
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
              }
            },
          });




        },
      });
    });

  } else if (Page_Name == 'Employee_Extra_Shift_Closing_Page') {

    var table = $("#Shift_Employee_List").DataTable({
      paging: false,
      searching: true,
      ordering: true,
      info: false,
    });

    function reinitializeTable() {
      if ($.fn.dataTable.isDataTable("#Shift_Employee_List")) {
        table.destroy();
      }
      table = $("#Shift_Employee_List").DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
      });
    }


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
          $("#Supervisor_Name").append("<option value=''></option>");

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


    $("#Shift_Closing_container").hide();
    $("#Shift_Closing_Section").hide();


    var Shift = $("#Shift").val();

    $.ajax({
      url: baseurl + "OT/Allocation_List",
      type: "POST",
      data: { Date: $("#Date").val(), Shift },
      success: function (response) {
        var response_Data = JSON.parse(response);
        var Allocation_List = response_Data.Allocation_List;

        if (Allocation_List.status == "error" || Allocation_List == []) {
          swal({
            type: "warning",
            title: "Warning",
            text: Allocation_List.message,
          });

          table.clear();
          table.draw();
          $("#Shift_Closing_Section").hide();
        } else {
          $("#Shift_Closing_container").hide();
          $("#Shift_Closing_Section").show();
          table.clear();

          var processedEmpNos = new Set();
          var serialNumber = 1; // Start serial number from 1 for each new allocation

          $.each(Allocation_List, function (index, item) {
            if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
              processedEmpNos.add(item.EmpNo);

              var row = `
                                      <tr>
                                          <td>${serialNumber++}</td> <!-- Increment serial number -->
                                          <td>${item.Sub_Department}</td>
                                          <td>${item.WorkArea}</td>
                                          <td>${item.Job_Card_No}</td>
                                          <td data-EmployeeId="${item.EmpNo}">${item.EmpNo
                }</td>
                                          <td data-EmployeeName="${item.FirstName}">${item.FirstName
                }</td>
                                          <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${item.EmpNo
                }"></td>
                                      </tr>
                                  `;
              table.row.add($(row)[0]);
            }
          });

          table.draw();
        }
      },
    });


    $("#Shift_Employee_List_Update").on("click", function () {

      var Supervisor_Name = $("#Supervisor_Name").val();


      if (Supervisor_Name == "") {
        swal({
          type: "warning",
          title: "Warning",
          text: "Please Select Valid Supervisor Name...",
        });
      } else {
        var Date = $("#Date").val();
        var Shift = $("#Shift").val();
        var employeesData = [];
        var remarks = $("#remarks").val();

        $("#Shift_Employee_List tbody tr").each(function () {
          var $checkbox = $(this).find(".OTEmployeeCheckbox");
          if ($checkbox.prop("checked")) {
            var departmentName = $(this).children().eq(1).text().trim();
            var workArea = $(this).children().eq(2).text().trim();
            var jobCardNo = $(this).children().eq(3).text().trim();
            var Employee_Id = $checkbox.data("empno");
            var isChecked = 1;

            employeesData.push({
              Date: Date,
              Shift: Shift,
              EmployeeId: Employee_Id,
              OTConfirm: isChecked,
              Department: departmentName,
              Work_Area: workArea,
              JobCardNo: jobCardNo,
              Remark: remarks,
              Supervisor_Name,
            });
          }
        });

        if (employeesData.length > 0) {
          $.ajax({
            url: baseurl + "OT/Employee_Shift_Closings",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ Employees: employeesData }),
            success: function (response) {
              var responseData = JSON.parse(response);

              var Employee_Shift_Closings = responseData.Employee_Shift_Closings;
              if (Employee_Shift_Closings == 1) {
                swal({
                  type: "success",
                  title: "Success",
                  text: "Shift Closing Process has been Completed!",
                });

                $("#Supervisor_Name").empty();

                $.ajax({
                  url: baseurl + "Master/Supervisor_List",
                  type: "POST",
                  success: function (response) {
                    var Response_Data = JSON.parse(response);
                    var Supervisor_List = Response_Data.Supervisor_List;

                    if (Supervisor_List.Status == "Error") {
                      swal({
                        type: "warning",
                        title: "Warning",
                        text: Supervisor_List.Message,
                      });
                    } else {
                      var Supervisor = {};

                      for (var i = 0; i < Supervisor_List.length; i++) {
                        var DName = Supervisor_List[i];
                        Supervisor[DName.EmpNo + " " + DName.FirstName] =
                          DName.FirstName;
                      }

                      $("#Supervisor_Name")
                        .empty()
                        .append("<option value=''></option>");

                      $.each(Supervisor, function (key, value) {
                        $("#Supervisor_Name").append(
                          $("<option></option>")
                            .attr("value", key + "  " + value)
                            .text(key + "  " + value)
                        );
                      });
                    }
                  },
                });

                table.clear();
                table.draw();
                $("#Shift_Closing_Section").hide();
              }

              var Shift = $("#Shift").val();

              $.ajax({
                url: baseurl + "OT/Allocation_List",
                type: "POST",
                data: { Date: $("#Date").val(), Shift },
                success: function (response) {
                  var response_Data = JSON.parse(response);
                  var Allocation_List = response_Data.Allocation_List;

                  if (Allocation_List.status == "error" || Allocation_List == []) {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: Allocation_List.message,
                    });

                    table.clear();
                    table.draw();
                    $("#Shift_Closing_Section").hide();
                  } else {
                    $("#Shift_Closing_container").hide();
                    $("#Shift_Closing_Section").show();
                    table.clear();

                    var processedEmpNos = new Set();
                    var serialNumber = 1;

                    $.each(Allocation_List, function (index, item) {
                      if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
                        processedEmpNos.add(item.EmpNo);

                        var row = `
                                    <tr>
                                      <td>${serialNumber++}</td>
                                      <td>${item.Sub_Department}</td>
                                      <td>${item.WorkArea}</td>
                                      <td>${item.Job_Card_No}</td>
                                      <td data-EmployeeId="${item.EmpNo}">${item.EmpNo}</td>
                                      <td data-EmployeeName="${item.FirstName}">${item.FirstName}</td>
                                      <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${item.EmpNo}"></td>
                                    </tr>
                                  `;
                        table.row.add($(row)[0]);
                      }
                    });

                    table.draw();
                  }
                },
              });
            },
            error: function (xhr, status, error) {
              console.error("Error: ", status, error);
              swal({
                type: "warning",
                title: "Error!",
                text: "There was an issue with closing the shift. Please try again.",
              });
            },
          });
        }
      }
    });


    function updateConfirmedCount() {
      var confirmedCount = $(".OTEmployeeCheckbox:checked").length;
      $("#confirmedCount").text(confirmedCount);
    }

    $(document).on("change", ".OTEmployeeCheckbox", function () {
      updateConfirmedCount();
    });

    $(document).ready(function () {
      updateConfirmedCount();
    });


    $("#Date").on("change", function () {

      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "OT/Allocation_List",
        type: "POST",
        data: { Date: $("#Date").val(), Shift },
        success: function (response) {
          var response_Data = JSON.parse(response);
          var Allocation_List = response_Data.Allocation_List;

          if (Allocation_List.status == "error" || Allocation_List == []) {
            swal({
              type: "warning",
              title: "Warning",
              text: Allocation_List.message,
            });

            table.clear();
            table.draw();
            $("#Shift_Closing_Section").hide();
          } else {
            $("#Shift_Closing_container").hide();
            $("#Shift_Closing_Section").show();
            table.clear();

            var processedEmpNos = new Set();
            var serialNumber = 1;

            $.each(Allocation_List, function (index, item) {
              if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
                processedEmpNos.add(item.EmpNo);

                var row = `
                                    <tr>
                                      <td>${serialNumber++}</td>
                                      <td>${item.Sub_Department}</td>
                                      <td>${item.WorkArea}</td>
                                      <td>${item.Job_Card_No}</td>
                                      <td data-EmployeeId="${item.EmpNo}">${item.EmpNo}</td>
                                      <td data-EmployeeName="${item.FirstName}">${item.FirstName}</td>
                                      <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${item.EmpNo}"></td>
                                    </tr>
                                  `;
                table.row.add($(row)[0]);
              }
            });

            table.draw();
          }
        },
      });

    })


  } else if (Page_Name == 'Employee_OT_Extra_Hours_Page') {


    // $(document).ajaxStart(function () {
    //   $("#preloader").fadeIn();
    // });

    // $(document).ajaxStop(function () {
    //   $("#preloader").fadeOut();
    // });

    // $(document).ajaxStart(function () {
    //   $("body").css("overflow", "hidden");
    // });

    // $(document).ajaxStop(function () {
    //   $("body").css("overflow", "auto");
    // });


    // var currentDate = new Date();
    // currentDate.setDate(currentDate.getDate());
    // var previousDate = currentDate.toISOString().split("T")[0];
    // $("#Date").val(currentDate);
    // $("#Date").attr("max", currentDate);

    var currentDate = new Date().toISOString().split("T")[0];
    $("#Date").val(currentDate);
    $("#Date").attr("max", currentDate);


    $("#Supervisor_Name").empty();

    var Type = $('#Type').val();

    if (Type == 'EXTRA') {

      $("#Shift_Previous").hide();

    } else if (Type == 'OT') {
      $("#Shift_Previous").show();

    } else if (Type == 'NOWORK') {

      $.ajax({
        url: baseurl + "Work/Shifts",
        type: "POST",
        success: function (response) {

          var Response_Data = JSON.parse(response);
          var Shift_Details = Response_Data.Shifts;

          var Shift = { "": "" };

          for (var i = 0; i < Shift_Details.length; i++) {
            var DName = Shift_Details[i];
            Shift[DName.ShiftDesc] = DName.ShiftDesc;
          }

          $("#Shift").empty();
          $.each(Shift, function (index, value) {

            $("#Shift").append(
              $("<option></option>").attr("value", value).text(value)
            );
          });

          $("#Shift option:eq(1)").prop("selected", true);

          var Type = $('#Type').val();

          $.ajax({
            url: baseurl + 'OT/No_Work_Employees',
            type: 'POST',
            data: {
              Date: $("#Date").val(),
              Type,
              Shift: $("#Shift").val()
            },
            success: function (response) {

              var Response_Data = JSON.parse(response);

              var No_Work_Employees = Response_Data.No_Work_Employees;

              // check length of No_Work_Employees zero
              if (No_Work_Employees == 0) {
                swal({
                  type: "warning",
                  title: "Warning",
                  text: "No Work Employee Details Not Found!..",
                });

                $("#ON_Work_Employee_List_Update_Section").hide();

              } else {



                const tbody = $('#ON_Work_Employee_List tbody');
                tbody.empty();
                let allRows = '';
                No_Work_Employees.forEach((employee, index) => {

                  allRows += `
  <tr>
    <td>${index + 1}</td>
    <td>${employee.EmpNo}</td>
    <td>${employee.Employee_Name}</td>
    <td>${employee.Status}</td>
    <td>${employee.IN_Time}</td>
    <td>${employee.IN_OUT}</td>
    <td>
      <select class="custom-select2 form-control form-control-lg NoWork_Employee_Status" style="width: 100%; height: 35px;">
      <option value="Absent">Absent</option>
        
       </select>
    </td>
    <td><button type="button" class="button btn-warning btn-sm NoWork_Update">Update</button></td>
  </tr>
`;

                });
                tbody.append(allRows);
                $("#ON_Work_Employee_List_Update_Section").show();

              }
            }
          })



        }
      });


    }


    $("#Type").on("change", function () {

      var Type = $('#Type').val();

      if (Type == 'OT') {

        $("#OT_Extra_Hours_Employee_Update").hide();
        $("#OT_Extra_Hours_Employee_Download").hide();
        $("#OT_Extra_Hours_Employee_Update_Section").hide();
        $("#ON_Work_Employee_List_Update_Section").hide();

        $("#Shift_Previous").show();

        $.ajax({
          url: baseurl + "Work/Shifts",
          type: "POST",
          success: function (response) {

            var Response_Data = JSON.parse(response);
            var Shift_Details = Response_Data.Shifts;

            var Shift = { "": "" };

            for (var i = 0; i < Shift_Details.length; i++) {
              var DName = Shift_Details[i];
              Shift[DName.ShiftDesc] = DName.ShiftDesc;
            }

            $("#Shift").empty();
            $.each(Shift, function (index, value) {

              $("#Shift").append(
                $("<option></option>").attr("value", value).text(value)
              );
            });

            $("#Shift option:eq(1)").prop("selected", true);


          }
        });



      } else if (Type === 'EXTRA') {

        $("#OT_Hours_Employee_Update_Section").hide();

        $("#OT_Hours_Employee_Download").hide();
        $("#OT_Extra_Hours_Employee_Update_Section").hide();
        $("#OT_Extra_Hours_Employee_Update_Section").hide();
        $("#ON_Work_Employee_List_Update_Section").hide();

        $("#Shift_Previous").hide();


      } else if (Type === 'NOWORK') {

        $("#OT_Extra_Hours_Employee_Update").hide();
        $("#OT_Extra_Hours_Employee_Download").hide();
        $("#OT_Extra_Hours_Employee_Update_Section").hide();

        $("#Shift_Previous").show();

        $.ajax({
          url: baseurl + "Work/Shifts",
          type: "POST",
          success: function (response) {

            var Response_Data = JSON.parse(response);
            var Shift_Details = Response_Data.Shifts;

            var Shift = { "": "" };

            for (var i = 0; i < Shift_Details.length; i++) {
              var DName = Shift_Details[i];
              Shift[DName.ShiftDesc] = DName.ShiftDesc;
            }

            $("#Shift").empty();
            $.each(Shift, function (index, value) {

              $("#Shift").append(
                $("<option></option>").attr("value", value).text(value)
              );
            });

            $("#Shift option:eq(1)").prop("selected", true);


          }
        });





      }


    });



    $.ajax({
      url: baseurl + "Master/Supervisor_List",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);

        // Check for top-level status or error object
        if (Response_Data.Status === "Error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Response_Data.Message,
          });
          return;
        }

        var Supervisor_List = Response_Data.Supervisor_List;

        if (!Array.isArray(Supervisor_List)) {
          swal({
            type: "error",
            title: "Error",
            text: "Invalid data format received for supervisor list.",
          });
          return;
        }

        // Clear the dropdown
        $("#Supervisor_Name").empty().append("<option value=''></option>");

        // Populate dropdown
        Supervisor_List.forEach(function (item) {
          var displayText = item.EmpNo + " - " + item.FirstName;
          $("#Supervisor_Name").append(
            $("<option></option>")
              .attr("value", item.EmpNo) // or use item.FirstName if that's intended
              .text(displayText)
          );
        });
      },
      error: function (xhr, status, error) {
        swal({
          type: "error",
          title: "AJAX Error",
          text: "Failed to retrieve supervisor list: " + error,
        });
      },
    });





    $("#OT_Extra_Hours_Employee_View").on("click", function () {

      $(document).ajaxStart(function () {
        $("#preloader").fadeIn();
        $("body").css("overflow", "hidden");
      });

      $(document).ajaxStop(function () {
        $("#preloader").fadeOut();
        $("body").css("overflow", "auto");
      });

      const type = $("#Type").val();

      if (type === 'EXTRA') {

        const selectedDate = $("#Date").val();

        $.ajax({
          url: baseurl + "OT/Get_OT_Extra_Hours_List_Employee",
          type: "POST",
          data: {
            Date: selectedDate,
            Type: type
          },
          success: function (response) {
            const Response_Data = JSON.parse(response);
            const Employee_List_Extra_Hours = Response_Data.Get_OT_Extra_Hours_List_Employee;

            if (Employee_List_Extra_Hours == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Extra Work Employee Details Not Found!"
              });

              $("#OT_Extra_Hours_Employee_Update_Section").hide();
              $("#OT_Extra_Hours_Employee_Update").hide();
              $("#OT_Extra_Hours_Employee_Download").hide();
            } else {
              $("#OT_Extra_Hours_Employee_Update_Section").show();
              $("#OT_Extra_Hours_Employee_Download").show();
              $("#OT_Hours_Employee_Update_Section").hide();

              const tbody = $('#OT_Extra_Hours_Employee_List tbody');
              tbody.empty();

              let showUpdateButton = false;

              Employee_List_Extra_Hours.forEach((Employee, index) => {
                const diffInMinutes = parseInt(Employee.OUT_Updated_Diff, 10);
                const hours = Math.floor(diffInMinutes / 60);
                const minutes = diffInMinutes % 60;
                const diffFormatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                const diffCellStyle = hours >= 1 ? 'background-color: rgb(250, 126, 126)' : '';

                const inputDisabled = Employee.Entry_Status === "1" ? 'disabled' : '';

                if (Employee.Entry_Status === "0") {
                  showUpdateButton = true;
                }

                const row = `
          <tr>
            <td>${index + 1}</td>
            <td>${Employee.Employee_ID}</td>
            <td>${Employee.Employee_Name}</td>
            <td>${Employee.IN_Time}</td>
            <td>${Employee.OUT_Time}</td>
            <td>${Employee.Updated_Time}</td>
            <td style="${diffCellStyle}">${diffFormatted}</td>
            <td>${Employee.Extra_Hours}</td>
            <td>
              <input type="number" class="form-control Extra_Hours" value="${Employee.Extra_Hours}"
                     style="width: 100%; height: 35px;" ${inputDisabled}>
            </td>
          </tr>
        `;
                tbody.append(row);
              });

              if (showUpdateButton) {
                $("#OT_Extra_Hours_Employee_Update").show();
              } else {
                $("#OT_Extra_Hours_Employee_Update").hide();
              }
            }
          },
          error: function (xhr, status, error) {
            console.error("Error fetching data:", status, error);
          }
        });






      } else if (type === 'OT') {

        $("#OT_Extra_Hours_Employee_Update").hide();
        $("#OT_Extra_Hours_Employee_Download").hide();

        const selectedDate = $("#Date").val();
        const shift = $("#Shift").val();

        $.ajax({
          url: baseurl + 'OT/OT_Employee_Details',
          type: 'POST',
          data: {
            Date: selectedDate,
            Type: type,
            Shift: shift
          },
          success: function (response) {
            const Response_Data = JSON.parse(response);
            const OT_Employee_Details = Response_Data.OT_Employee_Details;

            if (!OT_Employee_Details || OT_Employee_Details.length === 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "Employee Details Not Found!.."
              });

              $("#OT_Hours_Employee_Download").hide();
              $("#OT_Extra_Hours_Employee_Update_Section").hide();
              $("#OT_Hours_Employee_Update_Section").hide();
            } else {
              $("#OT_Hours_Employee_Download").show();
              $("#OT_Hours_Employee_Update_Section").show();

              const table = $('#OT_Hours_Employee_List');
              const tbody = table.find('tbody');

              if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
              }

              tbody.empty();

              let highPriorityRows = '';
              let normalRows = '';

              OT_Employee_Details.forEach((employee, index) => {
                const showUpdateButton = employee.Closing_Status === 1 || employee.Closing_Status === "1";
                const isCritical = employee.OT_Closing_Diff === 1 || employee.OT_Closing_Diff === "1";

                const rowHTML = `
            <tr style="${isCritical ? 'background-color: rgb(250, 126, 126);' : ''}">
              <td>${index + 1}</td>
              <td>${employee.Employee_ID}</td>
              <td>${employee.Employee_Name}</td>
              <td>${employee.In_Time}</td>
              <td>${employee.Out_Time}</td>
              <td>${employee.E_Master_Closing}</td>
              <td>${employee.Total_Working_Hours}</td>
              <td>${employee.OT_Hour}</td>
              <td>
                <input type="hidden" class="Original_OT" value="${employee.OT_Hour}">
                <input type="number" class="form-control Extra_Hours" value="${employee.OT_Hour}" style="width: 100%; height: 35px;" ${!showUpdateButton ? 'disabled' : ''}>
              </td>
              <td>
                ${showUpdateButton
                    ? `<button type="button" name="OT_Hours_Update" class="button btn-warning btn-sm OT_Hours_Update">Update</button>`
                    : `<button type="button" class="button btn-primary btn-sm" disabled>Updated</button>`}
              </td>
            </tr>
          `;

                if (isCritical) {
                  highPriorityRows += rowHTML;
                } else {
                  normalRows += rowHTML;
                }
              });

              tbody.append(highPriorityRows + normalRows);

              table.DataTable({
                paging: false,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: true,
              });
            }
          },

          error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            swal({
              type: "warning",
              title: "Error",
              text: "Failed to fetch employee details."
            });
          }
        });




      } else if (type === 'NOWORK') {

        $.ajax({
          url: baseurl + 'OT/No_Work_Employees',
          type: 'POST',
          data: {
            Date: $("#Date").val(),
            Type: type,
            Shift: $("#Shift").val()
          },
          success: function (response) {

            var Response_Data = JSON.parse(response);

            var No_Work_Employees = Response_Data.No_Work_Employees;

            // check length of No_Work_Employees zero
            if (No_Work_Employees == 0) {
              swal({
                type: "warning",
                title: "Warning",
                text: "No Work Employee Details Not Found!..",
              });

              $("#ON_Work_Employee_List_Update_Section").hide();

            } else {



              const tbody = $('#ON_Work_Employee_List tbody');
              tbody.empty();
              let allRows = '';
              No_Work_Employees.forEach((employee, index) => {

                allRows += `
  <tr>
    <td>${index + 1}</td>
    <td>${employee.EmpNo}</td>
    <td>${employee.Employee_Name}</td>
    <td>${employee.Status}</td>
    <td>${employee.IN_Time}</td>
    <td>${employee.IN_OUT}</td>
    <td>
      <select class="custom-select2 form-control form-control-lg NoWork_Employee_Status" style="width: 100%; height: 35px;">
      <option value="Absent">Absent</option>
        
       </select>
    </td>
    <td><button type="button" class="button btn-warning btn-sm NoWork_Update">Update</button></td>
  </tr>
`;

              });
              tbody.append(allRows);
              $("#ON_Work_Employee_List_Update_Section").show();

            }
          }
        })
      }


    });

    $(document).on("click", ".NoWork_Update", function () {

      let isValid = true;
      $(".form-control").removeClass("input-error");
      $(".error-text").remove();

      function showError(selector, message) {
        $(selector).addClass("input-error");
        $(selector).after('<div class="error-text">' + message + "</div>");
        isValid = false;
      }

      const Supervisor = $("#Supervisor_Name").val();
      if (!Supervisor) showError("#Supervisor_Name", "Valid Supervisor is required.");
      if (!isValid) return;



      const row = $(this).closest("tr");
      const EmployeeID = row.find("td:eq(1)").text();
      const Employee_Name = row.find("td:eq(2)").text();
      const E_Master_Closing_Status = row.find("td:eq(3)").text();
      const IN_Time = row.find("td:eq(4)").text();
      const IN_OUT = row.find("td:eq(5)").text();
      const Attendance = row.find(".NoWork_Employee_Status").val();
      const Shift = $("#Shift").val();
      const Date = $("#Date").val();

      $.ajax({
        url: baseurl + 'OT/No_Work_Employee_Update',
        type: 'POST',
        data: {
          EmployeeID,
          Employee_Name,
          E_Master_Closing_Status,
          IN_Time,
          IN_OUT,
          Attendance,
          Supervisor,
          Shift,
          Date

        },
        success: function (response) {

          var Response_Data = JSON.parse(response);
          var No_Work_Employee_Update = Response_Data.No_Work_Employees_Update;

          if (No_Work_Employee_Update.status === 'success') {
            swal({
              type: "success",
              title: "Updated",
              text: No_Work_Employee_Update.message,
            });
          } else {
            swal({
              type: "warning",
              title: "Warning",
              text: No_Work_Employee_Update.message,
            });
          }


        }

      })





    });








    $(document).on("click", ".OT_Hours_Update", function () {

      let isValid = true;
      $(".form-control").removeClass("input-error");
      $(".error-text").remove();

      const showError = (selector, message) => {
        $(selector).addClass("input-error");
        $(selector).after(`<div class="error-text">${message}</div>`);
        isValid = false;
      };

      const Supervisor = $("#Supervisor_Name").val();
      if (!Supervisor) showError("#Supervisor_Name", "Valid Supervisor is required.");
      if (!isValid) return;

      const Date = $("#Date").val();
      const Shift = $("#Shift").val();
      const Type = $("#Type").val();

      const row = $(this).closest("tr");
      const empNo = row.find("td:eq(1)").text().trim();
      const employeeName = row.find("td:eq(2)").text().trim();
      const inTime = row.find("td:eq(3)").text().trim();
      const outTime = row.find("td:eq(4)").text().trim();
      const updatedTime = row.find("td:eq(5)").text().trim();
      const originalOT = parseFloat(row.find(".Original_OT").val().trim());
      const extraHoursInput = row.find(".Extra_Hours");
      const extraHours = parseFloat(extraHoursInput.val().trim());

      if (isNaN(extraHours)) {
        swal({
          type: "warning",
          title: "Error",
          text: "Please enter a valid number for Extra Hours before updating.",
        });
        return;
      }

      if (extraHours > originalOT) {
        swal({
          type: "warning",
          title: "Invalid Extra Hours",
          text: `Extra Hours (${extraHours}) cannot be more than Working Hours (${originalOT}).`
        });
        extraHoursInput.addClass("input-error");
        return;
      }

      // Optional: compute difference, or use backend logic instead
      const difference = (extraHours - originalOT).toFixed(2);

      $.ajax({
        url: baseurl + "OT/OT_Details_Entry",
        type: "POST",
        data: {
          EmpNo: empNo,
          EmployeeName: employeeName,
          InTime: inTime,
          InOut: outTime,
          Actual_WHours: originalOT,
          UpdatedTime: updatedTime,
          Difference: difference,
          Final_ExtraHours: extraHours,
          Type,
          Date,
          Shift,
          Supervisor
        },
        success: function (response) {
          try {
            const Response_Data = JSON.parse(response);
            const OT_Details_Entry = Response_Data.OT_Details_Entry;

            if (OT_Details_Entry.status == 'success') {

              swal({
                type: "success",
                title: "OK",
                text: OT_Details_Entry.message,
              });


              $.ajax({
                url: baseurl + 'OT/OT_Employee_Details',
                type: 'POST',
                data: {
                  Date: $("#Date").val(),
                  Type: $("#Type").val(),
                  Shift: $("#Shift").val(),
                },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const OT_Employee_Details = Response_Data.OT_Employee_Details;

                  if (!OT_Employee_Details || OT_Employee_Details.length === 0) {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: "Employee Details Not Found!.."
                    });

                    $("#OT_Hours_Employee_Download").hide();
                    $("#OT_Extra_Hours_Employee_Update_Section").hide();
                    $("#OT_Hours_Employee_Update_Section").hide();
                  } else {
                    $("#OT_Hours_Employee_Download").show();
                    $("#OT_Hours_Employee_Update_Section").show();

                    const tbody = $('#OT_Hours_Employee_List tbody');
                    tbody.empty();

                    let highPriorityRows = '';
                    let normalRows = '';

                    OT_Employee_Details.forEach((employee, index) => {
                      const showUpdateButton = employee.Closing_Status === 1 || employee.Closing_Status === "1"; // ✅ use Closing_Status
                      const isCritical = employee.OT_Closing_Diff === 1 || employee.OT_Closing_Diff === "1";

                      const rowHTML = `
    <tr style="${isCritical ? 'background-color: rgb(250, 126, 126);' : ''}">
      <td>${index + 1}</td>
      <td>${employee.Employee_ID}</td>
      <td>${employee.Employee_Name}</td>
      <td>${employee.In_Time}</td>
      <td>${employee.Out_Time}</td>
      <td>${employee.E_Master_Closing}</td>
      <td>${employee.Total_Working_Hours}</td>
      <td>${employee.OT_Hour}</td>

      <input type="hidden" class="Original_OT" value="${employee.OT_Hour}">
      <td>
        <input type="number" class="form-control Extra_Hours" value="${employee.OT_Hour}"
               style="width: 100%; height: 35px;" ${!showUpdateButton ? 'disabled' : ''}>
      </td>
      <td>
        ${showUpdateButton
                          ? `<button type="button" name="OT_Hours_Update" class="button btn-warning btn-sm OT_Hours_Update">
              Update
            </button>`
                          : `<button type="button" class="button btn-primary btn-sm" disabled>
              Updated
            </button>`}
      </td>
    </tr>
  `;

                      // Append the row
                      if (isCritical) {
                        highPriorityRows += rowHTML;
                      } else {
                        normalRows += rowHTML;
                      }
                    });

                    tbody.append(highPriorityRows + normalRows);

                  }
                },
                error: function (xhr, status, error) {
                  console.error("AJAX Error:", status, error);
                  swal({
                    type: "warning",
                    title: "Error",
                    text: "Failed to fetch employee details."
                  });
                }
              });








            } else {
              swal({
                icon: "warning",
                title: "Warning",
                text: OT_Details_Entry.message,
              });
            }
          } catch (e) {
            swal({
              type: "warning",
              title: "Error",
              text: "Invalid server response. Please contact support.",
            });
            console.error("Response parse error:", e);
          }
        },
        error: function () {
          swal({
            type: "warning",
            title: "Update Failed",
            text: "An error occurred while updating. Please try again.",
          });
        }
      });
    });







    $("#OT_Extra_Hours_Employee_Update").on("click", function () {

      let isValid = true;
      $(".form-control").removeClass("input-error");
      $(".error-text").remove();

      function showError(selector, message) {
        $(selector).addClass("input-error");
        $(selector).after('<div class="error-text">' + message + "</div>");
        isValid = false;
      }

      const Date = $("#Date").val();
      const Type = $("#Type").val();
      const Supervisor_Name = $("#Supervisor_Name").val();

      if (!Date) showError("#Date", "Date is required.");
      if (!Type) showError("#Type", "Type is required.");
      if (!Supervisor_Name) showError("#Supervisor_Name", "Valid Supervisor is required.");

      if (!isValid) return;

      const employeeData = [];

      $('#OT_Extra_Hours_Employee_List tbody tr').each(function () {
        const row = $(this);
        const empNo = row.find('td').eq(1).text();
        const employeeName = row.find('td').eq(2).text();
        const firstPunchIn = row.find('td').eq(3).text();
        const lastPunchOut = row.find('td').eq(4).text();
        const updatedTime = row.find('td').eq(5).text();
        const totalWorkingHours = row.find('td').eq(7).text(); // can be "4:30" or "4.5"
        const extraHours = parseFloat(row.find('.Extra_Hours').val());

        let totalMinutesWorked = 0;
        if (totalWorkingHours.includes(':')) {
          const timeParts = totalWorkingHours.split(':');
          totalMinutesWorked = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
        } else {
          totalMinutesWorked = parseFloat(totalWorkingHours) * 60;
        }

        const extraMinutes = extraHours * 60;

        if (extraMinutes > totalMinutesWorked) {
          swal({
            type: "warning",
            title: "Invalid Working Hours",
            text: `Extra Hours (${extraHours}) cannot exceed Total Working Hours (${totalWorkingHours}).`,
          });
          row.find('.Extra_Hours').addClass("input-error");
          isValid = false;
          return false; // stop loop
        }

        if (isValid) {
          employeeData.push({
            EmpNo: empNo,
            Employee_Name: employeeName,
            FirstPunchIn: firstPunchIn,
            LastPunchOut: lastPunchOut,
            EMaster_Time: updatedTime,
            TotalWorking_Hours: totalWorkingHours,
            Extra_Hours: extraHours,
            Date: Date,
            Type: Type,
            Supervisor_Name: Supervisor_Name,
          });
        }
      });

      if (!isValid) return;

      const requestData = { Employees: employeeData };

      $.ajax({
        url: baseurl + 'OT/OT_Extra_Hours',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(requestData),
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var OT_Extra_Hours_Entry = Response_Data.OT_Extra_Hours_Entry;

          if (OT_Extra_Hours_Entry == 1) {

            swal({
              type: "success",
              title: "Success",
              text: "Extra Work Hours for Employees Have Been Updated!",
            });


            $.ajax({
              url: baseurl + "OT/Get_OT_Extra_Hours_List_Employee",
              type: "POST",
              data: {
                Date: $("#Date").val(),
                Type: $("#Type").val()
              },
              success: function (response) {
                const Response_Data = JSON.parse(response);
                const Employee_List_Extra_Hours = Response_Data.Get_OT_Extra_Hours_List_Employee;

                if (Employee_List_Extra_Hours == 0) {
                  swal({
                    type: "warning",
                    title: "Warning",
                    text: "Extra Work Employee Details Not Found!"
                  });

                  $("#OT_Extra_Hours_Employee_Update_Section").hide();
                  $("#OT_Extra_Hours_Employee_Update").hide();
                  $("#OT_Extra_Hours_Employee_Download").hide();
                } else {
                  $("#OT_Extra_Hours_Employee_Update_Section").show();
                  $("#OT_Extra_Hours_Employee_Download").show();
                  $("#OT_Hours_Employee_Update_Section").hide();

                  const tbody = $('#OT_Extra_Hours_Employee_List tbody');
                  tbody.empty();

                  let showUpdateButton = false;

                  Employee_List_Extra_Hours.forEach((Employee, index) => {
                    const diffInMinutes = parseInt(Employee.OUT_Updated_Diff, 10);
                    const hours = Math.floor(diffInMinutes / 60);
                    const minutes = diffInMinutes % 60;
                    const diffFormatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    const diffCellStyle = hours >= 1 ? 'background-color: rgb(250, 126, 126)' : '';

                    const inputDisabled = Employee.Entry_Status === "1" ? 'disabled' : '';

                    if (Employee.Entry_Status === "0") {
                      showUpdateButton = true;
                    }

                    const row = `
          <tr>
            <td>${index + 1}</td>
            <td>${Employee.Employee_ID}</td>
            <td>${Employee.Employee_Name}</td>
            <td>${Employee.IN_Time}</td>
            <td>${Employee.OUT_Time}</td>
            <td>${Employee.Updated_Time}</td>
            <td style="${diffCellStyle}">${diffFormatted}</td>
            <td>${Employee.Extra_Hours}</td>
            <td>
              <input type="number" class="form-control Extra_Hours" value="${Employee.Extra_Hours}"
                     style="width: 100%; height: 35px;" ${inputDisabled}>
            </td>
          </tr>
        `;
                    tbody.append(row);
                  });

                  if (showUpdateButton) {
                    $("#OT_Extra_Hours_Employee_Update").show();
                  } else {
                    $("#OT_Extra_Hours_Employee_Update").hide();
                  }
                }
              },
              error: function (xhr, status, error) {
                console.error("Error fetching data:", status, error);
              }
            });


          } else if (OT_Extra_Hours_Entry == 0) {
            swal({
              type: "warning",
              title: "Warning",
              text: "Extra Hours Already Updated!",
            });
          }
        },
        error: function (error) {
          console.error('Error saving data:', error);
        }
      });
    });



    $("#OT_Extra_Hours_Employee_Download").on("click", function () {

      var Date = $("#Date").val();
      var Type = $("#Type").val();

      $.ajax({
        url: baseurl + 'Reports/Extra_Hours_Employee_Download',
        type: 'POST',
        data: {
          Date,
          Type
        },
        success: function (response) {

          var Response_Data = JSON.parse(response);

          if (Response_Data.file_url) {
            var link = document.createElement("a");
            link.href = Response_Data.file_url;
            link.download = Response_Data.file_Name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }

        }
      })



    })



    $("#OT_Hours_Employee_Download").on("click", function () {

      var Date = $("#Date").val();
      var Type = $("#Type").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + 'Reports/OT_Hours_Employee_Download',
        type: 'POST',
        data: {
          Date,
          Type,
          Shift
        },
        success: function (response) {

          var Response_Data = JSON.parse(response);

          if (Response_Data.file_url) {
            var link = document.createElement("a");
            link.href = Response_Data.file_url;
            link.download = Response_Data.file_Name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }

        }
      })



    })








  }

})