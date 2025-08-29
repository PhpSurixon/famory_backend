<!-- Required meta tags-->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Title Page-->
<title>Famory</title>
<base href="/public">
<link rel="icon" type="image/x-icon" href="{{asset('/assets/img/favicon/favicon.png')}}" />

{{-- new head --}}

{{-- fonts --}}
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

<link href="{{ url('/') }}/assets/css/demo.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/pages/page-account-settings.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/pages/page-auth.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/pages/page-icons.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/pages/page-misc.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/core.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/css/theme-default.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/fonts/boxicons.css" rel="stylesheet" media="all">
<link href="{{ url('/') }}/assets/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" media="all">
<!--<script src="https://unpkg.com/google-cloud-storage@2.0.0/index.js"></script>-->


<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->

<!-- Include Summernote CSS and JS via CDN -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script> -->


<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/apex-charts/apex-charts.css" />

<!-- Page CSS -->

<!-- Helpers -->
<script src="{{ url('/') }}/assets/vendor/js/helpers.js"></script>

<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="{{ url('/') }}/assets/js/config.js"></script>

<!-- Row Group CSS -->
<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css">
<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
<link rel="stylesheet" href="{{ url('/') }}/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.css') }}">


