
@extends('layouts.admin-master', ['title' => 'Tutorial Page'])
@section('content')
<style>
        table tbody tr td{
            white-space: normal;
    }
    
    .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: hidden !important;
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center justify-content-end">
                    <a href="{{ route('create-tutorial') }}" class="au-btn--green user-btn m-b-9"> Add </a>
                    </h5>
                    @if($errors->has('error'))
                        <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        @if (session('success'))
                            <div class="alert alert-success" id="flashSuccessMessage">
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="alert alert-success hidden" id="successMessage">
                            <span></span>
                        </div>
                        
                        
                        <div class="card-datatable table-responsive text-nowrap custom">
                         <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No1.</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                              <tbody class="table-border-bottom-0">
                               @foreach($getTutorial as $key=>$data)
                                    <tr>
                                        <td>{{$key +1}}</td>
                                        <td>
                                        @if($data->image)
                                            <img src="{{ $data->image }}" alt="User Image" class="img-circle" style="max-width: 50px; max-height: 50px;"/>
                                        @else
                                            <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle" style="max-width: 50px; max-height: 50px;"/>
                                        @endif
                                        </td>
                                        <td>{{$data->title}}</td>
                                        <td>{!! $data->details !!}</td>
                                        <td>
                                            <div class="dropdown">
                                                @if(is_null($data->deleted_at))
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('edit-Tutorial', $data->id) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="deleteUser('{{ $data->id }}')">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </a>
                                                    </div>
                                                @else
                                                    <span style="color: red;">Deleted</span>
                                                @endif
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
    
    
    
    <script>
    $(document).ready(function() {
        $('#data-table').DataTable();
        
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
    });
    
        function deleteUser(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
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
                        url: "{{ route('destroy-tutorial', '') }}/" + id,
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
                            element.textContent = "User deleted Successfully.";
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
    
    
    </script>

    
@endsection