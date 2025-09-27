$(document).ready(function () {



  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);

var table = $("#OT_Employee_Report_Table").DataTable({
 paging: false,
 lengthChange: false,
 searching: true,
 ordering: true,
 info: true,
 autoWidth: true,
 });

  $("#Reports_For_Allocation").hide();

  $("#Work_Allocation_List_Container").hide();
  $("#Work_Allocation_Report_Down_Btn").hide();

  $.ajax({
    url: baseurl + "Shift_Closing/Shifts",
    type: "POST",
    success: function (response) {
      var responseData = JSON.parse(response);
      var Shifts = responseData.Shifts;
      var Shift = { "": "" };

      for (var i = 0; i < Shifts.length; i++) {
        var DName = Shifts[i];
        Shift[DName.ShiftDesc] = DName.ShiftDesc;
      }

      $.each(Shift, function (index, value) {
        $("#Shift").append(
          $("<option></option>").attr("value", value).text(value)
        );
      });
    },
  });




  $("#Work_Allocation_Report_View").on("click", function () {

    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    // Define the order for "Spinning-Prod" Sub_Department
    var frameOrder = [
      "RFHS1-4", "RFHS5-8", "RFHS9-12", "RFHS13-16", "RFHS17-20",
      "RFHS21-24", "RFHS25-28", "RFHS29-32", "RFHS33-36", "RFHS37-40",
      "RFHS41-44", "RFHS45-48", "RFHS49-52", "RFHS53-56", "RFHS57-60"
    ];

    function sortFrames(frames) {
      return frames.sort(function (a, b) {
        return frameOrder.indexOf(a) - frameOrder.indexOf(b);
      });
    }

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Work_Allocation",
        type: "POST",
        data: { Date: Date, Shift: Shift },
        success: function (response) {
          var responseData = JSON.parse(response);
          var Allocation_Report = responseData.Assigned_List;

          if (Allocation_Report.length > 0) {
            $("#Work_Allocation_List_Container").show();
            $("#Work_Allocation_Report_Down_Btn").show();

            var employeeData = {};

            $.each(Allocation_Report, function (index, item) {
              var empNo = item.EmpNo;

              if (!employeeData[empNo]) {
                employeeData[empNo] = {
                  empNo: item.EmpNo,
                  firstName: item.FirstName,
                  Department: item.Department,
                  Sub_Department: item.Sub_Department,
                  WorkArea: item.WorkArea,
                  Job_Card_No: item.Job_Card_No,
                  machines: [],
                };
              }

              var frames = Array.isArray(item.Frame) ? item.Frame : [item.Frame];

              // If Sub_Department is "Spinning-Prod", sort the frames
              if (item.Sub_Department === "Spinning-Prod") {
                frames = sortFrames(frames);
              } else {
                frames = frames.join(", ");
              }

              employeeData[empNo].machines.push({
                machineId: item.Machine_Id,
                machineName: item.Machine_Name,
                frameType: item.FrameType || "N/A",
                frames: frames,
                assignStatus: item.Assign_Status,
              });
            });

            $("#Reports_For_Allocation").show();
            $("#Work_Allocation_List tbody").empty();

            if (Object.keys(employeeData).length === 0) {
              $("#Work_Allocation_List").append("<tr><td colspan='12'>No data available</td></tr>");
            } else {
              var serialNumber = 1;
              var tableRows = [];

              $.each(employeeData, function (empNo, data) {
                var machineGroups = {};

                $.each(data.machines, function (index, machine) {
                  if (!machineGroups[machine.machineId]) {
                    machineGroups[machine.machineId] = {
                      machineName: machine.machineName,
                      frameType: machine.frameType,
                      frames: [],
                      assignStatus: machine.assignStatus,
                    };
                  }
                  machineGroups[machine.machineId].frames.push(machine.frames);
                });

                $.each(machineGroups, function (machineId, group) {
                  var badgeClass = group.assignStatus == 1 ? "badge-success" : "badge-danger";
                  var assignStatusText = group.assignStatus == 1 ? "Allocated" : "Not Allocated";
                  var description =
                    '<span class="badge ' + badgeClass + '">' + assignStatusText + "</span>";

                  var row = [
                    serialNumber,
                    data.empNo,
                    data.firstName,
                    data.WorkArea,
                    machineId,
                    group.frameType,
                    group.frames.join(", "),
                    description,
                  ];

                  tableRows.push(row);
                  serialNumber++;
                });
              });

              // Append rows to table
              $("#Work_Allocation_List tbody").append(
                tableRows
                  .map(function (row) {
                    return (
                      "<tr>" +
                      row
                        .map(function (cell) {
                          return "<td>" + cell + "</td>";
                        })
                        .join("") +
                      "</tr>"
                    );
                  })
                  .join("")
              );
            }
          } else {
            $("#Work_Allocation_List_Container").hide();
            $("#Work_Allocation_Report_Down_Btn").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: "Employee Data Not Found on Server!",
            });
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Work_Allocation_Sub_Section_Wise",
        type: "POST",
        data: { Date: Date, Shift: Shift, Sub_Section: Sub_Section },
        success: function (response) {
          var responseData = JSON.parse(response);
          var Allocation_Report = responseData.Work_Allocation_Sub_Section_Wise;

          if (Allocation_Report.length > 0) {
            $("#Work_Allocation_List_Container").show();
            $("#Work_Allocation_Report_Down_Btn").show();

            var employeeData = {};

            $.each(Allocation_Report, function (index, item) {
              var empNo = item.EmpNo;

              if (!employeeData[empNo]) {
                employeeData[empNo] = {
                  empNo: item.EmpNo,
                  firstName: item.FirstName,
                  Department: item.Department,
                  Sub_Department: item.Sub_Department,
                  WorkArea: item.WorkArea,
                  Job_Card_No: item.Job_Card_No,
                  machines: [],
                };
              }

              var frames = Array.isArray(item.Frame) ? item.Frame : [item.Frame];

              // If Sub_Department is "Spinning-Prod", sort the frames
              if (item.Sub_Department === "Spinning-Prod") {
                frames = sortFrames(frames);
              } else {
                frames = frames.join(", ");
              }

              employeeData[empNo].machines.push({
                machineId: item.Machine_Id,
                machineName: item.Machine_Name,
                frameType: item.FrameType || "N/A",
                frames: frames,
                assignStatus: item.Assign_Status,
              });
            });

            $("#Reports_For_Allocation").show();
            $("#Work_Allocation_List tbody").empty();

            if (Object.keys(employeeData).length === 0) {
              $("#Work_Allocation_List").append("<tr><td colspan='12'>No data available</td></tr>");
            } else {
              var serialNumber = 1;
              var tableRows = [];

              $.each(employeeData, function (empNo, data) {
                var machineGroups = {};

                $.each(data.machines, function (index, machine) {
                  if (!machineGroups[machine.machineId]) {
                    machineGroups[machine.machineId] = {
                      machineName: machine.machineName,
                      frameType: machine.frameType,
                      frames: [],
                      assignStatus: machine.assignStatus,
                    };
                  }
                  machineGroups[machine.machineId].frames.push(machine.frames);
                });

                $.each(machineGroups, function (machineId, group) {
                  var badgeClass = group.assignStatus == 1 ? "badge-success" : "badge-danger";
                  var assignStatusText = group.assignStatus == 1 ? "Allocated" : "Not Allocated";
                  var description =
                    '<span class="badge ' + badgeClass + '">' + assignStatusText + "</span>";

                  var row = [
                    serialNumber,
                    data.empNo,
                    data.firstName,
                    data.WorkArea,
                    machineId,
                    group.frameType,
                    group.frames.join(", "),
                    description,
                  ];

                  tableRows.push(row);
                  serialNumber++;
                });
              });

              // Append rows to table
              $("#Work_Allocation_List tbody").append(
                tableRows
                  .map(function (row) {
                    return (
                      "<tr>" +
                      row
                        .map(function (cell) {
                          return "<td>" + cell + "</td>";
                        })
                        .join("") +
                      "</tr>"
                    );
                  })
                  .join("")
              );
            }
          } else {
            $("#Work_Allocation_List_Container").hide();
            $("#Work_Allocation_Report_Down_Btn").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: Work_Allocation_Sub_Section_Wise.Message,
            });
          }
        },
      });
    }
  });




  $("#Work_Allocation_Report_Down_Btn").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Work_Allocation_Report_Download",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Employee_Work_Allocation_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Work_Allocation_Report_Download_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Employee_Work_Allocation_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    }
  });

  $("#Late_Employee_Report").on("click", function () {
    $.ajax({
      url: baseurl + "Reports/Late_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
      },
      success: function (response) {
        var responseData = JSON.parse(response);

        if (responseData.file_url) {
          var link = document.createElement("a");
          link.href = responseData.file_url;
          link.download = "Work_Allocation_Report.csv";
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        } else {
          alert("Failed to generate the report");
        }
      },
    });
  });

  $("#Shift_Closing_Report_Down_Btn").hide();
  $("#Shift_Closing_List_Table_Container").hide();

  $("#Shift_Closing_Report_View").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Shift_Closing_List",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.status == "error") {
            $("#Shift_Closing_Report_Down_Btn").hide();
            $("#Shift_Closing_List_Table_Container").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: response_Data.message,
            });
          } else {
            $("#Shift_Closing_Report_Down_Btn").show();
            $("#Shift_Closing_List_Table tbody").empty();
            $("#Shift_Closing_List_Table_Container").show();

            $.each(
              response_Data.Shift_Closing_Report_Download,
              function (index, item) {
                var closingStatus = "";
                var badgeClass = "";

                // Check the Closing Status and set the badge class accordingly
                if (item.Closing_Status == 1) {
                  closingStatus = "CLOSED";
                  badgeClass = "badge-success"; // Green for CLOSED
                } else if (item.Closing_Status == 0) {
                  closingStatus = "NOT CLOSED";
                  badgeClass = "badge-warning"; // Orange for NOT CLOSED
                }

                var row = `
                        <tr>
                            <td>${index + 1}</td>
                               <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                            <td>${item.Department}</td>
                                                        <td>${item.Sub_Department
                  }</td>

                            <td>${item.WorkArea}</td>
                            <td>${item.Job_Card_No}</td>

                            <td><span class="badge ${badgeClass}">${closingStatus}</span></td>

                        </tr>
                    `;
                $("#Shift_Closing_List_Table tbody").append(row);
              }
            );
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Shift_Closing_List_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);
          var Shift_Closing_List_Sub_Section =
            response_Data.Shift_Closing_List_Sub_Section;

          if (Shift_Closing_List_Sub_Section.Status == "error") {
            $("#Shift_Closing_Report_Down_Btn").hide();
            $("#Shift_Closing_List_Table_Container").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: Shift_Closing_List_Sub_Section.Message,
            });
          } else {
            $("#Shift_Closing_Report_Down_Btn").show();
            $("#Shift_Closing_List_Table tbody").empty();
            $("#Shift_Closing_List_Table_Container").show();

            $.each(Shift_Closing_List_Sub_Section, function (index, item) {
              var closingStatus = "";
              var badgeClass = "";

              // Check the Closing Status and set the badge class accordingly
              if (item.Closing_Status == 1) {
                closingStatus = "CLOSED";
                badgeClass = "badge-success"; // Green for CLOSED
              } else if (item.Closing_Status == 0) {
                closingStatus = "NOT CLOSED";
                badgeClass = "badge-warning"; // Orange for NOT CLOSED
              }

              var row = `
                        <tr>
                            <td>${index + 1}</td>
                               <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                            <td>${item.Department}</td>
                                                        <td>${item.Sub_Department
                }</td>

                            <td>${item.WorkArea}</td>
                            <td>${item.Job_Card_No}</td>

                            <td><span class="badge ${badgeClass}">${closingStatus}</span></td>

                        </tr>
                    `;
              $("#Shift_Closing_List_Table tbody").append(row);
            });
          }
        },
      });
    }
  });

  $("#Shift_Closing_Report_Down_Btn").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Shift_Closing_Report_Download",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Shift_Closing_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Shift_Closing_Report_Download_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Shift_Closing_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    }
  });

  $("#No_Work_Employee_Report_down").hide();
  $("#No_Work_Employee_Report_Section").hide();

  $("#No_Work_Employee_View").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/NoWork_Employee_List",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.status == "error") {
            $("#No_Work_Employee_Report_down").hide();
            $("#No_Work_Employee_Report_Section").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: response_Data.mesaage,
            });
          } else {
            $("#No_Work_Employee_Report_down").show();
            $("#No_Work_Employee_Report_Table tbody").empty();
            $("#No_Work_Employee_Report_Section").show();

            $.each(response_Data.NoWork_Employee_List, function (index, item) {
              var closingStatus = "";
              var badgeClass = "";

              // Check the Closing Status and set the badge class accordingly
              if (item.Closing_Status == 1) {
                closingStatus = "CLOSED";
                badgeClass = "badge-success"; // Green for CLOSED
              } else if (item.Closing_Status == 0) {
                closingStatus = "NOT CLOSED";
                badgeClass = "badge-warning"; // Orange for NOT CLOSED
              }

              var row = `
                        <tr>
                            <td>${index + 1}</td>
                              <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                                                        <td>${item.Sub_Department
                }</td>

                            <td>${item.WorkArea}</td>
                            <td><span class="badge ${badgeClass}">${closingStatus}</span></td>
                            <td>${item.Created_By}</td>
                            <td>${item.Created_Time}</td>

                        </tr>
                    `;
              $("#No_Work_Employee_Report_Table tbody").append(row);
            });
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/NoWork_Employee_List_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);
          var NoWork_Employee_List_Sub_Section =
            response_Data.NoWork_Employee_List_Sub_Section;

          if (NoWork_Employee_List_Sub_Section.Status == "error") {
            $("#No_Work_Employee_Report_down").hide();
            $("#No_Work_Employee_Report_Section").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: NoWork_Employee_List_Sub_Section.Message,
            });
          } else {
            $("#No_Work_Employee_Report_down").show();
            $("#No_Work_Employee_Report_Table tbody").empty();
            $("#No_Work_Employee_Report_Section").show();

            $.each(NoWork_Employee_List_Sub_Section, function (index, item) {
              var closingStatus = "";
              var badgeClass = "";

              // Check the Closing Status and set the badge class accordingly
              if (item.Closing_Status == 1) {
                closingStatus = "CLOSED";
                badgeClass = "badge-success"; // Green for CLOSED
              } else if (item.Closing_Status == 0) {
                closingStatus = "NOT CLOSED";
                badgeClass = "badge-warning"; // Orange for NOT CLOSED
              }

              var row = `
              <tr>
                  <td>${index + 1}</td>
                    <td>${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td>${item.Sub_Department
                }</td>

                  <td>${item.WorkArea}</td>
                  <td><span class="badge ${badgeClass}">${closingStatus}</span></td>
                  <td>${item.Created_By}</td>
                  <td>${item.Created_Time}</td>

              </tr>
          `;
              $("#No_Work_Employee_Report_Table tbody").append(row);
            });
          }
        },
      });
    }
  });

  $("#No_Work_Employee_Report_down").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/No_Work_Report_Download",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "No_Work_Employee_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/No_Work_Report_Download_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "No_Work_Employee_Report.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    }
  });

  $("#Late_Employee_Report_Dwon_Btn").hide();
  $("#Late_Extra_Employee_Section").hide();

  $("#Late_Employee_Report_view").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Late_Employee_List",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.status == "error") {
            $("#Late_Employee_Report_Dwon_Btn").hide();
            $("#Late_Extra_Employee_Section").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: response_Data.mesaage,
            });
          } else {
            $("#Late_Employee_Report_Dwon_Btn").show();
            $("#Late_Extra_Employee_Table tbody").empty();
            $("#Late_Extra_Employee_Section").show();

            $.each(response_Data.Late_Employee_List, function (index, item) {
              var closingStatus = "";
              var badgeClass = "";

              // Check the Closing Status and set the badge class accordingly
              if (item.Closing_Status == 1) {
                closingStatus = "CLOSED";
                badgeClass = "badge-success"; // Green for CLOSED
              } else if (item.Closing_Status == 0) {
                closingStatus = "NOT CLOSED";
                badgeClass = "badge-warning"; // Orange for NOT CLOSED
              }

              var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                            <td>${item.WorkArea}</td>
                            <td>${item.Type}</td>
                            <td>${item.Machine_Id}</td>
                            <td>${item.FrameType}</td>
                            <td>${item.Frame}</td>
                            <td>${item.Description}</td>
                            <td><span class="badge ${badgeClass}">${closingStatus}</span></td>


                        </tr>
                    `;
              $("#Late_Extra_Employee_Table tbody").append(row);
            });
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Late_Employee_List_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);
          var NoWork_Employee_List_Sub_Section =
            response_Data.Late_Employee_List_Sub_Section;

          if (NoWork_Employee_List_Sub_Section.Status == "error") {
            $("#Late_Employee_Report_Dwon_Btn").hide();
            $("#Late_Extra_Employee_Section").hide();

            swal({
              type: "warning",
              title: "Warning",
              text: NoWork_Employee_List_Sub_Section.Mesaage,
            });
          } else {
            $("#Late_Employee_Report_Dwon_Btn").show();
            $("#Late_Extra_Employee_Table tbody").empty();
            $("#Late_Extra_Employee_Section").show();

            $.each(NoWork_Employee_List_Sub_Section, function (index, item) {
              var closingStatus = "";
              var badgeClass = "";

              // Check the Closing Status and set the badge class accordingly
              if (item.Closing_Status == 1) {
                closingStatus = "CLOSED";
                badgeClass = "badge-success"; // Green for CLOSED
              } else if (item.Closing_Status == 0) {
                closingStatus = "NOT CLOSED";
                badgeClass = "badge-warning"; // Orange for NOT CLOSED
              }

              var row = `
              <tr>
                  <td>${index + 1}</td>
                  <td>${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td>${item.WorkArea}</td>
                  <td>${item.Type}</td>
                  <td>${item.Machine_Id}</td>
                  <td>${item.FrameType}</td>
                  <td>${item.Frame}</td>
                  <td>${item.Description}</td>
                  <td><span class="badge ${badgeClass}">${closingStatus}</span></td>


              </tr>
          `;
              $("#Late_Extra_Employee_Table tbody").append(row);
            });
          }
        },
      });
    }
  });

  $("#Late_Employee_Report_Dwon_Btn").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Reports/Late_Employee_List_Download",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Late_Employee_List.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Reports/Late_Employee_List_Download_Sub_Section",
        type: "POST",
        data: {
          Date,
          Shift,
          Sub_Section,
        },
        success: function (response) {
          var response_Data = JSON.parse(response);

          if (response_Data.file_url) {
            var link = document.createElement("a");
            link.href = response_Data.file_url;
            link.download = "Late_Employee_List.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    }
  });

  $("#Shift").on("change", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();

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
        $("#Sel_Shift").append(
          $("<option></option>").attr("value", value).text(value)
        );
      });

      $("#Sel_Shift option:first").prop("selected", true);
    }
  })


  $("#OT_Employee_Report_Section").hide();
  $("#OT_Employee_Report_Down").hide();
  $('#OT_Employee_Report_View').on('click', function () {
    // alert("hi")
    var Date = $('#Date').val();
    var Shift = $('#Sel_Shift').val();

    $.ajax({
      url: baseurl + "Reports/Get_OT_Employee_List",
      type: 'POST',
      data: {
        Date,
        Shift
      },
      success: function (response) {
        var responseData = JSON.parse(response);
        var Get_OT_Employee_List = responseData.Get_OT_Employee_List;

        let continuousIndex = 1;

        if (responseData.status == "error") {

          swal({
            type: "warning",
            title: "Warning",
            text: responseData.message,
          });
          $("#OT_Employee_Report_Section").hide();
          $("#OT_Employee_Report_Down").hide();

        } else {
          let allEmpty = true;
          let completedCount = 0;
          let missedCount = 0;
          let manualAttendanceCount = 0;
          $("#OT_Employee_Report_Down").show();
          $("#OT_Employee_Report_Table tbody").empty();
          $("#OT_Employee_Report_Section").show();

          $.each(Get_OT_Employee_List, function (index, item) {
            var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.WorkArea}</td>
                            <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                             <td>${item.Previous_Shift}</td>
                             <td>${item.Frame}</td>
                            <td>${item.Machine_Id}</td>
                        </tr>
                    `;
            $("#OT_Employee_Report_Table tbody").append(row);
            table.row.add($(row)).draw();
            continuousIndex++;
          });
        }
      }
    });
  });

  $("#OT_Employee_Report_Down").on("click", function () {
    var Date = $("#Date").val();
    var Shift = $("#Sel_Shift").val();

    // alert(Shift);   

    $.ajax({
      url: baseurl + "Reports/OT_Employee_List_Report_Download",
      type: "POST",
      data: {
        Date,
        Shift,
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);

        if (Response_Data.file_url) {
          var link = document.createElement("a");
          link.href = Response_Data.file_url;
          link.download = currentDate + "OT Employee List.xlsx";
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        } else {
          alert("Failed to generate the report");
        }
      },
    });
  });





});
