@extends('layouts.admin-master', ['title' => 'Post Details'])

@section('content')

<style>
    .post-cards .card-body .card-title {
        font-size: 2.125rem;
    }
    
    .post-cards .card-body .subtitle p small {
        font-size:1rem;
    }
    .post-cards .card-body .card-text {
        font-size:17px!important;
    }
    
    .post-cards .card-body .schedule-container p small {
        color: #566a7f !important;
        font-size: 18px;
        padding-right: 8px;
        font-weight: 600;
    }
    .post-cards .card-body .schedule-container p .input-text {
        font-size: 18px;
        color: #0a4ebb!important;
        font-weight: 400!important;
    }
    .post-cards .card-body .schedule-container p .input-text span {
        font-size: 18px!important;
        font-weight: 400!important;
    } 
    
    .post-button button {
        background-color: #0a4ebb!important;
        color:white;
        padding: 10px 30px;
        outline: none;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
    }
    
    .audio-container {
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
    background-size: cover;
    background-repeat: no-repeat;
    height: 100%;
    padding: 18px 18px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-end;
    background-position: center;
    }
    
    .card-img-top {
        aspect-ratio: 1 / 1;
        object-fit: cover;
    }
    
    .img-container img {
        aspect-ratio: 1 / 1;
        object-fit: cover;
    }
    
    .audio-container {
        aspect-ratio: 1 / 1;
        object-fit: cover;
    }
    
    .likes-no {
        font-size:17px!important;
    }
    
    .post-badge {
    color: white;
    background-color: #0a4ebb !important;
    font-size: 16px;
    padding: 3px 30px;
    border-radius: .5rem;
    font-weight: 500;
    outline: #0a4ebb 1px solid;
    outline-offset: 1px;
    }
    
    .post-text {
        color: #566a7f !important;
        font-size: 18px;
        padding-right: 8px;
        font-weight: 600;
    }
    
    
    .post-c .card {
       box-shadow: none!important; 
    }   
    
    .subtitle p small a {
        font-weight: 400!important;
    }
    

