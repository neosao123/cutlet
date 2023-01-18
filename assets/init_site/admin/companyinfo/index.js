const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$('document').ready(function() {
		var isBillingAddressSame= $('#isBillingAddressSame').val();
		if(isBillingAddressSame=='1'){
			  $('#billingAddress').attr('readonly', 'readonly');
			  $('#billingPinCode').attr('readonly', 'readonly');
			  $('#billingPlace').attr('readonly', 'readonly');
			  $('#billingTaluka').attr('readonly', 'readonly');
			  $('#billingDistrict').attr('readonly', 'readonly');
			  $('#billingState').attr('readonly', 'readonly');
			  $('#billingCountry').attr('readonly', 'readonly');
		 }
	$('#isBillingAddressSame').change(function(){
        if($(this).prop('checked')){
			$('#billingAddress').attr('readonly', 'readonly');
			// $('#permanentLandmark').attr('readonly', 'readonly');
			$('#billingPinCode').attr('readonly', 'readonly');
			$('#billingPlace').attr('readonly', 'readonly');
			$('#billingTaluka').attr('readonly', 'readonly');
			$('#billingDistrict').attr('readonly', 'readonly');
			$('#billingState').attr('readonly', 'readonly');
			$('#billingCountry').attr('readonly', 'readonly');
			var shippingAddress = $('#shippingAddress').val();
		    var shippingPinCode = $('#shippingPinCode').val();
			var shippingPlace = $('#shippingPlace').val();
            var shippingTaluka = $('#shippingTaluka').val();
            var shippingDistrict = $('#shippingDistrict').val();
            var shippingState = $('#shippingState').val();
            var shippingCountry = $('#shippingCountry').val();

            $('#billingAddress').val(shippingAddress);
            $('#billingPinCode').val(shippingPinCode);
            $('#billingPlace').val(shippingPlace);
            $('#billingTaluka').val(shippingTaluka);
            $('#billingDistrict').val(shippingDistrict);
            $('#billingState').val(shippingState);
            $('#billingCountry').val(shippingCountry);
        }else{
             $('#billingAddress').removeAttr('readonly', 'readonly');
            // $('#permanentLandmark').removeAttr('readonly', 'readonly');
             $('#billingPinCode').removeAttr('readonly', 'readonly');
             $('#billingPlace').removeAttr('readonly', 'readonly');
             $('#billingTaluka').removeAttr('readonly', 'readonly');
             $('#billingDistrict').removeAttr('readonly', 'readonly');
             $('#billingState').removeAttr('readonly', 'readonly');
             $('#billingCountry').removeAttr('readonly', 'readonly');
        }
             });
	});