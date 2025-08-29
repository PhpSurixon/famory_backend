<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head')
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
                                            <a href="{{ route('login') }}" class="app-brand-link gap-2">
                                                <span class="app-brand-logo demo logo-login">
                                                    <img src="{{ asset('assets/img/app_logo.png') }}" alt="">
                                                </span>
                                                <!--<span class="app-brand-text demo text-body fw-bolder">Fam Cam</span>-->
                                            </a>
                                        </div>
                                        <!-- /Logo -->
                                        <h4 class="mb-2">Welcome to Fam Cam!</h4>
                                        @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                        @endif
                                        <form id="formAuthentication" class="mb-3" action="{{ route('register') }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf

                                            <!-- first name-->
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">first_name</label>
                                                <input type="text"
                                                    class="form-control  @error('first_name') is-invalid @enderror"
                                                    id="first_name" name="first_name"
                                                    placeholder="Enter your email or username" autofocus />
                                                @error('first_name')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>last_name
                                                @enderror
                                            </div>
                                            <!-- last name-->
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">last_name</label>
                                                <input type="text"
                                                    class="form-control  @error('last_name') is-invalid @enderror"
                                                    id="last_name" name="last_name"
                                                    placeholder="Enter your last_name or username" autofocus />
                                                @error('last_name')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <!-- email-->
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="text"
                                                    class="form-control  @error('email') is-invalid @enderror"
                                                    id="email" name="email" placeholder="Enter your email or username"
                                                    autofocus />
                                                @error('email')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>

                                            <!--company name -->
                                            <div class="mb-3">
                                                <label for="comapany_name" class="form-label">company name</label>
                                                <input type="text"
                                                    class="form-control  @error('company_name') is-invalid @enderror"
                                                    id="company_name" name="company_name"
                                                    placeholder="Enter your email or username" autofocus />
                                                @error('company_name')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>

                                            <!--company address -->
                                            <div class="mb-3">
                                                <label for="company_address" class="form-label">company address</label>
                                                <input type="text"
                                                    class="form-control  @error('company_address') is-invalid @enderror"
                                                    id="company_address" name="company_address"
                                                    placeholder="Enter your company_address or username" autofocus />
                                                @error('company_address')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>

                                            <!--company logo -->
                                            <div class="mb-3">
                                                <label for="email" class="form-label">company logo</label>
                                                <input type="file"
                                                    class="form-control  @error('logo') is-invalid @enderror" id="logo"
                                                    name="logo" autofocus />
                                                @error('logo')
                                                <span class="text-danger" role="alert" style="font-size: 14px;">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>


                                            <div class="mb-3 form-password-toggle">
                                                <div class="d-flex justify-content-between">
                                                    <label class="form-label" for="password">Password</label>

                                                </div>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" id="password"
                                                        class="form-control  @error('password') is-invalid @enderror"
                                                        name="password"
                                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                        aria-describedby="password" />
                                                    <span class="input-group-text cursor-pointer"><i
                                                            class="bx bx-hide"></i></span>
                                                </div>
                                                @error('password')
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <!-- confirm password -->
                                            <div class="mb-3 form-password-toggle">
                                                <div class="d-flex justify-content-between">
                                                    <label class="form-label" for="password">confirm Password</label>

                                                </div>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" id="password_confirmation"
                                                        class="form-control  @error('password_confirmation') is-invalid @enderror"
                                                        name="password_confirmation"
                                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                        aria-describedby="password_confirmation" />
                                                    <span class="input-group-text cursor-pointer"><i
                                                            class="bx bx-hide"></i></span>
                                                </div>
                                                @error('password_confirmation')
                                                <span class="text-danger" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn btn-primary d-grid w-100" type="submit">Sign
                                                    in</button>
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