@extends('layouts.admin-master', ['title' => 'Featured Partner Price'])

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
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header d-flex align-items-center justify-content-end"><a href="{{ route('create-featured-company-price') }}"
                    class="au-btn--green user-btn m-b-9">Add</a></h5>

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
                                    <th>S.No.</th>
                                    <th>Month</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($datas as $key => $data)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$data->month}} Month</td>
                                    <td>${{$data->price}}</td>
                                    <td>
                                        <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('edit-featured-company-price', $data->id) }}">
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
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
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
                        url: "{{ route('destroy-featured-company-price', '') }}/" + id,
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