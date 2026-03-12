@extends('layouts.admin-master', ['title' => 'Order Management'])

@section('content')
<style>
    .badge-pending      { background:#ffc107; color:#000; }
    .badge-confirmed    { background:#0d6efd; color:#fff; }
    .badge-shipped      { background:#198754; color:#fff; }
    .badge-delivered    { background:#20c997; color:#fff; }
    .badge-notdelivered { background:#dc3545; color:#fff; }
    .badge-cancelled    { background:#6c757d; color:#fff; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fw-bold mb-0">Order Management</h4>
        <span class="text-muted">Total: {{ $orders->total() }} orders</span>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.order.list') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Order ID / Invoice / User"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pending</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Confirmed</option>
                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Shipped</option>
                            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>Delivered</option>
                            <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Not Delivered</option>
                            <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.order.list') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover border-top mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Waybill</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $index => $order)
                    @php
                        $statusMap = [
                            1 => ['label' => 'Pending',       'class' => 'badge-pending'],
                            2 => ['label' => 'Confirmed',     'class' => 'badge-confirmed'],
                            3 => ['label' => 'Shipped',       'class' => 'badge-shipped'],
                            4 => ['label' => 'Delivered',     'class' => 'badge-delivered'],
                            5 => ['label' => 'Not Delivered', 'class' => 'badge-notdelivered'],
                            6 => ['label' => 'Cancelled',     'class' => 'badge-cancelled'],
                        ];
                        $badge = $statusMap[$order->last_status_id] ?? ['label' => $order->order_status, 'class' => 'bg-secondary text-white'];
                    @endphp
                    <tr>
                        <td>{{ $orders->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $order->unique_order_id }}</strong><br>
                            <small class="text-muted">{{ $order->invoice_no }}</small>
                        </td>
                        <td>
                            @if($order->user)
                                <span>{{ $order->user->first_name }} {{ $order->user->last_name }}</span><br>
                                <small class="text-muted">{{ $order->user->email }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $order->order_datetime ? \Carbon\Carbon::parse($order->order_datetime)->format('d M Y') : '—' }}</td>
                        <td>{{ $order->orderDetail->count() }} item(s)</td>
                        <td>${{ number_format($order->payable_amount, 2) }}</td>
                        <td>{{ $order->payment_mode == 2 ? 'Online' : 'COD' }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td>
                            @if($order->waybill)
                                <span class="text-success fw-semibold">{{ $order->waybill }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.order.view', $order->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="View Order">
                                    <i class="bx bx-show"></i>
                                </a>
                                @if(in_array($order->last_status_id, [2]))
                                    <button class="btn btn-sm btn-success btn-ship"
                                            data-order-id="{{ $order->id }}"
                                            data-order-ref="{{ $order->unique_order_id }}"
                                            data-waybill="{{ $order->waybill }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#shipModal"
                                            title="{{ $order->last_status_id == 3 ? 'Update Waybill' : 'Mark as Shipped' }}">
                                        <i class="bx bx-car"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Showing {{ $orders->firstItem() }} – {{ $orders->lastItem() }} of {{ $orders->total() }}
            </small>
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Ship Modal --}}
<div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipModalLabel">Mark Order as Shipped</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Order: <strong id="modalOrderRef"></strong></p>
                <div class="mb-3">
                    <label for="waybillInput" class="form-label fw-semibold">Waybill / Tracking Number <span class="text-danger">*</span></label>
                    <input type="text" id="waybillInput" class="form-control" placeholder="Enter waybill number">
                    <div id="waybillError" class="text-danger small mt-1 d-none"></div>
                </div>
                <div id="shipAlertBox" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmShipBtn">
                    <span id="shipBtnText"><i class="bx bx-check me-1"></i> Confirm Shipment</span>
                    <span id="shipBtnSpinner" class="d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span> Processing…
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let currentOrderId = null;

    // Populate modal when Ship button is clicked
    document.querySelectorAll('.btn-ship').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentOrderId = this.dataset.orderId;
            document.getElementById('modalOrderRef').textContent = this.dataset.orderRef;
            document.getElementById('waybillInput').value = this.dataset.waybill || '';
            document.getElementById('waybillError').classList.add('d-none');
            document.getElementById('shipAlertBox').classList.add('d-none');
        });
    });

    document.getElementById('confirmShipBtn').addEventListener('click', function () {
        const waybill = document.getElementById('waybillInput').value.trim();
        const errorBox = document.getElementById('waybillError');
        const alertBox = document.getElementById('shipAlertBox');

        errorBox.classList.add('d-none');
        alertBox.classList.add('d-none');

        if (!waybill) {
            errorBox.textContent = 'Please enter a waybill number.';
            errorBox.classList.remove('d-none');
            return;
        }

        // Show spinner
        document.getElementById('shipBtnText').classList.add('d-none');
        document.getElementById('shipBtnSpinner').classList.remove('d-none');
        this.disabled = true;

        fetch('{{ route('admin.order.updateShipping') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order_id: currentOrderId, waybill: waybill })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.status === 'success') {
                alertBox.innerHTML = '<div class="alert alert-success mb-0">' + data.message + '</div>';
                alertBox.classList.remove('d-none');
                // Reload after short delay to reflect new status
                setTimeout(function () { window.location.reload(); }, 1200);
            } else {
                alertBox.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'Something went wrong.') + '</div>';
                alertBox.classList.remove('d-none');
                resetShipBtn();
            }
        })
        .catch(function () {
            alertBox.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
            alertBox.classList.remove('d-none');
            resetShipBtn();
        });
    });

    function resetShipBtn() {
        document.getElementById('shipBtnText').classList.remove('d-none');
        document.getElementById('shipBtnSpinner').classList.add('d-none');
        document.getElementById('confirmShipBtn').disabled = false;
    }
})();
</script>

@endsection
