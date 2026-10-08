@extends('layouts.admin-master', ['title' => 'Business Tags'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Business Tag</h5>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <form action="{{ route('store-business-tag') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="name">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" id="name" placeholder="Enter Name..." name="name" autofocus />
                                        @error('name')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="type_of_tag">Type of Tag</label>
                                        <select class="form-control @error('type_of_tag') is-invalid @enderror" id="type_of_tag" name="type_of_tag">
                                            <option value="" disabled {{ old('type_of_tag') ? '' : 'selected' }}>Select Type</option>
                                            <option value="metal" {{ old('type_of_tag') == 'metal' ? 'selected' : '' }}>Metal</option>
                                            <option value="plastic" {{ old('type_of_tag') == 'plastic' ? 'selected' : '' }}>Plastic</option>
                                            <option value="magnet" {{ old('type_of_tag') == 'magnet' ? 'selected' : '' }}>Magnet</option>
                                        </select>
                                        @error('type_of_tag')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="origin_price">Origin Price</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('origin_price') is-invalid @enderror"
                                            value="{{ old('origin_price') }}" id="origin_price" placeholder="Enter origin price..." name="origin_price" />
                                        @error('origin_price')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="selling_price">Selling Price</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('selling_price') is-invalid @enderror"
                                            value="{{ old('selling_price') }}" id="selling_price" placeholder="Enter selling price..." name="selling_price" />
                                        @error('selling_price')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                            placeholder="Enter Description..." cols="10" rows="5">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="image">Image</label>
                                        <input type="file" id="image" name="image"
                                            class="form-control @error('image') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('image')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                        <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Active</label>
                                        <br/>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('active') is-invalid @enderror" id="active-yes" name="active" value="1" {{ old('active', '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active-yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('active') is-invalid @enderror" id="active-no" name="active" value="0" {{ old('active', '1') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active-no">No</label>
                                        </div>
                                        @error('active')
                                            <span class="help-block invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="button-container">
                                <a href="{{ route('business-tag') }}" class="btn btn-primary">Back</a>
                                <button type="submit" class="btn btn-primary">Submit</button>
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
