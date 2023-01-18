@extends('layout', ['pageTitle' => 'CUTLETT'])
@push('styles')
@endpush
@section('content')
<!-- slider -->
<div class="main-slider"  >
  <div class="slider1">
     
     <div class="slider-img animatedParent">
	 <div class="container-fuld">
      <img class="main-img" src="{{ asset('assets/images/slider-4.png') }}">
      
         <div class="slider-text" style="text-align:center;">
          <h2 style="margin-left: 20px;" class="animated fadeInUpShort go"><span style="color: #ec3f17;">CUTLETT</span> is best <br>& <span style="color: #fc900f;">healthy</span> Food delivery services</h2>
          
        <p style="text-align:center;" class="animated fadeInDownShort go">when an unknown printer took a galley of type and scrambled it, 
		<br>when an unknown printer took a galley of type and scrambled it. <br>when an unknown printer took a galley of type and scrambled it.
        </p>
		<div class="link animatedParent">
			
			<a  href="#" class="animated fadeInUpShort go" style="margin-right:30px; width:185px;" >
                <img src="{{ asset('assets/images/play-store-btn.png') }}" class="zoom" >
            </a>
			
			<a  href="#" class="animated fadeInUpShort go"  style="width: 185px;">
                <img src="{{ asset('assets/images/app-store-btn.png') }}" class="zoom" >
            </a>
			
        </div>        
        </div>
      </div>     
    </div>
  </div> 
</div><!-- main-slider -->
 <div class="leaf">
    <img src="{{ asset('assets/images/slider-small-img-1.png') }}">
  </div>
<!-- slider -->
<div class="section about animatedParent" id="about">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-sm-6 col-xs-12">
         <div class="about-text animated fadeInUpShort go">
           <img src="{{ asset('assets/images/about_img.png')}}">
         </div>           
      </div><!-- collg -->
      <div class="col-md-6 col-sm-6 col-xs-12">
         <div class="about-text heading-label animated fadeInDownShort go">
         <label>About</label>         
          <div class="main-heading">CUTLETT</div>
          <p>
           Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
	      </p>
           
         </div>   
        <div class="more-btn animated fadeInDownShort go">
          <a href="{{ url('about') }}">Read More</a>         
        </div>        
      </div><!-- collg -->
    </div>
  </div>
</div><!-- about -->

<div class="three_section animatedParent" id="categories">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-4 col-sm-4 col-xs-12  mnp animated fadeInDownShort go">
         <div class="three_section_text">
           <div class="three_section_img">
               <img src="{{ asset('assets/images/three-1-img.jpg')}}">
              <h3>Food</h3>
           </div>       
         </div>           
      </div><!-- col-lg -->
      <div class="col-md-4 col-sm-4 col-xs-12  mnp animated fadeInUpShort go">
         <div class="three_section_text">
           <div class="three_section_img">
               <img src="{{ asset('assets/images/three-1-img.jpg')}}">
              <h3>Food</h3>
           </div>       
         </div>           
      </div><!-- col-lg -->
      <div class="col-md-4 col-sm-4 col-xs-12  mnp animated fadeInDownShort go">
         <div class="three_section_text">
           <div class="three_section_img">
               <img src="{{ asset('assets/images/three-1-img.jpg')}}">
              <h3>Food</h3>
           </div>       
         </div>           
      </div><!-- col-lg -->
    </div>
  </div>
</div> <!-- three_section -->

<div class="section feature animatedParent" id="features">
  <div class="container">
    <div class="row">
		
		<div class="col-lg-12">
			 <div class="feature-text heading-label">
                    
          <div><h2 style="font-size:35px;font-weight: 800;text-transform: uppercase;color:#000 !important;background:none !important; text-align:center;">Why use <span style="color: #ec3f17;">CUTLET</span> products</h2></div>
         
         </div> 
		</div>
        
		 
		<div class="col-md-6 col-sm-6 col-xs-12 animated fadeInDownShort go">
			<div class="all-feature line1 mt-5 ">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
			
              <div class="all-feature line1 mt-5">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
			
            <div class="all-feature line1 mt-5">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
			
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12 animated fadeInUpShort go">
			<div class="all-feature line1 mt-5">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            
            </div>
			
			<div class="all-feature line1 mt-5">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
			
			 <div class="all-feature line1 mt-5">
                <h6>Lorem Ipsum</h6>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
             </div>
		</div> 
    </div>
  </div>
</div>



