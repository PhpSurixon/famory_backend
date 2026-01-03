@extends('layouts.admin-master', ['title' => 'RIP Reports'])

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
        }.btn.btn-danger{
    background: #ff000026;
    border: 1px solid #ffffff;
    color: #ff0000;
    max-width: 109px;
    box-shadow: none;
    }
    </style>
  <div class="container-xxl flex-grow-1 container-p-y">
       <!--<button id="delete-selected" class="btn btn-danger mb-3">Delete Selected</button>-->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!--<h5 class="card-header d-flex align-items-center justify-content-between">RIP Reports</h5>-->
                <div id="deletebtn" style="display:none;">
                    <button id="delete-selected" class="btn btn-danger mt-3 mx-3 d-flex align-items-center gap-2 justify-content-center"><i class='bx bx-trash'></i> Delete</button>
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
                <input class="form-check-input border-dark" type="checkbox" id="select-all" />
                <label class="form-check-label" for="select-all"></label>
            </th>
            <th>S.No.</th>
            <th>Deceased Name</th>
            <th>Deceased Email</th>
            <th>Name of Abuser</th>
            <th>Email of Abuser</th>
            <th>How Often</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody class="table-border-bottom-0">
        @foreach ($datas as $key => $data)
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input border-dark contact-checkbox" value="{{ $key }}" id="options{{ $key }}">
                </td>
                <td>{{ $key + 1 }}</td>
                <td>
                    @if($data['user'])
                        <a href="{{ route('viewUserDeatils', $data['user']->id) }}">
                            {{ $data['user']->first_name . ' ' . $data['user']->last_name }}
                        </a>
                        <input type="hidden" class="user-id" value="{{ $data['user']->id }}">
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $data['user'] ? $data['user']->email : 'N/A' }}</td>
                <td>
                    @if($data['deceased_by'])
                        <a href="{{ route('viewUserDeatils', $data['deceased_by']->id) }}">
                            {{ $data['deceased_by']->first_name . ' ' . $data['deceased_by']->last_name }}
                        </a>
                        <input type="hidden" class="deceased-by-id" value="{{ $data['deceased_by']->id }}">
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $data['deceased_by'] ? $data['deceased_by']->email : 'N/A' }}</td>
                <td>{{ $data['count'] ?? '-' }}</td>
                <td>
                    @if($data['user'] && $data['deceased_by'])
                        <a class="dropdown-item" href="javascript:void(0);" onclick="deleteRecord('{{ $data['user']->id }}', '{{ $data['deceased_by']->id }}')">
                            <i class="bx bx-trash me-1" style="color:red;"></i>
                        </a>
                    @else
                       <div style="padding: 0.532rem 1.50rem;">-</div> 
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
</div>
  <script type="text/javascript">
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
   
   
$(document).ready(function() {
    
    $("#deletebtn").css('display','none');
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Initialize DataTable if not already initialized
    var table = $('#data-table').DataTable();
    
    if (table) {
        table.destroy();
    }
    
    $('#data-table').DataTable({
        "columnDefs": [
            { "orderable": false, "targets": [0,-1] } 
            // { "orderable": false, "targets": -1 } 
        ]
    });

    $('#select-all').on('click', function() {
        $('.contact-checkbox').prop('checked', this.checked);
        if ($('.contact-checkbox:checked').length > 0) {
            $('#deletebtn').show();
        } else {
            $('#deletebtn').hide();
        }
    });
    
     $('.contact-checkbox').on('change', function() {
        if ($('.contact-checkbox:checked').length > 0) {
            $('#deletebtn').show();
        } else {
            $('#deletebtn').hide();
        }
    });

    $('#delete-selected').on('click', function() {
        var deleteData = [];
        $('.contact-checkbox:checked').each(function() {
            
            var row = $(this).closest('tr');
            var userId = row.find('.user-id').val(); // Add a hidden field in your table rows
            var deceasedById = row.find('.deceased-by-id').val(); // Add a hidden field in your table rows
            if (userId && deceasedById) {
                deleteData.push({ user_id: userId, deceased_by_id: deceasedById });
            }
        });

        if (deleteData.length > 0) {
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
                        url: "{{ route('deceasedReportDelete') }}",
                        type: "POST",
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: { deleteData: deleteData },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Reports have been deleted.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.',
                                timer: 3000,
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
                title: 'No Selection',
                text: 'Please select at least one report to delete.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});

    function deleteRecord(userId, deceasedById) {
        console.log("Ids==>",userId,deceasedById);
        if (userId !== '' && deceasedById !== '') {
            
            let deleteData = [
                { user_id: userId, deceased_by_id: deceasedById }
            ];
            
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
                        url: "{{route('deceasedReportDelete')}}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            deleteData : deleteData
                        },
                        success: function(response) {
                            // Show success message with SweetAlert
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Report deleted successfully.',
                                confirmButtonText: 'OK' // Add an OK button
                                }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); // Reload the page when OK is clicked
                                }
                            });
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // Show error message with SweetAlert
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again.',
                                timer: 3000, // Auto-close after 2 seconds
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        }
    }
    
    
</script>

@endsection
