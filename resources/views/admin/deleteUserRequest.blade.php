@extends('layouts.admin-master', ['title' => 'Delete Account Request'])

@section('content')
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
        .btn.btn-danger{
        background: #ff000026;
        border: 1px solid #ffffff;
        color: #ff0000;
        max-width: 109px;
        box-shadow: none;
    }
    
    .p-btn {
        padding-inline: 16px;
    }
    </style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
           
            <div class="card">
                <!--<button id="deleteUsers"  onclick="deleteSelectedUsers()" class="btn btn-danger mt-3 mx-3 d-flex align-items-center gap-2 justify-content-center"><i class='bx bx-trash'></i> Delete</button>-->
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 justify-content-start">
                        <div class="p-btn">
                            <a id="deleteUsers" onclick="deleteSelectedUsers()" style="display: none;" title="Accept Request" href="javascript:void(0);"><i class='bx bx-check-circle fs-2' style="color: #0a700a;"></i></i></a>
                            <a id="rejectRequests" title="Reject Request" style="display: none;" href="javascript:void(0);" onclick="rejectSelectedRequests()"><i class='bx bxs-x-circle fs-2' style="color: darkred;"></i></a>
                      </div>  
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="datatables-basic table border-top" id="unique-data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input class="form-check-input border-dark" type="checkbox" id="selectMultiple" />
                                        <label class="form-check-label" for="selectMultiple"></label>
                                    </th>
                                    <th>S.No.</th>
                                    <th>Email</th>
                                    <th>Phone Number</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Reason for deletion</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($getdeleteUser as $key => $contact)
                                        <tr>
                                            <td>
                                                <input name="userIds" value="{{ $contact->user_id }}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options{{ $contact->id }}" />
                                                <label class="form-check-label" for="options{{ $contact->id }}"></label>
                                            </td>
                                            <td>{{ $key + 1 ?? 'N/A'}}</td>
                                            <td>{{ $contact->email ?? 'N/A'}}</td>
                                            <td>{{ $contact->phone_number ?? 'N/A'}}</td>
                                            <td>{{ $contact->source ?? 'N/A'}}</td>
                                            <td>
                                                <span style="{{ $contact->status == 0 ? 'color: #c6771a;' : '' }}">
                                                    {{ $contact->status == 0 ? 'Suspended' : $contact->status }}
                                                </span>
                                            </td>
                                            <td>{{ $contact->reason_for_deletion ?? 'N/A'}}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    
                                                <form id="deleteForm{{ $contact->user_id }}" action="{{ route('softDeleteUser') }}" method="POST">
                                                    <input type="hidden" name="id" value="{{ $contact->user_id }}" />
                                                    @csrf
                                                   
                                                    <a class="delete-btn" title="Accept Request" href="javascript:void(0);" data-user-id="{{ $contact->user_id }}"><i class='bx bx-check-circle fs-2' style="color: #0a700a;"></i></i></a>
                                                    <!--<button type="button" class="btn btn-danger delete-btn" data-user-id="{{ $contact->user_id }}">Delete <i class="fas fa-trash-alt"></i></button>-->
                                                </form>
                                                
                                                <form id="acceptForm{{ $contact->user_id }}" action="{{ route('rejectDeleteAccountRequest') }}" method="POST">
                                                    <input type="hidden" name="ids[]" value="{{ $contact->user_id }}" />
                                                    @csrf
                                                   
                                                    <a class="reject-btn" title="Reject Request" href="javascript:void(0);" data-user-id="{{ $contact->user_id }}"><i class='bx bxs-x-circle fs-2' style="color: darkred;"></i></a>
                                                    <!--<button type="button" class="btn btn-danger delete-btn" data-user-id="{{ $contact->user_id }}">Delete <i class="fas fa-trash-alt"></i></button>-->
                                                </form>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    
