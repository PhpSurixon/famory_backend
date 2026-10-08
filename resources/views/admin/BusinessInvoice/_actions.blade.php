<script type="text/javascript">
    // Issue / Mark paid / Cancel, shared by the invoice list and the invoice page.
    const invoiceBase = "{{ url('business-invoices') }}";
    const invoiceCsrf = $('meta[name="csrf-token"]').attr('content');
    const paymentMethods = @json(\App\Models\BusinessInvoice::PAYMENT_METHODS);
    const todayStr = "{{ now()->format('Y-m-d') }}";

    function invoiceRequest(id, action, data, doneTitle) {
        return $.ajax({
            url: invoiceBase + '/' + id + '/' + action,
            type: 'post',
            headers: { 'X-CSRF-TOKEN': invoiceCsrf },
            data: data
        }).done(function(res) {
            Swal.fire({ icon: 'success', title: doneTitle, text: res.message, timer: 1800, showConfirmButton: false })
                .then(function() { window.location.reload(); });
        }).fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
            Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
        });
    }

    function issueInvoice(id, no) {
        Swal.fire({
            title: 'Issue ' + no + '?',
            text: 'An issued invoice can no longer be edited. You can still mark it paid or cancel it.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Issue invoice',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) invoiceRequest(id, 'issue', {}, 'Issued');
        });
    }

    function payInvoice(id, no) {
        let options = '';
        paymentMethods.forEach(function(m) { options += '<option value="' + m + '">' + m + '</option>'; });

        Swal.fire({
            title: 'Mark ' + no + ' as paid',
            html:
                '<select id="pay-method" class="swal2-select" style="display:flex;width:80%;margin:12px auto 0;">' +
                    '<option value="" disabled selected>Payment method</option>' + options + '</select>' +
                '<input id="pay-date" type="date" class="swal2-input" value="' + todayStr + '" max="' + todayStr + '" title="Date paid">' +
                '<input id="pay-ref" class="swal2-input" maxlength="100" placeholder="Payment reference (optional)">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Mark as paid',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            preConfirm: function() {
                const method = document.getElementById('pay-method').value;
                const date = document.getElementById('pay-date').value;
                if (!method) { Swal.showValidationMessage('Choose how the invoice was paid'); return false; }
                if (!date) { Swal.showValidationMessage('Enter the date the payment was received'); return false; }
                return { payment_method: method, paid_date: date, payment_reference: document.getElementById('pay-ref').value };
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) invoiceRequest(id, 'paid', result.value, 'Paid');
        });
    }

    function cancelInvoice(id, no) {
        Swal.fire({
            title: 'Cancel ' + no + '?',
            text: 'The tag codes on it become billable again. The invoice stays on record as cancelled.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Reason for cancelling',
            inputValidator: function(value) {
                return (!value || value.trim().length < 3) ? 'Enter a reason for cancelling the invoice' : null;
            },
            showCancelButton: true,
            confirmButtonText: 'Cancel invoice',
            cancelButtonText: 'Keep invoice',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) invoiceRequest(id, 'cancel', { cancel_reason: result.value }, 'Cancelled');
        });
    }
</script>
