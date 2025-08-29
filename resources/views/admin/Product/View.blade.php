@extends('layouts.admin-master', ['title' => 'Famory Tags'])

@section('content')
 <style>
    .hidden {
        display:none;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    
   .dataTables_scroll .dataTables_scrollHeadInner{
       width: 100% !important;
   }
  
    .table-d table tbody tr td {
        vertical-align: top;
    }
    
    div#data-table_wrapper .row:nth-child(2)::-webkit-scrollbar {
            width: 0.5em!important;
        }
   
    div#data-table_wrapper .row:nth-child(2)::-webkit-scrollbar-track {
      box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3)!important;
    }
     
    div#data-table_wrapper .row:nth-child(2)::-webkit-scrollbar-thumb {
      background-color: #1550AE!important;
    }
    
    table .table-light tr th {
        text-align:center;
        white-space:nowrap;
    }
    
    table tbody tr td {
        text-align: justify;
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
    }.btn.btn-danger{
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
    
    div#data-table_wrapper .row:nth-child(2) {
    overflow-x: auto;
    margin-bottom: 1rem;
    }
    .table-d table tbody tr td {
    vertical-align: top;
    white-space: nowrap;
    }
    #td-scroll {
      white-space: break-spaces;
    height: 70px;
    width: 400px;
    overflow-y: scroll;
    scrollbar-width: thin;
}

</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!--<button id="delete-selected" class="btn btn-danger mb-3">Delete Selected</button>-->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-end"><a href="{{ route('create-product') }}"
                    class="au-btn--green user-btn m-b-9">Add</a></h5>
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
                </div>
                    <div class="card-datatable table-responsive table-d">
                        <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <!--<th><input type="checkbox" id="select-all"></th>-->
                                    <th>
                                        <input class="form-check-input border-dark" type="checkbox" id="selectMultiple" />
                                        <label class="form-check-label" for="selectMultiple"></label>
                                    </th>
                                    <th>S.No.</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Reseller Price</th>
                                    <th>Retail Price</th>
                                    <th>Total Quantity</th>
                                    <th>Total Purchased</th>
                                    <th>Description</th>
                                    <th>Tag Purpose</th>
                                    <th>Color</th>
                                    <th>Priority</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($datas as $key => $data)
                                    <tr>
                                        <!--<td><input type="checkbox" class="contact-checkbox" value="{{ $data->id }}"></td>-->
                                        <td>
                                            <input name="userIds" value="{{ $data->id }}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options{{ $data->id }}" />
                                            <label class="form-check-label" for="options{{ $data->id }}"></label>
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if($data->image)
                                                <img src="{{ $data->image }}" alt="User Image" class="img-circle">
                                            @else
                                                <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle">
                                            @endif
                                        </td>
                                        <td>{{ $data->name }}</td>
                                        <td>${{ $data->reseller_price ? number_format($data->reseller_price, 2) : 'N/A' }}</td>
                                        <td>${{ $data->price ? number_format($data->price, 2) : 'N/A'}}</td>
                                        <td>{{ $data->count ?? '-'}}</td>
                                        <td>{{ $data->total_purchased ?? '-'}}</td>
                                        <td>
                                            <div id="td-scroll">{{ $data->description ?? '-'}}</div>
                                        </td>
                                        <td>
                                            <div id="td-scroll">{{ $data->tag_purpose ?? '-'}}</div>
                                        </td>
                                        <td>{{ ucfirst($data->color) ?? '-'}}</td>
                                        <td>{{ $data->is_favourite == '1' ? 'Yes' : 'No' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('edit-product',$data->id) }}">
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
</div>
  <script type="text/javascript">
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
        $(window).on('load', function() {
            $('#card-loader').fadeOut('slow');
        });
        
    $(document).ready(function() {
        $('#data-table').DataTable();
        
        // $('.contact-checkbox').on('change', function() {
        //     if ($('.contact-checkbox:checked').length > 0) {
        //         $('#deletebtn').show();
        //     } else {
        //         $('#deletebtn').hide();
        //     }
        // });
        
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
        
        
        
        
        
        if ($("#flashSuccessMessage").length) {
            setTimeout(function() {
                $("#flashSuccessMessage").fadeOut("slow", function() {
                    $(this).remove();
                });
            }, 2000); // 2 seconds
        }
        
        if ($("#successMessage").text().trim()) {
            $("#successMessage").removeClass("hidden");
            setTimeout(function() {
                $("#successMessage").addClass("hidden");
            }, 3000); 
        }
        
        
        $('#select-all').on('click', function() {
            $('.contact-checkbox').prop('checked', this.checked);
            if ($('.contact-checkbox:checked').length > 0) {
                $('#deletebtn').show();
            } else {
                $('#deletebtn').hide();
            }
        });

        $('#delete-selected').on('click', function() {
            // var selectedIds = [];
            // $('.contact-checkbox:checked').each(function() {
            //     selectedIds.push($(this).val());
            // });
            
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
                            url: '{{ route("famoryTagDeleteSelected") }}',
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
                                        timer: 3000,
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
                    title: 'Oops...',
                    text: 'Please select at least one tag to delete.',
                    // timer: 2000,
                    showConfirmButton: false
                });
            }
        });
        });
    
        function deleteData(id) {
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('destroy-product', '') }}/" + id,
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success: function(response) {
                            location.reload();
                            var element = document.getElementById("successMessage");
                            element.classList.remove("hidden");
                            element.textContent = "Famory tag deleted Successfully.";
                            setTimeout(function() {
                                element.classList.add("hidden");
                                // location.reload(); // Refresh the page
                            }, 3000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            // location.reload();
                        }
                    });
                }
            });
        }
    }
    
</script>

@endsection