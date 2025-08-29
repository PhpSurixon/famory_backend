<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.advertiser-partial.head')
    
    <style>
        .logout-button {
          font-size: 14px;
          text-transform: capitalize;
          font-weight: 500;
          border-radius: 100px;
          padding: 8px 0;
          display: block;
          width: 100%;
          background: #ffff;
          color:#ed8530;
          border:2px solid #ed8530;
        }
        .logout-button:hover {
            background: #ed8530;
            color:white;
            border:2px solid #ed8530;
        }
        .logout-fill-button{
            font-size: 14px;
          text-transform: capitalize;
          font-weight: 500;
          border-radius: 100px;
          padding: 8px 0;
          display: block;
          width: 100%;
          background:  #ed8530;
          color:#ffffff !important;
          border:2px solid #ed8530;
          
        }
        .logout-fill-button:hover{
        background:#ffffff;
          color:#ed8530 !important;
          border:2px solid #ed8530;
        }
        
        .modal.fade .modal-dialog {
    transition: none !important;
    transform: none !important;
        
    </style>
</head>

<body class="g-sidenav-show" style="background-color:#e9e9e9bd;">
    <!--sidebar-->
    @include('layouts.advertiser-partial.sidebar')
     <!-- main section -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-0">
        <!--header-->
        @include('layouts.advertiser-partial.header')
        
        <!--content-->
        <div class="container-fluid py-4">
             @yield('content')
        </div>
        
    </main>  
    
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-modal="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-white border-0 pb-5" style="text-align: center;align-items: center;width: 90%;margin:auto;border-radius:15px;">
                <div class="modal-header pb-0 pt-5">
                    <img src="{{ asset('/advertiser/img/icons/logout-modal.png') }}" style="object-fit: contain;width: 70px;">
                </div>
                <div class="modal-body p-0">
                    <span style="font-weight: bolder; font-size: 25px;margin: 20px 0px;display: block;color: #26292d;">Logout</span>
                        <p class="mb-3">Are you sure you want to logout?</p>
                    <div style="display:contents" class="mb-5">
                        <button type="button" class="btn logout-fill-button" onclick="event.preventDefault();document.getElementById('logout-form2').submit();">Logout</button>
                        <button type="button" class="btn logout-button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                    <form id="logout-form2" action="{{ route('logout') }}" method="get"
                    class="d-none">
                        @csrf 
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.advertiser-partial.footer')
</body>

</html>