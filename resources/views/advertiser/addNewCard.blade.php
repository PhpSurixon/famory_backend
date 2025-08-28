@extends('layouts.advertiser-master', ['title' => 'Add New Card','previous'=> '/my-account'])

@section('content')

 <div class="row position-relative">
     <div id="preloaders" class="preloader"></div>
       <div class="col-xl-4 add-new-method offset-xl-4 col-lg-12 col-md-12 col-12">
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
        <form id="payment-form" action="{{ route('storeCardDetails') }}" method="post">
    @csrf
    <div class="mb-3">
        <label for="card-name" class="form-label fw-bold text-dark fs-5">Name</label>
        <input type="text" name="card_name" id="card-name" class="@error('card_name') is-invalid @enderror" placeholder="Enter name">
        @error('card_name')
            <span class="help-block invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="mb-3">
        <label for="card-element" class="form-label fw-bold text-dark fs-5">Card Details</label>
        <div id="card-element">
            <!-- Stripe Element will be inserted here -->
        </div>
        <div id="card-errors" role="alert"></div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn mb-0 bg-info border-radius-section w-100 color-white mt-3">Submit</button>
    </div>
</form>

       </div>
       </div>
<script src="https://js.stripe.com/v3/"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var stripeKey = "{{ config('app.stripe_client_id') }}"; // Your Stripe publishable key
        var stripe = Stripe(stripeKey);
        var elements = stripe.elements();

        var card = elements.create('card');
        card.mount('#card-element');
        // let link-pay = document.getElementById("#link-pay");
        // link-pay.setAttribute("autocomplete", "off");
        
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
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'stripeToken');
                    hiddenInput.setAttribute('value', result.token.id);
                    form.appendChild(hiddenInput);

                    form.submit();
                }
            });
        });
    });
</script>



@endsection