$(document).ready(function () {


    var Add_Menu_Section_Page = $("#Add_Menu_Section_Page").val();
    var User_Persmission_Section_Page = $("#User_Persmission_Section_Page").val();
    var Add_Sub_Menu_Section_Page = $("#Add_Sub_Menu_Section_Page").val();

    if (Add_Menu_Section_Page == 'Add_Menu_Section_Page') {



        $("#Insert_Menu_Btn").on("click", function () {

            var Menu_ID = $("#Menu_ID").val();
            var Menu_Name = $("#Menu_Name").val();

            $.ajax({
                url: baseurl + "Privacy/Add_Menu",
                type: "POST",
                data: {
                    Menu_ID,
                    Menu_Name
                },
                success: function (response) {

                    var Response_Data = JSON.parse(response);
                    var Add_Menu = Response_Data.Add_Menu;

                    if (Response_Data == 0) {

                        swal({
                            type: "warning",
                            title: "warning",
                            text: 'Unable to save the menu. Please verify and try again.',
                        }).then(function() {
                            window.location.reload();
                        });

                    } else {

                        swal({
                            type: "success",
                            title: "success",
                            text: 'Menu have been updated successfully. Kindly check.',
                        }).then(function() {
                            window.location.reload();
                        });

                    }



                }


            })


        })






    } else if (Add_Sub_Menu_Section_Page == 'Add_Sub_Menu_Section_Page') {


        $.ajax({
            url: baseurl + "Privacy/Get_Menus",
            type: "POST",
            success: function (ressponse) {

              var Response_Data = JSON.parse(ressponse);
              var Get_Menu = Response_Data.Get_Menus;

              var Menus = { "": "" };

              for (var i = 0; i < Get_Menu.length; i++) {
                var DName = Get_Menu[i];
                Menus[DName.Menu] = DName.Menu;
              }

              $("#Menu_Name").empty();

              $.each(Menus, function (index, value) {
                $("#Menu_Name").append(
                  $("<option></option>").attr("value", value).text(value)
                );
              });
            }

        })

        // $("#Menu_Name").on("change", function () {

        //     var Menu = $("#Menu_Name").val();


        //     $.ajax({
        //         url: baseurl + "Privacy/Get_Sub_Menus",
        //         type: "POST",
        //         data: {
        //             Menu,
        //             },
        //         success: function (ressponse) {

        //           var Response_Data = JSON.parse(ressponse);
        //           var Get_Menu = Response_Data.Get_Sub_Menus;

        //           var Sub_Menus = { "": "" };

        //           for (var i = 0; i < Get_Menu.length; i++) {
        //             var DName = Get_Menu[i];
        //             Sub_Menus[DName.Menu] = DName.Menu;
        //           }

        //           $("#Sub_Menu").empty();

        //           $.each(Sub_Menus, function (index, value) {
        //             $("#Sub_Menu").append(
        //               $("<option></option>").attr("value", value).text(value)
        //             );
        //           });
        //         }

        //     })





        // })









    }else if (User_Persmission_Section_Page == 'User_Persmission_Section_Page') {


        $("#Users").on("change", function () {


            $.ajax({
              url: baseurl + "Privacy/Get_Menus",
              type: "POST",
              success: function (ressponse) {

                var Response_Data = JSON.parse(ressponse);
                var Get_Menu = Response_Data.Get_Menus;

                var Menus = { "": "" };

                for (var i = 0; i < Get_Menu.length; i++) {
                  var DName = Get_Menu[i];
                  Menus[DName.Menu] = DName.Menu;
                }

                $("#Menu").empty();

                $.each(Menus, function (index, value) {
                  $("#Menu").append(
                    $("<option></option>").attr("value", value).text(value)
                  );
                });
              }

            })


          })


          $("#Menu").on("change", function () {

              var Menu = $("#Menu").val();
              var UserRole = $("#Users").val();
              var Location = $("#Location").val();


              $.ajax({
                  url: baseurl + "Privacy/Get_SubMenus",
                  type: "POST",
                  data: {
                      Menu: Menu,
                      UserRole: UserRole,
                      Location: Location
                  },
                  success: function(response) {
                      var Response_Data = JSON.parse(response);
                      var Get_SubMenu = Response_Data.Get_SubMenus;

                      if (Response_Data == 0) {

                          $("#Privacy_Table_Section").hide();
                         $("#Privacy_Table tbody").empty();

                      } else {

                      $("#Privacy_Table_Section").show();
                      $("#Privacy_Table tbody").empty();

                      $.each(Get_SubMenu, function (index, item) {
                          var screenChecked = item.Screen === "1" ? "checked" : "";
                          var editChecked = item.Edit === "1" ? "checked" : "";
                          var deleteChecked = item.Remove === "1" ? "checked" : ""; // <-- fix here

                          var row = `
                              <tr>
                                  <td>${index + 1}</td>
                                  <td class="Sub_Menu">${item.Sub_Menu}</td>
                                  <td><input type="checkbox" class="Screen" style="transform: scale(1.4); margin: 5px;" ${screenChecked}></td>
                                  <td><input type="checkbox" class="Edit" style="transform: scale(1.4); margin: 5px;" ${editChecked}></td>
                                  <td><input type="checkbox" class="Delete" style="transform: scale(1.4); margin: 5px;" ${deleteChecked}></td>
                              </tr>
                          `;
                          $("#Privacy_Table tbody").append(row);
                      });

                      }

                  },


                  error: function(xhr, status, error) {
                      console.error("AJAX Error: ", status, error);
                  }
              });


          })

          $("#User_Right_Button").on("click", function () {

              var Menu = $("#Menu").val();
              var UserRole = $("#Users").val();
              var Location = $("#Location").val();

              var Rights_Data = [];

              $("#Privacy_Table tbody tr").each(function () {

              var SubMenuID = $(this).find(".Sub_Menu").text().trim();
              var Screen = $(this).find(".Screen").is(":checked") ? "1" : "0";
              var Edit = $(this).find(".Edit").is(":checked") ? "1" : "0";
              var Delete = $(this).find(".Delete").is(":checked") ? "1" : "0";

                  Rights_Data.push({
                      Sub_Menu: SubMenuID,
                      Screen: Screen,
                      Edit: Edit,
                      Delete: Delete
                  });
              });

              $.ajax({
                  url: baseurl + "Privacy/Update_Permission",
                  type: "POST",
                  contentType: "application/json",
                  data: JSON.stringify({
                      Menu: Menu,
                      UserRole: UserRole,
                      Location: Location,
                      Rights: Rights_Data
                  }),
                  success: function (response) {

                      var Response_Data = JSON.parse(response);
                      var Get_SubMenu = Response_Data.Get_SubMenus;

                      if (Response_Data.Update_Permission == '1') {


                          swal({
                              type: "success",
                              title: "success",
                              text: 'Permissions have been updated successfully. Kindly check.',
                            });



                          $("#Privacy_Table_Section").show();
                          $("#Privacy_Table tbody").empty();

                          $.each(Get_SubMenu, function(index, item) {
                              var screenChecked = item.Screen === "1" ? "checked" : "";
                              var editChecked = item.Edit === "1" ? "checked" : "";
                              var deleteChecked = item.Remove === "1" ? "checked" : ""; // <-- fix here

                              var row = `
                                  <tr>
                                      <td>${index + 1}</td>
                                      <td class="Sub_Menu">${item.Sub_Menu}</td>
                                      <td><input type="checkbox" class="Screen" style="transform: scale(1.4); margin: 5px;" ${screenChecked}></td>
                                      <td><input type="checkbox" class="Edit" style="transform: scale(1.4); margin: 5px;" ${editChecked}></td>
                                      <td><input type="checkbox" class="Delete" style="transform: scale(1.4); margin: 5px;" ${deleteChecked}></td>
                                  </tr>
                              `;
                              $("#Privacy_Table tbody").append(row);
                          });

                      }


                  }



              });

          })



          $("#Location").on("change", function () {

              var Menu = $("#Menu").val();
              var UserRole = $("#Users").val();
              var Location = $("#Location").val();


              $.ajax({
                  url: baseurl + "Privacy/Get_SubMenus",
                  type: "POST",
                  data: {
                      Menu: Menu,
                      UserRole: UserRole,
                      Location: Location
                  },
                  success: function (response) {
                      var Response_Data = JSON.parse(response);
                      var Get_SubMenu = Response_Data.Get_SubMenus;

                      if (Response_Data == 0) {

                          $("#Privacy_Table_Section").hide();
                         $("#Privacy_Table tbody").empty();

                      } else {

                      $("#Privacy_Table_Section").show();
                      $("#Privacy_Table tbody").empty();

                      $.each(Get_SubMenu, function (index, item) {
                          var screenChecked = item.Screen === "1" ? "checked" : "";
                          var editChecked = item.Edit === "1" ? "checked" : "";
                          var deleteChecked = item.Remove === "1" ? "checked" : ""; // <-- fix here

                          var row = `
                              <tr>
                                  <td>${index + 1}</td>
                                  <td class="Sub_Menu">${item.Sub_Menu}</td>
                                  <td><input type="checkbox" class="Screen" style="transform: scale(1.4); margin: 5px;" ${screenChecked}></td>
                                  <td><input type="checkbox" class="Edit" style="transform: scale(1.4); margin: 5px;" ${editChecked}></td>
                                  <td><input type="checkbox" class="Delete" style="transform: scale(1.4); margin: 5px;" ${deleteChecked}></td>
                              </tr>
                          `;
                          $("#Privacy_Table tbody").append(row);
                      });

                      }


                  },
              })


          })


          $("#Users").on("change", function () {

              var Menu = $("#Menu").val();
              var UserRole = $("#Users").val();
              var Location = $("#Location").val();


              $.ajax({
                  url: baseurl + "Privacy/Get_SubMenus",
                  type: "POST",
                  data: {
                      Menu: Menu,
                      UserRole: UserRole,
                      Location: Location
                  },
                  success: function (response) {
                      var Response_Data = JSON.parse(response);
                      var Get_SubMenu = Response_Data.Get_SubMenus;

                      if (Response_Data == 0) {

                          $("#Privacy_Table_Section").hide();
                         $("#Privacy_Table tbody").empty();

                      } else {

                      $("#Privacy_Table_Section").show();
                      $("#Privacy_Table tbody").empty();

                      $.each(Get_SubMenu, function (index, item) {
                          var screenChecked = item.Screen === "1" ? "checked" : "";
                          var editChecked = item.Edit === "1" ? "checked" : "";
                          var deleteChecked = item.Remove === "1" ? "checked" : ""; // <-- fix here

                          var row = `
                              <tr>
                                  <td>${index + 1}</td>
                                  <td class="Sub_Menu">${item.Sub_Menu}</td>
                                  <td><input type="checkbox" class="Screen" style="transform: scale(1.4); margin: 5px;" ${screenChecked}></td>
                                  <td><input type="checkbox" class="Edit" style="transform: scale(1.4); margin: 5px;" ${editChecked}></td>
                                  <td><input type="checkbox" class="Delete" style="transform: scale(1.4); margin: 5px;" ${deleteChecked}></td>
                              </tr>
                          `;
                          $("#Privacy_Table tbody").append(row);
                      });

                      }


                  },
              })


          })


    }










  })