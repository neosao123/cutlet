@extends('admin.layout.master', ['pageTitle'=>"Real time chat"])
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Real time chat</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Real time chat</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-10">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">Chat</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
		<div class="row">
            <input type="hidden" id="port" name="port" value="<?= $port ?>">
			<input type="hidden" id="deliveryBoyCode" name="deliveryBoyCode" value="<?= $deliveryBoyCode ?>">
			<input type="hidden" id="orderCode" name="orderCode" value="<?= $orderCode ?>">
        </div>
		<div class="row">
			<div class="col-lg-12">
				@if($type==2)
					<div class="row">
						<div class="col-md-3">
							<input type="text" placeholder="Latitude" class="form-control" id="latitude" name="latitude" />
						</div>
						<div class="col-md-3">
							<input type="text" placeholder="Longitude" class="form-control" id="longitude" name="longitude" />
						</div>
						<div class="col-md-3">
							<input type="button" class="btn btn-primary" id="send" name="send" value="Send"/>
						</div>
					</div>
				@else
				<div id="messages">
					<div class="table-responsive" >
						<table id="datatable_delBoy" class="table table-bordered table-stripped" width="100%">
							<thead>
								<tr>
								    <th>Date</th>
                                   <th>Latitude</th>									 
                                   <th>Longitude</th>									 
								</tr>
							</thead>
							<tbody id="message-tbody">
								@if($trackingDetails)
									@foreach($trackingDetails as $tr)
                                     <tr>
                                        <td>{{ date('d/m/Y h:i A',strtotime($tr['addDate'])) }}</td>
										<td>{{ $tr['latitude'] }}</td>
										<td>{{ $tr['longitude'] }}</td>
                                     </tr>
                                    @endforeach 
								@endif
                            </tbody>
						</table>
					</div>
				</div>
				@endif
			</div>
		</div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('server/node_modules/socket.io/client-dist/socket.io.js') }}"></script>
    <script>
     const baseUrl = document.getElementsByTagName("meta").baseurl.content;
	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	var trackingPort = $('#port').val();
	$(document).ready(function(){
		$(document).on("click","#send",function() {
			$.ajax({
				type: "POST",
				url: baseUrl + "/chat/sendMessage",
				data: {
                    'latitude':$("#latitude").val(),
					'longitude' : $("#longitude").val(),
					'deliveryBoyCode' : $("#deliveryBoyCode").val(),
					'orderCode' : $("#orderCode").val(),
				},
				dataType: "json",
				cache : false,
				success: function(data){
					debugger;
					if(data.success ==true){
						var socket = io.connect('https://myvegiz.com:'+trackingPort);
						socket.emit('new_message', {
							latitude: data.latitude,
							longitude: data.longitude,
							addDate: data.addDate
						});
					}
				} 
			});
		});
	});
    var socket = io.connect('https://myvegiz.com:'+trackingPort);
   socket.on( 'new_message', function( data ) {
	   console.log(data);
	   $('#latitude').val('');
	   $('#longitude').val('');
		$("#message-tbody").prepend('<tr><td>'+data.addDate+'</td><td>'+data.latitude+'</td><td>'+data.longitude+'</td></tr>');
	});
   
</script>
@endpush
  
