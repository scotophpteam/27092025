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

    $("#Admin_List_Container").hide();


    


$.ajax({
    url: baseurl + "Admin/Unit_Details",
    type: "POST",
    data: {
        Date: $("#Date").val()
    },
    success: function (response) {
        var Response_Data = JSON.parse(response);
        var Unit_Details = Response_Data.Unit_Details;

        var groupedData = {};

        $.each(Unit_Details, function (index, item) {
            var key = item.Ccode + "|" + item.Lcode + "|" + item.Sub_Department;

            if (!groupedData[key]) {
                groupedData[key] = {
                    Ccode: item.Ccode,
                    Lcode: item.Lcode,
                    Sub_Department: item.Sub_Department,
                    SHIFT1: "-",
                    SHIFT2: "-",
                    SHIFT3: "-",
                    SHIFT4: "-"
                };
            }

            var statusBadge = getBadge(item.Work_Assign_Status, item.Shift_Closing_Status);
            groupedData[key][item.Shift] = statusBadge;
        });

        var sortedData = Object.values(groupedData).sort(function (a, b) {
            return a.Lcode.localeCompare(b.Lcode);
        });

        $("#Admin_Unit_List tbody").empty();
        $("#Admin_List_Container").show();

        var serialNo = 1;
        $.each(sortedData, function (index, data) {
            var row = `
                <tr>
                    <td>${serialNo++}</td>
                    <td>${data.Ccode}</td>
                    <td>${data.Lcode}</td>
                    <td>${data.Sub_Department}</td>
                    <td>${data.SHIFT1}</td>
                    <td>${data.SHIFT2}</td>
                    <td>${data.SHIFT3}</td>
                    <td>${data.SHIFT4}</td>
                </tr>
            `;
            $("#Admin_Unit_List tbody").append(row);
        });

        function getBadge(assignStatus, closeStatus) {
            var assignColor = assignStatus === "Allocated" ? "success" : "danger";
            var assignText = `<span class="badge badge-${assignColor}">${assignStatus}</span>`;

            var closeText = "";
            if (closeStatus === "Not Closed") {
                closeText = ` <span class="badge badge-warning">${closeStatus}</span>`;
            } else if (closeStatus === "Closed") {
              closeText = ` <span class="badge badge-success">${closeStatus}</span>`;

            }

            return assignText + closeText;
        }
    }
});


    $("#Date").on("change", function () {

        var Date = $("#Date").val();

        $.ajax({
    url: baseurl + "Admin/Unit_Details",
    type: "POST",
    data: {
        Date
    },
    success: function (response) {
        var Response_Data = JSON.parse(response);
        var Unit_Details = Response_Data.Unit_Details;

        var groupedData = {};

        $.each(Unit_Details, function (index, item) {
            var key = item.Ccode + "|" + item.Lcode + "|" + item.Sub_Department;

            if (!groupedData[key]) {
                groupedData[key] = {
                    Ccode: item.Ccode,
                    Lcode: item.Lcode,
                    Sub_Department: item.Sub_Department,
                    SHIFT1: "-",
                    SHIFT2: "-",
                    SHIFT3: "-",
                    SHIFT4: "-"
                };
            }

            var statusBadge = getBadge(item.Work_Assign_Status, item.Shift_Closing_Status);
            groupedData[key][item.Shift] = statusBadge;
        });

        var sortedData = Object.values(groupedData).sort(function (a, b) {
            return a.Lcode.localeCompare(b.Lcode);
        });

        $("#Admin_Unit_List tbody").empty();
        $("#Admin_List_Container").show();

        var serialNo = 1;
        $.each(sortedData, function (index, data) {
            var row = `
                <tr>
                    <td>${serialNo++}</td>
                    <td>${data.Ccode}</td>
                    <td>${data.Lcode}</td>
                    <td>${data.Sub_Department}</td>
                    <td>${data.SHIFT1}</td>
                    <td>${data.SHIFT2}</td>
                    <td>${data.SHIFT3}</td>
                    <td>${data.SHIFT4}</td>
                </tr>
            `;
            $("#Admin_Unit_List tbody").append(row);
        });

        function getBadge(assignStatus, closeStatus) {
            var assignColor = assignStatus === "Allocated" ? "success" : "danger";
            var assignText = `<span class="badge badge-${assignColor}">${assignStatus}</span>`;

            var closeText = "";
            if (closeStatus === "Not Closed") {
                closeText = ` <span class="badge badge-warning">${closeStatus}</span>`;
            } else if (closeStatus === "Closed") {
              closeText = ` <span class="badge badge-success">${closeStatus}</span>`;

            }

            return assignText + closeText;
        }
    }
});

    })



})