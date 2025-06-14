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
        swal({
          type: "warning",
          title: "Warning",
          text: Supervisor_List.Message,
        });
      } else {
        var Supervisor = {};

        for (var i = 0; i < Supervisor_List.length; i++) {
          var DName = Supervisor_List[i];
          Supervisor[DName.EmpNo] = DName.FirstName;
        }

        $("#Supervisor_Name").empty().append("<option value=''></option>");

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

  $("#Shift_Closing_container").hide();
  $("#Shift_Closing_Section").hide();

  $.ajax({
    url: baseurl + "Shift_Closing/Shifts",
    type: "POST",
    success: function (response) {
      var responseData = JSON.parse(response);
      var Shifts = responseData.Shifts;
      var Shift = {};

      // Populate the shift options
      for (var i = 0; i < Shifts.length; i++) {
        var DName = Shifts[i];
        Shift[DName.ShiftDesc] = DName.ShiftDesc;
      }

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

      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Shift_Closing/Allocation_List",
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
                                    <td data-EmployeeId="${item.EmpNo}">${
                  item.EmpNo
                }</td>
                                    <td data-EmployeeName="${item.FirstName}">${
                  item.FirstName
                }</td>
                                    <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${
                                      item.EmpNo
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
    },
  });

  $("#Shift_Employee_List_Update").on("click", function () {
    var Supervisor_Name = $("#Supervisor_Name").val();

    if (Supervisor_Name == "") {
      swal({
        type: "warning",
        title: "Warning",
        text: "Please Select Vaild Supervisor Name...",
      });
    } else {
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();
      var employeesData = [];
      var remarks = $("#remarks").val();

      $("#Shift_Employee_List tbody tr").each(function () {
        var departmentName = $(this).children().eq(1).text().trim();
        var workArea = $(this).children().eq(2).text().trim();
        var jobCardNo = $(this).children().eq(3).text().trim();
        var Employee_Id = $(this).find(".OTEmployeeCheckbox").data("empno");
        var isChecked = $(this).find(".OTEmployeeCheckbox").prop("checked")
          ? 1
          : 0;

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
      });

      $.ajax({
        url: baseurl + "Shift_Closing/Employee_Shift_Closings",
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

            $("Supervisor_Name").empty();

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
        },
        error: function (xhr, status, error) {
          console.error("Error: ", status, error);
          swal({
            icon: "error",
            title: "Error!",
            text: "There was an issue with closing the shift. Please try again.",
          });
        },
      });
    }
  });

  $("#Shift").on("change", function () {
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

    $.ajax({
      url: baseurl + "Shift_Closing/Allocation_List",
      type: "POST",
      data: { Date: $("#Date").val(), Shift },
      success: function (response) {
        var response_Data = JSON.parse(response);
        var Allocation_List = response_Data.Allocation_List;

        if (Allocation_List.status == "error") {
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

          $.each(Allocation_List, function (index, item) {
            if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
              processedEmpNos.add(item.EmpNo);

              var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.Sub_Department}</td>
                                    <td>${item.WorkArea}</td>
                                    <td>${item.Job_Card_No}</td>
                                    <td data-EmployeeId="${item.EmpNo}">${
                item.EmpNo
              }</td>
                                    <td data-EmployeeName="${item.FirstName}">${
                item.FirstName
              }</td>
                                    <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${
                                      item.EmpNo
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
  });

  $("#Date").on("change", function () {
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

    $.ajax({
      url: baseurl + "Shift_Closing/Allocation_List",
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

          $.each(Allocation_List, function (index, item) {
            if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
              processedEmpNos.add(item.EmpNo);

              var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.Sub_Department}</td>
                                    <td>${item.WorkArea}</td>
                                    <td>${item.Job_Card_No}</td>
                                    <td data-EmployeeId="${item.EmpNo}">${
                item.EmpNo
              }</td>
                                    <td data-EmployeeName="${item.FirstName}">${
                item.FirstName
              }</td>
                                    <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${
                                      item.EmpNo
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
  });

  $("#Sub_Section").on("change", function () {
    var Date = $("#Date").val();
    var Shift = $("#Shift").val();
    var Sub_Section = $("#Sub_Section").val();

    if (Sub_Section == "All") {
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Shift_Closing/Allocation_List",
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
                                    <td data-EmployeeId="${item.EmpNo}">${
                  item.EmpNo
                }</td>
                                    <td data-EmployeeName="${item.FirstName}">${
                  item.FirstName
                }</td>
                                    <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${
                                      item.EmpNo
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
    } else {
      $.ajax({
        url: baseurl + "Shift_Closing/Seperated_Assigned_Employee_List",
        type: "POST",
        data: {
          Shift,
          Date,
          Sub_Section,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);

          var Allocation_List = Response_Data.Seperated_Assigned_Employee_List;

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

            $.each(Allocation_List, function (index, item) {
              if (!processedEmpNos.has(item.EmpNo) && item.EmpNo) {
                processedEmpNos.add(item.EmpNo);

                var row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.Sub_Department}</td>
                                    <td>${item.WorkArea}</td>
                                    <td>${item.Job_Card_No}</td>
                                    <td data-EmployeeId="${item.EmpNo}">${
                  item.EmpNo
                }</td>
                                    <td data-EmployeeName="${item.FirstName}">${
                  item.FirstName
                }</td>
                                    <td><input type="checkbox" class="OTEmployeeCheckbox" data-empno="${
                                      item.EmpNo
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
    }
  });
});
