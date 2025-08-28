@extends('layouts.admin-master', ['title' => 'Trusted Partner'])

<style>
    .search-btn-c {
        background-color: #1550AE!important;
        color: white!important;
    }
</style>
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Trusted Partner</h5>
                    </div>
                    <div class="alert alert-danger d-none p-2 m-2" id="errorMessage"></div>
                    <div class="card-body">
                        <form action="{{ route('store-trusted-company') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-company_name">Company Name</label>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                            value="{{ old('company_name') }}" autocomplete="company_name" autofocus id="basic-default-company_name"
                                            placeholder="Enter Company Name..." name="company_name" />
                                        @error('company_name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-tag-type">Type of Tag</label>
                                        <select class="form-control @error('category') is-invalid @enderror" 
                                                id="basic-default-tag-type"
                                                name="category">
                                            <option value="" disabled selected>Select Type</option>
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
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label" for="basic-default-count">Phone</label>
                                        <input type="number" id="basic-default-count" name="phone" value="{{ old('phone') }}" 
                                            class="form-control @error('phone') is-invalid @enderror"
                                            placeholder="Enter phone ..." />
                                        @error('phone')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="basic-default-count">Website Link</label>
                                        <input type="text" id="basic-default-count" name="website" value="{{ old('website') }}" 
                                            class="form-control @error('website') is-invalid @enderror"
                                            placeholder="Enter website ..."/>
                                        @error('website')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="">Logo</label>
                                        <input type="file" id="basic-default-image" name="logo" value="{{ old('logo') }}" 
                                            class="form-control @error('logo') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('logo')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label" for="basic-default-price">State</label>
                                        <!--<input type="text" class="form-control @error('state') is-invalid @enderror"-->
                                        <!--    value="{{ old('state') }}" autocomplete="state" autofocus id="basic-default-state"-->
                                        <!--    placeholder="Enter state..." name="state" />-->
                                        <select class="form-control @error('state') is-invalid @enderror" 
                                                id="state"
                                                name="state">
                                            <option value="" disabled selected>Select State</option>
                                        </select>
                                        <input type="hidden" id='oldSelectedState' value="{{ old('state') }}">
                                        @error('state')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="basic-default-city">City</label>
                                        <!--<input type="text" class="form-control @error('city') is-invalid @enderror"-->
                                        <!--    value="{{ old('city') }}" autocomplete="city" autofocus id="basic-default-city"-->
                                        <!--    placeholder="Enter price..." name="city" />-->
                                        <select class="form-control @error('city') is-invalid @enderror" id="city" name="city">
                                            <option value="" disabled selected>Select City</option>
                                        </select>
                                        <input type="hidden" id='oldSelectedCity' value="{{ old('city') }}">
                                        @error('city')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="basic-default-count">Zip Code </label>
                                        <input type="text" id="basic-default-count" name="zip_code" value="{{ old('zip_code') }}" 
                                            class="form-control @error('zip_code') is-invalid @enderror"
                                            placeholder="Enter zip code ..." />
                                        @error('zip_code')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            
                            
                            <div class="mb-4">
                                <div class="row">
                                    <div class="col-4">
                                        <button type="button" id="search" class="btn w-25 mb-0 search-btn-c">Search</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                     <div class="col-12" style="height:200px;">
                                        <input type="hidden" name="lat" >
                                        <input type="hidden" name="lng" >
                                        <div id="map"></div>   
                                    </div>
                                </div>
                            </div>
                            <br/>
                             <div class="button-container">
                            <a href="{{ route('trusted-company') }}" class="btn btn-primary">Back</a>
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
    #map {
        height: 100%;
    }
   </style>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{config('app.map_key')}}&callback=initMap&v=weekly&solution_channel=GMP_CCS_geocodingservice_v2" defer></script>

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
     <script>
     
        const stateSelect = document.getElementById('state');
        const oldState = document.getElementById('oldSelectedState');
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
                        stateSelect.innerHTML += `<option value='${state.name}' ${oldState.value == state.name ? 'selected' : ''}>${state.name}</option>`
                    });
                }else{
                        
                }
            }
        });
        
        if(oldState.value !== ''){
            const citySelect = document.getElementById('city');
            const oldCitySelected = document.getElementById('oldSelectedCity');
                    
            $.ajax({
                url:"https://countriesnow.space/api/v0.1/countries/state/cities",
                method:"POST",
                data:{
                    "country": "United States",
                    "state": oldState.value
                },
                success:function(response){
                    if(!response.error){
                        let allOptions = '';
                        const cities = response.data;
                        if(cities.length > 0){
                            cities.map((city)=>{
                            allOptions += `<option value='${city}'  ${oldCitySelected.value == city ? 'selected' : ''}>${city}</option>`;
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
        }
     
        stateSelect.addEventListener('change',function(e){
                let stateName = this.value;;
                
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
                                    allOptions += `<option value='${city}' >${city}</option>`;
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
               
            });
        
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

    let lastAddress = localStorage.getItem('lastAddress') || ""; // Retrieve last address from local storage

    $(document).ready(function() {
        if (lastAddress) {
            getCompanyAddress(lastAddress); // Load last address on page load
        }
    
        $("#search").click(function() {
            let cityName = $("#city").val();
            let stateName = $("#state").val();
            let zipCode = $("input[name='zip_code']").val();
    
            if (cityName && stateName && zipCode) {
                let fullAddress = `${cityName}, ${stateName} ${zipCode}`;
                getCompanyAddress(fullAddress);
                lastAddress = fullAddress; // Update the lastAddress variable
                localStorage.setItem('lastAddress', lastAddress); // Store in local storage
            } else {
                $("#errorMessage").removeClass("d-none").text("Please select both city, state and enter a zip code.");
                setTimeout(function() {
                    $("#errorMessage").addClass("d-none");
                }, 3000);
            }
        });
    
        $("form").submit(function(e) {
            let cityName = $("#city").val();
            let stateName = $("#state").val();
            let zipCode = $("input[name='zip_code']").val();
    
            // Check if fields are empty
            if (!cityName || !stateName || !zipCode) {
                e.preventDefault(); // Prevent form submission
                $("#errorMessage").removeClass("d-none").text("Please fill in all required fields before submitting.");
                setTimeout(function() {
                    $("#errorMessage").addClass("d-none");
                }, 3000);
            }else {
            // Clear the lastAddress from local storage upon successful submission
                localStorage.removeItem('lastAddress');
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
    
        window.initMap = initMap; // Ensure initMap is accessible globally
    });

    </script>
@endsection