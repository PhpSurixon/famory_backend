<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $invoice->invoice_no }}</title>

<style>
@page { margin: 36px 40px; }

body{
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color:#333;
}

.header{ width:100%; margin-bottom:20px; }
.company{ float:left; }
.invoice-info{ float:right; text-align:right; }
.clear{ clear:both; }
.section{ margin-top:25px; }

.table{ width:100%; border-collapse:collapse; margin-top:10px; }
.table th{ background:#f4f4f4; padding:8px; border:1px solid #ddd; text-align:left; }
.table td{ padding:8px; border:1px solid #ddd; }
.right{ text-align:right; }

.total-section{ margin-top:20px; width:45%; float:right; }
.total-table td{ padding:6px; }

.badge{
    display:inline-block;
    padding:2px 8px;
    border:1px solid #888;
    border-radius:4px;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.5px;
}

.watermark{
    position:fixed;
    top:38%;
    left:0;
    width:100%;
    text-align:center;
    font-size:90px;
    font-weight:bold;
    color:#e9e9e9;
    z-index:-1;
}

.footer{ margin-top:40px; text-align:center; font-size:11px; color:#888; }
.refs td{ font-size:11px; letter-spacing:.3px; }
</style>
</head>

<body>

@if ($invoice->isDraft())
    <div class="watermark">DRAFT</div>
@elseif ($invoice->isCancelled())
    <div class="watermark">CANCELLED</div>
@endif

<div class="container">

<div class="header">
    <div class="company">
        <h2 style="margin:0 0 6px;">FamoryApp</h2>
        <p style="margin:0;">
            123 Business Street<br>
            New York, USA<br>
            support@famoryapp.com
        </p>
    </div>

    <div class="invoice-info">
        <h3 style="margin:0 0 6px;">INVOICE</h3>
        <p style="margin:0;">
            <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
            <strong>Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}<br>
            <strong>Status:</strong> <span class="badge">{{ $invoice->statusLabel() }}</span>
            @if ($invoice->isPaid())
                <br><strong>Paid on:</strong> {{ $invoice->paid_date->format('d M Y') }} ({{ $invoice->payment_method }})
                @if ($invoice->payment_reference)<br><strong>Reference:</strong> {{ $invoice->payment_reference }}@endif
            @endif
        </p>
    </div>

    <div class="clear"></div>
</div>

<hr>

<div class="section">
    <strong>Bill To</strong>
    <p style="margin:6px 0 0;">
        <strong>{{ $invoice->bill_to_name }}</strong><br>
        @if ($invoice->bill_to_address){!! nl2br(e($invoice->bill_to_address)) !!}<br>@endif
        @if ($invoice->bill_to_email){{ $invoice->bill_to_email }}<br>@endif
        @if ($invoice->bill_to_mobile)Phone: {{ $invoice->bill_to_mobile }}@endif
    </p>
</div>

<div class="section">
    <table class="table">
        <thead>
            <tr>
                <th width="6%">#</th>
                <th>Business Tag</th>
                <th width="12%" class="right">Codes</th>
                <th width="18%" class="right">Price per code</th>
                <th width="18%" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->tag_name }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="total-section">
    <table class="total-table" width="100%">
        <tr>
            <td>Total codes</td>
            <td align="right">{{ $invoice->total_codes }}</td>
        </tr>
        <tr>
            <td>Subtotal</td>
            <td align="right">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->tax_amount > 0)
            <tr>
                <td>Tax ({{ rtrim(rtrim(number_format($invoice->tax_percent, 2), '0'), '.') }}%)</td>
                <td align="right">${{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td><strong>Total</strong></td>
            <td align="right"><strong>${{ number_format($invoice->total, 2) }}</strong></td>
        </tr>
    </table>
</div>

<div class="clear"></div>

@if ($invoice->notes)
    <div class="section">
        <strong>Notes</strong>
        <p style="margin:6px 0 0;">{!! nl2br(e($invoice->notes)) !!}</p>
    </div>
@endif

<div class="footer">
    <p>If you have any questions about this invoice, contact support@famoryapp.com</p>
</div>

{{-- Reference numbers of the tags billed on this invoice (never the tag codes) --}}
@if ($invoice->items->isNotEmpty() && $refsByTag->isNotEmpty())
    <div style="page-break-before: always;">
        <h3 style="margin:0 0 4px;">Tags billed on {{ $invoice->invoice_no }}</h3>
        <p style="margin:0 0 10px; color:#888;">Reference numbers, grouped by Business Tag.</p>

        @foreach ($invoice->items as $item)
            @php $refs = $refsByTag->get($item->business_tag_id, []); @endphp
            @if (count($refs))
                <p style="margin:14px 0 4px;"><strong>{{ $item->tag_name }}</strong> &mdash; {{ count($refs) }} code{{ count($refs) === 1 ? '' : 's' }}</p>
                <table class="table refs">
                    @foreach (array_chunk($refs, 4) as $row)
                        <tr>
                            @foreach ($row as $ref)<td width="25%">{{ $ref }}</td>@endforeach
                            @for ($i = count($row); $i < 4; $i++)<td width="25%"></td>@endfor
                        </tr>
                    @endforeach
                </table>
            @endif
        @endforeach
    </div>
@endif

</div>

</body>
</html>
