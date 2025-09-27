$(document).ready(function () {



  var currentDate = new Date().toISOString().split("T")[0];
  $("#Date").val(currentDate);
  $("#Date").attr("max", currentDate);



  var Page_Name = $("#Page_Name").val();

  if (Page_Name == 'Home_Page_Dashboard') {

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
          url: baseurl + "Master/Standard_Actual_List",
          type: "POST",
          data: {
            Date: $("#Date").val(),
            Shift: $("#Shift").val(),
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Standard_Actual_List = Response_Data.Standard_Actual_List;

            $("#Standard_Actual_Table_Section").show();
            let totalStandard = 0;
            let totalActual = 0;

            $.each(Standard_Actual_List, function (index, item) {
              totalStandard += parseFloat(item.Standard) || 0;
              totalActual += parseFloat(item.Actual) || 0;

              var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.Position}</td>
                        <td>${item.Standard}</td>
                        <td>${item.Actual}</td>
                        <td>
                            ${
                              item.Status === "Active"
                                ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
                                : item.Status
                            }
                        </td>
                    </tr>
                `;
              $("#Standard_Actual_Table tbody").append(row);
            });

            // Append totals row
            let totalRow = `
      <tr style="background-color: rgb(147, 255, 226);">
          <td colspan="2" class="text-right" style="font-weight: bold;">Total</td>
          <td style="font-weight: bold;">${totalStandard}</td>
          <td style="font-weight: bold;">${totalActual}</td>
          <td></td>
      </tr>
  `;


            $("#Standard_Actual_Table tbody").append(totalRow);


            $("#Standard_Actual_Table").DataTable({
              paging: false,
              searching: true,
              ordering: true,
              info: true,
            });
          },
        });


        $.ajax({
          url: baseurl + "Master/Sub_Section_Employee_Count",
          type: "POST",
          data: {
            Date: $("#Date").val(),
            Shift: $("#Shift").val(),
          },
          success: function (response) {
            var Response_Data = JSON.parse(response);
            var Sub_Section_Employee_Count =
              Response_Data.Sub_Section_Employee_Count;
              $("#Sub_Division_Wise_Employee_List tbody").empty();

              let totalCount = 0;

              $.each(Sub_Section_Employee_Count, function (index, item) {
                totalCount += parseInt(item.SubSection_Count);

                var row = `
                      <tr>
                          <td>${index + 1}</td>
                          <td>${item.WorkArea}</td>
                          <td>${item.SubSection_Name}</td>
                          <td>${item.SubSection_Count}</td>
                      </tr>
                  `;
                $("#Sub_Division_Wise_Employee_List tbody").append(row);
              });

              var totalRow = `
      <tr style="background-color: rgb(147, 255, 226);">
          <td colspan="3" style="text-align: right; font-weight: bold;">Total Employees</td>
          <td style="font-weight: bold;">${totalCount}</td>
      </tr>
  `;


            $("#Sub_Division_Wise_Employee_List tbody").append(totalRow);




          },
        });


        $.ajax({
          url: baseurl + "Master/Employee_Home_Page",
          type: "POST",
          data: {
              Date: $("#Date").val(),
              Shift: $("#Shift").val()
          },
          success: function (data) {

            const responseData = JSON.parse(data);

            const homeData = responseData.Employee_Home_Page;

            const actualComers = parseInt(homeData.Actual_Comers) || 0;
            const lateComers = parseInt(homeData.Late_Comers) || 0;
            const totalAllocated = parseInt(homeData.Total_Allocated_Count) || 0;
            const lateAllocated = parseInt(homeData.Late_Total_Allocated_Count) || 0;
            const totalEngaged = actualComers + lateComers;

            // Hide chart if all values are zero
            if (actualComers === 0 && lateComers === 0 && totalAllocated === 0 && lateAllocated === 0) {
                $("#Chart_View_Employee_Count").hide();
                return;
            }

            $("#Chart_View_Employee_Count").show();

            const engagementData = [
                actualComers,
                totalEngaged,
                lateComers,
                totalAllocated,
                lateAllocated
            ];

            const engagementLabels = [
                'Total Punched Employee',
                'Total Engaged Employee',
                'Total Late Punched Employee',
                'Total Allocated Employee',
                'Allocated Late Employee'
            ];

            const dataForChart = {
                labels: engagementLabels,
                datasets: [{
                    label: 'Employee Counts',
                    data: engagementData,
                    backgroundColor: [
                        'rgb(43, 255, 0)',      // Green
                        'rgb(4, 0, 255)',       // Blue
                        'rgba(255, 0, 0, 0.8)', // Red
                        'rgb(255, 165, 0)',     // Orange
                        'rgb(128, 0, 128)'      // Purple
                    ],
                    borderColor: [
                        'rgb(43, 255, 0)',
                        'rgb(4, 0, 255)',
                        'rgba(255, 0, 0, 0.8)',
                        'rgb(255, 165, 0)',
                        'rgb(128, 0, 128)'
                    ],
                    borderWidth: 1
                }]
            };

            const options = {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 8
                        },
                        formatter: function (value, context) {
                            const label = context.chart.data.labels[context.dataIndex];
                            return `${label}: ${value}`;
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'No. of Employees'
                        }
                    }
                }
            };

            const ctx = document.getElementById('myBarChart').getContext('2d');

            if (window.myBarChart instanceof Chart) {
                window.myBarChart.destroy();
            }

            window.myBarChart = new Chart(ctx, {
                type: 'bar',
                data: dataForChart,
                options: options,
                plugins: [ChartDataLabels]
            });
        }
        ,


          error: function () {
              console.error("Failed to fetch data from server.");
          }
      });







      }
    })



        $("#Shift").on("change", function () {
      $.ajax({
        url: baseurl + "Master/Sub_Section_Employee_Count",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Sub_Section_Employee_Count =
            Response_Data.Sub_Section_Employee_Count;
          $("#Sub_Division_Wise_Employee_List tbody").empty();

          let totalCount = 0;

          $.each(Sub_Section_Employee_Count, function (index, item) {
            totalCount += parseInt(item.SubSection_Count);

            var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.WorkArea}</td>
                        <td>${item.SubSection_Name}</td>
                        <td>${item.SubSection_Count}</td>
                    </tr>
                `;
            $("#Sub_Division_Wise_Employee_List tbody").append(row);
          });

          var totalRow = `
            <tr style="font-weight: bold; background-color:rgb(147, 255, 226);">
                <td colspan="3" style="text-align: right; font-weight: bold;">Total Employees</td>
                <td>${totalCount}</td>
            </tr>
        `;

          $("#Sub_Division_Wise_Employee_List tbody").append(totalRow);



        $.ajax({
          url: baseurl + "Master/Employee_Home_Page",
          type: "POST",
          data: {
              Date: $("#Date").val(),
              Shift: $("#Shift").val()
          },
          success: function (data) {

            const responseData = JSON.parse(data);

            const homeData = responseData.Employee_Home_Page;

            const actualComers = parseInt(homeData.Actual_Comers) || 0;
            const lateComers = parseInt(homeData.Late_Comers) || 0;
            const totalAllocated = parseInt(homeData.Total_Allocated_Count) || 0;
            const lateAllocated = parseInt(homeData.Late_Total_Allocated_Count) || 0;
            const totalEngaged = actualComers + lateComers;

            // Hide chart if all values are zero
            if (actualComers === 0 && lateComers === 0 && totalAllocated === 0 && lateAllocated === 0) {
                $("#Chart_View_Employee_Count").hide();
                return;
            }

            $("#Chart_View_Employee_Count").show();

            const engagementData = [
                actualComers,
                totalEngaged,
                lateComers,
                totalAllocated,
                lateAllocated
            ];

            const engagementLabels = [
                'Total Punched Employee',
                'Total Engaged Employee',
                'Total Late Punched Employee',
                'Total Allocated Employee',
                'Allocated Late Employee'
            ];

            const dataForChart = {
                labels: engagementLabels,
                datasets: [{
                    label: 'Employee Counts',
                    data: engagementData,
                    backgroundColor: [
                        'rgb(43, 255, 0)',      // Green
                        'rgb(4, 0, 255)',       // Blue
                        'rgba(255, 0, 0, 0.8)', // Red
                        'rgb(255, 165, 0)',     // Orange
                        'rgb(128, 0, 128)'      // Purple
                    ],
                    borderColor: [
                        'rgb(43, 255, 0)',
                        'rgb(4, 0, 255)',
                        'rgba(255, 0, 0, 0.8)',
                        'rgb(255, 165, 0)',
                        'rgb(128, 0, 128)'
                    ],
                    borderWidth: 1
                }]
            };

            const options = {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 8
                        },
                        formatter: function (value, context) {
                            const label = context.chart.data.labels[context.dataIndex];
                            return `${label}: ${value}`;
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'No. of Employees'
                        }
                    }
                }
            };

            const ctx = document.getElementById('myBarChart').getContext('2d');

            if (window.myBarChart instanceof Chart) {
                window.myBarChart.destroy();
            }

            window.myBarChart = new Chart(ctx, {
                type: 'bar',
                data: dataForChart,
                options: options,
                plugins: [ChartDataLabels]
            });
        }
        ,


          error: function () {
              console.error("Failed to fetch data from server.");
          }
      });



          var Selected_Date = $("#Date").val();
          var Shift = $("#Shift").val();

          $.ajax({
            url: baseurl + "Master/Standard_Actual_List",
            type: "POST",
            data: {
              Date: Selected_Date,
              Shift,
            },
            success: function (response) {
              var Response_Data = JSON.parse(response);
              var Standard_Actual_List = Response_Data.Standard_Actual_List;

              $("#Standard_Actual_Table_Section").show();
              $("#Standard_Actual_Table tbody").empty();
              let totalStandard = 0;
              let totalActual = 0;

              $.each(Standard_Actual_List, function (index, item) {
                totalStandard += parseFloat(item.Standard) || 0;
                totalActual += parseFloat(item.Actual) || 0;

                var row = `
                      <tr>
                       <td>${index + 1}</td>
                            <td>${item.Position}</td>
                            <td>${item.Standard}</td>
                            <td>${item.Actual}</td>
                          <td>
                              ${
                                item.Status === "Active"
                                  ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
                                  : item.Status
                              }
                          </td>
                      </tr>
                  `;
                $("#Standard_Actual_Table tbody").append(row);
              });

              // Append totals row
              let totalRow = `
          <tr style="background-color: rgb(147, 255, 226);">
              <td colspan="2" class="text-right" style="font-weight: bold;">Total</td>
              <td style="font-weight: bold;">${totalStandard}</td>
              <td style="font-weight: bold;">${totalActual}</td>
              <td></td>
          </tr>
      `;


              $("#Standard_Actual_Table tbody").append(totalRow);


              $("#Standard_Actual_Table_Final").DataTable({
                paging: false,
                searching: true,
                ordering: true,
                info: true,
              });
            },
          });







        },
      });
    });

    $("#Date").on("change", function () {
      $.ajax({
        url: baseurl + "Master/Sub_Section_Employee_Count",
        type: "POST",
        data: {
          Date: $("#Date").val(),
          Shift: $("#Shift").val(),
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Sub_Section_Employee_Count =
            Response_Data.Sub_Section_Employee_Count;
          $("#Sub_Division_Wise_Employee_List tbody").empty();

          let totalCount = 0;

          $.each(Sub_Section_Employee_Count, function (index, item) {
            totalCount += parseInt(item.SubSection_Count);

            var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.WorkArea}</td>
                        <td>${item.SubSection_Name}</td>
                        <td>${item.SubSection_Count}</td>
                    </tr>
                `;
            $("#Sub_Division_Wise_Employee_List tbody").append(row);
          });

          var totalRow = `
            <tr style="font-weight: bold; background-color:rgb(147, 255, 226);">
                <td colspan="3" style="text-align: right; font-weight: bold;">Total Employees</td>
                <td>${totalCount}</td>
            </tr>
        `;

          $("#Sub_Division_Wise_Employee_List tbody").append(totalRow);



        $.ajax({
          url: baseurl + "Master/Employee_Home_Page",
          type: "POST",
          data: {
              Date: $("#Date").val(),
              Shift: $("#Shift").val()
          },
          success: function (data) {

            const responseData = JSON.parse(data);

            const homeData = responseData.Employee_Home_Page;

            const actualComers = parseInt(homeData.Actual_Comers) || 0;
            const lateComers = parseInt(homeData.Late_Comers) || 0;
            const totalAllocated = parseInt(homeData.Total_Allocated_Count) || 0;
            const lateAllocated = parseInt(homeData.Late_Total_Allocated_Count) || 0;
            const totalEngaged = actualComers + lateComers;

            // Hide chart if all values are zero
            if (actualComers === 0 && lateComers === 0 && totalAllocated === 0 && lateAllocated === 0) {
                $("#Chart_View_Employee_Count").hide();
                return;
            }

            $("#Chart_View_Employee_Count").show();

            const engagementData = [
                actualComers,
                totalEngaged,
                lateComers,
                totalAllocated,
                lateAllocated
            ];

            const engagementLabels = [
                'Total Punched Employee',
                'Total Engaged Employee',
                'Total Late Punched Employee',
                'Total Allocated Employee',
                'Allocated Late Employee'
            ];

            const dataForChart = {
                labels: engagementLabels,
                datasets: [{
                    label: 'Employee Counts',
                    data: engagementData,
                    backgroundColor: [
                        'rgb(43, 255, 0)',      // Green
                        'rgb(4, 0, 255)',       // Blue
                        'rgba(255, 0, 0, 0.8)', // Red
                        'rgb(255, 165, 0)',     // Orange
                        'rgb(128, 0, 128)'      // Purple
                    ],
                    borderColor: [
                        'rgb(43, 255, 0)',
                        'rgb(4, 0, 255)',
                        'rgba(255, 0, 0, 0.8)',
                        'rgb(255, 165, 0)',
                        'rgb(128, 0, 128)'
                    ],
                    borderWidth: 1
                }]
            };

            const options = {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            weight: 'bold',
                            size: 8
                        },
                        formatter: function (value, context) {
                            const label = context.chart.data.labels[context.dataIndex];
                            return `${label}: ${value}`;
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'No. of Employees'
                        }
                    }
                }
            };

            const ctx = document.getElementById('myBarChart').getContext('2d');

            if (window.myBarChart instanceof Chart) {
                window.myBarChart.destroy();
            }

            window.myBarChart = new Chart(ctx, {
                type: 'bar',
                data: dataForChart,
                options: options,
                plugins: [ChartDataLabels]
            });
        }
        ,


          error: function () {
              console.error("Failed to fetch data from server.");
          }
      });



          var Selected_Date = $("#Date").val();
          var Shift = $("#Shift").val();

          $.ajax({
            url: baseurl + "Master/Standard_Actual_List",
            type: "POST",
            data: {
              Date: Selected_Date,
              Shift,
            },
            success: function (response) {
              var Response_Data = JSON.parse(response);
              var Standard_Actual_List = Response_Data.Standard_Actual_List;

              $("#Standard_Actual_Table_Section").show();
              $("#Standard_Actual_Table tbody").empty();
              let totalStandard = 0;
              let totalActual = 0;

              $.each(Standard_Actual_List, function (index, item) {
                totalStandard += parseFloat(item.Standard) || 0;
                totalActual += parseFloat(item.Actual) || 0;

                var row = `
                      <tr>
                       <td>${index + 1}</td>
                            <td>${item.Position}</td>
                            <td>${item.Standard}</td>
                            <td>${item.Actual}</td>
                          <td>
                              ${
                                item.Status === "Active"
                                  ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
                                  : item.Status
                              }
                          </td>
                      </tr>
                  `;
                $("#Standard_Actual_Table tbody").append(row);
              });

              // Append totals row
              let totalRow = `
          <tr style="background-color: rgb(147, 255, 226);">
              <td colspan="2" class="text-right" style="font-weight: bold;">Total</td>
              <td style="font-weight: bold;">${totalStandard}</td>
              <td style="font-weight: bold;">${totalActual}</td>
              <td></td>
          </tr>
      `;


              $("#Standard_Actual_Table tbody").append(totalRow);


              $("#Standard_Actual_Table_Final").DataTable({
                paging: false,
                searching: true,
                ordering: true,
                info: true,
              });
            },
          });







        },
      });
    });





  } else {

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

      },
    })


    $.ajax({
      url: baseurl + "Master/Standard_Actual_Report",
      type: "POST",
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Standard_Master_List = Response_Data.Standard_Master_List;

        if (Standard_Master_List.Status == "Error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Standard_Master_List.Message,
          });

          $("#Standard_Actual_Table_Section").show();
          $("#Standard_Actual_Table_Final tbody").empty();
          $.each(Standard_Master_List, function (index, item) {
            var row = `
                          <tr>
                               <td>${index + 1}</td>
                              <td>${item.Department}</td>
                              <td>${item.Sub_Department}</td>
                              <td>${item.Position}</td>
                              <td>${item.Shift}</td>
                              <td>${item.Employee_Count}</td>
                              <td>
    ${
      item.Status === "Active"
        ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
        : item.Status
    }
  </td>

                              <td>

                          </tr>
                      `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });
        } else {
          $("#Standard_Actual_Table_Section").show();
          $("#Standard_Actual_Table_Final tbody").empty();
          $.each(Standard_Master_List, function (index, item) {
            var row = `
                          <tr>
                             <td>${index + 1}</td>
                              <td>${item.Department}</td>
                              <td>${item.Sub_Department}</td>
                              <td>${item.Position}</td>
                              <td>${item.Shift}</td>
                              <td>${item.Employee_Count}</td>
                              <td>
    ${
      item.Status === "Active"
        ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
        : item.Status
    }
  </td>

                              <td>

                              <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                                item.Standard_ID
                              }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                              <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                                item.Standard_ID
                              }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>

                              </td>
                          </tr>
                      `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });
        }
      },
    });


    $("#Standard_Actual_Table_Section").hide();

  $.ajax({
    url: baseurl + "Master/Departments",
    type: "POST",
    success: function (response) {
      var responseData = JSON.parse(response);

      var Department_Data = responseData.Departments;

      var Department = {};

      for (var i = 0; i < Department_Data.length; i++) {
        var DName = Department_Data[i];
        Department[DName.DeptGrp] = DName.DeptGrp;
      }

      $("#Department").empty();

      $.each(Department, function (key, value) {
        $("#Department").append(
          $("<option></option>").attr("value", key).text(value)
        );
      });

      var Department = $("#Department").val();

      $.ajax({
        url: baseurl + "Master/Sub_Departments",
        type: "POST",
        data: {
          Department,
        },
        success: function (response) {
          var responseData = JSON.parse(response);

          var Department_Data = responseData.Sub_Departments;

          var Sub_Department = {};

          for (var i = 0; i < Department_Data.length; i++) {
            var DName = Department_Data[i];
            Sub_Department[DName.DeptName] = DName.DeptName;
          }

          $("#Sub_Department").empty();

          $.each(Sub_Department, function (key, value) {
            $("#Sub_Department").append(
              $("<option></option>").attr("value", key).text(value)
            );
          });

          var Department = $("#Department").val();
          var Sub_Department = $("#Sub_Department").val();

          $.ajax({
            url: baseurl + "Master/Positions",
            type: "POST",
            data: {
              Department,
              Sub_Department,
            },
            success: function (response) {
              var responseData = JSON.parse(response);

              var Position_Data = responseData.Position;

              var Position_Datas = {};

              for (var i = 0; i < Position_Data.length; i++) {
                var DName = Position_Data[i];
                Position_Datas[DName.WorkArea] = DName.WorkArea;
              }

              $("#WorkArea").empty();

              $.each(Position_Datas, function (key, value) {
                $("#WorkArea").append(
                  $("<option></option>").attr("value", key).text(value)
                );
              });
            },
          });
        },
      });
    },
  });





  $("#Sub_Department").on("change", function () {
    var Department = $("#Department").val();
    var Sub_Department = $("#Sub_Department").val();

    $.ajax({
      url: baseurl + "Master/Positions",
      type: "POST",
      data: {
        Department,
        Sub_Department,
      },
      success: function (response) {
        var responseData = JSON.parse(response);

        var Position_Data = responseData.Position;

        var Position_Datas = {};

        for (var i = 0; i < Position_Data.length; i++) {
          var DName = Position_Data[i];
          Position_Datas[DName.WorkArea] = DName.WorkArea;
        }

        $("#WorkArea").empty();

        $.each(Position_Datas, function (key, value) {
          $("#WorkArea").append(
            $("<option></option>").attr("value", key).text(value)
          );
        });
      },
    });
  });

  $("#Standard_Update").on("click", function () {
    var Department = $("#Department").val();
    var Sub_Department = $("#Sub_Department").val();
    var Shift = $("#Shift").val();
    var WorkArea = $("#WorkArea").val();
    var Employee_Count = $("#Employee_Count").val();

    $.ajax({
      url: baseurl + "Master/Insert_Standard_Actual",
      type: "POST",
      data: {
        Department,
        Sub_Department,
        WorkArea,
        Employee_Count,
        Shift,
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);

        var Insert_Standard_Actual = Response_Data.Insert_Standard_Actual;
        var Standard_Master_List = Response_Data.Standard_Master_List;

        $("#Employee_Count").val("");

        if (Insert_Standard_Actual.Status == "Error") {
          swal({
            type: "warning",
            title: "Warning",
            text: Insert_Standard_Actual.Message,
          });

          $("#Employee_Count").val("");

          $("#Standard_Actual_Table_Section").show();
          $("#Standard_Actual_Table_Final tbody").empty();

          $.each(Standard_Master_List, function (index, item) {
            var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.Department}</td>
                            <td>${item.Sub_Department}</td>
                            <td>${item.Position}</td>
                            <td>${item.Shift}</td>
                            <td>${item.Employee_Count}</td>
                            <td>
  ${
    item.Status === "Active"
      ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
      : item.Status
  }
</td>

                            <td>

                            <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                            <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>

                            </td>
                        </tr>
                    `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });

          //            if ($.fn.DataTable.isDataTable("#Standard_Actual_Table_Final")) {
          //     $("#Standard_Actual_Table_Final").DataTable().clear().destroy();
          // }

          // $("#Standard_Actual_Table_Final").DataTable({
          //     paging: false,
          //     searching: true,
          //     ordering: true,
          //     info: true
          // });
        } else {
          swal({
            type: "success",
            title: "Success",
            text: Insert_Standard_Actual.Message,
          });

          $("#Employee_Count").val("");

          $("#Standard_Actual_Table_Section").show();
          $("#Standard_Actual_Table_Final tbody").empty();
          $.each(Standard_Master_List, function (index, item) {
            var row = `
                        <tr>
                             <td>${index + 1}</td>
                            <td>${item.Department}</td>
                            <td>${item.Sub_Department}</td>
                            <td>${item.Position}</td>
                            <td>${item.Shift}</td>
                            <td>${item.Employee_Count}</td>
                            <td>
  ${
    item.Status === "Active"
      ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
      : item.Status
  }
</td>

                            <td>

                            <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                            <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>

                            </td>
                        </tr>
                    `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });
        }
      },
    });
  });



  $(document).on("click", ".Standard-Edit-Btn", function () {
    var Standard_ID = $(this).data("id");
    $("#Edit_Standard_Pop_Model").modal("show");

    $.ajax({
      url: baseurl + "Master/Standard_Edit_List",
      type: "POST",
      data: {
        Standard_ID,
      },
      success: function (response) {
        var Response_Data = JSON.parse(response);
        var Standard_Edit_List = Response_Data.Standard_Edit_List;

        $("#Model_Standard_ID").val(Standard_Edit_List[0].Standard_ID);
        $("#Model_Department").val(Standard_Edit_List[0].Department);
        $("#Model_Sub_Department").val(Standard_Edit_List[0].Sub_Department);
        $("#Model_WorkArea").val(Standard_Edit_List[0].Position);
        $("#Model_Employee_Count").val(Standard_Edit_List[0].Employee_Count);
      },
    });
  });

  $("#Final_Standard_Edit_Btn").on("click", function () {
    var Standard_ID = $("#Model_Standard_ID").val();
    var Employee_Count = $("#Model_Employee_Count").val();

    $.ajax({
      url: baseurl + "Master/Standard_Edit",
      type: "POST",
      data: {
        Standard_ID,
        Employee_Count,
      },
      success: function (reponse) {
        var Response_Data = JSON.parse(reponse);
        var Standard_Edit = Response_Data.Standard_Edit;
        var Standard_Master_List = Response_Data.Standard_Master_List;

        if (Standard_Edit == 1) {
          swal({
            type: "success",
            title: "Success",
            text: "Updated For Standard Employee Count..!",
          });

          $("#Edit_Standard_Pop_Model").modal("hide");
          $("#Standard_Actual_Table_Final tbody").empty();
          $.each(Standard_Master_List, function (index, item) {
            var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.Department}</td>
                            <td>${item.Sub_Department}</td>
                            <td>${item.Position}</td>
                            <td>${item.Shift}</td>
                            <td>${item.Employee_Count}</td>
                            <td>
  ${
    item.Status === "Active"
      ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
      : item.Status
  }
</td>

                            <td>

                            <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                            <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>

                            </td>
                        </tr>
                    `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });
        } else {
          $("#Edit_Standard_Pop_Model").modal("hide");

          swal({
            type: "warning",
            title: "Warning",
            text: "Failed To Updated Standard Employee Count..!",
          });

          $("#Standard_Actual_Table_Final tbody").empty();

          $.each(Standard_Master_List, function (index, item) {
            var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.Department}</td>
                            <td>${item.Sub_Department}</td>
                            <td>${item.Position}</td>
                            <td>${item.Shift}</td>
                            <td>${item.Employee_Count}</td>
                                <td>
                                ${
                                  item.Status == "Active"
                                    ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
                                    : item.Status
                                }
                                </td>

                            <td>

                            <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                            <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                              item.Standard_ID
                            }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>

                            </td>
                        </tr>
                    `;
            $("#Standard_Actual_Table_Final tbody").append(row);
          });
        }
      },
    });
  });

  $(document).on("click", ".Standard-Delete-Btn", function () {
    var Standard_ID = $(this).data("id");

    swal({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      type: "warning",
      showCancelButton: true,
      confirmButtonClass: "btn btn-success",
      cancelButtonClass: "btn btn-danger",
      confirmButtonText: "Yes, delete it!",
    }).then(function (result) {
      //    if (result.isConfirmed) {

      $.ajax({
        url: baseurl + "Master/Standard_Delete",
        type: "POST",
        data: {
          Standard_ID: Standard_ID,
        },
        success: function (response) {
          var Response_Data = JSON.parse(response);
          var Standard_Delete = Response_Data.Standard_Delete;
          var Standard_Master_List = Response_Data.Standard_Master_List;

          if (Standard_Delete == 1) {
            swal({
              type: "success",
              title: "Success",
              text: "Standard Details Deleted Successfully..",
            });

            updateTable(Standard_Master_List);
          } else {
            swal({
              type: "warning",
              title: "Warning",
              text: "Failed To Delete Standard Employee Count..",
            });

            updateTable(Standard_Master_List);
          }
        },
        error: function () {
          swal({
            type: "error",
            title: "Error",
            text: "Something went wrong while deleting. Please try again later.",
          });
        },
      });
      //    } else {
      //         swal({
      //         type: 'info',
      //         title: 'Cancelled',
      //         text: 'Standard deletion was cancelled.',
      //     });
      //    }
    });

    function updateTable(Standard_Master_List) {
      $("#Standard_Actual_Table_Final tbody").empty();

      $.each(Standard_Master_List, function (index, item) {
        var row = `
                <tr>
                    <td>${index + 1}</td>
                            <td>${item.Department}</td>
                            <td>${item.Sub_Department}</td>
                            <td>${item.Position}</td>
                            <td>${item.Shift}</td>
                            <td>${item.Employee_Count}</td>
                            <td>
                        ${
                          item.Status === "Active"
                            ? `<span style="color: white; background: green; padding: 3px 8px; border-radius: 4px;">${item.Status}</span>`
                            : item.Status
                        }
                        </td>

                            <td>
                        <i class="fas fa-edit Standard-Edit-Btn action-icon" data-id="${
                          item.Standard_ID
                        }" style="color: #28a745; font-size: 20px;" title="Edit"></i> &nbsp; &nbsp;
                        <i class="fas fa-trash Standard-Delete-Btn action-icon" data-id="${
                          item.Standard_ID
                        }" style="color: rgb(167, 40, 40); font-size: 20px;" title="Delete"></i>
                    </td>
                </tr>
            `;
        $("#Standard_Actual_Table_Final tbody").append(row);
      });
    }
  });

  }







});
