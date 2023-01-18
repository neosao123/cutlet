const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
	$("#btnSearch").on("click", function (e) {
        var restaurantCode = $("#restaurantCode").val();
		var offerType = $("#offerType").val();
		var couponCode = $("#couponCode").val();
        getDataTable(restaurantCode, offerType, couponCode);
    });

    $("#btnClear").click(function () {
		$("#restaurantCode").val('').trigger('change');
		$("#offerType").val('').trigger('change');
		$("#couponCode").val('').trigger('change');
        getDataTable('','','');
    });
    getDataTable('','','');
    function getDataTable(p_restCode,p_offerType,p_couponCode) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-offer")) {
            $("#dataTable-offer").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-offer").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getCouponList",
                type: "GET",
				data:{
					'restCode':p_restCode,
					'offerType':p_offerType,
					'couponCode':p_couponCode,
				},
                complete: function (response) {
                   // operations();
                },
            },
        });
    }
       
});

$( document ).ready(function() {
	$('input[type=number]').on('mousewheel',function(e){ $(this).blur(); });
		$('input[type=number]').on('keydown',function(e) {
		var key = e.charCode || e.keyCode;
		if(key == 38 || key == 40 ) {
			e.preventDefault();
		} else {
			return;
		}
	});
	var typeget = $('#offerType').val().trim();
	if (typeget == "flat") {
        $('#discountDiv').addClass('d-none');
        $("#discount").prop('required', false);
        $('#capDiv').addClass('d-none');
        $("#capLimit").prop('required', false);
        $('#flatAmountDiv').removeClass('d-none');
        $("#flatAmount").prop('required', true);
    } else if (typeget == "cap") {
        $('#discountDiv').removeClass('d-none');
        $("#discount").prop('required', true);
        $('#capDiv').removeClass('d-none');
        $("#capLimit").prop('required', true);
        $('#flatAmountDiv').addClass('d-none');
		$("#flatAmount").prop('required', false);
	} else {
		$('#discountDiv').addClass('d-none');
		$("#discount").prop('required', false);
		$('#capDiv').addClass('d-none');
		$("#capLimit").prop('required', false);
		$('#flatAmountDiv').addClass('d-none');
		$("#flatAmount").prop('required', false);
	}
	
	$("#termsAndConditions").summernote({
		placeholder: 'Terms and conditions....',
		height: 200
	});
	$('.btn-reset').click(function(){
		$('#termsAndConditions').summernote('reset');
	});
	var date = new Date();
	var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());
	$('#startDate').datepicker({
		dateFormat: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "top",
		startDate: today,
	});
	$('#endDate').datepicker({
		dateFormat: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "top",
		startDate: today,
	});	
	
	$("body").on("change","#offerType",function(e){
		var typeValue = $(this).val().trim();
		if (typeValue == "flat") {
            $('#discountDiv').addClass('d-none');
            $("#discount").prop('required', false);
            $('#capDiv').addClass('d-none');
            $("#capLimit").prop('required', false);
            $('#flatAmountDiv').removeClass('d-none');
            $("#flatAmount").prop('required', true);
        } else if (typeValue == "cap") {
            $('#discountDiv').removeClass('d-none');
            $("#discount").prop('required', true);
            $('#capDiv').removeClass('d-none');
            $("#capLimit").prop('required', true);
            $('#flatAmountDiv').addClass('d-none');
            $("#flatAmount").prop('required', false);
        } else {
            $('#discountDiv').addClass('d-none');
            $("#discount").prop('required', false);
            $('#capDiv').addClass('d-none');
            $("#capLimit").prop('required', false);
            $('#flatAmountDiv').addClass('d-none');
            $("#flatAmount").prop('required', false);
        }
	});
	setApplyOn();
	$("body").on("change","#applyOn",function(e){
		setApplyOn();
	});
	$("body").on("change","#endDate",function(e){
		var endDate = $(this).val();
		var startDate =$('#startDate').val();
		//check the strings
		if(startDate  > endDate){
			$('#dateError').text('The End Date should be greater than the Start date.');
			 $('#couponAdd').prop('disabled', true);
		}else{
			$('#dateError').text('');
			$('#couponAdd').prop('disabled', false);
		}
	});
});
	
function setApplyOn(){
	var applyOn = $('#applyOn').val().trim();
	if (applyOn == "item") {
		 $('#itemDiv').removeClass('d-none');
		 $("#offerItems").prop('required', true);
	}else{
		$('#itemDiv').addClass('d-none');
		$("#offerItems").prop('required', false);
	}
}
function validateFlatAmount(){
	var minimumAmount = $('#minimumAmount').val();
	var offerType = $('#offerType').val();
	var flatAmount = $('#flatAmount').val();
	if(offerType=='flat'){
		if(minimumAmount!='' && flatAmount!=''){
			if(Number(flatAmount)>Number(minimumAmount)){
				toastr.error("Flat amount should be less than or equal to minimum amount", 'Vendor Offer', {"progressBar": true});
				$('#flatAmount').val('');
				$('#flatAmount').focus();
				return true;
			}
		}
	}
	
}
