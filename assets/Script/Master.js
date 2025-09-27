$(document).ready(function () {

    var currentDate = new Date().toISOString().split("T")[0];
    $("#Date").val(currentDate);
    $("#Date").attr("max", currentDate);



    //  Machine Mapping Section
    $.ajax({
        url: baseurl + "Master/Machine_Sub_Department",
        type: "POST",
        success: function (response) {
            var responseData = JSON.parse(response);
            var Sub_Departments = responseData.Machine_Sub_Department;

            var Sub_Departmentss = { "": "" };

            for (var i = 0; i < Sub_Departments.length; i++) {
                var DName = Sub_Departments[i];
                Sub_Departmentss[DName.Sub_Department] = DName.Sub_Department;
            }
            $.each(Sub_Departmentss, function (key, value) {
                $("#Department").append($("<option></option>").attr("value", key).text(value));
            });
        }
    });


    $("#Department").on("change", function () {
        var Department = $("#Department").val();

        $.ajax({
            url: baseurl + "Master/Department_Work_Areas",
            type: "POST",
            data:
            {
                Department
            },

            success: function (response) {
                var responseData = JSON.parse(response);
                var Work_Areas = responseData.Work_Areas;

                var Work_Area = {};

                for (var i = 0; i < Work_Areas.length; i++) {
                    var DName = Work_Areas[i];
                    Work_Area[DName.WorkArea] = DName.WorkArea;
                }

                $.each(Work_Area, function (key, value) {
                    $("#Work_Area").append($("<option></option>").attr("value", key).text(value));
                });

                $("#Work_Area option:first").prop("selected", true);
            },
        });
    });

    // $("#Work_Area").on("change", function () {
    //     var Department = $("#Department").val();
    //     var WorkArea = $("#Work_Area").val();

    //     $.ajax({
    //         url: baseurl + "Master/Machine_Id",
    //         type: "POST",
    //         data:
    //         {
    //           Department,
    //           WorkArea
    //         },
    //         success: function (response) {
    //             var responseData = JSON.parse(response);
    //             var Machine_Id = responseData.Machine_Id;

    //             var Work_Area = {};

    //             for (var i = 0; i < Machine_Id.length; i++) {
    //                 var DName = Machine_Id[i];
    //                 Work_Area[DName.Machine_Code] = DName.Machine_Code;
    //             }

    //             $.each(Work_Area, function (key, value) {
    //                 $("#Machine_Id").append($("<option></option>").attr("value", key).text(value));
    //             });

    //             $("#Machine_Id option:first").prop("selected", true);
    //           },
    //   });
    //   });

    $("#Work_Area").on("change", function () {
        var Department = $("#Department").val();
        var WorkArea = $("#Work_Area").val();

        $.ajax({
            url: baseurl + "Master/Machine_Id",
            type: "POST",
            data: {
                Department,
                WorkArea
            },
            success: function (response) {
                var responseData = JSON.parse(response);
                var Machine_Id = responseData.Machine_Id;

                // Clear existing options
                $('#Machine_Id').empty();

                // Add options
                $.each(Machine_Id, function (i, item) {
                    $('#Machine_Id').append(
                        $('<option></option>').val(item.Machine_Code).text(item.Machine_Code)
                    );
                });

                // Refresh selectpicker
                $('#Machine_Id').selectpicker('refresh');
            }
        });
    });

    // $("#Machine_Mapping_Update").on("click", function () {
    //     alert ("Machine Mapping Update");
    //     var Department = $("#Department").val();
    //     var WorkArea = $("#Work_Area").val();
    //     var Machine_Id = $("#Machine_Id").val();
    //     var Machine_Group_Name = $("#Machine_Group_Name").val();
    //     var Machine_Frame_Name = $("#Machine_Frame_Name").val();

    //     $.ajax({
    //         url: baseurl + "Master/Machine_Mapping_Update",
    //         type: "POST",
    //         data: {
    //             Department,
    //             WorkArea,
    //             Machine_Id,
    //             Machine_Group_Name,
    //             Machine_Frame_Name
    //         },
    //         success: function (response) {
    //            alert("inserttend...!!")
    //         }
    //     })
    // })

    // $("#Machine_Mapping_Update").on("click", function () {
    //     var Department = $("#Department").val();
    //     var WorkArea = $("#Work_Area").val();
    //     var Machine_Id = $("#Machine_Id").val();
    //     var Machine_Group_Name = $("#Machine_Group_Name").val();
    //     var Machine_Frame_Name = $("#Machine_Frame_Name").val();

    //     $.ajax({
    //         url: baseurl + "Master/Machine_Mapping_Update",
    //         type: "POST",
    //         data: {
    //             Department,
    //             WorkArea,
    //             Machine_Id,
    //             Machine_Group_Name,
    //             Machine_Frame_Name
    //         },
    //         success: function (response) {
    //             var res = JSON.parse(response);
    //             if (res.status === 'success') {
    //                 Swal.fire("Success", res.message, "success");
    //             } else if (res.status === 'warning') {
    //                 Swal.fire("Warning", res.message, "warning");
    //             } else {
    //                 Swal.fire("Error", res.message, "error");
    //             }
    //         },
    //         error: function () {
    //             Swal.fire("Error", "AJAX call failed!", "error");
    //         }
    //     });
    // });

    $("#Machine_Mapping_Update").on("click", function () {
        var Department = $("#Department").val();
        var WorkArea = $("#Work_Area").val();
        var Machine_Id = $("#Machine_Id").val();
        var Machine_Group_Name = $("#Machine_Group_Name").val();
        var Machine_Frame_Name = $("#Machine_Frame_Name").val();

        $.ajax({
            url: baseurl + "Master/Machine_Mapping_Update",
            type: "POST",
            data: {
                Department,
                WorkArea,
                Machine_Id,
                Machine_Group_Name,
                Machine_Frame_Name
            },
            success: function (response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    swal({
                        type: 'success',
                        title: 'Success!',
                        text: res.message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = baseurl + "Master/Machine_Master"; // Change to your desired redirect
                    });
                } else if (res.status === 'warning') {
                    swal({
                        type: 'warning',
                        title: 'Oops...',
                        text: res.message,
                        confirmButtonColor: '#f39c12',
                        confirmButtonText: 'OK'
                    });
                } else {
                    swal({
                        type: 'error',
                        title: 'Error!',
                        text: res.message,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function () {
                swal({
                    type: 'error',
                    title: 'AJAX Failed!',
                    text: 'Could not contact server.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    var Page_Name = $("#Page_Name").val();

    if (Page_Name == 'Employee_Position_Mapping_Page') {

        $.ajax({
            url: baseurl + 'Master/Emp_Department',
            type: 'POST',
            success: function (response) {
                var Response_Data = JSON.parse(response);
                var Emp_Departments = Response_Data.Emp_Department;

                var Emp_Department = {};

                for (var i = 0; i < Emp_Departments.length; i++) {
                    var DName = Emp_Departments[i];
                    Emp_Department[DName.DeptName] = DName.DeptName;
                }

                $("#Sub_Department").empty();
                $("#Sub_Department").append(
                    $("<option></option>").attr("value", "").text("")
                );

                $.each(Emp_Department, function (key, value) {
                    $("#Sub_Department").append(
                        $("<option></option>").attr("value", key).text(value)
                    );
                });
            }
        });


        $("#Sub_Department").on("change", function () {
            var Sub_Department = $(this).val();

            $.ajax({
                url: baseurl + 'Master/Position_Details',
                type: 'POST',
                data: {
                    Sub_Department: Sub_Department
                },
                success: function (response) {
                    var Response_Data = JSON.parse(response);
                    var Position_Details = Response_Data.Position_Details;

                    var $tableBody = $("#Employee_Position_Mapping_Table tbody");
                    $tableBody.empty();
                    $("#Employee_Position_Mapping_Section").show();
                    $("#Position_Update_Button").hide(); // Hide update button initially

                    $.each(Position_Details, function (index, item) {
                        var uniqueId = `Grade_${index}`;
                        var currentGrade = item.Grade;
                        var grades = ['A', 'B', 'C'];

                        var optionsHtml = `<option value="${currentGrade}">${currentGrade}</option>`;
                        grades.forEach(function (g) {
                            if (g !== currentGrade) {
                                optionsHtml += `<option value="${g}">${g}</option>`;
                            }
                        });

                        var row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.WorkArea}</td>
                        <td>
                            <select class="custom-select2 form-control grade-select" 
                                    name="Grade" 
                                    id="${uniqueId}" 
                                    data-original-grade="${currentGrade}">
                                ${optionsHtml}
                            </select>
                        </td>
                        <td class="status-cell">No changes</td>
                    </tr>
                `;

                        $tableBody.append(row);
                    });

                    $tableBody.find(".custom-select2").select2({
                        placeholder: "",
                        allowClear: false,
                        width: "200px",
                        dropdownCssClass: "custom-select2-dropdown",
                        containerCssClass: "custom-select2-container"
                    });

                    // Grade change handler (single handler only)
                    $tableBody.find(".grade-select").on("change", function () {
                        var $select = $(this);
                        var newGrade = $select.val();
                        var originalGrade = $select.data("original-grade");
                        var $statusCell = $select.closest("tr").find(".status-cell");

                        if (newGrade !== originalGrade) {
                            $statusCell.text("Grade changed").css("color", "red");
                        } else {
                            $statusCell.text("No changes").css("color", "");
                        }

                        // Check if any grade has been changed
                        var anyChanged = false;
                        $("#Employee_Position_Mapping_Table tbody tr").each(function () {
                            var $row = $(this);
                            var $select = $row.find(".grade-select");
                            var original = $select.data("original-grade");
                            var current = $select.val();

                            if (original !== current) {
                                anyChanged = true;
                                return false; // exit loop early
                            }
                        });

                        if (anyChanged) {
                            $("#Position_Update_Button").show();
                        } else {
                            $("#Position_Update_Button").hide();
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        });


        $("#Position_Update_Button").on("click", function () {

            var changedGrades = [];

            $("#Employee_Position_Mapping_Table tbody tr").each(function () {

                var $row = $(this);
                var $select = $row.find(".grade-select");
                var Sub_Department = $("#Sub_Department").val();

                var originalGrade = $select.data("original-grade");
                var Grade = $select.val();

                if (originalGrade !== Grade) {
                    var Position = $row.find("td:nth-child(2)").text().trim(); // Assuming WorkArea is in 2nd column
                    changedGrades.push({
                        Position,
                        Grade,
                        Sub_Department,
                    });
                }
            });

            if (changedGrades.length === 0) {
                alert("No changes to update.");
                return;
            }

            $.ajax({
                url: baseurl + 'Master/Save_Updated_Grades',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ UpdatedGrades: changedGrades }),
                success: function (response) {

                    var Response_Data = JSON.parse(response);
                    var Save_Updated_Grades = Response_Data.Save_Updated_Grades;


                    if (Save_Updated_Grades == 1) {

                        swal({
                            type: "success",
                            title: "success",
                            text: 'Position grade has been updated',
                        });

                    }

                },
                error: function (xhr, status, error) {
                    alert("Failed to update: " + error);
                }
            });
        });






    }


});










