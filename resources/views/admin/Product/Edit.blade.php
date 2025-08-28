@extends('layouts.admin-master', ['title' => 'Famory Tags'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Famory Tags</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('update-product',$data->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-first_name">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            value="{{$data->name}}" autocomplete="first_name" autofocus id="basic-default-name"
                                            placeholder="Enter Name..." name="name" />
                                        @error('name')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-tag-type">Type of Tag</label>
                                        <select class="form-control @error('type_of_tag') is-invalid @enderror" 
                                                id="basic-default-tag-type"
                                                name="type_of_tag">
                                            <option value="metal" {{ $data->tag_type == 'metal' ? 'selected' : '' }}>Metal</option>
                                            <option value="plastic" {{ $data->tag_type == 'plastic' ? 'selected' : '' }}>Plastic</option>
                                        </select>
                                        @error('type_of_tag')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-reseller_price">Reseller Price</label>
                                        <input type="number" class="form-control @error('reseller_price') is-invalid @enderror"
                                            value="{{$data->reseller_price}}" autocomplete="reseller_price" autofocus id="basic-default-price"
                                            placeholder="Enter price..." name="reseller_price" />
                                        @error('reseller_price')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-price">Price</label>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror"
                                            value="{{$data->price}}" autocomplete="price" autofocus id="basic-default-price"
                                            placeholder="Enter price..." name="price" />
                                        @error('price')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            
                            
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-count">Quantity</label>
                                        <input type="text" id="basic-default-count" name="count"
                                            class="form-control @error('count') is-invalid @enderror"
                                            placeholder="Enter Quantity..." value="{{$data->count}}" required />
                                        @error('count')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-image">Image</label>
                                        <input type="file" id="basic-default-image" name="image"
                                            class="form-control @error('image') is-invalid @enderror"
                                            accept="image/*" onchange="previewImage(event)" />
                                        @error('image')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        @if ($data->image)
                                            <img id="image-preview" src="{{ $data->image }}" alt="Image Preview" class="mt-2"  style="display:block; width: 100px; height: 100px; object-fit: cover; border-radius: 50%; float: left; margin-right: 10px;">
                                        @else
                                            <img id="image-preview" src="#" alt="Image Preview" class="mt-2" style="display:none; width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                             <div class="mb-3">
                                <div class="row">
                                        <div class="col-6">
                                            <label class="form-label" for="basic-default-description">Description</label>
                                            <textarea id="basic-default-description" name="description" 
                                                      class="form-control @error('description') is-invalid @enderror"
                                                      placeholder="Enter Description..." required cols="10" rows="5">{{$data->description}}</textarea>
                                            @error('description')
                                                <span class="help-block invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-6">
                                            <label class="form-label" for="basic-default-tag_purpose">Tag Purpose</label>
                                            <textarea id="basic-default-tag_purpose" name="tag_purpose" 
                                                      class="form-control @error('tag_purpose') is-invalid @enderror"
                                                      placeholder="Enter Tag Purpose..."  cols="10" rows="5">{{$data->tag_purpose}}</textarea>
                                            @error('tag_purpose')
                                                <span class="help-block invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        
                                        
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="color-options">Color</label>
                                        <br/>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('color') is-invalid @enderror"  id="color-yes"  name="color"  value="yes"{{ $data->color == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="color-yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('color') is-invalid @enderror" id="color-no" name="color" value="no" {{ $data->color == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="color-no">No</label>
                                        </div>
                                        @error('color')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="color-options">High priority</label>
                                        <br/>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('is_favourite') is-invalid @enderror"  id="f-yes"  name="is_favourite"  value="1"{{ $data->is_favourite == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="f-yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input @error('is_favourite') is-invalid @enderror" id="f-no" name="is_favourite" value="0" {{ $data->is_favourite == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="f-no">No</label>
                                        </div>
                                        @error('is_favourite')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <br/>
                        <div class="button-container">
                            <a href="{{ route('product') }}" class="btn btn-primary">Back</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .button-container {
        display: flex;
        justify-content: flex-end; 
    }
    
    .button-container .btn {
        margin-right: 10px; 
    }

    </style>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endsection