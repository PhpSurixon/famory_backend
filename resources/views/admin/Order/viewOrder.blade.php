@extends('layouts.admin-master', ['title' => 'Order Detail'])

@section('content')
@php
    $statusMap = [
        1 => ['label' => 'Pending',       'color' => '#ffc107', 'text' => '#000'],
        2 => ['label' => 'Confirmed',     'color' => '#0d6efd', 'text' => '#fff'],
        3 => ['label' => 'Shipped',       'color' => '#198754', 'text' => '#fff'],
        4 => ['label' => 'Delivered',     'color' => '#20c997', 'text' => '#fff'],
        5 => ['label' => 'Not Delivered', 'color' => '#dc3545', 'text' => '#fff'],
        6 => ['label' => 'Cancelled',     'color' => '#6c757d', 'text' => '#fff'],
    ];
    $badge = $statusMap[$order->last_status_id] ?? ['label' => $order->order_status, 'color' => '#6c757d', 'text' => '#fff'];

    $subtotal = 0;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Order Detail</h4>
            <small class="text-muted">{{ $order->unique_order_id }}</small>
        </div>
        <a href="{{ route('admin.order.list') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Orders
        </a>
    </div>

    <div class="row g-4">

        {{-- ── Left column ────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Order Items --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-package me-1"></i> Order Items</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderDetail as $index => $item)
                            @php
                                $product = json_decode($item->product_json, true);
                                $lineTotal = $item->product_unit_price * $item->buy_quantity;
                                $subtotal += $lineTotal;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if(!empty($product['image']))
                                        <img src="{{ $product['image'] }}" alt=""
                                             style="width:40px;height:40px;object-fit:cover;border-radius:4px;margin-right:8px;">
                                    @endif
                                    <span class="fw-semibold">{{ $product['name'] ?? 'Product #'.$item->product_id }}</span>
                                    @if(!empty($product['sku']))
                                        <br><small class="text-muted">SKU: {{ $product['sku'] }}</small>
                                    @endif
                                </td>
                                <td class="text-center">${{ number_format($item->product_unit_price, 2) }}</td>
                                <td class="text-center">{{ $item->buy_quantity }}</td>
                                <td class="text-end">${{ number_format($lineTotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end">Subtotal</td>
                                <td class="text-end">${{ number_format($order->subtotal_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Shipping</td>
                                <td class="text-end">
                                    @if($order->shipping_amount > 0)
                                        ${{ number_format($order->shipping_amount, 2) }}
                                    @else
                                        <span class="text-success">Free</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Paid</td>
                                <td class="text-end fw-bold text-primary fs-6">${{ number_format($order->payable_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-map me-1"></i> Shipping Address</h6>
                </div>
                <div class="card-body">
                    @if($order->address_data)
                    <p class="mb-1 fw-semibold">{{ $order->address_data['name'] ?? '—' }}</p>
                    <p class="mb-1 text-muted">
                        {{ $order->address_data['house_number'] ?? '' }}
                        @if(!empty($order->address_data['road_name'])), {{ $order->address_data['road_name'] }}@endif
                    </p>
                    <p class="mb-1 text-muted">
                        {{ $order->address_data['state'] ?? '' }}
                        @if(!empty($order->address_data['zip_code'])) — {{ $order->address_data['zip_code'] }}@endif
                    </p>
                    @if(!empty($order->address_data['phone_number']))
                    <p class="mb-0 text-muted"><i class="bx bx-phone me-1"></i>{{ $order->address_data['phone_number'] }}</p>
                    @endif
                    @else
                        <span class="text-muted">No address data available.</span>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Right column ───────────────────────── --}}
        <div class="col-lg-4">

            {{-- Order Summary --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-receipt me-1"></i> Order Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0">Order ID</td>
                            <td class="fw-semibold text-end">{{ $order->unique_order_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Invoice No</td>
                            <td class="fw-semibold text-end">{{ $order->invoice_no ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Order Date</td>
                            <td class="fw-semibold text-end">
                                {{ $order->order_datetime
                                    ? \Carbon\Carbon::parse($order->order_datetime)->format('d M Y, H:i')
                                    : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Payment</td>
                            <td class="fw-semibold text-end">{{ $order->payment_mode == 2 ? 'Online (Stripe)' : 'COD' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Status</td>
                            <td class="text-end">
                                <span class="badge rounded-pill"
                                      style="background:{{ $badge['color'] }};color:{{ $badge['text'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                        </tr>
                        @if($order->waybill)
                        <tr>
                            <td class="text-muted ps-0">Waybill</td>
                            <td class="fw-semibold text-end text-success">{{ $order->waybill }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-user me-1"></i> Customer</h6>
                </div>
                <div class="card-body">
                    @if($order->user)
                        <p class="mb-1 fw-semibold">{{ $order->user->first_name }} {{ $order->user->last_name }}</p>
                        <p class="mb-0 text-muted">{{ $order->user->email }}</p>
                    @else
                        <span class="text-muted">User not found.</span>
                    @endif
                </div>
            </div>

            {{-- Ship Action (only for Confirmed or Shipped) --}}
            @if(in_array($order->last_status_id, [2]))
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bx bx-car me-1"></i>
                        {{ $order->last_status_id == 3 ? 'Update Waybill' : 'Mark as Shipped' }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="shipAlert" class="d-none mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Waybill / Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" id="waybillInput" class="form-control"
                               placeholder="Enter waybill number"
                               value="{{ $order->waybill }}">
                        <div id="waybillError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <button type="button" class="btn btn-success w-100" id="shipBtn"
                            data-order-id="{{ $order->id }}">
                        <span id="shipBtnText">
                            <i class="bx bx-check me-1"></i>
                            {{ $order->last_status_id == 3 ? 'Update Waybill' : 'Confirm Shipment' }}
                        </span>
                        <span id="shipBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span> Processing…
                        </span>
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@if(in_array($order->last_status_id, [2, 3]))
<script>
document.getElementById('shipBtn').addEventListener('click', function () {
    const waybill    = document.getElementById('waybillInput').value.trim();
    const errorBox   = document.getElementById('waybillError');
    const alertBox   = document.getElementById('shipAlert');
    const orderId    = this.dataset.orderId;

    errorBox.classList.add('d-none');
    alertBox.classList.add('d-none');

    if (!waybill) {
        errorBox.textContent = 'Please enter a waybill number.';
        errorBox.classList.remove('d-none');
        return;
    }

    document.getElementById('shipBtnText').classList.add('d-none');
    document.getElementById('shipBtnSpinner').classList.remove('d-none');
    this.disabled = true;

    fetch('{{ route('admin.order.updateShipping') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order_id: orderId, waybill: waybill })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alertBox.innerHTML = '<div class="alert alert-success mb-0">' + data.message + '</div>';
            alertBox.classList.remove('d-none');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'Something went wrong.') + '</div>';
            alertBox.classList.remove('d-none');
            document.getElementById('shipBtnText').classList.remove('d-none');
            document.getElementById('shipBtnSpinner').classList.add('d-none');
            document.getElementById('shipBtn').disabled = false;
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
        alertBox.classList.remove('d-none');
        document.getElementById('shipBtnText').classList.remove('d-none');
        document.getElementById('shipBtnSpinner').classList.add('d-none');
        document.getElementById('shipBtn').disabled = false;
    });
});
</script>
@endif

@endsection
