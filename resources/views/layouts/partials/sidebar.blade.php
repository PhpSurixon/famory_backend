<style>
    
   .bg-menu-theme .menu-sub > .menu-item.active > .menu-link:not(.menu-toggle):before {
    background-color: #1550AE !important;
    border: 2px solid #e7e7ff !important;
    outline: 3px solid #1150AE !important;
} 
</style>


<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/app_logo.png') }}" alt="">
            </span>
            <!--<span class="app-brand-text demo menu-text fw-bolder ms-2">Fam Cam</span>-->
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('get-users','user/create','user/*/edit','user/details/*') ? 'active' : '' }}">
            <a href="{{ route('get-users') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-user'></i>
                <div data-i18n="Layouts">Users</div>
            </a>
        </li>
        
        <li class="menu-item {{ request()->is('get-delete-user-request', 'get-delete-user-request') ? 'active' : '' }}">
            <a href="{{ route('get-delete-user-request') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-user-x'></i>
                <div data-i18n="Layouts">Delete Account Requests</div>
            </a>
        </li>
        
        <li class="menu-item {{ request()->is('RIP-reports', 'RIP-reports') ? 'active' : '' }}">
            <a href="{{ route('deceasedReports') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bx-x'></i>
                <div data-i18n="Layouts">RIP Reports</div>
            </a>
        </li>
        
        <li class="menu-item {{ request()->is('trusted-company', 'trusted-company','edit-trusted-company/*') ? 'active' : '' }}">
            <a href="{{ route('trusted-company') }}" class="menu-link">
                <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                <i class='menu-icon tf-icons bx bxs-user-circle' ></i>
                <div data-i18n="Layouts">Trusted Partner</div>
            </a>
        </li>
        

        <li class="menu-item {{ request()->is('openworld', 'openworld','post-details/*') ? 'active' : '' }}">
            <a href="{{ route('openworld') }}" class="menu-link">
                <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                <i class='menu-icon tf-icons bx bx-world' ></i>
                <div data-i18n="Layouts">Open World</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('famory-tags', 'famory-tags','famory/*/tags') ? 'active' : '' }}">
            <a href="{{ route('famory-tags') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-purchase-tag' ></i>
                <div data-i18n="Layouts">Tags</div>
            </a>
        </li>
        
        <li class="menu-item {{ request()->is('product', 'product') ? 'active' : '' }}">
            <a href="{{ route('product') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-bookmarks' ></i>
                <div data-i18n="Layouts">Famory Tags</div>
            </a>
        </li>
        

        
        <li class="menu-item {{ request()->is('ads') ? 'active' : '' }}">
            <a href="{{ route('ads') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bx-bar-chart' ></i>
                <div data-i18n="Layouts">Ads</div>
            </a>
        </li>
        
        
        <li class="menu-item {{ request()->is('purchase-history', 'purchase-history','view-oder-detail/*') ? 'active' : '' }}">
            <a href="{{ route('purchase-history') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Purchased History</div>
            </a>
        </li>
        
        
        <li class="menu-item {{request()->is('ads-price', 'ads-price','edit-ads-price/*') || request()->is('featured-company-payment', 
        'featured-company-payment','edit-featured-company-price/*','create-featured-company-price') || 
        request()->is('subscription-setting', 'subscription-setting','create-subscription-setting','edit-subscription-setting/*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div>Price Setting</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('ads-price', 'ads-price','edit-ads-price/*') ? 'active' : '' }}">
                    <a href="{{ route('ads-price') }}" class="menu-link">
                        <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                        <i class='menu-icon tf-icons bx bxs-report' ></i>
                        <div data-i18n="Layouts">Ads Price</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->is('featured-company-payment', 'featured-company-payment','edit-featured-company-price/*','create-featured-company-price') ? 'active' : '' }}">
                    <a href="{{ route('featured-company-payment') }}" class="menu-link">
                        <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                        <i class='menu-icon tf-icons bx bxs-report' ></i>
                        <div data-i18n="Layouts">Featured Partner Price</div>
                    </a>
                </li>
                
                 <li class="menu-item {{ request()->is('subscription-setting', 'subscription-setting','create-subscription-setting','edit-subscription-setting/*') ? 'active' : '' }}">
                    <a href="{{ route('subscription-setting') }}" class="menu-link">
                        <i class='menu-icon tf-icons bx bxs-cog'></i>
                        <div data-i18n="Layouts">Subscription Setting</div>
                    </a>
                </li>
                
            </ul>
        </li>
        
        
        
        
        
       
        
        
        
        <li class="menu-item {{request()->is('tutorial', 'tutorial','edit-tutorial/*','create-tutorial') || request()->is('about', 'about','edit-about/*') || 
        request()->is('f-q-a', 'f-q-a','view-fqa','edit/*/fqa') || request()->is('get-infopage','get-infopage','info-pages/create','info-pages/*/edit') ? 'active open':'' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div>Pages</div>
            </a>
            <ul class="menu-sub">

                <li class="menu-item {{ request()->is('tutorial', 'tutorial','edit-tutorial/*','create-tutorial') ? 'active' : '' }}">
                    <a href="{{ route('tutorial') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-layout"></i>
                        <div data-i18n="Layouts">Tutorial</div>
                    </a>
                </li>
                
                 <li class="menu-item {{ request()->is('about', 'about','edit-about/*') ? 'active' : '' }}">
                    <a href="{{ route('about') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-layout"></i>
                        <div data-i18n="Layouts">About Us</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->is('f-q-a', 'f-q-a','view-fqa','edit/*/fqa') ? 'active' : '' }}">
                    <a href="{{ route('f-q-a') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-layout"></i>
                        <div data-i18n="Layouts">F.A.Q</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->is('get-infopage', 'get-infopage','info-pages/create','info-pages/*/edit') ? 'active' : '' }}">
                    <a href="{{ route('info-pages.index') }}" class="menu-link">
                        <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                       <i class=' menu-icon tf-icons bx bxs-detail' ></i>
                        <div data-i18n="Layouts">Privacy Policy/ Terms and condition</div>
                    </a>
                </li>
            </ul>
        </li>
        
        
        
        <li class="menu-item {{ request()->is('custom-notification') ? 'active' : '' }}">
            <a href="{{ route('customNotification') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-message-dots'></i>
                <div data-i18n="Layouts">Send Notification</div>
            </a>
        </li>
        
        
        <li class="menu-item {{ request()->is('contacts', 'contacts') ? 'active' : '' }}">
            <a href="{{ route('contacts') }}" class="menu-link">
                <!--<i class="menu-icon tf-icons bx bx-layout"></i>-->
                <i class='menu-icon tf-icons bx bxs-contact' ></i>
                <div data-i18n="Layouts">Contact Us</div>
            </a>
        </li>
    </ul>
    
</aside>

