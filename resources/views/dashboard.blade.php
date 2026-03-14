@extends('layouts.admin-master', ['title' => 'Dashboard'])
@section('content')
<!-- STATISTIC-->
<style>
   .box-height{
   display: flex;
   justify-content: stretch;
   align-items: stretch;   
   }
   .box-height a{
   display: block;
   width: 100%;
   }
   .box-height .card{
   height: auto;  
   }
   .avatar{
   display: flex;
   align-items: center;
   justify-content: center;
   background: #1550ae1f;
   border-radius: 4px;
   width: 56px;
   height: 56px;
   }
   .avatar i{
   font-size: 42px;
   color: #1550ae;
   }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row">
      <!-- <div class="col-lg-12 mb-4 order-0">
         <div class="card">
            <div class="d-flex align-items-end row">
               <div class="col-sm-7">
                  <div class="card-body">
                     <h2 class="card-title text-primary">Welcome {{ Auth::user()->first_name }}!</h2>
                  </div>
               </div>
               <div class="col-sm-5 text-center text-sm-left">
                  <div class="card-body pb-0 px-0 px-md-4">
                     <img src="{{ url('/') }}/assets/img/illustrations/images (4).jpeg" height="140"
                        alt="View Badge User" data-app-dark-img="illustrations/images (4).jpeg"
                        data-app-light-img="illustrations/images (4).jpeg" />
                  </div>
               </div>
            </div>
         </div>
      </div> -->
      <div class="col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Users & Posts</h5>
                        <canvas id="dashboardLineChart" height="100"></canvas>
                    </div>
                </div>
        </div>
        <div class="col-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Tag</h5>
                        <canvas id="dashboardLineChart1" height="100"></canvas>
                    </div>
                </div>
            </div>
      
      <div class="col-12">
         <div class="row">
            <!-- Card 1 -->
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('get-users') }}">
                  <div class="card" >
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-user-plus'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Total Users</span>
                           @php
                           $userCount = DB::table('users')->where('role_id',2)->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $userCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <!-- Card 2 -->
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('openworld') }}">
                  <div class="card" >
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bx-globe'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Open World Posts</span>
                           @php
                           $postCount = DB::table('posts')->where('post_type','public')->count();
                           @endphp
                           <h3 class="card-title mb-0" >{{ $postCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <!-- Card 3 -->
            
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('famory-tags') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-purchase-tag bx-rotate-90' ></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Registered  Digital Tags</span>
                           @php
                           $tagCount = DB::table('family_tag_ids')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $tagCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('famory-tags') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-purchase-tag bx-rotate-90' ></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Sold Digital Tags</span>
                           @php
                           $tag_count = DB::table('tags_purchase_histories')->sum('tag_count');
                           @endphp
                           <h3 class="card-title mb-0">{{ $tag_count }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('product') }}">
                  <div class="card" >
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-sticker'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Total Physical Tags</span>
                           @php
                           $dataCount = DB::table('products')->count();
                           @endphp
                           <h3 class="card-title mb-0" >{{ $dataCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('admin.order.list') }}">
                  <div class="card" >
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-sticker'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Sold Physical Tags</span>
                           @php
                           $order_count = DB::table('orders')->whereIn('last_status_id',[2,3,4,7,8])->count();
                           @endphp
                           <h3 class="card-title mb-0" >{{ $order_count  }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="javascript:void(0)">
                  <div class="card" >
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-sticker'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Sold Legacy Albums</span>
                           @php
                           $album_count = DB::table('legacy_album_purchase_histories')->sum('album_count');
                           @endphp
                           <h3 class="card-title mb-0" >{{ $album_count }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            
            <!-- Card 4 -->
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('info-pages.index') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-file-blank'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Pages</span>
                           @php
                           $pageCount = DB::table('info_pages')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $pageCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('f-q-a') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-conversation'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">FAQ</span>
                           @php
                           $pageCount = DB::table('fqas')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $pageCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('tutorial') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-chalkboard'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Tutorial</span>
                           @php
                           $pageCount = DB::table('tutorials')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $pageCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('about') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-info-circle' ></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">About Us</span>
                           @php
                           $pageCount = DB::table('about_us')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $pageCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <!-- <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('ads-price') }}">
               <div class="card" >
                   <div class="card-body d-flex gap-3">
                       <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                               <i class='bx bx-money'></i>
                           </div>
                       </div>
                       <div>
                       <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Ads Price</span>
                      @php
                           $dataCount = DB::table('ads_prices')->count();
                       @endphp
                       <h3 class="card-title mb-0" >{{ $dataCount }}</h3>
                       </div>
                   </div>
               </div>
               </a>
               </div> -->
            
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('contacts') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-contact' ></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Contact us</span>
                           @php
                           $contactCount = DB::table('contacts')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $contactCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('reported_post') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-contact' ></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Reported Posts</span>
                           @php
                           $post_reported_count = DB::table('post_reports')->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $post_reported_count }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
               <a href="{{ route('get-delete-user-request') }}">
                  <div class="card">
                     <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                           <div class="avatar flex-shrink-0">
                              <i class='bx bxs-user-minus'></i>
                           </div>
                        </div>
                        <div>
                           <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Delete Account Requests</span>
                           @php
                           $susCount = DB::table('delete_account_request')->where('status',0)->count();
                           @endphp
                           <h3 class="card-title mb-0">{{ $susCount }}</h3>
                        </div>
                     </div>
                  </div>
               </a>
            </div>
            
         </div>
      </div>
   </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const ctx = document.getElementById('dashboardLineChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [
                {
                    label: 'Users',
                    data: @json($userData),
                    borderWidth: 3,
                    tension: 0.4
                },
                {
                    label: 'Posts',
                    data: @json($postData),
                    borderWidth: 3,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });


    

});
</script>
<script>
const ctx1 = document.getElementById('dashboardLineChart1').getContext('2d');

new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: @json($months),
        datasets: [
            {
                label: 'Tag',
                data: @json($tagData),
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderColor: '#28a745',
                borderWidth: 1,
                borderRadius: 6,                            // Rounded bars
                maxBarThickness: 40
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            },
            tooltip: {
                enabled: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0   // No decimal values
                }
            }
        }
    }
});
</script>


@endsection