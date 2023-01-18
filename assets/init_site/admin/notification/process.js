const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function(){
	var userCount = $("#userCount").val();
    var title = $("#title").val();
    var message = $("#message").val();
    var image = $("#image").val();
    var product_id = $("#product_id").val();
    var type = $("#type").val();
    var cityCodes = $("#cityCodes").val();
    var clientCode = $("#clientCode").val();
  
    $('#process').css('display', 'block')
	var firebaseIds = $("input[name='firebaseIds[]']")
		.map(function() {
			return $(this).val();
		}).get();
	var size = 10;
	var arrayOfArrays = [];
	for (var i = 0; i < firebaseIds.length; i += size) {
		arrayOfArrays.push(firebaseIds.slice(i, i + size));
	}
	var loopTotalCount = arrayOfArrays.length;
	for (var i = 0; i < arrayOfArrays.length; i++) {
		sendData(arrayOfArrays[i]);
		var percentage = 0;
		percentage = (((i + 1) / loopTotalCount) * 100);
		progress_bar_process(percentage);
		if (percentage == 100) {
			setTimeout(function() {
				progress_bar_process(101);
			}, 3000);
		}
	}
	function sendData(firebaseIdsArray) {
		debugger;
        $.ajax({
            url: baseUrl + "notification/sendCommonNotification",
            method: "POST",
            data: {firebaseIdsArray,title,message,image,product_id,type,cityCodes,clientCode},
            datatype: "text",
			success: function(data) {
				debugger
                  
            }
		});
    }

    function progress_bar_process(percentage) {
        $('.progress-bar').css('width', percentage + '%');
        if (percentage > 100) {
            $('#process').css('display', 'none');
            $('.progress-bar').css('width', '0%');
            $('#success_message').html("<div class='alert alert-success'>All Notifications Sent Successfully</div>");
            setTimeout(function() {
                location.href=baseUrl+'/notification/create';
            }, 1000);
        }
	}
});