@extends('layouts.advertiser-master', ['title' => 'New Ad', 'previous' => '/partner/dashboard'])

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
        
        .payment-header {
            padding-bottom: 17px;
            background-color: aliceblue;
        }
        
        .payment-footer {
            border: none !important;
        }
        
        .card-group {
        display: flex!important;
        align-content: flex-start;
        flex-direction: column;
        }
        
        .card-one {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }
        
        .total-b {
           border-bottom: 1px solid #e7e7e7; 
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
        
         #PromoteModals .modal-body table tr .text-h{
        font-weight: 600;
        font-size: 16px;
        color: #7a7da3;
        padding: 1px!important;
        }
        
        .card-add-payment {
            margin-bottom: -4rem;
            height: auto !important;
            border: 1px solid #a0b3b0;
            padding: 6px;
        }
        .add-card-d {
            background-color: aliceblue;
        }
        .pay-mb {
            margin-bottom:5rem;
        }
    </style>

    <div class="row">
        <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-xl-4 position-relative">
            <div id="preloaders" class="preloader"></div>
            <div class="card mt-0 mb-5 " id="basic-info" style="border-radius: 10px;">
                <div class="card-header">
                    <h5>Basic Info</h5>
                </div>
                <form method="POST" enctype="multipart/form-data" id="storeAds">
                    @csrf
                    <div class="card-body pt-0 pb-4">
                        <div class="row">
                            <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Ad Name</label>
                                    <input type="text" class="form-control @error('ad_name') is-invalid @enderror"
                                        placeholder="Enter Ad Name" name="ad_name" value="{{ old('ad_name') }}" />
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
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        placeholder="DD/MM/YYYY" name="start_date" min="{{ $currentDate }}"
                                        value="{{ old('start_date') }}" />
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
                            <!--        <input type="date" class="form-control @error('expiration') is-invalid @enderror"-->
                            <!--            placeholder="DD/MM/YYYY" name="expiration" min="{{ $currentDate }}"-->
                            <!--            value="{{ old('expiration') }}" />-->
                            <!--        @error('expiration')-->
                            <!--            <span class="help-block invalid-feedback" role="alert">-->
                            <!--                <strong>{{ $message }}</strong>-->
                            <!--            </span>-->
                            <!--        @enderror-->
                            <!--    </div>-->
                            <!--</div>-->
                        

                        
                            <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <!--<label>Zip Code or National</label>-->
                                    <label>Zip Code</label>
                                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror"
                                        placeholder="Enter Zip Code" name="zip_code" value="{{ old('zip_code') }}" min="0" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" />
                                    @error('zip_code')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-check form-switch ps-0 mt-3">
                                    <input class="form-check-input ms-auto" type="checkbox" id="flexSwitchCheckDefault"
                                        name="is_national">
                                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                        for="flexSwitchCheckDefault">Select If National</label>
                                </div>
                            </div>
                            </div>
                            <div class="row">
                            <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Action Button Text</label>
                                    <input type="text"
                                        class="form-control @error('action_button_text') is-invalid @enderror"
                                        placeholder="Enter Button Text" name="action_button_text"
                                        value="{{ old('action_button_text') }}" />
                                    @error('action_button_text')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="input-group input-group-static flex-column">
                                    <label>Website Link</label>
                                    <input type="text"
                                        class="form-control @error('action_button_link') is-invalid @enderror"
                                        placeholder="Enter Website Link" name="action_button_link"
                                        value="{{ old('action_button_link') }}" />
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
                                    <label>Banner lmage </label>
                                    <label><span class="req" style="color:#3b4eb3;">The uploaded Banner lmage should be
                                            300 x 100 pixels*</span></label>
                                   
                                    <br />
                                  
                                    <div class="img-box" id="img-box-banner">
                                        <div class="input-box-container">
                                            <i class="fa-solid fa-camera"></i>
                                            <p>Add an image</p>
                                        </div>
                                        <input type="file" id="upload" class="account-file-input"
                                               accept="image/png, image/jpeg, image/jpg" name="banner_image"
                                               class=" @error('banner_image') is-invalid @enderror" />
                                    </div>

                                    <label for="upload" class="btn me-2 mb-1" tabindex="0">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="" alt="user-avatar" class="d-none rounded" width="50%"
                                                 id="uploadedAvatar" style="object-fit:contain" />
                                        </div>
                                    </label>

                                    <div id="banner_image-error-container"></div>
                                    
                                </div>
                            </div>
                                
                            <div class="col-6">
                                <div class="input-group input-group-static flex-column">
                                    <label>Full Screen lmage</label>
                                    <label><span class="req" style="color:#3b4eb3;">The uploaded Full Screen lmage
                                            should be 1080 x 1920 pixels*</span></label>
                                    <br />
                                    <div class="img-box" id="img-box-full">
                                        <div class="input-box-container">
                                            <i class="fa-solid fa-camera"></i>
                                            <p>Add an image</p>
                                        </div>
                                        <input type="file" id="uploadScreenImage" class="uploadScreenImage"
                                            accept="image/png, image/jpeg, image/jpg" name="full_screen_image"
                                            class="@error('full_screen_image') is-invalid @enderror" />
                                    </div>
                                    <label for="uploadScreenImage" class="btn me-2 mb-1" tabindex="0">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="" alt="image" class="d-none" width="50%"
                                            id="fullScreenImage" style="object-fit:contain" />
                                        </div>
                                    </label>
                                    
                                    <div id="full_screen_image-error-container"></div>
                                </div>
                                    
                            </div>

                            <button type="submit"
                                class="btn mb-0 bg-info border-radius-section w-10 color-white mt-3 storeAdBtn"
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
    
    @if($counts == 0)
        <div class="modal fade" id="PromoteModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="AdsPayment">
                    @csrf
                    <div class="modal-header payment-header">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add your payment form or content here -->
                        <input type="hidden" name="ads_id" id="adsId">
                        <input type="hidden" name="amount" value="0">
                        <table>
                            <tr>
                                <th class="text-h">Start Date</th>
                                <td class="txt-color" id="startDates">--</td>
                                <input type="hidden" name="start_date" id="startDates">
                            </tr>
                            
                            <tr>
                                <th class="text-h">Renew Date</th>
                                <td class="txt-color" id="renewDates">--</td>
                                <input type="hidden" name="renew_date" id="hiddenRenewDates">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer payment-footer">
                        <div class="d-flex flex-column ad-new gap-2 w-100">
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $card)
                            <div class="card-group card-one card mb-3">
                                <div class="d-flex">
                                <input type="radio" name="card" id="card_{{ $card->id }}" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}" style="width:auto!important;">
                                <!--<input type="hidden" name="card_number" id="card_number_{{ $card->id }}" value="{{ $card->card->last4 }}">-->
                                <label class="mb-0" for="card_{{ $card->id }}" style="font-size: 0.875rem;">**** **** **** {{ $card->card->last4 }}</label>
                                </div>
                                </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="w-100 d-flex justify-content-end mt-3">
                            <button class="btn btn-info border-radius-section color-white">
                            <a class="dropdown-item" style="font-weight: 800;" href="javascript:void(0)" data-toggle="modal"data-target="#cardAddModel" onclick="openModel()"><i class="bx bx-crown me-1"></i>Add Card</a>
                            </button>
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
        <div class="modal fade" id="PromoteModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="AdsPayment">
                    @csrf
                    <div class="modal-header payment-header">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add your payment form or content here -->
                        <input type="hidden" name="ads_id" id="adsId">
                        <table>
                            <tr>
                                <th class="text-h">Start Date</th>
                                <td class="txt-color" id="startDate">--</td>
                            </tr>
                            
                            <tr>
                                <th class="text-h">Renew Date</th>
                                <td class="txt-color" id="renewDate">--</td>
                                <input type="hidden" name="renew_date" id="hiddenRenewDate">
                            </tr>
                            
                            <tr>
                                <th class="text-h">Ads Monthly/Rate</th>
                                <td class="txt-color" id="adsRate">${{ number_format($price->price, 2, '.', ',') }}</td>
                            </tr>

                            <tr class="total-b">
                                <th class="total-p">Total Price</th>
                                <!--<td id="totalPrice">--</td>-->
                                <td class="txt-color" id="totalPrice">${{ number_format($price->price, 2, '.', ',') }}</td>
                                <input type="hidden" name="amount" value="{{$price->price}}">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer payment-footer">
                        <div class="d-flex flex-column ad-new gap-2 w-100">
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $card)
                            <div class="card-group card-one card mb-3">
                                <div class="d-flex">
                                <input type="radio" name="card" id="card_{{ $card->id }}" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}" style="width:auto!important;">
                                <!--<input type="hidden" name="card_number" id="card_number_{{ $card->id }}" value="{{ $card->card->last4 }}">-->
                                <label class="mb-0" for="card_{{ $card->id }}" style="font-size: 0.875rem;">**** **** **** {{ $card->card->last4 }}</label>
                                </div>
                                </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="w-100 d-flex justify-content-end mt-3">
                            <a href="{{ route('myAccount') }}" class="btn btn-info border-radius-section color-white mb-0">Add Card</a>
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
    
