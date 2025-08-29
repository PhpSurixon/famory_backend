@extends('layouts.admin-master', ['title' => 'F.A.Q'])
<style>
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
    
    .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: hidden !important;
    }
    
    .card-header {
        padding-bottom:8px!important;
    }
</style>
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center justify-content-end">
                    <a href="{{ route('view-fqa') }}" class="au-btn--green user-btn m-b-9"> Add </a>
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
                        
                        
                        <div class="card-datatable table-responsive">
                         <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Question</th>
                                    <th>Answer</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                              <tbody class="table-border-bottom-0">
                                @foreach($getFQAs as $key=>$fqa)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{($fqa->question) ?? 'N/A'}}</td>
                                        <td>{!!($fqa->answer) ?? 'N/A'!!}</td>
                                        <td>
                                            <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('editFQA', $fqa->id) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item" href="javascript:void(0);" onclick="deleteTag('{{$fqa->id }}')">
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
        
        // Check if there is text inside the span of the successMessage div
        if ($("#successMessage span").text().trim()) {
            $("#successMessage").removeClass("hidden");
            
            // Hide the success message after 3 seconds
            setTimeout(function() {
                $("#successMessage").addClass("hidden");
            }, 3000); 
        }
    });
    
    function deleteTag(id) {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (id !== '') {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover!",
                    icon: "warning",
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Okay',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('destroyFqa', '') }}/" + id,
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                console.log("hello");
                                location.reload()
                                // var element = document.getElementById("successMessage");
                                // element.classList.remove("hidden");
                                // element.textContent = "Famory Tag removed Successfully.";
                                // setTimeout(function() {
                                //     element.classList.add("hidden");
                                //     location.reload(); // Refresh the page
                                // }, 2000);
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