@extends('layouts.admin-master', ['title' => 'Trusted Partners','previous'=> '/advertiser/dashboard'])

@section('content')
<style>
    .hidden {
        display: none;
    }

    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }

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
    
    .card-header {
        padding-bottom:8px;
    }
    
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <!--<button id="delete-selected" class="btn btn-danger mb-3">Delete Selected</button>-->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-end"> <a
                        href="{{ route('create-trusted-company') }}" class="au-btn--green user-btn m-b-9">Add</a></h5>
                        <div id="deletebtn" style="display:none;">
                        <button id="delete-selected" class="btn btn-danger mx-3 d-flex align-items-center gap-2 justify-content-center"><i class='bx bx-trash'></i> Delete</button>
                        </div>
                <div class="table-responsive text-nowrap">
                    @if (session('success'))
                    <div class="alert alert-success" id="flashSuccessMessage">
                        {{ session('success') }}
                    </div>
                    @endif
                    <div class="alert alert-success hidden" id="successMessage">
                        <span></span>
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
                                    <th>Logo</th>
                                    <th>Company Name</th>
                                    <th>Status</th>
                                    <th>Phone</th>
                                    <th>Website</th>
                                    <th>Address</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @if($getTrustedPartners->count() > 0 )
                                @foreach ($getTrustedPartners as $key=>$data)
                                <tr>
                                    <td>
                                        <input name="userIds" value="{{ $data->id }}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options{{ $data->id }}" />
                                        <label class="form-check-label" for="options{{ $data->id }}"></label>
                                    </td>
                                    <td>{{ $key+1}}</td>
                                    <td>
                                        @if($data->logo)
                                        <img src="{{$data->logo}}" alt="Logo" class="img-circle preview-image">
                                        @else
                                        <img src="/assets/img/famcam.jpg" alt="Default Image"
                                            class="img-circle preview-image">
                                        @endif
                                    </td>
                                    <td>{{$data->company_name}}</td>
                                    @if($data->featured_partner == '1')
                                    <td style="color:green;">Feature</td>
                                    @else
                                    <td>-</td>
                                    @endif
                                    <td>{{$data->phone}}</td>
                                    <td><a href="{{ $data->website }}" target="_blank"><span
                                                style="color:#20c6d9;">Link</span></a></td>
                                    <td>{{ $data->city." , ".$data->state." - ".$data->zip_code }}</td>
                                    <td>{{\Carbon\Carbon::parse($data->created_at)->format('m/d/y') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if($data->featured_partner == 1)
                                                <a class="dropdown-item" href="javascript:void(0);"
                                                    onclick="cancelSubscription('{{ $data->id }}')">
                                                    <i class="bx bx-x me-1"></i> Cancel Subscription
                                                </a>
                                                @endif
                                                <a class="dropdown-item"
                                                    href="{{ route('edit-trusted-company',$data->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);"
                                                    onclick="deleteData('{{ $data->id }}')">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="9" style="text-align:center;">No Record Found</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Image</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Modal Image" class="img-fluid" hieght="500px" width="500px">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function () {
        $('#data-table').DataTable();
        
    
        if ($("#flashSuccessMessage").length) {
            setTimeout(function () {
                $("#flashSuccessMessage").fadeOut("slow", function () {
                    $(this).remove();
                });
            }, 2000); // 2 seconds
        }

        if ($("#successMessage").text().trim()) {
            $("#successMessage").removeClass("hidden");
            setTimeout(function () {
                $("#successMessage").addClass("hidden");
            }, 3000);
        }

         $(document).on('click', '.preview-image', function() {
            var imageUrl = $(this).attr('src');
            $('#modalImage').attr('src', imageUrl);
            $('#staticBackdrop').modal('show'); 
        });
        
      
        // Initialize DataTable if not already initialized
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

        

        $('#delete-selected').on('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.child-checkbox:checked'))
                .map(checkbox => checkbox.value);

            console.log('selectedIds', selectedIds);
            if (selectedIds.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('destroy-trusted-company') }}",
                            type: "post",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            data: {
                                id: selectedIds,
                            },
                            success: function (response) {
                                location.reload();
                                var element = document.getElementById("successMessage");
                                element.classList.remove("hidden");
                                element.textContent = "User deleted Successfully.";
                                setTimeout(function () {
                                    element.classList.add("hidden");
                                    location.reload(); // Refresh the page
                                }, 2000);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                location.reload();
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No Selection',
                    text: 'Please select at least one data to delete.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

    });

    function deleteData(id) {
        if (id !== '') {
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('destroy-trusted-company') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: [id],
                        },
                        success: function (response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "User deleted Successfully.";
                            setTimeout(function () {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            location.reload();
                        }
                    });
                }
            });
        }
    }

    function cancelSubscription(id) {
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "You Want to Cancel the Subscription",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('cancel-subscription', '') }}/" + id,
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success: function (response) {
                            // location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = response.message;
                            setTimeout(function () {
                                element.classList.add("hidden");
                                location.reload(); // Refresh the page
                            }, 2000);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            location.reload();
                        }
                    });
                }
            });
        }
    }


</script>

@endsection