<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PageInfoController;
use App\Http\Controllers\AdvertiserController;
use Illuminate\Http\Request;

Route::get('/', function () {
    // return redirect('login'); 
    return redirect('admin/login');
});


// Route::domain('partners.famoryapp.com')->group(function () {
Route::prefix('partners')->group(function () {
    Route::get('/login', [LoginController::class, 'viewLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});


// Route::domain('admin.famoryapp.com')->group(function () {
Route::prefix('admin')->group(function () {
    Route::get('/login', [LoginController::class, 'adminLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'adminStore'])->name('adminStore');
});

//advertiser login
// Route::get('/advertiser/login', [LoginController::class, 'viewLogin'])->name('login');
// Route::post('/advertiser/login', [LoginController::class, 'store']);



//admin login
// Route::get('/login', [LoginController::class, 'adminLogin'])->name('admin.login');
// Route::post('/login', [LoginController::class, 'adminStore'])->name('adminStore');

// Route::get('/register',[RegisterController::class,'view']);
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::resource('info-pages', PageInfoController::class);

// Route::domain('admin.famoryapp.com')->group(function () {
// Route::prefix('admin')->group(function () {
    Route::group(['middleware' => ['auth', 'isAdmin']], function () {


        Route::get('/', function () {
            return route('dashboard');
        });

        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        Route::get('/openworld', [AdminController::class, 'getOpenWorld'])->name('openworld');
        Route::get('/contacts', [AdminController::class, 'getContactUs'])->name('contacts');
        Route::get('/user/profile/{id}', [AdminController::class, 'profile'])->name('user.profile');
        Route::get('/get-users', [AdminController::class, 'getUsers'])->name('get-users');
        Route::get('/get-delete-user-request', [AdminController::class, 'getDeleteRequestUser'])->name('get-delete-user-request');
        Route::get('/user/create', [AdminController::class, 'createUser'])->name('user.create');
        Route::post('/user-store', [AdminController::class, 'storeUser'])->name('user.store');
        Route::get('/user/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
        Route::post('/user/{id}/update', [AdminController::class, 'updateUser'])->name('user.update');
        Route::post('/user/{id}/{dfgdg}', [AdminController::class, 'updateUserPassword'])->name('user.updateUserPassword');
        Route::post('/user/destroy', [AdminController::class, "destroyUser"])->name('user.softdelete');
        Route::post('/free-subscription/{id}', [AdminController::class, "freeSubscription"])->name('free-subscription');
        Route::get('/get-user-list', [AdminController::class, "getUserList"])->name('get-user-list');



        Route::get('/user/details/{id}', [AdminController::class, "viewUserDeatils"])->name('viewUserDeatils');
        Route::get('/user/all/post/{id}', [AdminController::class, "viewAllPosts"])->name('allPosts');
        Route::post('/approveAdvertiser', [AdminController::class, "approveAdvertiser"])->name("approveAdvertiser");

        Route::post('/delete-user', [AdminController::class, "softDeleteUser"])->name('softDeleteUser');
        Route::post('/reject-delete-acount-request', [AdminController::class, "rejectDeleteAccountRequest"])->name('rejectDeleteAccountRequest');

        Route::get('/famory-tags', [AdminController::class, 'famoryTags'])->name('famory-tags');
        Route::get('/famory/{id}/tags', [AdminController::class, 'editFamoryTag'])->name('editFamoryTag');
        Route::post('/famory-tag/{id}/update', [AdminController::class, 'updateFamoryTag'])->name('updateFamoryTag');
        Route::post('/famory-tag/destroy', [AdminController::class, "destroyFamoryTag"])->name('destroyFamoryTag');
        Route::get('/get-tag-listing', [AdminController::class, 'getTagListing'])->name('get-tag-listing');


        Route::get('/f-q-a', [AdminController::class, 'fqa'])->name('f-q-a');
        Route::get('/view-fqa', [AdminController::class, 'viewFQA'])->name('view-fqa');
        Route::post('/store-fqa', [AdminController::class, 'createFqa'])->name('store-fqa');
        Route::get('/edit/{id}/fqa', [AdminController::class, 'editFQA'])->name('editFQA');
        Route::post('/update/{id}/fqa', [AdminController::class, 'updateFqa'])->name('updateFqa');
        Route::post('/fqa/destroy/{id}', [AdminController::class, "destroyFqa"])->name('destroyFqa');


        Route::get('/tutorial', [AdminController::class, 'viewTutorial'])->name('tutorial');
        Route::get('/create-tutorial', [AdminController::class, 'createTutorial'])->name('create-tutorial');
        Route::post('/store-tutorial', [AdminController::class, 'storeTutorial'])->name('store-tutorial');
        Route::get('/edit-tutorial/{id}', [AdminController::class, 'editTutorial'])->name('edit-Tutorial');
        Route::post('/update-tutorial/{id}', [AdminController::class, 'updateTutorial'])->name('update-tutorial');
        Route::post('/destroy-tutorial/{id}', [AdminController::class, "destroyTutorial"])->name('destroy-tutorial');

        Route::get('/about', [AdminController::class, 'viewAbout'])->name('about');
        Route::get('/edit-about/{id}', [AdminController::class, 'editAbout'])->name('edit-about');
        Route::post('/update-about/{id}', [AdminController::class, 'updateabout'])->name('update-about');

        Route::get('/ads-price', [AdminController::class, 'viewAdsPrice'])->name('ads-price');
        Route::get('/edit-ads-price/{id}', [AdminController::class, 'editAdsPrice'])->name('edit-ads-price');
        Route::post('/update-ads-price/{id}', [AdminController::class, 'updateAdsPrice'])->name('update-ads-price');

        Route::get('/product', [AdminController::class, 'viewProduct'])->name('product');
        Route::get('/create-product', [AdminController::class, 'createProduct'])->name('create-product');
        Route::post('/store-product', [AdminController::class, 'storeProduct'])->name('store-product');
        Route::get('/edit-product/{id}', [AdminController::class, 'editProduct'])->name('edit-product');
        Route::post('/update-product/{id}', [AdminController::class, 'updateProduct'])->name('update-product');
        Route::post('/destroy-product/{id}', [AdminController::class, "destroyProduct"])->name('destroy-product');
        Route::get('/purchase-history', [AdminController::class, 'viewPurchaseHistory'])->name('purchase-history');
        Route::get('/get-user-detail-with-order/{id}', [AdminController::class, 'getUserDetailsWithOrders'])->name('get-user-detail-with-order');

        Route::get('/trusted-company', [AdminController::class, 'viewTrustedPartners'])->name('trusted-company');
        Route::get('/create-trusted-company', [AdminController::class, 'createTrustedCompany'])->name('create-trusted-company');
        Route::post('/store-trusted-company', [AdminController::class, 'storeTrustedCompany'])->name('store-trusted-company');
        Route::post('/destroy-trusted-company', [AdminController::class, "destroyTrustedCompany"])->name('destroy-trusted-company');
        Route::get('/edit-trusted-company/{id}', [AdminController::class, 'editTrustedCompany'])->name('edit-trusted-company');
        Route::post('/update-trusted-company/{id}', [AdminController::class, 'updateTrustedCompany'])->name('update-trusted-company');
        Route::post('/cancel-subscription/{id}', [AdminController::class, 'cancelSubscription'])->name('cancel-subscription');
        Route::post('/cancel-free-subscription/{id}', [AdminController::class, "cancelFreeSubscription"])->name('cancel-free-subscription');

        Route::get('/featured-company-payment', [AdminController::class, 'viewfeaturedCompanyPayment'])->name('featured-company-payment');
        Route::get('/create-featured-company-price', [AdminController::class, 'createfeaturedCompanyPayment'])->name('create-featured-company-price');
        Route::post('/store-featured-company-price', [AdminController::class, 'storefeaturedCompany'])->name('store-featured-company-price');
        Route::post('/destroy-featured-company-price/{id}', [AdminController::class, "destroyfeaturedCompany"])->name('destroy-featured-company-price');
        Route::get('/edit-featured-company-price/{id}', [AdminController::class, 'editfeaturedCompany'])->name('edit-featured-company-price');
        Route::post('/update-featured-company-price/{id}', [AdminController::class, 'updatefeaturedCompany'])->name('update-featured-company-price');


        Route::get('/subscription-setting', [AdminController::class, 'viewSubscriptionSetting'])->name('subscription-setting');
        Route::get('/create-subscription-setting', [AdminController::class, 'createSubscriptionSetting'])->name('create-subscription-setting');
        Route::post('/store-subscription-setting', [AdminController::class, 'storeSubscriptionSetting'])->name('store-subscription-setting');
        Route::post('/destroy-subscription-setting/{id}', [AdminController::class, "destroySubscriptionSetting"])->name('destroy-subscription-setting');
        Route::get('/edit-subscription-setting/{id}', [AdminController::class, 'editSubscriptionSetting'])->name('edit-subscription-setting');
        Route::post('/update-subscription-setting/{id}', [AdminController::class, 'updateSubscriptionSetting'])->name('update-subscription-setting');


        Route::get('/get-famory-tag-by-user', [AdminController::class, 'getFamoryTagByUser'])->name('get-famory-tag-by-user');

        Route::get('/view-oder-detail/{id}', [AdminController::class, 'viewOderDetail'])->name('view-oder-detail');
        Route::post('/update-order-status/{id}', [AdminController::class, 'updateOrderStaus'])->name('update-order-status');

        // 
        Route::get('/get-famory-tag-post/{id}', [AdminController::class, 'getFamoryTagPost'])->name('get-famory-tag-post');
        Route::get('/get-all-tag-user/{id}', [AdminController::class, 'getAllTagUser'])->name('get-all-tag-user');

        Route::post('/ban-user', [AdminController::class, 'banUser'])->name('banUser');


        // Ads 
        Route::get('/ads', [AdminController::class, 'getAllAds'])->name('ads');
        Route::post('/free-ads/{id}', [AdminController::class, "freeAds"])->name('free-ads');
        Route::post('/cancel-free-ads/{id}', [AdminController::class, "cancelFreeAds"])->name('cancel-free-ads');
        Route::post('/update-ad-status/{id}', [AdminController::class, "updateAdStatus"])->name('update-ad-status');

        //deceased 
        Route::get('/RIP-reports', [AdminController::class, 'deceasedReports'])->name('deceasedReports');
        Route::post('/RIP-reports-delete', [AdminController::class, 'deceasedReportDelete'])->name('deceasedReportDelete');

        //custom notifications
        Route::get("/custom-notification", [AdminController::class, 'customNotification'])->name('customNotification');
        Route::post("/custom-notification", [AdminController::class, 'sendCustomNotification'])->name('sendCustomNotification');

        Route::get("/post-details/{id}", [AdminController::class, 'viewPostDetails'])->name('postDetails');

        Route::post('/contacts-delete-selected', [AdminController::class, 'deleteSelectedContact'])->name('contactsDeleteSelected');
        Route::post('/famory-tag-delete-selected', [AdminController::class, 'famoryTagDeleteSelected'])->name('famoryTagDeleteSelected');


        Route::post('/open-world-post-hidden', [AdminController::class, 'openWorldPostHidden'])->name('openWorldPostHidden');

    });

// });

// for advertiser
// Route::domain('partners.famoryapp.com')->group(function () {
// Route::prefix('partners')->group(function () {
    Route::group(['middleware' => ['auth', 'isAdvertiser']], function () {
        Route::get('/', function () {
            return redirect()->route('advertiser/dashboard');
        });
        Route::get('/partner/dashboard', [AdvertiserController::class, 'dashboard'])->name('advertiser/dashboard');
        Route::get('/new-ad', [AdvertiserController::class, 'newAdView'])->name('newAdView');
        Route::post('/store-ad', [AdvertiserController::class, 'storeAd'])->name('storeAd');
        Route::post('/store-ad-payment', [AdvertiserController::class, 'storeAdPayment'])->name('storeAdPayment');
        Route::get('/selected-ad/{id}', [AdvertiserController::class, 'selectedAd'])->name('selectedAd');
        Route::get('/edit-ad/{id}', [AdvertiserController::class, 'editAd'])->name('editAd');
        Route::post('/update-ad/{id}', [AdvertiserController::class, 'updateAd'])->name('updateAd');
        Route::post('/delete-ad/{id}', [AdvertiserController::class, 'deleteAd'])->name('deleteAd');
        Route::get('/archieved-ads', [AdvertiserController::class, 'archievedAd'])->name('archievedAd');
        Route::post('/relist-ad/{id}', [AdvertiserController::class, 'relist'])->name('relist');
        Route::get('/contact-us', [AdvertiserController::class, 'contactUsView'])->name('contactUsView');
        Route::post('/contact-us', [AdvertiserController::class, 'contactUs'])->name('contactUs');
        Route::get('/my-account', [AdvertiserController::class, 'myAccount'])->name('myAccount');
        Route::get('/all-payments', [AdvertiserController::class, 'allPayments'])->name('allPayments');
        Route::get('/search-payment', [AdvertiserController::class, 'searchPayment'])->name('search-payment');
        Route::get('search-payment-my-account', [AdvertiserController::class, 'searchPaymentMyAccount'])->name('search-payment-my-account');
        Route::get('/add-new-card', [AdvertiserController::class, 'addNewCard'])->name('addNewCard');
        Route::post('/storeCardDetails', [AdvertiserController::class, 'storeCardDetails'])->name('storeCardDetails');
        Route::post('/deleteCard', [AdvertiserController::class, 'deleteCard'])->name('deleteCard');
        Route::get('/stickers', [AdvertiserController::class, 'viewstickers'])->name('stickers');
        Route::get('/selected-sticker/{id}', [AdvertiserController::class, 'selectedSticker'])->name('selectedSticker');
        Route::post('/purchase-sticker', [AdvertiserController::class, 'purchaseSticker'])->name('purchaseSticker');
        Route::post('/add-to-cart', [AdvertiserController::class, 'addToCart'])->name('add-to-cart');
        Route::get('/go-to-cart', [AdvertiserController::class, 'viewGoToCart'])->name('goToCart');
        Route::post('/remove-add-product', [AdvertiserController::class, 'removeAddProduct'])->name('remove-add-product');
        Route::post('/addCardDetails', [AdvertiserController::class, 'addCardDetails'])->name('addCardDetails');

        Route::get('/address', [AdvertiserController::class, 'viewAddress'])->name('address');
        Route::post('/create-address', [AdvertiserController::class, 'createAddress'])->name('createAddress');
        Route::get('/get-address', [AdvertiserController::class, 'getAddress'])->name('getAddress');
        Route::post('/edit-address', [AdvertiserController::class, 'editAddress'])->name('editAddress');
        Route::post('/update-address', [AdvertiserController::class, 'updateAddress'])->name('updateAddress');
        Route::post('/store-order', [AdvertiserController::class, 'storeOrder'])->name('storeOrder');
        Route::post('/purchase-tag', [AdvertiserController::class, 'purchaseTag'])->name('purchaseTag');
        Route::get('/purchasehistory', [AdvertiserController::class, 'purchaseHistory'])->name('purchasehistory');
        Route::get('/trustedpartners', [AdvertiserController::class, 'trustedPartners'])->name('trustedpartners');
        Route::get('/addNewPartner', [AdvertiserController::class, 'addNewPartner'])->name('addNewPartner');
        Route::post('/store-partner', [AdvertiserController::class, 'storePartner'])->name('storePartner');
        Route::post('/delete-partner/{id}', [AdvertiserController::class, 'destroyPartner'])->name('destroyPartner');
        Route::get('/edit-partner/{id}', [AdvertiserController::class, 'editPartner'])->name('editPartner');
        Route::post('/updatePartner', [AdvertiserController::class, 'updatePartner'])->name('updatePartner');
        Route::post('/storeFeaturePartnerPayment', [AdvertiserController::class, 'storeFeaturePartnerPayment'])->name('storeFeaturePartnerPayment');
        Route::post('/cancelSubscription', [AdvertiserController::class, 'cancelSubscription'])->name('cancelSubscription');
        Route::post('/adsSubscriptionCancel', [AdvertiserController::class, 'adsSubscriptionCancel'])->name('adsSubscriptionCancel');




        // Orders Routes
        Route::get('/orders', [AdvertiserController::class, 'ViewAllOrders'])->name('orders');

        // Invoice Routes
        Route::post('/invoice', [AdvertiserController::class, 'invoice'])->name('invoice');
    });

// });

Route::get('delete-account-request', function () {
    return view('admin.userInformation');
});

// Route::get('test', function () {
//     return phpinfo();
// });

// Route::get('test-1', function () {
//     return view('Email.AdsRenewalReminder');
// });


Route::get('/clear', function (Request $request) {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    dd($request->getHost());
});
