@extends('layouts.admin-master', ['title' => $invoice->invoice_no])

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
    .user-btn.danger { background: #fff; color: #dc3545; border-color: #dc3545; box-shadow: none; }
    .user-btn.danger:hover { background: #dc3545; color: #fff; }
    .ref-chip { display: inline-block; border: 1px solid #d9dee3; border-radius: 6px; padding: 3px 10px; margin: 0 6px 6px 0; background: #f5f7fa; font-size: .9rem; }
    .meta-label { color: #8592a3; font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <h5 class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>
                        {{ $invoice->invoice_no }}
                        <span class="badge {{ $invoice->statusBadge() }} ms-2">{{ $invoice->statusLabel() }}</span>
                    </span>
                    <span class="d-flex flex-wrap gap-2">
                        <a href="{{ route('business-invoices') }}" class="user-btn outline">Back</a>
                        <a href="{{ route('business-invoices.pdf', $invoice->id) }}" class="user-btn outline">Download PDF</a>
                        @if ($invoice->canEdit())
                            <a href="{{ route('business-invoices.edit', $invoice->id) }}" class="user-btn outline">Edit</a>
                        @endif
                        @if ($invoice->canIssue())
                            <button type="button" class="user-btn" onclick="issueInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')">Issue</button>
                        @endif
                        @if ($invoice->canMarkPaid())
                            <button type="button" class="user-btn" onclick="payInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')">Mark Paid</button>
                        @endif
                        @if ($invoice->canCancel())
                            <button type="button" class="user-btn danger" onclick="cancelInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no }}')">Cancel</button>
                        @endif
                    </span>
                </h5>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="meta-label mb-1">Bill To</div>
                            <div class="fw-semibold">{{ $invoice->bill_to_name }}</div>
                            @if ($invoice->bill_to_address)<div style="white-space: pre-line;">{{ $invoice->bill_to_address }}</div>@endif
                            @if ($invoice->bill_to_email)<div>{{ $invoice->bill_to_email }}</div>@endif
                            @if ($invoice->bill_to_mobile)<div>{{ $invoice->bill_to_mobile }}</div>@endif
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="meta-label mb-1">Invoice Date</div>
                            <div>{{ $invoice->invoice_date->format('d M Y') }}</div>
                            @if ($invoice->issued_at)
                                <div class="meta-label mt-3 mb-1">Issued</div>
                                <div>{{ $invoice->issued_at->format('d M Y h:i A') }}</div>
                            @endif
                        </div>
                        <div class="col-md-4 col-6">
                            @if ($invoice->isPaid())
                                <div class="meta-label mb-1">Paid</div>
                                <div>{{ $invoice->paid_date->format('d M Y') }} &middot; {{ $invoice->payment_method }}</div>
                                @if ($invoice->payment_reference)<div class="text-muted">Ref: {{ $invoice->payment_reference }}</div>@endif
                            @endif
                            @if ($invoice->isCancelled())
                                <div class="meta-label mb-1">Cancelled</div>
                                <div>{{ optional($invoice->cancelled_at)->format('d M Y h:i A') }}</div>
                                <div class="text-muted" style="white-space: pre-line;">{{ $invoice->cancel_reason }}</div>
                            @endif
                            @if ($invoice->notes)
                                <div class="meta-label mt-3 mb-1">Notes</div>
                                <div style="white-space: pre-line;">{{ $invoice->notes }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table border-top">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Business Tag</th>
                                    <th class="text-end">Codes</th>
                                    <th class="text-end">Price per code</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->tag_name }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">${{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total codes</strong></td>
                                    <td class="text-end"><strong>{{ $invoice->total_codes }}</strong></td>
                                    <td class="text-end">Subtotal</td>
                                    <td class="text-end">${{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if ($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="text-end">Tax ({{ rtrim(rtrim(number_format($invoice->tax_percent, 2), '0'), '.') }}%)</td>
                                        <td class="text-end">${{ number_format($invoice->tax_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end"><strong>Total</strong></td>
                                    <td class="text-end"><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <h5 class="card-header">Tags on this invoice</h5>
                <div class="card-body">
                    @forelse ($invoice->items as $item)
                        @php $refs = $refsByTag->get($item->business_tag_id, []); @endphp
                        <div class="mb-3">
                            <h6 class="mb-2">{{ $item->tag_name }} <span class="badge bg-label-primary ms-1">{{ $item->quantity }}</span></h6>
                            @if (count($refs))
                                @foreach ($refs as $ref)<span class="ref-chip">{{ $ref }}</span>@endforeach
                            @else
                                <span class="text-muted">The codes were released when this invoice was cancelled.</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No tags.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@include('admin.BusinessInvoice._actions')
@endsection
