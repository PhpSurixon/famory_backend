@extends('layouts.admin-master', ['title' => 'Ads Price'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Ads Price</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('update-ads-price',$getdata->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                               <div class="row">
                                   
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-last_name">Month</label>
                                        <input type="text" class="form-control" value="{{$getdata->day}}" readonly/>
                                       
                                   </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-first_name">Price</label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $getdata->price) }}" 
                                        min="1" onkeypress="return (event.charCode !=8 && event.charCode ==1 || (event.charCode >= 48 && event.charCode <= 57))" />
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
                            <a href="{{ route('ads-price') }}" class="btn btn-primary">Back</a>
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