@extends('layouts.admin-master', ['title' => 'Purchased History'])

@section('content')
 <style>
    .hidden {
        display:none;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }.text-order-received {
        color: #d38413 !important;
    }
    .text-order-sent {
        color: green !important;
    }.text-order-waiting {
        color: red !important;
    }
    .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: auto !important;
    padding-bottom: 50px;
    height: calc(100vh - 350px);
    }
    
    .dataTables_scroll {
        overflow: hidden !important;
    }
    
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!--<h5 class="card-header d-flex align-items-center justify-content-between"> View Purchased History</h5>-->

                <div class="table-responsive text-nowrap">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="alert alert-success hidden" id="successMessage">
                        <span></span>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Name</th>
                                    <th>Famory Tag Name</th>
                                    <th>Purchased Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                  @foreach ($transactionData as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if($data->user)
                                                <a href="{{ route('get-user-detail-with-order', $data->user->id) }}" class="text-info" style="cursor:pointer;">
                                                    {{ $data->user->first_name }} {{ $data->user->last_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">No user available</span>
                                            @endif
                                        </td>
                                        
                                        <td>{{$data->product['name'] ?? 'N/A'}}</td>
                                        <td>{{$data->sticker['quantity'] ?? 'N/A'}}</td>
                                        <td>${{ number_format($data->amount, 2) ?? 'N/A'}}</td>
                                        <td>{{\Carbon\Carbon::parse($data->created_at)->format('m/d/y') ?? 'N/A' }}</td>
                                     
                                       <td>
                                            <form method="post" action="{{ route('update-order-status', $data->sticker['id']) }}">
                                                @csrf
                                                <select name="order_status" class="form-select" onchange="getOrderStatus(this, '{{ $data->sticker['id'] }}')" @if($data->order_status == 'order sent') disabled @endif>
                                                    <option value="waiting" {{ $data->sticker->order_status == 'waiting' ? 'selected' : '' }} class="{{$data->sticker->order_status == 'order received' || $data->sticker->order_status == 'order sent' ? 'd-none' : '' }}" >Waiting</option>
                                                    <option value="order received" {{ $data->sticker->order_status == 'order received' ? 'selected' : '' }} class="{{$data->sticker->order_status == 'order sent' ? 'd-none' : '' }}" >Order Received</option>
                                                    <option value="order sent" {{ $data->sticker->order_status == 'order sent' ? 'selected' : '' }}>Order Sent</option>
                                                </select>
                                                <input type="text" class="form-control {{ $data->order_status == 'order sent' ? 'd-block' : 'd-none' }}" @if($data->order_status == 'order sent') readonly @endif name="shipTrackingId" placeholder="Ship Tracking Id" value="{{ $data->shipping_tracking_number }}">
                                                <div class="button-container mt-2 d-none" id="submitbutton{{ $data->sticker['id'] }}">
                                                    <button type="submit" class="btn btn-primary">Change</button>
                                                </div>
                                            </form>
                                        </td>



                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                            <a class="dropdown-item"  href="{{ route('view-oder-detail', $data->sticker['id']) }}">
                                                                <i class="bx bx-detail me-1"></i> View
                                                            </a>
                                                    </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function getOrderStatus(ele,id){
        const inputEle = ele.nextElementSibling;
        const submitButton = document.getElementById('submitbutton' + id);
        if(ele.value === 'order sent'){
            inputEle.classList.remove('d-none');
        }else{
            inputEle.classList.add('d-none')
        }
        
        if (submitButton) {
            submitButton.classList.remove('d-none');
        }
    }

</script>





@endsection