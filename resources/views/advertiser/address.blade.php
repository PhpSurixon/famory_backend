@extends('layouts.advertiser-master', ['title' => 'Address','previous' => '/go-to-cart'])

@section('content')
<style>
    .text-xs {
        font-weight: 600;
    }
     .quantity-selector {
        width: 120px;
    }
    .quantity-input {
        width: 40px;
        padding: 5px;
        text-align: center;
    }
    .decrement-btn, .increment-btn {
        width: 30px;
    }
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        border-radius: 8px;
    }
    
    .avatar img {
        border-radius: 8px;
        width: 80px;
        height: auto;
    }
    
    .card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }
    
    .card h5 {
        font-weight: bold;
    }
    
    .card h6 {
        font-weight: bold;
        color: #333;
    }
    
    .card p {
        font-size: 14px;
        color: #555;
    }
    
    .btn-block {
        width: 100%;
        background-color: #fb641b;
        color: white;
    }
    
    .btn-block:hover {
        background-color: #f45b1a;
    }
    
    input[type="radio"]{
        width: auto;
    }
    .text-sm {
        /* font-size: 0.875rem !important; */
        font-weight: 600;
        font-size: 16px;
    } 
    .modal-content {
        border: 3px solid #2174f9;
        background: ghostwhite;
    }
    
     .spinner-border {
        display: none;
        --bs-spinner-width: 1rem !important;
        --bs-spinner-height: 1rem !important;
    }

    .btn-loading .spinner-border {
        display: inline-block;
    }
    #editAddressModals .form-control,
    #PromoteModals .form-control {
        border: 1px solid #a0b3b0;
    }
    .radio-buttons{
        width: 100%;
    }
     .custom-radio,
     .payment-method-cards
     {
         position: relative;
     }
    .payment-method-cards input, .custom-radio input{
        opacity: 0;
        position: absolute;
        z-index: 9;
        width: 100%;
        height:100%;
    }
    
    .radio-btn{
        position:relative;
    }
    .radio-btn > i.fa-solid.fa-circle-check {
      color: #1550af;
      background-color: #ffffff;
      font-size: 20px;
      position: absolute;
      top: -15px;
      left: 1%;
      transform: translateX(-50%) scale(2);
      border-radius: 50px;
      padding: 3px;
      transition: 0.5s;
      /*pointer-events: none;*/
      opacity: 0;
    }
    
    .radio-btn >i.fa-solid.fa-pencil{
      color: #1550af;
      background-color: #ffffff;
      font-size: 16px;
      position: absolute;
      top: -15px;
      right: -0%;
      transform: translateX(-50%);
      border-radius: 50px;
      padding: 5px;
      transition: 0.5s;
      opacity: 0;
      border:2px solid #1550af;
    }
    
    .custom-radio:hover .addressCard > i.fa-solid.fa-pencil {
        opacity:1;
        color: #1550af;
        background-color: #ffffff;
        font-size: 16px;
        position: absolute;
        top: -16px;
        right: 0%;
        transform: translateX(-16%);
        border-radius: 50px;
        padding: 5px;
        transition: 0.5s;
        cursor:pointer;
        border:2px solid #1550af;
    }
    
    .custom-radio input:checked + .radio-btn{
        min-height: 159px;
    }
    .custom-radio input:checked + .radio-btn,
    .payment-method-cards input:checked + .radio-btn
    {
        border: 2px solid #1550af;
        border-radius: 10px;
        
    }
    .custom-radio input:checked 
    {
        border: 2px solid #1550af;
        border-radius: 10px;
    }
    
    
    
    
    .custom-radio input:checked + .radio-btn > i.fa-solid.fa-circle-check,
    .payment-method-cards input:checked + .radio-btn > i.fa-solid.fa-circle-check
    {
      opacity: 1;
      transform: translateX(-0%) scale(1);
    }
    .list-group{
            display: flex;
        align-items: center;
        flex-direction: row;
        justify-content: space-between;
        gap: 20px;
    }
    .list-group .list-group-item{
            width: calc(100% - 20px);
    }
    .custom-radio h4 i,
    .payment-method-cards h4 i{
        color:#1550af;
        font-size: 18px;
    }
    .custom-radio h4,
    .payment-method-cards h4{
        font-size: 13px !important;
            font-weight: 400;
    }
    .custom-radio h4:first-child span{
        font-size: 18px;
        color: #000;
        font-weight: 700;
    }
    .custom-radio h4:not(:last-child){
        margin-bottom: 13px !important;
    } 
    .label{
        font-size: 18px;
        font-weight: 700;
        color: #000;
    }
    .btn.bg-info i,
    .btn.bg-info span{
        color:#fff;
    }
    .swal-footer{
            text-align:center;
    }
    
    
    .addressCardOuter:hover>.radio-btn>h4>i.fa-solid.fa-pencel{
        background:red;
        display:none;
    }
    
    .payment-header {
        padding-bottom: 17px;
        background-color: aliceblue;
    }
    
    .card-group {
        display: flex!important;
       align-content: flex-start;
       flex-direction: column;
    }
    
    .payment-method-cards{
        background: #ffffff;
        border-radius: 10px;
    }
    .card-imgs{
        width: 50px;
        height: 32px;
    }
    .card-imgs img{
        width: 100%;
    }
    .total-price-card{
        background:#fff;
    }
    .total-price-card table th,
    .total-price-card table td{
        padding: 7px 2px;
    }
    .custom-radio{
        height: 159px;
        /*overflow-y:auto ;*/
    }
