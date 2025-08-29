@extends('layouts.advertiser-master', ['title' => 'Edit Ad','previous'=> "/selected-ad/$ad->id"])

@section('content')
<style>
    .btn.btn-primary{
            box-shadow: 0 3px 3px 0 rgb(40 99 219 / 16%), 0 3px 1px -2px rgb(40 99 219 / 16%), 0 1px 5px 0 rgb(40 99 219 / 16%);
            background: #2863db;
            border: 1px solid #2863db;
            color: #ffffff !important;
    }
    .btn.btn-primary span{
        color: #ffffff !important;
    }
</style>
<div class="row">
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-xl-4 position-relative">
            <div id="preloaders" class="preloader"></div>
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
            <div class="card mt-0 mb-5" id="basic-info">
              <div class="card-header">
                <h5>Basic Info</h5>
              </div>
<form method="POST" enctype="multipart/form-data" action="{{ route('updateAd',$ad->id) }}">
    @csrf
    <div class="card-body pt-0 pb-4">
        <div class="row">
            <div class="col-4">
                <div class="input-group input-group-static flex-column">
                    <label>Ad Name</label>
                    <input type="text" class="form-control @error('ad_name') is-invalid @enderror" placeholder="Enter Ad Name" name="ad_name" value="{{$ad->ad_name}}"/>
                @error('ad_name')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                </div>
            </div>
            
            <div class="col-4">
                <div class="input-group input-group-static flex-column">
                    <label>Start Date</label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" placeholder="DD/MM/YYYY" name="start_date" value="{{ $ad->start_date }}" {{ $ad->payment_status == 0 ? '' : 'readonly' }} />
                 @error('start_date')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                </div>
            </div>
            
            <!--<div class="col-4">-->
            <!--    <div class="input-group input-group-static flex-column">-->
            <!--        <label>Expiration</label>-->
            <!--        <input type="date" class="form-control @error('expiration') is-invalid @enderror" placeholder="DD/MM/YYYY" name="expiration" value="{{ $ad->expiration }}" {{ $ad->payment_status == 0 ? '' : 'readonly' }} />-->
            <!-- @error('expiration')-->
            <!--        <span class="help-block invalid-feedback" role="alert">-->
            <!--            <strong>{{ $message }}</strong>-->
            <!--        </span>-->
            <!--    @enderror-->
            <!--    </div>-->
            <!--</div>-->

            <div class="col-4">
                <div class="input-group input-group-static flex-column">
                    <label>Zip Code or National</label>
                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" placeholder="Enter Zip Code" name="zip_code" value="{{ $ad->zip_code }}" />
                 @error('zip_code')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                </div>
                <div class="form-check form-switch ps-0 mt-3">
                    <input class="form-check-input ms-auto" type="checkbox" id="flexSwitchCheckDefault" name="is_national" {{ ($ad->is_national == 1) ? "checked" : "" }} />
                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault">Select If National</label>
                </div>
            </div>
                    </div>
        
        <div class="row mt-4">
            <div class="col-4">
                <div class="input-group input-group-static flex-column">
                    <label>Action Button Text</label>
                    <input type="text" value="{{ $ad->action_button_text }}" class="form-control @error('action_button_text') is-invalid @enderror" placeholder="Enter Button Text" name="action_button_text" />
                 @error('action_button_text')
                    <span class="help-block invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                </div>
            </div>
        
            <div class="col-4">
                <div class="input-group input-group-static flex-column">
                    <label>Action Button Link</label>
                    <input type="text" value="{{ $ad->action_button_link }}" class="form-control @error('action_button_link') is-invalid @enderror" placeholder="Enter Button Link" name="action_button_link" />
             @error('action_button_link')
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
                <label>Banner lmage</label>
                <div class="d-flex align-items-start align-items-sm-start gap-4 mt-3 flex-column">
                    <img src="{{ $ad->banner_image }}"
                        alt="user-avatar" class="rounded" width="50%"
                        id="uploadedAvatar" style="object-fit:contain" />
                    <div class="button-wrapper">
                        <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                            <span class="d-none d-sm-block">Upload banner</span>
                            <i class="bx bx-upload d-block d-sm-none"></i>
                            <input type="file" id="upload" class="account-file-input" hidden
                                accept="image/png, image/jpeg, image/jpg" name="banner_image" />
                        </label>
                        

                        
                    </div>
                </div>
             @error('banner_image')
                    <span class="help-block invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        
        <div class="col-6">
            <div class="input-group input-group-static flex-column">
            <label>Full Screen lmage</label>
                <div class="d-flex align-items-start align-items-sm-start gap-4 mt-3 flex-column">
                    <img src="{{ $ad->full_screen_image }}"
                        alt="image" class="" width="50%"
                        id="fullScreenImage" style="object-fit:contain" />
                    <div class="button-wrapper">
                        <label for="uploadScreenImage" class="btn btn-primary me-2 mb-4" tabindex="0">
                            <span class="d-sm-block">Upload Full screen image</span>
                            <i class="bx bx-upload d-block d-sm-none"></i>
                            <input type="file" id="uploadScreenImage" hidden class="uploadScreenImage"
                                accept="image/png, image/jpeg, image/jpg" name="full_screen_image" class=" @error('full_screen_image') is-invalid @enderror" />
                        </label>
            @error('full_screen_image')
                <span class="help-block invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
                </div>
                    </div>
            </div>
        </div>
        
        <input type="submit" value="Submit" class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3" />
    </div>
</form>
            </div>
        </div>
          </div>
@endsection