<div class="section-download animatedParent" id="download">
	<div class="container">
		<div class="row">	
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<div class="download-text animated fadeInUpShort go">
				<div class="download-text">
					<a href="https://play.google.com/store/apps/details?id=com.neosao.myvegiz&hl=en_IN&gl=US" target="_blank" class="animated fadeInDownShort go"> <img src="{{ asset('assets/images/pay-icon.png')}}">Get it on google play</a>
				</div>
			</div>
		</div>		
		<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
			<div class="download-text animated fadeInUpShort go">
				<div class="heading">
					<span>Download</span>
					<h2>This Awesome App Today</h2>
				</div>
				<p class="align-ment">Change the way to purchase your vegetables. You Order We Deliver</p>
			</div>
		</div>
	</div>
	</div>
</div>


<div class="section screens-bg animatedParent" id="screenshot">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-sm-6 col-xs-12 animated fadeInUpShort go">
           <div class="screen-img">
        <div class="screenshot">
            <div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
            <div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
			<div><img src="{{ asset('assets/images/screen1.png')}}" class = "img-responsive" ></div>
         </div><!-- screenshot -->
        </div>
      </div>
      <div class="col-md-6 col-sm-6 col-xs-12 animated fadeInDownShort go">
         <div class="screenshot-text heading-label">
          <label>Screenshot</label>         
          <div class="main-heading">Our Screenshot</div>
          <p>Here are some app screenshots, after you register and log in to your account then you may experience a neat showcase of Products with user-friendly order process.</p>

          
         </div>         
      </div>
    </div>
  </div>
</div>


<div class="section-testimonials section heading-label animatedParent">
  <div class="container">
    <div class="row">   
     <div class="col-md-12 col-sm-12 col-xs-12">     
      <div class="testimonials-text animated fadeInUpShort go">
      <div class="top-testi">
      	 <label>Testimonials</label>         
         <div class="main-heading">Client’s Feedback</div>
      </div>
                
        <div class="testimonials">
            <div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
                  <div class="testimonials-inner-text">
                    <p>t is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>
                   
                    <h6>TEST TEST</h6>
                  </div>
                </div><!-- col-lg -->               
              </div><!-- row -->
            </div>
             <div>
              <div class="row">                
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="testimonials-inner-text">
                    <p>t is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. </p>
    
                    <h6>TEST TEST</h6> 
                  </div>
                </div><!-- col-lg -->
              </div><!-- row -->
            </div>
          </div><!-- testimonials -->
      </div><!-- testimonials-text -->

    </div>
  </div><!-- row -->
  </div><!-- container -->
</div><!-- section-about -->

<div class="main-footer section animatedParent" id="contact"> 
 <form method="post" action="{{ url('/sendmail')}}">
   @csrf
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="contact-form-left">
          <h4>Send your Request</h4>
          
          <form>
          <div class="row">
            <div class="col-lg-6">
          <div class="form-group">          
            <input type="text" name="name" class="form-control" required="required" id="name" aria-describedby="Name" placeholder="Name">           
          </div>
          </div>
          <div class="col-lg-6">
          <div class="form-group">          
            <input type="email" class="form-control" required="required" name="email" id="email" aria-describedby="Email" placeholder="Email">
          </div>
          </div>
          <div class="col-lg-6">
            <div class="form-group">          
            <input type="text" class="form-control" required="required" name="phone" id="phone" aria-describedby="Phone" placeholder="Phone" onchange="validate_contact1();">
             <div class="alert alert-danger" id="mobile_warning1" style="display:none;">Please enter a valid contact number.</div>			
          </div>
          </div>
          <div class="col-lg-6">
          <div class="form-group">          
            <input type="Subject" class="form-control" required="required" name="subject" id="subject" aria-describedby="Subject" placeholder="Subject">           
          </div>
          </div>
          <div class="col-lg-12">
          <div class="form-group">          
           <textarea name="message" class="form-control" rows="4" placeholder="Message*" required="required"></textarea>     
          </div>
          </div>
          <div class="more-btn col-lg-12" style="text-align: center;">
          <input type="submit" value="Submit" class="btn btn-success" >
        </div>
        </form>
        </div>
        </div>
      </div>
     
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="footer-text-right heading-label">
            <label>Address</label>         
          <div class="main-heading">Contact Us</div>
            <ul>             
              <li><p><i class="fa fa-map-marker" aria-hidden="true"></i> mumbai, <br>Dist- Mumbai Pin - 41622</p></li>
              <li><p><i class="fa fa-volume-control-phone" aria-hidden="true"></i> +91-774 222 7777</p></li>
              <li><p><i class="fa fa-envelope" aria-hidden="true"></i> info@cutlet.com</p></li>             
           </ul>
        </div>
      </div>
    </div>
  </div>
</form>
</div><!-- main-footer -->
@endsection
