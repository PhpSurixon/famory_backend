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
								<th>Reported User</th>
								<th>Email</th>
								<th>Mobile</th>
								<th>Reported By User</th>
								<!-- <th>Action</th> -->
							</tr>
						</thead>
						<tbody>
							@if(count($reported_users)>0)
							@foreach($reported_users as $key =>$reported_user)
							  @php
							     $user = $reported_user->reportedUser??null;
							     $reporter_user = $reported_user->reporter??null;
							  @endphp
							<tr>
								<td>{{ $key+1 }}</td>
								<td>{{ $user->full_name??'' }}</td>
								<td>{{ $user->email??""}}</td>
								<td>{{ $user->phone??""}}</td>
								<td>{{ $reporter_user->full_name ?? ""}}</td>
								
								<!-- <td>
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
								</td> -->
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
								Showing {{$reported_users->firstItem()}} to {{$reported_users->lastItem()}} of {{$reported_users->total()}} entries
							</span>
						</div>
						<div>
							<div id="pagination-container">
								{!! $reported_users->appends(request()->input())->links('custom') !!}
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
</script>
@endsection

