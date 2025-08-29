@extends('components.guest')

@section('content')
    <style>
        .login.login-3 .login-form {
            max-width: 100%;
        }

        .logo-img {
            display: none !important;
        }

        .term_para {
            max-width: 1020px;
            margin: auto;
        }

        #kt_subheader {
           background: #003366 !important; 
            margin-bottom: 20px;
            /* border-bottom: 4px solid #ff454e; */
            min-height: 72px;
        }

        #kt_subheader img {
            width: 100px;

        }

        h1 {
            color: #3F4254;
        }

        .term_para p {
            background: transparent !important;
        }
            .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    </style>

    <div class="subheader py-3 py-lg-8 subheader-transparent" id="kt_subheader">
        <div class="container-fluid">
            <div class="d-flex align-items-center mr-1">
                <div class="m-auto">
                    <img alt="Logo" src="{{ asset('assets/img/famcamlogo.png') }}" class="card-img-top ">
                      
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="term_para">
            <div class="term_heading">
                <h1 class="fl-post-title text-center my-4">{{ $page->page_name }}</h1>
            </div>
            {!! $page->page_content !!}
        </div>
    </div>
@endsection
