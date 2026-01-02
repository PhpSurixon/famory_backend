@extends('layouts.admin-master', ['title' => 'Reported Users'])
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
				

			<div class="table-responsive text-nowrap">

				<div class="card-datatable table-responsive pb-0">
					<table class="table border-top" >
						<thead>
							<tr>
								<th>S.No.</th>
								<th>Post Title</th>
								<th>Reported User</th>
								<th>Reported Email</th>
								<th>Reported Mobile</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							@if(count($reported_posts)>0)
							@foreach($reported_posts as $key =>$reported_post)
							  @php
							     $user = $reported_post->reporter??null;
							     $post = $reported_post->reportedPost??null;
							  @endphp
							<tr>
								<td>{{ $key+1 }}</td>
								<td>{{ $post->title ? $post->title: $post->post_type }}</td>
								<td>{{ $user->full_name??'' }}</td>
								<td>{{ $user->email??"N/A"}}</td>
								<td>{{ $user->phone??"N/A"}}</td>
								<td>
									<div class="dropdown">
										<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="bx bx-dots-vertical-rounded"></i>
										</button>
										<div class="dropdown-menu" style="">
											@if($user)
											<button class="dropdown-item deletPostBtn" data-id="{{ $post->id  }}">
											<i class="bx bx-trash me-1"></i>Delete Post
											</button>
											@endif
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
								Showing {{$reported_posts->firstItem()}} to {{$reported_posts->lastItem()}} of {{$reported_posts->total()}} entries
							</span>
						</div>
						<div>
							<div id="pagination-container">
								{!! $reported_posts->appends(request()->input())->links('custom') !!}
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


$('.deletPostBtn').on('click', function() {
	var postId = $(this).data('id');



// Make AJAX request
	if (postId != null) {
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Yes, approve request!'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: '{{route("delete_post")}}',
					type: 'POST',
					data: {
						id: postId,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success!',
								text: response.message,
								confirmButtonText: 'OK'
							}).then((result) => {
								if (result.isConfirmed) {
									window.location.href = '/reported-post/list'; 
								}
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Oops...',
								text: 'Something went wrong. Please try again.',
								timer: 2000,
								showConfirmButton: false
							}).then(() => {
								location.reload();
							});
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: 'Something went wrong. Please try again.',
							timer: 2000, 
							showConfirmButton: false
						}).then(() => {
							location.reload();
						});
					}
				});
			}
		});
	} else {
		Swal.fire({
			icon: 'error',
			title: 'Oops...',
			text: 'Please select at least one contact to delete.',
			timer: 2000,
			showConfirmButton: false
		});
	}
});
</script>
@endsection

