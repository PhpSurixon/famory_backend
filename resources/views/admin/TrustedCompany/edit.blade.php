@extends('layouts.admin-master', ['title' => 'Trusted Partner'])

@section('content')
<style>
    .search-btn-c {
     background-color: #1550AE;
     color:white;
    }
    
    .search-btn-c:hover {
       color:white; 
    }

    @media screen and (max-width: 767px){
        .form-control{
            margin-bottom: 15px;
        }
        .mb-3{
            margin-bottom: 0px !important;
        }

    }
</style>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Trusted Partner</h5>
                    </div>
                    <div class="alert alert-danger d-none p-2 m-2" id="errorMessage"></div>
                    <div class="card-body">
                         <form action="{{ route('update-trusted-company',$data->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-company_name">Company Name</label>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                            value="{{ $data->company_name}}" autocomplete="company_name" autofocus id="basic-default-company_name"
                                            placeholder="Enter Company Name..." name="company_name" />
                                        @error('company_name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-tag-type">Type of Tag</label>
                                        <select class="form-control @error('category') is-invalid @enderror" id="basic-default-tag-type" name="category">
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="Legal" {{ $data->category == "Legal" ? 'selected':'' }} >Legal</option>
                                            <option value="Funeral Home" {{ $data->category == "Funeral Home" ? 'selected':'' }} >Funeral Home</option>
                                            <option value="Financial" {{ $data->category== "Financial" ? 'selected':'' }}>Financial</option>
                                            <option value="Planning Services" {{ $data->category == "Planning Services" ? 'selected':'' }}>Planning Services</option>
                                            <option value="Other" {{ $data->category == "Other" ? 'selected':'' }}>Other</option>
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
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="basic-default-count">Phone</label>
                                        <input type="number" id="basic-default-count" name="phone" value="{{ $data->phone}}"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            placeholder="Enter phone ..." />
                                        @error('phone')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="basic-default-count">Website Link</label>
                                        <input type="text" id="basic-default-count" name="website" value="{{ $data->website}}"
                                            class="form-control @error('website') is-invalid @enderror"
                                            placeholder="Enter website ..."/>
                                        @error('website')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="">Logo</label>
                                        <input type="file" id="basic-default-image" name="logo"
                                            class="form-control @error('logo') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('logo')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        @if ($data->logo)
                                            <img id="image-preview" src="{{ $data->logo }}" alt="Image Preview" class="mt-2"  style="display:block; width: 100px; height: 100px; object-fit: cover; border-radius: 50%; float: left; margin-right: 10px;">
                                        @else
                                            <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="basic-default-price">State</label>
                                        <!--<input type="text" class="form-control @error('state') is-invalid @enderror"-->
                                        <!--    value="{{ $data->state}}" autocomplete="state" autofocus id="basic-default-state"-->
                                        <!--    placeholder="Enter state..." name="state" />-->
                                        <select class="form-control @error('state') is-invalid @enderror" id="state" name="state">
                                            <option value="{{$data->state}}">{{ $data->state }}</option>
                                        </select>
                                        @error('state')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="basic-default-city">City</label>
                                        <!--<input type="text" class="form-control @error('city') is-invalid @enderror"-->
                                        <!--    value="{{ $data->city}}" autocomplete="city" autofocus id="basic-default-city"-->
                                        <!--    placeholder="Enter price..." name="city" />-->
                                        <select class="form-control @error('city') is-invalid @enderror" id="city" name='city'>
                                            <option value='{{ $data->city }}' selected>{{ $data->city }}</option>
                                        </select>
                                        @error('city')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
                                        <label class="form-label" for="basic-default-count">Zip Code </label>
                                        <input type="text" id="basic-default-count" name="zip_code" value="{{ $data->zip_code}}"
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
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-12">
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
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>-->
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
        
    $(document).ready(function() {
        showcity();
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
               
            });
            
            
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
                        text: "Oops!",
                        title: "Please Select State",
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
    $(document).ready(function(){
        let companyAddress = '';
        let cityName = $("select[name='city']").val();
        let stateName = $("select[name='state']").val();
        let zipCode = $("input[name='zip_code']").val();
        getCompanyAddress(cityName,stateName,zipCode)
    });
        let companyAddress = '';
        let cityName = $("select[name='city']").val();
        let stateName = $("select[name='state']").val();
        let zipCode = $("input[name='zip_code']").val();
        
        
        
        let map;
        let marker;
        let geocoder;
        let responseDiv;
        let response;
        
        // $("select[name='city']").focusout(function(){
        //     cityName=($(this).val() == '')? "Delhi":$(this).val();;
        //     getCompanyAddress(cityName,stateName,zipCode)
        // });
        // $("select[name='state']").focusout(function(){
        //     stateName=$(this).val();
        //     getCompanyAddress(cityName,stateName,zipCode)
        // });
        // $("input[name='zip_code']").focusout(function(){
        //     zipCode=$(this).val();
        //     getCompanyAddress(cityName,stateName,zipCode)
        // });
        
        $("#search").click(function() {
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
        });
        

        function getCompanyAddress(cityName,stateName,zipCode){
            $("#map").show();
            companyAddress = `${cityName} ${stateName} ${zipCode}`;
            geocode({ address: companyAddress });
        }        
        
        
        
        
        function initMap() {
            const myLatlng = { lat: {{ $data->lat != '' ? $data->lat : -34.397  }}, lng: {{ $data->lng != '' ? $data->lng : 150.644 }}  };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: myLatlng,
                mapTypeControl: false,
                scrollwheel: false,
            });
        
            marker = new google.maps.Marker({
                position: myLatlng,
                map,
            });
            
            geocoder = new google.maps.Geocoder();
            // clear();
        }

        function clear() {
            marker.setMap(null);
        }

        function geocode(request) {
            clear();
            geocoder
                .geocode(request)
                .then((result) => {
                    const { results } = result;

                    map.setCenter(results[0].geometry.location);
                    marker.setPosition(results[0].geometry.location);
                    marker.setMap(map);
                    response = JSON.stringify(result, null, 2);
                    $("input[name='lat']").val(results[0].geometry.location.lat());
                    $("input[name='lng']").val(results[0].geometry.location.lng());
                    return results;
                })
                .catch((e) => {
                    // alert("Geocode was ");
                    $("input[name='lat']").val("");
                    $("input[name='lng']").val("");
                    $("#errorMessage").removeClass("d-none");
                    $("#errorMessage").html("Please Enter Valid City,State Or Zip Code")
                });
        }

        window.initMap = initMap;
    </script>


@endsection