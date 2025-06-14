$(document).ready(function () {

    $("#Update_Location").on("click", function () {

        var LocationCode = $("#Lcode").val();
        var CompanyCode = $("#Ccode").val();

        $.ajax({
            url: baseurl + 'IT/Update_Location',
            type: "POST",
            data: {
                LocationCode,
                CompanyCode
            },
            success: function (response) {
                window.location.href = baseurl + "Home";
            }

        })
    })

})