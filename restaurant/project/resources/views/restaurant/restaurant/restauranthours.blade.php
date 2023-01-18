@extends('restaurant.layout.master', ['pageTitle' => 'Restaurant Hours'])
@push('styles')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/restaurant/restaurant/index.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
@php
        $code = Auth::guard('restaurant')->user()->code;
@endphp
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Restaurant Hours</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Restaurant Hours</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">  
			
            </div>
        </div>
    </div>
	
	 <div class="container-fluid col-md-9">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">Restaurant Hours</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('dashboard') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form>
				    <div class="card">
						<div class="card-body">
							<h4 class="card-title">Restaurant Details</h4>
							<hr />
							<input type="hidden" id="restCode" name="restaurantCode" value="{{ $restaurant->code}}" class="form-control" readonly>
							<div class="form-row">
								<div class="col-md-8 mb-3">
									<label for="restaurantName">Restaurant Name : </label>
									<input type="text" id="restaurantName" readonly name="restaurantName" value="{{ $restaurant->entityName }}" class="form-control" readonly required>
								</div>
								<div class="col-md-4 mb-3">
									<label for="restaurantContact">Owner Contact : </label>
									<input type="text" id="restaurantContact" readonly name="restaurantContact" value="{{ $restaurant->ownerContact }}" class="form-control" readonly>
								</div>
								<div class="col-md-12 mb-3">
									<label for="address">Address : </label>
									<input type="text" id="address" name="address" readonly value="{{ $restaurant->address }}" class="form-control" readonly>
								</div>
							</div>
						   </div>
					   </div> 
                       <div class="card">
						   <div class="card-body">
							<h4 class="card-title">Hours Entry</h4>
							  <hr />
							 <div id="accordion2" class="accordion" role="tablist" aria-multiselectable="true">
							  <div class="card">
								<div class="card-header" role="tab" id="day_monday">
									<h5 class="mb-0">
										<a data-toggle="collapse" data-parent="#monday" href="#monday" aria-expanded="true" aria-controls="collapseOne">
											Monday
										</a>
									</h5>
								</div>
								<div id="monday" class="collapse show" role="tabpanel" aria-labelledby="day_monday">
									<div class="card-body">
										<div id="monday_fields" class="m-b-20">
										  @php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'monday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{$itemHours->code}}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{$dayRoom}}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{$dayRoom}}" name="day[]" placeholder="Day" value="{{$day}}">
																		<input type="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{$day}}" data-linecode="{{$itemHours->code}}" data-seq="{{$dayRoom}}" id="fromTime{{$dayRoom}}" name="fromTime[]" placeholder="From Time" value="{{$fromTime}}" data-previous="{{$fromTime}}">
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{$dayRoom}}">To Time</label>
																		<input type="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{$day}}" data-linecode="{{$itemHours->code}}" data-seq="{{$dayRoom}}" id="toTime{{$dayRoom}}" name="toTime[]" placeholder="To Time" value="{{$toTime}}" data-previous="{{$toTime}}"> 
																	</div>
																</div>
																<div class="col-sm-2"> 
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{$i}}','{{$day}}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="dayMonday" name="day[]" placeholder="Day" value="monday">
													<input type="text" readonly class="form-control pickatime" id="fromTimemonday" data-weekday="monday" name="fromTime[]" placeholder="From Time"> 
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input type="text" readonly class="form-control pickatime" id="toTimemonday" data-weekday="monday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('monday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								
							  </div>
							  	<div class="card">
								<div class="card-header" role="tab" id="day_tuesday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#tuesday" href="#tuesday" aria-expanded="false" aria-controls="collapseTwo">
											Tuesday
										</a>
									</h5>
								</div>
								<div id="tuesday" class="collapse show" role="tabpanel" aria-labelledby="day_tuesday">
									<div class="card-body">
										<div id="tuesday_fields" class="m-b-20">
											  @php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'tuesday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{$itemHours->code}}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{$fromTime}}" data-previous='{{$fromTime}}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{$toTime}}" data-previous='{{$toTime}}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{ $itemHours->code }}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="dayTuesdDay" name="day[]" placeholder="Day" value="tuesday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimetuesday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimetuesday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('tuesday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-header" role="tab" id="day_wednesday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#wednesday" href="#wednesday" aria-expanded="false" aria-controls="collapseThree">
											Wednesday
										</a>
									</h5>
								</div>
								<div id="wednesday" class="collapse show" role="tabpanel" aria-labelledby="headingThree">
									<div class="card-body">
										<div id="wednesday_fields" class="m-b-20">
											@php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'wednesday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{$itemHours->code}}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day}}" data-linecode="{{$itemHours->code}}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{$fromTime}}" data-previous='{{$fromTime}}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{$itemHours->code}}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{ $toTime}}" data-previous='{{ $toTime}}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="dayWednesday" name="day[]" placeholder="Day" value="wednesday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimewednesday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimewednesday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('wednesday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-header" role="tab" id="day_thursday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#thursday" href="#thursday" aria-expanded="false" aria-controls="collapseThree">
											Thursday
										</a>
									</h5>
								</div>
								<div id="thursday" class="collapse show" role="tabpanel" aria-labelledby="headingThree">
									<div class="card-body">
										<div id="thursday_fields" class="m-b-20">
											@php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'thursday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{ $itemHours->code }}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{ $fromTime }}" data-previous='{{ $fromTime }}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{ $toTime }}" data-previous='{{ $toTime }}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="dayThursday" name="day[]" placeholder="Day" value="thursday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimethursday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimethursday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('thursday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-header" role="tab" id="day_friday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#friday" href="#friday" aria-expanded="false" aria-controls="collapseThree">
											Friday
										</a>
									</h5>
								</div>
								<div id="friday" class="collapse show" role="tabpanel" aria-labelledby="headingThree">
									<div class="card-body">
										<div id="friday_fields" class="m-b-20">
										@php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'friday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{ $itemHours->code }}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{ $fromTime }}" data-previous='{{ $fromTime }}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{ $toTime }}" data-previous='{{ $toTime }}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php 
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="dayFriday" name="day[]" placeholder="Day" value="friday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimefriday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimefriday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('friday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-header" role="tab" id="day_saturday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#saturday" href="#saturday" aria-expanded="false" aria-controls="collapseThree">
											Saturday
										</a>
									</h5>
								</div>
								<div id="saturday" class="collapse show" role="tabpanel" aria-labelledby="day_saturday">
									<div class="card-body">
										<div id="saturday_fields" class="m-b-20">
											@php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'saturday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{ $itemHours->code }}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{ $fromTime }}" data-previous='{{ $fromTime }}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{ $toTime }}" data-previous='{{ $toTime }}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php 
													}
												}
											}
											@endphp
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="daySaturday" name="day[]" placeholder="Day" value="saturday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimesaturday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimesaturday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('saturday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-header" role="tab" id="day_sunday">
									<h5 class="mb-0">
										<a class="collapsed" data-toggle="collapse" data-parent="#sunday" href="#sunday" aria-expanded="false" aria-controls="collapseThree">
											Sunday
										</a>
									</h5>
								</div>
								<div id="sunday" class="collapse show" role="tabpanel" aria-labelledby="day_sunday">
									<div class="card-body">
										<div id="sunday_fields" class="m-b-20">
											@php
											if($restauranthours){
												foreach ($restauranthours as $itemHours) {
													$i=1;
													$fromTime ='';
													$toTime='';
													$day = $itemHours->weekDay;
													if ($day == 'sunday') {
														$i++;
														$dayRoom = $day . $i;
														if(!empty($itemHours->fromTime)){
															$fromTime=date('h:i A',strtotime($itemHours->fromTime));
														}
														if(!empty($itemHours->toTime)){
															$toTime=date('h:i A',strtotime($itemHours->toTime));
														}
										         @endphp
														<div class="form-group removeclass{{ $itemHours->code }}">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="fromTime{{ $dayRoom }}">From Time</label>
																		<input type="hidden" readonly class="form-control" id="day{{ $dayRoom }}" name="day[]" placeholder="Day" value="{{ $day }}">
																		<input ttype="text" readonly class="form-control pickatime fTime{{$itemHours->code}}" data-when="from" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="fromTime{{ $dayRoom }}" name="fromTime[]" placeholder="From Time" value="{{ $fromTime }}" data-previous='{{ $fromTime }}'>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label for="toTime{{ $dayRoom }}">To Time</label>
																		<input ttype="text" readonly class="form-control pickatime tTime{{$itemHours->code}}" data-when="to" data-weekday="{{ $day }}" data-linecode="{{ $itemHours->code }}" id="toTime{{ $dayRoom }}" name="toTime[]" placeholder="To Time" value="{{ $toTime }}" data-previous='{{ $toTime }}'>
																	</div>
																</div>
																<div class="col-sm-2">
																	<div class="form-group mt-4">
																		<button class="btn btn-danger" onclick="remove_hours_line('{{ $i }}','{{ $day }}','delete','{{$itemHours->code}}');" type="button"><i class="fa fa-trash"></i></button>
																	</div>
																</div>
															</div>
														</div>
											@php 
													}
												}
											}
											@endphp 
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="hidden" readonly class="form-control" id="daySunday" name="day[]" placeholder="Day" value="sunday">
													<input ttype="text" readonly class="form-control pickatime" id="fromTimesunday" name="fromTime[]" placeholder="From Time">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<input ttype="text" readonly class="form-control pickatime" id="toTimesunday" name="toTime[]" placeholder="To Time">
												</div>
											</div>
											<div class="col-sm-2">
												<div class="form-group">
													<button class="btn btn-success" type="button" onclick="day_fields('sunday');"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
                           </div>
                        </div>						   					
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
   <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/restaurant/restaurant/hours.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
 
@endpush
