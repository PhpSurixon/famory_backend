@extends('layouts.advertiser-master', ['title' => 'My Account','previous'=> '/partner/dashboard'] )


<style>
    .myaccount table tbody tr {
    background: #fff;
    border-bottom: 5px solid #eee !important;
}

.myaccount table tbody tr:last-child {
    border-bottom: none !important;
}

 .shadow-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    #dynamicSearch table tr th, td {
        font-family:"Montserrat", sans-serif !important;
    }


</style>
@section('content')
<div class="row">
    <div class='col-12 position-relative'>
        <div id="preloaders" class="preloader"></div>


<div class="row ">

          <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-4">
              
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
            <div class="d-flex gap-3 mb-4 scrollCards">
              <button class="btn mb-0 bg-white border-radius-section">
                <h6>Monthly</h6>
                Views: <span> {{$totalViews ?? '--'}}</span></button>
              <button class="btn mb-0 bg-white border-radius-section">
                <h6>Monthly</h6>
                Clicks: <span> {{$totalClicks??'--'}}</span></button>
              <button class="btn mb-0 bg-white border-radius-section">
                <h6>Monthly</h6>
                Conversions: <span> {{$totalWebsite??'--'}}</span></button>
            </div>

            <ul class="list-group">

              <li class="list-group-item border-0 d-flex align-items-center px-2 mb-2 shadow-card">
                <div class="avatar me-3 profile-image">
                @if($user->company_logo)
                    <img src="{{ $user->company_logo }}" alt="Company Logo" class="border-radius-lg shadow">
                @else
                    <img src="{{ asset('assets/img/famcam.jpg') }}" class="card-img-top border-radius-lg shadow" alt="...">
                @endif

                </div>
                <div class="d-flex align-items-start flex-column justify-content-center">
                <h6 class="mb-2 text-sm">{{ $user->first_name . " " . $user->last_name ?? "" }}</h6>
                <p class="mb-2 text-xs">{{ $user->email ?? ""}}</p>
                <p class="mb-0 text-xs">{{ $user->company_address ?? ""}}</p>
                </div>
              </li>

            </ul>

              <!-- <button class="btn mb-0 bg-info border-radius-section w-25 color-white mt-3">New Ad</button> -->
          </div>
        </div>
        <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-3">
            
        <div class="paymentBox my-4 myaccount shadow-card">

         <h5 class="d-flex justify-content-between ">Payment <span><a href="{{ route('allPayments') }}" style="color:#2863db !important;">View all</a></span></h5>

         <div class="form-outline mb-4" data-mdb-input-init>
            <input type="search" class="form-control" placeholder="Search" name="search" />
         </div>          
         <div id="dynamicSearch">
          <table class="table mb-0" cellspacing="5">
            <thead style="border: 0;">
              <tr>
                <th scope="col">Ad Name</th>
                <th scope="col">Date</th>
                <th scope="col">Amount</th>
                <th scope="col">Renewal Date</th>
                <th scope="col">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                @if($transactionHistory->count() > 0)
                    @foreach($transactionHistory as $data)
                     <tr>
                        <td>{{ $data->ad->ad_name ?? 'N/A'}}</td>
                        <td>{{ \Carbon\Carbon::parse($data->ad->start_date)->format('m/d/y') ?? 'N/A'}}</td>
                        <td>${{number_format((float)$data->amount, 2, '.', '' ?? 'N/A')}}</td>
                        <td>{{ isset($data->ad->renew_date) ? \Carbon\Carbon::parse($data->ad->renew_date)->format('m/d/y') : '-' }}</td>

                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align:center;">No Transaction History</td>
                    </tr>
                @endif
            </tbody>
          </table>
         </div>
        </div>
        </div>

 <div class="col-xl-12 col-sm-6 mb-xl-0 mb-4 px-3">
     
       <div class="payment__method__wrapper shadow-card ">
            <div class="payment__method">
          <h5 class="d-flex justify-content-between">Payment Method</h5>
        </div>
        <div class="row payment g-3 py-3">
             @if(is_array($cards) || is_object($cards))
                @foreach ($cards as $card)
                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card shadow-sm p-3 bg-body rounded px-3 py-3 rounded">
                        <div class=" d-flex justify-content-between align-items-center">
                            <div class="both d-flex align-items-center" style="gap: 11px;">
                                <span><i class="far fa-credit-card"></i></span>
                                <span>{{ $card->card->last4 ?? "Not Added" }}</span>
                            </div>
                            <span class="fs-4"><i class="fa-solid fa-trash-can text-danger cursor-pointer" aria-hidden="true" onclick="event.preventDefault();document.getElementById('deleteCard-form{{ $card->id }}').submit();"></i></span>
                        </div>
                    </div>
                </div>
                <form id="deleteCard-form{{ $card->id }}" action="{{ route('deleteCard') }}" method="POST" class="d-none">
                @csrf 
                
                <input type="hidden" name="card_id" value="{{ $card->id ?? "" }}" >
                </form>
                
                @endforeach
                @else
                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card shadow-sm p-3 bg-body rounded px-3 py-3">
                        <div class=" d-flex justify-content-between align-items-center">
                            <div class="both d-flex align-items-center" style="gap: 11px;">
                                <span><i class="far fa-credit-card"></i></span>
                                <span>No Card Added</span>
                            </div>
                            <!--<span class="fs-4"><i class="fas fa-trash text-danger cursor-pointer" aria-hidden="true" onclick="event.preventDefault();document.getElementById('deleteCard-form2').submit();"></i></span>-->
                        </div>
                    </div>
                </div>
            @endif
                
                
                <!--@if(count($user->stripeDetails) <= 0)-->
                <!--<div class="col-12 col-sm-12 col-md-4 col-lg-4">-->
                <!--    <div class="card shadow-sm p-3 bg-body rounded px-4 py-4">-->
                <!--        <div class=" d-flex justify-content-between align-items-center">-->
                <!--            <div class="both d-flex align-items-center" style="gap: 11px;">-->
                <!--                <span><i class="far fa-credit-card"></i></span>-->
                <!--                <span>No Card Added</span>-->
                <!--            </div>-->
                            <!--<span class="fs-4"><i class="fas fa-trash text-danger cursor-pointer" aria-hidden="true" onclick="event.preventDefault();document.getElementById('deleteCard-form2').submit();"></i></span>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                <!--@else-->
                <!--    @foreach($user->stripeDetails as $stripe)-->
                <!--        @php $res_detail = json_decode($stripe->res_detail); @endphp-->
                <!--        <div class="col-12 col-sm-12 col-md-4 col-lg-4">-->
                <!--            <div class="card shadow-sm p-3 bg-body rounded px-4 py-4">-->
                <!--                <div class=" d-flex justify-content-between align-items-center">-->
                <!--                    <div class="both d-flex align-items-center" style="gap: 11px;">-->
                <!--                        <span><i class="far fa-credit-card"></i></span>-->
                <!--                        <span>{{ $res_detail->last4 ?? "Not Added" }}</span>-->
                <!--                    </div>-->
                <!--                    <span class="fs-4"><i class="fas fa-trash text-danger cursor-pointer" aria-hidden="true" onclick="event.preventDefault();document.getElementById('deleteCard-form{{$stripe->id}}').submit();"></i></span>-->
                <!--                </div>-->
                <!--            </div>-->
                <!--        </div>-->
                <!--        <form id="deleteCard-form{{$stripe->id}}" action="{{ route('deleteCard') }}" method="POST" class="d-none">-->
                <!--        @csrf -->
                        
                <!--        <input type="hidden" name="card_id" value="{{ $stripe->stripe_card_id ?? '' }}" >-->
                <!--        </form>-->
                <!--    @endforeach-->
                <!--@endif-->
                
                
                    <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <a href="{{ route('addNewCard') }}">
                        <div class="card shadow-sm p-3 bg-body rounded px-3 py-3">
                            <div class=" d-flex justify-content-between align-items-center">
                                <div class="both d-flex align-items-center" style="gap: 11px;">
                                    <span><i class="fas fa-plus-circle "></i></span>
                                    <span>Add New Card</span>
                                </div>
                            </div>
                        </div>
                </a>
                    </div>
            </div>
       </div>
 </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
   

    const search = document.querySelector('input[name="search"]');
    // console.log(search);
    

    const debounceSearch = (func, delay) => {
        let debounceTimer
        return function () {
            const context = this
            const args = arguments
            clearTimeout(debounceTimer)
            debounceTimer = setTimeout(() => func.apply(context, args), delay)
        }
    }
    
    search.addEventListener("input", debounceSearch(function () {
    
        $.ajax({
            method:"GET",
            url:"{{ route('search-payment-my-account')  }}",
            data:{search:this.value},
            success:function(response){
                // console.log(response);
                
                const dynamicSearch = document.getElementById('dynamicSearch');
                dynamicSearch.innerHTML = response.data;
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
      
    },800));
</script>  

@endsection