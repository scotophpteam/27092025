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


	var Employee_Grade_Page = $("#Employee_Grade_Page").val();
	var Employee_Attendance_Record_Page = $("#Employee_Attendance_Record_Page").val();

	if (Employee_Grade_Page == 'Employee_Grade_Page') {

		$.ajax({
			url: baseurl + "Grade_Master/Get_Sub_Department",
			tyep: "POST",
			success: function (response) {

				var Response_Data = JSON.parse(response);
				var Get_Sub_Department = Response_Data.Get_Sub_Department;

				var Sub_Department = { "": "" };

				for (var i = 0; i < Get_Sub_Department.length; i++) {
				  var DName = Get_Sub_Department[i];
				  Sub_Department[DName.DeptName] = DName.DeptName;
				}

				$("#Sub_Department").empty();

				$.each(Sub_Department, function (index, value) {
				  $("#Sub_Department").append(
					$("<option></option>").attr("value", value).text(value)
				  );
				});




			}
		})

		// index Page Section

		$("#Inactive").hide();
		$("#Inactive1").hide();
		$("#Trainee").hide();
		$("#Day_Wise_Visible").hide();

		$("#Card_InActives").hide();
		$("#Card_Active").hide();
		$("#Card_Traniee").hide();
		$("#Card_OnRoll").hide();
		$("#OnRoll_Day_Wise_Visible").hide();

		$("#Employee_Attendance_List_Grade").hide();

		$("#Employee_Status").on("change", function () {


			$("#Card_InActives").hide();
			$("#Card_Active").hide();
			$("#Card_Traniee").hide();
			$("#Card_OnRoll").hide();
			$("#Card_OnRoll").hide();
			$("#OnRoll_Day_Wise_Visible").hide();

			$("#Inactive").hide();
			$("#Inactive1").hide();
			$("#Trainee").hide();

			var Department = $("#Sub_Department").val();
			var Employee_Status = $("#Employee_Status").val();

			if (Employee_Status == "InActive") {

				// Card_InActives

				$("#Button_Hide").hide();
				$("#Inactive").show();
				$("#Inactive1").show();
				$("#Day_Wise_Visible").show();
				$("#Card_InActives").hide();
				$("#OnRoll_Day_Wise_Visible").hide();


			} else if (Employee_Status == "Active") {

				$("#Inactive").hide();
				$("#Inactive1").hide();
				$("#Day_Wise_Visible").hide();
				$("#Button_Hide").show();
				$("#OnRoll_Day_Wise_Visible").hide();

			} else if (Employee_Status == "Traniee") {
				$("#Inactive").hide();
				$("#Inactive1").hide();
				$("#Trainee").show();
				$("#Day_Wise_Visible").hide();
				$("#Button_Hide").show();
				$("#OnRoll_Day_Wise_Visible").hide();
			} else if (Employee_Status == "OnRoll") {
				// Show the 'From Date' and 'To Date' fields when 'OnRoll' is selected
				$("#Inactive").show();
				$("#Inactive1").show();
				$("#Card_OnRoll").hide();

				// $("#Inactive").hide();
				// $("#Inactive1").hide();
				$("#Day_Wise_Visible").hide();
				$("#Button_Hide").hide();
				$("#OnRoll_Day_Wise_Visible").show();
			}
		});

		//Card Hide Section
		$("#Card_InActive").hide();
		$("#Card_Active").hide();





		$("#Sub_Department, #Employee_Status").on("change", function () {
			var department = $("#Sub_Department option:selected").text().trim();
			var type = $("#Employee_Status").val().trim();

			if (department && type) {
				var typeLabel = "";

				switch (type) {
					case "Active":
						typeLabel = "Active Employee List";
						break;
					case "InActive":
						typeLabel = "Inactive Employee List";
						break;
					case "Traniee":
						typeLabel = "Trainee Employee List";
						break;
					case "OnRoll":
						typeLabel = "OnRoll Employee List";
						break;
					default:
						typeLabel = "Employee List";
				}

				var fullTitle = department + " - " + typeLabel;

				$("#Table_Title_Active, #Table_Title_InActive, #Table_Title_Trainee, #Table_Title_Card_OnRoll").hide();

				switch (type) {
					case "Active":
						$("#Table_Title_Active").text(fullTitle).show();
						break;
					case "InActive":
						$("#Table_Title_InActive").text(fullTitle).show();
						break;
					case "Traniee":
						$("#Table_Title_Trainee").text(fullTitle).show();
						break;
					case "OnRoll":
						$("#Table_Title_Card_OnRoll").text(fullTitle).show();
						break;
				}
			}
		});





		$("#Employee_Statuss").on("click", function () {

			var Department = $("#Sub_Department").val();
			var Employee_Status = $("#Employee_Status").val();

			if (Employee_Status == "Active") {
				$("#Card_InActive").hide();
				$("#Card_Active").show();

				$.ajax({
					type: "POST",
					url: baseurl + "Grade_Master/Active",
					data: {
						Department: Department,
						Employee_Status: Employee_Status,
					},
					success: function (response) {

						var responseData = JSON.parse(response);
						$("#Active_Employee").empty();

						if (responseData.Active_Employee_List.length == 0) {
							$("#Card_Active").hide();

							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found.",
							  });
						} else {

							}

							var Active_Employee_List = responseData.Active_Employee_List;

							$.each(Active_Employee_List, function (index, item) {
								var badgeColor = item.IsActive ? "green" : "red";
								var badgeText = item.IsActive ? "Active" : "In Active";

								var row = `<tr>
									<td>${index + 1}</td>
									<td>${item.MachineID}</td>
									<td>${item.FirstName}</td>
									<td>${item.DOJ}</td>
									<td>${item.ExperienceFormatted}</td>
									<td>${item.Grade}</td>
									<td>${item.EmployeeMobile}</td>
									<td>
										<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
											${badgeText}
										</span>
									</td>
									 <!-- Displaying the calculated experience
								</tr>`;

								$("#Active_Employee").append(row);
							});



					},
					success: function (response) {
						var responseData = JSON.parse(response);
						$("#Active_Employee").empty();

						if (responseData.Active_Employee_List.length == 0) {
							$("#Card_Active").hide();

							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found.",
							  });
						} else {

							}

							var Active_Employee_List = responseData.Active_Employee_List;

							$.each(Active_Employee_List, function (index, item) {
								var badgeColor = item.IsActive ? "green" : "red";
								var badgeText = item.IsActive ? "Active" : "In Active";

								// Ensure the Experience is calculated and formatted as 'X Years Y Months'
								var experienceFormatted = item.ExperienceFormatted || "N/A"; // Example: '1 Years 6 Months'

								var row = `<tr>
									<td>${index + 1}</td>
									<td>${item.MachineID}</td>
									<td>${item.FirstName}</td>
									<td>${item.DOJ}</td>
									<td>${item.ExperienceFormatted}</td>
									<td>${item.Grade}</td>
									<td>${item.EmployeeMobile}</td>
									<td>
										<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
											${badgeText}
										</span>
									</td>
									 <!-- Displaying the calculated experience
								</tr>`;

								$("#Active_Employee").append(row);
							});



					},
				});
			} else if (Employee_Status == "InActive") {


				$("#InActive_Employee").empty();

				$("#Card_InActive").show();
				$("#Card_Active").hide();

				var FromDate = $("#Fdate").val();
				var ToDate = $("#Tdate").val();

				$.ajax({
					type: "POST",
					url: baseurl + "Grade_Master/InActive",
					data: {
						Department: Department,
						Employee_Status: Employee_Status,
						FromDate: FromDate,
						ToDate: ToDate,
					},
					success: function (response) {
						$("#InActive_Employee").empty();

						var responseData = JSON.parse(response);
						var InActiveEmployee_List = responseData.InActiveEmployee_List;

						if (responseData.InActiveEmployee_List.length == 0) {
							$("#Card_InActive").hide();

							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found.",
							  });
						} else {

							}

							$.each(InActiveEmployee_List, function (index, item) {
								if (item.IsActive == "No" || item.IsActive == "NO") {
									var badgeColor = "red";
									var badgeText = "In Active";

									// Ensure that ExperienceFormatted is passed from backend
									var experienceFormatted = item.ExperienceFormatted || "N/A"; // Example: '1 Years 6 Months'

									var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.MachineID}</td>
										<td>${item.FirstName}</td>
										<td>${item.DOR}</td>
										<td>${item.DOR}</td>
										<td>${item.Experience}</td>
										<td></td> <!-- Displaying experience in 'X Years Y Months' format -->
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>

									</tr>`;

									$("#InActive_Employee").append(row);
								}
							});

							// Reinitialize the DataTable


					},
				});
			} else if (Employee_Status == "Traniee") {
				var Department = $("#Sub_Department").val();
				var Employee_Status = $("#Employee_Status").val();
				var TaineeMonth = $("#TaineeMonth").val();

				$.ajax({
					type: "POST",
					url: baseurl + "Grade_Master/Trainee",
					data: {
						Department: Department,
						Employee_Status: Employee_Status,
						TaineeMonth: TaineeMonth,
					},
					success: function (response) {
						$("#TraineeEmployee").empty();

						var responseData = JSON.parse(response);

						if (responseData.Trainee_Employee_List == 0) {
							$("#Card_Traniee").hide();

							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found.",
							  });
						} else {
							$("#Card_Traniee").show();


							}

							var Trainee_Employee_List = responseData.Trainee_Employee_List;

							$.each(Trainee_Employee_List, function (index, item) {
								// Format DOJ as DD-MM-YYYY
								var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");

								var badgeColor = item.IsActive === "Yes" ? "green" : "red";
								var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

								var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.MachineID}</td>
										<td>${item.FirstName}</td>
										<td>${item.DOJ}</td>
										<td>${item.ExperienceFormatted}</td>
										<td>${item.ExperienceMonths}</td>
										<td></td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>

									</tr>`;

								$("#TraineeEmployee").append(row);
							});



					},
				});
			} else if (Employee_Status == "OnRoll") {
				var Department = $("#Sub_Department").val();
				var Employee_Status = $("#Employee_Status").val();
				var FromDate = $("#Fdate").val();
				var ToDate = $("#Tdate").val();

				$.ajax({
					type: "POST",
					url: baseurl + "Grade_Master/OnRoll",
					data: {
						Department: Department,
						Employee_Status: Employee_Status,
						FromDate: FromDate,
						ToDate: ToDate,
					},
					success: function (response) {
						$("#OnrollEmployee").empty();

						var responseData = JSON.parse(response);

						if (responseData.OnRoll_Employee_List.length === 0) {
							$("#Card_OnRoll").hide();
							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found.",
							  });
						} else {
							$("#Card_OnRoll").show();


							}

							var OnRoll_Employee_List = responseData.OnRoll_Employee_List;

							$.each(OnRoll_Employee_List, function (index, item) {
								// Format DOJ and SixMonthsAfterDOJ as DD-MM-YYYY
								var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");
								var formattedSixMonthsAfterDOJ = new Date(
									item.SixMonthsAfterDOJ
								).toLocaleDateString("en-GB");

								var badgeColor = item.IsActive === "Yes" ? "green" : "red";
								var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

								var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.MachineID}</td>
										<td>${item.FirstName}</td>
										<td>${item.DOJ}</td>
										<td>${item.ExperienceFormatted}</td>
										<td>${item.Months_Active}</td>
										<td></td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>

									</tr>`;

								$("#OnrollEmployee").append(row);
							});



					},
				});
			}
		});

		// -----------------------------------------Attendance - Sheet - Employee Details-----------------------------------------//

		var dataTableInitialized = false;

		// Inactive Employee List

		$("#Last_30").on("click", function () {
			// Clear existing table rows
			$("#InActive_Employee").empty();

			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/Last_thirdy",
				type: "POST",
				data: { Department: Department },
				success: function (response) {
					try {
						var responseData = JSON.parse(response);
						var InActiveEmployee_List = responseData.Last_thirdy;

						if (InActiveEmployee_List.length === 0) {
							$("#Card_InActives").hide();
							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found!",
							});
						} else {
							$("#Card_InActives").show();

							var rows = [];

							$.each(InActiveEmployee_List, function (index, item) {
								var row = `
									<tr>
										<td>${index + 1}</td>
										<td>${item.ExistingCode}</td>
										<td>${item.FirstName}</td>
										<td>${item.doj}</td>
										<td>${item.DOR}</td>
										<td>${item.WorkingMonths}</td>
										<td>${item.Grade}</td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: red; color: white; padding: 5px 10px; border-radius: 5px;">
												In Active
											</span>
										</td>
									</tr>`;
								rows.push(row);
							});

							$("#InActive_Employee").append(rows.join(''));
						}
					} catch (e) {
						console.error("Error parsing JSON response:", e);
						swal({
							type: "error",
							title: "Error",
							text: "Failed to process employee data.",
						});
					}
				},

				error: function (xhr, status, error) {
					console.error("AJAX error:", status, error);
					swal({
						type: "error",
						title: "Error",
						text: "An error occurred while fetching employee data.",
					});
				}
			});
		});


		$("#Last_60").on("click", function () {

			$("#InActive_Employee").empty();
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/Last_Sixty",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					try {
						var responseData = JSON.parse(response);
						var InActiveEmployee_List = responseData.Last_Sixty;

						if (InActiveEmployee_List.length === 0) {
							$("#Card_InActives").hide();
							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found!",
							});
						} else {
							$("#Card_InActives").show();

							var rows = [];

							$.each(InActiveEmployee_List, function (index, item) {
								var row = `
									<tr>
										<td>${index + 1}</td>
										<td>${item.ExistingCode}</td>
										<td>${item.FirstName}</td>
										<td>${item.doj}</td>
										<td>${item.DOR}</td>
										<td>${item.WorkingMonths}</td>
										<td>${item.Grade}</td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: red; color: white; padding: 5px 10px; border-radius: 5px;">
												In Active
											</span>
										</td>
									</tr>`;
								rows.push(row);
							});

							$("#InActive_Employee").append(rows.join(''));
						}
					} catch (e) {
						console.error("Error parsing JSON response:", e);
						swal({
							type: "error",
							title: "Error",
							text: "Failed to process employee data.",
						});
					}
				},
			});
		});

		$("#Last_90").on("click", function () {

			$("#InActive_Employee").empty();
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/Last_Ninety",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					try {
						var responseData = JSON.parse(response);
						var InActiveEmployee_List = responseData.Last_Ninety;

						if (InActiveEmployee_List.length === 0) {
							$("#Card_InActives").hide();
							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found!",
							});
						} else {
							$("#Card_InActives").show();

							var rows = [];

							$.each(InActiveEmployee_List, function (index, item) {
								var row = `
									<tr>
										<td>${index + 1}</td>
										<td>${item.ExistingCode}</td>
										<td>${item.FirstName}</td>
										<td>${item.doj}</td>
										<td>${item.DOR}</td>
										<td>${item.WorkingMonths}</td>
										<td>${item.Grade}</td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: red; color: white; padding: 5px 10px; border-radius: 5px;">
												In Active
											</span>
										</td>
									</tr>`;
								rows.push(row);
							});

							$("#InActive_Employee").append(rows.join(''));
						}
					} catch (e) {
						console.error("Error parsing JSON response:", e);
						swal({
							type: "error",
							title: "Error",
							text: "Failed to process employee data.",
						});
					}
				},
			});
		});


		$("#Last_120").on("click", function () {

			$("#InActive_Employee").empty();
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/Last_One_Twenty",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					try {
						var responseData = JSON.parse(response);
						var InActiveEmployee_List = responseData.Last_One_Twenty;

						if (InActiveEmployee_List.length === 0) {
							$("#Card_InActives").hide();
							swal({
								type: "error",
								title: "Error",
								text: "Employee Details Not Found!",
							});
						} else {
							$("#Card_InActives").show();

							var rows = [];

							$.each(InActiveEmployee_List, function (index, item) {
								var row = `
									<tr>
										<td>${index + 1}</td>
										<td>${item.ExistingCode}</td>
										<td>${item.FirstName}</td>
										<td>${item.doj}</td>
										<td>${item.DOR}</td>
										<td>${item.WorkingMonths}</td>
										<td>${item.Grade}</td>
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: red; color: white; padding: 5px 10px; border-radius: 5px;">
												In Active
											</span>
										</td>
									</tr>`;
								rows.push(row);
							});

							$("#InActive_Employee").append(rows.join(''));
						}
					} catch (e) {
						console.error("Error parsing JSON response:", e);
						swal({
							type: "error",
							title: "Error",
							text: "Failed to process employee data.",
						});
					}
				},
			});
		});

		// On Roll Employee List

		$("#On_Last_30").on("click", function () {
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/On_Last_thirdy",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					$("#OnrollEmployee").empty();

					var responseData = JSON.parse(response);
					var OnRoll_Employee_List = responseData.On_Last_thirdy;

					if (OnRoll_Employee_List.length === 0) {
						$("#Card_OnRoll").hide();


						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found!",
						});

					} else {
						$("#Card_OnRoll").show();

						// if ($.fn.dataTable.isDataTable(".table")) {
						// 	$(".table").DataTable().clear().destroy();
						// }

						$.each(OnRoll_Employee_List, function (index, item) {
							// Format DOJ and SixMonthsAfterDOJ as DD-MM-YYYY
							var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");
							var formattedSixMonthsAfterDOJ = new Date(
								item.SixMonthsAfterDOJ
							).toLocaleDateString("en-GB");

							var badgeColor = item.IsActive === "Yes" ? "green" : "red";
							var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

							var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.DeptName}</td>
										<td>${formattedDOJ}</td>
										<td>${item.FirstName}</td>
										<td>${item.MachineID}</td>
										<td>${item.ExperienceFormatted}</td>
										<td >${formattedSixMonthsAfterDOJ}</td>
										<td>${item.EmployeeMobile}</td>
										<td class='text-center'>${item.Months_Active}</td>


										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>
									</tr>`;

							$("#OnrollEmployee").append(row);
						});

						// var table = $(".table").DataTable({
						// 	paging: true,
						// 	ordering: true,
						// 	info: true,
						// 	searching: true,
						// });
					}
				},
			});
		});

		$("#Last_120").on("click", function () {

			$("#InActive_Employee").empty();

			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/Last_One_Twenty",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {

					var responseData = JSON.parse(response);
					var InActiveEmployee_List = responseData.Last_One_Twenty;

					if (InActiveEmployee_List.length == 0) {

						$("#InActive_Employee").empty();
					    $("#Card_InActives").hide();



						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found!",
						  });
					} else {

						// if ($.fn.dataTable.isDataTable(".table")) {
						// 	$(".table").DataTable().clear().destroy();
						// }

						$("#Card_InActives").show();


						$.each(InActiveEmployee_List, function (index, item) {

							if (item.IsActive == "No" || item.IsActive == "NO") {

								var badgeColor = "red";
								var badgeText = "In Active";

								// Ensure that ExperienceFormatted is passed from backend
								// var experienceFormatted = item.ExperienceFormatted || "N/A"; // Example: '1 Years 6 Months'

								var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.MachineID}</td>
										<td>${item.FirstName}</td>
										<td>${item.DOR}</td>
										<td>${item.DOR}</td>
										<td>${item.Experience}</td>
										<td></td> <!-- Displaying experience in 'X Years Y Months' format -->
										<td>${item.EmployeeMobile}</td>
										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>

									</tr>`;

								$("#InActive_Employee").append(row);


							}
						});
						// var table = $(".table").DataTable({
						// 	paging: true,
						// 	ordering: true,
						// 	info: true,
						// 	searching: true,
						// });


					}
				},
			});
		});






		$("#On_Last_60").on("click", function () {
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/On_Last_Sixty",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					$("#OnrollEmployee").empty();

					var responseData = JSON.parse(response);
					var OnRoll_Employee_List = responseData.On_Last_Sixty;

					if (OnRoll_Employee_List.length === 0) {
						$("#Card_OnRoll").hide();


						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found!",
						});

					} else {
						$("#Card_OnRoll").show();

						// if ($.fn.dataTable.isDataTable(".table")) {
						// 	$(".table").DataTable().clear().destroy();
						// }

						$.each(OnRoll_Employee_List, function (index, item) {
							// Format DOJ and SixMonthsAfterDOJ as DD-MM-YYYY
							var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");
							var formattedSixMonthsAfterDOJ = new Date(
								item.SixMonthsAfterDOJ
							).toLocaleDateString("en-GB");

							var badgeColor = item.IsActive === "Yes" ? "green" : "red";
							var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

							var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.DeptName}</td>
										<td>${formattedDOJ}</td>
										<td>${item.FirstName}</td>
										<td>${item.MachineID}</td>
										<td>${item.ExperienceFormatted}</td>
										<td >${formattedSixMonthsAfterDOJ}</td>
										<td>${item.EmployeeMobile}</td>
										<td class='text-center'>${item.Months_Active}</td>


										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>
									</tr>`;

							$("#OnrollEmployee").append(row);
						});

						// var table = $(".table").DataTable({
						// 	paging: true,
						// 	ordering: true,
						// 	info: true,
						// 	searching: true,
						// });
					}
				},
			});
		});

		$("#On_Last_90").on("click", function () {
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/On_Last_Ninety",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					$("#OnrollEmployee").empty();

					var responseData = JSON.parse(response);
					var OnRoll_Employee_List = responseData.On_Last_Ninety;

					if (OnRoll_Employee_List.length === 0) {
						$("#Card_OnRoll").hide();


						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found!",
						});

					} else {
						$("#Card_OnRoll").show();

						// if ($.fn.dataTable.isDataTable(".table")) {
						// 	$(".table").DataTable().clear().destroy();
						// }

						$.each(OnRoll_Employee_List, function (index, item) {
							// Format DOJ and SixMonthsAfterDOJ as DD-MM-YYYY
							var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");
							var formattedSixMonthsAfterDOJ = new Date(
								item.SixMonthsAfterDOJ
							).toLocaleDateString("en-GB");

							var badgeColor = item.IsActive === "Yes" ? "green" : "red";
							var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

							var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.DeptName}</td>
										<td>${formattedDOJ}</td>
										<td>${item.FirstName}</td>
										<td>${item.MachineID}</td>
										<td>${item.ExperienceFormatted}</td>
										<td >${formattedSixMonthsAfterDOJ}</td>
										<td>${item.EmployeeMobile}</td>
										<td class='text-center'>${item.Months_Active}</td>


										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>
									</tr>`;

							$("#OnrollEmployee").append(row);
						});

						// var table = $(".table").DataTable({
						// 	paging: true,
						// 	ordering: true,
						// 	info: true,
						// 	searching: true,
						// });
					}
				},
			});
		});

		$("#On_Last_120").on("click", function () {
			var Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Grade_Master/On_One_Twenty",
				type: "POST",
				data: {
					Department: Department,
				},
				success: function (response) {
					$("#OnrollEmployee").empty();

					var responseData = JSON.parse(response);
					var OnRoll_Employee_List = responseData.On_One_Twenty;

					if (OnRoll_Employee_List.length === 0) {
						$("#Card_OnRoll").hide();


						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found!",
						});

					} else {
						$("#Card_OnRoll").show();

						// if ($.fn.dataTable.isDataTable(".table")) {
						// 	$(".table").DataTable().clear().destroy();
						// }

						$.each(OnRoll_Employee_List, function (index, item) {
							// Format DOJ and SixMonthsAfterDOJ as DD-MM-YYYY
							var formattedDOJ = new Date(item.DOJ).toLocaleDateString("en-GB");
							var formattedSixMonthsAfterDOJ = new Date(
								item.SixMonthsAfterDOJ
							).toLocaleDateString("en-GB");

							var badgeColor = item.IsActive === "Yes" ? "green" : "red";
							var badgeText = item.IsActive === "Yes" ? "Active" : "In Active";

							var row = `<tr>
										<td>${index + 1}</td>
										<td>${item.DeptName}</td>
										<td>${formattedDOJ}</td>
										<td>${item.FirstName}</td>
										<td>${item.MachineID}</td>
										<td>${item.ExperienceFormatted}</td>
										<td >${formattedSixMonthsAfterDOJ}</td>
										<td>${item.EmployeeMobile}</td>
										<td class='text-center'>${item.Months_Active}</td>


										<td>
											<span style="background-color: ${badgeColor}; color: white; padding: 5px 10px; border-radius: 5px;">
												${badgeText}
											</span>
										</td>
									</tr>`;

							$("#OnrollEmployee").append(row);
						});

						// var table = $(".table").DataTable({
						// 	paging: true,
						// 	ordering: true,
						// 	info: true,
						// 	searching: true,
						// });
					}
				},
			});
		});





	} else if (Employee_Attendance_Record_Page == 'Employee_Attendance_Record_Page') {


		$("#Employee_Attendance_List_Grade").hide();


		$.ajax({
			url: baseurl + "Grade_Master/Get_Sub_Department",
			tyep: "POST",
			success: function (response) {

				var Response_Data = JSON.parse(response);
				var Get_Sub_Department = Response_Data.Get_Sub_Department;

				var Sub_Department = { "": "" };

				for (var i = 0; i < Get_Sub_Department.length; i++) {
				  var DName = Get_Sub_Department[i];
				  Sub_Department[DName.DeptName] = DName.DeptName;
				}

				$("#Sub_Department").empty();

				$.each(Sub_Department, function (index, value) {
				  $("#Sub_Department").append(
					$("<option></option>").attr("value", value).text(value)
				  );
				});







			}
		})



		$("#Attendance_Sheet_Tables").on("click", function () {

			var Department = $("#Sub_Department").val();

			$.ajax({
				type: "POST",
				url: baseurl + "Grade_Master/Attendance_Sheet",
				data: {
					Department: Department,
				},
				success: function (response) {
					$("#Attendance_Sheet_Table").empty();



					var responseData = JSON.parse(response);

					if (responseData.Attendance_List.length == 0) {
						$("#Card_Traniee").hide();
						$("#Employee_Attendance_List_Grade").hide();

						swal({
							type: "error",
							title: "Error",
							text: "Employee Details Not Found.",
						  });
					} else {
						$("#Employee_Attendance_List_Grade").show();

						$("#Attendance_Employee_Grade_Section").show();

						// Data for the table
						var Attendance_List = responseData.Attendance_List;

						// Sort the list by ExistingCode
						Attendance_List.sort(function (a, b) {
							if (a.ExistingCode < b.ExistingCode) {
								return -1;
							}
							if (a.ExistingCode > b.ExistingCode) {
								return 1;
							}
							return 0;
						});

						// Function to render table rows
						function renderTable(filteredList) {
							var tableData = filteredList.map(function (item, index) {
								var badgeColor = "";

								// Determine badge color based on grade
								if (item.Grade === "A+" || item.Grade === "A") {
									badgeColor = "background-color: green; color: white;";
								} else if (item.Grade === "B+" || item.Grade === "B") {
									badgeColor = "background-color: orange; color: white;";
								} else if (item.Grade === "C") {
									badgeColor = "background-color: red; color: white;";
								} else if (item.Grade === "T") {
									badgeColor = "background-color: gray; color: white;";
								} else if (item.Grade === "N") {
									badgeColor = "background-color: white; color: black;";
								}

								return [
									index + 1,
									item.ExistingCode,
									item.FirstName,
									item.doj,
									item.WorkingMonths,
									item.Status,
									item.Average_Percentage + " %",
									`<span style="padding: 5px 10px; border-radius: 5px; ${badgeColor}">${item.Grade}</span>`,
									item.LastGrade,
									item.GradeChangeDate,
								];
							});

							// If the DataTable is not initialized yet, initialize it
							if (!dataTableInitialized) {
								$(".table").DataTable({
									paging: false,
									ordering: true,
									info: true,
									searching: true,
									data: tableData,
									columns: [
										{ title: "#" },
										{ title: "Employee Id" },
										{ title: "Employee Name" },
										{ title: "J.Date" },
										{ title: "W.Months" },
										{ title: "Status" },
										{ title: "A.Percentage" },
										{ title: "A.Grade" },
										{ title: "Last Grade" },
										{ title: "Month Grade" },
									],
								});
								dataTableInitialized = true;
							} else {
								// If DataTable is already initialized, just update the rows
								var table = $(".table").DataTable();
								table.clear().rows.add(tableData).draw();
							}
						}

						renderTable(Attendance_List);

						// Event handler for "A Grade Group"
						$("#AGradeGroup").on("click", function () {
							var filteredList = Attendance_List.filter(function (item) {
								return item.Grade === "A" || item.Grade === "A+";
							});
							renderTable(filteredList);
						});

						// Event handler for "B Grade Group"
						$("#BGradegroup").on("click", function () {
							var filteredList = Attendance_List.filter(function (item) {
								return item.Grade === "B" || item.Grade === "B+";
							});
							renderTable(filteredList);
						});

						// Event handler for "C Grade Group"
						$("#CGradegroup").on("click", function () {
							var filteredList = Attendance_List.filter(function (item) {
								return item.Grade === "C";
							});
							renderTable(filteredList);
						});

						// Event handler for "Trainee Group"
						$("#TraineeGradegroup").on("click", function () {
							var filteredList = Attendance_List.filter(function (item) {
								return item.Grade === "T";
							});
							renderTable(filteredList);
						});

						// Event handler for "New Group"
						$("#NewGradegroup").on("click", function () {
							var filteredList = Attendance_List.filter(function (item) {
								return item.Grade === "N";
							});
							renderTable(filteredList);
						});

						// Event handler for "Default" button (view all data)
						$("#Default").on("click", function () {
							// Render the full data (no filters applied)
							renderTable(Attendance_List);
						});
					}
				},
			});
		});


		$.ajax({
			type: "POST",
			url: baseurl + "Grade_Master/Chart_Fro_Attendance_Grade",
			success: function(data) {
				try {
					var responseData = JSON.parse(data);

					if (Array.isArray(responseData.Attendance_List)) {
						const Attendance_List = responseData.Attendance_List;

						const gradeCount = {
							'A+': 0,
							'A': 0,
							'B+': 0,
							'B': 0,
							'C': 0,
							'T': 0,
							'N': 0
						};

						const gradeColors = {
							'A+': 'rgba(46, 225, 46, 0.71)',
							'A': 'rgba(46, 225, 46, 0.53)',
							'B+': 'rgba(255, 230, 0, 0.89)',
							'B': 'rgba(255, 230, 0, 0.59)',
							'C': 'rgb(255, 0, 0)',
							'T': 'rgb(133, 133, 133)',
							'N': 'rgba(133, 133, 133)'
						};

						const gradeDescriptions = {
							'A+': 'A+',
							'A': 'A',
							'B+': 'B+',
							'B': 'B',
							'C': 'C',
							'T': 'T - Trainee',
							'N': 'N - New Joining'
						};

						Attendance_List.forEach(employee => {
							if (employee.Grade && gradeCount[employee.Grade] !== undefined) {
								gradeCount[employee.Grade]++;
							} else {
								console.warn(`Invalid or missing grade for employee: ${employee.FirstName}`);
							}
						});

						const dataForChart = {
							labels: ['A+', 'A', 'B+', 'B', 'C', 'T', 'N'],
							datasets: [{
								label: 'Employee Grade Distribution',
								data: [
									gradeCount['A+'],
									gradeCount['A'],
									gradeCount['B+'],
									gradeCount['B'],
									gradeCount['C'],
									gradeCount['T'],
									gradeCount['N']
								],
								backgroundColor: [
									gradeColors['A+'],
									gradeColors['A'],
									gradeColors['B+'],
									gradeColors['B'],
									gradeColors['C'],
									gradeColors['T'],
									gradeColors['N']
								],
								borderColor: [
									gradeColors['A+'],
									gradeColors['A'],
									gradeColors['B+'],
									gradeColors['B'],
									gradeColors['C'],
									gradeColors['T'],
									gradeColors['N']
								],
								borderWidth: 1
							}]
						};

						const options = {
							responsive: true,
							plugins: {
								legend: { display: false },
								tooltip: {
									callbacks: {
										label: function(context) {
											const grade = context.label;
											const count = context.parsed.y;
											return `${gradeDescriptions[grade]}: ${count}`;
										}
									}
								},
								datalabels: {
									anchor: 'end',
									align: 'end',
									color: '#000',
									font: {
										weight: 'bold',
										size: 15
									},
									formatter: function(value) {
										return value;
									}
								}
							},
							scales: {
								x: {
									ticks: {
										font: {
											weight: 'bold'  // Make x-axis labels bold
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
						const myBarChart = new Chart(ctx, {
							type: 'bar',
							data: dataForChart,
							options: options,
							plugins: [ChartDataLabels]
						});

					} else {
						console.error("Attendance_List is missing or not an array");
					}

				} catch (error) {
					console.error("Error parsing response data or creating chart:", error);
				}
			},
			error: function(xhr, status, error) {
				console.error("Error fetching data:", error);
			}
		});

		$.ajax({
			url: baseurl + "Grade_Master/Get_Employee_Count",
			type: "POST",
			success: function (response) {

				var Response_Data = JSON.parse(response);
				var Get_Employee_Count = Response_Data.Get_Employee_Count;

				var Unit_Employee_Count = Get_Employee_Count[0].Total_Active_Employee;

				$("#Active_Employee_List").val(Unit_Employee_Count);
			}
		})


		$("#Attendance_Employee_Grade_Dbtn").on("click", function () {

			var Sub_Department = $("#Sub_Department").val();

			$.ajax({
				url: baseurl + "Reports/Download_Attendance_Grade",
				type: "POST",
				data: {
					Sub_Department
				},
				success: function (response) {

					var Response_Data = JSON.parse(response);
					var Dwonload_Attendance_Grade = Response_Data.Dwonload_Attendance_Grade;

					if (Response_Data.file_url) {
						var link = document.createElement("a");
						link.href = Response_Data.file_url;
						link.download = "Employee_Attendance_Grade.xlsx";
						document.body.appendChild(link);
						link.click();
						document.body.removeChild(link);
					  } else {
						alert("Employee Details Not Found");
					  }


				}
			})








		})





	}

















});
