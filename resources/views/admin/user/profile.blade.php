@extends('layouts.admin-master', ['title' => 'User Profile'])

@section('content')
<style>
    .btn.btn-primary:hover{
        color:#fff;
    }
</style>
   <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-success hidden" id="successMessage">
                <span></span>
            </div>
            <div class="card profile-card">
                <div class="profile-header text-center">
                    <!-- Profile Image -->
                    <!--<img src="{{ $user->image ?? '/assets/img/famcam.jpg' }}" alt="Profile Image" class="profile-image" data-bs-toggle="tooltip" data-bs-placement="top" title="Profile Picture">-->
                     @if(Auth::user()->image)
                        <img src="{{ Auth::user()->image }}" alt="User Image" alt="Profile Image" class="profile-image" data-bs-toggle="tooltip" data-bs-placement="top" title="Profile Picture">
                    @else
                        <img src="/assets/img/famcam.jpg" alt="Default Image" alt="Profile Image" class="profile-image" data-bs-toggle="tooltip" data-bs-placement="top" title="Profile Picture">
                    @endif
                </div>
                <div class="profile-info">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="profile-section" data-bs-toggle="tooltip" data-bs-placement="top" title="User's first name">
                                <h6>First Name</h6>
                                <p>{{ $user->first_name ?? "N/A" }}</p>
                            </div>
                            <div class="profile-section" data-bs-toggle="tooltip" data-bs-placement="top" title="User's last name">
                                <h6>Last Name</h6>
                                <p>{{ $user->last_name ?? "N/A" }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="profile-section" data-bs-toggle="tooltip" data-bs-placement="top" title="User's phone number">
                                <h6>Phone No.</h6>
                                <p>{{ $user->phone ?? "N/A" }}</p>
                            </div>
                            <div class="profile-section" data-bs-toggle="tooltip" data-bs-placement="top" title="User's email address">
                                <h6>Email</h6>
                                <p>{{ $user->email ?? "N/A" }}</p>
                            </div>
                        </div>
                    </div> 
                     <div class="button-container">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="bx bx-edit-alt me-1"></i> Edit</button>
                     <!--<button type="button" class="btn btn-primary" >Back to Dashboard</button>-->
                   <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>

   <!-- Edit Profile Modal -->
   <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form to edit user profile -->
               <form id="profileUpdate" method="post" enctype="multipart/form-data">
                    @csrf
                        <div class="mb-3">
                              <div class="row">
                                    <div class="col-6">
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
                                    <div class="col-6">
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
                                <div class="col-6">
                                    <label class="form-label" for="basic-default-company">Email Address</label>
                                    <input type="email" name="email" placeholder="Enter Email..." class="form-control  @error('email') is-invalid @enderror"
                                        autocomplete="email" id="basic-default-company" value="{{ old('email', $user->email) }}" disabled />
                                        @error('email')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-phone">Phone No</label>
                                        <input type="text" id="basic-default-phone" name="phone"
                                            class="form-control phone-mask  @error('phone') is-invalid @enderror"
                                           autocomplete="phone" placeholder="Enter Phone No..." value="{{$user->phone}}" />
        
                                        @error('phone')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                        <label class="form-label" for="basic-default-password">Password</label>
                                        <input type="password" id="basic-default-password" name="password"
                                            class="form-control phone-mask  @error('password') is-invalid @enderror"
                                           autocomplete="password" placeholder="Enter Password..." />
        
                                        @error('password')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                            </div>
                            <div class="mb-3">
                                 <div class="row">
                                    <div class="col-12">
                                        <label class="form-label" for="basic-default-upload">Upload</label>
                                        <input type="file" id="basic-default-upload" name="image"
                                            class="form-control  @error('image') is-invalid @enderror" />
                                        @error('image')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                     <div class="button-container">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                     </form>
                    <!-- <h5 class="mt-4">Change Password</h5>-->
                    <!-- <form id="updatePassword" method="post" >-->
                    <!--        @csrf-->
                            
                    <!--        <div class="mb-3">-->
                    <!--             <div class="row">-->
                    <!--                <div class="col-12">-->
                    <!--                    <label class="form-label" for="basic-default-last-password">Last Password</label>-->
                    <!--                    <input type="password" name="lastPassword" placeholder="Enter Last Password..."-->
                    <!--                        class="form-control  @error('lastPassword') is-invalid @enderror"-->
                    <!--                        autocomplete="off" id="basic-default-last-password" value=""  />-->
                    <!--                    @error('lastPassword')-->
                    <!--                        <span class="help-block invalid-feedback" role="alert">-->
                    <!--                            <strong>{{ $message }}</strong>-->
                    <!--                        </span>-->
                    <!--                    @enderror-->
                    <!--                </div>-->
                                   
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--        <div class="mb-3">-->
                    <!--             <div class="row">-->
                    <!--                <div class="col-6">-->
                    <!--                    <label class="form-label" for="basic-default-password">New Password</label>-->
                    <!--                    <input type="password" name="password" placeholder="Enter New Password..."-->
                    <!--                        class="form-control  @error('password') is-invalid @enderror"-->
                    <!--                        autocomplete="off" id="basic-default-password" value=""  />-->
                    <!--                    @error('passowrd')-->
                    <!--                        <span class="help-block invalid-feedback" role="alert">-->
                    <!--                            <strong>{{ $message }}</strong>-->
                    <!--                        </span>-->
                    <!--                    @enderror-->
                    <!--                </div>-->
                    <!--                <div class="col-6">-->
                    <!--                    <label class="form-label" for="basic-default-confirm-password">Confirm Password</label>-->
                    <!--                    <input type="password" id="basic-default-confirm-password" name="confirm_password"-->
                    <!--                        class="form-control phone-mask  @error('confirm_password') is-invalid @enderror"-->
                    <!--                       autocomplete="off" placeholder="Enter Confirm Password..." value="" />-->
        
                    <!--                    @error('confirm_password')-->
                    <!--                        <span class="help-block invalid-feedback" role="alert">-->
                    <!--                            <strong>{{ $message }}</strong>-->
                    <!--                        </span>-->
                    <!--                    @enderror-->
                    <!--                </div>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    <br/>-->
                    <!-- <div class="button-container">-->
                    <!--    <button type="submit" class="btn btn-primary">Update</button>-->
                        <!--<a href="{{ route('dashboard') }}" class="btn btn-primary">Back</a>-->
                    <!--</div>-->

                    <!-- </form>-->
            </div>
        </div>
    </div>
</div>

   <style>
    .profile-header {
        background-color: #f5f5f5;
        padding: 20px;
        border-bottom: 1px solid #ddd;
        position: relative;
    }
    .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }
    .profile-image:hover {
        transform: scale(1.1);
    }
    .profile-section {
        margin-bottom: 20px;
        transition: background-color 0.3s ease;
        padding: 10px;
    }
    .profile-section:hover {
        background-color: #f0f0f0;
    }
    .profile-card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .profile-info {
        padding: 20px;
    }
    .btn-primary {
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
     .button-container {
    display: flex;
    justify-content: flex-end; 
    }
    
    .button-container .btn {
        margin-right: 10px; 
    }

</style>
<script>



    $("#profileUpdate").on('submit',function(e){
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('user.update',$user->id) }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#successMessage').removeClass('hidden');
                $('#successMessage').html(response.message);
                $('#editProfileModal').modal('hide');
                
                setTimeout(function(){
                    location.reload();
                }, 800)
                
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $('.invalid-feedback').remove();
                    $('input, select').removeClass('is-invalid');
                    for (let field in errors) {
                            inputField = $('input[name="' + field + '"]');
                            inputField.addClass('is-invalid');
                            inputField.after(
                                '<span class="invalid-feedback d-block" role="alert"><strong>' + errors[field][0] + '</strong></span>');
                        }
                    } else {
                        // alert('Something went wrong, please try again.');
                    }
                }
        });
    });
    
    // $('#updatePassword').on('submit',function(e){
    //     e.preventDefault();
    //     console.log('hello testing');
    //     const formData = new FormData(this);
    //     $.ajax({
    //         // url: "{{ route('user.updateUserPassword', ['id' => $user->id, 'dfgdg' => 'hello']) }}",
    //         type: "POST",
    //         data: formData,
    //         contentType: false,
    //         processData: false,
    //         success: function(response) {
    //             console.log(response);
    //             // $('#successMessage').removeClass('hidden');
    //             // $('#successMessage').html(response.message);
    //             // $('#editProfileModal').modal('hide');
                
    //             // setTimeout(function(){
    //             //     location.reload();
    //             // }, 800)
                
    //         },
    //         error: function(xhr, status, error) {
    //             if (xhr.status === 422) {
    //                 let errors = xhr.responseJSON.errors;
    //                 $('.invalid-feedback').remove();
    //                 $('input, select').removeClass('is-invalid');
    //                 for (let field in errors) {
    //                         inputField = $('input[name="' + field + '"]');
    //                         inputField.addClass('is-invalid');
    //                         inputField.after(
    //                             '<span class="invalid-feedback d-block" role="alert"><strong>' + errors[field][0] + '</strong></span>');
    //                     }
    //                 } else {
    //                     // alert('Something went wrong, please try again.');
    //                 }
    //             }
    //     });
    // })
</script>

   @section('scripts')
   <script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
    
    
    
    
    </script>
   @endsection
   @endsection
