$(document).ready(function () {


    var Add_Menu_Section_Page = $("#Add_Menu_Section_Page").val();
    var User_Persmission_Section_Page = $("#User_Persmission_Section_Page").val();
    var Add_Sub_Menu_Section_Page = $("#Add_Sub_Menu_Section_Page").val();

    if (Add_Menu_Section_Page == 'Add_Menu_Section_Page') {


        $("#Insert_Menu_Btn").on("click", function () {
            var Menu_ID = $("#Menu_ID").val();
            var Menu_Name = $("#Menu_Name").val();

            // alert(Menu_Icon);


            $.ajax({
                url: baseurl + "Privacy/Add_Menu",
                type: "POST",
                data: {
                    Menu_ID,
                    Menu_Name,

                },
                success: function (response) {
                    var Response_Data = JSON.parse(response);

                    if (Response_Data == 0) {
                        swal({
                            type: "warning",
                            title: "Warning",
                            text: 'Unable to save the menu. Please verify and try again.',
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        swal({
                            type: "success",
                            title: "Success",
                            text: 'Menu has been added successfully.',
                        }).then(function () {
                            window.location.reload();
                        });
                    }
                }
            });
        });


        $(".edit-menu").on("click", function () {
            var menuId = $(this).data("id");
            var menu = $(this).data("menu");



            $("#Model_MenuID").val(menuId);
            $("#Model_Menu").val(menu);
            $("#Model_Standard_ID").val(menuId);


            $("#Edit_Menu_Model").modal("show");
        });


        $("#Final_Menu_Edit_Btn").on("click", function () {
            var Menu_ID = $("#Model_MenuID").val();
            var Menu_Name = $("#Model_Menu").val();


            $.ajax({
                url: baseurl + "Privacy/Edit_Menu",
                type: "POST",
                data: {
                    Menu_ID: Menu_ID,
                    Menu_Name: Menu_Name,

                },
                success: function (res) {
                    console.log("Response:", res);
                    try {
                        var result = JSON.parse(res);
                        if (result.status === 'success') {
                            $("#Edit_Menu_Model").modal("hide");
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else {
                            console.log("Update failed:", result.message);
                            alert("Update failed: " + result.message);
                        }
                    } catch (e) {
                        console.error("Invalid response:", e);
                        alert("Invalid server response.");
                    }
                },
                error: function (xhr) {
                    console.error("Server error:", xhr.statusText);
                    alert("Server error: " + xhr.statusText);
                }
            });
        });

        $(".delete-menu").on("click", function () {
            var MenuId = $(this).data("id");

            swal({
                title: 'Are you sure?',
                text: "Do you really want to delete this menu?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'

            }).then((result) => {
                // if (result.isConfirmed) {
                $.ajax({
                    url: baseurl + "Privacy/Delete_Menu",
                    type: "POST",
                    data: { MenuId: MenuId },
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr) {
                        swal({
                            icon: 'error',
                            title: 'Server Error',
                            text: xhr.statusText
                        });
                    }
                });
                // }
            });
        });

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

        $("#Insert_Sub_Menu_Btn").on("click", function () {
            var SubMenu_ID = $("#SubMenu_ID").val();
            var Menu_Name = $("#Menu_Name").val();
            var Sub_Menu = $("#Sub_Menu").val();

            $.ajax({
                url: baseurl + "Privacy/Add_SubMenu",
                type: "POST",
                data: {
                    SubMenu_ID,
                    Menu_Name,
                    Sub_Menu
                },
                success: function (response) {

                    var Response_Data = JSON.parse(response);
                    var Add_SubMenu = Response_Data.Add_SubMenu;

                    if (Response_Data == 0) {

                        swal({
                            type: "warning",
                            title: "warning",
                            text: 'Unable to save the submenu. Please verify and try again.',
                        }).then(function () {
                            window.location.reload();
                        });

                    } else {

                        swal({
                            type: "success",
                            title: "success",
                            text: 'SubMenu have been updated successfully. Kindly check.',
                        }).then(function () {
                            window.location.reload();
                        });

                    }
                }
            })
        })


        $(".edit-menu").on("click", function () {
            var menuId = $(this).data("id");
            var menu = $(this).data("menu");
            var submenu_id = $(this).data("submenu_id");
            var submenu = $(this).data("submenu");

            $("#Model_MenuID").val(menuId);
            $("#Model_Menu").val(menu);
            $("#Model_SubMenu_ID").val(submenu_id);
            $("#Model_Sub_Menu").val(submenu);
            $("#Model_Standard_ID").val(menuId);

            $("#Edit_SubMenu_Model").modal("show");
        });


        $("#Final_SubMenu_Edit_Btn").on("click", function () {
            var Menu_ID = $("#Model_MenuID").val();
            var Menu_Name = $("#Model_Menu").val();
            var SubMenu_ID = $("#Model_SubMenu_ID").val();
            var SubMenu_Name = $("#Model_Sub_Menu").val();
            //   alert(SubMenu_ID);

            $.ajax({
                url: baseurl + "Privacy/Edit_SubMenu",
                type: "POST",
                data: {
                    Menu_ID,
                    Menu_Name, SubMenu_ID, SubMenu_Name
                },
                success: function (res) {
                    console.log("Response:", res);
                    try {
                        var result = JSON.parse(res);
                        if (result.status === 'success') {
                            $("#Final_SubMenu_Edit_Btn").modal("hide");
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else {
                            console.log("Update failed:", result.message);
                            alert("Update failed: " + result.message);
                        }
                    } catch (e) {
                        console.error("Invalid response:", e);
                        alert("Invalid server response.");
                    }
                },
                error: function (xhr) {
                    console.error("Server error:", xhr.statusText);
                    alert("Server error: " + xhr.statusText);
                }
            });
        });


        $(".delete-menu").on("click", function () {
            var SubMenu_Id = $(this).data("id");

            swal({
                title: 'Are you sure?',
                text: "Do you really want to delete this menu?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {

                $.ajax({
                    url: baseurl + "Privacy/Delete_SubMenu",
                    type: "POST",
                    data: { SubMenu_Id },
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr) {
                        swal({
                            icon: 'error',
                            title: 'Server Error',
                            text: xhr.statusText
                        });
                    }
                });

            });
        });


        $("#Update_Sub_Menu_Btn").on('click', function () {
            // alert('work')


        })









    } else if (User_Persmission_Section_Page == 'User_Persmission_Section_Page') {


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
                                   
                          `;
                            $("#Privacy_Table tbody").append(row);
                        });

                    }

                },


                error: function (xhr, status, error) {
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
                    Delete: Delete,

                });
            });

            $.ajax({
                url: baseurl + "Privacy/Update_Permission",
                type: "POST",
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({
                    Menu: Menu,
                    UserRole: UserRole,
                    Location: Location,
                    Rights: Rights_Data
                }),
                success: function (Response_Data) {

                    // var Response_Data = JSON.parse(response);
                    var Get_SubMenu = Response_Data.Get_SubMenus;

                    if (Response_Data.Update_Permission == '1') {

                        swal({
                            type: "success",
                            title: "Success",
                            text: 'Permissions have been updated successfully.',
                        });

                        $("#Privacy_Table_Section").show();
                        $("#Privacy_Table tbody").empty();

                        $.each(Get_SubMenu, function (index, item) {
                            var screenChecked = item.Screen === "1" ? "checked" : "";
                            var editChecked = item.Edit === "1" ? "checked" : "";
                            var deleteChecked = item.Remove === "1" ? "checked" : "";

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

        });


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