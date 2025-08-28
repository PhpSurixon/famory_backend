@extends('layouts.admin-master', ['title' => 'Users'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit User</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('user.update',$user->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                               <div class="row">
                                   
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-first_name">First Name</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                            autocomplete="first_name" autofocus id="basic-default-first_name"
                                            placeholder="Enter First Name..." name="first_name" value="{{$user->first_name}}"/>
                                        @error('first_name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-last_name">Last Name</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           autocomplete="last_name" autofocus id="basic-default-last_name"
                                            placeholder="Enter Last Name..." name="last_name" value="{{$user->last_name}}" />
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
                                <label class="form-label" for="basic-default-company">Email Address</label>
                                <input type="email" name="email" placeholder="Enter Email..."
                                    class="form-control @error('email') is-invalid @enderror"
                                    autocomplete="email" id="basic-default-company" value="{{ old('email', $user->email) }}" readonly />

                                @error('email')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                <label class="form-label" for="basic-default-phone">Phone No</label>
                                <input type="number" id="basic-default-phone" name="phone"
                                    class="form-control phone-mask  @error('phone') is-invalid @enderror"
                                   autocomplete="phone" placeholder="Enter Phone No..." value="{{$user->phone}}"/>

                                @error('phone')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                              <div class="col-xl-6 col-lg-6 col-md-6 col-12">
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
                                 @if ($user->image)
                                            <img id="image-preview" src="{{ $user->image }}" alt="Image Preview" class="mt-2"  style="display:block; width: 100px; height: 100px; object-fit: cover; border-radius: 50%; float: left; margin-right: 10px;">
                                        @else
                                            <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                        @endif
                            </div>
                        </div>
                     </div>
                        <br/>
                        <div class="button-container">
                            <a href="{{ route('get-users') }}" class="btn btn-primary">Back</a>
                            <button type="submit" class="btn btn-primary">Update</button>
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
