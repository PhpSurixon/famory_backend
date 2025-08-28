@extends('layouts.advertiser-master', ['title' => 'Edit Partner Details', 'previous' => '/trustedpartners'])

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
        #map{
            height:100%;
        }
        body{
            overflow-y:hidden;
        }
        
        .img-ht {
            height: 270px;
        }
        
        .s-btn {
            margin-top:18px!important;
            border-radius:100px;
        }
        .sub-btn {
            border-radius:100px;
        }
    </style>

    <div class="row">
        <div class="col-xl-12 col-sm-12 mb-xl-0 mb-4 position-relative">
            <div id="preloaders" class="preloader"></div>
            <div class="card mt-4 mb-5" id="basic-info">
                <div class="card-header">
                    <h5>Edit Partner Details</h5>
                </div>
                <div class="alert alert-danger d-none p-2 m-2" id="errorMessage"></div>
                <form method="POST" enctype="multipart/form-data" id="updateTrustedPartner">
                    @csrf
                    <input type="hidden" name="id" value="{{ $partnerDetails->id }}">
                    <div class="card-body pt-0 pb-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Category</label>
                                    <select class="form-control @error('category') is-invalid @enderror" name='category'>
                                        <option value=''>--Please Select the Category--</option>
                                        <option value="Legal" {{ $partnerDetails->category == "Legal" ? 'selected':'' }}  >Legal</option>
                                        <option value="Funeral Home" {{ $partnerDetails->category == "Funeral Home" ? 'selected':'' }} >Funeral Home</option>
                                        <option value="Financial" {{ $partnerDetails->category == "Financial" ? 'selected':'' }}>Financial</option>
                                        <option value="Planning Services" {{ $partnerDetails->category == "Planning Services" ? 'selected':'' }}>Planning Services</option>
                                        <option value="Other" {{ $partnerDetails->category == "Other" ? 'selected':'' }}>Other</option>
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
                                        placeholder="Enter Company Name" name="company_name" value="{{ $partnerDetails->company_name }}" />
                                    @error('company_name')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 align-items-center">
                             
                             <div class="col-3">
                                <!--<div class="input-group input-group-static flex-column">-->
                                <!--    <label>State</label>-->
                                <!--    <input type="text" class="form-control @error('state') is-invalid @enderror"-->
                                <!--        placeholder="Enter State" name="state" value="{{ $partnerDetails->state }}" />-->
                                <!--    @error('state')-->
                                <!--        <span class="help-block invalid-feedback" role="alert">-->
                                <!--            <strong>{{ $message }}</strong>-->
                                <!--        </span>-->
                                <!--    @enderror-->
                                <!--</div>-->
                                <div class="input-group input-group-static flex-column">
                                    <label>State</label>
                                    <select class="form-control @error('state') is-invalid @enderror" id="state" name='state'>
                                        <option value='{{ $partnerDetails->state }}' selected>{{ $partnerDetails->state }}</option>
                                    </select>
                                    @error('state')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-3">
                                <!--<div class="input-group input-group-static flex-column">-->
                                <!--    <label>City</label>-->
                                <!--    <input type="text" class="form-control @error('city') is-invalid @enderror"-->
                                <!--        placeholder="Enter City" name="city" value="{{ $partnerDetails->city }}" />-->
                                <!--    @error('city')-->
                                <!--        <span class="help-block invalid-feedback" role="alert">-->
                                <!--            <strong>{{ $message }}</strong>-->
                                <!--        </span>-->
                                <!--    @enderror-->
                                <!--</div>-->
                                <div class="input-group input-group-static flex-column">
                                    <label>City</label>
                                    <select class="form-control @error('city') is-invalid @enderror" id="city" name='city'>
                                        <option value='{{ $partnerDetails->city }}' selected>{{ $partnerDetails->city }}</option>
                                    </select>
                                    @error('city')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-3">
                                <div class="input-group input-group-static flex-column">
                                    <label>Zip Code</label>
                                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror"
                                        placeholder="Enter Zip Code" name="zip_code" value="{{ $partnerDetails->zip_code }}" />
                                    @error('zip_code')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                           
                                <div class="col-3">
                                    <button type="button" id="search" class="btn btn-info w-100 mb-0 s-btn">Search</button>
                                </div>
                            
                            
                        </div>

                        <div class="row mt-4">
                             <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="Enter Phone" name="phone" value="{{ $partnerDetails->phone }}" />
                                    @error('phone')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                             <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Website Link</label>
                                    <input type="text" class="form-control @error('website') is-invalid @enderror"
                                        placeholder="Enter Website Link" name="website" value="{{ $partnerDetails->website }}" />
                                    @error('website')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                           
                        </div>
                        <div class="row mt-4">
                            <!--<div class="col-3">-->
                            <!--    <div class="input-group input-group-static flex-column">-->
                            <!--        <label>Current Logo</label>-->
                            <!--        <br>-->
                            <!--        <label  class="btn me-2 mb-1" tabindex="0">-->
                            <!--            <div class="d-flex align-items-start align-items-sm-center gap-4">-->
                            <!--                <img src="{{$partnerDetails->logo }}" alt="user-avatar" class="rounded" width="50%"-->
                            <!--                     style="object-fit:contain" />-->
                            <!--            </div>-->
                            <!--        </label>-->
                            <!--        <br />-->
                            <!--      </div>-->
                            <!--</div>-->
                            <!--<div class="col-3">-->
                            <!--    <div class="input-group input-group-static flex-column">-->
                            <!--        <label>Logo</label>-->
                                   
                            <!--        <br />-->
                                  
                            <!--        <div class="img-box img-ht m-0" id="img-box-banner">-->
                            <!--            <div class="input-box-container">-->
                            <!--                <i class="fa-solid fa-camera"></i>-->
                            <!--                <p>Add an image</p>-->
                            <!--            </div>-->
                            <!--            <input type="file" id="upload" class="account-file-input"-->
                            <!--                   accept="image/png, image/jpeg, image/jpg" name="logo"-->
                            <!--                   class=" @error('logo') is-invalid @enderror" />-->
                            <!--        </div>-->

                            <!--        <label for="upload" class="btn me-2 mb-1 d-none" tabindex="0">-->
                            <!--            <div class="d-flex align-items-start align-items-sm-center gap-4">-->
                            <!--                <img src="" alt="user-avatar" class="d-none rounded" width="50%"-->
                            <!--                     id="uploadedAvatar" style="object-fit:contain" />-->
                            <!--            </div>-->
                            <!--        </label>-->

                            <!--        <div id="logo-error-container"></div>-->
                                    
                            <!--    </div>-->
                            <!--</div>-->
                            
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
                                            <img src="{{$partnerDetails->logo }}" alt="user-avatar" class="d-none rounded" width="50%"
                                                 id="uploadedAvatar" style="object-fit:contain" />
                                        </div>
                                    </label>

                                    <div id="logo-error-container"></div>
                                </div>
                            </div>
                            
                            
                            
                            <div class="col-8" style="height:400px;">
                                <input type="hidden" name="lat" value="{{$partnerDetails->lat }}">
                                <input type="hidden" name="lng" value="{{$partnerDetails->lng }}">
                                <div id="map"></div>
                            </div>
                        </div>  
                            
                        <div class="row mt-4">
                            <button type="submit"
                                class="btn mb-0 btn-info sub-btn border-radius-section w-10 color-white mt-3 updatePartnerBtn"
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
            showcity();
            if ($('#uploadedAvatar').attr('src')) {
                // Hide the image box if there is an image
                $('#img-box-banner').hide();
                $('#uploadedAvatar').removeClass('d-none');
            }
            // $('#upload').on('change', function() {
            //     if ($(this).val()) {
            //         // Hide the image box once an image is selected
            //         $('#img-box-banner').hide();
                    
            //         // Optionally, display a preview of the selected image
            //         const file = this.files[0];
            //         if (file) {
            //             const reader = new FileReader();
            //             reader.onload = function(e) {
            //                 $('#uploadedAvatar').attr('src', e.target.result).removeClass('d-none');
            //             }
            //             reader.readAsDataURL(file);
            //         }
            //     }
            // });
            
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
            

            $('#updateTrustedPartner').on('submit', function(e) {
                e.preventDefault();
                $('.spinner-border').css("display","inline-block");
                $("#submitButton").attr("disabled",true);
                let formData = new FormData(this);
                
                $.ajax({
                    url: "{{ route('updatePartner') }}",
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
                           
                        }).then((value)=>{
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
                                if(field != 'category'){
                                     inputField = $('input[name="' + field + '"]');
                                }else{
                                     inputField = $(`select[name='${field}']`);
                                }
                                if (field === 'logo') {
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
                        // $('#preloader').hide();
                        $('.spinner-border').css("display","none");
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
                        title: "Oops!",
                        text: "Please Select State",
                        icon: "error",
                        button: {
                                text: "Ok",
                            },
                        })
                }
               
            })
            
            function showcity(){
                const stateName = $('#state').val();
                const cityName = $('#city').val();
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
                                        if(city == cityName){
                                            allOptions += `<option value='${city}' selected>${city}</option>`;
                                        }else{
                                            
                                            allOptions += `<option value='${city}'>${city}</option>`;
                                        }
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
                        title: "Oops!",
                        text: "Please Select State",
                        icon: "error",
                        button: {
                                text: "Ok",
                            },
                        })
                }
               
            }
        });
    </script>
    <script>
       $(document).ready(function() {
        let map;
        let marker;
        let geocoder;
        var lat = {{$partnerDetails->lat ?? -34.397}};
        var lng = {{$partnerDetails->lng ?? 150.644}};
        // Initialize map
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: { lat: lat, lng: lng }, // Initial center, can be any location
                mapTypeControl: false,
                scrollwheel: false,
            });
            geocoder = new google.maps.Geocoder();
            marker = new google.maps.Marker({
                map,
            });
            updateAddress();
        }
    
        // Event handler for search button click
        $("#search").click(function() {
           updateAddress();
        });
    
        function updateAddress(){
             let cityName = $("#city").val();
                let stateName = $("#state").val();
                let zipCode = $("input[name='zip_code']").val();
        
                if (cityName && stateName && zipCode) {
                    let fullAddress = `${cityName}, ${stateName} ${zipCode}`;
                    getCompanyAddress(fullAddress);
                } else {
                    $("#errorMessage").removeClass("d-none").text("Please select both city, state, and enter a zip code.");
                    setTimeout(function() {
                        $("#errorMessage").addClass("d-none");
                    }, 3000);
                }
        }
        // Function to get company address
        function getCompanyAddress(address) {
            $("#map").show(); // Show the map only when searching
            geocode({ address: address });
        }
    
        // Geocode function
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
                    // Do not reset map on error
                    $("#errorMessage").removeClass("d-none").text("Error: " + e.message);
                    setTimeout(function() {
                        $("#errorMessage").addClass("d-none");
                    }, 3000);
                });
        }
        initMap();
    
    });

    </script>
@endsection
