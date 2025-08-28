
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
            <div class="col-lg-12 mb-4 order-0">
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
                            <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Users</span>
                        @php
                            $userCount = DB::table('users')->count();
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
                            <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Open World</span>
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
                <a href="{{ route('contacts') }}">
                <div class="card">
                    <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                            <div class="avatar flex-shrink-0">
                                <i class='bx bxs-contact' ></i>
                            </div>
                        </div>
                        <div>
                        <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Contacts</span>
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
                <a href="{{ route('famory-tags') }}">
                <div class="card">
                    <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                            <div class="avatar flex-shrink-0">
                                <i class='bx bxs-purchase-tag bx-rotate-90' ></i>
                            </div>
                        </div>
                        <div>
                        <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Tag</span>
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
                <a href="{{ route('get-delete-user-request') }}">
                <div class="card">
                    <div class="card-body d-flex gap-3">
                        <div class="card-title d-flex align-items-start justify-content-between mb-0">
                            <div class="avatar flex-shrink-0">
                                <i class='bx bxs-user-minus'></i>
                            </div>
                        </div>
                        <div>
                        <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Suspended User</span>
                        @php
                            $susCount = DB::table('delete_account_request')->where('status',0)->count();
                        @endphp
                        <h3 class="card-title mb-0">{{ $susCount }}</h3>
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
                        <span class="fw-semibold d-block mb-1" style="font-size: 17px;">F.A.Q</span>
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
            
            <div class="col-12 col-md-6 col-lg-3 mb-4 box-height">
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
                        <span class="fw-semibold d-block mb-1" style="font-size: 17px;">Famory Tag</span>
                       @php
                            $dataCount = DB::table('products')->count();
                        @endphp
                        <h3 class="card-title mb-0" >{{ $dataCount }}</h3>
                        </div>
                    </div>
                </div>
                </a>
            </div>
            
        </div>
    </div>

        </div>

    </div>
    <script src="{{url('/')}}/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script>
        let cardColor, headingColor, axisColor, shadeColor, borderColor;

        cardColor = config.colors.white;
        headingColor = config.colors.headingColor;
        axisColor = config.colors.axisColor;
        borderColor = config.colors.borderColor;
        const chartOrderStatistics = document.querySelector('#orderStatisticsChart'),
            orderChartConfig = {
                chart: {
                    height: 165,
                    width: 130,
                    type: 'donut'
                },
                labels: ['Franchisees', 'Instructors', 'Parents', 'Students'],
                series: [12,13, 14, 15],
                colors: [config.colors.primary, config.colors.secondary, config.colors.info, config.colors.success],
                stroke: {
                    width: 5,
                    colors: cardColor
                },
                dataLabels: {
                    enabled: false,
                    formatter: function(val, opt) {
                        return parseInt(val) + '%';
                    }
                },
                legend: {
                    show: false
                },
                grid: {
                    padding: {
                        top: 0,
                        bottom: 0,
                        right: 15
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                value: {
                                    fontSize: '1.5rem',
                                    fontFamily: 'Public Sans',
                                    color: headingColor,
                                    offsetY: -15,
                                    formatter: function(val) {
                                        return parseInt(val) + '%';
                                    }
                                },
                                name: {
                                    offsetY: 20,
                                    fontFamily: 'Public Sans'
                                },
                                total: {
                                    show: true,
                                    fontSize: '0.8125rem',
                                    color: axisColor,
                                }
                            }
                        }
                    }
                }
            };
        if (typeof chartOrderStatistics !== undefined && chartOrderStatistics !== null) {
            const statisticsChart = new ApexCharts(chartOrderStatistics, orderChartConfig);
            statisticsChart.render();
        }
    </script>
@endsection
