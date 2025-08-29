@extends('layouts.admin-master', ['title' => 'Tags'])

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
    .img-fluid {
        max-width: 100%;
        height: 431px;
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
    
    
.dataTables_scroll {
  overflow-y: hidden; !important;
}

.dataTables_wrapper .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: scroll !important;
    padding-bottom: 50px;
    height: calc(100vh - 350px);
}
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <!--<button id="delete-selected" class="btn btn-danger mb-3">Delete Selected</button>-->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!--<h5 class="card-header d-flex align-items-center justify-content-between">View Tags</h5>-->
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
                                        <input class="form-check-input border-dark" type="checkbox" id="selectMultiple" />
                                        <label class="form-check-label" for="selectMultiple"></label>
                                    </th>
                                    <th>S.No.</th>
                                    <th>Image</th>
                                    <th>Famory Tags</th>
                                    <!--<th>Created User Name</th>-->
                                    <th>Assign User Name</th>
                                    <th>Register Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($tags as $key => $tag)
                                    <tr>
                                        <td>
                                            <input name="userIds" value="{{ $tag->id }}" class="form-check-input child-checkbox border-dark" type="checkbox" id="options{{ $tag->id }}" />
                                            <label class="form-check-label" for="options{{ $tag->id }}"></label>
                                        </td>
                                        
                                        <td>{{$key+1}}</td>
                                        <td>  
                                            @if($tag->image)
                                                <img src="{{$tag->image}}" alt="User Image" class="img-circle preview-image"/>
                                            @else
                                                <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle preview-image"/>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('get-famory-tag-post', $tag->family_tag_id) }}">
                                                {{ $tag->family_tag_id }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($tag->user)
                                                <a href="{{ route('get-all-tag-user', $tag->user->id) }}">
                                                    {{$tag->user->first_name}} {{$tag->user->last_name}}
                                                </a>
                                            @else
                                                <span>No user assigned</span>
                                            @endif
                                        </td>
                                        
                                        
                                        <td>{{\Carbon\Carbon::parse($tag->created_at)->format('m/d/y') ?? 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('editFamoryTag', $tag->id) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="deleteTag('{{ $tag->id }}')">
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
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">Famory Tag Image</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img class="modalImage" src="" alt="Modal Image" class="img-fluid" hieght="500px" width="500px">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="userTag" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">All Tag Lists</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="datatables-basic table border-top" id="data-table">
                <thead class="table-light">
                    <tr>
                        <th>S.No.</th>
                        <th>Image</th>
                        <th>Famory Tags</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0 addData">
                </tbody>
            </table>
            <div id="bootpag"></div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bootpag/1.0.7/jquery.bootpag.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        // $('#data-table').DataTable();
        
        if ($("#flashSuccessMessage").length) {
            setTimeout(function() {
                $("#flashSuccessMessage").fadeOut("slow", function() {
                    $(this).remove();
                });
            }, 2000); // 2 seconds
        }
        
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
        
        
        
        
        
        if ($("#successMessage").text().trim()) {
            $("#successMessage").removeClass("hidden");
            setTimeout(function() {
                $("#successMessage").addClass("hidden");
            }, 3000); 
        }
        
        $(document).on('click', '.preview-image', function() {
            var imageUrl = $(this).attr('src');
            $('.modalImage').attr('src', imageUrl);
            $('#staticBackdrop').modal('show'); 
        });
        
        // initializeImagePreview();

        // Function to delete a tag
        function deleteTag(id) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (id !== '') {
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
                            url: "{{ route('destroyFamoryTag') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            data: {
                                id: [id],
                            },
                            success: function(response) {
                                var element = document.getElementById("successMessage");
                                element.classList.remove("hidden");
                                element.textContent = "Famory Tag removed Successfully.";
                                setTimeout(function() {
                                    element.classList.add("hidden");
                                    location.reload(); // Refresh the page
                                }, 2000);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                location.reload();
                            }
                        });
                    }
                });
            }
        }
        
        // Ensure the deleteTag function is accessible
        window.deleteTag = deleteTag;  // Make it globally accessible
        
         // Initialize DataTable if not already initialized
        // var table = $('#data-table').DataTable();

        // if (table) {
        //     table.destroy();
        // }

        // $('#data-table').DataTable({
        //     "columnDefs": [
        //         { "orderable": false, "targets": [0, -1] }
        //         // { "orderable": false, "targets": -1 } 
        //     ]
        // });

        // $('#select-all').on('click', function () {
        //     $('.contact-checkbox').prop('checked', this.checked);
            
        //     if ($('.contact-checkbox:checked').length > 0) {
        //         $('#deletebtn').show();
        //     } else {
        //         $('#deletebtn').hide();
        //     }
            
        // });

        $('#delete-selected').on('click', function () {
            console.log("hello");
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
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('destroyFamoryTag') }}",
                            type: "post",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            data: {
                                id: selectedIds,
                            },
                            success: function (response) {
                                // initializeImagePreview();
                                location.reload();
                                var element = document.getElementById("successMessage");
                                element.classList.remove("hidden");
                                element.textContent = "Tag deleted Successfully.";
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
                    text: 'Please select at least one tag to delete.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        
    });

</script>

@endsection