$('.pickatime').timepicker({
		timeFormat: 'h:mm p',
		interval: 30, 
		dynamic: true,
		dropdown: true,
		scrollbar: true,
		change: function() {
			var lineCode = $(this).data('linecode');
			var id = $(this).attr("id");
			var restCode = $('#restCode').val();
			var weekDay = $(this).data("weekday");
			//var when = $(this).data('when');
			//var previous = $(this).data('previous');
			var time = $(this).val().trim();
			var seq=$(this).data("seq");
			var fromTime = $(".fTime" + lineCode).val().trim();
		    var toTime = $(".tTime" + lineCode).val().trim(); 
			var updateto =""; 
			/*if(when=='from'){
				
				updateto = 'from'; 
				var fromtime=$(this).val().trim();
				var totime=$("#toTime"+weekDay).val().trim();
				if(totime < fromtime && totime!='' && fromtime!=''){
					toastr.error('from time must be less than to time', "Serving Hours", {
							"progressBar": true
						});
						$(this).val(previous)
						return false
				}
			} else {

				updateto = 'to';
				var totime=$(this).val().trim();
				var fromtime=$("#fromTime"+weekDay).val().trim();
				if(totime < fromtime && totime!='' && fromtime!=''){
					toastr.error('to time must be greater than from time', "Serving Hours", {
							"progressBar": true
						});
						$(this).val(previous)
						return false
				}
			} */
			if(lineCode!='' && lineCode!=undefined){
			$.ajax({
				type: "get",
				url: baseUrl+"/updateRestaurantHour",
				data: {'lineCode':lineCode,'time':time,'updateTo':updateto,'restCode':restCode,'weekDay':weekDay},
				success: function (response) {
					
					var result = JSON.parse(response);
					if(result.status){
						toastr.success(result.message,"Serving Hours",{"progressBar":true});
					}else{
						toastr.error(result.message, "Serving Hours", {
							"progressBar": true
						});
						//$('#'+id).val(previous)
					}
				}
			});
			}
		}
	});
 var room = "<?php echo $i;>";
 room = Number(room); 	
 function day_fields(day) {
		
		var fromTime = $("#fromTime" + day).val().trim();
		var toTime = $("#toTime" + day).val().trim();
		var restCode = $("#restCode").val().trim();
		if (fromTime != "" && toTime != "") {
			$.ajax({
				type: "get",
				url: baseUrl + "/saveHours",
				data: {
					"weekDay": day,
					'fromTime': fromTime,
					'toTime': toTime,
					'restCode': restCode
				},
				success: function(response) {
					
					var result = JSON.parse(response);
					if (result.status) {
						var hourlineCode = result.lineCode;
						room++;
						var objTo = document.getElementById(day + '_fields')
						var divtest = document.createElement("div");
						divtest.setAttribute("class", "form-group "); 
						divtest.setAttribute("class","removeclass"+hourlineCode);
						var rdiv = 'removeclass' + room;
						divtest.innerHTML = `
								<div class="row"> 
										<div class="col-sm-4">
												<div class="form-group">
													<label for="fromTime${day+room}">From Time</label>
													<input type="hidden" readonly class="form-control" id="day${day+room}" name="day[]" placeholder="Day" value="${day}">
													<input type="text" readonly class="form-control pickatime" data-seq="${day+room}" data-when="from" data-linecode="${hourlineCode}" id="fromTime${day+room}" name="fromTime[]" placeholder="From Time" value="${fromTime}" data-previous="${fromTime}">
												</div>
										</div>
										<div class="col-sm-4">
												<div class="form-group">
													<label for="toTime${day+room}">To Time</label>
													<input ttype="text" readonly class="form-control pickatime" data-seq="${day+room}" data-when="to" data-linecode="${hourlineCode}" id="toTime${day+room}" name="toTime[]" placeholder="To Time" value="${toTime}" data-previous="${toTime}">
												</div>
										</div>
										<div class="col-sm-2">
												<div class="form-group mt-4">
														<button class="btn btn-danger" type="button" onclick="remove_hours_line('${room}','${day}','delete','${hourlineCode}');"> <i class="fa fa-trash"></i></button>
												</div>
										</div>
								</div>
							`;
						objTo.appendChild(divtest);
						$("#fromTime" + day).val("");
						$("#toTime" + day).val("");
						toastr.success("Record Added Successfully", "Serving Hours", {
							"progressBar": true
						});
						$('.pickatime').timepicker({
							timeFormat: 'h:mm p',
							interval: 30, 
							dynamic: true,
							dropdown: true,
							scrollbar: true,
							change: function() {
								var lineCode = $(this).data('linecode');
								var when = $(this).data('when');
								var weekDay = $(this).data('when');
								var time = $(this).val().trim();
								var updateto ="";
								var seq=$(this).data("seq");
								var previous = $(this).data('previous');
								if(when=='from'){
									updateto = 'from'; 
									var fromtime=$(this).val().trim();
									var totime=$("#toTime"+seq).val().trim();
									if(totime < fromtime && fromtime!='' && totime!=''){
										toastr.error('from time must be less than to time', "Serving Hours", {
												"progressBar": true
											});
											$(this).val(previous)
											return false
									}
								} else {
									updateto = 'to';
									var totime=$(this).val().trim();
									var fromtime=$("#fromTime"+seq).val().trim();
									if(totime < fromtime  && fromtime!='' && totime!=''){
										toastr.error('to time must be greater than from time', "Serving Hours", {
												"progressBar": true
											});
											$(this).val(previous)
											return false
									}
								} 
								$.ajax({
									type: "get",
									url: baseUrl + "/updateRestaurantHour",
									data: {'lineCode':lineCode,'time':time,'updateTo':updateto},
									success: function (response) {
										var result = JSON.parse(response);
										if(result.status){
											toastr.success(result.message,"Serving Hours",{"progressBar":true});
										}
									}
								});
							}
						});
					} else {
						$("#fromTime" + day).val("");
						$("#toTime" + day).val("");
						toastr.error(result.message, "Serving Hours", {
							"progressBar": true
						});
						return false;
					}
				}
			}); 
		} else {
			$("#fromTime" + day).val("");
			$("#toTime" + day).val("");
			toastr.error("Please enter From-Time and To-Time", "Serving Hours", {
				"progressBar": true
			});
			false;
		}
	}

	function remove_hours_line(rid, day, flag, code) { 
	//	debugger;
		if (flag == 'add') {
			$('.removeclass' + day + '_' + rid).remove();
		} else {
			$.ajax({
				type: "get",
				url: baseUrl + "/deleteHourLine",
				data: {
					"lineCode": code
				},
				success: function(response) {
					//debugger;
					var result = JSON.parse(response);
					if (result.status) {
						$('.removeclass' +code).remove();
						toastr.success("Record Deleted Successfully", "Serving Hours", {
							"progressBar": true
						});
						return false;
					} else {
						toastr.error("Failed to delete the record! Please try later...", "Serving Hours", {
							"progressBar": true
						});
						return false;
					}
				}
			});
		}
	}
  