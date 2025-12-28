@extends('layouts.admin-master', ['title' => 'User Details'])

@section('content')
 <style>
    .hidden {
        display:none;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    .title{
        font-weight: 600;
        font-size: 18px;
    }
    .img-circle {
        border-radius: 50%;
        width: 50px;
        height: 50px;
    }
    
    
    /* Style the tab */
    .tab {
      overflow: hidden;
    
    }

/* Style the buttons that are used to open the tab content */
    .tab button, a.grp-btnn  {
        color: #0e0c0c;
        background-color: #B6D2FC;
        margin-right: 10px;
        padding: 12px 18px;
        font-size: 1.125rem;
        font-weight: 500;
        line-height: 1.1;
        float: left;
        transition: 0.3s;
        cursor: pointer;
        border-radius: 60px;
        outline: none;
        border:0;
        box-shadow:0 !important;
         
    }
    
    .tab {
        
    background-color: #B6D2FC;
    border-radius: 60px;

    }
    
    .tab button.active {
      background-color: #305daa;;
      color: white;
    }

    .tab button:hover, a.grp-btnn:hover {
    color: white;
    background-color: #305daa;;
    outline: 1px solid #305daa;;
    box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
    }

/* Style the tab content */
    .tabcontent {
    display: none;
    padding: 22px 0px;
    box-shadow: 0 0.125rem 0.25rem 0 rgb(229 229 229 / 40%);
    border-top: none;
    }
    
    .card-info {
        width: 100%;
        background-color: #f3fbff;
        box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
        margin-left: 0 !important;
    }
    
    .info-grp h5 {
       margin-bottom: 10px;
    }
    
    .info-img img {
        border-radius: 50%;
    }
    
    .red-btn button {
    padding: 0;
    margin-right: 10px;
    margin-top: 16px;
    border-radius: 39px;
    }
    
    
    
    .red-btn button:hover {
        background-color:transparent;
        color:red;
    }
    
   
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="row">
        <div class="mb-4 d-flex justify-content-start">
                    <a href="{{ route('get-users') }}" class="btn btn-primary m-b-9 mb-1">Back</a>
                </div>
        <div class="card">
        <div class="col-md-12">
            <div class="my-4">
                <div class="card-info d-flex justify-content-between">
                 <div class="card-header  d-flex gap-4">
                     <div class="info-img">
                           @if($user->image)
                            <img src="{{ $user->image }}" alt="User Image" width="100" height="100">
                        @else
                            <img src="/assets/img/famcam.jpg" alt="Default Image" width="100" height="100">
                        @endif
                     </div>
                     
                     <div class="info-grp">
                    <div class="d-flex gap-2">
                <h5 class="text-dark">Name:- 
                </h5>
                
                <h5 class="fw-light">{{$user->first_name .' '. $user->last_name}}</h5>
                    </div>
                    
                    
                    <div class="d-flex gap-2">
                <h5 class="text-dark">Email Address:-
                </h5>
                
                <h5 class="fw-light">{{$user->email ?? '-'}}</h5>
                    </div>
                    
                    <div class="d-flex gap-2">
                <h5 class="text-dark">Phone No:- 
                </h5>
                
                <h5 class="fw-light">{{$user->phone ?? '-'}}</h5>
                    </div>
                    </div>
                </div>
                
                @if($user->ban_user == '1')
                
                <div class="my-3 mx-3">
                    <p class="fw-bold text-danger">Banned</p>
                </div>
                    
                @else
               
                   <div class="red-btn">
                       <button class="btn btn-outline-danger btn-sm">
                       <a class="dropdown-item" style="color: red;" href="javascript:void(0);" onclick="deleteUser('{{ $user->id }}')">
                    <i class="bx bx-trash me-1"></i> Ban User 
                    <!--<i class="bi bi-ban"></i>-->
                </a>
                </button>
                   </div>
                @endif
                </div>
                
            </div>
        </div>
   
  
        <div class="col-md-12">
            <!--<div class="card">-->
                <div id="card-loader" class="loader-container">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <!--new-->
                
                <div class="card-body m-0 p-0">

                            <!--whole-content-->
                            
                            <!-- Tab links -->
                            <div class="tab px-3 py-2">
                              <button class="tablinks active" onclick="openCity(event, 'Burial-Info')" id="defaultOpen">Burial Info</button>
                              <button class="tablinks" onclick="openCity(event, 'Family-&-Friends')">Family Member</button>
                              <button class="tablinks" onclick="openCity(event, 'Created-Groups-Info')">Created Groups Info</button>
                              <button class="tablinks" onclick="openCity(event, 'Block-User-Info')">Block User Info</button>
                                <!--<a href="{{ route('get-users') }}" class="btn grp-btnn">Back</a>-->
                                <a href="{{ route('allPosts', $user->id) }}" class="btn grp-btnn">User All Post</a>
                            
                            </div>
                            
                            <!-- Tab content -->
                            <div id="Burial-Info" class="tabcontent">
                              <!--<div class="con card">-->
                              <!--    <div class="d-flex">-->
                              <!--        <span class="title" style="padding: 0.625rem 1.25rem;">Funeral Name</span>-->
                              <!--        <span style="font-size: 18px; padding: 0.625rem 1.25rem;">{{($getBurial->funeral_home) ?? '-'}}</span>-->
                              <!--    </div>-->
                                  
                              <!--    <div class="d-flex">-->
                              <!--        <span class="title" style="padding: 0.625rem 1.25rem;">Address</span>-->
                              <!--        <span style="font-size: 18px; padding: 0.625rem 1.25rem;">{{($getBurial->address) ?? '-'}}</span>-->
                              <!--    </div>-->
                                  
                              <!--    <div class="d-flex">-->
                              <!--        <span class="title" style="padding: 0.625rem 1.25rem;">Plot #</span>-->
                              <!--        <span style="font-size: 18px; padding: 0.625rem 1.25rem;">{{($getBurial->plot_number) ?? '-'}}</span>-->
                              <!--    </div>-->
                                  
                              <!--    <div class="d-flex">-->
                              <!--        <span class="title" style="padding: 0.625rem 1.25rem;">Contact</span>-->
                              <!--        <span style="font-size: 18px; padding: 0.625rem 1.25rem;">{{($getBurial->contact ) ?? '-'}}</span>-->
                              <!--    </div>-->
                                  
                              <!--    <div class="d-flex">-->
                              <!--        <span class="title" style="padding: 0.625rem 1.25rem;">Notes</span>-->
                              <!--        <span style="font-size: 18px; padding: 0.625rem 1.25rem;">{{($getBurial->notes) ?? '-'}}</span>-->
                              <!--    </div>-->
                              <!--</div>-->
                                  <table class="datatables-basic table border-top" style="border:  0.5px solid lightgray;">
                                   <thead class="table-light">
                                       <tr style="border:none;">
                                           <td style="border:none;" class="title">
                                               Funeral Name
                                           </td>
                                           <td style="font-size: 18px; border:none;">
                                               {{($getBurial->funeral_home) ?? '-'}}
                                           </td>
                                       </tr>
                                        <tr style="border:none;">
                                           <td class="title" style="border:none;">
                                               Address
                                           </td>
                                           <td style="font-size: 18px; border:none;">
                                              {{($getBurial->address) ?? '-'}}
                                           </td>
                                       </tr>
                                        <tr style="border:none;">
                                           <td class="title" style="border:none;">
                                                Plot #
                                           </td>
                                           <td style="font-size: 18px; border:none;">
                                              {{($getBurial->plot_number) ?? '-'}}
                                           </td>
                                       </tr>
                                        <tr style="border:none;">
                                           <td class="title" style="border:none;">
                                               Contact
                                           </td>
                                           <td style="font-size: 18px; border:none;">
                                               {{($getBurial->contact ) ?? '-'}}
                                           </td>
                                       </tr>
                                       <tr style="border:none;">
                                           <td class="title" style="border:none;">
                                               Notes
                                           </td>
                                           <td style="border:none;">
                                               {{($getBurial->notes) ?? '-'}}
                                           </td>
                                       </tr>
                                       <!--<tr>-->
                                       <!--    <td class="title">-->
                                       <!--        Burial PDF Url-->
                                       <!--    </td>-->
                                       <!--    <td style="font-size: 18px;">-->
                                       <!--         @if($getBurial && $getBurial->burial_pdf_url)-->
                                       <!--             <a href="{{ $getBurial->burial_pdf_url }}"  target="_blank">Link</a>-->
                                       <!--         @else-->
                                       <!--             --->
                                       <!--         @endif-->
                                       <!--    </td>-->
                                       <!--</tr>-->
                                   </thead>
                               </table>
                              </div>
                            </div>
                            
                            <div id="Family-&-Friends" class="tabcontent">
                              <div>
                                  <table class="datatables-basic table border-top" style="border:  0.5px solid lightgray;">
                                  @if($paginatedUserGroups->isEmpty())
                                    <tr>
                                        <td colspan="2" style="text-align: center; vertical-align: middle;">
                                            <span style="color: black; font-weight: 600;">Not Found</span>
                                        </td>
                                    </tr>
                                    @else
                                        <thead class="table-light">
                                        @foreach($paginatedUserGroups as $data)
                                           <tr>
                                               <td style="width: 18px;">
                                                    @if($data && $data->image)
                                                        <img src="{{$data->image}}"  class = "img-circle"/>
                                                    @else
                                                        <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle">
                                                    @endif
            
                                               </td>
                                               <td class="title">
                                                   {{$data ? $data->first_name. ' ' . $data->last_name : '-'}}
                                               </td>
                                           </tr>
                                        @endforeach
                                        </thead>
                                    @endif
                                </table>
                              </div>
                            </div>
                            
                            <div id="Created-Groups-Info" class="tabcontent">
                              <div>
                                  <table class="datatables-basic table border-top" style="border:  0.5px solid lightgray;">
                                   @if($group->isEmpty())
                                        <td colspan="2" style="text-align: center; vertical-align: middle;">
                                            <span style="color: black; font-weight: 600;">Not Found</span>
                                        </td>
                                    @else
                                       <thead class="table-light">
                                        @foreach($group as $data)
                                           <tr>
                                               <td style="width: 18px;">
                                                    @if($data && $data->image)
                                                        <img src="{{$data->image}}"  class = "img-circle"/>
                                                    @else
                                                        <img src="/assets/img/fam-cam-logo.png" alt="Default Image" class="img-circle">
                                                    @endif
            
                                               </td>
                                               <td class="title">
                                                   {{ ucfirst($data->name ?? '-') }}
                                               </td>
                                           </tr>
                                            @endforeach
                                       </thead>
                                    @endif
                               </table>
                              </div>
                            </div>
                            
                            <div id="Block-User-Info" class="tabcontent">
                              <div>
                                  <table class="datatables-basic table border-top" style="border:  0.5px solid lightgray;">
                                @if($blockUsers->isEmpty())
                                    <td colspan="2" style="text-align: center; vertical-align: middle;">
                                        <span style="color: black; font-weight: 600;">Not Found</span>
                                    </td>
                                @else
                                   <thead class="table-light">
                                    @foreach($blockUsers as $data)
                                       <tr>
                                           <td style="width: 18px;">
                                                @if($data->blockedUser && $data->blockedUser->image)
                                                    <img src="{{$data->blockedUser->image}}"  class = "img-circle"/>
                                                @else
                                                    <img src="/assets/img/famcam.jpg" alt="Default Image" class="img-circle">
                                                @endif
        
                                           </td>
                                           <td class="title">
                                               {{$data->blockedUser->first_name. ' ' . $data->blockedUser->last_name}}
                                           </td>
                                       </tr>
                                    @endforeach
                                    </thead>
                                @endif
                                </table>
                              </div>
                            </div>
                            
                            
                           <!--whole-content-->
                </div>
            <!--new-->
            <!--</div>-->
        </div>
    </div>
</div>

<script type="text/javascript">
     $(window).on('load', function() {
            $('#card-loader').fadeOut('slow');
        });
</script>

<script>
    function openCity(evt, cityName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
 }
 
 
// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();



 const csrfToken = $('meta[name="csrf-token"]').attr('content');
function deleteUser(id) {
    if (id !== '') {
        swal.fire({
            title: "Are you sure?",
            text: "you want to ban this user from accessing the app.",
            icon: "warning",
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Okay',
            dangerMode: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('banUser') }}",
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id,
                    },
                    success: function(response) {
                        swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        swal.fire({
                            title: 'Error',
                            text: jqXHR.responseJSON.message || 'An error occurred. Please try again.',
                            icon: 'error',
                        });
                    }
                });
            }
        });
    }
}

</script>


@endsection
