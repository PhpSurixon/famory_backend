@extends('layouts.advertiser-master', ['title' =>
'Selected Famory Tag','previous'=> '/stickers'])

@section('content')
<style>
    .text-sm {
        /* font-size: 0.875rem !important; */
        font-weight: 600;
        font-size: 16px;
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
    .imageBox{
        position: relative;
        padding-top: 82%;
        width: 100%;
    }
    .imageBox img{
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 10px;
    }
    ul li h6{
        color: #000 !important;
        font-weight: 700 !important;
    }
    ul li p{
        font-weight: 400 !important;
    }
    .swal-footer{
        text-align:center;
    }
    
</style>

<div class="row">
  <div class="col-xl-12 col-sm-12 mb-xl-0 mb-4 pe-4 position-relative">
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
    <!--<div class="d-flex gap-3 mb-4">-->
    <!--  <button class="btn mb-0 bg-white border-radius-section">Views: <span>1000</span></button>-->
    <!--  <button class="btn mb-0 bg-white border-radius-section">Clicks: <span>1000</span></button>-->
    <!--  <button class="btn mb-0 bg-white border-radius-section">Conversions: <span>1000</span></button>-->
    <!--</div>-->
    <div class="row ">
        <div class="col-xl-5">
            
          <div class="d-flex justify-content-center">
              <div class="imageBox text-center">
                      <img src="{{ $data->image ?? '/assets/img/img_default.png'}}" class=""/ >
              </div>
          </div>
        </div>
        <div class="col-xl-7">
            
    <ul class="list-group bg-white p-3">
     

      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex  w-100">
            <h6 class="mb-0 text-sm">Name</h6>
            <p class="mb-0 text-sm" style="margin-left:8px;"> {{ $data->name }} </p>
          </div>
        </div>

      </li>
      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex  w-100">
            <h6 class="mb-0 text-sm">Type Of Tag</h6>
            <p class="mb-0 text-sm" style="margin-left:8px;"> {{ ucfirst($data->type_of_tag) }} </p>
          </div>
        </div>

      </li>
      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex  w-100">
            <h6 class="mb-0 text-sm">Color</h6>
            <p class="mb-0 text-sm" style="margin-left:8px;"> {{ ucfirst($data->color) }} </p>
          </div>
        </div>

      </li>
      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex  w-100">
            <h6 class="mb-0 text-sm">Price</h6>
            <p class="mb-0 text-sm" style="margin-left:8px;">${{ number_format((float)$data->price, 2, '.', '')  }}</p>
          </div>
        </div>

      </li>
      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex  w-100">
            <h6 class="mb-0 text-sm">Total Quantity</h6>
            <p class="mb-0 ml-1 text-sm" style="margin-left:8px;">{{ $data->count }}</p>
          </div>
        </div>

      </li>
      <li class="list-group-item border-0 px-3 py-2">

        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class=" w-100">
            <h6 class="mb-0 text-sm">Description</h6>
            <p class="mb-0 text-sm">{{ $data->description }}</p>
          </div>
        </div>
        </li>
         <li class="list-group-item border-0 px-3 py-2">
        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class=" w-100">
            <h6 class="mb-0 text-sm">Tag Purpose</h6>
            <p class="mb-0 text-sm">{{ $data->tag_purpose }}</p>
          </div>
        </div>

      </li>
      @if($isExist == "true")
      
      <li class="list-group-item border-0 px-3 py-2">
        <div
          class="d-flex align-items-start flex-column justify-content-center">
          <div class="d-flex justify-content-between w-100">
            <!--<h6 class="text-sm">Add</h6>-->
            <p class="text-sm"><a href="{{route('goToCart')}}" class="btn btn-info d-flex align-items-center gap-2 justify-content-center"><i class="fa fa-shopping-cart" aria-hidden="true"></i>Go To Cart</a></p>
          </div>
        </div>

      </li>
      @else
          <li class="list-group-item border-0 px-3 py-2">
            <div
              class="d-flex align-items-start flex-column justify-content-center">
              <div class="d-flex justify-content-start w-100 gap-3">
                <!--<h6 class="text-sm">Add</h6>-->
                <!--<p class="text-sm m-0"></p>-->
                <button class="btn btn-warning m-0 " id="addCart"><i class="fa fa-shopping-cart" aria-hidden="true"></i> <span>Add to cart</span></button>
                <!--<button class="btn btn-info m-0" id="paymentButton"><i class="fa fa-bolt" aria-hidden="true"></i> Payment</button>-->
              </div>
            </div>
    
          </li>
      @endif
      
      
      <!--<li class="list-group-item border-0 px-3 py-2">-->
      <!--  <div-->
      <!--    class="d-flex align-items-start flex-column justify-content-center">-->
      <!--    <div class="d-flex justify-content-between w-100">-->
      <!--      <h6 class="mb-0 text-sm">Purchase</h6>-->
      <!--      <p class="mb-0 text-sm m-0"></p>-->
      <!--    </div>-->
      <!--  </div>-->

      <!--</li>-->

    </ul>
        </div>
    </div>

    <!--<a href="{{ route('newAdView') }}" class="btn mb-0 bg-info border-radius-100 w-25 color-white mt-3">New Ad</a>-->

  </div>
</div>

<div class="modal fade" id="PromoteModals" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="Payment">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Add your payment form or content here -->
                        <input type="hidden" name="product_id" id="" value={{$data->id}}>
                        <table>
                            <tr>
                                <th>Sticker Price</th>
                                <td id="price">${{ $data->price }}</td>
                            </tr>
                            <tr>
                                <th>Total Quantity</th>
                                <td id="count">{{ $data->count }}</td>
                            </tr>
                            <tr>
                                <th>Count</th>
                                <td id="totalDays">
                                    <input type="number" class="form-control box" name="quantity" id="countInput" />
                                    <span id="error-message" style="color: red; display: none;">Value cannot be greater than the total quantity.</span>
                                </td>
                            </tr>

                            <tr>
                                <th>Total Price</th>
                                <td id="totalPrice">--</td>
                                <input type="hidden" name="amount" id="hiddenAmount">
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex align-items-center ad-new gap-2">
                            
                            @if(is_array($cards) || is_object($cards))
                            @foreach ($cards as $card)
                                <input type="radio" name="card_id" value="{{ $card->id }}">
                                <input type="hidden" name="card_number" value="{{ $card->card->last4 }}">
                                <label class="mb-0" for="card_{{ $card->id }}">**** **** ****
                                    {{ $card->card->last4 }}</label>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-info border-radius-section color-white"
                            style="margin-left:250px;" id="close" data-bs-dismiss="modal"
                            aria-label="Close">Close</button>
                        <button type="submit" class="btn bg-info border-radius-section color-white">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
             $('#paymentButton').on('click', function() {
                $('#PromoteModals').modal('show');
            });
            
            const totalQuantity = parseInt($('#count').text().trim(), 10);
            const price = parseFloat($('#price').text().replace('$', '').trim());
    
            $('#countInput').on('input', function() {
                const $this = $(this);
                const $errorMessage = $('#error-message');
                const inputValue = parseInt($this.val(), 10);
    
                if (inputValue > totalQuantity) {
                    $this.val(totalQuantity);
                    $errorMessage.text(`Value cannot be greater than ${totalQuantity}.`);
                    $errorMessage.show();
                    $('#totalPrice').text('--');
                } else {
                    $errorMessage.hide();
                    // Calculate total price
                    const totalPrice = inputValue * price;
                    if (!isNaN(totalPrice)) {
                        console.log("Price===>", totalPrice);
                        $('#totalPrice').text(`$${totalPrice.toFixed(2)}`);
                        $('#hiddenAmount').val(totalPrice.toFixed(2));
                    }else{
                        $('#totalPrice').text('--');
                    }
                }
                
            });
            
             $('#Payment').on('submit', function(e) {
                e.preventDefault();
                
                let countValue = $('#countInput').val();
                let cardNumber = $('input[name="card_id"]:checked').siblings('input[name="card_number"]').val();
                let hasError = false;
            
                // Clear previous error messages
                $('.error-message').remove();
            
                // Check if the count is empty or zero
                if (!countValue || countValue <= 0) {
                    $('#countInput').addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please enter a valid count.</span>')
                        .insertAfter('#countInput');
                    hasError = true;
                } else {
                    $('#countInput').removeClass('is-invalid');
                }
            
                // Check if a card is selected
                if (!cardNumber) {
                    $('input[name="card_id"]').parent().addClass('is-invalid');
                    $('<span class="error-message" style="color: red;font-weight: 400;">Please select a card.</span>')
                        .insertAfter($('input[name="card_id"]').parent().last());
                    hasError = true;
                } else {
                    $('input[name="card_id"]').parent().removeClass('is-invalid');
                }
            
                if (!hasError) {
                    // If no errors, proceed with form submission or AJAX call
                    let formData = new FormData(this);
                   
                    $.ajax({
                    url: "{{ route('purchaseSticker') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        $('#PromoteModals').hide();
                        console.log(response.status);
                        if (response.status == 'success') {
                            swal({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "Ok",
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
            
            $('#addCart').on('click', function(e) {
                e.preventDefault();
                var dataId = "{{ $data->id }}";
                $.ajax({
                    url: "{{ route('add-to-cart') }}",
                    type: "POST",
                   data: {
                        product_id: dataId, // Send the ID as a key-value pair
                        _token: "{{ csrf_token() }}" // Include CSRF token for Laravel
                    },
                    beforeSend: function() {
                        $('#addCart').prop('disabled', true);
                        console.log("AJAX request is about to be sent");
                    },
                    
                    success: function(response) {
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        $('#PromoteModals').hide();
                        if (response.status === 'success') {
                            swal({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "Ok",
                            }).then(() => {
                                // window.location.href = "{{ route('stickers') }}";
                                window.location.reload();
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
            });
            
            
        });
    </script>
@endsection

    