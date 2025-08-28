@extends('layouts.admin-master', ['title' => 'Users'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create User</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-first_name">First Name</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                            value="{{ old('first_name') }}" autocomplete="first_name" autofocus id="basic-default-first_name"
                                            placeholder="Enter First Name..." name="first_name" />
                                        @error('first_name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-last_name">Last Name</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                            value="{{ old('last_name') }}" autocomplete="last_name" autofocus id="basic-default-last_name"
                                            placeholder="Enter Last Name..." name="last_name" />
                                        @error('last_name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-email">Email Address</label>
                                        <input type="email" name="email" placeholder="Enter Email..."
                                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                            autocomplete="email" id="basic-default-email" />
                                        @error('email')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-phone">Phone No</label>
                                        <input type="number" id="basic-default-phone" name="phone"
                                            class="form-control phone-mask @error('phone') is-invalid @enderror"
                                            value="{{ old('phone') }}" autocomplete="phone" placeholder="Enter Phone No..." />
                                        @error('phone')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-password">Password</label>
                                        <input type="password" id="basic-default-password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter password..." required />
                                        @error('password')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-image">Image</label>
                                        <input type="file" id="basic-default-image" name="image"
                                            class="form-control @error('image') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('image')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                </div>
                            </div>
                            <br/>
                             <div class="button-container">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <style>
        .button-container {
    display: flex;
    justify-content: flex-end; 
    }
    
    .button-container .btn {
        margin-right: 10px; 
    }

   </style>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection
