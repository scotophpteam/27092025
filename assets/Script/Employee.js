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

  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);



  var table = $("#Employee_Punching_List_Table").DataTable({
    paging: false,
    searching: true,
    ordering: true,
    info: false,
  });

  function reinitializeTable() {
    if ($.fn.dataTable.isDataTable("#Employee_Punching_List_Table")) {
      table.destroy();
    }
    table = $("#Employee_Punching_List_Table").DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
    });
  }

  var Manual_Attendance_Entry_Screen = $("#Manual_Attendance_Entry_Screen").val();
  var Punching_Attendance_Report = $("#Punching_Attendance_Report").val();
  var Employee_Punching_List_Screen = $("#Employee_Punching_List_Screen").val();
  var Employee_Punching_List_Details_Screen = $("#Employee_Punching_List_Details_Screen").val();

  if (Manual_Attendance_Entry_Screen == "Manual_Attendance_Entry_Screen") {


    $.ajax({
      url: baseurl + "Employee/Shift_Details",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Shift_Details = Response_Data.Shift_Details;

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

        $.ajax({
          url: baseurl + "Employee/Shift_Timings",
          type: "POST",
          data: {
            Shift: $("#Shift").val(),
          },
          success: function (response) {
            $("#To_Time").val("");
            $("#From_Time").val("");

            var Response_Data = JSON.parse(response);
            var Shift_Timings = Response_Data.Shift_Timings;

            var From_Time = Shift_Timings[0].StartTime;
            var To_Time = Shift_Timings[0].EndTime;

            $("#From_Time").val(From_Time);
            $("#To_Time").val(To_Time);

            var fromParts = From_Time.split(":");
            var toParts = To_Time.split(":");

            var fromDate = new Date(
              0,
              0,
              0,
              parseInt(fromParts[0]),
              parseInt(fromParts[1])
            );
            var toDate = new Date(
              0,
              0,
              0,
              parseInt(toParts[0]),
              parseInt(toParts[1])
            );

            var diffMs = toDate - fromDate;
            if (diffMs < 0) {
              toDate.setDate(toDate.getDate() + 1);
              diffMs = toDate - fromDate;
            }

            var hours = Math.floor(diffMs / 1000 / 60 / 60);
            var minutes = Math.floor((diffMs / 1000 / 60) % 60);

            var Total_Working_Time =
              hours.toString().padStart(2, "0") +
              ":" +
              minutes.toString().padStart(2, "0");

            $("#Total_Working_Hour").val(Total_Working_Time);
          },
        });

        $.ajax({
          url: baseurl + "Employee/Shift_Employee_List",
          type: "POST",
          data: {
            Shift: $("#Shift").val(),
            Date: $("#Date").val(),
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Shift_Employee_List = Response_Data.Shift_Employee_List;

            $("#Employee_Id").empty();

            for (var i = 0; i < Shift_Employee_List.length; i++) {
              var item = Shift_Employee_List[i];
              var machineID = item.MachineID;
              var firstName = item.FirstName;

              $("#Employee_Id").append(
                $("<option></option>")
                  .attr("value", machineID)
                  .text(machineID + " - " + firstName)
              );
            }
          },
        });
      },
    });

    $("#Shift").on("change", function () {
      $.ajax({
        url: baseurl + "Employee/Shift_Timings",
        type: "POST",
        data: {
          Shift: $("#Shift").val(),
        },
        success: function (response) {
          $("#To_Time").val("");
          $("#From_Time").val("");

          var Response_Data = JSON.parse(response);
          var Shift_Timings = Response_Data.Shift_Timings;

          var From_Time = Shift_Timings[0].StartTime;
          var To_Time = Shift_Timings[0].EndTime;

          $("#From_Time").val(From_Time);
          $("#To_Time").val(To_Time);

          var fromParts = From_Time.split(":");
          var toParts = To_Time.split(":");

          var fromDate = new Date(
            0,
            0,
            0,
            parseInt(fromParts[0]),
            parseInt(fromParts[1])
          );
          var toDate = new Date(
            0,
            0,
            0,
            parseInt(toParts[0]),
            parseInt(toParts[1])
          );

          var diffMs = toDate - fromDate;
          if (diffMs < 0) {
            toDate.setDate(toDate.getDate() + 1);
            diffMs = toDate - fromDate;
          }

          var totalMinutes = Math.floor(diffMs / 1000 / 60);
          var hours = Math.floor(totalMinutes / 60);
          var minutes = totalMinutes % 60;

          var Total_Working_Time =
            hours.toString().padStart(2, "0") +
            ":" +
            minutes.toString().padStart(2, "0");
          $("#Total_Working_Hour").val(Total_Working_Time);

          var Working_Type = $("#Working_Type").val();

          if (Working_Type == "First Half") {
            $("#Total_OT_Hour").prop("readonly", true);

            var halfMinutes = Math.floor(totalMinutes / 2);

            var halfHours = Math.floor(halfMinutes / 60);
            var halfMins = halfMinutes % 60;

            var Convert_Total_Timing =
              halfHours.toString().padStart(2, "0") +
              ":" +
              halfMins.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Convert_Total_Timing);

            var fromTotalMinutes =
              parseInt(fromParts[0]) * 60 + parseInt(fromParts[1]);
            var newToMinutes = fromTotalMinutes + halfMinutes;

            var newToHours = Math.floor(newToMinutes / 60) % 24;
            var newToMins = newToMinutes % 60;

            var Convert_To_Time =
              newToHours.toString().padStart(2, "0") +
              ":" +
              newToMins.toString().padStart(2, "0");
            $("#To_Time").val(Convert_To_Time);
          } else if (Working_Type == "Second Half") {
            $("#Total_OT_Hour").prop("readonly", true);

            var halfMinutes = Math.floor(totalMinutes / 2);

            var halfHours = Math.floor(halfMinutes / 60);
            var halfMins = halfMinutes % 60;

            var Convert_Total_Timing =
              halfHours.toString().padStart(2, "0") +
              ":" +
              halfMins.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Convert_Total_Timing);

            var toTotalMinutes =
              parseInt(toParts[0]) * 60 + parseInt(toParts[1]);
            var newFromMinutes = toTotalMinutes - halfMinutes;

            if (newFromMinutes < 0) {
              newFromMinutes += 24 * 60;
            }

            var newFromHours = Math.floor(newFromMinutes / 60) % 24;
            var newFromMins = newFromMinutes % 60;

            var Convert_From_Time =
              newFromHours.toString().padStart(2, "0") +
              ":" +
              newFromMins.toString().padStart(2, "0");
            $("#From_Time").val(Convert_From_Time);
          } else {
            $("#Total_OT_Hour").prop("readonly", false);
          }
        },
      });

      $.ajax({
        url: baseurl + "Employee/Shift_Employee_List",
        type: "POST",
        data: {
          Shift: $("#Shift").val(),
          Date: $("#Date").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Shift_Employee_List = Response_Data.Shift_Employee_List;

          $("#Employee_Id").empty();

          for (var i = 0; i < Shift_Employee_List.length; i++) {
            var item = Shift_Employee_List[i];
            var machineID = item.MachineID;
            var firstName = item.FirstName;

            $("#Employee_Id").append(
              $("<option></option>")
                .attr("value", machineID)
                .text(machineID + " - " + firstName)
            );
          }
        },
      });
    });

    // $("Supervisor_Name").empty();

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

    $("#Working_Type").on("change", function () {
      $.ajax({
        url: baseurl + "Employee/Shift_Timings",
        type: "POST",
        data: {
          Shift: $("#Shift").val(),
        },
        success: function (response) {
          $("#To_Time").val("");
          $("#From_Time").val("");

          var Response_Data = JSON.parse(response);
          var Shift_Timings = Response_Data.Shift_Timings;

          var From_Time = Shift_Timings[0].StartTime;
          var To_Time = Shift_Timings[0].EndTime;

          $("#From_Time").val(From_Time);
          $("#To_Time").val(To_Time);

          var fromParts = From_Time.split(":");
          var toParts = To_Time.split(":");

          var fromDate = new Date(
            0,
            0,
            0,
            parseInt(fromParts[0]),
            parseInt(fromParts[1])
          );
          var toDate = new Date(
            0,
            0,
            0,
            parseInt(toParts[0]),
            parseInt(toParts[1])
          );

          var diffMs = toDate - fromDate;
          if (diffMs < 0) {
            toDate.setDate(toDate.getDate() + 1);
            diffMs = toDate - fromDate;
          }

          var totalMinutes = Math.floor(diffMs / 1000 / 60);
          var hours = Math.floor(totalMinutes / 60);
          var minutes = totalMinutes % 60;

          var Total_Working_Time =
            hours.toString().padStart(2, "0") +
            ":" +
            minutes.toString().padStart(2, "0");
          $("#Total_Working_Hour").val(Total_Working_Time);

          var Working_Type = $("#Working_Type").val();

          if (Working_Type == "First Half") {
            $("#Total_OT_Hour").prop("readonly", true);

            var halfMinutes = Math.floor(totalMinutes / 2);

            var halfHours = Math.floor(halfMinutes / 60);
            var halfMins = halfMinutes % 60;

            var Convert_Total_Timing =
              halfHours.toString().padStart(2, "0") +
              ":" +
              halfMins.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Convert_Total_Timing);

            var fromTotalMinutes =
              parseInt(fromParts[0]) * 60 + parseInt(fromParts[1]);
            var newToMinutes = fromTotalMinutes + halfMinutes;

            var newToHours = Math.floor(newToMinutes / 60) % 24;
            var newToMins = newToMinutes % 60;

            var Convert_To_Time =
              newToHours.toString().padStart(2, "0") +
              ":" +
              newToMins.toString().padStart(2, "0");
            $("#To_Time").val(Convert_To_Time);
          } else if (Working_Type == "Second Half") {
            $("#Total_OT_Hour").prop("readonly", true);

            var halfMinutes = Math.floor(totalMinutes / 2);

            var halfHours = Math.floor(halfMinutes / 60);
            var halfMins = halfMinutes % 60;

            var Convert_Total_Timing =
              halfHours.toString().padStart(2, "0") +
              ":" +
              halfMins.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Convert_Total_Timing);

            var toTotalMinutes =
              parseInt(toParts[0]) * 60 + parseInt(toParts[1]);
            var newFromMinutes = toTotalMinutes - halfMinutes;

            if (newFromMinutes < 0) {
              newFromMinutes += 24 * 60;
            }

            var newFromHours = Math.floor(newFromMinutes / 60) % 24;
            var newFromMins = newFromMinutes % 60;

            var Convert_From_Time =
              newFromHours.toString().padStart(2, "0") +
              ":" +
              newFromMins.toString().padStart(2, "0");
            $("#From_Time").val(Convert_From_Time);
          } else {
            $("#Total_OT_Hour").prop("readonly", false);
          }
        },
      });
    });

    $("#Total_OT_Hour").keyup(function () {
      var Total_OT_Hour = $("#Total_OT_Hour").val();
      var To_Time = $("#To_Time").val();
      var Total_Working_Hour = $("#Total_Working_Hour").val();

      if (!isNaN(Total_OT_Hour) && Total_OT_Hour > 8) {
        alert("OT Hours cannot exceed 8 hours.");
        $("#Total_OT_Hour").val("");
        Total_OT_Hour = 8;

        $.ajax({
          url: baseurl + "Employee/Shift_Timings",
          type: "POST",
          data: {
            Shift: $("#Shift").val(),
          },
          success: function (response) {
            $("#To_Time").val("");
            $("#From_Time").val("");

            var Response_Data = JSON.parse(response);
            var Shift_Timings = Response_Data.Shift_Timings;

            var From_Time = Shift_Timings[0].StartTime;
            var To_Time = Shift_Timings[0].EndTime;

            $("#From_Time").val(From_Time);
            $("#To_Time").val(To_Time);

            var fromParts = From_Time.split(":");
            var toParts = To_Time.split(":");

            var fromDate = new Date(
              0,
              0,
              0,
              parseInt(fromParts[0]),
              parseInt(fromParts[1])
            );
            var toDate = new Date(
              0,
              0,
              0,
              parseInt(toParts[0]),
              parseInt(toParts[1])
            );

            var diffMs = toDate - fromDate;
            if (diffMs < 0) {
              toDate.setDate(toDate.getDate() + 1);
              diffMs = toDate - fromDate;
            }

            var totalMinutes = Math.floor(diffMs / 1000 / 60);
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;

            var Total_Working_Time =
              hours.toString().padStart(2, "0") +
              ":" +
              minutes.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Total_Working_Time);

            var Working_Type = $("#Working_Type").val();

            if (Working_Type == "First Half") {
              $("#Total_OT_Hour").prop("readonly", true);

              var halfMinutes = Math.floor(totalMinutes / 2);
              var halfHours = Math.floor(halfMinutes / 60);
              var halfMins = halfMinutes % 60;

              var Convert_Total_Timing =
                halfHours.toString().padStart(2, "0") +
                ":" +
                halfMins.toString().padStart(2, "0");
              $("#Total_Working_Hour").val(Convert_Total_Timing);

              var fromTotalMinutes =
                parseInt(fromParts[0]) * 60 + parseInt(fromParts[1]);
              var newToMinutes = fromTotalMinutes + halfMinutes;

              var newToHours = Math.floor(newToMinutes / 60) % 24;
              var newToMins = newToMinutes % 60;

              var Convert_To_Time =
                newToHours.toString().padStart(2, "0") +
                ":" +
                newToMins.toString().padStart(2, "0");
              $("#To_Time").val(Convert_To_Time);
            } else if (Working_Type == "Second Half") {
              $("#Total_OT_Hour").prop("readonly", true);

              var halfMinutes = Math.floor(totalMinutes / 2);
              var halfHours = Math.floor(halfMinutes / 60);
              var halfMins = halfMinutes % 60;

              var Convert_Total_Timing =
                halfHours.toString().padStart(2, "0") +
                ":" +
                halfMins.toString().padStart(2, "0");
              $("#Total_Working_Hour").val(Convert_Total_Timing);

              var toTotalMinutes =
                parseInt(toParts[0]) * 60 + parseInt(toParts[1]);
              var newFromMinutes = toTotalMinutes - halfMinutes;

              if (newFromMinutes < 0) {
                newFromMinutes += 24 * 60;
              }

              var newFromHours = Math.floor(newFromMinutes / 60) % 24;
              var newFromMins = newFromMinutes % 60;

              var Convert_From_Time =
                newFromHours.toString().padStart(2, "0") +
                ":" +
                newFromMins.toString().padStart(2, "0");
              $("#From_Time").val(Convert_From_Time);
            } else {
              $("#Total_OT_Hour").prop("readonly", false);
            }
          },
        });
      } else if (Total_OT_Hour == "") {
        $.ajax({
          url: baseurl + "Employee/Shift_Timings",
          type: "POST",
          data: {
            Shift: $("#Shift").val(),
          },
          success: function (response) {
            $("#To_Time").val("");
            $("#From_Time").val("");

            var Response_Data = JSON.parse(response);
            var Shift_Timings = Response_Data.Shift_Timings;

            var From_Time = Shift_Timings[0].StartTime;
            var To_Time = Shift_Timings[0].EndTime;

            $("#From_Time").val(From_Time);
            $("#To_Time").val(To_Time);

            var fromParts = From_Time.split(":");
            var toParts = To_Time.split(":");

            var fromDate = new Date(
              0,
              0,
              0,
              parseInt(fromParts[0]),
              parseInt(fromParts[1])
            );
            var toDate = new Date(
              0,
              0,
              0,
              parseInt(toParts[0]),
              parseInt(toParts[1])
            );

            var diffMs = toDate - fromDate;
            if (diffMs < 0) {
              toDate.setDate(toDate.getDate() + 1);
              diffMs = toDate - fromDate;
            }

            var totalMinutes = Math.floor(diffMs / 1000 / 60);
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;

            var Total_Working_Time =
              hours.toString().padStart(2, "0") +
              ":" +
              minutes.toString().padStart(2, "0");
            $("#Total_Working_Hour").val(Total_Working_Time);

            var Working_Type = $("#Working_Type").val();

            if (Working_Type == "First Half") {
              $("#Total_OT_Hour").prop("readonly", true);

              var halfMinutes = Math.floor(totalMinutes / 2);
              var halfHours = Math.floor(halfMinutes / 60);
              var halfMins = halfMinutes % 60;

              var Convert_Total_Timing =
                halfHours.toString().padStart(2, "0") +
                ":" +
                halfMins.toString().padStart(2, "0");
              $("#Total_Working_Hour").val(Convert_Total_Timing);

              var fromTotalMinutes =
                parseInt(fromParts[0]) * 60 + parseInt(fromParts[1]);
              var newToMinutes = fromTotalMinutes + halfMinutes;

              var newToHours = Math.floor(newToMinutes / 60) % 24;
              var newToMins = newToMinutes % 60;

              var Convert_To_Time =
                newToHours.toString().padStart(2, "0") +
                ":" +
                newToMins.toString().padStart(2, "0");
              $("#To_Time").val(Convert_To_Time);
            } else if (Working_Type == "Second Half") {
              $("#Total_OT_Hour").prop("readonly", true);

              var halfMinutes = Math.floor(totalMinutes / 2);
              var halfHours = Math.floor(halfMinutes / 60);
              var halfMins = halfMinutes % 60;

              var Convert_Total_Timing =
                halfHours.toString().padStart(2, "0") +
                ":" +
                halfMins.toString().padStart(2, "0");
              $("#Total_Working_Hour").val(Convert_Total_Timing);

              var toTotalMinutes =
                parseInt(toParts[0]) * 60 + parseInt(toParts[1]);
              var newFromMinutes = toTotalMinutes - halfMinutes;

              if (newFromMinutes < 0) {
                newFromMinutes += 24 * 60;
              }

              var newFromHours = Math.floor(newFromMinutes / 60) % 24;
              var newFromMins = newFromMinutes % 60;

              var Convert_From_Time =
                newFromHours.toString().padStart(2, "0") +
                ":" +
                newFromMins.toString().padStart(2, "0");
              $("#From_Time").val(Convert_From_Time);
            } else {
              $("#Total_OT_Hour").prop("readonly", false);
            }
          },
        });
      } else {
        var Total_OT_Hour = $("#Total_OT_Hour").val();
        var To_Time = $("#To_Time").val();
        var From_Time = $("#From_Time").val();

        // Add OT hours to To_Time
        var timeParts = To_Time.split(":");
        var hours = parseInt(timeParts[0], 10);
        var minutes = parseInt(timeParts[1], 10);
        var otHours = parseFloat(Total_OT_Hour);

        if (!isNaN(hours) && !isNaN(minutes) && !isNaN(otHours)) {
          hours += Math.floor(otHours);
          minutes += Math.round((otHours % 1) * 60);

          if (minutes >= 60) {
            hours += Math.floor(minutes / 60);
            minutes = minutes % 60;
          }

          hours = hours % 24;

          var newTime =
            (hours < 10 ? "0" : "") +
            hours +
            ":" +
            (minutes < 10 ? "0" : "") +
            minutes;

          $("#To_Time").val(newTime);

          // Now calculate total working hours from From_Time and updated To_Time
          var fromParts = From_Time.split(":");
          var fromHours = parseInt(fromParts[0], 10);
          var fromMinutes = parseInt(fromParts[1], 10);

          var toHours = hours;
          var toMinutes = minutes;

          var fromTotalMinutes = fromHours * 60 + fromMinutes;
          var toTotalMinutes = toHours * 60 + toMinutes;

          // Handle overnight shifts
          if (toTotalMinutes < fromTotalMinutes) {
            toTotalMinutes += 24 * 60;
          }

          var totalMinutesWorked = toTotalMinutes - fromTotalMinutes;
          var workedHours = Math.floor(totalMinutesWorked / 60);
          var workedMinutes = totalMinutesWorked % 60;
          var totalWorkedDecimal = workedHours + workedMinutes / 60;

          $("#Total_Working_Hour").val(totalWorkedDecimal.toFixed(2));
        }
      }
    });

    $("#Entry_Manual_Attendance").on("click", function () {
      let isValid = true;

      $(".form-control").removeClass("input-error");
      $(".error-text").remove();

      function showError(selector, message) {
        $(selector).addClass("input-error");
        $(selector).after('<div class="error-text">' + message + "</div>");
        isValid = false;
      }

      const date = $("#Date").val();
      const shift = $("#Shift").val();
      const workingType = $("#Working_Type").val();
      const employeeId = $("#Employee_Id").val();
      const punchingType = $("#Punching_Type").val();
      const fromTime = $("#From_Time").val();
      const toTime = $("#To_Time").val();
      const totalWorkingHour = $("#Total_Working_Hour").val();
      const totalOtHour = $("#Total_OT_Hour").val();
      const Supervisor = $("#Supervisor_Name").val();

      if (!date) showError("#Date", "Date is required.");
      if (!shift) showError("#Shift", "Shift is required.");
      if (!workingType) showError("#Working_Type", "Working type is required.");
      if (!employeeId) showError("#Employee_Id", "Employee ID is required.");
      if (!Supervisor)
        showError("#Supervisor_Name", "Please Vaild Supervisor is required.");
      if (!punchingType)
        showError("#Punching_Type", "Attendance type is required.");
      if (!fromTime) showError("#From_Time", "From Time is required.");
      if (!toTime) showError("#To_Time", "To Time is required.");
      if (!totalWorkingHour)
        showError("#Total_Working_Hour", "Total Working Hour is required.");


      if (!isValid) return;

      $.ajax({
        url: baseurl + "Employee/Manual_Attendance_Entry",
        type: "POST",
        data: {
          Date: date,
          Shift: shift,
          Working_Type: workingType,
          Employee_Id: employeeId,
          Punching_Type: punchingType,
          From_Time: fromTime,
          To_Time: toTime,
          Total_Working_Hour: totalWorkingHour,
          Total_OT_Hour: totalOtHour,
          Supervisor,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);

          swal({
            type: Response_Data.status,
            title: Response_Data.status === "success" ? "Success" : "Warning",
            text: Response_Data.message,
          });

          $("#Shift").empty();
          $("#Employee_Id").empty();
          $("#Supervisor_Name").empty();
          $("#Employee_Id").empty();
          $("#Total_OT_Hour").val("");

          $.ajax({
            url: baseurl + "Employee/Shift_Details",
            type: "POST",
            success: function (response) {
              var Response_Data = JSON.parse(response);
              var Shift_Details = Response_Data.Shift_Details;

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

              $.ajax({
                url: baseurl + "Employee/Shift_Timings",
                type: "POST",
                data: {
                  Shift: $("#Shift").val(),
                },
                success: function (response) {
                  $("#To_Time").val("");
                  $("#From_Time").val("");

                  var Response_Data = JSON.parse(response);
                  var Shift_Timings = Response_Data.Shift_Timings;

                  var From_Time = Shift_Timings[0].StartTime;
                  var To_Time = Shift_Timings[0].EndTime;

                  $("#From_Time").val(From_Time);
                  $("#To_Time").val(To_Time);

                  var fromParts = From_Time.split(":");
                  var toParts = To_Time.split(":");

                  var fromDate = new Date(
                    0,
                    0,
                    0,
                    parseInt(fromParts[0]),
                    parseInt(fromParts[1])
                  );
                  var toDate = new Date(
                    0,
                    0,
                    0,
                    parseInt(toParts[0]),
                    parseInt(toParts[1])
                  );

                  var diffMs = toDate - fromDate;
                  if (diffMs < 0) {
                    toDate.setDate(toDate.getDate() + 1);
                    diffMs = toDate - fromDate;
                  }

                  var hours = Math.floor(diffMs / 1000 / 60 / 60);
                  var minutes = Math.floor((diffMs / 1000 / 60) % 60);

                  var Total_Working_Time =
                    hours.toString().padStart(2, "0") +
                    ":" +
                    minutes.toString().padStart(2, "0");

                  $("#Total_Working_Hour").val(Total_Working_Time);
                },
              });

              $.ajax({
                url: baseurl + "Employee/Shift_Employee_List",
                type: "POST",
                data: {
                  Shift: $("#Shift").val(),
                  Date: $("#Date").val(),
                },
                success: function (response) {
                  var Response_Data = JSON.parse(response);
                  var Shift_Employee_List = Response_Data.Shift_Employee_List;

                  $("#Employee_Id").empty();

                  for (var i = 0; i < Shift_Employee_List.length; i++) {
                    var item = Shift_Employee_List[i];
                    var machineID = item.MachineID;
                    var firstName = item.FirstName;

                    $("#Employee_Id").append(
                      $("<option></option>")
                        .attr("value", machineID)
                        .text(machineID + " - " + firstName)
                    );
                  }
                },
              });
            },
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
              $("#Supervisor_Name")
                .empty()
                .append("<option value=''></option>");

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
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", status, error);
        },
      });
    });

    // Optional: Remove red border when user starts typing
    $(".form-control").on("input change", function () {
      $(this).removeClass("input-error");
      $(this).next(".error-text").remove();
    });

    $("#Date").on("change", function () {

      $.ajax({
        url: baseurl + "Employee/Shift_Employee_List",
        type: "POST",
        data: {
          Shift: $("#Shift").val(),
          Date: $("#Date").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Shift_Employee_List = Response_Data.Shift_Employee_List;

          $("#Employee_Id").empty();

          for (var i = 0; i < Shift_Employee_List.length; i++) {
            var item = Shift_Employee_List[i];
            var machineID = item.MachineID;
            var firstName = item.FirstName;

            $("#Employee_Id").append(
              $("<option></option>")
                .attr("value", machineID)
                .text(machineID + " - " + firstName)
            );
          }
        },
      });
    })




    //===========================================================================================================================//
  } else if (Punching_Attendance_Report == "Punching_Attendance_Report") {
    $("#Employee_Punching_List_Table_Section").hide();
    $("#Employee_Punching_LoginIN_DBtn").hide();

    var table = $("#Employee_Punching_Table").DataTable({
      // DataTable configurations
      paging: false,
      lengthChange: false,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: true,
    });

    $.ajax({
      url: baseurl + "Work/Shifts",
      type: "POST",
      success: function (response) {
        var responseData = JSON.parse(response);
        var Shifts = responseData.Shifts;

        var Shift = { "": "" };

        for (var i = 0; i < Shifts.length; i++) {
          var DName = Shifts[i];
          Shift[DName.ShiftDesc] = DName.ShiftDesc;
        }

        $("#Shift").empty();

        $.each(Shift, function (index, value) {
          $("#Shift").append(
            $("<option></option>").attr("value", value).text(value)
          );
        });

        $("#Shift option:eq(1)").prop("selected", true);

        var Punching_Type = $("#Punching_Type").val();
        var Date = $("#Date").val();
        var Shift = $("#Shift").val();
        $.ajax({
          url: baseurl + "Employee/Employee_Attendance",
          type: "POST",
          data: {
            Date,
            Shift,
            Punching_Type,
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Employee_Punching_List = Response_Data.Employee_Punching_List;

            if (Response_Data.Status == "Error") {
              swal({
                type: "warning",
                title: "Warning",
                text: Response_Data.Message,
              });

              $("#Employee_Punching_List_Table_Section").hide();
              $("#Employee_Punching_LoginIN_DBtn").hide();
              $("#Employee_Punching_LoginIn_Table tbody").empty();
            } else {
              $("#Employee_Punching_List_Table_Section").show();
              $("#Employee_Punching_LoginIN_DBtn").show();
              $("#Employee_Punching_LoginIn_Table tbody").empty();

              $.each(Employee_Punching_List, function (index, item) {
                var badgeClass =
                  item.Get_Type === "SHIFT"
                    ? "badge bg-success"
                    : "badge bg-warning";

                var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.DeptName}</td>
                        <td>${item.Wages}</td>
                        <td>${item.SubSection_Name}</td>
                        <td>${item.WorkArea}</td>
                        <td>${item.MachineID}</td>
                        <td>${item.FirstName}</td>
                        <td><span class="${badgeClass}">${item.Get_Type
                  }</span></td>
                    </tr>`;

                $("#Employee_Punching_LoginIn_Table tbody").append(row);
              });
            }
          },
        });
      },
    });

    $("#Date").on("change", function () {
      var Punching_Type = $("#Punching_Type").val();
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();
      $.ajax({
        url: baseurl + "Employee/Employee_Attendance",
        type: "POST",
        data: {
          Date,
          Shift,
          Punching_Type,
        },

        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Employee_Punching_List = Response_Data.Employee_Punching_List;

          if (Response_Data.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.Message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_LoginIN_DBtn").hide();
            $("#Employee_Punching_LoginIn_Table tbody").empty();
          } else {
            $("#Employee_Punching_LoginIn_Table tbody").empty();
            $("#Employee_Punching_List_Table_Section").show();
            $("#Employee_Punching_LoginIN_DBtn").show();

            $.each(Employee_Punching_List, function (index, item) {
              var badgeClass =
                item.Get_Type === "SHIFT"
                  ? "badge bg-success"
                  : "badge bg-warning";

              var row = `
                  <tr>
                      <td>${index + 1}</td>
                      <td>${item.DeptName}</td>
                      <td>${item.Wages}</td>
                      <td>${item.SubSection_Name}</td>
                      <td>${item.WorkArea}</td>
                      <td>${item.MachineID}</td>
                      <td>${item.FirstName}</td>
                      <td><span class="${badgeClass}">${item.Get_Type
                }</span></td>
                  </tr>`;

              $("#Employee_Punching_LoginIn_Table tbody").append(row);
            });
          }
        },
      });
    });

    $("#Shift").on("change", function () {
      var Punching_Type = $("#Punching_Type").val();
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();
      $.ajax({
        url: baseurl + "Employee/Employee_Attendance",
        type: "POST",
        data: {
          Date,
          Shift,
          Punching_Type,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Employee_Punching_List = Response_Data.Employee_Punching_List;

          if (Response_Data.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.Message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_LoginIN_DBtn").hide();
            $("#Employee_Punching_LoginIn_Table tbody").empty();
          } else {
            $("#Employee_Punching_List_Table_Section").show();
            $("#Employee_Punching_LoginIN_DBtn").show();
            $("#Employee_Punching_LoginIn_Table tbody").empty();

            $.each(Employee_Punching_List, function (index, item) {
              var badgeClass =
                item.Get_Type === "SHIFT"
                  ? "badge bg-success"
                  : "badge bg-warning";

              var row = `
                  <tr>
                      <td>${index + 1}</td>
                      <td>${item.DeptName}</td>
                      <td>${item.Wages}</td>
                      <td>${item.SubSection_Name}</td>
                      <td>${item.WorkArea}</td>
                      <td>${item.MachineID}</td>
                      <td>${item.FirstName}</td>
                      <td><span class="${badgeClass}">${item.Get_Type
                }</span></td>
                  </tr>`;

              $("#Employee_Punching_LoginIn_Table tbody").append(row);
            });
          }
        },
      });
    });

    $("#Punching_Type").on("change", function () {
      var Punching_Type = $("#Punching_Type").val();
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();
      $.ajax({
        url: baseurl + "Employee/Employee_Attendance",
        type: "POST",
        data: {
          Date,
          Shift,
          Punching_Type,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Employee_Punching_List = Response_Data.Employee_Punching_List;

          if (Response_Data.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.Message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_LoginIN_DBtn").hide();
            $("#Employee_Punching_LoginIn_Table tbody").empty();
          } else {
            $("#Employee_Punching_List_Table_Section").show();
            $("#Employee_Punching_LoginIN_DBtn").show();
            $("#Employee_Punching_LoginIn_Table tbody").empty();

            $.each(Employee_Punching_List, function (index, item) {
              var badgeClass =
                item.Get_Type === "SHIFT"
                  ? "badge bg-success"
                  : "badge bg-warning";

              var row = `
                  <tr>
                      <td>${index + 1}</td>
                      <td>${item.DeptName}</td>
                      <td>${item.Wages}</td>
                      <td>${item.SubSection_Name}</td>
                      <td>${item.WorkArea}</td>
                      <td>${item.MachineID}</td>
                      <td>${item.FirstName}</td>
                      <td><span class="${badgeClass}">${item.Get_Type
                }</span></td>
                  </tr>`;
              table.row.add($(row)[0]);
              $("#Employee_Punching_LoginIn_Table tbody").append(row);
            });
            table.draw();
          }
        },
      });
    });

    $("#Employee_Punching_LoginIN_DBtn").on("click", function () {
      var Punching_Type = $("#Punching_Type").val();
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();
      $.ajax({
        url: baseurl + "Reports/Employee_Punching_List_Download",
        type: "POST",
        data: {
          Date,
          Shift,
          Punching_Type,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Employee_Punching_List =
            Response_Data.Employee_Punching_List_Download;

          if (Response_Data.Status == "Error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.Message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_LoginIN_DBtn").hide();
            $("#Employee_Punching_LoginIn_Table tbody").empty();
          } else {
            if (Response_Data.file_url) {
              var link = document.createElement("a");
              link.href = Response_Data.file_url;
              link.download = currentDate + " LogIN Details.xlsx";
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
            } else {
              alert("Failed to generate the report");
            }
          }
        },
      });
    });

    //===========================================================================================================================//
  } else if (
    (Employee_Punching_List_Screen == "Employee_Punching_List_Screen")
  ) {
    $("#Employee_Punching_List_Table_Section").hide();

    $.ajax({
      url: baseurl + "Employee/Shift_Details",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Shift_Details = Response_Data.Shift_Details;

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
      },
    });



    $("#Employee_Punching_List_View").on("click", function () {
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Employee/Get_Punching_List",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Get_Punching_List = Response_Data.Get_Punching_List;

          if (Response_Data.status == "error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_List_Down_Btn").hide();
          } else {
            let allEmpty = true;
            let completedCount = 0;
            let missedCount = 0;
            let manualAttendanceCount = 0;

            $("#Employee_Punching_List_Table_Section").show();
            $("#Employee_Punching_List_Down_Btn").show();

            table.clear().draw();

            $.each(Get_Punching_List, function (index, item) {
              let hasDayIn = !!item.Day_In;
              let hasBreakOut = !!item.Break_Out;
              let hasBreakIn = !!item.Break_IN;
              let hasDayOut = !!item.Day_Out;

              let allPunchesComplete = hasDayIn && hasBreakOut && hasBreakIn && hasDayOut;
              let isManualAttendance = hasDayIn && hasDayOut && !hasBreakOut && !hasBreakIn;

              let nullCount = 0;
              if (!hasDayIn) nullCount++;
              if (!hasBreakOut) nullCount++;
              if (!hasBreakIn) nullCount++;
              if (!hasDayOut) nullCount++;

              if (hasDayIn || hasBreakOut || hasBreakIn || hasDayOut) {
                allEmpty = false;
              }

              if (allPunchesComplete) {
                completedCount++;
              } else if (isManualAttendance) {
                manualAttendanceCount++;
                missedCount++;
              } else if (nullCount > 0) {
                missedCount++;
              }

              let cellColor = (value) => {
                if (!value) return "#FD9393"; // red for missing
                if (allPunchesComplete) return "#93FD95"; // green
                if (isManualAttendance) return "orange"; // orange for manual
                return "#FD9393"; // red default
              };

              let statusBadge =
                nullCount === 0
                  ? `<span class="badge badge-success">Done</span>`
                  : `<span class="badge badge-danger">Not-Done</span>`;

              var row = `
            <tr>
              <td>${index + 1}</td>
              <td>${item.MachineID}</td>
              <td>${item.EmpName}</td>
              <td>${item.WorkArea}</td>
              <td style="background-color: ${cellColor(item.Day_In)};">${item.Day_In || ""}</td>
              <td style="background-color: ${cellColor(item.Break_Out)};">${item.Break_Out || ""}</td>
              <td style="background-color: ${cellColor(item.Break_IN)};">${item.Break_IN || ""}</td>
              <td style="background-color: ${cellColor(item.Day_Out)};">${item.Day_Out || ""}</td>
              <td>${statusBadge}</td>
            </tr>`;

              table.row.add($(row));
            });

            table.draw();

            $("#Completed_Punching_List").text("Completed Punching: " + completedCount);
            $("#Missed_Punching_List").text("Missed Punching: " + missedCount);
            $("#Manual_Attendance_List").text("Manual Attendance: " + manualAttendanceCount);

            if (allEmpty) {
              $("#Employee_Punching_List_Table_Section").hide();
              $("#Employee_Punching_List_Down_Btn").hide();

              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Employee Details Not Found..",
              });
            }
          }
        },
      });
    });






    $("#Employee_Punching_List_Down_Btn").on("click", function () {
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Reports/Employee_Punching_List_Download_Login",
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
            link.download = currentDate + " Employee Punching Details.xlsx";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    });



  } else if (Employee_Punching_List_Details_Screen == 'Employee_Punching_List_Details_Screen') {


    $("#Employee_Punching_List_Table_Section").hide();


    $.ajax({
      url: baseurl + "Employee/Shift_Details",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Shift_Details = Response_Data.Shift_Details;

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
      },
    });


    $("#Employee_Punching_List_View").on("click", function () {
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Employee/Get_Punching_List_Details",
        type: "POST",
        data: {
          Date,
          Shift,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Get_Punching_List = Response_Data.Get_Punching_List_Details;

          if (Response_Data.status == "error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.message,
            });

            $("#Employee_Punching_List_Table_Section").hide();
            $("#Employee_Punching_List_Down_Btn").hide();
          } else {
            let allEmpty = true;
            let completedCount = 0;
            let missedCount = 0;
            let manualAttendanceCount = 0;

            $("#Employee_Punching_List_Table_Section").show();
            $("#Employee_Punching_List_Down_Btn").show();

            table.clear().draw();

            $.each(Get_Punching_List, function (index, item) {
              let hasDayIn = !!item.Day_In;
              let hasBreakOut = !!item.Break_Out;
              let hasBreakIn = !!item.Break_IN;
              let hasDayOut = !!item.Day_Out;

              let allPunchesComplete = hasDayIn && hasBreakOut && hasBreakIn && hasDayOut;
              let isManualAttendance = hasDayIn && hasDayOut && !hasBreakOut && !hasBreakIn;

              let nullCount = 0;
              if (!hasDayIn) nullCount++;
              if (!hasBreakOut) nullCount++;
              if (!hasBreakIn) nullCount++;
              if (!hasDayOut) nullCount++;

              if (hasDayIn || hasBreakOut || hasBreakIn || hasDayOut) {
                allEmpty = false;
              }

              if (allPunchesComplete) {
                completedCount++;
              } else if (isManualAttendance) {
                manualAttendanceCount++;
                missedCount++;
              } else if (nullCount > 0) {
                missedCount++;
              }

              let rowBgColor = "";
              if (allPunchesComplete) {
                rowBgColor = "background-color: #93FD95;";
              } else if (isManualAttendance) {
                rowBgColor = "background-color: orange;";
              } else {
                rowBgColor = "background-color: #FD9393;";
              }

              let cellColor = nullCount === 0 ? "#93FD95" : "#FD9393";
              let statusBadge =
                nullCount === 0
                  ? `<span class="badge badge-success">Done</span>`
                  : `<span class="badge badge-danger">Not-Done</span>`;

              var row = `
            <tr style="${rowBgColor}">
              <td>${index + 1}</td>
              <td>${item.MachineID}</td>
              <td>${item.EmpName}</td>
              <td>${item.WorkArea}</td>
              <td style="background-color: ${cellColor};">${item.Day_In || ""}</td>
              <td style="background-color: ${cellColor};">${item.Break_Out || ""}</td>
              <td style="background-color: ${cellColor};">${item.Break_IN || ""}</td>
              <td style="background-color: ${cellColor};">${item.Day_Out || ""}</td>
              <td>${statusBadge}</td>
            </tr>`;

              table.row.add($(row));
            });

            table.draw();

            $("#Completed_Punching_List").text("Completed Punching: " + completedCount);
            $("#Missed_Punching_List").text("Missed Punching: " + missedCount);
            $("#Manual_Attendance_List").text("Manual Attendance: " + manualAttendanceCount);

            if (allEmpty) {
              $("#Employee_Punching_List_Table_Section").hide();
              $("#Employee_Punching_List_Down_Btn").hide();

              swal({
                type: "warning",
                title: "Warning",
                text: "Shift Not Starting Employee Details Not Found..",
              });
            }
          }
        },
      });
    });






    // $("#Employee_Punching_List_View").on("click", function () {
    //   var Date = $("#Date").val();
    //   var Shift = $("#Shift").val();

    //   $.ajax({
    //     url: baseurl + "Employee/Get_Punching_List_Details",
    //     type: "POST",
    //     data: {
    //       Date,
    //       Shift,
    //     },
    //     success: function (response) {
    //       var Response_Data = JSON.parse(response);
    //       var Get_Punching_List = Response_Data.Get_Punching_List_Details;

    //       if (Response_Data.status == "error") {
    //         swal({
    //           type: "warning",
    //           title: "Warning",
    //           text: Response_Data.message,
    //         });

    //         $("#Employee_Punching_List_Table_Section").hide();
    //         $("#Employee_Punching_List_Down_Btn").hide();
    //       } else {
    //         let allEmpty = true;
    //         let completedCount = 0;
    //         let missedCount = 0;
    //         let manualAttendanceCount = 0;

    //         $("#Employee_Punching_List_Table_Section").show();
    //         $("#Employee_Punching_List_Down_Btn").show();

    //         table.clear().draw();

    //         $.each(Get_Punching_List, function (index, item) {
    //           let nullCount = 0;

    //           if (!item.Day_In) nullCount++;
    //           if (!item.Break_IN) nullCount++;
    //           if (!item.Break_Out) nullCount++;
    //           if (!item.Day_Out) nullCount++;

    //           if (nullCount === 0) {
    //             completedCount++;
    //             return true;
    //           }

    //           missedCount++;

    //           let isManualAttendance = item.Day_In && item.Day_Out && !item.Break_IN && !item.Break_Out;
    //           if (isManualAttendance) {
    //             manualAttendanceCount++;
    //           }

    //           let rowBgColor = isManualAttendance ? "background-color: orange;" : "";
    //           let cellColor = nullCount === 0 ? "#93FD95" : "#FD9393";
    //           let statusBadge =
    //             nullCount === 0
    //               ? `<span class="badge badge-success">Done</span>`
    //               : `<span class="badge badge-danger">Not-Done</span>`;

    //           var row = `
    //         <tr style="${rowBgColor}">
    //           <td class="serial-number"></td>
    //           <td>${item.MachineID}</td>
    //           <td>${item.EmpName}</td>
    //           <td>${item.WorkArea}</td>
    //           <td style="background-color: ${cellColor};">${item.Day_In || ""}</td>
    //           <td style="background-color: ${cellColor};">${item.Break_Out || ""}</td>
    //           <td style="background-color: ${cellColor};">${item.Break_IN || ""}</td>
    //           <td style="background-color: ${cellColor};">${item.Day_Out || ""}</td>
    //           <td>${statusBadge}</td>
    //         </tr>`;

    //           table.row.add($(row));
    //         });

    //         table.rows().every(function (rowIdx) {
    //           $(this.node()).find('.serial-number').text(rowIdx + 1);
    //         });

    //         table.draw();

    //         $("#Completed_Punching_List").text("Completed Punching : " + completedCount);
    //         $("#Missed_Punching_List").text("Missed Punching : " + missedCount);
    //         $("#Manual_Attendance_List").text("Manual Attendance : " + manualAttendanceCount);

    //         if (missedCount === 0) {
    //           $("#Employee_Punching_List_Table_Section").hide();
    //           $("#Employee_Punching_List_Down_Btn").hide();

    //           swal({
    //             type: "warning",
    //             title: "Warning",
    //             text: "No Missing Punching Data Found...",
    //           });
    //         }
    //       }
    //     },
    //   });
    // });


    $("#Employee_Punching_List_Down_Btn").on("click", function () {
      var Date = $("#Date").val();
      var Shift = $("#Shift").val();

      $.ajax({
        url: baseurl + "Reports/Employee_Punching_List_Download_Login_Det",
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
            link.download = currentDate + " Employee Punching Details.xlsx";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          } else {
            alert("Failed to generate the report");
          }
        },
      });
    });




  }


});
