@extends('layouts.admin-master', ['title' => 'Users'])
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
	<div class="row">
		<div class="col-md-12">
			<div class="card">

				<div id="card-loader1" class="loader-container">
					<div class="spinner-border" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
				<h5 class="card-header d-flex align-items-center justify-content-end"> 
					<a href="{{ route('admin_user_create') }}" class="au-btn--green user-btn m-b-9">Add Admin User</a>
			    </h5>

			<div class="table-responsive text-nowrap">

				<div class="card-datatable table-responsive pb-0">
					<table class="table border-top" >
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Name</th>
								<th>Email</th>
								<th>Mobile</th>
								<th>Role</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							@if(count($admin_users)>0)
							@foreach($admin_users as $key =>$user)
							<tr>
								<td>{{ $key+1 }}</td>
								<td>{{ $user->full_name??'' }}</td>
								<td>{{ $user->email??""}}</td>
								<td>{{ $user->phone??""}}</td>
								<td>{{ $user->role ? $user->role->name : ' ' }}</td>
								<td>
									<div class="dropdown">
										<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="bx bx-dots-vertical-rounded"></i>
										</button>
										<div class="dropdown-menu" style="">
											
											<a class="dropdown-item" href="{{ route('admin_user_edit', [$user->id]) }}">
											<i class="bx bx-edit-alt me-1"></i> Edit
											</a>

											<a class="dropdown-item" href="javascript:void(0);" onclick="deleteUser('{{ $user->id }}')">
											<i class="bx bx-trash me-1"></i> Delete
											</a>
										</div>
									</div>
								</td>
							</tr>
							@endforeach
							@else
							 <tr class="text-center">
							 	 <td colspan="6">No Record Found</td>
							 </tr>
							@endif
						</tbody>
					</table>
					<div class="d-flex justify-content-between mx-3 mt-4 ">
						<div class="pg-one">
							<span id="entries-info">
								Showing {{$admin_users->firstItem()}} to {{$admin_users->lastItem()}} of {{$admin_users->total()}} entries
							</span>
						</div>
						<div>
							<div id="pagination-container">
								{!! $admin_users->appends(request()->input())->links('custom') !!}
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>
</div>
<script>
$(window).on('load', function() {
$('#card-loader1').fadeOut('fast');
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
                    console.log('hekljlkjlkjlkjlk',result.isConfirmed);
                    $.ajax({
                        url: "{{ route('admin_user_delete') }}",
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
                        console.log("hello this is error",jqXHR, textStatus, errorThrown);
                            // location.reload();
                        }
                    });
                }
            });
        }
    }
</script>
@endsection

