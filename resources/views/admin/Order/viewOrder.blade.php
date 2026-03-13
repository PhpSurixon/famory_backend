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
        8 => ['label' => 'Refunded',      'color' => '#fd7e14', 'text' => '#fff'],
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
        <div class="d-flex gap-2">
            <a href="{{ route('admin.order.invoice', $order->id) }}" class="btn btn-outline-danger" target="_blank">
                <i class="bx bx-download me-1"></i> Download Invoice
            </a>
            <a href="{{ route('admin.order.list') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Left column ────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Order Items --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="bx bx-package me-1"></i> Order Items</h6>
                    @if($order->orderDetail->whereNull('tag_code')->count() > 0 && in_array($order->last_status_id, [2, 3]))
                    <button type="button" class="btn btn-sm btn-primary" id="genTagBtn" data-order-id="{{ $order->id }}">
                        <span id="genTagBtnText"><i class="bx bx-purchase-tag me-1"></i> Generate Tag Codes
                            <span class="badge bg-white text-primary ms-1">{{ $order->orderDetail->whereNull('tag_code')->count() }}</span>
                        </span>
                        <span id="genTagBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1"></span> Generating…
                        </span>
                    </button>
                    @endif
                </div>
                <div id="tagAlert" class="d-none mx-3 mt-3"></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Tag Code</th>
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
                                <td class="text-center">
                                    @if($item->tag_code)
                                        <span class="badge bg-label-success me-1">{{ $item->tag_code }}</span>
                                        <a href="javascript:void(0);"
                                           onclick="showTagQR('{{ $item->tag_code }}')"
                                           title="View QR Code"
                                           class="text-primary">
                                            <i class="bx bx-qr-scan fs-5"></i>
                                        </a>
                                    @else
                                        <span class="badge bg-label-warning" role="button"
                                              onclick="alertNoTagCode()"
                                              title="Click for info">
                                            <i class="bx bx-qr-scan me-1"></i>Not Generated
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">${{ number_format($lineTotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end">Subtotal</td>
                                <td class="text-end">${{ number_format($order->subtotal_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Shipping</td>
                                <td class="text-end">
                                    @if($order->shipping_amount > 0)
                                        ${{ number_format($order->shipping_amount, 2) }}
                                    @else
                                        <span class="text-success">Free</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total Paid</td>
                                <td class="text-end fw-bold text-primary fs-6">${{ number_format($order->payable_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Shipping Address + Customer side-by-side --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
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
                <div class="col-md-6">
                    <div class="card h-100">
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


            {{-- CONFIRMED → Ship or Cancel --}}
            @if($order->last_status_id == 2)
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bx bx-car me-1"></i> Mark as Shipped</h6>
                </div>
                <div class="card-body">
                    <div id="shipAlert" class="d-none mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Waybill / Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" id="waybillInput" class="form-control" placeholder="Enter waybill number" value="{{ $order->waybill }}">
                        <div id="waybillError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tracking URL <span class="text-muted small">(optional)</span></label>
                        <input type="url" id="trackingUrlInput" class="form-control" placeholder="https://track.example.com/..." value="{{ $order->tracking_url }}">
                    </div>
                    <button type="button" class="btn btn-success w-100" id="shipBtn" data-order-id="{{ $order->id }}">
                        <span id="shipBtnText"><i class="bx bx-check me-1"></i> Confirm Shipment</span>
                        <span id="shipBtnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span> Processing…</span>
                    </button>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bx bx-x-circle me-1"></i> Cancel Order</h6>
                </div>
                <div class="card-body">
                    <div id="cancelAlert" class="d-none mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason <span class="text-muted small">(optional)</span></label>
                        <textarea id="cancelReason" class="form-control" rows="2" placeholder="Enter cancellation reason…"></textarea>
                    </div>
                    @if($order->payment_mode == 2)
                    <p class="text-muted small mb-3"><i class="bx bx-info-circle me-1"></i> This will initiate an automatic Stripe refund of <strong>${{ number_format($order->payable_amount, 2) }}</strong>.</p>
                    @endif
                    <button type="button" class="btn btn-danger w-100" id="cancelBtn" data-order-id="{{ $order->id }}">
                        <span id="cancelBtnText"><i class="bx bx-trash me-1"></i> Cancel Order{{ $order->payment_mode == 2 ? ' & Refund' : '' }}</span>
                        <span id="cancelBtnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span> Processing…</span>
                    </button>
                </div>
            </div>
            @endif

            {{-- SHIPPED → Mark as Shipped (update) or set Delivered / Not Delivered --}}
            @if($order->last_status_id == 3)
            <div class="card border-success mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bx bx-car me-1"></i> Update Shipping Details</h6>
                </div>
                <div class="card-body">
                    <div id="shipAlert" class="d-none mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Waybill / Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" id="waybillInput" class="form-control" placeholder="Enter waybill number" value="{{ $order->waybill }}">
                        <div id="waybillError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tracking URL <span class="text-muted small">(optional)</span></label>
                        <input type="url" id="trackingUrlInput" class="form-control" placeholder="https://track.example.com/..." value="{{ $order->tracking_url }}">
                    </div>
                    <button type="button" class="btn btn-success w-100" id="shipBtn" data-order-id="{{ $order->id }}">
                        <span id="shipBtnText"><i class="bx bx-refresh me-1"></i> Update Waybill</span>
                        <span id="shipBtnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span> Processing…</span>
                    </button>
                </div>
            </div>

            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bx bx-package me-1"></i> Delivery Status</h6>
                </div>
                <div class="card-body">
                    <div id="deliveryAlert" class="d-none mb-3"></div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="deliveredBtn" data-order-id="{{ $order->id }}" data-status="4">
                            <span id="deliveredBtnText"><i class="bx bx-check-double me-1"></i> Mark as Delivered</span>
                            <span id="deliveredBtnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span></span>
                        </button>
                        <button type="button" class="btn btn-warning" id="notDeliveredBtn" data-order-id="{{ $order->id }}" data-status="5">
                            <span id="notDeliveredBtnText"><i class="bx bx-x me-1"></i> Mark as Not Delivered</span>
                            <span id="notDeliveredBtnSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span></span>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- CANCELLED / REFUNDED --}}
            @if(in_array($order->last_status_id, [6, 8]))
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Order {{ $order->order_status }}</h6>
                </div>
                <div class="card-body">
                    @if($order->cancel_reason)
                    <p class="mb-2 text-muted small"><strong>Reason:</strong> {{ $order->cancel_reason }}</p>
                    @endif
                    @if($order->stripe_refund_id)
                    <p class="mb-0 text-success small"><i class="bx bx-check-circle me-1"></i> Refund processed — <code>{{ $order->stripe_refund_id }}</code></p>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@if(in_array($order->last_status_id, [2, 3]))
<script>
// ── Ship / Update Waybill ──────────────────────────────────────
document.getElementById('shipBtn').addEventListener('click', function () {
    const waybill     = document.getElementById('waybillInput').value.trim();
    const trackingUrl = document.getElementById('trackingUrlInput').value.trim();
    const errorBox    = document.getElementById('waybillError');
    const alertBox    = document.getElementById('shipAlert');
    const orderId     = this.dataset.orderId;

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
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ order_id: orderId, waybill: waybill, tracking_url: trackingUrl || null })
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
            this.disabled = false;
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
        alertBox.classList.remove('d-none');
        document.getElementById('shipBtnText').classList.remove('d-none');
        document.getElementById('shipBtnSpinner').classList.add('d-none');
        this.disabled = false;
    });
});
</script>
@endif

@if($order->last_status_id == 2)
<script>
// ── Cancel Order ──────────────────────────────────────────────
document.getElementById('cancelBtn').addEventListener('click', function () {
    const reason    = document.getElementById('cancelReason').value.trim();
    const alertBox  = document.getElementById('cancelAlert');
    const orderId   = this.dataset.orderId;

    if (!confirm('Are you sure you want to cancel this order? This cannot be undone.')) return;

    alertBox.classList.add('d-none');
    document.getElementById('cancelBtnText').classList.add('d-none');
    document.getElementById('cancelBtnSpinner').classList.remove('d-none');
    this.disabled = true;

    fetch('{{ route('admin.order.cancel') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ order_id: orderId, cancel_reason: reason || null })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alertBox.innerHTML = '<div class="alert alert-success mb-0">' + data.message + '</div>';
            alertBox.classList.remove('d-none');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'Something went wrong.') + '</div>';
            alertBox.classList.remove('d-none');
            document.getElementById('cancelBtnText').classList.remove('d-none');
            document.getElementById('cancelBtnSpinner').classList.add('d-none');
            this.disabled = false;
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
        alertBox.classList.remove('d-none');
        document.getElementById('cancelBtnText').classList.remove('d-none');
        document.getElementById('cancelBtnSpinner').classList.add('d-none');
        this.disabled = false;
    });
});
</script>
@endif

