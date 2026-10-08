@extends('layouts.admin-master', ['title' => 'Business'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Business</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('update-business', $data->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="business_name">Business Name</label>
                                        <input type="text" class="form-control @error('business_name') is-invalid @enderror"
                                            value="{{ old('business_name', $data->business_name) }}" id="business_name"
                                            placeholder="Enter Business Name..." name="business_name" autofocus />
                                        @error('business_name')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $data->email) }}" id="email" placeholder="Enter Email..." name="email" />
                                        @error('email')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="business_type">Business Type</label>
                                        <select class="form-control @error('business_type') is-invalid @enderror" id="business_type" name="business_type">
                                            <option value="Internal" {{ old('business_type', $data->business_type) == 'Internal' ? 'selected' : '' }}>Internal</option>
                                            <option value="External" {{ old('business_type', $data->business_type) == 'External' ? 'selected' : '' }}>External</option>
                                        </select>
                                        @error('business_type')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="mobile">Mobile</label>
                                        <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                            value="{{ old('mobile', $data->mobile) }}" id="mobile" placeholder="Enter Mobile..." name="mobile" />
                                        @error('mobile')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="register_date">Register Date</label>
                                        <input type="date" class="form-control @error('register_date') is-invalid @enderror"
                                            value="{{ old('register_date', optional($data->register_date)->format('Y-m-d')) }}" id="register_date" name="register_date" />
                                        @error('register_date')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Active</label>
                                        <br/>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('is_active') is-invalid @enderror" id="active-yes" name="is_active" value="1" {{ old('is_active', (int) $data->is_active) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active-yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('is_active') is-invalid @enderror" id="active-no" name="is_active" value="0" {{ old('is_active', (int) $data->is_active) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active-no">No</label>
                                        </div>
                                        @error('is_active')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="image">Image</label>
                                        <input type="file" id="image" name="image"
                                            class="form-control @error('image') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('image')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                        @if ($data->image)
                                            <img id="image-preview" src="{{ $data->image }}" alt="Image Preview" class="mt-2" style="display:block; width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                        @else
                                            <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="button-container">
                                <a href="{{ route('business') }}" class="btn btn-primary">Back</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .button-container { display: flex; justify-content: flex-end; }
        .button-container .btn { margin-right: 10px; }
    </style>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection
