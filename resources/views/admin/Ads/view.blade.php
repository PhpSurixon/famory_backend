@extends('layouts.admin-master', ['title' => 'Ads '])

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
    
     .dataTables_scroll .dataTables_scrollBody:last-child {
    overflow: auto !important;
    padding-bottom: 50px;
    height: calc(100vh - 350px);
    }
    
    .dataTables_scroll {
        overflow: hidden !important;
    }
    
</style>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!--<h5 class="card-header d-flex align-items-center justify-content-between">View Trusted Company <a href="{{ route('create-trusted-company') }}"-->
                <!--    class="au-btn--green m-b-9">Add</a></h5>-->
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
                                    <th>Banner</th>
                                    <th>Ad name</th>
                                    <th>Start Date</th>
                                    <th>Renew Date</th>
                                    <th>Zip Code</th>
                                    <th>Payment Status</th>
                                    <th>Created Date</th>
                                    <th>National Ads</th>
                                    <th>Expiry Date free Ads</th>
                                    <th>Free Ads</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                
                                @foreach ($ads as $key=>$data)
                                    <tr>
                                        <td>{{ $key+1}}</td>
                                        <td>
                                            @if($data->banner_image)
                                                <img src="{{ $data->banner_image }}" alt="Logo" class="img-circle preview-image">
                                            @else
                                                    <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle preview-image">
                                            @endif
                                        </td>
                                        <td>{{ $data->ad_name }}</td>
                                        <td class="text-center">{{ $data->start_date ? \Carbon\Carbon::parse($data->start_date)->format('m/d/Y') : '-'}}</td>
                                        <td class="text-center">{{ $data->renew_date ?\Carbon\Carbon::parse($data->renew_date)->format('m/d/Y') : '-'}}</td>
                                        <td>{{ $data->zip_code }}</td>
                                        <td>{!! $data->payment_status == '1' ? '<span class="text-success">Completed</span>':'<span class="text-danger">Pending</span>' !!}</td>
                                        <td class="text-center">{{\Carbon\Carbon::parse($data->created_at)->format('m/d/Y') ?? '-' }}</td>
                                        <td>
                                        @if($data->payment_status == '1' || $data->free_expiration_date !== null)
                                            <input type="checkbox" {{ $data->is_national == '1' ? 'checked': '' }}  onclick="updateAdStatus('{{ $data->id }}',this)">
                                        @else
                                            <span class="text-danger">Pending</span>
                                        @endif
                                        </td>
                                        <td class="text-center">{{ $data->free_expiration_date ? \Carbon\Carbon::parse($data->free_expiration_date)->format('m/d/Y') : '-'  }}</td>
                                        <td>
                                            @if($data->payment_status !== '1')
                                                @if($data->free_expiration_date === null)
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        data-user-id="{{ $data->id }}" 
                                                        data-toggle="modal"
                                                        data-target="#subscribeModel"
                                                        onclick="opensubscribeModel({{ $data->id }})">
                                                        <i class="bx bx-crown me-1"></i>Free Ad
                                                    </a>
                                                @else
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        data-user-id="{{ $data->id }}" 
                                                        data-toggle="modal"
                                                        data-target="#subscribeModel"
                                                        onclick="cancelsubscribeModel({{ $data->id }})">
                                                        <i class="bx bx-x me-1"></i>Cancel
                                                    </a>
                                                @endif
                                            @else
                                               <div class="text-center">-</div> 
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

