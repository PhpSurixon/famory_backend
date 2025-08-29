@extends('layouts.admin-master', ['title' => 'Contact Us'])
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .btn.btn-danger{
            background: #ff000026;
            border: 1px solid #ffffff;
            color: #ff0000;
            max-width: 109px;
            box-shadow: none;
        }
    </style>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                <div id="deletebtn" style="display:none;">
                  <button id="delete-selected" class="btn btn-danger mt-3 mx-3 d-flex align-items-center gap-2 justify-content-center"><i class='bx bx-trash'></i> Delete</button>
                </div>    
                <div class="card-datatable table-responsive">
                        <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input class="form-check-input border-dark" type="checkbox" id="selectMultiple" />
                                        <label class="form-check-label" for="selectMultiple"></label>
                                    </th>
                                    <th>S.No.</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($contacts as $key => $contact)
                                    <tr>
                                        <!--<td><input type="checkbox" class="contact-checkbox" value="{{ $contact->id }}"></td>-->
                                        <td>
                                            <input name="userIds" value="{{ $contact->id }}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options{{ $contact->id }}" />
                                            <label class="form-check-label" for="options{{ $contact->id }}"></label>
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $contact->email }}</td>
                                        <td>{{ $contact->phone }}</td>
                                        <td>{{ $contact->message }}</td>
                                        <td>{{\Carbon\Carbon::parse($contact->created_at)->format('m/d/y') ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #data-table thead th:first-child .sorting,
        #data-table thead th:first-child .sorting_asc,
        #data-table thead th:first-child .sorting_desc {
            display: none !important;
        }
    
        #data-table thead th:first-child {
            position: relative;
        }
    
        #data-table thead th:first-child::after {
            content: "";
            display: none;
        }
        
        #data-table thead th:first-child::before {
            content: "";
            display: none;
        }
    </style>

    
    <script>
    $(document).ready(function() {
    var table = $('#data-table').DataTable();
    
    if (table) {
        table.destroy();
    }
    
    let dataTable = $('#data-table').DataTable({
            "columnDefs": [
                { "orderable": false, "targets": 0 } 
            ]
        });

  
  
    ////////////////
        
        
            // Function to update the header checkbox state
    function updateHeaderCheckbox() {
        const headerCheckbox = document.getElementById('selectMultiple');
        const childCheckboxes = dataTable.rows({ page: 'current' }).nodes().to$().find('.child-checkbox');
        const deleteButton = document.getElementById('deletebtn');
       

        const totalCheckboxes = childCheckboxes.length;
        const checkedCheckboxes = childCheckboxes.filter(':checked').length;

        if (totalCheckboxes === 0) {
            headerCheckbox.checked = false;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'none';
           
        } else if (checkedCheckboxes === totalCheckboxes) {
            headerCheckbox.checked = true;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'block';
           
        } else if (checkedCheckboxes > 0) {
            headerCheckbox.indeterminate = true;
            headerCheckbox.checked = false;
            deleteButton.style.display = 'block';
        } else {
            headerCheckbox.checked = false;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'none';
        }
    }

    // Handle header checkbox change
    $('#selectMultiple').on('change', function () {
        const isChecked = this.checked;
        dataTable.rows({ page: 'current' }).nodes().to$().find('.child-checkbox').prop('checked', isChecked);
        updateHeaderCheckbox();
    });

    // Handle individual checkbox change
    $('#data-table').on('change', '.child-checkbox', function () {
        updateHeaderCheckbox();
    });
        
        
        
        
        ///////////////
  
  

    $('#delete-selected').on('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
                .map(checkbox => checkbox.value);
                
                
        if (selectedIds.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve request!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("contactsDeleteSelected") }}',
                        type: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonText: 'OK',
                                    timer:3000,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                                setTimeout(() => {
                                        location.reload();
                                    }, 3000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong. Please try again.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again.',
                                timer: 2000, 
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select at least one contact to delete.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});

    </script>
@endsection


