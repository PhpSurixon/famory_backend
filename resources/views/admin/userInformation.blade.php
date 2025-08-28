<!DOCTYPE html>
    <html lang="en">
        <head>
            <!-- Required meta tags -->
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
            <!-- Bootstrap CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap" rel="stylesheet" />
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;700&display=swap" rel="stylesheet" />
            
            <!-- jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert@2"></script>


            <!-- jQuery Validate -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.min.js"></script>
            
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
            
            <!-- Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="{{asset('public/js/sweetalert.min.js')}}"></script>

            <title>User Delete Account Request</title>
            <link rel="icon" type="image/x-icon" href="{{asset('/assets/img/favicon/favicon.png')}}"/>
            <style>
                body {
                    font-family: "Poppins", sans-serif;
                     background:#f3f6f9;
                }
                .logo{
                   height: 20%;
                    
                }
                .img{
                    display: block;
                    margin-left: auto;
                    margin-right: auto;
                    height: 80px;
                }
                .bRqCYC .Header-h7cxjh-0 {
                    height: 100%;
                    max-width: 1200px;
                    margin-left: auto;
                    margin-right: auto;
                }
                .busVyp {
                    display: flex;
                    padding: 1.25rem 1.75rem;
                }
                .header-img {
                    max-height: 100%;
                    width: auto;
                    /* width: 240px; */
                }
                .bRqCYC {
                    position: sticky;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 100px;
                    border-bottom: 1px solid lightgray;
                    background: #f3f6f9;
                    z-index: 100;
                }
                .information-sec p {
                    /*display: inline;*/
                    color: #505965;
                    font-size: 15px;
                }
                .information-sec h6 {
                    display: inline;
                    font-weight: 600;
                    font-size: 18px;
                    color: #575757;
                }
                .information-sec {
                    margin-bottom: 20px !important;
                    line-height: 1.8 !important;
                }
                .information-heading {
                    font-weight: 600;
                }
                input[type=number]::-webkit-inner-spin-button, 
                input[type=number]::-webkit-outer-spin-button { 
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                    margin: 0; 
                }
                .information-sec {
                    margin-bottom: 15px !important;
                    line-height: 1.5 !important;
                    border-bottom: 1px dashed #888;
                    padding: 0 0 10px;
                }
                .information-sec h6 {
                    display: inline;
                    font-weight: 600;
                    font-size: 18px;
                }
                .fl-post-title {
                    margin-top: 0 !important;
                    text-align: center !important;
                }
                .information-heading {
                    font-weight: 600;
                    margin: 0 !important;
                }
                .update_form label {
                    font-size: 18px;
                }
                .update_form input {
                    height: 45px;
                }
                label.error {
                    font-size: 12px;
                    font-weight: 500;
                    color: red;
                    margin: 5px 0 0;
                }
                .form-control.error {
                    border: 1px solid red !important;
                }
                .button-input-box {
                    position: relative;
                }
                .button-input-box button {
                    position: absolute;
                    right: 9px;
                }
                .button-input-box input {
                    padding-right: 80px;
                }
                .update_form button.submit-button {
                    font-size: 16px;
                    padding: 5px 20px;
                    border-radius: 5px;
                }
                .box{
                    background : #f3f6f9;
                }
                .form-container{
                    border: 1px solid #ddd;
                    border-radius: 10px;
                    padding: 0 20px;
                    margin: 30px 0;
                    box-shadow: 0 0px 20px #d5d5d5;
                }
                .btn-primary, .btn-primary:hover, .btn-primary:active, .btn-primary:visited, .btn-primary:focus{
                    color: #fff;
                    background-color: #1550ae;
                    border-color: #00cc8c;
                }
                #kt_subheader{
                    /*background: #3977d9 !important;*/
                    margin-bottom: 10px;
                    /*border-bottom: 4px solid #00cb8c;*/
                    min-height: 120px;
                    /*padding: 30px;*/
                }
                .headline{
                    text-align: center;
                    font-weight: 500;
                    font-size: 28px;
                    padding-top: 30px;
                    color: #3f4254;
                }
                
            </style>
        </head>
        <body>
          
            <section style="background-color:#f3f6f9">
                <div class="subheader py-3 py-lg-8 subheader-transparent" id="kt_subheader">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center mr-1" >
                            <div class="m-auto">
                                <img alt="Logo" src="https://admin.famoryapp.com/assets/img/app_logo.png" class="max-h-30x" width="300px">
                            </div>                               
                        </div>        
                    </div>
                </div> 
                <div class="container box mt-2">
    
                    
                     
                    <div class="form-container">
                        <h5 class="headline">Delete Account</h5>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 pt-5 pl-lg-3 text-justify pr-lg-4">
                                <div class="information-sec">
                                    <h6>Name:</h6>
                                    <p>The name of the person making the request. This will help you verify their identity and ensure that you`re deleting the right data.</p>
                                </div>
                                <div class="information-sec">
                                    <h6>Contact information:</h6>
                                    <p>You`ll need a way to get in touch with the person making the request, so you`ll want to include fields for their email address and/or phone number.</p>
                                </div>
        
                                <div class="information-sec">
                                    <h6>Verification information:</h6>
                                    <p>To ensure that the request is valid, you may want to ask for additional information to verify the person`s identity, such as a verification code sent to their email.</p>
                                </div>
                                
                                <div class="information-sec" style="border-bottom:none;">
                                    <h6>Reason for deletion:</h6>
                                    <p>It`s helpful to know why someone wants their data deleted. This will help you evaluate whether the request is valid and whether there are any legal or regulatory requirements that apply.</p>
                                </div>
                                
                                <!--<div class="information-sec" style="border-bottom:none;">-->
                                <!--    <h6>Additional comments:</h6>-->
                                <!--    <p>Include an open text field for any additional comments or questions the person making the request may have.</p>-->
                                <!--</div>-->
                            </div>
                            <div class="col-md-6 pt-5 col-sm-12">
                                <form id="delete_acount_request"  method="post">
                                     @csrf
                                    <div class="col-sm-12 container p-0 pb-3">
                                        <div class="update_form">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" id="name" placeholder="Name" name="name" data-rule-required=true data-msg-required="Name is required">
                                            </div>
                                            <div class="form-group">
                                                <label for="phone">Phone Number</label>
                                                <input type="number" maxlength="10" class="form-control" id="phone" placeholder="Contact..." name="phone" data-rule-required=true data-msg-required="Phone Number is required">
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <div class="button-input-box">
                                                    <button type="button" class="btn btn-primary mt-1" onclick="sendOtp()" id="send_otp_btn">Send OTP</button>
                                                    <input type="email" class="form-control" id="email" placeholder="Email" name="email" data-rule-required=true data-msg-required="Email is required">
                                                    <div>
                                                        <p id="error-message" style="color:red;">
                                                            
                                                        </p>
                                                    </div>                                        
                                                </div>
                                            </div>
                                            <p id="showOTPMsg" style="color:green">OTP send, please check e-mail</p>
                                            <div class="form-group" id="verification_html">
                                                <div id="verification_alert"></div>
                                                <label for="">Verification OTP</label>
                                                <input class="form-control" type="number" id="verification_code" name="verification" placeholder="Verification..." data-rule-required=true data-msg-required="Please enter the verification code sent to your email"  style="resize:none;">
                                                <p id="otp-message" style="color:red;">
                                            </div>
                                            <div class="form-group">
                                                <label for="reason">Reason for deletion</label>
                                                <textarea rows="5" cols="50" class="form-control" id="reason" name="reason" placeholder="Reason for deletion..." data-rule-required=true style="resize:none;" data-msg-required="Reason for deletion is required"></textarea>
                                            </div>
                                            <input type="hidden" class="form-control" id="status" name="source" value="web">
                                            <button type="button" class="btn btn-primary mt-3 btn- submit-button" onclick="saveInfo();" name="submit">Submit</button>
                                            <br>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <script type="text/javascript">
                function sendOtp() {
                    var email = $('#email').val();
                    $.ajax({
                        type: 'POST',
                        url: '/api/verifyEmail',
                        data: {
                            email: email,
                        },
                        success: function (response) {
                            if (response.status == 404) {
                                if (response.status == 404 && response.message == 'Email does Not exist, Please Try Again') {
                                    showErrorMessage(response.message);
                                } else {
                                    swal({
                                        title: response.message,
                                        text: "Error!",
                                        icon: "error",
                                        button: "Cancel",
                                        timer: 4000,
                                    });
                                }
                            }else{
                                $("#showOTPMsg").show();
                                    setTimeout(function() { $("#showOTPMsg").hide(); }, 4000);
                                }
                        },
                        error: function (error) {
                            console.error('Error sending OTP:', error);
                        }
                    });
                }

               
                function showErrorMessage(message) {
                    
                    var errorMessageElement = $('#error-message');
                    var emailInputElement = $('#email');
                    
                    if (errorMessageElement.length) {
                        errorMessageElement.text(message);
                    }
                    
                    emailInputElement.on('focus', function () {
                        errorMessageElement.text('');
                    });
                }

                
                
                function saveInfo() {
                     if (!$('#delete_acount_request').valid()) {
                        return; // Stop execution if the form is invalid
                     }
                    var formData = $('#delete_acount_request').serialize();
                    $.ajax({
                        type: "POST",
                        url: "/api/userInformationForDeleteAC",
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response){
                           
                            if (response.status == 404) {
                                swal({
                                    title: response.message,
                                    text: "Error!",
                                    icon: "error",
                                    button: "Ok",
                                    timer: 4000,
                                });
                            }else{
                                // window.location.reload();
                                $('#delete_acount_request')[0].reset();
                                swal({
                                    title: response.message,
                                    text: "Successfully!",
                                    icon: "success",
                                    button: "Ok",
                                    timer: 4000,
                                }).then(() => {
                            // window.location.href = '/get-delete-user-request';
                            });
                                    }
                        },
                        error: function(error) {
                             if(error.responseJSON.status_code == 400){
                                 var errors = error.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        // Display error messages next to the form fields
                                        var input = $('[name="' + key + '"]');
                                        var errorMessage = $('<label class="error"></label>').text(value[0]);
                                        input.addClass('error');
                                        input.after(errorMessage);
                                    });
                                } else {
                                swal({
                                    title: error.responseJSON.message,
                                    text: "Error!",
                                    icon: "error",
                                    button: "Ok",
                                    timer: 4000,
                                });
                            }
                        }
                    });
        
                    return false;
                }
                
                function showOTPMessage(message) {
                    
                    var errorMessageElement = $('#otp-message');
                    var otpInputElement = $('#verification_code');
                    
                    if (errorMessageElement.length) {
                        errorMessageElement.text(message);
                    }
                    
                    otpInputElement.on('focus', function () {
                        errorMessageElement.text('');
                    });
                }
                
                $(document).ready(function () {
                    $("#showOTPMsg").hide();
                    $('#delete_acount_request').validate();
                });
            </script>
        </body>
    </html>