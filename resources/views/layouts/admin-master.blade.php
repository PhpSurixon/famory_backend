<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head')
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('layouts.partials.sidebar')
            <div class="layout-page">
                @include('layouts.partials.header')
                <div class="content-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="page-wrapper">
        @include('layouts.partials.sidebar')
        <div class="page-container2">
            @include('layouts.partials.header')
            @include('layouts.partials.mobile-sidebar')
            @yield('content')
        </div>
    </div> --}}
    @include('layouts.partials.footer')
    
    <script>
         $(document).ready(function() {
             $(".select2").select2();
         });
    </script>
</body>

</html>
