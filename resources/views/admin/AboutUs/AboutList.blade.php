@extends('layouts.admin-master', ['title' => 'About us Page'])
@section('content')
<style>
    table tbody tr td{
            white-space: normal;
            vertical-align: top;
    }
    .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: hidden !important;
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
                    <!--<a href="{{ route('create-tutorial') }}" class="au-btn--green m-b-9"> Add </a>-->
                    </h5>
                    @if($errors->has('error'))
                        <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                    @endif

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
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                              <tbody class="table-border-bottom-0">
                               @foreach($getData as $key=>$data)
                                    <tr>
                                        <td>{{$key +1}}</td>
                                        <td>
                                        @if($data->image)
                                            <img src="{{ $data->image }}" alt="User Image" class="img-circle" style="max-width: 50px; max-height: 50px;">
                                        @else
                                            <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle" style="max-width: 50px; max-height: 50px;">
                                        @endif
                                        </td>
                                        <!--<td><img src="" alt="Tutorial Image" style="max-width: 50px; max-height: 50px;"/></td>-->
                                        <td>{{$data->title}}</td>
                                        <td>{!!$data->details!!}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('edit-about', $data->id) }}">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
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
    

</script>
@endsection