<div id="cardAddModel" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <!-- Increased modal size for better layout -->
        <div class="modal-content">
            <div class="modal-header add-card-d">
                <h5 class="modal-title" id="staticBackdropLabel">Add Card Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form" method="post">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="pay-mb">
                            <label for="card-name" class="form-label fw-bold text-dark fs-5 m-0">Name</label>
                            <input type="text" name="card_name" id="card-name" class="card-add-payment form-control @error('card_name') is-invalid @enderror" placeholder="Enter name">
                            @error('card_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div>
                            <label for="card-element" class="form-label fw-bold text-dark fs-5 m-0">Card Details</label>
                            <div id="card-element" class="form-control">
                                <!-- Stripe Element will be inserted here -->
                            </div>
                            <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-3">
                            <button type="close" class="btn rounded-pill btn-info w-20 mt-3" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn rounded-pill btn-info w-15 mt-3">Submit</button>
                            </div>
                        </div>
                    </div>

                    <!--<div class="row mb-3">-->
                    <!--    <div class="col-12">-->
                    <!--        <label for="card-element" class="form-label fw-bold text-dark fs-5">Card Details</label>-->
                    <!--        <div id="card-element" class="form-control">-->
                                <!-- Stripe Element will be inserted here -->
                    <!--        </div>-->
                    <!--        <div id="card-errors" class="text-danger mt-2" role="alert"></div>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <!--<div class="row">-->
                    <!--    <div class="col-12">-->
                    <!--        <button type="submit" class="btn btn-info w-100 mt-3">Submit</button>-->
                    <!--    </div>-->
                    <!--</div>-->
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            
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
            
            $('#uploadScreenImage').on('change', function() {
                if ($(this).val()) {
                    $('#img-box-full').hide();
                    
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#fullScreenImage').attr('src', e.target.result).removeClass('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                }
            });

            $('.storeAdBtn').on('click', function() {
                
                var firstCard = $('input[name="card"]').first();
                if (firstCard.length && !$('input[name="card"]:checked').length) {
                    firstCard.prop('checked', true);
                }
                var counts = @json($counts); // or however you pass the PHP variable to JavaScript
            
                var startDate = $('input[name="start_date"]').val();
            
                // Function to add days to a date
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
            
                if (counts == 0) {
                    // Handle case where $counts is 0
                    if (startDate) {
                        var parsedStartDate = new Date(startDate);
                        var renewDate = addDays(parsedStartDate, 90);
            
                        // Update the table and hidden inputs
                        $('#startDates').text(formatDateUS(parsedStartDate));
                        $('#renewDates').text(formatDateUS(renewDate));
                        $('#hiddenRenewDates').val(formatDate(renewDate));
                    } else {
                        console.error('Start date is not provided.');
                    }
                } else {
                    // Handle case where $counts is not 0
                    if (startDate) {
                        var start = new Date(startDate);
                        var renewDate = new Date(start);
                        renewDate.setMonth(renewDate.getMonth() + 1);
            
                        // Update the table and hidden inputs
                        $('#startDate').text(formatDateUS(start));
                        $('#renewDate').text(formatDateUS(renewDate));
                        $('#hiddenRenewDate').val(formatDate(renewDate));
                    } else {
                        // Handle missing start date
                        $('#startDate').text('--');
                        $('#endDate').text('--');
                        $('#renewDate').text('--');
                        $('#totalDays').text('--');
                        $('#totalPrice').text('--');
                    }
                }
            });

            $('#storeAds').on('submit', function(e) {
                e.preventDefault();

                // $('#submitButton').hide();
                $('#submitButton').attr('disabled',true);
                $('#preloader').show();
                $('.spinner-border').css("display","inline-block");
                $('#submitValue').css("display","none");

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('storeAd') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        // $('.storeAdBtn').hide();
                        var adsId = response.data.id;
                        $('#adsId').val(adsId);
                        
                        // Store the response message
                        responseMessage = response.message || "Operation successful";
                        
                        $('#PromoteModals').modal('show');

                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $('.invalid-feedback').remove();
                            $('input, select').removeClass('is-invalid');
                            for (let field in errors) {
                                let inputField = $('input[name="' + field + '"]');
                                
                                if (field === 'full_screen_image' || field === 'banner_image') {
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
                        $('#submitButton').show();
                        $("#submitButton").removeAttr("disabled"); 
                        $('#preloader').hide();
                        $('#submitValue').css("display","block");
                    }
                });
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
                 formData.append('card_number', cardNumber); // Append card number

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
                         $('#PromoteModals').hide();
                        // window.location.href = "{{ route('advertiser/dashboard') }}";
                        swal({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        confirmButtonText: "Okay",
                        didClose: () => {
                            window.location.href = "{{ route('advertiser/dashboard') }}";
                        },
                    }).then(() => {
                        // Show the next error after the current one is dismissed
                        console.log(responseMessage);
                        window.location.href = "{{ route('advertiser/dashboard') }}";
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
                                    swal({
                                        title: "Validation Error",
                                        text: errorMessages[index],
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

           
            $('#PromoteModals').on('hidden.bs.modal', function () {
                console.log(responseMessage);
                if (responseMessage) {
                    // Show SweetAlert2 with the response message
                    swal({
                        title: "Success",
                        text: responseMessage,
                        icon: "success",
                        confirmButtonText: "Okay",
                        timer: 3000,
                        didClose: () => {
                            window.location.href = "{{ route('advertiser/dashboard') }}";
                        },
                    }).then(() => {
                        // Show the next error after the current one is dismissed
                        console.log(responseMessage);
                        window.location.href = "{{ route('advertiser/dashboard') }}";
                    });
                } else {
                    // Handle case where no response message is provided
                    window.location.href = '/advertiser/dashboard';
                }
            });

        });
        
    function openModel(){
        $("#cardAddModel").modal('show');
    }
        
        
    document.addEventListener('DOMContentLoaded', function() {
        var stripeKey = "{{ config('app.stripe_client_id') }}";
        var stripe = Stripe(stripeKey);
        var elements = stripe.elements();

        var card = elements.create('card');
        card.mount('#card-element');
        
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    var displayError = document.getElementById('card-errors');
                    displayError.textContent = result.error.message;
                } else {
                    // Add Stripe token to the form
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', result.token.id);
                    form.appendChild(hiddenInput);

                    // Use AJAX to submit the form
                    $.ajax({
                        url: "{{route('addCardDetails')}}",
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            $("#cardAddModel").modal('hide');
                            if (response.status === 'success' && response.data) {
                                response.data.forEach(function(cardData) {
                                    var newCard = `
                                        <div class="card-group card-one card mb-3">
                                            <div class="d-flex">
                                                <input type="radio" name="card" id="card_${cardData.id}" value="${cardData.id}" data-card-number="${cardData.card.last4}" style="width:auto!important;" checked>
                                                <label class="mb-0" for="card_${cardData.id}" style="font-size: 0.875rem;">
                                                    **** **** **** ${cardData.card.last4}
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                    $('.ad-new').append(newCard); // Append new card to the list
                                });

                                // Show the updated PromoteModals modal
                                $("#PromoteModals").modal('show');
                            }
                        },
                        error: function(response) {
                            // Handle any errors here
                            console.log(response);
                            alert("There was an error processing your payment.");
                        }
                    });
                }
            });
        });
    });
        
        
        
    </script>
@endsection
