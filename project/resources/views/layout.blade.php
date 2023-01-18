<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>CUTLETT</title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- googlefont -->
<!-- favicon -->
 <link rel="shortcut icon" href="{{asset('assets/theme/assets/images/logo.png')}}" type="image/x-icon">
<!-- fontawesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- fontawesome -->
<!-- css -->
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/inner.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/animations.css') }}" rel="stylesheet">
<link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet">
<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
<style>
.zoom {
  transition: transform .2s; /* Animation */
  margin: 0 auto;
}

.zoom:hover {
  transform: scale(1.1); /* (150% zoom - Note: if the zoom is too large, it will go outside of the viewport) */
}
</style>
<!-- bxslider -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
<!-- bxslider -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- css  -->
</head>
<body>
<!-- top -->
<div id="top"></div> 
<!-- top -->
<!-- Preloader -->
<div id="preloader">
  <div id="status">&nbsp;</div>
</div>
<header>
	<div class="new-navbar fixed-top">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 ">
				<nav class="navbar navbar-expand-lg navbar-light">
				  <a href="#"><img src="{{ asset('assets/theme/assets/images/logo.png') }}" alt="logo"></a>
				  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				    <span class="navbar-toggler-icon"></span>
				  </button>
			  <div class="collapse navbar-collapse" id="navbarSupportedContent">
			    <ul class="navbar-nav ml-auto">
		      <li><a href="{{url('/')}}">Home</a></li>
		      <li><a href="{{url('/')}}#about">About Us</a></li>
		      <li><a href="{{url('/')}}#categories">Categories</a></li>
		      <li><a href="{{url('/')}}#features">Features</a></li>
		      <li><a href="{{url('/')}}#screenshot">Screenshot</a></li>
		      <li><a href="{{url('/')}}#contact">Contact Us</a></li> 
			    </ul>				   
			  </div>
				</nav>
			</div>
		</div>
	</div>
</div> <!-- new-navbar-->
</header>

@yield('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script type="text/javascript">
$(window).load(function() {
    $(".loader").fadeOut("slow");
});
</script>


<div class="copy-right mt-5">
  <div class="container">
    <div class="row">   
       <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
		  <div class="footer-social-link">      
			<a href="#" target="_blank"><i class="fa fa-facebook-official" aria-hidden="true"></i></a>
			<a href="#" target="_blank"><i class="fa fa-twitter-square" aria-hidden="true"></i></a>
		  </div>
	  </div>	
      <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
        <div class="copy-l">
          <p>©2021 <span  style=" color: #ec3f17; font-weight: 600;">CUTLETT</span> | Proudly Powered by : <a href="https://neosao.com/" target="_blank">Neosao Services Pvt. Ltd.</a></p>
        </div>
		
      </div> 
	  
	  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12"> 
	  <div class="copy-l">
	  <p><a href="{{ url('privacy') }}">Privacy Policy</a> <span style="color:#797979; font-weight:200px;"> | </span> <a href="{{ url('terms') }}">Terms & Conditions</a></p>
	  </div>
	  </div>  
    </div>  
    </div>  
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- loader script -->

<!-- loader script -->

<script>
	$(window).on('load', function() { // makes sure the whole site is loaded 
  $('#status').fadeOut(); // will first fade out the loading animation 
  $('#preloader').delay(250).fadeOut('slow'); // will fade out the white DIV that covers the website. 
  $('body').delay(250).css({'overflow':'visible'});
})
</script>
<!-- loader script -->
<!-- top btn -->
<button onclick="topFunction()" id="back-to-top-btn" title="Go to top"><img src="{{ asset('assets/images/top_button.png') }}"></button>

<!-- top btn -->
<script src="{{ asset('assets/js/popper.min.js')}}" type="text/javascript"></script>
<script src="{{ asset('assets/js/bootstrap.min.js')}}" type="text/javascript" charset="utf-8" async defer></script>
<script src="{{ asset('assets/js/animate-it.js')}}" type="text/javascript"></script>
<script src="{{ asset('assets/js/script.js')}}" type="text/javascript"></script> 

<!-- bxslider -->
<script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
@if(Session::has('status'))
		  <script type="text/javascript">
          notification = @json(session()->pull("status"));
		  function message() {
		  Swal.fire({
			  icon: 'success',
			  text: notification.message, 
			});
		  }
		  window.onload = message;
		  @php 
			  session()->forget('status'); 
		   @endphp
		 </script>
@endif
<!-- stick header -->
<script>
$(window).scroll(function() {
    if ($(this).scrollTop() > 1){  
        $('header').addClass("sticky");
    }
    else{
        $('header').removeClass("sticky");
    }
});
</script>

<script>
var validateContact1=false;

	function validate_contact1(){
		//validate email
		    debugger
			var contact = document.getElementById('phone').value;  
			
			var phoneno = /^\d+/;
			if(contact.match(phoneno))
			{
				document.getElementById("mobile_warning1").style.display = "none";
				validateContact1=true;
			}
		  else
			{
				document.getElementById("mobile_warning1").style.display = "block";
				validateContact1=false;
			}
	}
	
	function validate1(){
		validate_contact1();
		if(validateContact1==true){
			return true;
		}else{
			return false;
		}
	}
	
</script>
</body>

</html>