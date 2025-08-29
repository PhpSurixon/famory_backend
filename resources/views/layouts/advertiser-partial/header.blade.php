 <!-- Navbar -->
 <style>
     .cart-badge{
        position: relative;
            left: -12px;
    top: -3px;
           padding: 6px 5px 5px;
    width: 22px;
    height: 22px;
        border-radius: 100px;
        /*border: 1px solid #fff;*/
     }
     
     .navbar-main .active-nn:hover {
         background-color: rgb(255 255 255 / 31%)
     }
     
     .navbar-main .active-nc:hover {
         background-color: rgb(255 255 255 / 31%)
     }
     
     .navbar-main .active-nn {
          margin-right: 28px;
          padding: 3px 5px;
     }
     
     .navbar-main .active-nc {
          margin-right: 28px;
          padding: 3px 5px;
     }
     
     
 </style>
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0  border-radius-0 bg-primary header-one" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-3 px-3">
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-0" id="navbar">
          <div class="ms-md-auto pe-md-0 d-flex align-items-center">
            <div class="input-group input-group-outline">

              <a href="javascript:;" class="nav-link text-body p-0 d-none" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            
            </div>
          </div>

        </div>
      </div>
    </nav>

    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-0 bg-dark" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-0" id="navbar">
          <div class="w-100 pe-md-0 d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-3 color-white" style="width: 30%;">
               <a href="{{ url($previous ?? '') }}">@if(!empty($previous))<i class="fa-solid fa-arrow-left"></i>@endif</a> {{ $title ?? '' }}
            </div>
            
            <div class="input-group input-group-outline justify-content-end">      
              <div class="d-flex align-items-center justify-content-end mobileView">
               
                @if(!empty($viewRoute))
                    <div class="activeN">
                           {{$viewRoute}} {{'link not found'}}
                       <a href="{{ url($viewRoute) }}"><i class="fa-solid fa-eye"></i></a>
                    </div>
                @endif
                @if(!empty($editRoute))
                    <div class="activeN">
                      <a href="{{ url($editRoute) }}"><i class="fa-solid fa-pen-to-square" style="font-size:21px!important;"></i></a>
                    </div>
                @endif
                
                
                
                
                @if(!empty($archiveAd))
                    <div class="activeN">
                        <a href="{{ url($archiveAd) }}">
                            <i class="fa fa-archive" aria-hidden="true" style="color:white;font-size:16px;font-weight: 600;"></i>
                            <span style="font-size: 16px; margin: 3px;color:#fff;">Archived Ad</span>
                        </a>
                    </div>
                @endif
                
                @if(!empty($order))
                    <div class="activeN active-nn">
                        <a href="{{ url($order) }}">
                            <i class="fa fa-list" aria-hidden="true" style="color:white;font-size:16px;font-weight: 600;"></i>                            
                              <span style="font-size: 16px; margin: 3px;color:#fff;">Orders</span>
                        </a>
                    </div>
                @endif
                
                @if(!empty($purchase_history))
                    <div class="activeN active-nn">
                        <a href="{{ url($purchase_history) }}">
                            <i class="fa fa-history" aria-hidden="true" style="color:white;font-size:16px;font-weight: 600;"></i>
                            <span style="font-size: 16px; margin: 3px;color:#fff;">Purchase History</span>
                        </a>
                    </div>
                @endif
                
                
                
                @if(!empty($addRoute))
                    <div class="activeN active-nn">
                        <a href="{{ url($addRoute) }}">
                            <i class="fa-regular fa-plus" style="color:white;font-size:16px;font-weight: 600;">
                              <span style="font-size: 16px; margin: 3px;">Add New</span>
                            </i>
                        </a>
                    </div>
                @endif
                
                
                @if(!empty($deleteRoute))
                    <div class="activeN active-nn">
                        <form id="delete-form" action="{{ url($deleteRoute) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('POST') <!-- This should be POST if your route is defined as POST -->
                            <a href="#" onclick="confirmDelete(); return false;">
                                <i class="fa-solid fa-trash-can"  style="font-size:21px!important;"></i>
                            </a>
                        </form>
                    </div>
                @endif
                
                <!--<div class="activeN">-->
                <!--     <a href="{{ route('goToCart')  }}"><i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 21px;"><sup><span style="color:red;">3</span></sup></i></a>-->
                <!--</div>-->
                
                <!--@php-->
                <!--    $cartItemCount = DB::table('add_to_carts')->where('user_id', Auth::id())->count();-->
                <!--@endphp-->
                
                
                @php
                    // Count items in the cart that have products with count > 0
                    $cartItemCount = DB::table('add_to_carts')
                        ->join('products', 'add_to_carts.product_id', '=', 'products.id')
                        ->where('add_to_carts.user_id', Auth::id())
                        ->where('products.count', '>', 0)
                        ->count();
                @endphp
                
                
                @if(!request()->routeIs('goToCart'))
                    @if($cartItemCount > 0)
                        <!-- Display something if there are items in the cart -->
                        <div class="activeN active-nc">
                        <a href="{{ route('goToCart') }}">
                            <i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 21px; color:#fff;">
                                <sup><span class="badge bg-danger text-white cart-badge" >{{ $cartItemCount }}</span></sup>
                            </i>
                        </a>
                        </div>
                    @else
                        <!-- Display something else if the cart is empty -->
                        <div class="activeN active-nc">
                        <a href="{{ route('goToCart') }}">
                            <i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 21px;color:#fff;"></i>
                        </a>
                        </div>
                    @endif
                @endif
                

                
                
               
                <!--@if(!empty($deleteRoute))-->
                <!--    <div class="activeN">-->
                <!--      <a href="{{ url($deleteRoute) }}"><i class="fa-solid fa-trash-can"></i></a>-->
                <!--    </div>-->
                <!--@endif-->
                
                
                

              </div>
            </div>
          </div>

        </div>
      </div>
    </nav>

    <div class="navbar navbar-main navbar-expand-lg px-0 mx-0 shadow-none border-radius-0 bg-info" style="background-image: url('{{ asset('assets/img/bg-1.jpg') }}');background-repeat: no-repeat;background-size: cover; " id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-0" id="navbar">
          <div class="w-100 pe-md-0 d-flex align-items-center justify-content-between px-4">

            <div class="d-flex align-items-center gap-3 color-white w-50">
              Advertise with Us
            </div>

            <div class="input-group input-group-outline justify-content-end">      
              <button class="btn mb-0 bg-white border-radius-100 learn-more-btn" data-bs-toggle="modal" data-bs-target="#PromoteModal">Learn More</button>
            </div>
          </div>

        </div>
      </div>
    </div>
    <!-- End Navbar -->
    
    
    <!-- Modal -->