$(document).ready(function() {
    // Initialize DataTable with a unique ID
    let dataTable = $('#unique-data-table').DataTable({
        "paging": true,
        "searching": true,
        "columnDefs": [
            { "orderable": false, "targets": [0,-1] } 
            // { "orderable": false, "targets": -1 } 
        ]
        // "ordering": true, 
    });

    // Function to update the header checkbox state
    function updateHeaderCheckbox() {
        const headerCheckbox = document.getElementById('selectMultiple');
        const childCheckboxes = dataTable.rows({ page: 'current' }).nodes().to$().find('.child-checkbox');
        const deleteButton = document.getElementById('deleteUsers');
        const rejectButton = document.getElementById('rejectRequests');

        const totalCheckboxes = childCheckboxes.length;
        const checkedCheckboxes = childCheckboxes.filter(':checked').length;

        if (totalCheckboxes === 0) {
            headerCheckbox.checked = false;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'none';
            rejectButton.style.display = 'none';
        } else if (checkedCheckboxes === totalCheckboxes) {
            headerCheckbox.checked = true;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'inline-block';
            rejectButton.style.display = 'inline-block';
        } else if (checkedCheckboxes > 0) {
            headerCheckbox.indeterminate = true;
            headerCheckbox.checked = false;
            deleteButton.style.display = 'inline-block';
            rejectButton.style.display = 'inline-block';
        } else {
            headerCheckbox.checked = false;
            headerCheckbox.indeterminate = false;
            deleteButton.style.display = 'none';
            rejectButton.style.display = 'none';
        }
    }

    // Handle header checkbox change
    $('#selectMultiple').on('change', function () {
        const isChecked = this.checked;
        dataTable.rows({ page: 'current' }).nodes().to$().find('.child-checkbox').prop('checked', isChecked);
        updateHeaderCheckbox();
    });

    // Handle individual checkbox change
    $('#unique-data-table').on('change', '.child-checkbox', function () {
        updateHeaderCheckbox();
    });

    // SweetAlert delete confirmation
    $('#unique-data-table').on('click', '.delete-btn', function() {
        var userId = $(this).data('user-id');

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
                $('#deleteForm' + userId).submit();
            }
        });
    });
    
    $('#unique-data-table').on('click', '.reject-btn', function() {
        var userId = $(this).data('user-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unapprove request!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#acceptForm' + userId).submit();
            }
        });
    });

    // Initial call to update header checkbox state
    updateHeaderCheckbox();
});


function deleteSelectedUsers() {
  const selectedUserIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
    .map(checkbox => checkbox.value);

  if (selectedUserIds.length > 0) {
    console.log('Selected User IDs for deletion:', selectedUserIds);
    approveRequest(selectedUserIds);
    // Here, handle the deletion logic
  } else {
    alert('No users selected!');
  }
}

function rejectSelectedRequests() {
  const selectedUserIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
    .map(checkbox => checkbox.value);

  if (selectedUserIds.length > 0) {
    console.log('Selected User IDs for deletion:', selectedUserIds);
    unApproveRequest(selectedUserIds);
    // Here, handle the deletion logic
  } else {
Swal.fire({
        icon: 'error',
        title: 'No Selection',
        text: 'Please select at least one data to delete.',
        timer: 2000,
        showConfirmButton: false
    });
  }
}



function approveRequest(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (id !== '') {
            
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
                    console.log('hekljlkjlkjlkjlk',result.isConfirmed);
                    $.ajax({
                        url: "{{ route('softDeleteUser') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                            // _method: 'delete'
                        },
                        success: function(response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "User Deleted Successfully.";
                             setTimeout(function() {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                        console.log("hello this is error",jqXHR, textStatus, errorThrown);
                            // location.reload();
                        }
                    });
                }
            });
        }
    }
    
function unApproveRequest(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (id !== '') {
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, unapprove request!'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('hekljlkjlkjlkjlk',result.isConfirmed);
                    $.ajax({
                        url: "{{ route('rejectDeleteAccountRequest') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            ids: id,
                            // _method: 'delete'
                        },
                        success: function(response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "User Deleted Successfully.";
                             setTimeout(function() {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                        console.log("hello this is error",jqXHR, textStatus, errorThrown);
                            // location.reload();
                        }
                    });
                }
            });
        }
    }

</script>

@endsection
