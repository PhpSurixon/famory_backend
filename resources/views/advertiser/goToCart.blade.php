
@extends('layouts.advertiser-master', ['title' => 'Cart'])

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
    
    .input-group.quantity-selector {
    border: 1px solid #1a73e8;
    border-radius: 50px;
    padding: 4px;
    margin: 5px 0;
    width: 100% !important;
    max-width: 120px;
}
    
    .input-group.quantity-selector .btn {
   position: relative;
    z-index: 2;
    padding: 0px;
    width: 34px;
    height: 34px;
    border-radius: 50px;
    font-size: 20px;
    line-height: 0;
    background: #1a73e8;
    color: #fff;
    font-weight: 400;
    border: none;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.input-group.quantity-selector .btn i{
    font-size:12px;
}
.input-group.quantity-selector input {
    width: 100%;
    margin: 0;
    font-weight: 600;
}

.swal-footer{
        text-align:center;
    }
.swal-modal .swal-text{
    text-align: center;
    max-width: 400px;
}
</style>
    <div class="container">
    <div class="row position-relative">
        <div id="preloaders" class="preloader"></div>
        <!-- Cart Items -->
        <div class="col-lg-8 col-md-7 col-sm-12">
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
            @if($datas->count() > 0)
                @foreach($datas as $data)
                <ul class="list-group">
                    <li class="list-group-item d-flex align-items-start border-0 border-radius p-3 mb-3 shadow">
                        <!-- Product Image -->
                        <div class="avatar me-3">
                            <img src="{{ $data->product['image'] ?? '/assets/img/img_default.png' }}" alt="product image" class="border-radius-lg" style="">
                        </div>
                        <!-- Product Details -->
                        <div class="d-flex flex-column justify-content-center flex-fill">
                            
                            <h5 class="mb-2">{{ $data->product['name'] }}</h5>
                            <p class="mb-2 text-xs price-per-unit">Price: ${{ number_format((float)$data->product['price'], 2, '.', '') }}</p>
                            <p class="mb-2 text-xs">Quantity: {{ $data->quantity }}</p>
                            <input type="hidden" class="product-count" value="{{ $data->product['count'] }}">
                            <p class="mb-2 text-xs">Details: {{ $data->product['description'] }}</p>
                            <p class="mb-2 text-xs total-price">Total Price: ${{ ($data->product['price'] * $data->quantity) }}.00</p>
                            <!-- Quantity Selector -->
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="input-group quantity-selector mt-2" style="width: 120px;">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary decrement-btn" type="button"> 
                                   <i class="fa-solid fa-minus"></i>
                                    </button>
                                </div>
                                <input type="text" class="form-control text-center quantity-input add_cart_quantity" value="{{ $data->quantity }}" readonly>
                                <input type="hidden" value="{{$data->id}}" class="add_cart_id" data-product_id = "{{ $data->product['id'] }}"  data-amount="{{ $data->product['price'] }}"/>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary increment-btn" type="button"> 
                                   <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-info border-radius-section color-white m-0 removeCart" data-id="{{ $data->id }}">Remove</button>
                            </div>
                        </div>
                    </li>
                </ul>
                @endforeach
        </div>
            @endif
            
     @if($datas->count() > 0)
        <!-- Price Details -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="card shadow p-3">
                <h5 class="mb-2">PRICE DETAILS</h5>
                <p id="item-count" class="m-0">Items: {{ count($datas) }}</p>
                <!--<p id="total-quantity">Total Quantity: </p>-->
                <p id="amount" class="m-0">Amount: $0.00</p>
                <hr style="width:100%;height:1px;background:#888;">
                <h6 id="grand-total">Total Amount: $0.00</h6>
                <button id="place-order" class="btn btn-info border-radius-section color-white m-0">PLACE ORDER</button>
                <!--<a href="#" class="btn btn-primary btn-block">PLACE ORDER</a>-->
            </div>
        </div>
        @endif
    </div>
    
    @if($datas->count() <= 0)
       <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <img src="{{ asset('assets/img/emptyCart.gif') }}">
                </div>
                <h6 class="text-info text-center">Your Cart is Empty</h6>
            </div>
       </div>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.increment-btn').click(function() {
            var $item = $(this).closest('.quantity-selector');
            var $input = $item.find('.quantity-input');
            var value = parseInt($input.val());
            var total_quantity = parseInt($item.closest('li').find('.product-count').val()); // Get the total quantity for this item
            value = isNaN(value) ? 0 : value;

            // Ensure the increment does not exceed the total_quantity
            if (value + 1 <= total_quantity) {
                $input.val(value + 1);
                console.log("increment-btn==>", value + 1); // Log the new value
                updateTotalPrice($item.closest('li'));
            } else {
                console.log("Maximum quantity reached:", total_quantity);
            }
        });

        $('.decrement-btn').click(function() {
            var $item = $(this).closest('.quantity-selector');
            var $input = $item.find('.quantity-input');
            var value = parseInt($input.val());
            value = isNaN(value) ? 0 : value;

            if (value > 1) {
                $input.val(value - 1);
                console.log("decrement-btn==>", value - 1);
                updateTotalPrice($item.closest('li'));
            }
        });

        function updateTotalPrice($item) {
            var pricePerUnit = parseFloat($item.find('.price-per-unit').text().replace('Price: $', ''));
            var quantity = parseInt($item.find('.quantity-input').val());
    
            if (!isNaN(pricePerUnit) && !isNaN(quantity)) {
                var totalPrice = pricePerUnit * quantity;
                console.log("Total Price Calculated:",totalPrice);
                updateGrandTotal();
                $item.find('.total-price').text('Total Price: $' + totalPrice.toFixed(2));
            } else {
                console.error("Error calculating total price: pricePerUnit or quantity is NaN");
            }
        }
        
        
        function updateGrandTotal() {
            var grandTotal = 0;
            
            $('.list-group-item').each(function() {
                var $item = $(this);
                var pricePerUnit = parseFloat($item.find('.price-per-unit').text().replace('Price: $', ''));
                var quantity = parseInt($item.find('.quantity-input').val());
                
                if (!isNaN(pricePerUnit) && !isNaN(quantity)) {
                    grandTotal += pricePerUnit * quantity;
                }
            });
    
            console.log("Grand Total Amount:", grandTotal);
            $('#amount').text('Amount: $' + grandTotal.toFixed(2));
            $('#grand-total').text('Total Amount: $' + grandTotal.toFixed(2));
            
            
            
            
        }

        // Initial update of total price for each item
        $('.quantity-selector').each(function() {
            updateTotalPrice($(this).closest('li'));
        });
        
        
        $('.removeCart').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            console.log("Id", id);

            // Show SweetAlert confirmation
            swal({
                title: 'Are you sure?',
                // text: 'You won\'t be able to revert this!',
                text: 'Are you sure you want to remove this item from your cart?',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: null,
                        visible: true,
                        closeModal: true,
                    },
                    confirm: {
                        text: "Remove",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    }
                },
            }).then((result) => {
                if (result) {
                    // Perform AJAX request after confirmation
                    $.ajax({
                        url: "{{ route('remove-add-product') }}",
                        type: "POST",
                        data: {
                            id: id, // Send the ID as a key-value pair
                            _token: "{{ csrf_token() }}" // Include CSRF token for Laravel
                        },
                        success: function(response) {
                            console.log("Successfully removed", response);
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error removing item", error);
                        }
                    });
                }
            });
        });
        
        
        $('#place-order').on('click', function(e) {
            e.preventDefault();
    
            var totalAmount = $('#grand-total').text().replace('Total Amount: $', '');
            var itemCount = $('#item-count').text().replace('Items: ', '');
            
            var items = [];
            $('.add_cart_id').each(function() {
                var id = $(this).val();
                var product_id = $(this).data('product_id');
                var quantity = $(this).siblings('.add_cart_quantity').val();
                var amount = $(this).data('amount');
                items.push({ cart_id: id, quantity: quantity, productId : product_id, amount: amount });
            });
            if(items.length > 0){
                $.ajax({
                    url: '{{ route("storeOrder") }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        total_amount: totalAmount,
                        items: items,
                        item_count: itemCount
                    
                    },
                    success: function(response) {
                        console.log('Total amount stored successfully.');
                        window.location.href = "{{ route('address') }}";
                    },
                    error: function(xhr) {
                        console.error('Error storing total amount:', xhr.responseText);
                    }
                });
            }
            
        });
        
        
    });
    
    
</script>

@endsection