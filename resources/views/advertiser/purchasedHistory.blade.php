@extends('layouts.advertiser-master', ['title' => 'Purchase History','previous'=> '/stickers'])

@section('content')
<style>
    .hidden {
        display:none;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    
    .shadow-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
        .viewall table tbody #b-none {
        border:none!important;
    }
    
    #for-font thead tr th, td {
        font-family:"Montserrat", sans-serif !important;
    }
</style>
<div class="row">
    <div class="col-md-12 position-relative px-4">
        <div id="preloaders" class="preloader"></div>
        <div class="paymentBox mt-4 viewall">
            <!--<h5>Payment</h5>-->

            <table class="table" id="for-font" cellspacing="5">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        
                        <th>Famory Tag Name</th>
                        <th>Purchased Quantity</th>
                        <th>Total Amount</th>
                        <th>Card Number</th>
                        
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @if($transactionData->count() > 0)
                    @foreach ($transactionData as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            
                           <td>{{ ucfirst($data->product['name'] ?? 'N/A') }}</td>
                            <td>{{$data->sticker['quantity'] ?? 'N/A'}}</td>
                            <td> ${{ number_format($data->amount, 2) ?? 'N/A'}}</td>
                            <td>**** {{$data->source ?? 'N/A'}}</td>
                            
                            <td>{{\Carbon\Carbon::parse($data->created_at)->format('m/d/y') ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr id="b-none">
                        <td colspan="8"  class="shadow-card text-center">No Record Found</td>
                    </tr>
                @endif
                </tbody>
            </table>

            <!-- Pagination at the bottom -->
            {{ $transactionData->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
