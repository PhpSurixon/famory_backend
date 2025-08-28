<style>
    .user-d {
        height: 40px !important;
    width: 40px !important;
}
    }
</style>

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                {{-- <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none" placeholder="Search..."
                    aria-label="Search..." /> --}}
                    {{ $title ?? '' }}
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online bg-transparent user-d">
                        <!--<img src="{{ Auth::user()->image }}" alt class="w-px-40  h-px-40 rounded-circle" />-->
                         @if(Auth::user()->image)
                            <img src="{{ Auth::user()->image }}" alt="User Image" class="w-px-40 h-px-40 rounded-circle">
                        @else
                            <img src="/assets/img/famcam.jpg" alt="Default Image" class="w-px-40 h-px-40 rounded-circle">
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <!--<img src="{{ Auth::user()->image }}" alt-->
                                        <!--    class="w-px-40  h-px-40 rounded-circle" />-->
                                            @if(Auth::user()->image)
                                                <img src="{{ Auth::user()->image }}" alt="User Image" class="w-px-40 h-px-40 rounded-circle">
                                            @else
                                                <img src="/assets/img/famcam.jpg" alt="Default Image" class="w-px-40 h-px-40 rounded-circle">
                                            @endif
                                            
                                            
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block"> {{ Auth::user()->first_name }}</span>
                                    <small class="text-muted"> Admin</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <!--<li>-->
                    <!--    <a class="dropdown-item" href="">-->
                    <!--        <i class="bx bx-user me-2"></i>-->
                    <!--        <span class="align-middle">My Profile</span>-->
                    <!--    </a>-->
                    <!--</li>-->
                    <li>
                        <a class="dropdown-item" href="{{ url('user/profile', Auth()->user()->id) }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>


                    {{-- <li>
                        <a class="dropdown-item" href="#">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li> --}}

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href=""  onclick="event.preventDefault();
                        document.getElementById('logout-form2').submit();">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                        <form id="logout-form2" action="{{ route('logout') }}" method="get"
                        class="d-none">
                        @csrf
                    </form>
                    </li>
        </ul>
    </div>
</nav>
    <style>
        .avatar img {
        width: 40px;
        height: 40px; 
        border-radius: 50%; 
        object-fit: cover; 
    }
    
    </style>
