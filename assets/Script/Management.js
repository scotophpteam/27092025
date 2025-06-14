$(document).ready(function () {
  var Page = $("#Employee_Leave_Apply_Screen").val();
  var Employee_Leave_Apply_List_Screen = $(
    "#Employee_Leave_Apply_List_Screen"
  ).val();

  if (Page == "Employee_Leave_Apply_Screen") {
    var currentDate = new Date().toISOString().split("T")[0];
    $("#Date").val(currentDate);
    $("#Date").attr("max", currentDate);

    $.ajax({
      url: baseurl + "Management/Employee_List",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Employee_List = Response_Data.Employee_List;

        $("#Employee_Id").empty();

        for (var i = 0; i < Employee_List.length; i++) {
          var item = Employee_List[i];
          var machineID = item.EmpNo;
          var firstName = item.FirstName;

          $("#Employee_Id").append(
            $("<option></option>")
              .attr("value", machineID)
              .text(machineID + " - " + firstName)
          );
        }
      },
    });

    $("#Employee_Leave_Apply").on("click", function () {
      $("#Employee_Id_error").text("");
      $("#From_Date_error").text("");
      $("#To_Date_error").text("");
      $("#Remarks_error").text("");

      let isValid = true;

      if ($("#Employee_Id").val() === "") {
        $("#Employee_Id_error").text("Please select an employee.");
        isValid = false;
      }

      const fromDate = $("#From_Date").val();
      if (!fromDate) {
        $("#From_Date_error").text("Please select a from date.");
        isValid = false;
      }

      const toDate = $("#To_Date").val();
      if (!toDate) {
        $("#To_Date_error").text("Please select a to date.");
        isValid = false;
      }

      if (fromDate && toDate && toDate < fromDate) {
        $("#To_Date_error").text("To Date cannot be before From Date.");
        isValid = false;
      }

      if ($("#Remarks").val().trim() === "") {
        $("#Remarks_error").text("Remarks is required.");
        isValid = false;
      }

      if (isValid) {
        var Applied_Date = currentDate;
        var Employee_ID = $("#Employee_Id").val();
        var From_Date = $("#From_Date").val();
        var To_Date = $("#To_Date").val();
        var Remarks = $("#Remarks").val();

        $.ajax({
          url: baseurl + "Management/Leave_Apply",
          type: "POST",
          data: {
            Applied_Date,
            Employee_ID,
            From_Date,
            To_Date,
            Remarks,
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Applied_Status = Response_Data.Apply_Leave;

            if (Response_Data.status == 'error') {

              swal({
                type: "warning",
                title: "warning",
                text: Response_Data.message,
              });

              $("#Employee_Id").val("");
              $("#From_Date").val("");
              $("#To_Date").val("");
              $("#Remarks").val("");

              $.ajax({
                url: baseurl + "Management/Employee_List",
                type: "POST",
                success: function (response) {
                  var Response_Data = JSON.parse(response);
                  var Employee_List = Response_Data.Employee_List;

                  $("#Employee_Id").empty();

                  for (var i = 0; i < Employee_List.length; i++) {
                    var item = Employee_List[i];
                    var machineID = item.EmpNo;
                    var firstName = item.FirstName;

                    $("#Employee_Id").append(
                      $("<option></option>")
                        .attr("value", machineID)
                        .text(machineID + " - " + firstName)
                    );
                  }
                },
              });
            } else {

                swal({
                  type: "success",
                  title: "success",
                  text: Response_Data.message,
                });

                $("#Employee_Id").val("");
                $("#From_Date").val("");
                $("#To_Date").val("");
                $("#Remarks").val("");

                $.ajax({
                  url: baseurl + "Management/Employee_List",
                  type: "POST",
                  success: function (response) {
                    var Response_Data = JSON.parse(response);
                    var Employee_List = Response_Data.Employee_List;

                    $("#Employee_Id").empty();

                    for (var i = 0; i < Employee_List.length; i++) {
                      var item = Employee_List[i];
                      var machineID = item.EmpNo;
                      var firstName = item.FirstName;

                      $("#Employee_Id").append(
                        $("<option></option>")
                          .attr("value", machineID)
                          .text(machineID + " - " + firstName)
                      );
                    }
                  },
                });


            }
          },
        });
      }
    });
  }else if (Employee_Leave_Apply_List_Screen == "Employee_Leave_Apply_List_Screen") {

    $("#Employee_Leave_Apply_List_Section").hide();
    $("#Employee_Leave_Apply_List_Table tbody").empty();

    var currentDate = new Date().toISOString().split("T")[0];
    $("#From_Date").val(currentDate);
    $("#From_Date").attr("max", currentDate);

    var From_Date = $("#From_Date").val();
    var To_Date = $("#To_Date").val();

    $.ajax({
      url: baseurl + "Management/Leave_Apply_List",
      type: "POST",
      data: {
            From_Date,
            To_Date
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Leave_Apply_List = Response_Data.Leave_Apply_List;

        if(Response_Data.status == 'error'){

            swal({
              type: "warning",
              title: "warning",
              text: Response_Data.message,
            });

            $("#Employee_Leave_Apply_List_Section").hide();
            $("#Employee_Leave_Apply_List_Table tbody").empty();

        } else {

            if (
              $.fn.DataTable.isDataTable("#Employee_Leave_Apply_List_Table")
            ) {
              $("#Employee_Leave_Apply_List_Table")
                .DataTable()
                .clear()
                .destroy();
            }

            $("#Employee_Leave_Apply_List_Section").show();
            $("#Employee_Leave_Apply_List_Table tbody").empty();

            $.each(Leave_Apply_List, function (index, item) {
              var badgeClass =
                item.Approved_Status === "Success"
                  ? "badge bg-success"
                  : "badge bg-warning";

              var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.Applied_Date}</td>
                        <td>${item.Sub_Division}</td>
                        <td>${item.Position}</td>
                        <td>${item.EmpNo}</td>
                        <td>${item.FirstName}</td>
                        <td>${item.From_Date}</td>
                        <td>${item.To_Date}</td>
                        <td><span class="${badgeClass}">${
                item.Approved_Status
              }</span></td>
                    </tr>`;

              $("#Employee_Leave_Apply_List_Table tbody").append(row);
            });

            $("#Employee_Leave_Apply_List_Table").DataTable({
              lengthChange: true,
              searching: true,
              ordering: true,
              info: true,
              autoWidth: true,
            });



        }



      },
    });


    $("#Applied_Employee_List_Leave").on("click",function(){


        var From_Date = $("#From_Date").val();
        var To_Date = $("#To_Date").val();

        $.ajax({
          url: baseurl + "Management/Leave_Apply_List",
          type: "POST",
          data: {
            From_Date,
            To_Date,
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Leave_Apply_List = Response_Data.Leave_Apply_List;

            if (Response_Data.status == "error") {
              swal({
                type: "warning",
                title: "warning",
                text: Response_Data.message,
              });

              $("#Employee_Leave_Apply_List_Section").hide();
              $("#Employee_Leave_Apply_List_Table tbody").empty();
            } else {
              $("#Employee_Leave_Apply_List_Section").show();
              $("#Employee_Leave_Apply_List_Table tbody").empty();

              if (
                $.fn.DataTable.isDataTable("#Employee_Leave_Apply_List_Table")
              ) {
                $("#Employee_Leave_Apply_List_Table")
                  .DataTable()
                  .clear()
                  .destroy();
              }

              $.each(Leave_Apply_List, function (index, item) {
                var badgeClass =
                  item.Approved_Status === "Success"
                    ? "badge bg-success"
                    : "badge bg-warning";

                var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.Applied_Date}</td>
                            <td>${item.Sub_Division}</td>
                            <td>${item.Position}</td>
                            <td>${item.EmpNo}</td>
                            <td>${item.FirstName}</td>
                            <td>${item.From_Date}</td>
                            <td>${item.To_Date}</td>
                            <td><span class="${badgeClass}">${
                  item.Approved_Status
                }</span></td>
                        </tr>`;

                $("#Employee_Leave_Apply_List_Table tbody").append(row);
              });

              $("#Employee_Leave_Apply_List_Table").DataTable({
               
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: true,
              });
            }
          },
        });

    });









  }
});
