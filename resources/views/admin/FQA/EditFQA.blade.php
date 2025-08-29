@extends('layouts.admin-master', ['title' => 'F.Q.A'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit F.Q.A</h5>
                    </div>
                    <div class="card-body">
                         <form action="{{ route('updateFqa',$getFQAs->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                               <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="basic-default-ques">Question</label>
                                        <textarea class="form-control mb-3 @error('ques') is-invalid @enderror" value="{{ old('ques') }}" autocomplete="ques" autofocus id="basic-default-ques"
                                            placeholder="Enter Question..." name="ques">{{$getFQAs->question}} </textarea>
                                        @error('ques')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-xl-6 col-lg-6 col-md-6 col-12">
                                        <label class="form-label" for="content">Answer</label>
                                        <div id="editor" style="height: 400px;" class="@error('page_content') is-invalid @enderror">
                                            {!! $getFQAs->answer !!}
                                        </div>
                                        @error('page_content')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <textarea class="mb-3 d-none" name="ans" id="content">{{ $getFQAs->answer }}</textarea>
                                    </div>

                                    <!--<div class="col-6">-->
                                    <!--    <label class="form-label" for="basic-default-ques">Answer</label>-->
                                    <!--    <textarea class="form-control @error('ans') is-invalid @enderror" value="{{ old('ans') }}" autocomplete="ans" autofocus id="basic-default-ans"-->
                                    <!--        placeholder="Enter Answer..." name="ans">{{$getFQAs->answer}}</textarea>-->
                                    <!--    @error('ques')-->
                                    <!--        <span class="help-block invalid-feedback" role="alert">-->
                                    <!--            <strong>{{ $message }}</strong>-->
                                    <!--        </span>-->
                                    <!--    @enderror-->
                                    <!--</div>-->
                                </div>
                            </div>
                        <br/>
                        <div class="button-container">
                            <a href="{{ route('get-users') }}" class="btn btn-primary">Back</a>
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
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('content')) {
                var editor = new Quill('#editor', {
                    // modules: {
                    //     toolbar: true
                    // },
                    placeholder: 'Enter content',
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ["bold", "italic", "underline", "strike"],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            // [{
                            //     script: "super"
                            // }, {
                            //     script: "sub"
                            // }],
                            [{
                                header: "1"
                            }, {
                                header: "2"
                            }, "blockquote", "code-block"],
                            [{
                                list: "ordered"
                            }, {
                                list: "bullet"
                            }, {
                                indent: "-1"
                            }, {
                                indent: "+1"
                            }],
                            [{
                                direction: "rtl"
                            }],
                              [{ 'align': [] }],
                            // ["link", "image", "video", "formula"],
                            ["clean"]
                        ]
                    }
                });
                var quillEditor = document.getElementById('content');
                editor.on('text-change', function() {
                    quillEditor.value = editor.root.innerHTML;
                });

                quillEditor.addEventListener('input', function() {
                    editor.root.innerHTML = quillEditor.value;
                });
            }
        });
    </script>
@endsection