@extends('layouts.admin-master', ['title' => 'Featured Partner Price'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Featured Partner Price</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('store-featured-company-price') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-month">Month</label>
                                        <input type="number" class="form-control @error('month') is-invalid @enderror"
                                            value="{{ old('month') }}" autocomplete="month" autofocus id="basic-default-city"
                                            placeholder="Enter month..." name="month" />
                                        @error('month')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-price">Price</label>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror"
                                            value="{{ old('price') }}" autocomplete="price" autofocus id="basic-default-state"
                                            placeholder="Enter price..." name="price" />
                                        @error('price')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <br/>
                             <div class="button-container">
                            <a href="{{ route('featured-company-payment') }}" class="btn btn-primary">Back</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
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