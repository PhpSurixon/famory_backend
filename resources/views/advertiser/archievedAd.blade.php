@extends('layouts.advertiser-master', ['title' => 'Archived Ad','previous'=> '/partner/dashboard','addRoute'=>'/new-ad'])

@section('content')
<style>
.btn.bg-info{
       padding: 10px 15px;
}
     @media screen and (min-width: 1200px){
        
    .list-group .list-group-item{
         width: calc(100% / 3.1);
    }
    }
     @media screen and (max-width: 1198px){
        
    .list-group .list-group-item{
         width: calc(100% / 2);
    }
    }
     @media screen and (max-width: 767px){
        
    .list-group .list-group-item{
         width: 100% !important;
    }
</style>
  <div class="row">
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 position-relative">
            <div id="preloaders" class="preloader"></div>
            @if($ads->count() > 0)
            <ul class="list-group d-flex flex-wrap align-items-center flex-row gap-3">
                @foreach($ads as $ad)
                  <li class="list-group-item border-0 d-flex align-items-center px-2 mb-2">
                    <div class="avatar me-2">
                    <img src="{{ $ad->banner_image }}" alt="kal" class="border-radius-lg shadow">
                    </div>
                    <div class="d-flex align-items-start flex-column justify-content-center">
                    <h6 class="mb-2 text-sm">{{ $ad->ad_name }}</h6>
                    <p class="mb-2 text-xs">Start Date: {{ $ad->start_date }}</p>
                    <p class="mb-2 text-xs">Expires: {{ $ad->expiration }}</p>
                    </div>
                     <span class="btn btn-link pe-0 ps-0 mb-0 ms-auto w-auto text-end">
                     <a class="btn mb-0 bg-info border-radius-section w-auto color-white mt-0 me-md-0" href="" onclick="event.preventDefault();document.getElementById('relist-form').submit();">Relist</a>
                    <!-- <a href="{{ route('selectedAd',$ad->id) }}" class="btn btn-link pe-3 ps-0 mb-0 ms-auto w-25 w-md-auto" href="javascript:;">-->
                    <!--   <i class="fa-solid fa-chevron-right color-dark arrow-size"></i>-->
                    <!--</a>-->
                </span>
                    <form id="relist-form" action="{{ route('relist',$ad->id) }}" method="POST" class="d-none">
                        @csrf 
                    </form>
                  </li>
                @endforeach
              </ul>
               @else
                    <div class='alert' style="text-align:center !important;">No Record Found</div>
                @endif

              <!--<a href="{{ route('newAdView') }}" class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3">New Ad</a>-->
          </div>
            <div class = "mt-4">
                {{ $ads->links('pagination::bootstrap-5') }}
            </div>
        </div>
@endsection