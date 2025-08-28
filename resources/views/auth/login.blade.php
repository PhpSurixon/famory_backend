<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/x-icon" href="https://admin.famoryapp.com/assets/img/favicon/favicon.png" />
  <title>
    Famory
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css"
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
   <link href="{{ asset('/advertiser/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('/advertiser/css/nucleo-svg.css') }}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"  />

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('/assets/css/login-signup.css?v=3.1.0') }}" rel="stylesheet" />
  <!-- Nepcha Analytics (nepcha.com) -->
  <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
</head>

<body class="bg-gray-200">
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-color: #fff;">
      <span class="bg-gradient-dark"></span>
      <div class="container my-4">
        <div class="row">
          <div class="col-lg-8 col-md-8 col-12 mx-auto text-center">
            <img src="../assets/img/logo-ct(1).png" class="navbar-brand-img mb-3" alt="main_logo">
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <ul class="tab-group">
              <li class="tab active"><a href="#login">Sign In</a></li>
              <li class="tab"><a href="#signup">Sign Up</a></li>
            </ul>
            <!-- <div> -->


              <div class="tab-content">
                <div id="login" class="form-signin">

                  <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="field-wrap">
                        <label>
                            Email Address<span class="req">*</span>
                        </label>
                        <input type="email" class="@error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email or username" autofocus />
                        @error('email')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                         @enderror
                    </div>

                    <div class="field-wrap">
                      <label>
                        Password<span class="req">*</span>
                      </label>
                      <input type="password" class="@error('password') is-invalid @enderror" name="password"
                                placeholder="Enter your password"
                                aria-describedby="password" autocomplete="false" />
                        @error('password')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="forgot">
                      <div class="remember-be d-flex gap-2">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} /> Remember me
                      </div>
                      <!--<a href="#">Forgot Password?</a>-->
                    </div>
                    
                    <input type="submit" class="button button-block submit-button" value="Sign In">

                  </form>

                </div>

                <div id="signup" class="form-signup">

                  <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="top-row">
                      <div class="field-wrap">
                        <label>
                          Your Name<span class="req">*</span>
                        </label>
                        <input type="text" class="@error('name') is-invalid @enderror" id="name" name="name"
                                placeholder="Enter Your Name" autofocus value="{{ old('name') }}" />
                        @error('name')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                      </div>

                      <div class="field-wrap">
                        <label>
                          Company Name<span class="req">*</span>
                        </label>
                        <input type="text" class="@error('company_name') is-invalid @enderror" id="company_name" name="company_name"
                                                    placeholder="Enter Company Name" value="{{ old('company_name') }}" autofocus />
                        @error('company_name')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                      </div>
                    </div>

                    <div class="top-row">
                      <div class="field-wrap">
                        <label>
                          Company Address<span class="req">*</span>
                        </label>
                        <input type="text" class="@error('company_address') is-invalid @enderror" id="company_address" name="company_address"
                             placeholder="Enter your company_address or username" autofocus value="{{ old('company_address') }}" />
                        @error('company_address')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                      </div>

                      <div class="field-wrap">
                        <label>
                          Email Address<span class="req">*</span>
                        </label>
                        <input type="email" class="@error('email_address') is-invalid @enderror" name="email_address" placeholder="Enter Email Address"
                                                    autofocus value="{{ old('email_address') }}" />
                        @error('email_address')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                      </div>
                    </div>

                    <div class="top-row">
                      <div class="field-wrap">
                        <label>
                          Password<span class="req">*</span>
                        </label>
                        <input type="password"  class="@error('new_password') is-invalid @enderror" name="new_password" placeholder="Enter Password"
                                aria-describedby="password" autocomplete="false" />
                                                   
                        @error('new_password')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                      </div>

                        <div class="field-wrap">
                            <label>
                              Confirm Password<span class="req">*</span>
                            </label>
                            <input type="password" name="new_password_confirmation" placeholder="Enter Confirm Password" />
                        </div>
                    
                    </div>

                    <div class="field-wrap">
                      <label>
                        <!--Company Logo<span class="req">*</span>-->
                        Company Logo
                      </label>
                        @error('logo')
                            <span class="text-danger" role="alert" style="font-size: 14px;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        
                    </div>
                    <!-- Image Preview Element -->
                    <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 180px; height: 180px; object-fit: contain; cursor: pointer; margin-bottom:10px;">
                    
                    <!-- Hidden File Input Element -->
                    <input type="file" id="file-input" name="logo" style="display:none;" onchange="previewImage(event)">


                    <div class="box-img">
                        <div class="upload">
                            <label class="upload-area">
                                <input type="file" id="logo" name="logo" autofocus  onchange="previewImage(event)" />
                                <span class="upload-button">
                                    <i class="fas fa-camera"></i>
                                </span>
                            </label>
                            <p id="logoText">Tap to add logo</p>
                        </div>
                        <label><span class="req" style="color:#e46b6b;">The uploaded image should be 180*180</span></label>
                    </div>
                    
                    
                    <div class="d-flex">
                        <input type="checkbox" name="termConditions" {{ old('termConditions') ? 'checked' : '' }} class="@error('termConditions') is-invalid @enderror" style="max-width:20px;margin:2px 0px;" /> 
                        <label class="form-check-label text-left" for="flexCheckDefault">
                        I agree to the <a href="https://admin.famoryapp.com/info-pages/terms-and-conditions" target="_blank" class="text-info" >Terms of Service</a>  and <a href="https://admin.famoryapp.com/info-pages/privacy-policy" target="_blank" class="text-info">Privacy Policy</a>, and consent to receive marketing emails and other communications from Famory.
                        </label>
                        <br/>
                    </div>
                    @error('termConditions')
                        <span class="text-danger" role="alert" style="font-size: 14px;">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                      
                     
                    

                    <button type="submit" class="button button-block2 submit-button">Submit</button>

                  </form>

                </div>
              </div>
              
              <!-- tab-content -->

            <!-- </div> /form -->
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script>

    jQuery('.form').find('input, textarea').on('keyup blur focus', function (e) {

      var $this = jQuery(this),
        label = $this.prev('label');

      if (e.type === 'keyup') {
        if ($this.val() === '') {
          label.removeClass('active highlight');
        } else {
          label.addClass('active highlight');
        }
      } else if (e.type === 'blur') {
        if ($this.val() === '') {
          label.removeClass('active highlight');
        } else {
          label.removeClass('highlight');
        }
      } else if (e.type === 'focus') {

        if ($this.val() === '') {
          label.removeClass('highlight');
        }
        else if ($this.val() !== '') {
          label.addClass('highlight');
        }
      }

    });

    jQuery('.tab a').on('click', function (e) {

      e.preventDefault();

      jQuery(this).parent().addClass('active');
      jQuery(this).parent().siblings().removeClass('active');

      target = $(this).attr('href');

      jQuery('.tab-content > div').not(target).hide();

      jQuery(target).fadeIn(600);

    });
  </script>
  
  <script>
        document.addEventListener('DOMContentLoaded', function() {
            var hash = window.location.hash;

            if (hash === '#login') {
                document.getElementById('login').focus();
            } else if (hash === '#signup') {
                document.getElementById('signup').focus();
            }
            
            @if(session('tab') == 'signup')
                jQuery('.tab').removeClass('active');
                jQuery('.tab a[href="#signup"]').parent().addClass('active');
                jQuery('.tab-content > div').hide();
                jQuery('#signup').fadeIn(600);
            @endif
        });
    </script>
    <script>
        // $(document).ready(function() {
            // $('#logo').on('change', function() {
            //     var fileName = $(this).val().split('\\').pop();  // Get file name
            //     if (fileName) {
            //         console.log("hello===>",fileName);
            //         $('#logoText').text(fileName).css({
            //             'color': '#154fb0',
            //             'font-weight': '400',
            //             'font-size': '18px'
            //         });
            //     } else {
            //         $('#logoText').text('Tap to add logo');  // Reset text
            //     }
            // });
        // });
            
        $(document).ready(function() {
            // Handle image click to trigger file input click
            $('#image-preview').click(function() {
                $('#file-input').click();
            });
        
            // Handle file input change event to preview the selected image
            $('#file-input').change(function(event) {
                previewImage(event);
            });
        });
        
        // Function to preview the selected image
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
                $('.box-img').hide(); // Ensure '.box-img' element is hidden
            };
            reader.readAsDataURL(event.target.files[0]);
        }
            
        
        document.addEventListener('DOMContentLoaded', function() {
        // Add event listener to the signup form on submit
        const form = document.querySelector('form[action="{{ route('register') }}"]');
        const termsCheckbox = document.querySelector('input[name="termConditions"]');
        const agreementText = termsCheckbox.closest('.d-flex'); // Use closest to get the wrapper div
        const errorContainer = document.createElement('div'); // Create a container for the error message

        // Add the error container right after the agreement text
        errorContainer.className = 'text-danger term-condition-error';
        errorContainer.style.fontSize = '14px';
        errorContainer.style.display = 'none'; // Initially hidden
        errorContainer.style.marginTop = '8px'; // Add some margin for spacing
        errorContainer.innerHTML = '<strong>You must agree to the Terms of Service and Privacy Policy.</strong>';
        agreementText.insertAdjacentElement('afterend', errorContainer); // Insert error message after the agreement text wrapper

        form.addEventListener('submit', function(e) {
            // Check if the checkbox is not checked
            if (!termsCheckbox.checked) {
                // Prevent form submission
                e.preventDefault();
                
                // Show the error message
                errorContainer.style.display = 'block';
            } else {
                // Hide the error message if checkbox is checked
                errorContainer.style.display = 'none';
            }
        });

        // Optional: Hide error message when checkbox is checked manually
        termsCheckbox.addEventListener('change', function() {
            if (termsCheckbox.checked) {
                errorContainer.style.display = 'none';
            }
        });
    });
            
        
    </script>

    
</body>

</html>