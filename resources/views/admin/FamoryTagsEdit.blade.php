@extends('layouts.admin-master', ['title' => 'Tag'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Tag</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('updateFamoryTag',$tag->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                               <div class="row">
                                   
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-last_name">Created User</label>
                                        <input type="text" class="form-control" value="{{$tag->user->first_name}} {{$tag->user->last_name}}" readonly/>
                                       
                                   </div>
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-first_name">Famory Tag Id</label>
                                        <input type="text" class="form-control" value="{{$tag->family_tag_id}}" readonly/>
                                        
                                    </div>
                                   
                                   <div class="col-6">
                                        <label class="form-label" for="basic-default-last_name">Assign User</label>
                                        <select class="form-control" name="user_id" required>
                                            @foreach($users as $user)
                                            <option value = "{{$user->id}}" @if($user->id == $tag->created_user_id) selected @endif>{{$user->first_name}} {{$user->last_name}}</option>
                                            @endforeach
                                        </select>
                                       
                                   </div>
                                   
                               </div>
                            </div>
                            
                        <br/>
                        <div class="button-container">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('famory-tags') }}" class="btn btn-primary">Back</a>
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