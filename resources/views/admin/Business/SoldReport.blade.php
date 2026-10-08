@extends('layouts.admin-master', ['title' => 'Sold Report'])

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
    .user-btn:hover {
        color: #1550AE;
        background-color: #fff;
        border-color: #1550AE;
        transform: translateY(-1px);
    }
    .user-btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
    .user-btn.outline { background: #fff; color: #1550AE; border-color: #1550AE; }
    .user-btn.outline:hover { background: #1550AE; color: #fff; }
    .stat-card { border: 1px solid #e4e6eb; border-radius: 8px; padding: 14px 18px; height: 100%; }
    .stat-card .num { font-size: 1.8rem; font-weight: 600; line-height: 1.1; }
    .stat-card .lbl { color: #8592a3; font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; }
    table .table-light tr th { text-align: center; white-space: nowrap; }
    div#data-table_wrapper .row:nth-child(2) { overflow-x: auto; margin-bottom: 1rem; }
    .table-d table tbody tr td { vertical-align: middle; white-space: nowrap; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <h5 class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>Sold Report &mdash; {{ $business->business_name }}</span>
                    <span class="d-flex flex-wrap gap-2">
                        <a href="{{ route('business-invoices.create', ['business_id' => $business->id]) }}" class="user-btn">Create Invoice</a>
                        <a href="{{ route('business.assign-tags', $business->id) }}" class="user-btn outline">Assign Tag Codes</a>
                        <a href="{{ route('business') }}" class="user-btn">Back</a>
                    </span>
                </h5>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-card"><div class="num">{{ $summary['assigned'] }}</div><div class="lbl">Assigned Tags</div></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card"><div class="num text-warning">{{ $summary['sold'] }}</div><div class="lbl">Sold Tags</div></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card"><div class="num text-success">{{ $summary['registered'] }}</div><div class="lbl">Registered in App</div></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card"><div class="num text-danger">{{ $summary['pending'] }}</div><div class="lbl">Pending to Sell</div></div>
                        </div>
                    </div>

                    @if ($byTag->isNotEmpty())
                        <div class="table-responsive mt-4">
                            <table class="table table-sm border-top mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tag</th>
                                        <th class="text-end">Business Price</th>
                                        <th class="text-end">Assigned</th>
                                        <th class="text-end">Sold</th>
                                        <th class="text-end">Registered</th>
                                        <th class="text-end">Pending to Sell</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($byTag as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td class="text-end">{{ $row['business_price'] !== null ? '$' . number_format((float) $row['business_price'], 2) : '-' }}</td>
                                            <td class="text-end">{{ $row['assigned'] }}</td>
                                            <td class="text-end">{{ $row['sold'] }}</td>
                                            <td class="text-end">{{ $row['registered'] }}</td>
                                            <td class="text-end fw-semibold">{{ $row['pending'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <h5 class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>Mark Tags Sold</span>
                    <span class="d-flex flex-wrap align-items-center gap-2">
                        <select id="status-filter" class="form-select form-select-sm" style="width:auto;">
                            <option value="">All tags</option>
                            <option value="Pending to sell">Pending to sell</option>
                            <option value="Sold">Sold</option>
                            <option value="Registered">Registered</option>
                        </select>
                        <label for="sold-date" class="mb-0 text-muted small">Sold date</label>
                        <input type="date" id="sold-date" class="form-control form-control-sm" style="width:auto;"
                               value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                        <button type="button" id="btn-mark" class="user-btn" disabled>Mark as Sold (<span id="mark-count">0</span>)</button>
                        <button type="button" id="btn-unmark" class="user-btn outline" disabled>Undo Sold (<span id="unmark-count">0</span>)</button>
                    </span>
                </h5>
                <div class="card-datatable table-responsive table-d">
                    <table class="datatables-basic table border-top" id="data-table">
                        <thead class="table-light">
                            <tr>
                                <th><input class="form-check-input border-dark" type="checkbox" id="select-all" title="Select all shown"></th>
                                <th>S.No.</th>
                                <th>Tag</th>
                                <th>Reference No</th>
                                <th>Status</th>
                                <th>Sold At</th>
                                <th>Registered By</th>
                                <th>Registered At</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($codes as $key => $code)
                                @php $status = $code->status(); @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input border-dark row-check"
                                            value="{{ $code->id }}" data-status="{{ $status }}"
                                            {{ ($status === 'registered' || $code->business_invoice_id) ? 'disabled' : '' }}
                                            title="{{ $status === 'registered' ? 'Registered by a user - cannot be changed' : ($code->business_invoice_id ? 'On invoice ' . optional($code->invoice)->invoice_no . ' - cannot be changed' : 'Select') }}">
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $code->businessTag->name ?? 'Deleted Tag' }}</td>
                                    <td>{{ $code->reference_no }}</td>
                                    <td>
                                        @if ($status === 'registered')
                                            <span class="badge bg-label-success">Registered</span>
                                        @elseif ($status === 'sold')
                                            <span class="badge bg-label-warning">Sold</span>
                                        @else
                                            <span class="badge bg-label-secondary">Pending to sell</span>
                                        @endif
                                    </td>
                                    <td>{{ $code->sold_at ? $code->sold_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if ($code->registeredUser)
                                            {{ trim($code->registeredUser->first_name . ' ' . $code->registeredUser->last_name) }}
                                            <small class="text-muted d-block">
                                                {{ $code->registeredUser->email }}@if ($code->registeredUser->phone) &middot; {{ $code->registeredUser->phone }}@endif
                                            </small>
                                        @elseif ($code->isRegistered())
                                            <span class="text-muted">User removed</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $code->registered_at ? $code->registered_at->format('d M Y h:i A') : '-' }}</td>
                                    <td>
                                        @if ($code->invoice)
                                            <a href="{{ route('business-invoices.show', $code->invoice->id) }}">{{ $code->invoice->invoice_no }}</a>
                                        @else
                                            -
                                        @endif
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
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const markUrl = "{{ route('business.sold-tags.mark', $business->id) }}";
    const unmarkUrl = "{{ route('business.sold-tags.unmark', $business->id) }}";
    let dataTable;

    $(document).ready(function() {
        // Replace the global scrollX table (see assets/js/datatable.js) with a plain one
        if ($.fn.dataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().destroy();
        }
        dataTable = $('#data-table').DataTable({
            autoWidth: false,
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
            columnDefs: [{ orderable: false, targets: [0] }],
            order: [[1, 'asc']]
        });

        // Status column filter (column 4): All / Pending to sell / Sold / Registered
        $('#status-filter').on('change', function() {
            const v = $(this).val();
            dataTable.column(4).search(v, false, true).draw();
        });

        $('#select-all').on('change', function() {
            const checked = this.checked;
            dataTable.rows({ search: 'applied' }).nodes().to$().find('.row-check:not(:disabled)').prop('checked', checked);
            updateSelection();
        });
        $('#data-table').on('change', '.row-check', updateSelection);
        dataTable.on('draw', updateSelection);

        $('#btn-mark').on('click', markSold);
        $('#btn-unmark').on('click', undoSold);

        updateSelection();
    });

    // Checked ids across every page, split by what each action can act on.
    function selected(status) {
        return dataTable.$('.row-check:checked:not(:disabled)')
            .filter(function() { return $(this).data('status') === status; })
            .map(function() { return this.value; }).get();
    }

    function updateSelection() {
        const enabled = dataTable.$('.row-check:not(:disabled)');
        const checked = enabled.filter(':checked').length;
        const toMark = selected('assigned').length;
        const toUndo = selected('sold').length;

        $('#mark-count').text(toMark);
        $('#unmark-count').text(toUndo);
        $('#btn-mark').prop('disabled', toMark === 0);
        $('#btn-unmark').prop('disabled', toUndo === 0);

        const box = document.getElementById('select-all');
        box.checked = enabled.length > 0 && checked === enabled.length;
        box.indeterminate = checked > 0 && checked < enabled.length;
        box.disabled = enabled.length === 0;
    }

    function send(url, data, doneTitle) {
        return $.ajax({
            url: url,
            type: 'post',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: data
        }).done(function(res) {
            Swal.fire({ icon: 'success', title: doneTitle, text: res.message, timer: 1800, showConfirmButton: false })
                .then(() => window.location.reload());
        }).fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
            Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
        });
    }

    function markSold() {
        const ids = selected('assigned');
        if (!ids.length) return;
        const date = $('#sold-date').val();
        Swal.fire({
            title: 'Mark ' + ids.length + ' tag' + (ids.length === 1 ? '' : 's') + ' as sold?',
            text: 'Recorded as sold by {{ addslashes($business->business_name) }}' + (date ? ' on ' + date : '') + '. A sold tag can no longer be released from this business.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Mark as sold',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) send(markUrl, { code_ids: ids, sold_date: date }, 'Marked sold');
        });
    }

    function undoSold() {
        const ids = selected('sold');
        if (!ids.length) return;
        Swal.fire({
            title: 'Undo sold for ' + ids.length + ' tag' + (ids.length === 1 ? '' : 's') + '?',
            text: 'They go back to pending to sell. Tags a user has registered cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Undo sold',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) send(unmarkUrl, { code_ids: ids }, 'Updated');
        });
    }
</script>
@endsection
