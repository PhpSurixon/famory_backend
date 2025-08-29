@extends('layouts.admin-master', ['title' => 'Open World'])

@section('content')
<style>
    .post-cards .card{
           padding: 15px;
    max-height: 373px;
    min-height: 415px;
    }
    .post-cards .card .video-container,
    .post-cards .card .img-container,
    .post-cards .card  .audio-container{
        width: 100% !important;
        margin-top: 0px;
    }
    .post-cards .card  .audio-container
    .post-cards .card .video-container video,
    .post-cards .card .img-container img{
        width: 100% !important;
        border-radius: 10px;
    }
    .post-cards .card .audio-container{
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #f9f9f9;
        background-size: cover;
        background-repeat: no-repeat;
        height: 175px;
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
    .post-cards .card .video-container video{
        width: 100% !important;
        border-radius: 10px;
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">


<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="main-box">
        <div id="card-loader" class="loader-container">
                <div class="spinner-border" role="status"></div>
                    <span class="visually-hidden">Loading...</span>
            </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" action="{{ route('openworld') }}">
                    <label for="sort-select" class="mb-1">Sort by Schedule Date:</label>
                    <select id="sort-select" name="sort_by" class="form-select" onchange="this.form.submit();">
                        <option value="desc" {{ request('sort_by') == 'desc' ? 'selected' : '' }}>Newest</option>
                        <option value="asc" {{ request('sort_by') == 'asc' ? 'selected' : '' }}>Oldest</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="row">
            
                @foreach ($posts as $key => $post)
                    @if($post->user)
                    <div class="box post-cards col-xl-4 col-lg-4 col-md-6 col-12 mb-4 h-85">
                        <div class="card" onclick="window.location.href='{{ route('postDetails',$post['id']) }}'" style="cursor: pointer;">
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
                                    <audio class="w-100" controls  muted>
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
                            <div class="card-body">
                                @php
                                    $postCount = DB::table('likes')->where('post_id',$post->id)->count();
                                @endphp
                                <h5 class="card-title mb-3">{{ ucfirst($post['title']) }}</h5>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="card-text mb-0"><small class="text-muted">Posted by: <strong><a href="{{ route('viewUserDeatils',$post['user']['id']) }}">{{ $post['user']['first_name'] }} {{ $post['user']['last_name'] }}</a></strong></small></p>
                                </div>
                                <hr>
                                <p class="card-text long-description">
                                    {{ substr($post['description'], 0, 100) }}{{ strlen($post['description']) > 100 ? '...' : '' }}
                                    @if(strlen($post['description']) > 100)
                                        <span class="more-text" style="display: none;">{{ substr($post['description'], 75) }}</span>
                                        <a href="#" class="show-more">show more</a>
                                    @endif
                                </p>
                                <hr>
                                <p class="card-text"><small class="text-muted"><img src="{{ url('/') }}/assets/img/likes.png" height="20px" width="20px" alt="..."> <b> {{  $postCount > 0? $postCount:0}} </b></small></p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            <br/>
            {{ $posts->links('pagination::bootstrap-5') }}
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
    

    
    
    
});
    $(window).on('load', function() {
        $('#card-loader').fadeOut('slow');
    });
</script>
@endsection

<style>
    a .card-link {
        text-decoration: none;
    }
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

</style>