</style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-xxl flex-grow-1 container-p-y">
     <!--<div class="post-button d-flex justify-content-start mb-3 gap-3">-->
     <!--                       <button type="button">Back</button>-->
     <!--                       <button type="button">Post</button>-->
     <!--</div>-->
                    
    
    <div class="main-box">
        <div class="card p-3 pb-0">
        <div class="row">
            <div id="card-loader" class="loader-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
                @if($post->user)
                
                 <div class="box post-cards col-md-7 mb-4 h-auto">
                    
                         <div class="card-body p-0 pt-3">
                             <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title">{{ucfirst($post['title']) }}</h5>
                            <div class="post-badge">
                                @if($post['scheduling_post']['is_post'] == 1)
                                    <span class="post" data-id="{{ $post['id'] }}">Remove Post</span>
                                @else
                                    <span>Not Post</span>
                                @endif
                            </div>
                            </div>
                            <!--<p class="card-text long-description">{{ $post['description'] }}</p>-->
                            <div class="d-flex justify-content-between align-items-center mb-3 subtitle">
                                <p class="card-text mb-0"><small class="text-muted post-text d-flex gap-1">Posted by:<a href="{{ route('viewUserDeatils',$post['user']['id']) }}">{{ $post['user']['first_name'] }} {{ $post['user']['last_name'] }}</a></small></p>
                            </div>
                            
                            <p class="card-text long-description mb-3">
                                {{ substr($post['description'], 0, 200) }}{{ strlen($post['description']) > 200 ? '...' : '' }}
                                @if(strlen($post['description']) > 200)
                                    <span class="more-text" style="display: none;">{{ substr($post['description'], 75) }}</span>
                                    <a href="#" class="show-more">show more</a>
                                @endif
                            </p>
                            <hr>
                            
                           <div class="schedule-container mb-5 mt-3">
                                    <p class="card-text mb-1"><small class="text-muted">Schedule Type: </small><small class="input-text" style="color:#0a4ebb;">{{ ucfirst($post['scheduling_post']['schedule_type']) }}</small></p>
                                    @php
                                        $scheduleTime = $post['scheduling_post']['schedule_time'];
                                        $dateTime = new DateTime($scheduleTime);
                                        $formattedTime = $dateTime->format('h:i A'); // 12-hour format with AM/PM
                                    @endphp
                                    
                                    <!--<p class="card-text mb-1"><small class="text-muted">Schedule Time:</small><small class="text-muted input-text">{{ $formattedTime }}</small></p>-->
                                    <p class="card-text mb-1"><small class="text-muted">Schedule Time Date:</small><small class="text-muted input-text"> {{ $formattedTime }} {{\Carbon\Carbon::parse($post['scheduling_post']['schedule_date'])->format('m/d/y') ?? 'N/A' }}</small></p>
                                    @if($post['scheduling_post']['reoccurring_time'])
                                    <p class="card-text mb-1"><small class="text-muted">Reoccurring Type:</small><small class="text-muted input-text">{{ ucfirst($post['scheduling_post']['reoccurring_type']) }}</small></p>
                                    <p class="card-text mb-1"><small class="text-muted">Reoccurring Time:</small><small class="text-muted input-text"> {{ $post['scheduling_post']['reoccurring_time'] ?? 'Not set' }}</small></p>
                                    @endif
                                    <!--<p class="card-text mb-1">-->
                                    <!--    <small class="text-muted">-->
                                    <!--        Status: -->
                                            
                                    <!--    </small>-->
                                    <!--    <small class="input-text">-->
                                    <!--        @if($post['scheduling_post']['is_post'] == 1)-->
                                    <!--            <span style="color:green;">Post</span>-->
                                    <!--        @else-->
                                    <!--            <span style="color:red;">Not Post</span>-->
                                    <!--        @endif-->
                                    <!--    </small>-->
                                    <!--</p>-->
                                    
                                    <hr>
                                </div>
                                
                        </div>
                    </div>
                
                <div class="box post-c post-cards col-md-5 mb-4 h-auto">
                    
                    <div class="card h-100">
                        @php
                            $file = $post['file'] ?? null;
                            $extension = $file ? pathinfo($file, PATHINFO_EXTENSION) : null;
                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                            $audioExtensions = ['mp3', 'wav', 'ogg'];
                            
                        @endphp
                    
                    
                        @if($file && in_array($extension, $imageExtensions))
                        <div class="img-container">
                            <img src="{{ $file }}" class="card-img-top" alt="...">
                        </div>
                        @elseif($file && in_array($extension, $audioExtensions))
                            <div class="audio-container " style="background-image:url('{{ asset('assets/img/audio_bg.png') }}');">
                                
                                <h5 style="color:#fff; margin-bottom: 20px; margin-top: 30px;">Listen to Audio:</h5>
                                <audio class="w-100" style="margin-bottom:200px;" controls  muted>
                                    <source src="{{ $file }}" type="audio/{{ $extension }}">
                                    Your browser does not support the audio tag.
                                </audio>
                            </div>
                        @elseif(!empty($post['video_formats']))
                        <div class= "video-container">
                            <video class="card-img-top" muted controls>
                                <source src="{{ $post['video_formats']['original']}}" type="video/mp4" >
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        @else
                        <div class="img-container ">
                            <img src="{{ asset('assets/img/famcam.jpg') }}" class="card-img-top" alt="...">
                        </div>
                        @endif
                            <div class="d-flex align-items-center justify-content-between py-2">
                                @php
                                    $postCount = DB::table('likes')->where('post_id',$post->id)->count();
                                @endphp
                                <p class="card-text mb-1"><small class="text-muted"><img src="{{ url('/') }}/assets/img/likes.png" height="30px" width="30px" alt="..."> <b class="likes-no"> {{  $postCount > 0? $postCount:0}} </b></small></p>
                                
                                <!--<p class="card-text mb-0"><small class="text-muted"><img src="{{ url('/') }}/assets/img/comment.png" height="30px" width="30px" alt="..."> <b class="likes-no"> 0 </b></small></p>-->
                            </div>
                        </div>
                    </div>
                                 
                
          
                @endif
        </div>
        </div>
    </div>
</div>


<div class="modal fade" id="mediaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Set modal size to large -->
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="mediaModalLabel">Media</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Content will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#data-table').DataTable();
    $('.show-more').click(function(e) {
        e.preventDefault();
        $(this).siblings('.more-text').toggle();
        $(this).text($(this).text() === 'show more' ? 'show less' : 'show more');
    });
    
    
    $('#showImage').click(function(){
        const mediaType = 'image'; // Change this to 'video' for video content
        const mediaSource = mediaType === 'image' 
        ? 'https://via.placeholder.com/800x400' // Image URL
        : 'https://www.youtube.com/embed/dQw4w9WgXcQ'; // Video URL

      let content;
      if (mediaType === 'image') {
        content = `<img src="${mediaSource}" class="img-fluid" alt="Responsive Image">`;
      } else {
        content = `<iframe width="100%" height="400" src="${mediaSource}" frameborder="0" allowfullscreen></iframe>`;
      }

        $('#modalBody').html(content);
        $('#mediaModal').modal('show'); 
    });
    
     $('.post').on('click', function() {
        var postId = $(this).data('id');
        
        

        // Make AJAX request
        if (postId != null) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve request!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route("openWorldPostHidden")}}',
                        type: 'POST',
                        data: {
                            id: postId,
                             _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                         window.location.href = '/openworld';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong. Please try again.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again.',
                                timer: 2000, 
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select at least one contact to delete.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
    
    
});
    $(window).on('load', function() {
        $('#card-loader').fadeOut('slow');
    });
</script>

@endsection