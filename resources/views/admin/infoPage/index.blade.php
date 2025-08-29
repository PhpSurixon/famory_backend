@extends('layouts.admin-master', ['title' => 'App Info Settings'])
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
        padding-bottom:8px !important;
    }
</style>
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center justify-content-end">
                    <a href="{{ route('info-pages.create') }}" class="au-btn--green user-btn m-b-9">Add
                            New Page</a>
                    </h5>
                    @if($errors->has('error'))
                        <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                    @endif

                    <div class="card-datatable table-responsive">
                        <table class="datatables-basic table border-top" id="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Page Name</th>
                                    <th>Page's Slug</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                              <tbody class="table-border-bottom-0">

                                @foreach ($infoPages as $key => $page)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $page->page_name }}</td>
                                        <td>{{ $page->page_url }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item"
                                                        href="{{ route('info-pages.show', $page->page_url) }}"><i
                                                            class="bx bx-show-alt me-1"></i>View</a>
                                                    <a class="dropdown-item"
                                                        href="{{ route('info-pages.edit', $page->id) }}"><i
                                                            class="bx bx-edit-alt me-1"></i> Edit</a>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        onclick="deletePage('{{ $page->id }}')"><i
                                                            class="bx bx-trash me-1"></i> Delete</a>
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
        function deletePage(id) {
            console.log(id);
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
                            url: "{{ route('info-pages.destroy','') }}/" + id,
                            type: "post",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            data: {
                               _method: 'delete'
                            },
                            success: function(response) {
                                location.reload();
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
