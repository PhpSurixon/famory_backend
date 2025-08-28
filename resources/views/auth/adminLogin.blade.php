<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head')
    
    <style>
        
    .alert {
        position: relative;
        padding: 0.6rem 0.9375rem;
        margin-bottom: 1rem;
        border: 0 solid transparent;
        border-radius: 0.375rem;
    }
    @media screen and (min-width: 1200px) {
        .content-wrapper .container-xxl{
            margin-right:auto !important;
        }
    }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <div class="layout-page">
                <div class="content-wrapper">
                   <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{ route('admin.login') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo logo-login">
                                    <img src="{{ asset('assets/img/app_logo.png') }}" alt="">
                                </span>
                                <!--<span class="app-brand-text demo text-body fw-bolder">Fam Cam</span>-->
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">Welcome to Famory!</h4>
                        <p class="mb-4">Please sign-in to your account</p>
                        
                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        <form id="formAuthentication" class="mb-3" action="{{ route('adminStore') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email or Username</label>
                                <input type="text" class="form-control  @error('email') is-invalid @enderror" id="email" name="email"
                                    placeholder="Enter your email or username" autofocus />
                                @error('email')
                                    <span class="text-danger" role="alert" style="font-size: 14px;">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label"
                                        for="password">Password</label>
                                    {{-- <a href="auth-forgot-password-basic.html">
                                        <small>Forgot Password?</small>
                                    </a> --}}
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control  @error('password') is-invalid @enderror" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="remember-me"> {{ __('Remember Me') }} </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
   
    @include('layouts.partials.footer')
</body>

</html>



