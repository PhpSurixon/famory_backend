 <!-- //sidebar -->
 
 <style>
     .submenu{
             list-style-type: none;
        padding-left: 15px !important;
     }
 </style>
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-0 my-0 fixed-start ms-0 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand text-center m-0" href="{{ route('advertiser/dashboard') }}">
        <img src="{{ asset('/advertiser/img/logo-ct.png') }}" class="navbar-brand-img h-100" alt="main_logo">
      </a>
    </div>
    <!-- <hr class="horizontal light mt-0 mb-2"> -->
    <br> 
    
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white {{ request()->is('my-account','add-new-card','all-payments') ? 'active' : '' }}" href="{{ route('myAccount') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
              <span class="nav-link-text ms-1">My Account</span>
            </div>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-white {{ request()->is('stickers','selected-sticker/*') ? 'active' : '' }}" href="{{ route('stickers') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
              <span class="nav-link-text ms-1">Famory Tags</span>
            </div>
          </a>
        </li>
        
        
        
     <!--   <li class="nav-item has-submenu">-->
    	<!--	<a class="nav-link {{ request()->is('advertiser/orders','advertiser/invoice','advertiser/stickers','advertiser/selected-sticker/*','advertiser/my-purchasehistory','advertiser/purchasehistory','advertiser/purchasehistory') ? 'active' : '' }} d-flex justify-content-between" href="#"><span class="nav-link-text ms-1">Famory Tags</span> <i class="fa-solid fa-chevron-up" style="font-size:10px;"></i></a>-->
    	<!--	<ul class="submenu collapse {{ request()->is('advertiser/orders','advertiser/invoice','advertiser/stickers','advertiser/selected-sticker/*','advertiser/my-purchasehistory','advertiser/purchasehistory','advertiser/purchasehistory') ? 'show' : '' }}">-->
    	<!--		<li><a class="nav-link {{ request()->is('advertiser/stickers','advertiser/selected-sticker/*') ? '' : '' }}" href="{{ route('stickers') }}">Famory Tags</a></li>-->
    	<!--		<li><a class="nav-link {{ request()->is('advertiser/orders','advertiser/invoice') ? 'active':'' }}" href="{{ route('orders') }}">Orders </a></li>-->
    	<!--		<li><a class="nav-link {{ request()->is('advertiser/my-purchasehistory','advertiser/purchasehistory','advertiser/purchasehistory') ? 'active' : '' }}" href="{{ route('purchasehistory') }}">Purchased History </a></li>-->
    	<!--	</ul>-->
    	<!--</li>-->
        <li class="nav-item">
          <a class="nav-link text-white {{ request()->is('trustedpartners','addNewPartner') ? 'active' : '' }}" href="{{ route('trustedpartners') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
              <span class="nav-link-text ms-1">Trusted Partners</span>
            </div>
          </a>
        </li>
          
        <li class="nav-item">
          <a class="nav-link text-white {{ request()->is('partner/dashboard','new-ad','selected-ad/*','edit-ad/*','archieved-ads') ? 'active' : '' }}" href="{{ route('advertiser/dashboard') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
              <span class="nav-link-text ms-1">My Ads</span>
            </div>
            
          </a>
        </li>
        <!--<li class="nav-item">-->
        <!--  <a class="nav-link text-white {{ request()->is('advertiser/archieved-ads') ? 'active' : '' }}" href="{{ route('archievedAd') }}">-->
        <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
        <!--      <span class="nav-link-text ms-1">Archived Ads</span>-->
        <!--    </div>-->
        <!--  </a>-->
        <!--</li>-->
        <!--<li class="nav-item">-->
        <!--  <a class="nav-link text-white {{ request()->is('advertiser/stickers','advertiser/selected-sticker/*') ? 'active' : '' }}" href="{{ route('stickers') }}">-->
        <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
        <!--      <span class="nav-link-text ms-1">Famory Tags</span>-->
        <!--    </div>-->
        <!--  </a>-->
        <!--</li>-->
        
        <!--<li class="nav-item">-->
        <!--  <a class="nav-link text-white {{ request()->is('advertiser/goToCart') ? 'active' : '' }} " href="{{ route('goToCart') }}">-->
        <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
        <!--      <span class="nav-link-text ms-1">Go To Cart</span>-->
        <!--    </div>-->
        <!--  </a>-->
        <!--</li>-->
       
        
        <!--<li class="nav-item">-->
        <!--  <a class="nav-link text-white {{ request()->is('advertiser/my-purchasehistory','advertiser/purchasehistory','advertiser/purchasehistory') ? 'active' : '' }}" href="{{ route('purchasehistory') }}">-->
        <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
        <!--      <span class="nav-link-text ms-1">Purchased History</span>-->
        <!--    </div>-->
        <!--  </a>-->
        <!--</li>-->
        
        <!--<li class="nav-item">-->
        <!--  <a class="nav-link text-white {{ request()->is('advertiser/orders','advertiser/invoice') ? 'active':'' }} " href="{{ route('orders') }}">-->
        <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
        <!--      <span class="nav-link-text ms-1">Orders</span>-->
        <!--    </div>-->
        <!--  </a>-->
        <!--</li>-->
        
        
        <li class="nav-item">
          <a class="nav-link text-white {{ request()->is('contact-us') ? 'active' : '' }} " href="{{ route('contactUsView') }}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
              <span class="nav-link-text ms-1">Contact Us</span>
            </div>
          </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">
                <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">
                    <span class="nav-link-text ms-1">Logout</span>
                </div>
            </a>
            <!--<a class="nav-link text-white " href="" onclick="event.preventDefault();document.getElementById('logout-form2').submit();">-->
            <!--    <div class="text-white text-center me-2 d-flex align-items-center justify-content-start gap-2">-->
            <!--        <span class="nav-link-text ms-1">Logout</span>-->
            <!--    </div>-->
            <!--</a>-->
            <!--<form id="logout-form2" action="{{ route('logout') }}" method="POST"-->
            <!--class="d-none">-->
            <!--    @csrf -->
            <!--</form>-->
        </li>
      </ul>
    </div>
  </aside>
  
  

  