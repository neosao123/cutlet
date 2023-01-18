 const baseUrl = document.getElementsByTagName("meta").baseurl.content;
 function Upload() {
	var fileUpload = document.getElementById("uploadFile");
	if(fileUpload.value!=''){
        if (typeof (FileReader) != "undefined") {
			$('#modal').modal('show');
            var reader = new FileReader();
			if (reader.readAsBinaryString) {
				reader.onload = function (e) {
					ProcessExcel(e.target.result);
                };
                reader.readAsBinaryString(fileUpload.files[0]);
			} else {
				reader.onload = function (e) {
					var data = "";
					var bytes = new Uint8Array(e.target.result);
						for (var i = 0; i < bytes.byteLength; i++) {
							data += String.fromCharCode(bytes[i]);
						}
						ProcessExcel(data);
					};
					reader.readAsArrayBuffer(fileUpload.files[0]);
            }
        } else {
			toastr.error("This browser does not support HTML5.", 'Receipt Excel File', {"progressBar": true});
			$('#modal').modal('hide');
		}
	}else{
		toastr.error("Please upload Excel file.", 'Receipt Excel File', {"progressBar": true});
		$('#modal').modal('hide');
	}   
} 
function ProcessExcel(data) {
    var workbook = XLSX.read(data, {
        type: 'binary'
    });
    var firstSheet = workbook.SheetNames[0];
    var excelRows = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[firstSheet]);
    var table = document.createElement("table");
    table.border = "1";
    table.id = "tableID";
	table.setAttribute('class', "table table-responsive table-sm table-stripped");
	var row = table.insertRow(-1);
	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Sr No";
	row.appendChild(headerCell);
	
	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Item Name";
	row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Restaurant";
    row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Sale Price";
    row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Packaging Charges";
    row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Max Order Qty";
    row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Cuisine Type";
    row.appendChild(headerCell);

	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Item Image";
    row.appendChild(headerCell);

	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Item Description"
	row.appendChild(headerCell);
	
	headerCell = document.createElement("TH");
	headerCell.innerHTML = "Menu Category";
    row.appendChild(headerCell);

	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Menu Subcategory";
    row.appendChild(headerCell);

    headerCell = document.createElement("TH");
    headerCell.innerHTML = "Item Status";
    row.appendChild(headerCell);

	headerCell = document.createElement("TH");
    headerCell.innerHTML = "Approved";
    row.appendChild(headerCell);

    var hiddenHtml="";
	for (var i = 0; i < excelRows.length; i++) {
        var row = table.insertRow(-1);
		row.id = "row"+i;
		
		var cell = row.insertCell(-1);
        var srno = cell.innerHTML = (i+1); 
		
		var cell0 = row.insertCell(-1);
        var itemName = cell0.innerHTML = excelRows[i][Object.keys(excelRows[i])[0]];

        var cell = row.insertCell(-1);
        var restaurant = cell.innerHTML = excelRows[i][Object.keys(excelRows[i])[1]];

        cell1 = row.insertCell(-1);
        var salePrice = cell1.innerHTML = excelRows[i][Object.keys(excelRows[i])[2]];

        cell2 = row.insertCell(-1);
        var packagingCharges = cell2.innerHTML = excelRows[i][Object.keys(excelRows[i])[3]];
		
        cell3 = row.insertCell(-1);
        var maxOrderQty = cell3.innerHTML = excelRows[i][Object.keys(excelRows[i])[4]];

        cell4 = row.insertCell(-1);
        var cuisineType = cell4.innerHTML = excelRows[i][Object.keys(excelRows[i])[5]];

		cell5 = row.insertCell(-1);
        var itemImage = cell5.innerHTML = 'Image';

        cell6 = row.insertCell(-1);
        var itemDescription = cell6.innerHTML = excelRows[i][Object.keys(excelRows[i])[6]];

        cell7 = row.insertCell(-1);
        var menuCategory = cell7.innerHTML = excelRows[i][Object.keys(excelRows[i])[7]];                 
         
		cell8 = row.insertCell(-1);
        var menuSubcategory = cell8.innerHTML = excelRows[i][Object.keys(excelRows[i])[8]];
		
		cell9 = row.insertCell(-1);
        var itemStatus = cell9.innerHTML = excelRows[i][Object.keys(excelRows[i])[9]];
		
		cell10 = row.insertCell(-1);
		var approved = cell10.innerHTML = excelRows[i][Object.keys(excelRows[i])[10]];
		
       
	}
	var dvExcel = document.getElementById("dvExcel");    
    dvExcel.innerHTML = "";
    dvExcel.appendChild(table);
	validateExcelRows();
	var trowCount = document.getElementById('tableID').rows.length;           
	var Excelcount= excelRows.length;       
}
	$('#modalForm').on('submit', function(e){ 
		e.preventDefault();
		$('#message1').css('display','none');
		var file_data = $('#uploadFile').prop('files')[0];
		var noOfRecords = $('#tableID tr').length;
		var rowExcepts = $('#rowExcepts').val()
		var formData = new FormData($("form#upload_form")[0]);
		formData.append('tableLength',noOfRecords);
		formData.append('uploadFile', file_data);
		formData.append('rowExcepts', rowExcepts);
		$.ajax({ 
			xhr: function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(element) {
					if (element.lengthComputable) {
						var percentComplete = Math.round((element.loaded / element.total) * 100);
						$("#file-progress-bar").width(percentComplete + '%');
						$("#file-progress-bar").html(percentComplete + '%');
					}
				}, false);
				return xhr;
			},
			type:'POST',
			url: baseUrl + '/restaurantItem/uploadData',
			data: formData,
			contentType: false,
			cache: false,
			processData:false,	
			 dataType: 'json',
			beforeSend:function(){
			   $('#cutomerSubmit').attr('disabled','disabled');
				$('#cutomerSubmit').text('Saving');
				$('#process').css('display', 'block');
				$("#file-progress-bar").width('0%');
			},
			success: function(response){
				$('#process').css('display', 'none');
				if(response.status==true){  
				  $('#successMsg').html('<div class="alert alert-success text-center">'+response.text+'</div>');
				  $('#cutomerSubmit').css('display','none');
				  $('#uploadFile').val('');
				}else{
					$('#cutomerSubmit').css('display','inlline');
					$('#successMsg').html('<div class="alert alert-success text-center">'+response.text+'</div>');
					  $('#cutomerSubmit').attr('disabled',false);
					  $('#cutomerSubmit').text('Save');
				}
			}
		})
    });
	
	function validateExcelRows(){
		var convertedIntoArray = [];
	   $("table#tableID tr").each(function() {
		  var rowDataArray = [];
		  var actualData = $(this).find('td');
		  if (actualData.length > 0) {
			 actualData.each(function() {
				rowDataArray.push($(this).text());
			 });
			 convertedIntoArray.push(rowDataArray);
		  }
	   });
	   console.log(convertedIntoArray)
	   $.ajax({
            url: baseUrl + "/restaurantItem/validateExcelFile",
			async:false,
			dataType:'JSON',
            type: 'POST',
            data: {
              'convertedIntoArray': convertedIntoArray
            },
            success: function(response) {
				if(response.msg==''){
					$('#cutomerSubmit').css('display','inline');
					$('#message1').css('display','none');
					document.getElementById('validMsgs').innerHTML='';
					$('#rowExcepts').val('')
					$('#cutomerSubmit').text('Save');
				}else{
					$('#message1').css('display','block');
					$('#rowExcepts').val(response.rowArr)
					document.getElementById('validMsgs').innerHTML=response.msg;
					if(convertedIntoArray.length==JSON.parse(response.rowArr).length){
						$('#cutomerSubmit').css('display','none');
					}else{
						$('#cutomerSubmit').css('display','block');
						$('#cutomerSubmit').text('Proceed with valid rows');
					}
					$.each(JSON.parse(response.rowArr) , function(index, val) { 
					   document.getElementById('row'+val).style.backgroundColor = '#F68D0F';
					});

				}
			}
	   });
	}
 $(document).ready(function(){
	var clear_timer;
	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	$('#myModal').on('hidden', function () {
  document.location.reload();
})
 });