<div class="modal fade" id="PromoteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <!--<div class="modal fade" id="PromoteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">-->
  <div class="modal-dialog" >
    <div class="promote-modal" style="background-image: url('{{ asset('assets/img/bg-1.png') }}');background-repeat: no-repeat;background-size: cover;">
      <div class="modal-header">
        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Promote Your Business on the Famory Platform</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <i class="fas fa-xmark"></i>
        </button>
      </div>
      <div class="logo-content">
        <img src="{{ asset('assets/img/logo-ct(1).png') }}" class="" alt="main_logo" style="width: 100px;">
      </div>

      <div class="box">
        <h6>Famory Offers:</h6>
        <ul>
          <!--<li><b>Targeted Audience:</b> Reach individuals navigating grief and mourning</li>-->
          <li>Use Famory to Advertise your Business or Service.  You can be listed in our featured partners section for FREE!</li>
          <li>Ads are monthly and local to your area.  You can get an overview of your impressions and metrics for each campaign you wish to run.</li>
          <li>Create new cashflow for your business by selling Famory Tags.  Its simple to order and we will send them to you.  Then you can resell the tags to help your customers promote unity and memories.</li>
          <!--<li><b>Trusted Environment:</b> A safe space for users to find comfort and nisiounces</li>-->
          <!--<li>Brand Alignment Showcase your compassionate services cluring e difficult time</li>-->
        </ul>
        <!--<p>Reach grieving users with compassion on Famory Our low-cost banner ads provide a supportive space to connect with those experiencing loss</p>-->
        <p class="fw-bold">Do you have any questions?  Use our contact form and we will get right back to you!</p>
      </div>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            
            
            buttons: {
                cancel: {
                    text: "No, cancel!",
                    value: null,
                    visible: true,
                    className: "btn btn-danger",
                    closeModal: true,
                },
                confirm: {
                    text: "Yes, delete it!",
                    value: true,
                    visible: true,
                    className: "btn btn-primary",
                    closeModal: true
                }
            },
        }).then((result) => {
            if (result) {
                document.getElementById('delete-form').submit();
            }
        });
    }
    
</script>



