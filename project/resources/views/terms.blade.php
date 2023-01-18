<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>CUTLETT</title>

<!-- googlefont -->
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Mukta:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- googlefont -->
<!-- favicon -->
<!-- favicon -->
 <link rel="shortcut icon" href="{{asset('assets/theme/assets/images/logo.png')}}" type="image/x-icon">
<!-- <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"> -->

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
<!-- bxslider -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
<!-- bxslider -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<!-- css  -->
</head>

<body>
	<!-- top -->
	<div id="top"></div> 

 <div class="screens-bg">
	
	<div class="section about screens-bg img-responsive all_policy">
	
		<div class="container" >
			<div class="row">
			<div  class="col-lg-12 top-inner">
			    <h2 class="main-heading" id="main-heading">Terms & Conditions</h2>
		        <a href="{{ url('/')}}">Home |</a> <label>Terms &amp; Conditions</label>
			</div>
			  <div class="col-sm-12">
				 <h4 class="main-heading">1. Terms</h4>
					<ul style="list-style-type:disc" class="ml-5">
						<li>Downloading, accessing, or otherwise using the App indicates that you have read this Privacy Policy and consent to its terms. If you do not consent to the terms of this Privacy Policy, do not proceed to download, access, or otherwise use the App.</li>
						<li>We collect your personal information in order to provide and continually improve our products and services.</li>
						<li>Our privacy policy is subject to change at any time without notice. To make sure you are aware of any changes, please review this policy periodically. The last updated date can found at the beginning of this policy.</li>
					</ul>
					
				<h4 class="main-heading">2. Terms</h4>
					<ul style="list-style-type:disc" class="ml-5">
						<li>Downloading, accessing, or otherwise using the App indicates that you have read this Privacy Policy and consent to its terms. If you do not consent to the terms of this Privacy Policy, do not proceed to download, access, or otherwise use the App.</li>
						<li>We collect your personal information in order to provide and continually improve our products and services.</li>
						<li>Our privacy policy is subject to change at any time without notice. To make sure you are aware of any changes, please review this policy periodically. The last updated date can found at the beginning of this policy.</li>
					</ul>
		     </div>
		</div>
	  </div>
	</div>

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
</body>

</html>