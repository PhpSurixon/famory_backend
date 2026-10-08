@extends('layouts.admin-master', ['title' => 'Assign Tag Codes'])

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
    }
    .user-btn:hover {
        color: #1550AE;
        background-color: #fff;
        border-color: #1550AE;
        transform: translateY(-1px);
    }
    .user-btn:disabled { opacity: .6; pointer-events: none; }
    #codes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 8px;
        max-height: 420px;
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
    .code-item.sold { cursor: not-allowed; background: #f6f7f9; border-color: #d9dee3; }
    .code-item .badge { margin-left: auto; }
    .button-container { display: flex; justify-content: flex-end; gap: 10px; }
    .assigned-grid { display: flex; flex-wrap: wrap; gap: 8px; max-height: 240px; overflow-y: auto; }
    .assigned-chip {
        border: 1px solid #d9dee3;
        border-radius: 6px;
        padding: 4px 10px;
        background: #f5f7fa;
        font-size: 0.9rem;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assign Tag Codes &mdash; {{ $business->business_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="business_tag_id">Business Tag</label>
                            <select class="form-control" id="business_tag_id">
                                <option value="" selected disabled>Select Business Tag</option>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}">
                                        {{ $tag->name }} ({{ $tag->available_count }} available, {{ $tag->assigned_count }} assigned to this business, {{ $tag->sold_count }} sold)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="codes-section" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label" for="business_price">Business Price ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="business_price" placeholder="0.00">
                                <small class="text-muted" id="price-hint"></small>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                            <div class="form-check mb-0">
                                <input class="form-check-input border-dark" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">Select all</label>
                            </div>
                            <div>
                                <input type="text" id="code-search" class="form-control form-control-sm" placeholder="Search reference no..." style="min-width:200px;">
                            </div>
                            <div id="change-summary" class="text-muted">No changes</div>
                        </div>
                        <div id="codes-grid"></div>
                        <small class="text-muted d-block mt-2">Tags already registered by users are not listed here. See "Assigned Tag Codes" below or the Sold Report.</small>
                        <p id="no-codes" class="text-muted mt-3" style="display:none;">No tag codes available for this tag.</p>
                    </div>
                    <div id="codes-loading" class="text-muted" style="display:none;">Loading codes...</div>

                    <br/>
                    <div class="button-container">
                        <a href="{{ route('business') }}" class="btn btn-primary">Back</a>
                        <button type="button" id="save-btn" class="user-btn" disabled>Save</button>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assigned Tag Codes ({{ $assignedCodes->count() }})</h5>
                </div>
                <div class="card-body">
                    @if ($assignedCodes->isEmpty())
                        <p class="text-muted mb-0">No tag codes are assigned to this business yet.</p>
                    @else
                        @foreach ($assignedCodes->groupBy('business_tag_id') as $tagId => $group)
                            @php
                                $sellingPrice = $group->first()->businessTag->selling_price ?? null;
                                $businessPrice = $prices[$tagId] ?? $sellingPrice;
                            @endphp
                            <div class="mb-3">
                                <h6 class="mb-2">
                                    {{ $group->first()->businessTag->name ?? 'Deleted Tag' }}
                                    <span class="badge bg-label-primary ms-1">{{ $group->count() }}</span>
                                    @if ($businessPrice !== null)
                                        <span class="badge bg-label-success ms-1">Business Price: ${{ number_format((float) $businessPrice, 2) }}</span>
                                        @if ($sellingPrice !== null)
                                            <small class="text-muted ms-1">Selling price ${{ number_format((float) $sellingPrice, 2) }}</small>
                                        @endif
                                    @endif
                                </h6>
                                <div class="assigned-grid">
                                    @foreach ($group as $code)
                                        @if ($code->isRegistered())
                                            <span class="assigned-chip" title="Registered by {{ trim(($code->registeredUser->first_name ?? '') . ' ' . ($code->registeredUser->last_name ?? '')) }} ({{ $code->registeredUser->email ?? '-' }}) on {{ optional($code->registered_at)->format('d M Y h:i A') }}">
                                                {{ $code->reference_no }} <span class="badge bg-label-success ms-1">Registered</span>
                                            </span>
                                        @elseif ($code->isSold())
                                            <span class="assigned-chip" title="Sold on {{ optional($code->sold_at)->format('d M Y h:i A') }}">
                                                {{ $code->reference_no }} <span class="badge bg-label-warning ms-1">Sold</span>
                                            </span>
                                        @else
                                            <span class="assigned-chip" title="Assigned {{ optional($code->assigned_at)->format('d M Y h:i A') }}">{{ $code->reference_no }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const codesUrl = "{{ route('business.assign-tags.codes', $business->id) }}";
    const saveUrl = "{{ route('business.assign-tags.save', $business->id) }}";

    // Unsold codes can be ticked (assign) or unticked (release). Sold codes are locked.
    // Each checkbox remembers its saved state in data-initial so only real changes are counted.
    // Business price the page loaded with (saved price, or the tag's selling price as the default).
    let initialPrice = null;

    function priceValue() {
        const v = parseFloat($('#business_price').val());
        return isNaN(v) ? null : v;
    }

    function pendingChanges() {
        const pickable = $('#codes-grid .code-check:not(:disabled)');
        let toAssign = 0, toRelease = 0;
        pickable.each(function() {
            const initial = $(this).data('initial') === 1;
            if (this.checked && !initial) toAssign++;
            if (!this.checked && initial) toRelease++;
        });
        const price = priceValue();
        const priceChanged = price !== null && initialPrice !== null && price.toFixed(2) !== initialPrice.toFixed(2);
        return { pickable: pickable, toAssign: toAssign, toRelease: toRelease, priceChanged: priceChanged };
    }

    function updateCount() {
        const p = pendingChanges();
        const total = p.pickable.length;
        const checked = p.pickable.filter(':checked').length;

        const parts = [];
        if (p.toAssign) parts.push(p.toAssign + ' to assign');
        if (p.toRelease) parts.push(p.toRelease + ' to release');
        if (p.priceChanged) parts.push('price changed');
        $('#change-summary').text(parts.length ? parts.join(', ') : 'No changes')
                            .toggleClass('text-muted', parts.length === 0);

        $('#select-all').prop('checked', total > 0 && checked === total)
                        .prop('indeterminate', checked > 0 && checked < total)
                        .prop('disabled', total === 0);
        $('#save-btn').prop('disabled', p.toAssign + p.toRelease === 0 && !p.priceChanged);
        $('#codes-grid .code-item').each(function() {
            $(this).toggleClass('checked', $(this).find('.code-check').is(':checked'));
        });
    }

    $('#business_tag_id').on('change', function() {
        const tagId = $(this).val();
        $('#codes-section').hide();
        $('#codes-loading').show();
        $('#save-btn').prop('disabled', true);
        $('#code-search').val('');

        $.get(codesUrl, { business_tag_id: tagId }).done(function(res) {
            const grid = $('#codes-grid').empty();
            res.codes.forEach(function(c) {
                const item = $('<label class="code-item"></label>').attr('data-code', c.reference_no.toLowerCase());
                const cb = $('<input type="checkbox" class="form-check-input border-dark code-check">')
                    .val(c.id).prop('checked', c.assigned).prop('disabled', c.sold)
                    .data('initial', c.assigned ? 1 : 0);
                item.append(cb).append($('<span></span>').text(c.reference_no));
                if (c.sold) {
                    // Sold by the shop but not registered yet: locked, cannot be released or moved
                    item.addClass('sold').attr('title', 'Sold by the shop - cannot be changed');
                    item.append('<span class="badge bg-label-warning">Sold</span>');
                } else if (c.assigned) {
                    // Assigned but not sold yet: untick it to release it for another business
                    item.attr('title', 'Assigned - untick and save to release it');
                    item.append('<span class="badge bg-label-success">Assigned</span>');
                }
                grid.append(item);
            });
            // Business price defaults to the tag's selling price until one is saved for this business
            const sellingPrice = parseFloat(res.selling_price);
            const savedPrice = res.business_price !== null ? parseFloat(res.business_price) : null;
            initialPrice = savedPrice !== null ? savedPrice : sellingPrice;
            $('#business_price').val(initialPrice.toFixed(2));
            $('#price-hint').text('Selling price: $' + sellingPrice.toFixed(2)
                + (savedPrice === null ? ' (default for this business)' : ''));

            $('#no-codes').toggle(res.codes.length === 0);
            $('#codes-loading').hide();
            $('#codes-section').show();
            updateCount();
        }).fail(function() {
            $('#codes-loading').hide();
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Unable to load tag codes.' });
        });
    });

    $('#codes-grid').on('change', '.code-check', updateCount);
    $('#business_price').on('input change', updateCount);

    $('#select-all').on('change', function() {
        $('#codes-grid .code-item:visible .code-check:not(:disabled)').prop('checked', this.checked);
        updateCount();
    });

    $('#code-search').on('input', function() {
        const q = $(this).val().trim().toLowerCase();
        $('#codes-grid .code-item').each(function() {
            $(this).toggle($(this).data('code').indexOf(q) !== -1);
        });
    });

    $('#save-btn').on('click', function() {
        const tagId = $('#business_tag_id').val();
        if (!tagId) return;
        const p = pendingChanges();
        if (p.toAssign + p.toRelease === 0 && !p.priceChanged) return;
        const ids = p.pickable.filter(':checked').map(function() { return this.value; }).get();

        const price = priceValue();
        if (price === null || price < 0) {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Enter a valid business price (0 or more).' });
            return;
        }

        function save() {
            $.ajax({
                url: saveUrl,
                type: 'post',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { business_tag_id: tagId, code_ids: ids, business_price: price.toFixed(2) }
            }).done(function(res) {
                Swal.fire({ icon: 'success', title: 'Saved', text: res.message, timer: 2000, showConfirmButton: false })
                    .then(() => { window.location.reload(); });
            }).fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
            });
        }

        if (p.toRelease > 0) {
            Swal.fire({
                title: 'Release ' + p.toRelease + ' code' + (p.toRelease === 1 ? '' : 's') + '?',
                text: 'They will be removed from this business and can be assigned to another business.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Save changes',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => { if (result.isConfirmed) save(); });
        } else {
            save();
        }
    });
</script>
@endsection
