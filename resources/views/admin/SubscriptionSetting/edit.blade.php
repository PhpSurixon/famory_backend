@extends('layouts.admin-master', ['title' => 'Subscription Setting'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Subscription Setting</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('update-subscription-setting',$data->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-title">Title</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            value="{{$data->title}}" autocomplete="title" autofocus id="basic-default-name"
                                            placeholder="Enter Title..." name="title" />
                                        @error('title')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-plans">Plans</label>
                                        <input type="text" class="form-control @error('plans') is-invalid @enderror"
                                            value="{{$data->plans}}" autocomplete="plans" autofocus id="basic-default-name"
                                            placeholder="Enter plans..." name="plans" />
                                        @error('plans')
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
                                        <label class="form-label" for="basic-default-plan_id_ios">Plan ID (iOS)</label>
                                        <input type="text" class="form-control @error('plan_id_ios') is-invalid @enderror"
                                            value="{{$data->plan_id_ios}}" autocomplete="plan_id_ios" autofocus id="basic-default-price"
                                            placeholder="Enter Plan ID (IOS)..." name="plan_id_ios" />
                                        @error('plan_id_ios')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-plan_id_android">Plan ID (Android)</label>
                                        <input type="text" class="form-control @error('plan_id_android') is-invalid @enderror"
                                            value="{{$data->plan_id_android}}" autocomplete="plan_id_android" autofocus id="basic-default-price"
                                            placeholder="Enter Plan Id Android..." name="plan_id_android" />
                                        @error('plan_id_android')
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
                                        <label class="form-label" for="basic-default-benefits">Benefits</label>
                                        <!--<input type="text" class="form-control @error('benefits') is-invalid @enderror"-->
                                        <!--    value="{{$data->benefits}}" autocomplete="benefits" autofocus id="basic-default-name"-->
                                        <!--    placeholder="Enter Benefits..." name="benefits" />-->
                                        <textarea data-rule-required="true" data-msg-required="Benefits is required" class="form-control" placeholder=" Enter Benefits" id="benefits" name="benefits" spellcheck="false" style="height:content-fit;">{{$data->benefits}}</textarea>
                                        @error('benefits')
                                            <span class="help-block invalid-feedback" role="alert" >
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                </div>
                            </div>
                            <br/>
                        <div class="button-container">
                            <a href="{{ route('subscription-setting') }}" class="btn btn-primary">Back</a>
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