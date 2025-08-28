@extends('layouts.admin-master', ['title' => 'Ads Price'])

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
  
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!--<h5 class="card-header d-flex align-items-center justify-content-between">View Ads Price</h5>-->

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
                                @foreach ($price as $key => $data)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$data->day}} Month</td>
                                    <td> ${{ number_format($data->price, 2) ?? 'N/A'}}</td>
                                    <td>
                                        <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('edit-ads-price', $data->id) }}">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                    <!--<a class="dropdown-item" href="javascript:void(0);" onclick="deleteTag('{{ $data->id }}')">-->
                                                    <!--    <i class="bx bx-trash me-1"></i> Delete-->
                                                    <!--</a>-->
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