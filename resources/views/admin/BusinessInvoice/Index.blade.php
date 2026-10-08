@extends('layouts.admin-master', ['title' => 'Business Invoices'])

@section('content')
<style>
    .user-btn {
        color: #fff;
        background-color: #1550AE;
        border-color: #1550AE;
        box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        padding: 0.4375rem 1.25rem;
        font-size: 0.9375rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
        transition: all 0.2s ease-in-out;
        display: inline-block;
        cursor: pointer;
    }
    .user-btn:hover { color: #1550AE; background-color: #fff; border-color: #1550AE; transform: translateY(-1px); }
    .user-btn.outline { background: #fff; color: #1550AE; border-color: #1550AE; }
    .user-btn.outline:hover { background: #1550AE; color: #fff; }
    .stat-card { border: 1px solid #e4e6eb; border-radius: 8px; padding: 12px 16px; height: 100%; }
    .stat-card .num { font-size: 1.5rem; font-weight: 600; line-height: 1.1; }
    .stat-card .lbl { color: #8592a3; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; }
    table .table-light tr th { text-align: center; white-space: nowrap; }
    div#data-table_wrapper .row:nth-child(2) { overflow-x: auto; margin-bottom: 1rem; }
    .table-d table tbody tr td { vertical-align: middle; white-space: nowrap; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <h5 class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>Business Invoices</span>
                    <a href="{{ route('business-invoices.create') }}" class="user-btn">Create Invoice</a>
                </h5>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3"><div class="stat-card"><div class="num">{{ $summary['draft'] }}</div><div class="lbl">Drafts</div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-card"><div class="num text-info">${{ number_format($summary['issued_amount'], 2) }}</div><div class="lbl">Issued &middot; {{ $summary['issued_count'] }} awaiting payment</div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-card"><div class="num text-success">${{ number_format($summary['paid_amount'], 2) }}</div><div class="lbl">Paid &middot; {{ $summary['paid_count'] }} invoices</div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-card"><div class="num text-danger">{{ $summary['cancelled'] }}</div><div class="lbl">Cancelled</div></div></div>
                    </div>

                    <form method="get" action="{{ route('business-invoices') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Business</label>
                            <select name="business_id" class="form-select form-select-sm">
                                <option value="">All businesses</option>
                                @foreach ($businesses as $b)
                                    <option value="{{ $b->id }}" {{ (string) request('business_id') === (string) $b->id ? 'selected' : '' }}>{{ $b->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach (['draft', 'issued', 'paid', 'cancelled'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">From</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">To</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="user-btn">Filter</button>
                            <a href="{{ route('business-invoices') }}" class="user-btn outline">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-datatable table-responsive table-d">
                    <table class="datatables-basic table border-top" id="data-table">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice No</th>
                                <th>Business</th>
                                <th>Invoice Date</th>
                                <th>Total Codes</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('business-invoices.show', $invoice->id) }}">{{ $invoice->invoice_no }}</a></td>
                                    <td>{{ $invoice->bill_to_name }}</td>
                                    <td data-order="{{ $invoice->invoice_date->format('Y-m-d') }}">{{ $invoice->invoice_date->format('d M Y') }}</td>
                                    <td>{{ $invoice->total_codes }}</td>
                                    <td>${{ number_format($invoice->subtotal, 2) }}</td>
                                    <td>${{ number_format($invoice->tax_amount, 2) }}</td>
                                    <td><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                                    <td><span class="badge {{ $invoice->statusBadge() }}">{{ $invoice->statusLabel() }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('business-invoices.show', $invoice->id) }}"><i class="bx bx-show me-1"></i> View</a>
                                                <a class="dropdown-item" href="{{ route('business-invoices.pdf', $invoice->id) }}"><i class="bx bx-download me-1"></i> Download PDF</a>
                                                @if ($invoice->canEdit())
                                                    <a class="dropdown-item" href="{{ route('business-invoices.edit', $invoice->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                                @endif
                                                @if ($invoice->canIssue())
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="issueInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')"><i class="bx bx-send me-1"></i> Issue</a>
                                                @endif
                                                @if ($invoice->canMarkPaid())
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="payInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')"><i class="bx bx-check-circle me-1"></i> Mark Paid</a>
                                                @endif
                                                @if ($invoice->canCancel())
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="cancelInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')"><i class="bx bx-x-circle me-1"></i> Cancel</a>
                                                @endif
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
<script type="text/javascript">
    $(document).ready(function() {
        // Replace the global scrollX table (see assets/js/datatable.js) with a plain one
        if ($.fn.dataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().destroy();
        }
        $('#data-table').DataTable({
            autoWidth: false,
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
            columnDefs: [{ orderable: false, targets: [-1] }],
            order: [[0, 'desc']]
        });
    });
</script>
@include('admin.BusinessInvoice._actions')
@endsection
