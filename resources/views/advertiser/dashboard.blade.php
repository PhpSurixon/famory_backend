@extends('layouts.advertiser-master', ['title' => 'My Ads','previous' => '/partner/dashboard','addRoute'=>'/new-ad','archiveAd'=>'/archieved-ads'])

@section('content')
<style>

    .text-xs {
        font-weight: 500;
    }
    
    .instructionContainer{
        background-color:#fff;
        padding:10px;
        margin-bottom:10px;
        border-radius:10px;
    }
    .trustedPartnerInstructions{
        font-size:16px;
        font-weight:600;
        margin:0;
    }
    
</style>

     <div class="row">
        <div class="col-12 px-4">
            
         <div class="instructionContainer card mb-4">
                <p class="trustedPartnerInstructions"><strong>Advertise with us!</strong>
Click "+Add New" in the top corner to place your ad. Simply upload a premade banner, enter your full ad content, and provide a link to your external landing page. Your monthly subscription will display metrics such as views, clicks on the full ad, and conversions to your website. Ads will be shown to users within a 50-mile radius of your business location.
                </p>
                <!--<p class="trustedPartnerInstructions">Advertise with us!  Press +Add New in the top corner to place your ad.  Simply upload a premade banner, add in your full ad, and then place a link to your external landing page.-->
                <!--    Your monthly subscrption will show you views, clicks to full ad, and conversions to your website.-->
                <!--    Ads will be shown to users in a 50 miles radius of your business location.-->
                <!--</p>-->
                
                
            </div>
        </div>
          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-4" style="position:relative;">
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
            <ul class="list-group dashboard-list d-flex flex-wrap align-items-center flex-row gap-3">
            @if($ads->count() > 0)
                @foreach($ads as $key=>$ad)
                <!--@php-->
                <!--echo "<pre>";-->
                <!--print_r($ad);-->
                <!--@endphp-->
                    <a class="d-block" href="{{ route('selectedAd',$ad->id) }}">
                    <li class="list-group-item border-0 d-flex align-items-center border-radius p-3 mb-0 shadow">
                    <div class="avatar me-3">
                    @if($ad->banner_image)
                        <img src="{{ $ad->banner_image }}" alt="kal" class="border-radius-lg">
                    @else
                        <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle">
                    @endif
                    
                    </div>
                    <div class="d-flex align-items-start flex-column justify-content-center">
                    <h5 class="mb-2">{{ $ad->ad_name ?? '-'}}</h5>
                    <p class="mb-2 text-xs">Start Date: {{ \Carbon\Carbon::parse($ad->start_date)->format('m/d/Y') ?? '-'}}</p>
                    <p class="mb-2 text-xs">Renewal Date: {{ $ad->renew_date ? \Carbon\Carbon::parse($ad->renew_date)->format('m/d/Y') : '-' }}</p>
                    
                    <p class="mb-2 text-xs">Ad Subscription status:
                    @if($ad->renew_date === null)
                        Pending
                    @else
                     {{ $ad->cancel_status == 1 ? 'Cancel' : 'Continue'}}
                    @endif
                    </p>
                    
                    
                    <!--@if($loop->last && $ad->renew_date !== null)-->
                    <!--    <p class="mb-2 text-xs">This advertisement is free for a full 90 days.</p>-->
                    <!--@endif-->
                    <!--<p class="mb-2 text-xs">Payment Status:-->
                    <!--<span style="color: {{ $ad->payment_status == 0 ? 'red' : 'green' }}">-->
                    <!--     {{ $ad->payment_status == 0 ? 'Pending' : 'Completed' }}-->
                    <!--</span>-->
                    </p>
                    </div>
                    <span class="btn btn-link pe-0 ps-0 mb-0 ms-auto w-25 w-md-auto">
                    
                        <i class="fa-solid fa-chevron-right color-dark arrow-size"></i>
                    </span>
                  </li>
                    </a>
                @endforeach
                @else
                    <div class='alert card w-100 bg-white text-center m-0'>No Ads Found</div>
                @endif
              </ul>

              <!--<a href="{{ route('newAdView') }}" class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3">New Ad</a>-->
          </div>
          <div class = "mt-4 px-4">
                {{ $ads->links('pagination::bootstrap-5') }}
          </div>
        </div>

@endsection