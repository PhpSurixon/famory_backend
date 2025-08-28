@extends('layouts.admin-master', ['title' => 'App Info Settings'])

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create Page</h5>
                    </div>
                     @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                    <div class="card-body">
                        <form action="{{ route('info-pages.store') }}" method="post">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="page_name">Page Name</label>
                                <input type="text" class="form-control @error('page_name') is-invalid @enderror"
                                    value="{{ old('page_name') }}" autocomplete="page_name" autofocus id="page_name"
                                    placeholder="Enter Page Name..." name="page_name" />
                                @error('page_name')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="content">Page Content</label>
                                <div id="editor" style="height: 400px;"
                                    class="@error('page_content') is-invalid @enderror"></div>
                                @error('page_content')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <input type="hidden" name="page_content" id="content">
                            </div>
                             <div class="button-container">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="{{ route('info-pages.index') }}" class="btn btn-primary">Back</a>
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
                            [{
                                script: "super"
                            }, {
                                script: "sub"
                            }],
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
                            ["link", "image", "video", "formula"],
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
