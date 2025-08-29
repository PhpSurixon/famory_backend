@extends('layouts.advertiser-master', ['title' => 'Orders','previous' => '/stickers'])

@section('content')
<style>
    
    .btn .fa-solid.fa-file-invoice{
        font-size:16px;
    }
    
    .img-container{
        width:60px;
        height:60px;
        background-size:cover;
        background-position:center;
    }
    
    #for-font thead tr th, td {
        font-family:"Montserrat", sans-serif !important;
    }
    
    .shadow-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    .viewall table tbody #b-none {
        border:none!important;
    }
    
</style>

     
<!--@php-->
<!--    dd($orders);-->
<!--@endphp-->

<div class="row">
    <div class="col-md-12 position-relative">
        <div id="preloaders" class="preloader"></div>
        <div class="paymentBox mt-0 viewall px-xl-4">
            <table class="table" id="for-font" cellspacing="5">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Order Id</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @if($orders->count() > 0)
                    @foreach ($orders as $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                
                        <td>
                            <div class="img-container" 
                                 style="background-image: url('{{ $data->products[0]->image ?? '/assets/img/img_default.png' }}');">
                            </div>
                        </td>
                
                        <td>{{ $data->products[0]->name ?? 'N/A' }}</td>
                        <td>{{ $data->order_id ?? 'N/A' }}</td>
                        <td>{{ $data->quantity ?? 'N/A' }}</td>
                        <td>
                            <span class="{{ $data->order_status == 'waiting' || $data->order_status == '' ? 'text-info':'' }} 
                                         {{ $data->order_status == 'order received' ? 'text-warning':'' }} 
                                         {{ $data->order_status == 'order sent' ? 'text-success':'' }}">
                                {{ $data->order_status == '' ? 'Waiting' : ucfirst($data->order_status) }}
                            </span>
                        </td>
                        <td>
                             @if(isset($data->products[0]) && $data->id)
                                <form action="{{ route('invoice') }}" class="d-inline" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $data->id }}">
                                    <button type="submit" class="btn btn-info btn-sm" title="Invoice">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </button>
                                </form>
                            @else
                                N/A
                            @endif
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

            <!-- Pagination at the bottom -->
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>


@endsection