@if($order->last_status_id == 3)
<script>
// ── Delivered / Not Delivered ─────────────────────────────────
function handleDelivery(btnId, spinnerId, textId, statusVal) {
    const btn      = document.getElementById(btnId);
    const alertBox = document.getElementById('deliveryAlert');
    const orderId  = btn.dataset.orderId;

    alertBox.classList.add('d-none');
    document.getElementById(textId).classList.add('d-none');
    document.getElementById(spinnerId).classList.remove('d-none');
    btn.disabled = true;

    fetch('{{ route('admin.order.updateDelivery') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ order_id: orderId, status: statusVal })
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
            document.getElementById(textId).classList.remove('d-none');
            document.getElementById(spinnerId).classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
        alertBox.classList.remove('d-none');
        document.getElementById(textId).classList.remove('d-none');
        document.getElementById(spinnerId).classList.add('d-none');
        btn.disabled = false;
    });
}

document.getElementById('deliveredBtn').addEventListener('click', function () {
    handleDelivery('deliveredBtn', 'deliveredBtnSpinner', 'deliveredBtnText', 4);
});
document.getElementById('notDeliveredBtn').addEventListener('click', function () {
    handleDelivery('notDeliveredBtn', 'notDeliveredBtnSpinner', 'notDeliveredBtnText', 5);
});
</script>
@endif

