@extends('layouts.advertiser-master', ['title' => 'Trusted Partners','previous' => '/partner/dashboard','addRoute'=>'/addNewPartner'])

@section('content')
<style>
    .text-xs {
        font-weight: 500;
    }
    input[name='card_number']{
        font-weight:700;
        font-size:16px;
    }
    .btn .fa-solid.fa-pen-to-square,
    .btn .fa-solid.fa-trash-can,
    .btn .fa-solid.fa-crown,
    .btn .fa-solid.fa-x{
        font-size:16px;
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
    .modal-content {
        border: 3px solid #4b8cf7;
    }.modal .modal-footer .btn.btn-info {
        margin-left: 321px;
    }
    
    .payment-head {
        padding-bottom: 12px;
        background: aliceblue;
    }
    
    .model-select select {
        border: 1px solid #3d6be1;
    }
    
    .card-one {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
    }
    
    .card-group {
        display: flex!important;
        align-content: flex-start;
        flex-direction: column;
    }
     #t-font thead tr th, td {
        font-family:"Montserrat", sans-serif !important;
    }
    
    .shadow-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    .viewall table tbody #b-none {
        border:none!important;
    }
    .btn-webLink{
        background: #eeeeee;
        border-radius: 7px;
        color: #2174f9 !important;
           width: 100%;
        display: block;
        text-align: center;
        padding: 5px;
    }
    .address{
        text-align: left !important;
    }
</style>
    <div class="row">
    <div class="col-md-12 position-relative">
        <div id="preloaders" class="preloader"></div>
        <div class="paymentBox viewall px-xl-4">
            <div class="instructionContainer card">
                <p class="trustedPartnerInstructions">Have your local business be listed for free!  Each partner is allowed a free listing in our partners page!  Simply put in your company name, address, and category and users within 50 miles of your location will easily find you in the local directory.</p>
                <p class="trustedPartnerInstructions"><b>Want to get to the top?</b>  After creating a free listing you can have your add featured with a simple monthly subscription.</p>
            </div>
            <div class="paymentBox mt-4 viewall">
                
            <table class="table" id="t-font" cellspacing="5">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <!--<th>Logo</th>-->
                        <th>Partners</th>
                        <th>Phone</th>
                        <th>Website</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @if($getTrustedPartners->count() > 0)
                    @foreach ($getTrustedPartners as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <!--<td>-->
                               
                            <!--</td>-->
                            <td>
                                <div class="d-flex aling-items-start gap-2">
                                     @if($data->logo)
                                <div style="width:60px;height:60px;background-image:url('{{$data->logo}}');background-size:cover;background-position:center;">
                                </div>
                                @else
                                <div style="width:60px;height:60px;background-image:url('/assets/img/img_default.png');background-size:cover;background-position:center;">
                                </div>
                                @endif
                                    <div class="content">
                                        {{$data->company_name ?? 'N/A'}}
                                    </div>
                                </div>
                                </td>
                            <td>{{$data->phone ?? 'N/A'}}</td>
                            <td>
                                @if($data->website)
                                <a href="{{$data->website}}" class="btn-webLink" target="_blank"><span style="color:#20c6d9;">Visit Link</span></a>
                                @else
                                 N/A
                                @endif
                            </td>
                            <td class="address">{{ $data->city." , ".$data->state." - ".$data->zip_code  ?? 'N/A'}}</td>
                                <td>
                                
                                <!--<div class="form-check form-switch">-->
                                <!--  <input class="form-check-input" type="checkbox"  role="switch" id="flexSwitchCheckDefault" onchange="makeFeaturedPartner('{{$data->id}}',this)"  >-->
                                <!--</div>-->
                                @if($data->featured_partner != 1)
                                    <button class="btn btn-success btn-sm" onclick="makeFeaturedPartner('{{$data->id}}')" title="Make Featured"><i class="fa-solid fa-crown"></i></button>
                                @elseif($data->featured_partner == 1)
                                    <form id="cancelSubscriptionForm" class="d-inline" onsubmit="cancelSubscription('{{$data->id}}','cancelSubscriptionForm',event)" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" title="Cancel Subscription"><i class="fa-solid fa-x"></i></button>
                                    </form>
                                    <!--<button class="btn btn-info btn-sm" onclick="cancelSubscription('{{$data->id}}')" ><i class="fa-solid fa-x" title="Cancel Subscription"></i></button>-->
                                @endif
                                <a href="{{ route('editPartner', $data->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <form id='destroyPartnerForm' class="d-inline" onsubmit="destroyPartner('{{ route('destroyPartner', $data->id) }}','destroyPartnerForm',event)" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="b-none">
                        <td colspan="7" class="shadow-card text-center">No Record Found</td>
                    </tr>
                @endif
                </tbody>
            </table>
            </div>

            <!-- Pagination at the bottom -->
            {{ $getTrustedPartners->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-white">
                <form method="POST" enctype="multipart/form-data" id="FeaturedPartnersPayment">
                    @csrf
                    <div class="modal-header payment-head" style="padding-bottom: 17px;">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body model-select">
                        <!-- Add your payment form or content here -->
                      
                        <table class="w-100 mt-3">
                            <tr>
                                <label style="font-weight: 600;">Select The Plan</label>
                            </tr>
                            <tr>
                                <select class='form-control'  id="featuredCompanyPrice">
                                    <option value=''>-Please Select The Plan-</option>
                                    
                                    @foreach($getFeaturedCompanyPrice as $price)
                                        <option value="{{ $price }}">{{$price->month}} Months at ${{$price->price}}</option>
                                    @endforeach
                                </select>
                            </tr>
                            <tr>
                                <th style="font-weight: 600; font-size: 16px;">Price</th>
                                <td id="planPrice" class="text-right">--</td>
                            </tr>

                            <tr style="border-bottom: 1px solid #e7e7e7;">
                                <th style="color: #3d3e44; font-weight: 600; font-size: 19px;">Total Price</th>
                                <td id="totalPrice" class="text-right">--</td>
                                <input type="hidden" name="amount" id="hiddenAmount">
                                <input type="hidden" name="partner" id="hiddenPartnerId">
                                <input type="hidden" name="featurePlanId" id="featurePlanId">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer flex-column justify-content-start payment-footer" style="border: none;">
                        <div class="d-flex flex-column w-100 gap-3">
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $i=>$card)
                            <div class="card-group card card-one mb-3">
                            <div class="d-flex" style="min-width: 10rem; gap:7px;">
                                <input type="radio" name="card" value="{{ $card->id }}" style="width: auto;" data-card-number="{{ $card->card->last4 }}" {{ $i === 0 ? 'checked' : '' }}>
                                <!--<input type="hidden" name="card_number" value="{{ $card->card->last4 }}">-->
                                <label class="mb-0" for="card_{{ $card->id }}">**** **** **** {{ $card->card->last4 }}</label>
                                </div>
                                </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="w-100 d-flex justify-content-start mt-1">
                            <a href="{{ route('myAccount') }}" class="btn btn-info border-radius-section color-white" style="margin-bottom: 0;">Add Card</a>
                        </div>

                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-info border-radius-section color-white"
                            style="margin-left:250px;" id="close" data-bs-dismiss="modal" 
                            aria-label="Close">Close</button>
                        <button type="submit" class="btn bg-info border-radius-section color-white" style="background-color:#1A73E8!important;">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    function makeFeaturedPartner(partnerId){
        $('#hiddenPartnerId').val(partnerId);
        $("#paymentModal").modal("show");
    }
    
    function cancelSubscription(partnerId,formId,e){
        e.preventDefault();
        const form = document.getElementById(formId);
       let formData = new FormData(form);
        formData.append('partnerId',partnerId);
        Swal.fire({
          title: "Are you sure?",
          text: "You want to Cancel Subscription!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, Do it!"
        }).then((result) => {
          if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('cancelSubscription') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if(response.status == 'success'){
                            Swal.fire({
                              title: "Done!",
                              text: response.message,
                              icon: "success"
                            }).then((value)=>{
                            window.location.href = "{{ route('trustedpartners') }}";
                            });
                        }else{
                          Swal.fire({
                              title: "Done!",
                              text: response.message,
                              icon: "success"

                            }); 
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $('.invalid-feedback').remove();
                            $('input, select').removeClass('is-invalid');
                            for (let field in errors) {
                                let inputField = '';
                                if(field != 'category'){
                                     inputField = $('input[name="' + field + '"]');
                                }else{
                                     inputField = $(`select[name='${field}']`);
                                }
                                
                                
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
                        // $('#submitButton').show();
                        // $('#preloader').hide();
                        // $('#submitValue').css("display","block");
                    }
                });
          }
        });
    }