</style>

<div class="container">
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-12 col-md-7 col-sm-12 position-relative">
            <div id="preloaders" class="preloader"></div>
           <div class="row g-3 my-0">
                <div class="col-md-12 mt-0">
                        <div id="error-message" class="text-danger fw-normal"></div>
                    <div class="d-flex align-items-center justify-content-between mb-3" >
                        <h3 class="label my-0".>Address</h3>
                        <button id="addressModal" class="btn bg-info mb-0 d-flex align-items-center gap-2 justify-content-center"><i class="fa fa-plus" aria-hidden="true" style="font-size: 1.5rem; margin-right: 0.5rem;"></i> <span>New Address</span></button>
                    </div>
                   
                </div>
                <div class="col-md-12">
                   <ul class="list-group" id="addressList">
                        <!-- Addresses will be loaded here -->
                   </ul> 
                </div>
                <div class="col-md-12">
                     <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="label my-0">Place Order</h3>
                        <!--<button id="paymentModel" class="btn bg-info mb-0 d-flex align-items-center gap-2 justify-content-center"><i class="fa fa-credit-card" aria-hidden="true" style="font-size: 1.5rem; margin-right: 0.5rem;"></i> <span>Place Order</span></button>-->
                        <a href="{{route('addNewCard')}}" class="btn bg-info mb-0 d-flex align-items-center gap-2 justify-content-center"><i class="fa fa-credit-card" aria-hidden="true" style="font-size: 1.5rem; margin-right: 0.5rem;"></i> <span>Add new card</span></a>
                    </div>
                  
                        
                </div>
                <div class="col-md-12">
                      <div class="place-order-container">
                         <div class="card-methods row">
                        @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $index => $card)    
                                <div class="col-xl-4 col-md-4 col-12">
                                    <div class="payment-method-cards shadow">
                                        <input type="radio" name="card_id" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}" {{ $index == 0 ? 'checked' : '' }}>
                                        <div class="radio-btn w-100 p-3 border-radius addressCard">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="card-imgs">
                                                    <img src="assets/img/master-card.png" alt="master-card">
                                                </div>
                                                <h3 class="mb-2 text-lg price-per-unit d-flex align-items-center gap-2" for="card_{{ $card->id }}">
                                                    <span>**** **** ****{{ $card->card->last4 }}</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif  
                    </div>
                        
                    </div> 
                </div>

