@extends('layouts.advertiser-master', ['title' => 'Selected Ad','previous'=> '/partner/dashboard', 'editRoute' => "/edit-ad/$ad->id",'deleteRoute' => "/delete-ad/$ad->id",])

@section('content')
<style>
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
        
        #preloader {
            font-size: 12px;
            margin-left: 7px;
        }
        
        .payment-head {
                background: aliceblue;
            }
            
        #paymentModal .modal-body table tr .text-h{
        font-weight: 600;
        font-size: 16px;
        color: #7a7da3;
        padding: 1px!important;
        }
        
        .txt-color {
            color: #1a73e8;
            font-weight: 600;
            font-size: 16px;
            padding: 1px!important;
        }
        
        .total-p {
            color: #3d3e44;
            font-weight: 600;
            font-size: 19px;
            padding: 1px!important;
        }
        
        .total-b {
           border-bottom: 1px solid #e7e7e7; 
        }
        
        .card-group {
        display: flex!important;
        align-content: flex-start;
        flex-direction: column;
        }
        
        .card-pay {
            padding-block: 1rem;
            padding-inline: 1rem;
            width: 100% !important;
            border-radius: 10px;
            margin-top: 1rem !important;
        }
        
        .child-first img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 5px;
        }
        
        .child-second img {
            width: 400px !important;
            height: 117px ;
            border-radius: 5px;
        }
        
        .card-new {
            padding-block: 1rem;
            padding-inline: 1rem;
            width: 100% !important;
            border-radius: 10px;
        }
        
        .card-parent {
            /*padding-block: 1rem;*/
            /*padding-inline: 1rem;*/
            display: flex;
            gap: 50px;
        }
        
        .text-zero {
            color: #7b809a!important;
        }
        
        .card-new .content{
            display: flex;
            align-items:flex-end;
            gap: 10px;
        }
        .card-new .content h6{
            font-size: 13px;
            color:#969696;
        }
        .card-new .content h4{
            font-size: 20px;
        }
        .full-banner-img{
            width: 400px;
            height: 400px;
        }
        .link{
                color: #1550af;
    text-decoration: underline;
        }
        .link:hover{
             color: #1550af;
    text-decoration: underline;
        }
        @media screen and (min-width: 768px) and (max-width: 1194px){
            .full-banner-img{
                max-width: 400px;
                height: 400px;
            }
            .card-parent{
                gap:20px;
            }
            .child-second img {
                   width: 348px !important;
        height: 117px;
        }
        .child-second .short-banner-img img{
            width: 100%;
            height: 100%;
        }
        }
        @media screen and (max-width: 768px){
            .card-parent{
                flex-direction: column;
            } 
            .card-parent{
                gap:20px;
            }
            .full-banner-img{
                width: 350px;
                height: 350px;
            }
             .child-second img {
                   width: 348px !important;
                    height: 117px;
        }
        .link{
                color: #1550af;
    text-decoration: underline;
    width: 160px;
    display: block;
    margin-left: auto;
    text-align: end;
        }
        }
    </style>
    
 <div class="row px-xl-4">
     <div id="preloaders" class="preloader"></div>
         
         
         
         
        <div class="col-xl-12 col-sm-12">
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
            <div class="d-flex gap-3 mb-4">
            @if($ad_stats)
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1 ">Views: <span class="text-zero">{{$ad_stats->view ?? 0}}</span></button>
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1">Clicks: <span class="text-zero">{{$ad_stats->click_to_open ?? 0}}</span></button>
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1">Conversions: <span class="text-zero">{{$ad_stats->click_to_website ?? 0}}</span></button>
            @else
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1">Views: <span class="text-zero">0</span></button>
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1">Clicks: <span class="text-zero">0</span></button>
              <button class="btn mb-0 bg-white text-dark border-radius-section card d-flex flex-row align-items-center gap-1">Conversions: <span class="text-zero">0</span></button>
            @endif
            </div>
            
              <div class="card card-pay mb-4">
            <ul class="list-group">

              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Ad Name</h6>
                    <p class="mb-0 text-sm"> {{ $ad->ad_name }} </p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Start Date</h6>
                    <p class="mb-0 text-sm">{{\Carbon\Carbon::parse($ad->start_date)->format('m/d/Y') ?? '-' }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Renewal Date</h6>
                    <p class="mb-0 text-sm">{{$ad->renew_date ?\Carbon\Carbon::parse($ad->renew_date)->format('m/d/Y') : '-' }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Zip Code</h6>
                    <p class="mb-0 text-sm">{{ $ad->zip_code }}</p>
                  </div>
                </div>

              </li>
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Action Button Text</h6>
                    <p class="mb-0 text-sm">{{ $ad->action_button_text }}</p>
                  </div>
                </div>

              </li>

              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Action Button Link</h6>
                    <p class="mb-0 text-sm"><a href="{{ $ad->action_button_link }}" class="link" target="_blank">{{ $ad->action_button_link }}</a></p>
                  </div>
                </div>

              </li>
              
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Payment Status</h6>
                    <p class="mb-0 text-sm"><span style="color: {{ $ad->payment_status == 0 ? 'red' : 'green' }}">
                         {{ $ad->payment_status == 0 ? 'Pending' : 'Completed' }}
                    </span></p>
                  </div>
                </div>

              <!--</li>-->
              @if($ad->payment_status == 1)
               <li class="list-group-item border-0 px-0 pt-0 pb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 text-sm"></h6>
                        <a class="btn btn-danger btn-sm" href="javascript:void(0);" onclick="deleteUser('{{ $ad->id }}')">
                            Cancel
                        </a>
                    </div>
                </li>
                @endif
              @if($ad->payment_status == 0) 
              <li class="list-group-item border-0 px-0 pt-0 pb-3">

                <div class="d-flex align-items-start flex-column justify-content-center">
                  <div class="d-flex justify-content-between w-100">
                    <h6 class="mb-0 text-sm">Payment</h6>
                    <p class="mb-0 text-sm"><span style="color: {{ $ad->payment_status == 0 ? 'red' : 'green' }}">
                        <button id="payment"  class="btn mb-0 border-radius-section" style="background-color: #387db8; color: aliceblue;">Make Payment</button> 
                    </span></p>
                  </div>
                </div>

              </li>
              @endif

              </ul>
              
              </div>
               <!--<a href="{{ route('newAdView') }}" class="btn mb-0 bg-info border-radius-100 w-25 color-white mt-3">New Ad</a>-->
         </div>
          <div class="col-xl-12 col-sm-12 mb-xl-0 mb-4 position-relative">

          
            
            <!--new-->
            
            <div class="card card-new">
                <div class="card-parent">
                <div class="child-first">
                    <div class="content">
                        <h4>Full Screen Image</h4>
                        <h6>({{ $fullScreenImageDimensions['width'] }} * {{ $fullScreenImageDimensions['height'] }}px)</h6>
                    </div>
                    
                    <div class="content-img full-banner-img mb-2">
                        <img src="{{ $ad->full_screen_image }}" class="w-100 border-radius-lg"/>
                    </div>
                </div>
                
                <div class="child-second">
                    <div class="content">
                        <h4>Banner Image</h4>
                        <h6>({{ $bannerImageDimensions['width'] }} * {{ $bannerImageDimensions['height'] }}px)</h6>
                    </div>
                    
                    <div class="content-img short-banner-img">
                        <img src="{{ $ad->banner_image }}" class="w-100 border-radius-lg"/>
                    </div>
                </div>
                </div>
            </div>
            
             <!--new-->
        </div>
</div>

    @if($firstAd <= 1)
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="AdsPayment">
                    @csrf
                    <div class="modal-header payment-head">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add your payment form or content here -->
                        <input type="hidden" name="ads_id" id="adsId" value="{{ $ad->id }}">
                        <table>
                            <!--<tr>-->
                            <!--    <th class="text-h">Start Date</th>-->
                            <!--    <td id="startDate" class="txt-color">{{ $ad->start_date }}</td>-->
                            <!--    <input type="hidden" name="start_date" value="{{ $ad->start_date }}">-->
                            <!--</tr>-->
                                                        <tr>
                                <th class="text-h">Start Date</th>
                                <td id="startDate" class="txt-color">
                                    
                                    {{ \Carbon\Carbon::parse($ad->start_date)->lt(\Carbon\Carbon::today()) ? \Carbon\Carbon::today()->format('m/d/Y') : \Carbon\Carbon::parse($ad->start_date)->format('m/d/Y') }}
                                </td>
                                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($ad->start_date)->lt(\Carbon\Carbon::today()) ? \Carbon\Carbon::today()->format('Y-m-d') : \Carbon\Carbon::parse($ad->start_date)->format('Y-m-d') }}">
                            </tr>
                            
                            
                            <!--<tr>-->
                            <!--    <th>End Date</th>-->
                            <!--    <td id="endDate">{{ $ad->expiration }}</td>-->
                            <!--</tr>-->
                            <!--<tr>-->
                            <!--    <th>Total Days</th>-->
                            <!--    <td id="totalDays">--</td>-->
                            <!--</tr>-->
                            <th class="text-h">Renew Date</th>
                                <td id="renewDates" class="txt-color">--</td>
                                <input type="hidden" name="renew_date" id="hiddenRenewDates">
                            </tr>
                            <tr>
                                <th class="text-h">Ads Monthly/Rate</th>
                                <td id="adsRate" class="txt-color">${{ number_format($price->price, 2, '.', ',') }}</td>
                            </tr>

                            <tr class="total-b">
                                <th class="total-p">Total Price</th>
                                <!--<td id="totalPrice">--</td>-->
                                <td id="totalPrice" class="txt-color">${{ number_format($price->price, 2, '.', ',') }}</td>
                                <input type="hidden" name="amount" value="{{$price->price}}">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer" style="border:none;">
                        <div class="d-flex flex-column ad-new gap-2 w-100">
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $index=>$card)
                            <div class="card-group card card-one mb-3 p-3 rounded">
                            <div class="d-flex">
                                <input type="radio" name="card" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}" id="card_{{ $card->id }}" style="width:auto;" {{ $index === 0 ? 'checked' : '' }}> 
                                <!--<input type="hidden" name="card_number" value="{{ $card->card->last4 }}">-->
                                <label class="mb-0" for="card_{{ $card->id }}" style="font-size: 0.875rem;">**** **** **** {{ $card->card->last4 }}</label>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="d-flex align-items-center ad-new gap-2 w-100">
                           <a href="{{route('addNewCard')}}" class="btn btn-info ms-auto mb-0">Add new card</a>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">

                        <button type="button" class="btn btn-info border-radius-section color-white"
                            style="margin-left:250px;" id="close" data-bs-dismiss="modal"
                            aria-label="Close">Close</button>
                        <button type="submit" class="btn bg-info border-radius-section color-white" style="background-color:#1A73E8!important;">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="AdsPayment">
                    @csrf
                    <div class="modal-header payment-head">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add your payment form or content here -->
                        <input type="hidden" name="ads_id" id="adsId" value="{{ $ad->id }}">
                        <table>
                            <!--<tr>-->
                            <!--    <th class="text-h">Start Date</th>-->
                            <!--    <td id="startDate" class="txt-color">{{ $ad->start_date }}</td>-->
                            <!--    <input type="hidden" name="start_date" value="{{ $ad->start_date }}">-->
                            <!--</tr>-->
                                                        <tr>
                                <th class="text-h">Start Date</th>
                                <td id="startDate" class="txt-color">
                                    
                                    {{ \Carbon\Carbon::parse($ad->start_date)->lt(\Carbon\Carbon::today()) ? \Carbon\Carbon::today()->format('m/d/Y') : \Carbon\Carbon::parse($ad->start_date)->format('m/d/Y') }}
                                </td>
                                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($ad->start_date)->lt(\Carbon\Carbon::today()) ? \Carbon\Carbon::today()->format('Y-m-d') : \Carbon\Carbon::parse($ad->start_date)->format('Y-m-d') }}">
                            </tr>
                            
                            
                            <!--<tr>-->
                            <!--    <th>End Date</th>-->
                            <!--    <td id="endDate">{{ $ad->expiration }}</td>-->
                            <!--</tr>-->
                            <!--<tr>-->
                            <!--    <th>Total Days</th>-->
                            <!--    <td id="totalDays">--</td>-->
                            <!--</tr>-->
                            <th class="text-h">Renew Date</th>
                                <td id="renewDate" class="txt-color">--</td>
                                <input type="hidden" name="renew_date" id="hiddenRenewDate">
                            </tr>
                            <tr>
                                <th class="text-h">Ads Monthly/Rate</th>
                                <td id="adsRate" class="txt-color">${{ number_format($price->price, 2, '.', ',') }}</td>
                            </tr>

                            <tr class="total-b">
                                <th class="total-p">Total Price</th>
                                <!--<td id="totalPrice">--</td>-->
                                <td id="totalPrice" class="txt-color">${{ number_format($price->price, 2, '.', ',') }}</td>
                                <input type="hidden" name="amount" value="{{$price->price}}">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer" style="border:none;">
                        <div class="d-flex flex-column ad-new gap-2 w-100">
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $index=>$card)
                            <div class="card-group card card-one mb-3 p-3 rounded">
                            <div class="d-flex">
                                <input type="radio" name="card" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}" id="card_{{ $card->id }}" style="width:auto;" {{ $index === 0 ? 'checked' : '' }}> 
                                <!--<input type="hidden" name="card_number" value="{{ $card->card->last4 }}">-->
                                <label class="mb-0" for="card_{{ $card->id }}" style="font-size: 0.875rem;">**** **** **** {{ $card->card->last4 }}</label>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="d-flex align-items-center ad-new gap-2 w-100">
                           <a href="{{route('addNewCard')}}" class="btn btn-info ms-auto mb-0">Add new card</a>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">

                        <button type="button" class="btn btn-info border-radius-section color-white"
                            style="margin-left:250px;" id="close" data-bs-dismiss="modal"
                            aria-label="Close">Close</button>
                        <button type="submit" class="btn bg-info border-radius-section color-white" style="background-color:#1A73E8!important;">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Function to format date to m/d/y
            // function formatDate(date) {
            //     return date.toLocaleDateString('en-US');
            // }
            
        $('#payment').on('click', function() {
                console.log("btn");
                var startDate = $('#startDate').text();
                var endDate = $('#endDate').text();
                var adsRate = parseFloat($('#adsRate').text());
                var firstAd = @json($firstAd); 
                
                 function addDays(date, days) {
                    var result = new Date(date);
                    result.setDate(result.getDate() + days);
                    return result;
                }
            
                // Function to format the date to YYYY-MM-DD
                function formatDate(date) {
                    var day = String(date.getDate()).padStart(2, '0');
                    var month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
                    var year = date.getFullYear();
                    return `${year}-${month}-${day}`;
                }
            
                // Function to format date to m/d/Y
                function formatDateUS(date) {
                    var day = String(date.getDate()).padStart(2, '0');
                    var month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
                    var year = date.getFullYear();
                    return `${month}/${day}/${year}`; // Format: m/d/Y
                }
                
                console.log("firstAd",firstAd);
                if(firstAd <= 1){
                    console.log("if");
                      if (startDate) {
                        var parsedStartDate = new Date(startDate);
                        var renewDates = addDays(parsedStartDate, 90);
            
                        // Update the table and hidden inputs
                        $('#startDate').text(formatDateUS(parsedStartDate));
                        $('#renewDates').text(formatDateUS(renewDates));
                        $('#hiddenRenewDates').val(formatDate(renewDates));
                    } else {
                        console.error('Start date is not provided.');
                    }
                }else{
                    console.log("else");
                    if (startDate) {
                        var start = new Date(startDate);
                        var renewDate = new Date(start);
                        renewDate.setMonth(renewDate.getMonth() + 1);
                        $('#startDate').text(formatDateUS(start));
                        $('#renewDate').text(formatDateUS(renewDate));
                        $('#hiddenRenewDate').val(formatDate(renewDate));
    
                    } else {
                        $('#startDate').text('--');
                        $('#endDate').text('--');
                        $('#totalDays').text('--');
                        $('#totalPrice').text('--');
                        $('#renewDate').text('--');
                    }
                }
                
               
                
                
                
                $("#paymentModal").modal("show");
            });
    
        $('#AdsPayment').on('submit', function(e) {
                e.preventDefault();
                
                var selectedCard = $('input[name="card"]:checked');
                var cardNumber = selectedCard.data('card-number');

                
                var hasError = false;
                if (!selectedCard.length) {
                    $('input[name="card"]').parent().addClass('is-invalid');
                    let $errorSpan = $('input[name="card"]').parent().next('.error-message');
                    
                    if ($errorSpan.length) {
                        $errorSpan.text('Please select a card.');
                    } else {
                        $('<span class="error-message" style="color: red; font-weight: 400;">Please select a card.</span>')
                            .insertAfter($('input[name="card"]').parent().last());
                    }
                    
                    hasError = true;
                } else {
                    $('input[name="card"]').parent().removeClass('is-invalid');
                    $('input[name="card"]').parent().next('.error-message').remove();
                    console.log('Selected Card ID:', selectedCard.val());
                    console.log('Card Number:', cardNumber);
                }
                if (!hasError) {
                    console.log('Form submitted with Card ID:', selectedCard.val(), 'and Card Number:', cardNumber);
                }
                
                
                let formData = new FormData(this);
                formData.append('card_number', cardNumber); 

                $.ajax({
                    url: "{{ route('storeAdPayment') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        $('.storeAdBtn').hide();
                        $('#paymentModal').hide();
                        // window.location.href = "{{ route('advertiser/dashboard') }}";
                        Swal.fire({
                            text: "Success",
                            title: response.message,
                            icon: "success",
                            confirmButtonText: "Okay",
                            timer: 5000,
                            didClose: () => {
                                window.location.reload();
                            },
                        }).then(() => {
                            // Show the next error after the current one is dismissed
                            window.location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = [];

                            for (let field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errors[field].forEach(message => {
                                        errorMessages.push(message);
                                    });
                                }
                            }

                            // Function to show each error one by one
                            const showErrorsSequentially = (index = 0) => {
                                if (index < errorMessages.length) {
                                    Swal.fire({
                                        text: "Validation Error",
                                        title: errorMessages[index],
                                        icon: "error",
                                        button: "Ok",
                                    }).then(() => {
                                        // Show the next error after the current one is dismissed
                                        showErrorsSequentially(index + 1);
                                    });
                                }
                            };

                            // Start showing errors
                            showErrorsSequentially();

                        } else {
                            // alert('Something went wrong, please try again.');
                        }
                    }
                });
            });
            
    function deleteUser(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (id !== '') {
            Swal.fire({
              title: "Are you sure?",
              text: "You want to Cancel Subscription!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, Do it!"
            }).then((result) => {
                console.log("ok");
                if (result.isConfirmed) {
                    console.log("Hello");
                    $.ajax({
                        url: "{{route('adsSubscriptionCancel')}}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success: function(response) {
                            if(response.status == 'success'){
                                Swal.fire({
                                    title: "Done!",
                                    text: response.message,
                                    icon: "success"
                                }).then(() => {
                                    window.location.href = "{{ route('dashboard') }}";
                                });
                            } else {
                                Swal.fire({
                                    title: "Oops!",
                                    text: response.message,
                                    icon: "error"
                                }).then(() => {
                                    window.location.href = "{{ route('dashboard') }}";
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: "Error!",
                            text: "An error occurred while processing your request.",
                            icon: "error"
                        });
                    }
                    });
                }
            });
        }
    }
    </script>
        
@endsection