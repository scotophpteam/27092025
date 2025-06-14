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

  $("#Allocation_Table_Container").hide();

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
        url: baseurl + "Work/Shift_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {

          const Response_Data = JSON.parse(response);
          const Shift_Employee_List = Response_Data.Shift_Employee_List;
          const User_Department = Response_Data.User_Department;
          const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
          const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });




          if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
            const item = Work_Allocation_Details_Count[0];

            $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
            $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
            $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
            $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
            $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
            $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
        }





          if (Shift_Employee_List.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Shift_Employee_List.Message,
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
                const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                $("#Allocation_Table tbody").append(wageRow);

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
                        `<option value="${dept}" ${
                          dept === item.Sub_Department ? "selected" : ""
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
                    <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                    item.EmpNo
                  }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>

                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                    machineOptions || ""
                  }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                    item.Description || ""
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
                url: baseurl + "Work/Work_Areas",
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

  $("#Allocation_Table tbody").on("change", ".Frame", function () {
    const $row = $(this).closest("tr");
    const department = $row.find(".Department").val();
    const workArea = $row.find(".WorkArea").val();
    const jobCardSelect = "";
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
                  frameSelect.append("<option value='Others'>Others</option>");
                  frameSelect.append(
                    "<option value='Multiple Trainee'>Multiple Trainee</option>"
                  );
                  frameSelect.append(
                    "<option value='Trainee'>Trainee</option>"
                  );
                  frameSelect.append("<option value='NoWork'>NoWork</option>");
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
              frameSelect.append("<option value='Trainee'>Trainee</option>");
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

              const hasEmptyFrame = Work_Type.some((work) => work.Frame === "");

              if (hasEmptyFrame) {
                frameSelect.empty();
                frameSelect.append("<option value=''></option>");
                frameSelect.append(
                  "<option value='Machine Wise'>Machine Wise</option>"
                );
                frameSelect.append("<option value='Others'>Others</option>");
                frameSelect.append(
                  "<option value='Multiple Trainee'>Multiple Trainee</option>"
                );
                frameSelect.append("<option value='Trainee'>Trainee</option>");
                frameSelect.append("<option value='NoWork'>NoWork</option>");
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
      url: baseurl + "Work/Save",
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
            url: baseurl + "Work/Shift_Employee_List",
            type: "POST",
            data: {
              Date: $("#Date").val(),
              Shift: $("#Shift").val(),
              Type: $("#Type").val(),
            },
            success: function (response) {
              const Response_Data = JSON.parse(response);
              const Shift_Employee_List = Response_Data.Shift_Employee_List;
              const User_Department = Response_Data.User_Department;
              const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
              const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

              if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
                const item = Work_Allocation_Details_Count[0];

                $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
                $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
                $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
                $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
                $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
                $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
            }

              if (Shift_Employee_List.Status == "Error") {
                swal({
                  type: "warning",
                  title: "Warning",
                  text: Shift_Employee_List.Message,
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
                    const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                    $("#Allocation_Table tbody").append(wageRow);

                    let groupedData = {};

                    groupedByWages[wage].forEach((item) => {
                      const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                      if (!groupedData[key]) {
                        groupedData[key] = {
                          ...item,
                          Machine_Id: [],
                          Frame: [],
                        };
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
                            `<option value="${dept}" ${
                              dept === item.Sub_Department ? "selected" : ""
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
                    <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                        item.EmpNo
                      }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                        machineOptions || ""
                      }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                        item.Description || ""
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
                    url: baseurl + "Work/Work_Areas",
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
              const Shift_Employee_List = Response_Data.Seperated_Sub_Section;
              const User_Department = Response_Data.User_Department;
              const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
              const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

              if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
                const item = Work_Allocation_Details_Count[0];

                $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
                $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
                $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
                $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
                $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
                $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
            }

              if (Shift_Employee_List.Status == "Error") {
                swal({
                  type: "warning",
                  title: "Warning",
                  text: Shift_Employee_List.Message,
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
                    const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                    $("#Allocation_Table tbody").append(wageRow);

                    let groupedData = {};

                    groupedByWages[wage].forEach((item) => {
                      const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                      if (!groupedData[key]) {
                        groupedData[key] = {
                          ...item,
                          Machine_Id: [],
                          Frame: [],
                        };
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
                            `<option value="${dept}" ${
                              dept === item.Sub_Department ? "selected" : ""
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
                   <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                        item.EmpNo
                      }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                        machineOptions || ""
                      }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                        item.Description || ""
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
                    url: baseurl + "Work/Work_Areas",
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
      },
    });
  });

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
      url: baseurl + "Work/Edit",
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

            var Sub_Section = $("#Sub_Section").val();

            if (Sub_Section == "All") {
              $.ajax({
                url: baseurl + "Work/Shift_Employee_List",
                type: "POST",
                data: {
                  Date: $("#Date").val(),
                  Shift: $("#Shift").val(),
                  Type: $("#Type").val(),
                },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Shift_Employee_List = Response_Data.Shift_Employee_List;
                  const User_Department = Response_Data.User_Department;
                  const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
                  const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });



                  $('#Total_Machine_Count').val(Get_Allocated_Machine_ID.Counts.Total_Machines);
                  alert(Get_Allocated_Machine_ID.Counts.Total_Machines)
                  $('#Allocated_Machine_Count').val(Get_Allocated_Machine_ID.Counts.Allocated_Count);
                  $('#Un_Allocated_Machine_Count').val(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

                  $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
                  $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

                  $('#Balance_MachineID').empty();
                  $('#Balance_MachineID').append('<option value="">-- Select Unallocated Machine --</option>');

                  $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
                      const frame = machineGroup.Frame;
                      const machineList = machineGroup.Machine_Id.split(',');

                      $.each(machineList, function(j, singleMachine) {
                          const trimmedMachine = $.trim(singleMachine);
                          if (trimmedMachine !== '') {
                              $('#Balance_MachineID').append(
                                  $('<option></option>')
                                      .val(trimmedMachine)
                                      .text(trimmedMachine + ' (' + frame + ')')
                              );
                          }
                      });
                  });






                  if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
                    const item = Work_Allocation_Details_Count[0];

                    $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
                    $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
                    $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
                    $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
                    $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
                    $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
                }

                  if (Shift_Employee_List.Status == "Error") {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: Shift_Employee_List.Message,
                    });

                    $("#Allocation_Table tbody").empty();
                    $(
                      "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                    ).hide();
                  } else {
                    const filteredShiftEmployeeList =
                      Shift_Employee_List.filter(
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
                        const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                        $("#Allocation_Table tbody").append(wageRow);

                        let groupedData = {};

                        groupedByWages[wage].forEach((item) => {
                          const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                          if (!groupedData[key]) {
                            groupedData[key] = {
                              ...item,
                              Machine_Id: [],
                              Frame: [],
                            };
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
                                `<option value="${dept}" ${
                                  dept === item.Sub_Department ? "selected" : ""
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
                            item.Assign_Status == 1 ||
                            item.Closing_Status == "1"
                              ? "display: none;"
                              : "display: inline;";
                          const editButtonVisibility =
                            item.Assign_Status == 1 &&
                            item.Closing_Status != "1"
                              ? "display: inline;"
                              : "display: none;";

                          const row = `<tr>
                    <td style="${rowBackgroundColor}">${continuousIndex}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                    <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                            item.EmpNo
                          }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                            machineOptions || ""
                          }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                            item.Description || ""
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
                        url: baseurl + "Work/Work_Areas",
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
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: {
                            Department,
                            WorkArea,
                            JobCardNo,
                            Date,
                            Shift,
                          },
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
                    Response_Data.Seperated_Sub_Section;
                  const User_Department = Response_Data.User_Department;

                  const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;

                  if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
                    const item = Work_Allocation_Details_Count[0];

                    $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
                    $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
                    $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
                    $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
                    $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
                    $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
                }

                  if (Shift_Employee_List.Status == "Error") {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: Shift_Employee_List.Message,
                    });

                    $("#Allocation_Table tbody").empty();
                    $(
                      "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                    ).hide();
                  } else {
                    const filteredShiftEmployeeList =
                      Shift_Employee_List.filter(
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
                        const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                        $("#Allocation_Table tbody").append(wageRow);

                        let groupedData = {};

                        groupedByWages[wage].forEach((item) => {
                          const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                          if (!groupedData[key]) {
                            groupedData[key] = {
                              ...item,
                              Machine_Id: [],
                              Frame: [],
                            };
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
                                `<option value="${dept}" ${
                                  dept === item.Sub_Department ? "selected" : ""
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
                            item.Assign_Status == 1 ||
                            item.Closing_Status == "1"
                              ? "display: none;"
                              : "display: inline;";
                          const editButtonVisibility =
                            item.Assign_Status == 1 &&
                            item.Closing_Status != "1"
                              ? "display: inline;"
                              : "display: none;";

                          const row = `<tr>
                    <td style="${rowBackgroundColor}">${continuousIndex}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                    <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                            item.EmpNo
                          }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                            machineOptions || ""
                          }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                            item.Description || ""
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
                        url: baseurl + "Work/Work_Areas",
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
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: {
                            Department,
                            WorkArea,
                            JobCardNo,
                            Date,
                            Shift,
                          },
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

  $("#Previous-Date-Allocation").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Type = $("#Type").val();

    $.ajax({
      url: baseurl + "Work/Previous_Allocation",
      type: "POST",
      data: { Date, Shift, Type },
      success: function (response) {
        try {
          var Response_Data = JSON.parse(response);

          if (
            Response_Data.Previous_Allocation &&
            Response_Data.Previous_Allocation.length > 0
          ) {
            var Previous_Allocation_Status =
              Response_Data.Previous_Allocation[0].status;

            if (Previous_Allocation_Status === "error") {
              swal({
                type: "warning",
                title: "Warning",
                text: Response_Data.Previous_Allocation[0].message,
              });
            } else {
              swal({
                type: "success",
                title: "Success",
                text: Response_Data.Previous_Allocation[0].message,
              });

              $.ajax({
                url: baseurl + "Work/Shift_Employee_List",
                type: "POST",
                data: {
                  Date: $("#Date").val(),
                  Shift: $("#Shift").val(),
                  Type: $("#Type").val(),
                },
                success: function (response) {
                  const Response_Data = JSON.parse(response);
                  const Shift_Employee_List = Response_Data.Shift_Employee_List;
                  const User_Department = Response_Data.User_Department;
                  const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
                  const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

                  if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
                    const item = Work_Allocation_Details_Count[0];

                    $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
                    $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
                    $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
                    $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
                    $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
                    $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
                }

                  if (Shift_Employee_List.Status == "Error") {
                    swal({
                      type: "warning",
                      title: "Warning",
                      text: Shift_Employee_List.Message,
                    });

                    $("#Allocation_Table tbody").empty();
                    $(
                      "#Allocation_Table_Container, #Previous-Date-Allocation, #Allocation_Details_Color_Details"
                    ).hide();
                  } else {
                    const filteredShiftEmployeeList =
                      Shift_Employee_List.filter(
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
                        const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                        $("#Allocation_Table tbody").append(wageRow);

                        let groupedData = {};

                        groupedByWages[wage].forEach((item) => {
                          const key = `${item.EmpNo}_${item.Sub_Department}_${item.WorkArea}_${item.Job_Card_No}`;
                          if (!groupedData[key]) {
                            groupedData[key] = {
                              ...item,
                              Machine_Id: [],
                              Frame: [],
                            };
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
                                `<option value="${dept}" ${
                                  dept === item.Sub_Department ? "selected" : ""
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
                            item.Assign_Status == 1 ||
                            item.Closing_Status == "1"
                              ? "display: none;"
                              : "display: inline;";
                          const editButtonVisibility =
                            item.Assign_Status == 1 &&
                            item.Closing_Status != "1"
                              ? "display: inline;"
                              : "display: none;";

                          const row = `<tr>
                    <td style="${rowBackgroundColor}">${continuousIndex}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Department form-control-sm' id='Department${continuousIndex}'>${departmentOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control WorkArea form-control-sm' id='WorkArea${continuousIndex}'>${workAreaOption}</select></td>
                   <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                            item.EmpNo
                          }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                            machineOptions || ""
                          }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                            item.Description || ""
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
                        url: baseurl + "Work/Work_Areas",
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
                        const JobCardNo = "";
                        const Date = $("#Date").val();
                        const Shift = $("#Shift").val();

                        $.ajax({
                          url: baseurl + "Work/Work_Type",
                          method: "POST",
                          data: {
                            Department,
                            WorkArea,
                            JobCardNo,
                            Date,
                            Shift,
                          },
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
              text: "No allocation data found.",
            });
          }
        } catch (e) {
          swal({
            type: "error",
            title: "Error",
            text: "Failed to process the response.",
          });
        }
      },
    });
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

    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Type = $("#Type").val();

    $.ajax({
      url: baseurl + "Work/Shift_Employee_List",
      type: "POST",
      data: {
        Date,
        Shift,
        Type,
      },
      success: function (response) {
        const Response_Data = JSON.parse(response);
        const Shift_Employee_List = Response_Data.Shift_Employee_List;
        const User_Department = Response_Data.User_Department;
        const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
        const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

        if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
          const item = Work_Allocation_Details_Count[0];

          $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
          $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
          $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
          $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
          $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
          $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
      }

        if (Shift_Employee_List.Status == "Error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Shift_Employee_List.Message,
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
              const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
              $("#Allocation_Table tbody").append(wageRow);

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
                      `<option value="${dept}" ${
                        dept === item.Sub_Department ? "selected" : ""
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
                   <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                  item.EmpNo
                }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                  machineOptions || ""
                }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                  item.Description || ""
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
              url: baseurl + "Work/Work_Areas",
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
  });

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

    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Type = $("#Type").val();

    $.ajax({
      url: baseurl + "Work/Shift_Employee_List",
      type: "POST",
      data: {
        Date,
        Shift,
        Type,
      },
      success: function (response) {
        const Response_Data = JSON.parse(response);
        const Shift_Employee_List = Response_Data.Shift_Employee_List;
        const User_Department = Response_Data.User_Department;
        const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
        const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

        if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
          const item = Work_Allocation_Details_Count[0];

          $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
          $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
          $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
          $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
          $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
          $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
      }

        if (Shift_Employee_List.Status == "Error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Shift_Employee_List.Message,
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
              const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
              $("#Allocation_Table tbody").append(wageRow);

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
                      `<option value="${dept}" ${
                        dept === item.Sub_Department ? "selected" : ""
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
                   <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                  item.EmpNo
                }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                  machineOptions || ""
                }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                  item.Description || ""
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
              url: baseurl + "Work/Work_Areas",
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
  });

  $("#Sub_Section").on("change", function () {

    $("#Allocation_Table tbody").empty();
    $("#Allocation_Table_Container").hide();

    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Work/Shift_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {
          const Response_Data = JSON.parse(response);
          const Shift_Employee_List = Response_Data.Shift_Employee_List;
          const User_Department = Response_Data.User_Department;
          const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
          const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

          if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
            const item = Work_Allocation_Details_Count[0];

            $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
            $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
            $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
            $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
            $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
            $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
        }

          if (Shift_Employee_List.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Shift_Employee_List.Message,
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
                const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                $("#Allocation_Table tbody").append(wageRow);

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
                        `<option value="${dept}" ${
                          dept === item.Sub_Department ? "selected" : ""
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
                    <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                    item.EmpNo
                  }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                    machineOptions || ""
                  }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                    item.Description || ""
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
                url: baseurl + "Work/Work_Areas",
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
          const Shift_Employee_List = Response_Data.Seperated_Sub_Section;
          const User_Department = Response_Data.User_Department;
          const Work_Allocation_Details_Count = Response_Data.Work_Allocation_Details_Count;
          const Get_Allocated_Machine_ID = Response_Data.Get_Allocated_Machine_ID;



          $('#Total_Machine_Count').val('Total Machine :' + Get_Allocated_Machine_ID.Counts.Total_Machines);
          $('#Allocated_Machine_Count').val('Allocated Machine : ' + Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#Un_Allocated_Machine_Count').val('Un Allocated Machine : ' +Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#allocatedCount').text(Get_Allocated_Machine_ID.Counts.Allocated_Count);
          $('#unAllocatedCount').text(Get_Allocated_Machine_ID.Counts.Unallocated_Count);

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $('#Balance_MachineID').empty();
          $('#Balance_MachineID').append('<option value=""></option>');

          $.each(Get_Allocated_Machine_ID.Unallocated_Machines, function(i, machineGroup) {
              $('#Balance_MachineID').append(
                  $('<option></option>')
                      .val(machineGroup.Frame)
                      .text(machineGroup.Frame + ' => ' + machineGroup.Machine_Id)
              );
          });

          if (Work_Allocation_Details_Count && Work_Allocation_Details_Count.length > 0) {
            const item = Work_Allocation_Details_Count[0];

            $('#unAllocatedBtn').text(`Un Allocated Employee: ${item.Un_Allocated_Count}`);
            $('#allocatedBtn').text(`Allocated Employee: ${item.Work_Allocated_Count}`);
            $('#noWorkBtn').text(`No Work Employee: ${item.No_Work_Count}`);
            $('#shiftClosedBtn').text(`Partial Shift Closed: ${item.Shift_Closing_Count}`);
            $('#shiftBtn').text(`Shift Punched Employee : ${item.Shift_Count}`);
            $('#lateBtn').text(`Late Punched Employee : ${item.Late_Count}`);
        }

          if (Shift_Employee_List.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Shift_Employee_List.Message,
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
                const wageRow = `<tr class="wage-header">
                                <td colspan="10"><strong>Wage Group: ${wage} (Employees: ${employeeCount})</strong></td>
                            </tr>`;
                $("#Allocation_Table tbody").append(wageRow);

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
                        `<option value="${dept}" ${
                          dept === item.Sub_Department ? "selected" : ""
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
                   <td style="${rowBackgroundColor}">${item.Type}</td>
                    <td style="${rowBackgroundColor}" class="Employee_Id" value="${
                    item.EmpNo
                  }">${item.EmpNo}</td>
                    <td style="${rowBackgroundColor}">${item.FirstName}</td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Frame form-control-sm' multiple="multiple" id='Frame${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${frameOptions}</select></td>
                    <td style="${rowBackgroundColor}"><select class='custom-select2 form-control Machine_Id form-control-sm' multiple="multiple" id='Machine_Id${continuousIndex}' style="width: 150px; line-height: 1.2; text-align: center;">${
                    machineOptions || ""
                  }</select></td>
                    <td style="${rowBackgroundColor}"><input type="text" class='form-control Description form-control-sm' id='Description${continuousIndex}' value="${
                    item.Description || ""
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
                url: baseurl + "Work/Work_Areas",
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
  });
});