<div class="col-md-12">
    <div class="card total-price-card">
        <div class="card-body p-0">
            <form method="POST" enctype="multipart/form-data" id="Payment">
                @csrf
                <table style="width:100%">
                    <tr>
                        <th style="text-align:left; font-weight: 600; font-size: 16px; color: #7a7da3;">Total Item:</th>
                        <td id="price" style="text-align:right;color: #1a73e8;">{{$orderData['item_count'] ?? '--'}}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left; font-weight: 600; font-size: 16px; color: #7a7da3;">Total Quantity:</th>
                        <td id="count" style="text-align:right; color: #1a73e8">
                            @php
                                $totalQuantity = 0;
                                if (isset($orderData) && is_array($orderData) && isset($orderData['items']) && is_array($orderData['items'])) {
                                    foreach ($orderData['items'] as $item) {
                                        if (isset($item['quantity']) && is_numeric($item['quantity'])) {
                                            $totalQuantity += (int)$item['quantity'];
                                        }
                                    }
                                }
                            @endphp
                            {{ $totalQuantity }}
                            <input type="hidden" value="{{ $totalQuantity }}" name="quantity"/>
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #e7e7e7;border-bottom: 1px solid #e7e7e7;">
                        <th style="text-align:left; color: #3d3e44; font-weight: 600; font-size: 19px;">Total Price</th>
                        <td id="totalPrice" style="text-align:right;color: #1a73e8;">
                            @php
                                $totalAmount = isset($orderData['total_amount']) ? $orderData['total_amount'] : '--';
                            @endphp
                            ${{ $totalAmount }}
                        </td>
                        <input type="hidden" name="amount" value="{{ $totalAmount }}">
                    </tr>
                </table>
                <button type="submit" class="btn bg-info border-radius-section color-white mt-3 mb-0" style="background-color:#1A73E8!important;">Submit</button>
            </form>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<!--Address Modal-->
<div class="modal fade" id="PromoteModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
               <form method="POST" enctype="multipart/form-data" id="Address">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Address</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control px-2" id="name" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control px-2" id="phoneNumber" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="houseNo" class="form-label">House Number</label>
                        <input type="text" class="form-control px-2" id="houseNo" name="house_number">
                    </div>
                    <div class="mb-3">
                        <label for="roadName" class="form-label">Road Name</label>
                        <input type="text" class="form-control px-2" id="roadName" name="road_name">
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control px-2" id="state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="zipCode" class="form-label">Zip Code</label>
                        <input type="text" class="form-control px-2" id="zipCode" name="zip_code">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-info border-radius-section color-white" id="close" data-bs-dismiss="modal" aria-label="Close">Close</button>
                    <button type="submit" class="btn bg-info border-radius-section color-white">Submit</button>
                </div>
            </form>

            </div>
        </div>
    </div>
<!--<div class="modal fade" id="PaymentModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"-->
<!--        data-bs-keyboard="false" data-bs-backdrop="static">-->
<!--        <div class="modal-dialog" role="document">-->
<!--            <div class="modal-content">-->
                
<!--                <form method="POST" enctype="multipart/form-data" id="Payments">-->
<!--                    @csrf-->
<!--                    <div class="modal-header payment-header">-->
<!--                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>-->
<!--                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">-->
<!--                            <span aria-hidden="true">&times;</span>-->
<!--                        </button>-->
<!--                    </div>-->
                    
<!--                    <div class="modal-body">-->
<!--                        <div id="error-message" class="text-danger fw-normal"></div>-->
                        <!-- Add your payment form or content here -->