</script>
<script>
    function destroyPartner(url,formid,e){
        e.preventDefault();
        const form = document.getElementById(formid);
        let formData = new FormData(form);
        
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!"
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if(response.status == 'success'){
                            Swal.fire({
                              title: "Deleted!",
                              text: response.message,
                              icon: "success",
                              timer:3000,
                            }).then((value)=>{
                            // console.log(value);
                            window.location.href = "{{ route('trustedpartners') }}";
                            });
                        }else{
                           Swal.fire({
                              icon: "error",
                              title: "Oops...",
                              text: "Something went wrong!",
                            }); 
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $('.invalid-feedback').remove();
                            $('input, select').removeClass('is-invalid');
                            for (let field in errors) {
                                let inputField = '';
                                if(field != 'category'){
                                     inputField = $('input[name="' + field + '"]');
                                }else{
                                     inputField = $(`select[name='${field}']`);
                                }
                                
                                
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
                        // $('#submitButton').show();
                        // $('#preloader').hide();
                        // $('#submitValue').css("display","block");
                    }
                });
          }
        });
    }


$("#featuredCompanyPrice").on('change',function(){
    const selectedPlan = $(this).val();
    if(selectedPlan != ''){
        $("#featuredCompanyPrice").next('.error-message').remove();
        const plan = JSON.parse(selectedPlan);
        $('#planPrice').text(`$${plan.price}`)
        $('#totalPrice').text(`$${plan.price}`);
        $('#hiddenAmount').val(plan.price);
        $("#featurePlanId").val(plan.id);
    }else{
        $('#planPrice').text(`$0`)
        $('#totalPrice').text(`$0`);
        $('#hiddenAmount').val('');
        $('<span class="error-message" style="color: red;font-weight: 400;">Please select a Plan.</span>').insertAfter($("#featuredCompanyPrice"));
    }
});

            $('#FeaturedPartnersPayment').on('submit', function(e) {
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
                    url: "{{ route('storeFeaturePartnerPayment') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        // // $('.storeAdBtn').hide();
                        $('#paymentModal').hide();
                        // // window.location.href = "{{ route('advertiser/dashboard') }}";
                        if(response.status == 'success'){
                            Swal.fire({
                                icon: "success",
                                text: response.message,
                                showConfirmButton: true,
                                timer:3000
                            }).then(()=>{
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = [];
                            
                            for (let field in errors) {
                                if (field == 'amount') {
                                    $('<span class="error-message" style="color: red;font-weight: 400;">Please select a Plan.</span>').insertAfter($("#featuredCompanyPrice"));
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

</script>

@endsection