@extends('layouts.advertiser-master', ['title' => 'Add New Partner', 'previous' => '/trustedpartners'])

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
    .swal-footer {
        padding: 13px 197px ;
    }
        .modal-content {
            border: 3px solid #2174f9;
            background: ghostwhite;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
        }

        td {
            text-align: right;
        }

        th,
        td {
            padding: 8px;
            /*border: 1px solid #ddd;*/
        }

        hr {
            margin: 10px 0;
            border: 0;
            /*border-top: 1px solid #ddd; */
        }

        .ad-new {
            input[type="radio"] {
                width: 20px;
                height: 20px;
            }

            label {
                font-size: 18px;
            }
        }

        .card-item {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .card-item input[type="radio"] {
            margin-bottom: 5px;
        }

        .spinner-border {
            display: none;
            --bs-spinner-width: 1rem !important;
            --bs-spinner-height: 1rem !important;
        }

        .btn-loading .spinner-border {
            display: inline-block;
        }
        #preloader{
            font-size: 12px;
            margin-left: 7px;
        }
        
         #map {
            height: 100%;
        }
        
        .s-round {
            border-radius:100px!important;
        }
        
        .img-ht {
            height: 354px!important;
        }
    </style>

    <div class="row">
        <div class="col-xl-12 col-sm-12 mb-xl-0 mb-4 position-relative px-xl-4">
            <div id="preloaders" class="preloader"></div>
            <div class="card mt-0 mb-5" id="basic-info" style="border-radius: 10px;">
                <div class="card-header">
                    <h5>New Partner Details</h5>
                </div>
                <div class="alert alert-danger d-none p-2 m-2" id="errorMessage"></div>
                <form method="POST" enctype="multipart/form-data" id="storePartner">
                    @csrf
                    <div class="card-body pt-0 pb-4">
                        <div class="row">
                            
                            <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Category</label>
                                    <select class="form-control @error('category') is-invalid @enderror" name='category'>
                                        <option value=''>--Please Select the Category--</option>
                                        <option value="Legal" {{ old('category') == "Legal" ? 'selected':'' }} >Legal</option>
                                        <option value="Funeral Home" {{ old('category') == "Funeral Home" ? 'selected':'' }} >Funeral Home</option>
                                        <option value="Financial" {{ old('category') == "Financial" ? 'selected':'' }}>Financial</option>
                                        <option value="Planning Services" {{ old('category') == "Planning Services" ? 'selected':'' }}>Planning Services</option>
                                        <option value="Other" {{ old('category') == "Other" ? 'selected':'' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            
                            <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Company Name</label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                        placeholder="Enter Company Name" name="company_name" value="{{ old('company_name') }}" />
                                    @error('company_name')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                             <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Phone</label>
                                    <input type="number" class="form-control @error('phone') is-invalid @enderror" maxlength="10"
                                        placeholder="Enter Phone" name="phone" value="{{ old('phone') }}" min="0" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" />
                                    @error('phone')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                             <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Website Link</label>
                                    <input type="text" class="form-control @error('website') is-invalid @enderror"
                                        placeholder="Enter Website Link" name="website" value="{{ old('website') }}" />
                                    @error('website')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                           
                        </div>
                        <div class="row mt-4 align-items-center">
                             
                             <div class="col-xl-3 col-md-6 col-12">
                                <!--<div class="input-group input-group-static flex-column">-->
                                <!--    <label>State</label>-->
                                <!--    <input type="text" class="form-control @error('state') is-invalid @enderror"-->
                                <!--        placeholder="Enter State" name="state" value="{{ old('state') }}"  />-->
                                <!--    @error('state')-->
                                <!--        <span class="help-block invalid-feedback" role="alert">-->
                                <!--            <strong>{{ $message }}</strong>-->
                                <!--        </span>-->
                                <!--    @enderror-->
                                <!--</div>-->
                                
                                <div class="input-group input-group-static flex-column">
                                    <label>State</label>
                                    <select class="form-control @error('state') is-invalid @enderror" id="state" name='state'>
                                        <option selected disabled value=''>--Please Select State--</option>
                                    </select>
                                    @error('state')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                <!--<div class="input-group input-group-static flex-column">-->
                                <!--    <label>City</label>-->
                                <!--    <input type="text" class="form-control @error('city') is-invalid @enderror"-->
                                <!--        placeholder="Enter City" name="city" value="{{ old('city') }}"  />-->
                                <!--    @error('city')-->
                                <!--        <span class="help-block invalid-feedback" role="alert">-->
                                <!--            <strong>{{ $message }}</strong>-->
                                <!--        </span>-->
                                <!--    @enderror-->
                                <!--</div>-->
                                
                                 <div class="input-group input-group-static flex-column">
                                    <label>City</label>
                                    <select class="form-control @error('city') is-invalid @enderror" id="city" name='city'>
                                        <option selected disabled value=''>--Please Select City--</option>
                                    </select>
                                    @error('city')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                <div class="input-group input-group-static flex-column">
                                    <label>Zip Code</label>
                                    <input type="number" class="form-control @error('zip_code') is-invalid @enderror"
                                        placeholder="Enter Zip Code" name="zip_code" value="{{ old('zip_code') }}" min="0" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" />
                                    @error('zip_code')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                
                            <button type="button" id="search" class="btn btn-info w-100 my-0 border-radius-section color-white s-round">Search</button>
                            </div>
                        </div>

                        
                        <div class="row mt-4">
                            <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Logo</label>
                                   <!--<span class="text-danger">(Dimensions: 300px X 300px)</span>-->
                                    <br />
                                  
                                    <div class="img-box img-ht" id="img-box-banner">
                                        <div class="input-box-container">
                                            <i class="fa-solid fa-camera"></i>
                                            <p>Add an image</p>
                                        </div>
                                        <input type="file" id="upload" class="account-file-input"
                                               accept="image/png, image/jpeg, image/jpg" name="logo"
                                               class=" @error('logo') is-invalid @enderror" />
                                        
                                    </div>

                                    <label for="upload" class="btn me-2 mb-1" tabindex="0">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="" alt="user-avatar" class="d-none rounded" width="50%"
                                                 id="uploadedAvatar" style="object-fit:contain" />
                                        </div>
                                    </label>

                                    <div id="logo-error-container"></div>
                                </div>
                            </div>
                                
                            
                            <div class="col-8" style="height:400px;">
                                <input type="hidden" name="lat" >
                                <input type="hidden" name="lng" >
                                <div id="map"></div>   
                            </div>

                            <button type="submit"
                                class="btn mb-0 bg-info border-radius-section w-10 color-white mt-3 storePartnerBtn"
                                id="submitButton"><span id="submitValue">Submit</span>
                                <div class="spinner-border text-light" role="status" id="preloader">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{config('app.map_key')}}&callback=initMap&v=weekly&solution_channel=GMP_CCS_geocodingservice_v2" defer></script>
    <script type="text/javascript">
        $(document).ready(function() {
            
            $('#upload').on('change', function() {
                if ($(this).val()) {
                    // Hide the image box once an image is selected
                    $('#img-box-banner').hide();
                    
                    // Optionally, display a preview of the selected image
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#uploadedAvatar').attr('src', e.target.result).removeClass('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });
            
            $('#uploadScreenImage').on('change', function() {
                if ($(this).val()) {
                    $('#img-box-full').hide();
                    
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#fullScreenImage').attr('src', e.target.result).removeClass('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });


            $('#storePartner').on('submit', function(e) {
                e.preventDefault();
                $('.spinner-border').css("display","inline-block");
                $("#submitButton").attr("disabled",true);
                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('storePartner') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        
                         swal({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            button: {
                                text: "Ok",
                             },
                            timer:5000,
                        }).then((value)=>{
                            // console.log(value);
                            window.location.href = "{{ route('trustedpartners') }}";
                        });

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            $("#submitButton").removeAttr("disabled"); 
                            let errors = xhr.responseJSON.errors;
                            $('.invalid-feedback').remove();
                            $('input, select').removeClass('is-invalid');
                            for (let field in errors) {

                                let inputField = '';
                                if(field != 'category' && field != 'state' && field != 'city'){
                                     inputField = $('input[name="' + field + '"]');
                                }else{
                                     inputField = $(`select[name='${field}']`);
                                }
                                
                                
                                if (field === 'full_screen_image' || field === 'banner_image' || field === 'logo') {
                                    // Handle image-specific error
                                    $("#" + field + "-error-container").html(
                                        '<span class="invalid-feedback d-block" role="alert"><strong>' +
                                        errors[field][0] + '</strong></span>'
                                    );
                                } else {
                                inputField.addClass('is-invalid');
                                inputField.after(
                                    '<span class="invalid-feedback d-block" role="alert"><strong>' +
                                    errors[field][0] + '</strong></span>');
                                
                                }
                            }
                        } else {
                            // alert('Something went wrong, please try again.');
                        }
                        // $('#submitButton').show();
                        $('.spinner-border').css("display","none");
                         $("#submitButton").removeAttr("disabled");
                        // $('#submitValue').css("display","block");
                    }
                });
            });
            
            const stateSelect = document.getElementById('state');
            $.ajax({
                url:"https://countriesnow.space/api/v0.1/countries/states",
                method:"POST",
                data:{
                    "country": "United States"
                },
                success:function(response){
                    if(!response.error){
                        const states = response.data.states;
                        states.map((state)=>{
                            stateSelect.innerHTML += `<option value='${state.name}'>${state.name}</option>`
                        });
                    }else{
                        
                    }
                }
            });



            stateSelect.addEventListener('change',function(e){
                const stateName = this.value;
                if(stateName != ''){
                    const citySelect = document.getElementById('city');
                    $.ajax({
                        url:"https://countriesnow.space/api/v0.1/countries/state/cities",
                        method:"POST",
                        data:{
                            "country": "United States",
                            "state": stateName
                        },
                        success:function(response){
                            if(!response.error){
                                let allOptions = '';
                                const cities = response.data;
                                if(cities.length > 0){
                                    cities.map((city)=>{
                                    allOptions += `<option value='${city}'>${city}</option>`;
                                    });
                                }else{
                                    allOptions += `<option value=''>No City Found</option>`;
                                }
                                
                                citySelect.innerHTML = allOptions;
                            }else{
                                alert('state not found');
                            }
                        }
                    });
                } else{
                     swal({
                        text: "Oops!",
                        title: "Please Select State",
                        icon: "error",
                        button: {
                                text: "Ok",
                            },
                        })
                }
               
            })

        });
    </script>
    
    
    <script>
    let map;
    let marker;
    let geocoder;

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: { lat: -34.397, lng: 150.644 }, // Initial center, can be any location
            mapTypeControl: false,
            scrollwheel: false,
        });
        geocoder = new google.maps.Geocoder();
        marker = new google.maps.Marker({
            map,
        });
    }

    $(document).ready(function() {
        $("#search").click(function() {
            let cityName = $("#city").val();
            let stateName = $("#state").val();
            let zipCode = $("input[name='zip_code']").val();
    
            if (cityName && stateName && zipCode) {
                let fullAddress = `${cityName}, ${stateName} ${zipCode}`;
                getCompanyAddress(fullAddress);
            } else {
                $("#errorMessage").removeClass("d-none").text("Please select both city, state and enter a zip code.");
                setTimeout(function() {
                    $("#errorMessage").addClass("d-none");
                }, 3000);
            }
        });
        
        function getCompanyAddress(address) {
            $("#map").show(); // Show the map only when searching
            geocode({ address: address });
        }
    
        function geocode(request) {
            geocoder.geocode(request)
                .then((result) => {
                    const { results } = result;
                    if (results && results.length > 0) {
                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                        marker.setMap(map);
                        $("input[name='lat']").val(results[0].geometry.location.lat());
                        $("input[name='lng']").val(results[0].geometry.location.lng());
                    } else {
                        alert("No results found for the given address.");
                    }
                })
                .catch((e) => {
                    alert("Error: " + e.message);
                });
        }
    
        window.initMap = initMap;
    });
    </script>
    
    
@endsection