<!--                        <input type="hidden" name="product_id" id="" value=>-->
<!--                        <table style="width:100%">-->
<!--                            <tr>-->
<!--                                <th style="text-align:left; font-weight: 600; font-size: 16px; color: #7a7da3;">Total Item:</th>-->
<!--                                <td id="price" style="text-align:right;color: #1a73e8;">{{$orderData['item_count'] ?? '--'}}</td>-->
<!--                            </tr>-->
<!--                            <tr>-->
<!--                                <th style="text-align:left; font-weight: 600; font-size: 16px; color: #7a7da3;">Total Quantity:</th>-->
<!--                            <td id="count" style="text-align:right; color: #1a73e8">-->
<!--                                @php-->
<!--                                    $totalQuantity = 0;-->
<!--                                    if (isset($orderData) && is_array($orderData) && isset($orderData['items']) && is_array($orderData['items'])) {-->
<!--                                        foreach ($orderData['items'] as $item) {-->
<!--                                            if (isset($item['quantity']) && is_numeric($item['quantity'])) {-->
<!--                                                $totalQuantity += (int)$item['quantity'];-->
<!--                                            }-->
<!--                                        }-->
<!--                                    }-->
<!--                                @endphp-->
<!--                                {{ $totalQuantity }}-->
<!--                                <input type="hidden" value="{{ $totalQuantity }}" name="quantity"/>-->
<!--                            </td>-->
<!--                            </tr>-->
<!--                            <tr style="border-bottom: 1px solid #e7e7e7;">-->
<!--                            <th style="text-align:left; color: #3d3e44; font-weight: 600; font-size: 19px;">Total Price</th>-->
<!--                            <td id="totalPrice" style="text-align:right;color: #1a73e8;">-->
<!--                                @php-->
<!--                                    // Set a default value if $orderData is null or does not contain 'total_amount'-->
<!--                                    $totalAmount = isset($orderData['total_amount']) ? $orderData['total_amount'] : '--';-->
<!--                                @endphp-->
<!--                                ${{ $totalAmount }}-->
<!--                            </td>-->
<!--                            <input type="hidden" name="amount" value="{{ $totalAmount }}">-->
<!--                        </tr>-->
<!--                        </table>-->
<!--                    </div>-->
<!--                    <div class="modal-footer" style="border:none;">-->
<!--                        <div class="d-flex flex-column ad-new gap-2 w-100" >  -->
<!--                            @if(is_array($cards) || is_object($cards))-->
<!--                            @foreach ($cards as $card)-->
<!--                            <div class="card-group card mb-3">-->
<!--                                <div class="d-flex">-->
<!--                                <input type="radio" name="card_id" value="{{ $card->id }}" data-card-number="{{ $card->card->last4 }}">-->
                                <!--<input type="hidden" name="card_number" value="{{ $card->card->last4 }}">-->
<!--                                <label class="mb-0 text-black" for="card_{{ $card->id }}">**** **** ****-->
<!--                                    {{ $card->card->last4 }}</label>-->
<!--                                    </div>-->
<!--                            </div>-->
<!--                            @endforeach-->
<!--                            @endif-->
<!--                        </div>-->
<!--                        <div class="d-flex align-items-center ad-new gap-2 w-100">-->
<!--                           <a href="{{route('addNewCard')}}" class="btn btn-info ms-auto" style="margin-bottom: 0;">Add new card</a>-->
<!--                        </div>-->
<!--                    </div>-->
                    
<!--                    <div class="modal-footer d-flex justify-content-end">-->
<!--                        <button type="button" class="btn btn-info border-radius-section color-white"-->
<!--                            id="close" data-bs-dismiss="modal"-->
<!--                            aria-label="Close">Close</button>-->
<!--                        <button type="submit" class="btn bg-info border-radius-section color-white" style="background-color:#1A73E8!important;">Submit</button>-->
<!--                    </div>-->
<!--                </form>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->


