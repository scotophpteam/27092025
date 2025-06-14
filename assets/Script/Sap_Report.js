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
    
    	$("#Reports_For_Allocation").hide();

		$("#Work_Allocation_List_Container").hide();
		$("#Work_Allocation_Report_Down_Btn").hide();

    $.ajax({
		url: baseurl + "Shift_Closing/Shifts",
		type: "POST",
		success: function (response) {
			var responseData = JSON.parse(response);
			var Shifts = responseData.Shifts;
			var Shift = {'' : ''};

			for (var i = 0; i < Shifts.length; i++) {
				var DName = Shifts[i];
				Shift[DName.ShiftDesc] = DName.ShiftDesc;
			}

			$.each(Shift, function (index, value) {
				$("#Shift").append($("<option></option>").attr("value", value).text(value));
            });


		}
    })


    $("#Date").on("change", function () {

        var Date = $("#Date").val();
        var Shift = $("#Shift").val();

        $.ajax({
            url: baseurl + 'Sap/Machine_Work_Details',
            type: 'POST',
            data: {
                Date,
                Shift
            },
            success: function (response) {

                var Response_Data = JSON.parse(response);
                var Machine_Work_Details = Response_Data.Machine_Work_Details;

                if (Machine_Work_Details.Status == 'Error') {

                    $("#Work_Allocation_List_Container").hide();
                    $("#Work_Allocation_Report_Down_Btn").hide();
                    $("#Work_Allocation_List_Container tbody").empty();

                    swal({
                        type: 'warning',
                        title: 'Warning',
                        text: Machine_Work_Details.Message,
                    });

                } else {

                    $("#Work_Allocation_List_Container").show();
                    $("#Work_Allocation_Report_Down_Btn").show();
                    $("#Work_Allocation_List_Container tbody").empty();

                    // ✅ Sort by Employee_Id in ascending order
                    Machine_Work_Details.sort(function (a, b) {
                        return a.Employee_Id - b.Employee_Id;
                    });

                    $.each(Machine_Work_Details, function (index, item) {

                        var row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.Ccode}</td>
                    <td>${item.Lcode}</td>
                    <td>${item.Date}</td>
                    <td>${item.Shift}</td>
                    <td>${item.Department}</td>
                    <td>${item.Sub_Department}</td>
                    <td>${item.Employee_Division}</td>
                    <td>${item.Wages}</td>
                    <td>${item.WorkArea}</td>
                    <td>${item.Employee_Id}</td>
                    <td>${item.Employee_Name}</td>
                    <td>${item.Machine_Id}</td>
                    <td>${item.Frame}</td>
                </tr>
            `;
                        $("#Work_Allocation_List_Container tbody").append(row);
                    });
                }
            }

        })

    });




    $("#Shift").on("change", function () {

        var Date = $("#Date").val();
        var Shift = $("#Shift").val();

        $.ajax({
            url: baseurl + 'Sap/Machine_Work_Details',
            type: 'POST',
            data: {
                Date,
                Shift
            },
            success: function (response) {

                var Response_Data = JSON.parse(response);
                var Machine_Work_Details = Response_Data.Machine_Work_Details;

                if (Machine_Work_Details.Status == 'Error') {

                    $("#Work_Allocation_List_Container").hide();
                    $("#Work_Allocation_Report_Down_Btn").hide();
                    $("#Work_Allocation_List_Container tbody").empty();

                    swal({
                        type: 'warning',
                        title: 'Warning',
                        text: Machine_Work_Details.Message,
                    });

                } else {

                    $("#Work_Allocation_List_Container").show();
                    $("#Work_Allocation_Report_Down_Btn").show();
                    $("#Work_Allocation_List_Container tbody").empty();

                    // ✅ Sort by Employee_Id in ascending order
                    Machine_Work_Details.sort(function (a, b) {
                        return a.Employee_Id - b.Employee_Id;
                    });

                    $.each(Machine_Work_Details, function (index, item) {

                        var row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.Ccode}</td>
                    <td>${item.Lcode}</td>
                    <td>${item.Date}</td>
                    <td>${item.Shift}</td>
                    <td>${item.Department}</td>
                    <td>${item.Sub_Department}</td>
                    <td>${item.Employee_Division}</td>
                    <td>${item.Wages}</td>
                    <td>${item.WorkArea}</td>
                    <td>${item.Employee_Id}</td>
                    <td>${item.Employee_Name}</td>
                    <td>${item.Machine_Id}</td>
                    <td>${item.Frame}</td>
                </tr>
            `;
                        $("#Work_Allocation_List_Container tbody").append(row);
                    });
                }
            }

        })



    });


    $("#Work_Allocation_Report_Down_Btn").on("click", function () {

        var Date = $("#Date").val();
        var Shift = $("#Shift").val();

        $.ajax({
            url: baseurl + 'Sap/Machine_Work_Details_Download',
            type: 'POST',
            data: {
                Date,
                Shift
            },
            success: function (response) {

                var Response_Data = JSON.parse(response);

                if (Response_Data.file_url) {
					var link = document.createElement("a");
					link.href = Response_Data.file_url;
                    link.download = "Daily_Sap_Machine_Details" + "_" + currentDate + "." + "csv";
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				} else {
					alert("Failed to generate the report");
				}

            }
        })



    })




})