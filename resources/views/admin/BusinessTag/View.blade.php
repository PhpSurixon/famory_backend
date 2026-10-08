@extends('layouts.admin-master', ['title' => 'Business Tags'])

@section('content')
<style>
    .hidden { display: none; }
    .img-circle { border-radius: 50%; width: 50px; height: 50px; object-fit: cover; }
    .table-d table tbody tr td { vertical-align: top; white-space: nowrap; }
    table .table-light tr th { text-align: center; white-space: nowrap; }
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
    .card-header { padding-bottom: 8px; }
    div#data-table_wrapper .row:nth-child(2) { overflow-x: auto; margin-bottom: 1rem; }
    #td-scroll { white-space: break-spaces; max-height: 70px; width: 300px; overflow-y: auto; scrollbar-width: thin; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-end">
                    <a href="{{ route('create-business-tag') }}" class="au-btn--green user-btn m-b-9">Add</a>
                </h5>
                <div class="table-responsive text-nowrap">
                    @if (session('success'))
                        <div class="alert alert-success" id="flashSuccessMessage">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                </div>
                <div class="card-datatable table-responsive table-d">
                    <table class="datatables-basic table border-top" id="data-table">
                        <thead class="table-light">
                            <tr>
                                <th>S.No.</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Origin Price</th>
                                <th>Selling Price</th>
                                <th>Description</th>
                                <th>Codes</th>
                                <th>Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($datas as $key => $data)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <img src="{{ !empty($data->image) ? $data->image : asset('assets/img/default_tag.jpg') }}"
                                            alt="Tag Image" class="img-circle" loading="lazy"
                                            onerror="this.src='{{ asset('assets/img/default_tag.jpg') }}'">
                                    </td>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ ucfirst($data->type_of_tag) }}</td>
                                    <td>${{ number_format($data->origin_price, 2) }}</td>
                                    <td>${{ number_format($data->selling_price, 2) }}</td>
                                    <td><div id="td-scroll">{{ $data->description ?? '-' }}</div></td>
                                    <td>
                                        <a href="{{ route('business-tag.codes', $data->id) }}">{{ $data->codes_count }}</a>
                                    </td>
                                    <td>
                                        <span class="badge {{ $data->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ $data->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="generateCodes('{{ $data->id }}', '{{ addslashes($data->name) }}')">
                                                    <i class="bx bx-barcode me-1"></i> Generate Codes
                                                </a>
                                                <a class="dropdown-item" href="{{ route('business-tag.codes', $data->id) }}">
                                                    <i class="bx bx-list-ul me-1"></i> View Codes
                                                </a>
                                                <a class="dropdown-item" href="{{ route('edit-business-tag', $data->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="deleteData('{{ $data->id }}')">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
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
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $(document).ready(function() {
        // Replace the global scrollX table (see assets/js/datatable.js) with a plain one
        if ($.fn.dataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().destroy();
        }
        $('#data-table').DataTable({
            autoWidth: false,
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
            columnDefs: [{ orderable: false, targets: [1, -1] }]
        });

        if ($("#flashSuccessMessage").length) {
            setTimeout(function() {
                $("#flashSuccessMessage").fadeOut("slow", function() { $(this).remove(); });
            }, 2000);
        }
    });

    function generateCodes(id, name) {
        Swal.fire({
            title: 'Generate Tag Codes',
            text: 'How many codes do you want to generate for "' + name + '"?',
            input: 'number',
            inputAttributes: { min: 1, max: 100, step: 1 },
            inputPlaceholder: 'Max 100',
            showCancelButton: true,
            confirmButtonText: 'Generate',
            showLoaderOnConfirm: true,
            preConfirm: (quantity) => {
                quantity = parseInt(quantity, 10);
                if (!quantity || quantity < 1 || quantity > 100) {
                    Swal.showValidationMessage('Enter a quantity between 1 and 100');
                    return false;
                }
                return $.ajax({
                    url: "{{ url('business-tag') }}/" + id + "/generate-codes",
                    type: "post",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: { quantity: quantity }
                }).catch(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                    Swal.showValidationMessage(msg);
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({ icon: 'success', title: 'Done', text: result.value.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            }
        });
    }

    function deleteData(id) {
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "This also deletes all tag codes generated for it. You will not be able to recover them!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('destroy-business-tag', '') }}/" + id,
                        type: "post",
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        }
    }
</script>
@endsection
