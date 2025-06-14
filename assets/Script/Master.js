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


});










