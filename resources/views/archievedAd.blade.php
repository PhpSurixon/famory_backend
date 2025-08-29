@extends('layouts.advertiser-master', ['title' => 'Selected Ad'])

@section('content')

 <div class="row">
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4">

            <div class="d-flex gap-3 mb-4">
              <button class="btn mb-0 bg-white border-radius-section">Views: <span>1000</span></button>
              <button class="btn mb-0 bg-white border-radius-section">Clicks: <span>1000</span></button>
              <button class="btn mb-0 bg-white border-radius-section">Conversions: <span>1000</span></button>
            </div>

            <ul class="list-group bg-white">

              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Ad Name</h6>
                    <p class="mb-0 text-sm"> {{ $ad->ad_name }} </p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Start Date</h6>
                    <p class="mb-0 text-sm">{{ $ad->start_date }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Expiration</h6>
                    <p class="mb-0 text-sm">{{ $ad->expiration }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Zip Code</h6>
                    <p class="mb-0 text-sm">{{ $ad->zip_code }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Action Button Text</h6>
                    <p class="mb-0 text-sm">{{ $ad->action_button_text }}</p>
                  </div>
                </div>

              </li>

              <li class="list-group-item border-0 px-3 mb-2 py-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Action Button Link</h6>
                    <p class="mb-0 text-sm"><a href="{{ $ad->action_button_link }}" target="_blank">{{ $ad->action_button_link }}</a></p>
                  </div>
                </div>

              </li>

              </ul>
                
              <a href="{{ url('new-ad') }}" class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3">New Ad</a>
    
          </div>
        </div>

        <div class="d-flex gap-5 mt-4">
          
          <div class="imageBox w-25">
            <h4>Full Screen Image</h4>

            <img src="{{ $ad->full_screen_image }}" class="w-100 border-radius-lg"/>
          </div>

          <div class="imageBox w-25">
            <h4>Banner Image</h4>

            <img src="{{ $ad->banner_image }}" class="w-100 border-radius-lg"/>
          </div>

        </div>
@endsection