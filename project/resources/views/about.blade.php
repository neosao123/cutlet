@extends('layout', ['pageTitle' => 'CUTLET About'])
@section('content')
<div class="inner-top-header animatedParent" >
  <div class="container">
    <div class="row">
      <div class="col-12 animated fadeInUpShort go"> 
        <div class="header-text abt-inner">
          <h2>About Us</h2>
          <a href="#">Home |</a> <label>About us</label>          
        </div><!-- header-text -->
      </div><!-- col-lg -->
    </div><!-- row -->
  </div><!-- container -->
</div>

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
          <div class="main-heading">CUTLET</div>
          <p>
           Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
	      </p>
           
         </div>   
        <div class="more-btn animated fadeInDownShort go">
          <a href="{{ url('admin/about') }}">Read More</a>         
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
@endsection
 