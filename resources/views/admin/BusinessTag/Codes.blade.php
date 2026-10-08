@extends('layouts.admin-master', ['title' => 'Business Tag Codes'])

@section('content')
@php
    $pendingCount = $codes->whereNull('qr_downloaded_at')->count();
@endphp
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
    .user-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
        pointer-events: none;
    }
    table .table-light tr th { text-align: center; white-space: nowrap; }
    div#data-table_wrapper .row:nth-child(2) { overflow-x: auto; margin-bottom: 1rem; }
    .table-d table tbody tr td { vertical-align: middle; white-space: nowrap; }
    tr.qr-done td { background-color: #f6f7f9; }
    tr.qr-done .qr-cell { opacity: .55; }
    .download-link.disabled { color: #a1acb8; pointer-events: none; cursor: not-allowed; text-decoration: none; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>{{ $tag->name }} &mdash; Tag Codes ({{ $codes->count() }})</span>
                    <span class="d-flex flex-wrap gap-2">
                        <a href="{{ route('business-tag') }}" class="user-btn">Back</a>
                        <a href="{{ route('business-tag.codes.export', $tag->id) }}" class="user-btn">Export CSV</a>
                        <button type="button" id="btn-selected" class="user-btn" disabled>Download Selected (<span id="selected-count">0</span>)</button>
                        <button type="button" id="btn-all" class="user-btn" {{ $pendingCount ? '' : 'disabled' }}>Download All Pending ({{ $pendingCount }})</button>
                    </span>
                </h5>
                <div class="card-datatable table-responsive table-d">
                    <table class="datatables-basic table border-top" id="data-table">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input class="form-check-input border-dark" type="checkbox" id="select-all" title="Select all pending">
                                </th>
                                <th>S.No.</th>
                                <th>QR</th>
                                <th>Reference No</th>
                                <th>Tag Code</th>
                                <th>Printable Tag Link</th>
                                <th>Assigned Business</th>
                                <th>Sale Status</th>
                                <th>Sold At</th>
                                <th>Registered By</th>
                                <th>Registered At</th>
                                <th>Created At</th>
                                <th>QR Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($codes as $key => $code)
                                @php $done = !is_null($code->qr_downloaded_at); @endphp
                                <tr class="{{ $done ? 'qr-done' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input border-dark row-check"
                                            value="{{ $code->id }}" {{ $done ? 'disabled' : '' }}
                                            title="{{ $done ? 'QR already downloaded' : 'Select to download QR' }}">
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="qr-cell">{!! QrCode::size(60)->margin(0)->generate(\App\Services\QrPngService::tagUrl($code->tag_code)) !!}</td>
                                    <td>{{ $code->reference_no }}</td>
                                    <td>{{ $code->tag_code }}</td>
                                    <td>{{ \App\Services\QrPngService::tagUrl($code->tag_code) }}</td>
                                    <td>{{ $code->business->business_name ?? '-' }}</td>
                                    <td>
                                        @switch($code->status())
                                            @case('registered')
                                                <span class="badge bg-label-success">Registered</span>
                                                @break
                                            @case('sold')
                                                <span class="badge bg-label-warning">Sold</span>
                                                @break
                                            @case('assigned')
                                                <span class="badge bg-label-info">Assigned</span>
                                                @break
                                            @default
                                                <span class="badge bg-label-secondary">Available</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $code->sold_at ? $code->sold_at->format('d M Y h:i A') : '-' }}</td>
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
                                    <td>{{ $code->created_at->format('d M Y h:i A') }}</td>
                                    <td>
                                        @if ($done)
                                            <span class="badge bg-label-success">Downloaded</span>
                                            <small class="text-muted d-block">{{ $code->qr_downloaded_at->format('d M Y h:i A') }}</small>
                                        @else
                                            <span class="badge bg-label-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($done)
                                            <a href="javascript:void(0);" class="download-link disabled" title="QR already downloaded">
                                                <i class="bx bx-download"></i> Download PNG
                                            </a>
                                        @else
                                            <a href="javascript:void(0);" class="download-link pending" data-id="{{ $code->id }}" title="Download QR as PNG">
                                                <i class="bx bx-download"></i> Download PNG
                                            </a>
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
    const downloadUrl = "{{ route('business-tag.codes.download-qr', $tag->id) }}";
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
            columnDefs: [{ orderable: false, targets: [0, 2, -1] }],
            order: [[1, 'asc']]
        });

        // Checkboxes live in all pages, so use the DataTables API rather than the DOM.
        $('#select-all').on('change', function() {
            const checked = this.checked;
            dataTable.rows({ search: 'applied' }).nodes().to$().find('.row-check:not(:disabled)').prop('checked', checked);
            updateSelection();
        });
        $('#data-table').on('change', '.row-check', updateSelection);
        dataTable.on('draw', updateSelection);

        $('#data-table').on('click', '.download-link.pending:not(.disabled)', function() {
            downloadQr([$(this).data('id')]);
        });

        $('#btn-selected').on('click', function() {
            const ids = selectedIds();
            if (ids.length) downloadQr(ids);
        });
        $('#btn-all').on('click', function() { downloadQr(null); });

        updateSelection();
    });

    function selectedIds() {
        return dataTable.$('.row-check:checked:not(:disabled)').map(function() { return this.value; }).get();
    }

    function updateSelection() {
        const all = dataTable.$('.row-check:not(:disabled)');
        const checked = all.filter(':checked').length;
        $('#selected-count').text(checked);
        $('#btn-selected').prop('disabled', checked === 0);
        const box = document.getElementById('select-all');
        box.checked = all.length > 0 && checked === all.length;
        box.indeterminate = checked > 0 && checked < all.length;
        box.disabled = all.length === 0;

        // A row's own download link is disabled while its checkbox is ticked
        // (the selection is downloaded with "Download Selected" instead).
        dataTable.rows().nodes().to$().each(function() {
            const row = $(this);
            row.find('.download-link.pending').toggleClass('disabled', row.find('.row-check').is(':checked'));
        });
    }

    // ids = array of code ids, or null for every pending code
    function downloadQr(ids) {
        const count = ids ? ids.length : {{ $pendingCount }};
        Swal.fire({
            title: 'Download ' + count + ' QR code' + (count === 1 ? '' : 's') + '?',
            text: 'Once downloaded, these QR codes cannot be downloaded again.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Download',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: () => {
                return fetch(downloadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(ids ? { ids: ids } : { all: true })
                }).then(async (res) => {
                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        throw new Error(data.message || 'Download failed');
                    }
                    const disposition = res.headers.get('Content-Disposition') || '';
                    const match = /filename="?([^";]+)"?/.exec(disposition);
                    const blob = await res.blob();
                    return { blob: blob, name: match ? match[1] : 'qr-codes' };
                }).catch((err) => {
                    Swal.showValidationMessage(err.message);
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const url = URL.createObjectURL(result.value.blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = result.value.name;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(url), 5000);

                Swal.fire({ icon: 'success', title: 'Downloaded', text: result.value.name, timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            }
        });
    }
</script>
@endsection
