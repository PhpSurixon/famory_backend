@extends('layouts.admin-master', ['title' => 'User Details With All Orders'])

@section('content')
<style>
    td,th{
        text-align:center;
    }
    .imageContrainer{
        width:50px;
        height:50px;
        background-position:center;
        border-radius:50%;
        background-size:cover;
        background-repeat:no-repeat;
    }
    .dataTables_scroll{
        overflow:auto;
    }
    #data-table th,
    #data-table td{
        text-align:left !important;
    }
    #userDetails tr td:nth-child(1){
        color:#1550ae;
        font-weight:800;
        font-size:16px;
    }
    #userDetails tr td:nth-child(2){
        font-size:16px;
        font-weight:500;
    }
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-between">User's Details</h5>

                <div class="table-responsive text-nowrap pl-3 pr-3 pt-0 pb-0">
                    <div class="card-datatable table-responsive" id="userDetails">
                        <h5></h5>
                        <table class="table table-bordered">
                            @php 
                                $user=$stickerPurchase[0]->user;
                            @endphp
                                <tbody>
                                    <tr>
                                        <td class="text-center">User Name</td>
                                        <td class="text-center">{{ $user->first_name }} {{ $user->last_name }} </td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td>
                                        <td>{{ $user->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>Stripe Id</td>
                                        <td>{{$user->stripe_customer_id}}</td>
                                    </tr>
                                    <tr>
                                        <td>Image</th>
                                        <td class="d-flex justify-content-center"><a href="{{$user->image}}" target="_blank"><div class="imageContrainer" style="background-image:url('{{$user->image}}')"></div></a></td>
                                    </tr>
                                    @if($user->trashed())
                                    <tr>
                                        <td>Status</th>
                                        <td class="d-flex justify-content-center"><span class="text-danger font-bold">Deleted</span></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                    </div>
                </div>
            
            
            
                <h5 class="card-header d-flex align-items-center justify-content-between">All Order's</h5>
                    <div class="card-datatable table-responsive p-3">
                        <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Id</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                  @foreach ($stickerPurchase as $key => $data)
                                    @if(isset($data->products[0]))
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->order_id }}</td>
                                        
                                        <td>
                                       
                                            <div class="imageContrainer" style="background-image:url('{{$data->products[0]->image}}')"></div>
                                     
                                      
                                        </td>
                                        <td>{{$data->products[0]->name}}</td>
                                        <td>{{$data->products[0]->price }}</td>
                                        <td>{{$data->quantity}}</td>
                                    </tr>
                                  @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                
            </div>
        </div>
    </div>
</div>
@endsection