<div id="subscribeModel" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="subscribeForm">
                @csrf
                <div class="modal-header">
                     <h5 class="modal-title" id="subscribeModelLabel">Free Ads</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noOfDays" class="form-label">Please set an expiration date for the free Ads</label>
                        <input type="hidden" name="ads_id">
                        <input type="date" class="form-control px-2" id="noOfDays" name="noOfDays">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-info border-radius-section color-white">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>





  <script type="text/javascript">
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function() {
        $('#data-table').DataTable();
        if ($("#successMessage").text().trim()) {
            $("#successMessage").removeClass("hidden");
            setTimeout(function() {
                $("#successMessage").addClass("hidden");
            }, 3000); 
        }
        
        var today = new Date().toISOString().split('T')[0];
        $('#noOfDays').attr('min', today);
    });
    
    function opensubscribeModel(id){
        $('input[name="ads_id"]').val(id);
        $("#subscribeModel").modal('show');
    }
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('#subscribeForm').on('submit', function(e) {
        e.preventDefault();
        var hasError = false;
    
        // Clear any existing error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.error-message').remove();
    
        const noOfDays = $('input[name="noOfDays"]').val();
        const id = $('input[name="ads_id"]').val();
    
        // Validate No of Days
        if (!noOfDays) {
            $('input[name="noOfDays"]').addClass('is-invalid');
            $('<span class="error-message" style="color: red;font-weight: 400;">Please enter No of Days.</span>')
                .insertAfter($('input[name="noOfDays"]'));
            hasError = true;
        }
    
        // Validate ads_id
        if (!id) {
            $('input[name="ads_id"]').addClass('is-invalid');
            $('<span class="error-message" style="color: red;font-weight: 400;">Please refresh and try again.</span>')
                .insertAfter($('input[name="ads_id"]'));
            hasError = true;
        }
    
        // If no errors, proceed with AJAX submission
        if (!hasError) {
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('free-ads', '') }}/" + id,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.invalid-feedback').remove();
                    $('input, select').removeClass('is-invalid');
                    $("#subscribeModel").modal('hide');
                    const element = document.getElementById('successMessage');
                    element.classList.remove("hidden");
                    element.textContent = response.message;
                    setTimeout(function() {
                        element.classList.add('hidden');
                        location.reload();
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];
    
                        for (let field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errors[field].forEach(message => {
                                    errorMessages.push(message);
                                });
                            }
                        }
    
                        // Show validation errors sequentially
                        const showErrorsSequentially = (index = 0) => {
                            if (index < errorMessages.length) {
                                swal({
                                    text: "Validation Error",
                                    title: errorMessages[index],
                                    icon: "error",
                                    button: "Ok",
                                }).then(() => {
                                    showErrorsSequentially(index + 1);
                                });
                            }
                        };
                        showErrorsSequentially();
                    }else{
                        $('.invalid-feedback').remove();
                        $('input, select').removeClass('is-invalid');
                        $("#subscribeModel").modal('hide');
                        
                        const element = document.getElementById('successMessage');
                        element.classList.remove("alert-success");
                        element.classList.add("alert-danger");
                        element.classList.remove("hidden");
                        element.textContent = "Please try again Data was not saved successfully";
                        setTimeout(function() {
                        element.classList.add('hidden');
                    }, 4000);
                    }
                    
                }
            });
        }
    });
    
    
    function cancelsubscribeModel(id){
        console.log("id==>",id);
        if (id !== '') {
            swal.fire({
                title: "Are you sure?",
                text: "You want to cancel the ads for free.",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('cancel-free-ads', '') }}/" + id,
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
                            element.textContent = response.message;
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
    };


    
    
    
    function updateAdStatus(id,ele){
        let message = '';
        if(ele.checked){
            message = 'You Want Make This Ad a National Ad';
        }else{
            message = 'You Want to Remove This Ad from National Ad';
        }
        if(id !== ''){
            // console.log(id);
            swal.fire({
                title: "Are you sure?",
                text: message,
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Okay',
                dangerMode: true,
                reverseButtons: true,
            }).then((result) => {
                if(result.isConfirmed){
                    $.ajax({
                        url: "{{ route('update-ad-status', '') }}/" + id,
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            id: id,
                        },
                        success:function(response){
                            console.log(response)
                            const element = document.getElementById('successMessage');
                            element.classList.remove("hidden");
                            element.textContent = response.message;
                            setTimeout(function(){
                                element.classList.add('hidden');
                                location.reload();
                            },2000);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            location.reload();
                        }
                    });
                }else{
                    if(ele.checked){
                        ele.checked = false;
                    }else{
                        ele.checked = true;
                    }
                }
            });
        }
    }
</script>

@endsection