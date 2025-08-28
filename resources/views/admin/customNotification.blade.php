@extends('layouts.admin-master', ['title' => 'Send Notification'])

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<style>

    .select2-container{
        width: 100% !important;
    }
    
    .select2-container .selection .select2-selection{
       border: none !important;
    }
    
    .select2-container .selection ul li input[type='search']{
        width: unset!important;
        padding-left: 10px;
        
    }
    
    .scroll input[type='search']:focus-visible  {
        outline-offset: 0!important;
        outline:none!important;
        
    }
    
    .scroll input[type='search']:focus {
        outline: #1550AE 1px solid!important;
        border: none;
    }
    
    
    
    /*#select2Primary option {*/
    /*    padding-block: 7px;*/
    /*    padding-inline: 10px;*/
    /*}*/
    
    
    .noselect {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.dropdown-container, .instructions {
    width: 100%;
    margin: 20px auto 0;
    font-size: 14px;
    font-family: sans-serif;
    overflow: auto;
}

.instructions {
    width: 100%;
    text-align: center;
}

.dropdown-button {
    float: left;
    width: 100%;
    background: whitesmoke;
    padding: 10px 12px;
    cursor: pointer;
    border: 1px solid lightgray;
    box-sizing: border-box;
    
    .dropdown-label, .dropdown-quantity {
        float: left;
    }
    
    .dropdown-quantity {
        margin-left: 4px;
    }
    
    .fa-filter {
        float: right;
    }
}

.dropdown-list {
    float: left;
    width: 100%;
    box-sizing: border-box;
    padding: 10px 0px;
    border: 1px solid #1550ae !important;
    padding-bottom: 4px;
    margin-bottom: 20px;
    border-radius: 7px;
}

.dropdown-list {
    
    input[type="search"] {
        padding: 5px 0;
        width:99%;
        margin: 0px 7px;
    }
    
    input[type="search"]:focus-visible  {
        outline-offset: 0!important;
        outline:none!important;
    }
    
    input[type='search']:focus {
        outline: #979da7 1px solid!important;
        border: none;
    }
    
    /*    #select2primary option:checked {*/
    /*    background-color: #979da7;*/
    /*    color: white;*/
    /*    border-radius: 7px;*/
    /*}*/
    
    /*        #select2primary option:selected {*/
    /*    background-color: #979da7;*/
    /*    color: white;*/
    /*    border-radius: 7px;*/
    /*}*/
    
    input[type='search'] {
         padding-block: 7px;
        padding-inline: 10px;
    }
    
    label {
        padding-bottom: 7px;
        padding-inline: 10px;
        width: 100%;
        border-bottom: 1px solid;
    }
    
    
    label:focus-visible  {
        outline-offset: 0!important;
        outline:none!important;
    }
    
    label:focus {
        outline: #1550AE 1px solid!important;
        border: none;
    }
    
   
    
    ul {
        margin: 10px 0;
        max-height: 200px;
        overflow-y: auto;
        
        input[type="checkbox"] {
            position: relative;
            top: 2px;
        }
    }
    
}

    .select2-container--default .select2-results>.select2-results__options  {
        max-height: 230px!important;
    }
    
    
    
</style>

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Send Notification</h5>
                    </div>
                     @if (session('success'))
                        <div class="alert alert-success" id="flashSuccessMessage">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="card-body">
                        <form action="{{ route('sendCustomNotification') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Select whom to send</label>
                                <div class="mb-3">
                                    <div class="btn-group @error('chosenOption') is-invalid @enderror" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" name="chosenOption" id="all"
                                            autocomplete="off" value="all" checked>
                                        <label class="btn btn-outline-primary" for="all">All Users</label>

                                        <input type="radio" class="btn-check" name="chosenOption" id="individual"
                                            autocomplete="off" value="individual">
                                        <label class="btn btn-outline-primary" for="individual">Individual</label>
                                    </div>
                                    @error('chosenOption')
                                        <span class="help-block invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3 d-none" id="users">
                                <label class="form-label" for="select2Primary">Select Users</label>
                                 <div class="form-floating form-floating-outline">
                                <div class="select2-primary">
                                      
                                     <!--new-->
                                <!--<div class="scroll margin: 0 3px;">-->
                                <!--    <input type="search" placeholder="Search.." style="width:100%; outline-offset:0; height: 40px; padding: 10px;">-->
                                <!--</div>-->
                                <!--new-->
                                
                                
                                    <!--<div class="dropdown-container">-->
                                    <!--     <div class="dropdown-button noselect">-->
                                    <!--            <div class="dropdown-label">States</div>-->
                                    <!--            <div class="dropdown-quantity">(<span class="quantity">Any</span>)</div>-->
                                    <!--            <i class="fa fa-filter"></i>-->
                                    <!--    </div>-->
                                                <div class="dropdown-list" >
                                        <!--<label for="select2Primary" class=" mb-2">Select Options:</label>-->
                                        <!--<input type="search" placeholder="Search states" class="dropdown-search"/>-->
                                        <select id="select2Primary" class="select2 form-select" style="border-radius: 0;" placeholder="Search By Email..." name="ids[]" multiple>
                                          @foreach($users as $user) 
                                            <option value="{{$user->id}}">{{$user->email}}</option>
                                          @endforeach
                                        </select>
                                    </div>
                                <!--</div>-->
                                    
                                  </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="title">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" autocomplete="title" autofocus id="title"
                                    placeholder="Enter Page Name..." name="title" />
                                @error('title')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="message">Message</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                    value="{{ old('message') }}" autocomplete="message" autofocus id="message"
                                    placeholder="Enter Page Name..." name="message" style="height:6rem;"></textarea>
                                @error('message')
                                    <span class="help-block invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                    
                            <br/>
                             <div class="button-container">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
   <script>
    jQuery(document).ready(function() {
        // $("#select2Primary").select2();
        
        // $('#select2Primary').on('select2:open', function () {
        //     $('.select2-search__field').attr('placeholder', 'Search by option....');
        // });
        
        // Hide the session success message after 2 seconds
        if (jQuery("#flashSuccessMessage").length) {
            setTimeout(function() {
                $("#flashSuccessMessage").fadeOut("slow", function() {
                    $(this).remove();
                });
            }, 2000); // 2 seconds
        }
        jQuery('#individual').on("click", function() {
            $('#users').removeClass('d-none');
        });
        jQuery('#all').on("click", function() {
            jQuery('#users').addClass('d-none');
            jQuery('#select2Primary').val('').trigger('change');
            jQuery('#select2Primary').val('').trigger('chosen:updated');
        });
    });
        
   </script>
   
   
   

   
   
    

@endsection