<div class="modal fade" id="editAddressModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
               <form method="POST" enctype="multipart/form-data" id="editAdress">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">Address</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" id="id" name="id" >
                <input type="hidden" id="user_id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control px-2" id="uname" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control px-2" id="uphoneNumber" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="houseNo" class="form-label">House Number</label>
                        <input type="text" class="form-control px-2" id="uhouseNo" name="house_number">
                    </div>
                    <div class="mb-3">
                        <label for="roadName" class="form-label">Road Name</label>
                        <input type="text" class="form-control px-2" id="uroadName" name="road_name">
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control px-2" id="ustate" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="zipCode" class="form-label">Zip Code</label>
                        <input type="text" class="form-control px-2" id="uzipCode" name="zip_code">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-info border-radius-section color-white" id="close" data-bs-dismiss="modal" aria-label="Close">Close</button>
                    <button type="submit" class="btn bg-info border-radius-section color-white">Submit</button>
                </div>
            </form>

            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
        $(document).ready(function() {
            $('#addressModal').on('click', function() {
                $('#PromoteModals').modal('show');
            });
            
            $('#paymentModel').on('click',function(){
                $('#PaymentModals').modal('show');
            });
            
            $("#error-message").show();
            
            setTimeout(function() {
                $("#error-message").fadeOut();
            }, 10000);
            
            $('#Address').on('submit', function(e) {
                
                e.preventDefault();
                var hasError = false;

                // Clear any existing error messages
                $('.is-invalid').removeClass('is-invalid');
                $('.error-message').remove();
        
                var name = $('input[name="name"]').val();
                var phoneNumber = $('input[name="phone_number"]').val();
                var houseNo = $('input[name="house_number"]').val();
                var roadName = $('input[name="road_name"]').val();
                var state = $('input[name="state"]').val();
                var zipCode = $('input[name="zip_code"]').val();
        
                // Validate Name
                if (!name) {
                    $('input[name="name"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your name.</span>')
                        .insertAfter($('input[name="name"]'));
                    hasError = true;
                }
        
                // Validate Phone Number
                if (!phoneNumber) {
                    $('input[name="phone_number"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your phone number.</span>')
                        .insertAfter($('input[name="phone_number"]'));
                    hasError = true;
                }
        
                // Validate House Number
                if (!houseNo) {
                    $('input[name="house_number"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your house number.</span>')
                        .insertAfter($('input[name="house_number"]'));
                    hasError = true;
                }
        
                // Validate Road Name
                if (!roadName) {
                    $('input[name="road_name"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your road name.</span>')
                        .insertAfter($('input[name="road_name"]'));
                    hasError = true;
                }
        
                // Validate State
                if (!state) {
                    $('input[name="state"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your state.</span>')
                        .insertAfter($('input[name="state"]'));
                    hasError = true;
                }
        
                // Validate Zip Code
                if (!zipCode) {
                    $('input[name="zip_code"]').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your zip code.</span>')
                        .insertAfter($('input[name="zip_code"]'));
                    hasError = true;
                }
        
                if (!hasError) {
                        let formData = new FormData(this);
                        $.ajax({
                            url: "{{ route('createAddress') }}",
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                console.log(response);
                                $('.invalid-feedback').remove();
                                $('input, select').removeClass('is-invalid');
                                $('#PromoteModals').hide();
                                if (response.status === 'success') {
                                    swal({
                                        title: response.message,
                                        text: "Success",
                                        icon: "success",
                                        confirmButtonText: "Ok",
                                    })
                                    .then(() => {
                                        window.location.href = "{{ route('address') }}";
                                    });
                                } else if (response.status === 'failed' && response.message === 'The provided address is not valid.') {
                                    swal({
                                        title: response.message,
                                        text: "Invalid Address",
                                        icon: "error",
                                        button: "Ok",
                                    });
                                }else {
                                    swal({
                                        title: "Error",
                                        text: "Something went wrong, please try again.",
                                        icon: "error",
                                        button: "Ok",
                                    });
                                }
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
                                    alert('Something went wrong, please try again.');
                                }
                            }
                        });
                }
            });
            
            $.ajax({
                url: '{{ route('getAddress') }}',
                method: 'GET',
                success: function(response) {
                    // Check if the response status is 'success'
                    if (response.status === "success") {
                        // Clear the existing list
                        $('ul.list-group').empty();
        
                        // Loop through the addresses and append them to the list
                        response.data.forEach(function(address) {
                            var addressItem = `
                                <li class="list-group-item d-block border-radius p-0 mb-3 shadow addressCardOuter">
                                 <div class="custom-radio box-select">
                                    
                                    <input type="radio" name="address" value="${address.id}" />
                                    <div class="radio-btn w-100 p-3 border-radius addressCard">
                                       <i class="fa-solid fa-circle-check"></i>
                                       <i class="fa-solid fa-pencil" onClick="editAddress('${address.id}')"></i>
                                        <div>
                                            <h4 class="mb-2 text-xs price-per-unit d-flex align-items-center gap-2"><i class="fa-solid fa-user"></i> <span>${address.name}</span></h4>
                                            <h4 class="mb-2 text-xs price-per-unit d-flex align-items-center gap-2"><i class="fa-solid fa-phone"></i> <span>${address.phone_number}</span></h4>
                                            <h4 class="mb-2 text-xs price-per-unit d-flex align-items-center gap-2"><i class="fa-solid fa-location-dot"></i> <span>${address.house_number} ${address.road_name}, ${address.state} ${address.zip_code}</span></h4>
                                            
                                        </div>
                                    </div>
                                  </div>
                                </li>`;

                            $('ul.list-group').append(addressItem);
                            $('#addressList input[name="address"]').first().prop('checked', true);
                            $('.custom-radio').on('click',function(){
                                
                            });
                            
                            
                            
                        });
                    } else {
                        console.log('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                }
            });
            
            $('#Payment').on('submit', function(e) {
                e.preventDefault();
                    
                    let address = $('input[name="address"]:checked').val();
                    var selectedCard = $('input[name="card_id"]:checked');
                    let hasError = false;
                
                    // Check if a card is selected
                    if (!selectedCard.length) {
                        $('input[name="card_id"]').parent().addClass('is-invalid');
                        let $errorSpan = $('input[name="card_id"]').parent().next('.error-message');
                        if ($errorSpan.length) {
                            $errorSpan.text('Please select a card.');
                        } else {
                            $('<span class="error-message" style="color: red; font-weight: 400;">Please select a card.</span>')
                                .insertAfter($('input[name="card_id"]').parent().last());
                        }
                        hasError = true;
                    } else {
                        $('input[name="card_id"]').parent().removeClass('is-invalid');
                        $('input[name="card_id"]').parent().next('.error-message').remove();
                    }
                
                    if (!address) {
                        $("#error-message").html('Please Add Address First');
                        hasError = true;
                    } else {
                        $("#error-message").html('');
                    }
                
                    if (!hasError) {
                        let formData = new FormData(this);
                        formData.append('card_id', selectedCard.val()); // Ensure card_id is appended
                        formData.append('address', address);
                        formData.append('card_number', selectedCard.data('card-number'));
                    
                    $.ajax({
                        url: "{{ route('purchaseTag') }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            $('.invalid-feedback').remove();
                            $('input, select').removeClass('is-invalid');
                            $('#PromoteModals').hide();
                            if (response.status === 'success') {
                                swal({
                                    title: "Success",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                }).then(() => {
                                    window.location.href = "{{ route('stickers') }}";
                                });
                            } else if (response.status == 'failed') {
                                swal({
                                    title: "Sold Out",
                                    text: response.message,
                                    icon: "warning",
                                    button: "Ok",
                                }).then(() => {
                                    window.location.href = "{{ route('stickers') }}";
                                });
                            } else {
                                swal({
                                    title: "Error",
                                    text: "Something went wrong, please try again.",
                                    icon: "error",
                                    button: "Ok",
                                });
                            }
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
            
                                const showErrorsSequentially = (index = 0) => {
                                    if (index < errorMessages.length) {
                                        swal({
                                            text: "Validation Error",
                                            title: errorMessages[index],
                                            icon: "error",
                                            button: "Ok",
                                        }).then(() => {
                                            showErrorsSequentially(index + 1);
                                        });
                                    }
                                };
            
                                showErrorsSequentially();
            
                            } else {
                                alert('Something went wrong, please try again.');
                            }
                        }
                    });
                }
            });

            
            
            
            
            
            $('#editAdress').on('submit', function(e) {
                
                e.preventDefault();
                var hasError = false;

                // Clear any existing error messages
                $('.is-invalid').removeClass('is-invalid');
                $('.error-message').remove();
        
                var name = $('#uname').val();
                var phoneNumber = $('#uphoneNumber').val();
                var houseNo = $('#uhouseNo').val();
                var roadName = $('#uroadName').val();
                var state = $('#ustate').val();
                var zipCode = $('#uzipCode').val();
        
                // Validate Name
                if (!name) {
                    $('#uname').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your name.</span>')
                        .insertAfter($('#uname'));
                    hasError = true;
                }
        
                // Validate Phone Number
                if (!phoneNumber) {
                    $('#uphoneNumber').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your phone number.</span>')
                        .insertAfter($('#uphoneNumber'));
                    hasError = true;
                }
        
                // Validate House Number
                if (!houseNo) {
                    $('#uhouseNo').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your house number.</span>')
                        .insertAfter($('#uhouseNo'));
                    hasError = true;
                }
        
                // Validate Road Name
                if (!roadName) {
                    $('#uroadName').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your road name.</span>')
                        .insertAfter($('#uroadName'));
                    hasError = true;
                }
        
                // Validate State
                if (!state) {
                    $('#ustate').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your state.</span>')
                        .insertAfter($('#ustate'));
                    hasError = true;
                }
        
                // Validate Zip Code
                if (!zipCode) {
                    $('#uzipCode').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter your zip code.</span>')
                        .insertAfter($('#uzipCode'));
                    hasError = true;
                }
        
                if (!hasError) {
                        let formData = new FormData(this);
                        $.ajax({
                            url: "{{ route('updateAddress') }}",
                            type: "POST",
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                console.log(response);
                                $('.invalid-feedback').remove();
                                $('input, select').removeClass('is-invalid');
                                $('#editAddressModals').hide();
                                if (response.status === 'success') {
                                    swal({
                                        title: response.message,
                                        text: "Success",
                                        icon: "success",
                                        confirmButtonText: "Ok",
                                    })
                                    .then(() => {
                                        window.location.href = "{{ route('address') }}";
                                    });
                                } else if (response.status === 'failed' && response.message === 'The provided address is not valid.') {
                                    swal({
                                        title: response.message,
                                        text: "Invalid Address",
                                        icon: "error",
                                        button: "Ok",
                                    });
                                }else {
                                    swal({
                                        title: "Error",
                                        text: "Something went wrong, please try again.",
                                        icon: "error",
                                        button: "Ok",
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.status);
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
                }
            });
            
            
            
        });
        
        
        function editAddress(addressId) {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
               url:"{{ route('editAddress') }}",
               method:"POST",
               data:{addressId : addressId},
               headers: {
                 'X-CSRF-TOKEN': csrfToken 
               },
               success:function(response){  
                   console.log(response.data);
                   if(response.data !== null){
                       const data = response.data;
                    //   console.log(data);
                        $('#id').val(data.id);
                        $('#user_id').val(data.user_id);
                        $('#uname').val(data.name);
                        $('#uphoneNumber').val(data.phone_number);
                        $("#uhouseNo").val(data.house_number);
                        $('#uroadName').val(data.road_name);
                        $('#ustate').val(data.state);
                        $("#uzipCode").val(data.zip_code);
                       $('#editAddressModals').modal('show');
                   }
               }
            });
        }
</script>




@endsection