@extends('layouts.admin-master', ['title' => 'Tutorial Page'])

@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Tutorial</h5>
                    </div>
                     @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                    <div class="card-body">
                        <form action="{{ route('store-tutorial') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="image">Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    value="{{ old('image') }}" autocomplete="image" autofocus id="image"
                                    placeholder="Enter Image..." name="image" />
                                @error('image')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="title">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" autocomplete="title" autofocus id="title"
                                    placeholder="Enter Title..." name="title" />
                                @error('title')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="content">Answer</label>
                                        <div id="editor" style="height: 400px;" class="@error('page_content') is-invalid @enderror">
                                        </div>
                                        @error('page_content')
                                            <span class="help-block invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <textarea class="mb-3 d-none" name="details" id="content"></textarea>
                                <!--<label class="form-label" for="content">Details</label>-->
                                <!--<textarea class="summernote form-control" autofocus name="details"></textarea>-->
                            </div>
                             <div class="button" style="margin-left: 1050px;">
                            <a href="{{ route('info-pages.index') }}" class="btn btn-primary">Back</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 200,
            });
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