{{-- No Tag Code Toast --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index:1100;">
    <div id="noTagCodeToast" class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold">
                <i class="bx bx-error-circle me-1"></i>
                Tag code not generated yet. Click <strong>Generate Tag Codes</strong> button above to assign one.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

{{-- QR Code Modal --}}
<div class="modal fade" id="tagQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:320px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-qr-scan me-1"></i> QR Code — <span id="qrTagCodeLabel"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="tagQrContainer" style="display:inline-block;padding:16px;background:#fff;border-radius:8px;"></div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" onclick="downloadTagQR()">
                    <i class="bx bx-download me-1"></i> Download PNG
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
window.showTagQR = function(tagCode) {
    document.getElementById('qrTagCodeLabel').textContent = tagCode;
    var container = document.getElementById('tagQrContainer');
    container.innerHTML = '';
    var qrUrl = '{{ config("app.url") }}/tag-view/' + tagCode;
    new QRCode(container, {
        text: qrUrl,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    new bootstrap.Modal(document.getElementById('tagQrModal')).show();
};

window.downloadTagQR = function() {
    var tagCode = document.getElementById('qrTagCodeLabel').textContent;
    var canvas = document.querySelector('#tagQrContainer canvas');
    if (canvas) {
        var link = document.createElement('a');
        link.download = 'QR_' + tagCode + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
};
</script>

@if($order->orderDetail->whereNull('tag_code')->count() > 0)
<script>
document.getElementById('genTagBtn').addEventListener('click', function () {
    const alertBox = document.getElementById('tagAlert');
    const btn      = this;

    alertBox.classList.add('d-none');
    document.getElementById('genTagBtnText').classList.add('d-none');
    document.getElementById('genTagBtnSpinner').classList.remove('d-none');
    btn.disabled = true;

    fetch('{{ route('admin.order.generateTagCodes') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order_id: {{ $order->id }} })
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
            document.getElementById('genTagBtnText').classList.remove('d-none');
            document.getElementById('genTagBtnSpinner').classList.add('d-none');
            btn.disabled = false;
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
        alertBox.classList.remove('d-none');
        document.getElementById('genTagBtnText').classList.remove('d-none');
        document.getElementById('genTagBtnSpinner').classList.add('d-none');
        btn.disabled = false;
    });
});
</script>
@endif

<script>
window.alertNoTagCode = function() {
    var toast = new bootstrap.Toast(document.getElementById('noTagCodeToast'), { delay: 4000 });
    toast.show();
};
</script>

@endsection
