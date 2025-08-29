@extends('layouts.admin-master', ['title' => 'FQA'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create FQA</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('store-fqa') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="basic-default-ques">Question</label>
                                        <textarea class="form-control @error('ques') is-invalid @enderror" value="{{ old('ques') }}" autocomplete="ques" autofocus id="basic-default-ques"
                                            placeholder="Enter Question..." name="ques"> </textarea>
                                        @error('ques')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="content">Answer</label>
                                        <div id="editor" style="height: 400px;"
                                            class="@error('page_content') is-invalid @enderror"></div>
                                        @error('page_content')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <input type="hidden" name="ans" id="content">
                                    </div>
                                    <!--<div class="col-6">-->
                                    <!--    <label class="form-label" for="basic-default-ques">Answer</label>-->
                                    <!--    <textarea class="form-control @error('ans') is-invalid @enderror" value="{{ old('ans') }}" autocomplete="ans" autofocus id="basic-default-ans"-->
                                    <!--        placeholder="Enter Answer..." name="ans"> </textarea>-->
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
                            <a href="{{route ('f-q-a')}}" class="btn btn-primary">Back</a>   
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
        
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('content')) {
                var editor = new Quill('#editor', {
                    modules: {
                        toolbar: true
                    },
                    placeholder: 'Enter Answer',
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