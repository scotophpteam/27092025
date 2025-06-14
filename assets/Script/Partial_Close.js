$(document).ready(function () {

  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);
  

  $("#Partial_Closing_Table_Section").hide();

  var table = $("#Partial_Closing_Table").DataTable({
    // DataTable configurations
    paging: false,
    lengthChange: false,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: true,
  });

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
        url: baseurl + "Shift_Closing/Assigned_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);

          var Assigned_Employee_List = Response_Data.Assigned_Employee_List;

          if (Response_Data.status == "error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.mesaage,
            });

            $("#Partial_Closing_Table_Section").hide();
          } else {

            table.clear().draw();
            $("#Partial_Closing_Table tbody").empty();

            $.each(Assigned_Employee_List, function (index, item) {
              var row = `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.WorkArea}</td>
                  <td class="Employee_Id">${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td><input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning close-btn">Close</button>
                  </td>
                </tr>
              `;
              $("#Partial_Closing_Table tbody").append(row);
              table.row.add($(row)).draw();
            });

            $("#Partial_Closing_Table_Section").show();
          }
        },
      });

      $("#Partial_Closing_Table tbody").on("click", ".close-btn", function () {
        const $row = $(this).closest("tr");
        var Reason = $row.find(".Description").val();
        var Employee_Id = $row.find(".Employee_Id").text();

        if (Reason == "") {
          var Error = "Please Enter Vaild Reason..!";

          swal(Error);
        } else {
          var Date = $("#Date").val();
          var Shift = $("#Shift").val();
          var Supervisor = $("#Supervisor_Name").val();

          $.ajax({
            url: baseurl + "Shift_Closing/Partial_Closing",
            type: "POST",
            data: {
              Date,
              Shift,
              Employee_Id,
              Supervisor,
              Reason,
            },
            success: function (response) {
              var Response_Data = JSON.parse(response);

              var Assigned_Employee_List = Response_Data.Assigned_Employee_List;

              if (Response_Data.status == "error") {
                swal({
                  type: "warning",
                  title: "Warning",
                  text: Response_Data.message,
                });

                $("#Partial_Closing_Table_Section").hide();
              } else {
                swal({
                  type: "success",
                  title: "Updated",
                  text: "Partial closing has been updated successfully!",
                });

                table.clear().draw();
                $("#Partial_Closing_Table tbody").empty();

                $.each(Assigned_Employee_List, function (index, item) {
                  var row = `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.WorkArea}</td>
                  <td class="Employee_Id">${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td><input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning close-btn">Close</button>
                  </td>
                </tr>
              `;
                  $("#Partial_Closing_Table tbody").append(row);
                  table.row.add($(row)).draw();
                });

                $("#Partial_Closing_Table_Section").show();
              }
            },
          });
        }
      });
    },
  });

  $("#Date").on("change",function(){

    $.ajax({
      url: baseurl + "Shift_Closing/Assigned_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);

        var Assigned_Employee_List = Response_Data.Assigned_Employee_List;

        if (Response_Data.status == "error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Response_Data.mesaage,
          });

          $("#Partial_Closing_Table_Section").hide();
        } else {

          table.clear().draw();
          $("#Partial_Closing_Table tbody").empty();

          $.each(Assigned_Employee_List, function (index, item) {
            var row = `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.WorkArea}</td>
                  <td class="Employee_Id">${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td><input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning close-btn">Close</button>
                  </td>
                </tr>
              `;
            $("#Partial_Closing_Table tbody").append(row);
            table.row.add($(row)).draw();
          });

          $("#Partial_Closing_Table_Section").show();
        }

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

            $("#Sub_Section").append(
              "<option value='All' selected>All</option>"
            );

            $.each(Get_Sub_Sections, function (key, value) {
              $("#Sub_Section").append(
                $("<option></option>").attr("value", key).text(value)
              );
            });
          },
        });
      },
    });

    

  })


  $("#Shift").on("change", function () {

    $.ajax({
      url: baseurl + "Shift_Closing/Assigned_Employee_List",
      type: "POST",
      data: {
        Date: $("#Date").val(),
        Shift: $("#Shift").val(),
        Type: $("#Type").val(),
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);

        var Assigned_Employee_List = Response_Data.Assigned_Employee_List;

        if (Response_Data.status == "error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Response_Data.mesaage,
          });

          $("#Partial_Closing_Table_Section").hide();
        } else {

          table.clear().draw();
          $("#Partial_Closing_Table tbody").empty();

          $.each(Assigned_Employee_List, function (index, item) {
            var row = `
                <tr>
                  <td>${index + 1}</td>
                  <td>${item.WorkArea}</td>
                  <td class="Employee_Id">${item.EmpNo}</td>
                  <td>${item.FirstName}</td>
                  <td><input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning close-btn">Close</button>
                  </td>
                </tr>
              `;
            $("#Partial_Closing_Table tbody").append(row);
            table.row.add($(row)).draw();
          });

          $("#Partial_Closing_Table_Section").show();
        }

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

            $("#Sub_Section").append(
              "<option value='All' selected>All</option>"
            );

            $.each(Get_Sub_Sections, function (key, value) {
              $("#Sub_Section").append(
                $("<option></option>").attr("value", key).text(value)
              );
            });
          },
        });
      },
    });
  });



  $("#Sub_Section").on("change", function () {
    var Sub_Section = $("#Sub_Section").val();
    var table = $("#Partial_Closing_Table").DataTable();
    table.clear().draw();

    if (Sub_Section == "All") {
      $.ajax({
        url: baseurl + "Shift_Closing/Assigned_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Assigned_Employee_List = Response_Data.Assigned_Employee_List;

          if (Response_Data.status == "error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.mesaage,
            });
            table.clear().draw();
            $("#Partial_Closing_Table_Section").hide();
          } else {
            $.each(Assigned_Employee_List, function (index, item) {
              var row = [
                index + 1,
                item.WorkArea,
                `<span class="Employee_Id">${item.EmpNo}</span>`,
                item.FirstName,
                `<input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;">`,
                `<button type="button" class="btn btn-sm btn-warning close-btn">Close</button>`,
              ];
              table.row.add(row);
            });
            table.draw();
            $("#Partial_Closing_Table_Section").show();
          }
        },
      });
    } else {
      $.ajax({
        url: baseurl + "Shift_Closing/Seperated_Assigned_Employee_List",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
          Type: $("#Type").val(),
          Sub_Section: Sub_Section,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Seperated_Assigned_Employee_List =
            Response_Data.Seperated_Assigned_Employee_List;

          if (Response_Data.status == "error") {
            swal({
              type: "warning",
              title: "Warning",
              text: Response_Data.mesaage,
            });
            table.clear().draw();
            $("#Partial_Closing_Table_Section").hide();
          } else {
            $.each(Seperated_Assigned_Employee_List, function (index, item) {
              var row = [
                index + 1,
                item.WorkArea,
                `<span class="Employee_Id">${item.EmpNo}</span>`,
                item.FirstName,
                `<input type="text" class="form-control Description" id="Description_${index}" style="width: 320px; height: 40px;">`,
                `<button type="button" class="btn btn-sm btn-warning close-btn">Close</button>`,
              ];
              table.row.add(row);
            });
            table.draw();
            $("#Partial_Closing_Table_Section").show();
          }
        },
      });
    }
  });
  





});
