@extends('layouts.admin-master', ['title' => 'Order Detail'])

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-between">View Order Detail</h5>

                <div class="table-responsive text-nowrap">
                        @error('shipTrackingId')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                        @enderror
                    <div class="card-datatable table-responsive">
                        <form action="{{ route('update-order-status', $data->id) }}" method="POST">
                            @csrf
                            <table class="table table-bordered">
                                <thead>
                                    <th class="text-center">Order Id</th>
                                    <th class="text-center">Product Name</th>
                                    <th class="text-center">User Name</th>
                                     <th class="text-center">Shipping Address</th>
                                     <th class="text-center">Quantity</th>
                                     <th class="text-center">Order Status</th>
                                      <th class="text-center">Change Order Status</th>
                                </thead>
                                <tbody>
                                    <tr>
                                       
                                        <td class="text-center">{{$data->order_id}}</td>
                                        <td class="text-center">{{$data->product->name}}</td>
                                        <td class="text-center"><a href="{{ route('get-user-detail-with-order',$data->user->id) }}" class="text-info" style="cursor:pointer;">{{ $data->user->first_name }} {{ $data->user->last_name }}</a></td>
                                        <td class="text-center">{{$data->address->house_number}}, {{$data->address->road_name}}, {{$data->address->state}}, {{$data->address->zip_code}}</td>
                                        <td class="text-center">{{$data->quantity ?? '-'}}</td>
                                        <td class="text-center"><span class="{{ $data->order_status == 'waiting' || $data->order_status == '' ? 'text-info':'' }} {{ $data->order_status == 'order received' ? 'text-primary':'' }} {{ $data->order_status == 'order sent' ? 'text-success':'' }}">{{$data->order_status == '' ? 'Waiting':ucfirst($data->order_status)}}</span></td>
                                        <td class="text-center">
                                            <select name="order_status" class="form-select" onchange="getOrderStatus(this)" @if($data->order_status == 'order sent') disabled @endif >
                                                @if($data->order_status == 'waiting' || $data->order_status == '')
                                                <option value="waiting" {{ $data->order_status == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                                <option value="order received" {{ $data->order_status == 'order received' ? 'selected' : '' }}>Order Received</option>
                                                <option value="order sent" {{ $data->order_status == 'order sent' ? 'selected' : '' }}>Order Sent</option>
                                                @elseif($data->order_status == 'order received')
                                                <option value="order received" {{ $data->order_status == 'order received' ? 'selected' : '' }}>Order Received</option>
                                                <option value="order sent" {{ $data->order_status == 'order sent' ? 'selected' : '' }}>Order Sent</option>
                                                @elseif($data->order_status == 'order sent')
                                                <option value="order sent" {{ $data->order_status == 'order sent' ? 'selected' : '' }}>Order Sent</option>
                                                @endif
                                            </select>
                                            <input type="text" class="form-control {{ $data->order_status == 'order sent' ? 'd-block':'d-none' }}" @if($data->order_status == 'order sent') readonly @endif name="shipTrackingId" placeholder="Ship Tracking Id" value="{{ $data->shipping_tracking_number }}">
                                        </td>
                                    </tr>
                                    <!--<tr>-->
                                        
                                        
                                    <!--</tr>-->
                                    <!--<tr>-->
                                        
                                        
                                    <!--</tr>-->
                                    <!--<tr>-->
                                       
                                        
                                    <!--</tr>-->
                                    <!--<tr>-->
                                        
                                        
                                    <!--</tr>-->
                                    <!--<tr>-->
                                        
                                        
                                    <!--</tr>-->
                                    <!--<tr>-->
                                       
                                        
                                    <!--</tr>-->
                                </tbody>
                            </table>
                            <br/>
                            <div class="button-container d-flex align-items-center justify-content-end gap-3 px-3">
                                @if($data->order_status != 'order sent')
                                    <button type="submit" class="btn btn-primary">Update</button>
                                @endif
                                <a href="{{ route('purchase-history') }}" class="btn btn-primary">Back</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function getOrderStatus(ele){
        const inputEle = ele.nextElementSibling;
        if(ele.value === 'order sent'){
            inputEle.classList.remove('d-none');
        }else{
            inputEle.classList.add('d-none')
        }
    }
</script>

@endsection


