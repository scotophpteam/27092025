$(document).ready(function () {

    var currentDate = new Date().toISOString().split("T")[0];
    $("#From_Date").val(currentDate);
    $("#From_Date").attr("max", currentDate);

    $("#To_Date").val(currentDate);
    $("#To_Date").attr("max", currentDate);


    var Page_Name = $("#Page_Name").val();

    if (Page_Name == 'Employee_Position_Report_Page') {

        $.ajax({
            url: baseurl + 'Incentive/Employee_Details',
            type: 'POST',
            success: function (response) {
                var Response_Data = JSON.parse(response);
                var Employee_Details = Response_Data.Employee_Details;

                var Employee_Detail = {};

                for (var i = 0; i < Employee_Details.length; i++) {
                    var DName = Employee_Details[i];
                    Employee_Detail[DName.EmpNo] = DName.FirstName;
                }

                $("#Employee_Id").empty();
                $("#Employee_Id").append(
                    $("<option></option>").attr("value", "All").text("All")
                );

                $.each(Employee_Detail, function (key, value) {
                    $("#Employee_Id").append(
                        $("<option></option>").attr("value", key).text(key + " = " + value)
                    );
                });
            }
        });

        $('#Employee_Position_Report_Tables').hide();


        $("#Position_Report_View_Button").on('click', function () {

            var From_Date = $("#From_Date").val();
            var To_Date = $("#To_Date").val();
            // var Position_Grade = $("#Grade").val();
            var Employee_Id = $("#Employee_Id").val();


            var fromDate = $("#From_Date").val();

            if (fromDate) {
                let dateParts = fromDate.split("-");

                let day = dateParts[0];
                let month = dateParts[1];
                var year = fromDate.split("-")[0];

                if (year.length == 2) {
                    let currentYear = new Date().getFullYear();
                    let currentCentury = Math.floor(currentYear / 100) * 100;
                    year = currentCentury + parseInt(year);
                }

                let daysInMonth = new Date(parseInt(year), parseInt(month), 0).getDate();

                let $theadRow = $('<tr>');
                $theadRow.append('<th>S.No</th>');
                $theadRow.append('<th>EmpNo</th>');
                $theadRow.append('<th>Name</th>');
                $theadRow.append('<th>Total A</th>');
                $theadRow.append('<th>Total B</th>');
                $theadRow.append('<th>Total C</th>');
                $theadRow.append('<th>Total</th>');

                for (let d = 1; d <= daysInMonth; d++) {
                    let dayFormatted = (d < 10 ? '0' : '') + d;
                    let fullDate = `${dayFormatted}-${month}-${year}`;
                    $theadRow.append('<th>' + fullDate + '</th>');
                }

                $('#Employee_Position_Report_Table thead').empty().append($theadRow);
            }


            $.ajax({
                url: baseurl + "Incentive/Employee_Position_Details",
                type: "POST",
                data: {
                    From_Date,
                    To_Date,
                    // Position_Grade,
                    Employee_Id
                },
                success: function (response) {

                    var Response_Data = JSON.parse(response);

                    if (Response_Data.Status == 'Error') {


                        swal({
                            type: "warning",
                            title: "No Data",
                            text: Response_Data.Message
                        });

                        $('#Employee_Position_Report_Table_Tbody').empty();
                        $('#Employee_Position_Report_Tables').hide();
                        $("#Overal_Report_Down_Button").hide();
                        $("#Short_Report_Down_Button").hide();

                    } else {

                        var Employee_Position_Details = Response_Data.Employee_Position_Details;

                        // if ($.fn.DataTable.isDataTable('#Employee_Position_Report_Table')) {
                        //     $('#Employee_Position_Report_Table').DataTable().clear().destroy();
                        // }

                        $('#Employee_Position_Report_Table_Tbody').empty();
                        $('#Employee_Position_Report_Tables').show();
                        $("#Overal_Report_Down_Button").show();
                        $("#Short_Report_Down_Button").show();

                        $.each(Employee_Position_Details, function (index, emp) {


                            let $tr = $('<tr>');

                            $tr.append($('<td>').text(index + 1));
                            $tr.append($('<td>').text(emp.EmpNo));
                            $tr.append($('<td>').text(emp.FirstName));
                            let totalA = emp.Total_A_Count || 0;
                            let totalB = emp.Total_B_Count || 0;
                            let totalC = emp.Total_C_Count || 0;
                            let total = totalA + totalB + totalC;

                            $tr.append($('<td>').text(totalA));
                            $tr.append($('<td>').text(totalB));
                            $tr.append($('<td>').text(totalC));
                            $tr.append($('<td>').text(total));

                            for (let day = 1; day <= 31; day++) {
                                let dayKey = 'DAY-' + day;
                                let dayValue = emp[dayKey] || '';
                                $tr.append($('<td>').text(dayValue));
                            }



                            $('#Employee_Position_Report_Table_Tbody').append($tr);


                        });

                        // $('#Employee_Position_Report_Table').DataTable({
                        //     paging: true,
                        //     searching: true,
                        //     ordering: true,
                        //     scrollX: true,
                        //     fixedHeader: true,
                        //     destroy: true // Ensure it resets
                        // });

                    }
                },

            });

        });


        $("#Overal_Report_Down_Button").on("click", function () {

            var From_Date = $("#From_Date").val();
            var To_Date = $("#To_Date").val();
            // var Position_Grade = $("#Grade").val();
            var Employee_Id = $("#Employee_Id").val();


            $.ajax({
                url: baseurl + "Reports/Employee_Position_Overal_Report_Down",
                type: "POST",
                data: {
                    From_Date,
                    To_Date,
                    // Position_Grade,
                    Employee_Id
                },
                success: function (response) {

                    var Response_Data = JSON.parse(response);

                    if (Response_Data.file_url) {
                        var link = document.createElement("a");
                        link.href = Response_Data.file_url;
                        link.download = "Employee Position Details.xlsx";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert("Failed to generate the report");
                    }
                }

            })



        })


        $("#Short_Report_Down_Button").on("click", function () {

            var From_Date = $("#From_Date").val();
            var To_Date = $("#To_Date").val();
            // var Position_Grade = $("#Grade").val();
            var Employee_Id = $("#Employee_Id").val();


            $.ajax({
                url: baseurl + "Reports/Employee_Position_Short_Report_Down",
                type: "POST",
                data: {
                    From_Date,
                    To_Date,
                    // Position_Grade,
                    Employee_Id
                },
                success: function (response) {

                    var Response_Data = JSON.parse(response);

                    if (Response_Data.file_url) {
                        var link = document.createElement("a");
                        link.href = Response_Data.file_url;
                        link.download = "Employee Position Details.xlsx";
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