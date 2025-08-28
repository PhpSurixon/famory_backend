@extends('layouts.admin-master', ['title' => 'Tags'])

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
    .img-fluid {
        max-width: 100%;
        height: 431px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!-- Assuming you have a variable $userName passed to the view -->
                <h5 class="card-header d-flex align-items-center justify-content-between">User Name: {{ $user->first_name }} {{$user->last_name}}<a href="{{ route('famory-tags') }}" class="btn btn-primary m-b-9">Back</a></h5>
                <div class="table-responsive text-nowrap">
                    @if (session('success'))
                        <div class="alert alert-success">
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
                                    <th>Famory Tags</th>
                                    <th>Register Date</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($tags as $key => $tag)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>  
                                        @if($tag->image)
                                            <img src="{{ $tag->image }}" alt="User Image" class="img-circle preview-image">
                                        @else
                                            <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle preview-image">
                                        @endif
                                    </td>
                                    <td>
                                        {{ $tag->family_tag_id }}
                                    </td>
                                    <td>{{\Carbon\Carbon::parse($tag->created_at)->format('m/d/y') ?? 'N/A' }}</td>
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
        <!-- Corrected 'height' spelling -->
        <img id="modalImage" src="" alt="Modal Image" class="img-fluid" height="500px" width="500px">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#data-table').DataTable();
        $('.preview-image').on('click', function() {
            var imageUrl = $(this).attr('src');
            $('#modalImage').attr('src', imageUrl);
            $('#staticBackdrop').modal('show'); 
        });
    });
</script>

@endsection
