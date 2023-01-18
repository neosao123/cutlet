@extends('restaurant.layout.master', ['pageTitle' => 'Order Details'])
@push('styles')
	<link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/restaurant/confirmorder/index.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
     <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Order Invoice</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Order Invoice</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">  
			
            </div>
        </div>
    </div>
	
	 <div class="container-fluid col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">Order Invoice</h5>
                    </div>
                    <div class="col-sm-2">
                        <a onclick="$('#invoiceContent').printArea()" href="#" class="btn btn-outline-primary btn-sm"> Print </a>
                    </div>
                </div>
            </div>
            <div class="card-body" id="invoiceContent">
			      @if($query)
					@foreach($query as $row)
					  <table width="1000" align="center" border="0">
						<tr>
							<td td colspan="5">
								<img src="{{ asset('assets/theme/assets/images/logo.png') }}" alt="Cutlet Logo">
							</td>
							<td colspan="4">
								<div align="right"><strong>Invoice Date :&nbsp; </strong>
								{{date("d/m/Y", strtotime($row->addDate));}}</div>
							</td>
						</tr>
						<tr>
							<td colspan="9">
								<div align="center">
									<h3><strong>Order Invoice </strong></h3>
								</div>
							</td>
						</tr>
						@if($company)
							@foreach($company as $companyItem)
							<tr>
								<td colspan="9"><strong>Sold By :&nbsp; </strong>{{$companyItem->companyName}}</td>

							</tr>
							<tr style="border-bottom:1pt solid black;">
								<td colspan="7"><strong>Address : </strong>{{ $companyItem->shippingAddress}}, {{$companyItem->shippingPlace}} ,{{ $companyItem->shippingTaluka }} , {{$companyItem->shippingDistrict}} , {{$companyItem->shippingState }} ,{{ $companyItem->shippingPinCode}} </td>
								<td colspan="2">
									<div align="right"><strong>GST Number : </strong> {{$companyItem->gstNo}}</div>
								</td>
							</tr>
							@endforeach
						@endif
						<tr>
							<td colspan="3"><div align="center"><strong>Order Code</strong></div></td>
							<td colspan="3"><div align="center"><strong>Coupon Code</strong></div></td>
							<td colspan="4"><div align="center"><strong>Shipping Address </strong></div></td>
						</tr>
						<tr  style="border-bottom:1pt solid black;">
							<td colspan="3"><div align="center">{{ $row->code}}</div></td>
							<td colspan="3"><div align="center">{{ $row->couponCode}}</div></td>
							<td colspan="4"><div align="center">{{ $row->Clientname}},{{ $row->address}} <br/>{{ $row->mobile}}</div></td>
							
						</tr>
						<tr>
                            <td class="border" colspan="2"><strong>
                                    <div align="center">Sr.No </div>
                                </strong></td>
                            <td class="border" colspan="2"><strong>
                                    <div align="center">Product Name </div>
                                </strong></td>
                            <td class="border" colspan="2"><strong>
                                    <div align="center">Sale Price </div>
                                </strong></td>
                            <td class="border" colspan="2"><strong>
                                    <div align="center">Quantity</div>
                                </strong></td>
                            
                            <td class="border" colspan="2"><strong>
                                    <div align="center">Sub Total</div>
                                </strong></td>
                         </tr>
						 {!! $lineData !!}
						 <tr style="border-bottom:1pt solid black;">
                            <td colspan="9">&nbsp;</td>
                         </tr>
                         <tr>
                            <td colspan="8">
                                <div align="right"><strong>Item Total</strong></div>
                            </td>
                            <td>
                                <div align="center"><label id="itemTotal">{{ number_format($row->subTotal, 2, '.', '') }}</label></div>
                            </td>
                          </tr>
						  <tr style="border-bottom:1pt solid black;">
                            <td colspan="9">&nbsp;</td>
                          </tr>
						  <tr>
							<td colspan="8"><div align="right"><strong>Item Total:  </strong></div></td>
							<td colspan="2"><div align="center"><label id="totalPrice">{{ number_format($row->subTotal, 2, '.', '') }}</label></div></td>
						  </tr>
						   <tr>
							<td colspan="8"><div align="right"><strong>Discount (-): </strong></div></td>
							<td colspan="2"><div align="center"><label  id="discount">{{ number_format($row->discount, 2, '.', '') }}</label></div></td>
						  </tr>
	
						  <tr>
							<td colspan="8"><div align="right"><strong>Packaging Charges (+): </strong></div></td>
							<td colspan="2"><div align="center"><label  id="discount"><?= number_format($row->totalPackgingCharges, 2, '.', '') ?></label></div></td>
						  </tr> 
						  <tr>
							<td colspan="8"><div align="right"><strong>Shipping Charges </strong></div></td>
							<td colspan="2"><div align="center"><label  id="shipping"><?= number_format($row->shippingCharges, 2, '.', '') ?></label></div></td>
						  </tr> 

						   <tr style="border-bottom:1pt solid black;">
							<td colspan="8"><div align="right"><strong>Grand Total: </strong></div></td>
											  
							<td colspan="2"><div align="center" ><label  id="payable"><?= number_format($row->grandTotal, 2, '.', '') ?></label></div></td>
						  </tr>
						  <tr style="border-bottom:1pt solid black;">
							<td colspan="10"><div align="left" ><strong>Amount In Rupees:- </strong><label  id="inwords"  align="center"></label></div></td>
						  </tr>
						  <tr>
							<td colspan="7"><div align="left"> *This is a computer generated invoice.<br />
							</div></td>
							<td colspan="3"><div align="center">CompanyName</div></td>
						  </tr>
						  <tr>
							<td colspan="10">&nbsp;</td>
						  </tr>
						  <tr>
							<td colspan="7">&nbsp;</td>
							<td colspan="3"><div align="center">(Authorised Signatory)</div></td>
						  </tr>
					 </table>
				  @endforeach 
				@endif
            </div>
	
        </div>
    </div>
@endsection
@push('scripts')
	<script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/init_site/restaurant/confirmorder/view.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/dist/js/jquery.Printarea.js') }}"></script>

@endpush 