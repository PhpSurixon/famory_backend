@extends('layouts.admin-master', ['title' => 'Post'])

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
        .post-cards .card{
        padding: 15px;
    }
    .post-cards .card .video-container,
    .post-cards .card .img-container,
    .post-cards .card  .audio-container{
        width: 100% !important;
    }
    .post-cards .card .video-container video,
    .post-cards .card .img-container img{
        width: 100% !important;
    }
    .post-cards .card .card-body .card-title{
            font-weight: 700;
            font-size: 22px;
            color: #1550ae;
        margin-bottom: 10px;
    }
    .post-cards .card .card-body{
        padding: 10px;
    }
    .post-cards .card .card-body .long-description{
        font-size: 13px;
    }
    .post-cards .card hr{
            margin: 5px 0px 10px;
    }
    .post-cards .schedule-container .card-text{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .post-cards .schedule-container .card-text:not(:last-child){
        margin-bottom: 10px !important;
    }
    .main-box{
        height: auto !important;
        overflow-y: auto !important;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="{{ route('viewUserDeatils', $user->id) }}" class="btn btn-primary m-b-9 mb-4">Back</a>
    <div class="main-box">
        <h5 class="card-header d-flex align-items-center justify-content-start">Name:- {{$user->first_name .' '. $user->last_name}}</h5>
        <div class="row">
            <div id="card-loader" class="loader-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            @if($posts->isEmpty())
            <div class="noData">
                Not Any Post
            </div>
            @else
            @foreach ($posts as $key => $post)
                <div class="box post-cards col-md-4 mb-4 h-85">
                    <div class="card ">
                        @php
                            $file = $post['file'] ?? null;
                            $extension = $file ? pathinfo($file, PATHINFO_EXTENSION) : null;
                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                            $audioExtensions = ['mp3', 'wav', 'ogg'];
                            
                        @endphp
                        
                        
                            @if($file && in_array($extension, $imageExtensions))
                            <div class="img-container m-0">
                                <img src="{{ $file }}" class="card-img-top" alt="...">
                            </div>
                            @elseif($file && in_array($extension, $audioExtensions))
                                <div class="audio-container m-0" style="border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;    background-size: cover;background-repeat: no-repeat;height: 220px;background-image:url('{{ asset('assets/img/audio_bg.png') }}');">
                                    <h5 style="color:#fff; margin-bottom: 20px; margin-top: 30px;">Listen to Audio:</h5>
                                    <audio class="w-100" controls muted>
                                        <source src="{{ $file }}" type="audio/{{ $extension }}">
                                        Your browser does not support the audio tag.
                                    </audio>
                                </div>
                            @elseif(!empty($post['video_formats']))
                            <div class= "video-container m-0">
                                <video class="card-img-top" muted controls>
                                    <source src="{{ $post['video_formats']['original']}}" type="video/mp4" >
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            @else
                            <div class="img-container m-0">
                                <img src="{{ asset('assets/img/famcam.jpg') }}" class="card-img-top" alt="...">
                            </div>
                            @endif
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                    @php
                                        $postCount = DB::table('likes')->where('post_id',$post->id)->count();
                                    @endphp
                                    <p class="card-text mb-1"><small class="text-muted"><img src="{{ url('/') }}/assets/img/likes.png" height="20px" width="20px" alt="..."> <b>: {{  $postCount > 0? $postCount:0}} </b></small></p>
                                    
                                    
                                </div>
                             <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-text mb-0"><small class="text-muted">Posted by: <strong>{{ $post['user']['first_name'] }} {{ $post['user']['last_name'] }}</strong></small></p>
                                <p class="card-text mb-0"><small class="text-muted">Post Type: <strong>{{ ucfirst($post['post_type']) }}</strong></small></p>
                            </div>
                            <h5 class="card-title">{{ $post['title'] }}</h5>
                            <!--<p class="card-text long-description">{{ $post['description'] }}</p>-->
                            <p class="card-text long-description">
                                {{ substr($post['description'], 0, 75) }}{{ strlen($post['description']) > 75 ? '...' : '' }}
                                @if(strlen($post['description']) > 75)
                                    <span class="more-text" style="display: none;">{{ substr($post['description'], 75) }}</span>
                                    <a href="#" class="show-more">show more</a>
                                @endif
                            </p>
                           <hr>
                           <div class="schedule-container">

                                    <p class="card-text mb-0"><small class="text-muted">Schedule Type: </small><small style="color:#0a4ebb;">{{ ucfirst($post['scheduling_post']['schedule_type']) }}</small></p>
                                    @php
                                        $scheduleTime = $post['scheduling_post']['schedule_time'];
                                        $dateTime = new DateTime($scheduleTime);
                                        $formattedTime = $dateTime->format('h:i A'); // 12-hour format with AM/PM
                                    @endphp
                                    
                                    <p class="card-text mb-0"><small class="text-muted">Schedule Time:</small><small class="text-muted">{{ $formattedTime }}</small></p>
                                    <p class="card-text mb-0"><small class="text-muted">Schedule Date:</small><small class="text-muted">{{ $post['scheduling_post']['schedule_date'] }}</small></p>
                                    <p class="card-text mb-0"><small class="text-muted">Reoccurring Type:</small><small class="text-muted">{{ ucfirst($post['scheduling_post']['reoccurring_type']) }}</small></p>
                                    <p class="card-text mb-0"><small class="text-muted">Reoccurring Time:</small><small class="text-muted">{{ $post['scheduling_post']['reoccurring_time'] ?? 'Not set' }}</small></p>
                                    <p class="card-text mb-0">
                                        <small class="text-muted">
                                            Status: 
                                            
                                        </small>
                                        <small>
                                            @if($post['scheduling_post']['is_post'] == 1)
                                                <span style="color:green;font-size: 11px;">Post</span>
                                            @else
                                                <span style="color:red;font-size: 11px;">Not Post</span>
                                            @endif</small>
                                    </p>
                                </div>
                        </div>
                        <!--<div class="card-footer bg-transparent border-top">-->
                        <!--    <div class="d-flex justify-content-between align-items-start">-->
                                
                                
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                </div>
            @endforeach
            @endif
        </div>
        <br/>
    {{ $posts->links('pagination::bootstrap-5') }}
    </div>
</div>

<script type="text/javascript">
    $(window).on('load', function() {
        $('#card-loader').fadeOut('slow');
    });
    
    $(document).ready(function() {
    $('#data-table').DataTable();
    $('.show-more').click(function(e) {
        e.preventDefault();
        $(this).siblings('.more-text').toggle();
        $(this).text($(this).text() === 'show more' ? 'show less' : 'show more');
    });
});
</script>
@endsection

<style>
    .main-box {
        padding: 15px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.18);
        border-radius: 8px;
        background: #d6d6d75c;
        height: calc(100vh - 115px);
        overflow-y: scroll;
    }
    .card {
        transition: transform 0.3s;
        color: #000000b0;
        /*margin: 20px;*/
        margin-bottom:20px;
        margin-top:20px;
        box-shadow: 1px 1px 1px 1px #11377e;
    }
    .card .img-container {
        position: relative;
        padding-top: 58%;
        width: 90%;
    }
    .card .img-container img {
        position: absolute;
        width: 95%;
        height: 100%;
        object-fit: cover;
        left: 0;
        top: 0;
    }
    
      .card .video-container {
        position: relative;
        padding-top: 58%;
        width: 90%;
    }
    .card .video-container .card-img-top {
        position: absolute;
        width: 95%;
        height: 100%;
        object-fit: cover;
        left: 0;
        top: 0;
    }
    .card:hover {
        transform: scale(1.02);
    }
    .image-container {
        margin: 10px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .card-text .text-muted, .card-title {
        color:  #000000b !important;
    }
    .card-footer .card-text .text-muted {
        font-weight: 600;
    }
    .video-container{
        width: 35%;
        margin:auto;
        margin-top:8px;
    }
    .audio-container {
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #f9f9f9;
        padding: 10px;
    }
    
    .audio-container h5 {
        margin-bottom: 10px;
    }
    
    .audio-container .audio-info {
        margin-top: 10px;
        font-size: 0.9em;
        color: #555;
    }
    .noData {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 25vh;
        font-size: 24px;
    }

</style>