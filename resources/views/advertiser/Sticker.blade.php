@extends('layouts.advertiser-master', ['title' => 'Famory Tags','previous' => '/partner/dashboard','order'=>'/orders','purchase_history'=>'/purchasehistory'])

@section('content')
<style>

    .body {
        font-family: "Montserrat", sans-serif !important;
    }
    .text-xs {
        font-weight: 500;
    }
    
    .img-fit {
       min-width: 130px !important;
       height: 130px !important;
    }
    
    .img-fit img {
        object-fit: contain;
        width: 100% !important;
        height: 100% !important;
    }
</style>
     <div class="row">
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 position-relative px-4">
              <div id="preloaders" class="preloader"></div>
            <ul class="list-group">
                @foreach($getStickers as $data)
                    <a href="{{ route('selectedSticker',$data->id) }}">
                    <li class="list-group-item border-0 d-flex align-items-center border-radius p-3 mb-3 shadow">
                    <div class="avatar me-3 img-fit">
                    <img src="{{$data->image ?? '/assets/img/img_default.png'}}" alt="kal" class="border-radius-lg">
                    </div>
                    <div class="d-flex align-items-start flex-column justify-content-center text-justify me-4 lh-sm">
                    <h6 class="mb-2 text-sm">{{ $data->name }}</h6>
                    <p class="mb-2 text-xs">Price: ${{ number_format((float)$data->price, 2, '.', '') }}</p>
                    <p class="mb-2 text-xs">Total Quantity: {{ $data->count }}</p>
                    <p class="mb-0 text-xs">Description: {{ $data->description }}</p>
                    </div>
                    <span class="btn btn-link pe-3 ps-0 mb-0 ms-auto w-25 w-md-auto">
                    
                        <i class="fa-solid fa-chevron-right color-dark arrow-size"></i>
                    </span>
                  </li>
                    </a>
                @endforeach
                
              </ul>

              <!--<a href="{{ route('newAdView') }}" class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3">New Ad</a>-->
          </div>
          <div class = "mt-4">
                {{ $getStickers->links('pagination::bootstrap-5') }}
          </div>
        </div>

@endsection