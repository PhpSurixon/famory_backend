@extends('layouts.admin-master', ['title' => $invoice ? 'Edit Invoice' : 'Create Invoice'])

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
    .user-btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
    .user-btn.outline { background: #fff; color: #1550AE; border-color: #1550AE; }
    .user-btn.outline:hover { background: #1550AE; color: #fff; }
    #codes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 8px;
        max-height: 320px;
        overflow-y: auto;
        padding: 4px;
    }
    .code-item {
        border: 1px solid #d9dee3;
        border-radius: 6px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        user-select: none;
    }
    .code-item:hover { border-color: #1550AE; }
    .code-item.checked { background: #e8f0fc; border-color: #1550AE; }
    .code-item small { margin-left: auto; color: #8592a3; }
    .ref-chip { display: inline-block; border: 1px solid #d9dee3; border-radius: 6px; padding: 2px 8px; margin: 0 4px 4px 0; background: #f5f7fa; font-size: .8rem; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <h5 class="card-header">{{ $invoice ? 'Edit Invoice ' . $invoice->invoice_no : 'Create Invoice' }}</h5>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="business_id">Business</label>
                            <select id="business_id" class="form-control" {{ $invoice ? 'disabled' : '' }}>
                                <option value="" disabled {{ $selectedBusinessId ? '' : 'selected' }}>Select business</option>
                                @foreach ($businesses as $b)
                                    <option value="{{ $b->id }}" {{ (int) $selectedBusinessId === (int) $b->id ? 'selected' : '' }}>{{ $b->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="invoice_date">Invoice Date</label>
                            <input type="date" id="invoice_date" class="form-control"
                                   value="{{ $invoice ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label" for="tax_percent">Tax % <small class="text-muted">(optional)</small></label>
                            <input type="number" id="tax_percent" class="form-control" min="0" max="100" step="0.01"
                                   value="{{ $invoice ? rtrim(rtrim(number_format($invoice->tax_percent, 2, '.', ''), '0'), '.') ?: 0 : 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bill_to_address">Billing Address <small class="text-muted">(optional)</small></label>
                            <textarea id="bill_to_address" class="form-control" rows="3" maxlength="1000"
                                      placeholder="Street, city, state, zip...">{{ $invoice->bill_to_address ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="notes">Notes <small class="text-muted">(optional)</small></label>
                            <textarea id="notes" class="form-control" rows="3" maxlength="2000"
                                      placeholder="Shown on the invoice">{{ $invoice->notes ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h5 class="card-header">Add Business Tag</h5>
                <div class="card-body">
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="tag_select">Business Tag</label>
                            <select id="tag_select" class="form-control" disabled>
                                <option value="" selected disabled>Select a business first</option>
                            </select>
                            <small class="text-muted" id="tag-hint">Only tags the business has sold and not yet invoiced are listed.</small>
                        </div>
                    </div>

                    <div id="codes-panel" style="display:none;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input border-dark" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">Select all</label>
                            </div>
                            <input type="text" id="code-search" class="form-control form-control-sm" placeholder="Search reference no..." style="max-width:220px;">
                            <div class="text-muted"><strong id="picked-count">0</strong> selected &middot; <span id="picked-price">$0.00</span></div>
                        </div>
                        <div id="codes-grid"></div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" id="cancel-line-btn" class="user-btn outline">Cancel</button>
                            <button type="button" id="add-line-btn" class="user-btn" disabled>Add to invoice</button>
                        </div>
                    </div>
                    <div id="codes-loading" class="text-muted" style="display:none;">Loading codes...</div>
                </div>
            </div>

            <div class="card mb-4">
                <h5 class="card-header">Invoice Lines</h5>
                <div class="table-responsive">
                    <table class="table border-top mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Business Tag</th>
                                <th>Tag codes (reference no.)</th>
                                <th class="text-end">Codes</th>
                                <th class="text-end">Price per code</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="lines-body">
                            <tr><td colspan="6" class="text-center text-muted py-4">No tags added yet.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm mb-0">
                                <tr><td>Total codes</td><td class="text-end" id="sum-codes">0</td></tr>
                                <tr><td>Subtotal</td><td class="text-end" id="sum-subtotal">$0.00</td></tr>
                                <tr><td>Tax <span id="sum-tax-pct" class="text-muted"></span></td><td class="text-end" id="sum-tax">$0.00</td></tr>
                                <tr><td><strong>Total</strong></td><td class="text-end"><strong id="sum-total">$0.00</strong></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a href="{{ $invoice ? route('business-invoices.show', $invoice->id) : route('business-invoices') }}" class="user-btn outline">Back</a>
                        <button type="button" id="save-draft-btn" class="user-btn outline">Save as Draft</button>
                        <button type="button" id="save-issue-btn" class="user-btn">Save &amp; Issue</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const eligibleTagsUrl = "{{ route('business-invoices.eligible-tags') }}";
    const eligibleCodesUrl = "{{ route('business-invoices.eligible-codes') }}";
    const saveUrl = "{{ $invoice ? route('business-invoices.update', $invoice->id) : route('business-invoices.store') }}";
    const invoiceId = {{ $invoice ? $invoice->id : 'null' }};
    const editing = invoiceId !== null;

    // Lines already on the invoice (edit) or added by the admin (create)
    let lines = @json($initialLines);
    let tagCache = {};   // tag id -> { name, unit_price } from the eligible-tags call

    function money(n) { return '$' + Number(n).toFixed(2); }
    function businessId() { return $('#business_id').val(); }

    /* ---------- lines table + totals ---------- */
    function renderLines() {
        const body = $('#lines-body').empty();
        if (!lines.length) {
            body.append('<tr><td colspan="6" class="text-center text-muted py-4">No tags added yet.</td></tr>');
        }
        lines.forEach(function(line, idx) {
            const tr = $('<tr></tr>');
            tr.append($('<td></td>').text(line.tag_name));
            const refs = $('<td></td>');
            line.refs.forEach(function(r) { refs.append($('<span class="ref-chip"></span>').text(r)); });
            tr.append(refs);
            tr.append($('<td class="text-end"></td>').text(line.code_ids.length));
            tr.append($('<td class="text-end"></td>').text(money(line.unit_price)));
            tr.append($('<td class="text-end"></td>').text(money(line.code_ids.length * line.unit_price)));
            const actions = $('<td class="text-end text-nowrap"></td>');
            actions.append($('<a href="javascript:void(0);" class="me-3">Edit</a>').on('click', function() { editLine(idx); }));
            actions.append($('<a href="javascript:void(0);" class="text-danger">Remove</a>').on('click', function() { removeLine(idx); }));
            tr.append(actions);
            body.append(tr);
        });
        renderTotals();
    }

    function renderTotals() {
        let codes = 0, subtotal = 0;
        lines.forEach(function(l) { codes += l.code_ids.length; subtotal += l.code_ids.length * l.unit_price; });
        const pct = Math.max(0, Math.min(100, parseFloat($('#tax_percent').val()) || 0));
        const tax = Math.round(subtotal * pct) / 100;
        $('#sum-codes').text(codes);
        $('#sum-subtotal').text(money(subtotal));
        $('#sum-tax-pct').text(pct > 0 ? '(' + pct + '%)' : '');
        $('#sum-tax').text(money(tax));
        $('#sum-total').text(money(subtotal + tax));
    }

    function removeLine(idx) { lines.splice(idx, 1); renderLines(); loadTags(); }

    function editLine(idx) {
        const line = lines[idx];
        if (!$('#tag_select option[value="' + line.business_tag_id + '"]').length) {
            Swal.fire({ icon: 'info', title: 'Not available', text: 'This tag has no billable codes left to edit.' });
            return;
        }
        $('#tag_select').val(line.business_tag_id).trigger('change');
        $('html, body').animate({ scrollTop: $('#tag_select').offset().top - 120 }, 250);
    }

    /* ---------- tag + code pickers ---------- */
    function resetPanel() {
        $('#codes-panel').hide();
        $('#codes-grid').empty();
        $('#tag_select').val('');
        $('#code-search').val('');
        $('#add-line-btn').prop('disabled', true);
    }

    function loadTags() {
        const bid = businessId();
        const select = $('#tag_select');
        resetPanel();
        if (!bid) { return; }

        select.prop('disabled', true).html('<option value="" selected disabled>Loading tags...</option>');
        $.get(eligibleTagsUrl, { business_id: bid, invoice_id: invoiceId }).done(function(res) {
            tagCache = {};
            select.empty();
            if (!res.tags.length) {
                select.append('<option value="" selected disabled>No billable tags for this business</option>');
                $('#tag-hint').text('This business has no sold tags waiting to be invoiced.');
                return;
            }
            select.append('<option value="" selected disabled>Select a Business Tag</option>');
            res.tags.forEach(function(t) {
                tagCache[t.id] = { name: t.name, unit_price: t.unit_price };
                select.append($('<option></option>').val(t.id)
                    .text(t.name + ' (' + t.available + ' available, ' + money(t.unit_price) + ' per code)'));
            });
            select.prop('disabled', false);
            $('#tag-hint').text('Only tags the business has sold and not yet invoiced are listed.');
        }).fail(function() {
            select.html('<option value="" selected disabled>Unable to load tags</option>');
        });
    }

    $('#business_id').on('change', function() {
        const previous = $(this).data('previous');
        if (lines.length && previous && previous !== this.value) {
            const select = this;
            Swal.fire({
                title: 'Change business?',
                text: 'The tags added so far will be cleared.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Change',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) { lines = []; renderLines(); $(select).data('previous', select.value); loadTags(); }
                else { $(select).val(previous); }
            });
            return;
        }
        $(this).data('previous', this.value);
        loadTags();
    });

    $('#tag_select').on('change', function() {
        const tagId = this.value;
        if (!tagId) { return; }
        $('#codes-loading').show();
        $('#codes-panel').hide();

        $.get(eligibleCodesUrl, { business_id: businessId(), business_tag_id: tagId, invoice_id: invoiceId }).done(function(res) {
            const existing = lines.find(function(l) { return String(l.business_tag_id) === String(tagId); });
            const preselected = existing ? existing.code_ids.map(String) : [];
            const grid = $('#codes-grid').empty();

            res.codes.forEach(function(c) {
                const item = $('<label class="code-item"></label>').attr('data-code', c.reference_no.toLowerCase());
                const cb = $('<input type="checkbox" class="form-check-input border-dark code-check">')
                    .val(c.id).data('ref', c.reference_no).prop('checked', preselected.indexOf(String(c.id)) !== -1);
                item.append(cb).append($('<span></span>').text(c.reference_no));
                if (c.sold_at) { item.append($('<small></small>').text('Sold ' + c.sold_at)); }
                grid.append(item);
            });
            $('#codes-loading').hide();
            $('#codes-panel').show();
            updatePicked();
        }).fail(function() {
            $('#codes-loading').hide();
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Unable to load tag codes.' });
        });
    });

    function updatePicked() {
        const all = $('#codes-grid .code-check');
        const checked = all.filter(':checked').length;
        const tag = tagCache[$('#tag_select').val()];
        $('#picked-count').text(checked);
        $('#picked-price').text(tag ? money(checked * tag.unit_price) : '$0.00');
        $('#select-all').prop('checked', all.length > 0 && checked === all.length)
                        .prop('indeterminate', checked > 0 && checked < all.length)
                        .prop('disabled', all.length === 0);
        $('#add-line-btn').prop('disabled', checked === 0);
        $('#codes-grid .code-item').each(function() {
            $(this).toggleClass('checked', $(this).find('.code-check').is(':checked'));
        });
    }

    $('#codes-grid').on('change', '.code-check', updatePicked);
    $('#select-all').on('change', function() {
        $('#codes-grid .code-item:visible .code-check').prop('checked', this.checked);
        updatePicked();
    });
    $('#code-search').on('input', function() {
        const q = $(this).val().trim().toLowerCase();
        $('#codes-grid .code-item').each(function() { $(this).toggle($(this).data('code').indexOf(q) !== -1); });
    });
    $('#cancel-line-btn').on('click', resetPanel);

    $('#add-line-btn').on('click', function() {
        const tagId = $('#tag_select').val();
        const tag = tagCache[tagId];
        const checked = $('#codes-grid .code-check:checked');
        if (!tag || !checked.length) { return; }

        const line = {
            business_tag_id: parseInt(tagId, 10),
            tag_name: tag.name,
            unit_price: tag.unit_price,
            code_ids: checked.map(function() { return parseInt(this.value, 10); }).get(),
            refs: checked.map(function() { return $(this).data('ref'); }).get()
        };
        const at = lines.findIndex(function(l) { return String(l.business_tag_id) === String(tagId); });
        if (at === -1) { lines.push(line); } else { lines[at] = line; }

        resetPanel();
        renderLines();
    });

    $('#tax_percent').on('input change', renderTotals);

    /* ---------- save ---------- */
    function save(action) {
        if (!businessId()) { Swal.fire({ icon: 'error', title: 'Oops...', text: 'Choose a business.' }); return; }
        if (!$('#invoice_date').val()) { Swal.fire({ icon: 'error', title: 'Oops...', text: 'Enter the invoice date.' }); return; }
        if (!lines.length) { Swal.fire({ icon: 'error', title: 'Oops...', text: 'Add at least one Business Tag to the invoice.' }); return; }

        const payload = {
            business_id: businessId(),
            invoice_date: $('#invoice_date').val(),
            bill_to_address: $('#bill_to_address').val(),
            notes: $('#notes').val(),
            tax_percent: $('#tax_percent').val() === '' ? 0 : $('#tax_percent').val(),
            action: action,
            lines: lines.map(function(l) { return { business_tag_id: l.business_tag_id, code_ids: l.code_ids }; })
        };

        function send() {
            $('#save-draft-btn, #save-issue-btn').prop('disabled', true);
            $.ajax({
                url: saveUrl,
                type: 'post',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: 'application/json',
                data: JSON.stringify(payload)
            }).done(function(res) {
                Swal.fire({ icon: 'success', title: 'Saved', text: res.message, timer: 1500, showConfirmButton: false })
                    .then(function() { window.location.href = res.redirect; });
            }).fail(function(xhr) {
                $('#save-draft-btn, #save-issue-btn').prop('disabled', false);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
            });
        }

        if (action === 'issue') {
            Swal.fire({
                title: 'Save & issue this invoice?',
                text: 'An issued invoice can no longer be edited.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Issue',
                reverseButtons: true
            }).then(function(result) { if (result.isConfirmed) send(); });
        } else {
            send();
        }
    }

    $('#save-draft-btn').on('click', function() { save('draft'); });
    $('#save-issue-btn').on('click', function() { save('issue'); });

    /* ---------- start ---------- */
    $(document).ready(function() {
        $('#business_id').data('previous', $('#business_id').val());
        renderLines();
        if (businessId()) { loadTags(); }
    });
</script>
@endsection
