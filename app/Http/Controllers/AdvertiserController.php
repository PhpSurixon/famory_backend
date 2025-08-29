<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


use Illuminate\Validation\Rule;


use App\Models\User;
use App\Models\Contact;
use App\Models\Post;
use App\Models\StripeDetail;
use App\Models\DeleteAccountRequest;
use App\Models\AssignUserGroup;
use App\Mail\UserSendRequestByAdmin;
use App\Mail\DeleteAccountRequestSendAdmin;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\FamilyTagId;
use App\Models\Advertisement;
use App\Services\StripeService;
use Illuminate\Support\Facades\Http;
use App\Models\AdsPrice;
use App\Models\Product;
use App\Models\StickerPurchase;
use App\Models\TransactionHistory;
use App\Models\AddToCart;
use App\Models\AdAddress;
use App\Models\TrustedPartners;
use App\Models\SubscribedPartner;
use App\Models\FeaturedCompanyPrice;
use App\Models\AdsSee;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailStartAdsSubscription;
use App\Services\UploadImage;

use Illuminate\Support\Facades\Log;
// require '/home3/famcamb/public_html/backend/vendor/vendor/autoload.php';
// use Google\Cloud\Storage\StorageClient;

use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Query\Builder;
class AdvertiserController extends Controller
{
    protected $StripeService;
    protected $storageClient;
    
    public function __construct(StripeService $StripeService,UploadImage $UploadImage)
    {
        $this->StripeService = $StripeService;
        $this->UploadImage = $UploadImage;
    }
    
    
    public function dashboard(){
        $authId = Auth::id();
        $ads = Advertisement::where(['user_id'=> $authId,'is_archieved'=>'0'])->orderBy('id','desc')->paginate(6);
        return view('advertiser.dashboard',compact('ads'));
    }
    
    public function newAdView(){
        $currentDate = now()->format('Y-m-d');
        $price = AdsPrice::first();
        $current_user = Auth::user();
        if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        $counts = Advertisement::where('user_id',$current_user->id)->count();
        return view('advertiser.newAd',compact('currentDate','price','cards','counts'));
    }
    
    public function storeAd(Request $request){
        
            // Custom validation function for ZIP code
            $validator = Validator::make($request->all(), [
                'ad_name' => 'required|max:255',
                'start_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $date = Carbon::parse($value)->startOfDay();
                        $today = Carbon::now()->startOfDay();
                
                        if ($date->lessThan($today)) {
                            $fail('The ' . $attribute . ' cannot be earlier than today.');
                        }
                    },
                ],
                // 'expiration' => [
                //     'required',
                //     'date',
                //     function ($attribute, $value, $fail) use ($request) {
                //         $startDate = Carbon::parse($request->input('start_date'));
                //         $expirationDate = Carbon::parse($value);
                        
                //         if ($expirationDate->lt($startDate)) {
                //             $fail('The ' . $attribute . ' must be a date after the start date.');
                //         }
                //     },
                // ],
                "action_button_link" => "required|url",
                "action_button_text" => 'required|max:20',
                "full_screen_image" => "required|image",
                "banner_image" => "required|image",
                "zip_code" => "required",
            ]);

    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        
        try {
            
            // Create User
            DB::beginTransaction();
            $current_user = Auth::user();
            $ad = new Advertisement;
            $ad->user_id = Auth::id();
            $ad->ad_name = $request->ad_name;
            $ad->start_date = $request->start_date;
            // $ad->expiration = $request->expiration;
            $ad->zip_code = $request->zip_code;
            $ad->action_button_text = $request->action_button_text;
            $ad->action_button_link = $request->action_button_link;
            $ad->is_national = $request->is_national ? true : false;
            
         
    
            if ($request->banner_image) {
                $res = $this->UploadImage->saveMedia($request->banner_image, Auth::id());
                $ad->banner_image = $res;
            }
              
            if ($request->full_screen_image) {
                $res = $this->UploadImage->saveMedia($request->full_screen_image, Auth::id());
                $ad->full_screen_image = $res;
            }
            $ad->save();
            
            DB::commit();
            return response()->json(['message' => "Ad created successfully", 'status' => 'success', 'data' => $ad], 200);
        } catch (\Exception $e) {
            
            DB::rollBack();
            
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
   
    }
    
    public function storeAdPayment(Request $request){
        $validator = Validator::make($request->all(), [
            'ads_id' => 'required',
            "amount" => 'required',
            "card_number" => 'required',
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        
        try {
            DB::beginTransaction();
            $current_user = Auth::user();
            
            $count = Advertisement::where('user_id',Auth::id())->orderBy('created_at', 'asc')->count();
            if($count == 1){
                $renewDate =  Carbon::parse($request->renew_date);
                $update = Advertisement::where('id', $request->ads_id)->update(['payment_status' => '1','payment_intent_id' => null, 'charge_id' => null,'renew_date' => $renewDate,'card_id'=>$request->card]);
                DB::commit();
            
                $adsData = Advertisement::find($request->ads_id);
                // Send Mail
                if($current_user){
                    Mail::to($current_user->email)->send(new SendMailStartAdsSubscription($adsData,$current_user));            
                }
                return response()->json(['message' => "You are being given a free subscription to ADs for 90 days", 'status' => 'success', 'data' => $adsData], 200);
                
                
                
            }else{
                $stripe_res = $this->StripeService->stripePaymentIntent($request->amount, $request->card, $current_user->stripe_customer_id);
                if($stripe_res['res'] == false){
                    return response()->json(['message' => $stripe_res['msg'], 'status' => 'failed', 'data' => []], 500);
                }
                $payment_intent_id = $stripe_res['payment_intent_id'];
                $charge_id = $stripe_res['charge_id'];  
                $data = new TransactionHistory;
                $data->user_id = $current_user->id;
                $data->ads_id = $request->ads_id;
                $data->source = $request->card_number;
                $data->source_type = 'card';
                $data->type = 'debit';
                $data->amount = $request->amount;
                $data->save();
                // $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $request->start_date)->format('Y-m-d');
                $renewDate = Carbon::parse($request->renew_date);
                $update = Advertisement::where('id', $request->ads_id)->update(['payment_status' => '1','payment_intent_id' => $payment_intent_id, 'charge_id' => $charge_id,'renew_date' => $renewDate,'card_id'=>$request->card]);
            
                DB::commit();
                $adsData = Advertisement::find($request->ads_id);
                // Send Mail
                if($current_user){
                    Mail::to($current_user->email)->send(new SendMailStartAdsSubscription($adsData,$current_user));            
                }
                return response()->json(['message' => "Payment has been done successfully", 'status' => 'success', 'data' => $data], 200);
            }

            DB::commit();
            
            $adsData = Advertisement::find($request->ads_id);
            // Send Mail
            if($current_user){
                Mail::to($current_user->email)->send(new SendMailStartAdsSubscription($adsData,$current_user));            
            }
            return response()->json(['message' => "Payment has been done successfully", 'status' => 'success', 'data' => $data], 200);
        
            
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }
    
     public function adsSubscriptionCancel(Request $request){
        $validator = Validator::make($request->all(), [
            "id" => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } 
        
         try {
            $ads = Advertisement::find($request->id);
            if($ads->cancel_status == 0 ){
                Advertisement::where('id', $request->id)->update(['cancel_status' => '1' ]);
                return response()->json(['message'=>"Your Cancellation Request Saved Successfully",'status'=>"success"],200);
            }else{
                return response()->json(['message'=>"Your Subscription Cancellation Request is Pending",'status'=>'error'],200);   
            }
            
            
            
        } catch (\Exception $e) {
                
        DB::rollBack();
        return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
     }
    
    
    public function deleteAd($id){
        $ad = Advertisement::find($id);
        $ad->is_archieved = 1;
        $ad->save();
        return redirect()->route('advertiser/dashboard')->with('success', 'Ad delete successfully');
    }
    
    public function selectedAd($id){
        $ad = Advertisement::find($id);
        $fullScreenImage = $ad->full_screen_image;
        $bannerImage = $ad->banner_image;
        $fullScreenImageDimensions = $this->getImageDimensions($fullScreenImage);
        $bannerImageDimensions = $this->getImageDimensions($bannerImage);
        $currentDate = now()->format('Y-m-d');
        $price = AdsPrice::first();
        $current_user = Auth::user();
        
        $firstAd = Advertisement::where('user_id',Auth::id())->count();
        $ad_stats = AdsSee::where('ads_id',$ad->id)->first();
 
        
         if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        return view('advertiser.selectedAd', [
            'ad' => $ad,
            'fullScreenImageDimensions' => $fullScreenImageDimensions,
            'bannerImageDimensions' => $bannerImageDimensions,
            'currentDate' => $currentDate,
            'price' => $price,
            'cards' => $cards,
            'ad_stats'=>$ad_stats,
            'firstAd' => $firstAd,
        ]);
    }
    
    public function getImageDimensions($url)
    {
            // Fetch image data
        $response = Http::get($url);
        
        // Check if the request was successful
        if ($response->successful()) {
            // Save image temporarily
            $tempPath = sys_get_temp_dir() . '/' . basename($url);
            file_put_contents($tempPath, $response->body());

            // Get image size
            $dimensions = getimagesize($tempPath);
            
            // Remove temporary file
            unlink($tempPath);

            return [
                'width' => $dimensions[0],
                'height' => $dimensions[1]
            ];
        }
        
        return ['width' => 'N/A','height' => 'N/A'];
    }
    
    
    
    public function editAd($id){
        $ad = Advertisement::find($id);
        return view('advertiser.editAd',compact('ad'));
    }
    
    
    public function updateAd(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'ad_name' => 'required|max:255',
            'start_date' => [
                'required',
                // 'date',
                // function ($attribute, $value, $fail) {
                //     if (Carbon::parse($value)->isPast()) {
                //         $fail('The ' . $attribute . 'cannot be earlier than the current date.');
                //     }
                // },
            ],
            // 'expiration' => [
            //     'required',
            //     'date',
            //     function ($attribute, $value, $fail) use ($request) {
            //         $startDate = Carbon::parse($request->input('start_date'));
            //         $expirationDate = Carbon::parse($value);
                    
            //         if ($expirationDate->lt($startDate)) {
            //             $fail('The ' . $attribute . ' must be a date after the start date.');
            //         }
            //     },
            // ],
            
            'zip_code' => 'required',
            "action_button_text" => 'required|max:20',
            "action_button_link"=> "required",
        ]);
    
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }   
        
        try {
            // Create User
            $ad = Advertisement::find($id);
            $ad->user_id = Auth::id();
            $ad->ad_name = $request->ad_name;
            $ad->start_date = $request->start_date;
            // $ad->expiration = $request->expiration;
            $ad->zip_code = $request->zip_code;
            $ad->action_button_text = $request->action_button_text;
            $ad->action_button_link = $request->action_button_link;
            $ad->is_national = $request->is_national ? true : false;
            
         
    
            if ($request->banner_image) {
                $res = $this->UploadImage->saveMedia($request->banner_image, Auth::id());
                $ad->banner_image = $res;
            }
              
            if ($request->full_screen_image) {
                $res = $this->UploadImage->saveMedia($request->full_screen_image, Auth::id());
                $ad->full_screen_image = $res;
            }
            $ad->save();
            
           return redirect()->route('selectedAd', ['id' => $ad->id])->with('success', 'Ad updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage() );
        }
   
    }
    
    public function archievedAd(){
        $authId = Auth::id();
        $ads = Advertisement::where(['user_id'=> $authId,'is_archieved'=>'1'])->orderBy('id','desc')->paginate(10);
        return view('advertiser.archievedAd',compact('ads'));
    }
    
    public function relist($id){
         $ad = Advertisement::find($id);
         $ad->is_archieved = 0;
         $ad->save();
         return redirect()->route('archievedAd');
    }
    
    public function contactUsView(){
        return view('advertiser.contactUs');
    }
    
    public function contactUs(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'email' => 'required',
                'phone' => 'nullable|numeric|digits:10',
                'label' => 'required'
            ]);
            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }   
        
            $contact = new Contact();
            $contact->name = $request->name ?? Auth::user()->first_name;
            $contact->phone = $request->phone;
            $contact->email = $request->email;
            $contact->message = $request->label;
            $contact->user_id = Auth::user()->id;
            $contact->save();
            if($contact){
                return redirect()->route('contactUs')->with('success','Thank you for contacting us. Famory will review your request and get back to you as soon as we can.');
            }
        } catch (\Exception $e) {
            return redirect()->route('contactUs')->with('error',$e->getMessage());
        }
            
    }
    
    public function myAccount(){
        $user = User::with('stripeDetails')->find(Auth::id());
        $current_user = Auth::user();
         if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        
        $ads = Advertisement::where('user_id',$current_user->id)->pluck('id');
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Total views and clicks for all ads of the user in the current month
        $totalViews = AdsSee::whereIn('ads_id', $ads)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('view');
        $totalClicks = AdsSee::whereIn('ads_id', $ads)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('click_to_open');
        $totalWebsite = AdsSee::whereIn('ads_id', $ads)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('click_to_website');
        
        $transactionHistory = TransactionHistory::where('user_id',Auth::id())->where('ads_id','!=','null')->with('ad')->orderBy('created_at','Desc')->limit(3)->get();
        
        
        return view('advertiser.myAccount',compact('user','transactionHistory','cards','totalViews','totalClicks','totalWebsite'));
    }
    public function allPayments(){
        $transactionData = TransactionHistory::where('user_id',Auth::id())->where('ads_id','!=','null')->with('ad')->orderBy('id','Desc')->paginate(5);
        return view('advertiser.allPayments',compact('transactionData'));
    }
    
    
    public function searchPayment(Request $request){
        $search = "%".$request->search."%";
        $transactionData = TransactionHistory::where('user_id',Auth::id())->where('ads_id','!=','null')->whereHas('ad', function ($query) use ($search) {
            $query->where('ad_name','like',$search);
        })->with('ad')->orderBy('id','Desc')->get();
        
         $html ='<table class="table" cellspacing="5">
              <thead>
                <tr>
                  <th scope="col">Ad Name</th>
                  <th scope="col">Date</th>
                  <th scope="col">Amount</th>
                  <th scope="col">Expiration</th>
                  <th scope="col">&nbsp;</th>
                </tr>
              </thead>
              <tbody>';
               if($transactionData->count() > 0){
                    foreach($transactionData as $data){

                        $html.="<tr><td>".$data->ad->ad_name."</td>
                            <td>".date('m/d/Y',strtotime($data->ad->start_date))."</td>
                            <td>$".number_format((float)$data->amount, 2, '.', '')."</td>
                            <td>".date('m/d/Y',strtotime($data->ad->expiration))."</td>
                            <td>
                                <i class='fas fa-chevron-right'></i>
                            </td>
                        </tr>";
                    }
               }else{
                $html .='<tr>
                        <td colspan="5" style="text-align: center;">No Transaction History</td>
                    </tr>';
               }
              $html.="</tbody>
            </table>
            
            ";
            
            return response()->json(['message' => "Successfully", 'status' => 'success', 'data' => $html], 200);
        
    }
    
    
    
    public function searchPaymentMyAccount(Request $request){
        $search = "%".$request->search."%";
        $transactionData = TransactionHistory::where('user_id',Auth::id())->where('ads_id','!=','null')->whereHas('ad', function ($query) use ($search) {
            $query->where('ad_name','like',$search);
        })->with('ad')->orderBy('created_at','Desc')->limit(3)->get();
        
         $html ='<table class="table" cellspacing="5">
              <thead>
                <tr>
                  <th scope="col">Ad Name</th>
                  <th scope="col">Date</th>
                  <th scope="col">Amount</th>
                  <th scope="col">Expiration</th>
                  <th scope="col">&nbsp;</th>
                </tr>
              </thead>
              <tbody>';
               if($transactionData->count() > 0){
                    foreach($transactionData as $data){

                        $html.="<tr><td>".$data->ad->ad_name."</td>
                            <td>".date('m/d/Y',strtotime($data->ad->start_date))."</td>
                            <td>$".number_format((float)$data->amount, 2, '.', '')."</td>
                            <td>".date('m/d/Y',strtotime($data->ad->expiration))."</td>
                            <td>
                                <i class='fas fa-chevron-right'></i>
                            </td>
                        </tr>";
                    }
               }else{
                $html .='<tr>
                        <td colspan="5" style="text-align: center;">No Transaction History</td>
                    </tr>';
               }
              $html.="</tbody>
            </table>
            
            ";
            
            return response()->json(['message' => "Successfully", 'status' => 'success', 'data' => $html], 200);
        
    }
    
    
    public function addNewCard(Request $request){
        
        return view('advertiser.addNewCard');
    }
    
    
    public function storeCardDetails(Request $request){
        try{
    
            $validator = Validator::make($request->all(), [
                'stripeToken' => 'required',
                'card_name' => 'required',
                // 'card_number' => 'required',
                // 'security_code' => 'required',
                // 'expiration_date' => 'required',
            ]);
    
            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }   
        
            $current_user = User::find(Auth::id());
            $stripe_customer_id = $current_user->stripe_customer_id ?? null;
            
            //create customer
            if(empty($stripe_customer_id)){
                $stripe_res = $this->StripeService->createStripeCustomer($current_user);

                if($stripe_res['res'] == false){
                    return back()->with('error',$stripe_res['msg']);
                }

                $stripe_customer_id = $stripe_res['stripe_customer_id'];

                $current_user->stripe_customer_id = $stripe_customer_id;
                $current_user->save();
            }

           
            // save card on stripe customer
            $res = $this->StripeService->addCardToCustomer($stripe_customer_id, $request->stripeToken);
            if($res['res'] == false){
                return back()->with('error',$res['msg']);
            }

            // save card in db

            $card = new StripeDetail;
            $card->user_id = $current_user->id;
            $card->stripe_card_id = $res['card_id'];
            $card->res_detail = substr($res['card_data'], 18);
            $card->save();
            
            return back()->with('success','Card added successfully');
            
        }catch(\Exception $e){
          return back()->with('error', $e->getMessage() );
       }
    }
    
    
    
    public function addCardDetails(Request $request){
        try{
    
            $validator = Validator::make($request->all(), [
                'stripeToken' => 'required',
                'card_name' => 'required',
            ]);
    
            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }   
        
            $current_user = User::find(Auth::id());
            $stripe_customer_id = $current_user->stripe_customer_id ?? null;
            
            //create customer
            if(empty($stripe_customer_id)){
                $stripe_res = $this->StripeService->createStripeCustomer($current_user);

                if($stripe_res['res'] == false){
                    return back()->with('error',$stripe_res['msg']);
                }

                $stripe_customer_id = $stripe_res['stripe_customer_id'];

                $current_user->stripe_customer_id = $stripe_customer_id;
                $current_user->save();
            }

           
            // save card on stripe customer
            $res = $this->StripeService->addCardToCustomer($stripe_customer_id, $request->stripeToken);
            if($res['res'] == false){
                return back()->with('error',$res['msg']);
            }

            // save card in db

            $card = new StripeDetail;
            $card->user_id = $current_user->id;
            $card->stripe_card_id = $res['card_id'];
            $card->res_detail = substr($res['card_data'], 18);
            $card->save();
            
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
            
            if(!$card){
                return response()->json(['message' => "Not added card", 'status' => 'success', 'data' => null], 400);
            }   
            return response()->json(['message' => "Card store successfully", 'status' => 'success', 'data' => $cards], 200);
        
            
        }catch(\Exception $e){
          return back()->with('error', $e->getMessage() );
       }
    }
    
    
    
    public function deleteCard(Request $request){
        try{
          if(empty($request->card_id)){
              return back()->with('error', 'Please provide card id');
          }

          $res = $this->StripeService->deleteCardFromCustomer($request->card_id);
          if($res['res'] == false){
              return back()->with('error', $res['msg']);
          }
            $card = StripeDetail::where('stripe_card_id',$request->card_id)->delete();
            return back()->with('success', $res['msg']);

        }catch(\Exception $exception){
            return back()->with('error', $exception->getMessage());
       } 
        
    }
    
    public function viewstickers(Request $request){
        $getStickers = Product::where('count','>','0')->orderBy('is_favourite','Desc')->orderBy('id','Desc')->paginate(5);
        return view('advertiser.Sticker',compact('getStickers'));
    }
    
    public function selectedSticker(Request $request,$id){
        $current_user = Auth::user();
        $data = Product::find($id);
        if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        $isExist = AddToCart::where('user_id', $current_user->id)->where('product_id', $id)->exists();
        return view('advertiser.selectedSticker',compact('data','cards','isExist'));
    }
    
    public function purchaseSticker(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            "amount" => 'required',
            "card_number" => 'required',
            "quantity" => 'required',
            'card_id' => 'required',
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        try {

            DB::beginTransaction();
            $current_user = Auth::user();
            $order_id = uniqid('', true);
            $findOrderid = StickerPurchase::where('order_id',$order_id)->get();
            
            if($findOrderid == 0){
                $stripe_res = $this->StripeService->stripePaymentIntent($request->amount, $request->card_id, $current_user->stripe_customer_id);
                if($stripe_res['res'] == false){
                    
                     return response()->json(['message' => $stripe_res['msg'], 'status' => 'failed', 'data' => []], 500);
                }
                $payment_intent_id = $stripe_res['payment_intent_id'];
                $charge_id = $stripe_res['charge_id'];
                
                
                
                $save = new StickerPurchase();
                $save->user_id = $current_user->id;
                $save->order_id = $order_id;
                $save->product_id = $request->product_id;
                $save->quantity = $request->quantity;
                $save->payment_intent_id = $payment_intent_id;
                $save->charge_id = $charge_id;
                $save->save();
    
                $data = new TransactionHistory;
                $data->user_id = $current_user->id;
                $data->product_id = $request->product_id;
                $data->sticker_purchase_id = $save->id;
                $data->source = $request->card_number;
                $data->source_type = 'card';
                $data->type = 'debit';
                $data->amount = $request->amount;
                $data->save();
                
                $update = Product::where('id', $request->product_id)->first();
                
                // Check if the product exists before updating
                if ($update) {
                    $update->total_purchased = $update->total_purchased + $request->quantity;
                    $update->count = $update->count - $request->quantity;
                    $update->save();
                }
                
                DB::commit();
        
                return response()->json(['message' => "Sticker purchase has been successful", 'status' => 'success', 'data' => $data], 200);
            }else{
                return response()->json(['message' => "Order Id Already Present in the DB",'status'=>'error'],200);
            }
            
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }
    
    public function addToCart(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        try {
            DB::beginTransaction();
            
            $current_user = Auth::user();
            
            $save = new AddToCart();
            $save->user_id = $current_user->id;
            $save->product_id = $request->product_id;
            $save->quantity = 1;
            $save->save();

            DB::commit();
    
            return response()->json(['message' => "Famory Tag successfully added to cart", 'status' => 'success', 'data' => $save], 200);
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }
    
    public function viewGoToCart(){
        $datas = AddToCart::where('user_id', Auth::id())
        ->whereHas('product', function ($query) {
            $query->where('count', '>', 0); // Filter out products with count <= 0
        })
        ->with('product')
        ->orderBy('id', 'desc')
        ->get();
        return view('advertiser.goToCart',compact('datas'));
    }
    
    public function removeAddProduct(Request $request){
         $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        try {
            
            $current_user = Auth::user();
            $data = AddToCart::find($request->id);
            if(!$data){
                return response()->json(['message' => "Something went wrong, please try again.", 'status' => 'success', 'data' => null], 422);
            }
            $data->delete();
            return response()->json(['message' => "Famory Tag remove successfully", 'status' => 'success', 'data' => null], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }

    public function viewAddress(){
        $current_user = Auth::user();
        if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        // $orderData = session('order_data');
        $orderData = Session::get('order_data');
        return view('advertiser.address',compact('cards','orderData'));
    }
    
    
    public function createAddress(Request $request){
    $stateZipCodes = [
        'Alabama' => ['35004', '36925'],
        'Alaska' => ['99501', '99950'],
        'Arizona' => ['85001', '86556'],
        'Arkansas' => ['71601', '72959'],
        'California' => ['90001', '96162'],
        'Colorado' => ['80001', '81658'],
        'Connecticut' => ['06101', '06928'],
        'Delaware' => ['19701', '19980'],
        'Florida' => ['33101', '34997'],
        'Georgia' => ['30301', '39901'],
        'Hawaii' => ['96801', '96898'],
        'Idaho' => ['83201', '83877'],
        'Illinois' => ['60601', '62999'],
        'Indiana' => ['46001', '47997'],
        'Iowa' => ['50001', '52809'],
        'Kansas' => ['66002', '67954'],
        'Kentucky' => ['40003', '42788'],
        'Louisiana' => ['70112', '71497'],
        'Maine' => ['03901', '04992'],
        'Maryland' => ['21201', '21930'],
        'Massachusetts' => ['01001', '02791'],
        'Michigan' => ['48001', '49971'],
        'Minnesota' => ['55001', '56763'],
        'Mississippi' => ['38601', '39776'],
        'Missouri' => ['63001', '65899'],
        'Montana' => ['59001', '59937'],
        'Nebraska' => ['68001', '69367'],
        'Nevada' => ['89501', '89795'],
        'New Hampshire' => ['03301', '03897'],
        'New Jersey' => ['07001', '08989'],
        'New Mexico' => ['87501', '88439'],
        'New York' => ['10001', '14925'],
        'North Carolina' => ['27501', '28909'],
        'North Dakota' => ['58001', '58856'],
        'Ohio' => ['43001', '45999'],
        'Oklahoma' => ['73001', '74966'],
        'Oregon' => ['97001', '97920'],
        'Pennsylvania' => ['15001', '19640'],
        'Rhode Island' => ['02801', '02940'],
        'South Carolina' => ['29001', '29945'],
        'South Dakota' => ['57001', '57799'],
        'Tennessee' => ['37010', '38589'],
        'Texas' => ['73301', '79999'],
        'Utah' => ['84001', '84791'],
        'Vermont' => ['05601', '05907'],
        'Virginia' => ['20101', '24658'],
        'Washington' => ['98001', '99403'],
        'West Virginia' => ['24701', '26886'],
        'Wisconsin' => ['53201', '54990'],
        'Wyoming' => ['82001', '83128'],
    ];
        
        $validStates = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
            'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
            'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
            'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma',
            'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee',
            'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];
        
    
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => ['required', 'string', 'regex:/^\+?1?\d{9,15}$/'],
            'house_number' => 'required',
            'road_name' => 'required',
            'state' => [
                'required',
                'string',
                Rule::in($validStates),
            ],
            'zip_code' => [
                'required',
                'regex:/^\d{5}(-\d{4})?$/',
                function ($attribute, $value, $fail) use ($request, $stateZipCodes) {
                    $state = $request->state;
                    if(array_key_exists($state,$stateZipCodes)){
                        if(!($value >= $stateZipCodes[$state][0] && $value <= $stateZipCodes[$state][1])){
                            $fail('The ZIP code does not match the selected state.');
                        }
                    }else{
                        $fail('Please Select Valid State');
                    }
                },
            ],
        ],[
            'state'=>'Please Enter Valid State Name'    
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        try {
            DB::beginTransaction();
            
            $current_user = Auth::user();
            $save = new AdAddress();
            $save->user_id = $current_user->id;
            $save->name = $request->name;
            $save->phone_number = $request->phone_number;
            $save->house_number = $request->house_number;
            $save->road_name = $request->road_name;
            $save->state = $request->state;
            $save->zip_code = $request->zip_code;
            $save->save();

            DB::commit();
    
            return response()->json(['message' => "Address saved successfully", 'status' => 'success', 'data' => $save], 200);
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }  
    }
    
    public function getAddress(Request $request){
        $current_user = Auth::id();
        $getAddress = AdAddress::where('user_id',$current_user)->orderBy('id','DESC')->get();
        if(!$getAddress){
            return response()->json(['message' => "Not get any address", 'status' => 'success', 'data' => null], 200);
        }
        return response()->json(['message' => "get address successfully", 'status' => 'success', 'data' => $getAddress], 200);
    }
    
    public function editAddress(Request $request){
        $id = $request->addressId;
        $current_user = Auth::id();
        $address = AdAddress::find($id);
        if(!$address){
            return response()->json(['message'=>'No Record Found','status'=>'success','data'=>null],200);
        }
        return response()->json(['message'=>"Get Address Successfullly",'status'=>'success','data'=>$address],200);
    }
    
    
    public function updateAddress(Request $request){
        $stateZipCodes = [
        'Alabama' => ['35004', '36925'],
        'Alaska' => ['99501', '99950'],
        'Arizona' => ['85001', '86556'],
        'Arkansas' => ['71601', '72959'],
        'California' => ['90001', '96162'],
        'Colorado' => ['80001', '81658'],
        'Connecticut' => ['06101', '06928'],
        'Delaware' => ['19701', '19980'],
        'Florida' => ['33101', '34997'],
        'Georgia' => ['30301', '39901'],
        'Hawaii' => ['96801', '96898'],
        'Idaho' => ['83201', '83877'],
        'Illinois' => ['60601', '62999'],
        'Indiana' => ['46001', '47997'],
        'Iowa' => ['50001', '52809'],
        'Kansas' => ['66002', '67954'],
        'Kentucky' => ['40003', '42788'],
        'Louisiana' => ['70112', '71497'],
        'Maine' => ['03901', '04992'],
        'Maryland' => ['21201', '21930'],
        'Massachusetts' => ['01001', '02791'],
        'Michigan' => ['48001', '49971'],
        'Minnesota' => ['55001', '56763'],
        'Mississippi' => ['38601', '39776'],
        'Missouri' => ['63001', '65899'],
        'Montana' => ['59001', '59937'],
        'Nebraska' => ['68001', '69367'],
        'Nevada' => ['89501', '89795'],
        'New Hampshire' => ['03301', '03897'],
        'New Jersey' => ['07001', '08989'],
        'New Mexico' => ['87501', '88439'],
        'New York' => ['10001', '14925'],
        'North Carolina' => ['27501', '28909'],
        'North Dakota' => ['58001', '58856'],
        'Ohio' => ['43001', '45999'],
        'Oklahoma' => ['73001', '74966'],
        'Oregon' => ['97001', '97920'],
        'Pennsylvania' => ['15001', '19640'],
        'Rhode Island' => ['02801', '02940'],
        'South Carolina' => ['29001', '29945'],
        'South Dakota' => ['57001', '57799'],
        'Tennessee' => ['37010', '38589'],
        'Texas' => ['73301', '79999'],
        'Utah' => ['84001', '84791'],
        'Vermont' => ['05601', '05907'],
        'Virginia' => ['20101', '24658'],
        'Washington' => ['98001', '99403'],
        'West Virginia' => ['24701', '26886'],
        'Wisconsin' => ['53201', '54990'],
        'Wyoming' => ['82001', '83128'],
    ];
        
        $validStates = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
            'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
            'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
            'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma',
            'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee',
            'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];
        
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => ['required', 'string', 'regex:/^\+?1?\d{9,15}$/'],
            'house_number' => 'required',
            'road_name' => 'required',
            'state' => [
                'required',
                'string',
                Rule::in($validStates),
            ],
            'zip_code' => [
                'required',
                'regex:/^\d{5}(-\d{4})?$/',
                function ($attribute, $value, $fail) use ($request, $stateZipCodes) {
                    $state = $request->state;

                    if(array_key_exists($state,$stateZipCodes)){
                        if(!($value >= $stateZipCodes[$state][0] && $value <= $stateZipCodes[$state][1])){
                            $fail('The ZIP code does not match the selected state.');
                        }
                    }else{
                        $fail('Please Select Valid State');
                    }
                },
            ],
        ],[
            'state'=>'Please Enter Valid State Name'    
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }   
        try {
            DB::beginTransaction();
            
            $current_user = Auth::user();
            $save = AdAddress::find($request->id);
            $save->user_id = $current_user->id;
            $save->name = $request->name;
            $save->phone_number = $request->phone_number;
            $save->house_number = $request->house_number;
            $save->road_name = $request->road_name;
            $save->state = $request->state;
            $save->zip_code = $request->zip_code;
            $save->save();

            DB::commit();
    
            return response()->json(['message' => "Address saved successfully", 'status' => 'success', 'data' => $save], 200);
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }
    
    
    public function storeOrder(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'total_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.cart_id' => 'required|integer',
            'items.*.quantity' => 'required|integer',
            'items.*amount' => 'required|integer',
            'item_count' => 'required|integer'
        ]);
        
        Session::put('order_data', $request->all());
        foreach($request->items as $item) {
            AddToCart::where('id', $item['cart_id'])
                ->update(['quantity' => $item['quantity'], 'amount' => $item['amount']]);
        }

        

        // Process the data (e.g., store order in the database)

        return response()->json(['message' => 'Order stored successfully.']);
    }
    
    public function purchaseTag(Request $request) {
        $validator = Validator::make($request->all(), [
            "card_number" => 'required',
            "quantity" => 'required',
            'card_id' => 'required',
        ]);
        
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $oderDatas = Session::get('order_data');
    
            if ($oderDatas && isset($oderDatas['items'])) {
                DB::beginTransaction();
                
                foreach ($oderDatas['items'] as $data) {
                    $getaData = AddToCart::where('id', $data['cart_id'])->first();
                    $product = Product::where('id', $getaData->product_id)->first();
                    if($product){
                        if($product->count <= 0){
                            return response()->json(['message' => 'This Famory tag '.$product->name.' is sold out.', 'status' => 'failed'], 200);
                        }
                    }
                    
                    
                    if (!$getaData) {
                        throw new \Exception("Cart item with ID {$data['cart_id']} not found.");
                    }
    
                    $current_user = Auth::user();
                    $stripe_res = $this->StripeService->stripePaymentIntent($getaData->amount, $request->card_id, $current_user->stripe_customer_id);
    
                    if ($stripe_res['res'] == false) {
                        throw new \Exception($stripe_res['msg']);
                    }
                    
                    $order_id = uniqid('',true);
    
                    $payment_intent_id = $stripe_res['payment_intent_id'];
                    $charge_id = $stripe_res['charge_id'];
    
                    $save = new StickerPurchase();
                    $save->user_id = $current_user->id;
                    $save->order_id = $order_id;
                    $save->product_id = $getaData->product_id;
                    $save->quantity = $getaData->quantity;
                    $save->payment_intent_id = $payment_intent_id;
                    $save->charge_id = $charge_id;
                    $save->ad_address_id = $request->address;
                    $save->save();
    
                    $product = Product::where('id', $getaData->product_id)->first();
                    
                    $transaction = new TransactionHistory();
                    $transaction->user_id = $current_user->id;
                    $transaction->product_id = $getaData->product_id;
                    $transaction->sticker_purchase_id = $save->id;
                    $transaction->source = $request->card_number;
                    $transaction->source_type = 'card';
                    $transaction->type = 'debit';
                    $transaction->amount = $product->price*$getaData->quantity;
                    $transaction->save();
    
                    
    
                    // Check if the product exists before updating
                    if ($product) {
                        $product->total_purchased += $getaData->quantity;
                        $product->count -= $getaData->quantity;
                        $product->save();
                    } else {
                        throw new \Exception("Product with ID {$getaData->product_id} not found.");
                    }
                    $getaData->delete();
                }
    
                DB::commit();
    
                return response()->json(['message' => "Sticker purchase has been successful", 'status' => 'success'], 200);
            } else {
                return response()->json(['message' => 'No items found in the order.', 'status' => 'failed'], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function purchaseHistory(){
        $current_user_id = Auth::id();
        $transactionData = TransactionHistory::where('user_id',$current_user_id)->where('product_id','!=','null')->with(['product','product.stickerpurchase','user','sticker'])->orderBy('id','Desc')->paginate(10);
        return view('advertiser.purchasedHistory',compact('transactionData'));
    }
    
    public function addNewPartner(){
        return view('advertiser.addNewPartner');
    }
   
    public function storePartner(Request $request){
        
    // $stateZipCodes = [
    //     'Alabama' => ['35004', '36925'],
    //     'Alaska' => ['99501', '99950'],
    //     'Arizona' => ['85001', '86556'],
    //     'Arkansas' => ['71601', '72959'],
    //     'California' => ['90001', '96162'],
    //     'Colorado' => ['80001', '81658'],
    //     'Connecticut' => ['06101', '06928'],
    //     'Delaware' => ['19701', '19980'],
    //     'Florida' => ['33101', '34997'],
    //     'Georgia' => ['30301', '39901'],
    //     'Hawaii' => ['96801', '96898'],
    //     'Idaho' => ['83201', '83877'],
    //     'Illinois' => ['60601', '62999'],
    //     'Indiana' => ['46001', '47997'],
    //     'Iowa' => ['50001', '52809'],
    //     'Kansas' => ['66002', '67954'],
    //     'Kentucky' => ['40003', '42788'],
    //     'Louisiana' => ['70112', '71497'],
    //     'Maine' => ['03901', '04992'],
    //     'Maryland' => ['21201', '21930'],
    //     'Massachusetts' => ['01001', '02791'],
    //     'Michigan' => ['48001', '49971'],
    //     'Minnesota' => ['55001', '56763'],
    //     'Mississippi' => ['38601', '39776'],
    //     'Missouri' => ['63001', '65899'],
    //     'Montana' => ['59001', '59937'],
    //     'Nebraska' => ['68001', '69367'],
    //     'Nevada' => ['89501', '89795'],
    //     'New Hampshire' => ['03301', '03897'],
    //     'New Jersey' => ['07001', '08989'],
    //     'New Mexico' => ['87501', '88439'],
    //     'New York' => ['10001', '14925'],
    //     'North Carolina' => ['27501', '28909'],
    //     'North Dakota' => ['58001', '58856'],
    //     'Ohio' => ['43001', '45999'],
    //     'Oklahoma' => ['73001', '74966'],
    //     'Oregon' => ['97001', '97920'],
    //     'Pennsylvania' => ['15001', '19640'],
    //     'Rhode Island' => ['02801', '02940'],
    //     'South Carolina' => ['29001', '29945'],
    //     'South Dakota' => ['57001', '57799'],
    //     'Tennessee' => ['37010', '38589'],
    //     'Texas' => ['73301', '79999'],
    //     'Utah' => ['84001', '84791'],
    //     'Vermont' => ['05601', '05907'],
    //     'Virginia' => ['20101', '24658'],
    //     'Washington' => ['98001', '99403'],
    //     'West Virginia' => ['24701', '26886'],
    //     'Wisconsin' => ['53201', '54990'],
    //     'Wyoming' => ['82001', '83128'],
    // ];
        
        
        $validator = Validator::make($request->all(), [
            "category" => 'required',
            "company_name" => 'required|max:100',
            "city"=>'required',
            "state"=>'required',
            "zip_code" =>"required",
            // 'zip_code' => [
            //     'required',
            //     'regex:/^\d{5}(-\d{4})?$/',
            //     function ($attribute, $value, $fail) use ($request, $stateZipCodes) {
            //         $state = $request->state;
            //         if(array_key_exists($state,$stateZipCodes)){
            //             if(!($value >= $stateZipCodes[$state][0] && $value <= $stateZipCodes[$state][1])){
            //                 $fail('The ZIP code does not match the selected state.');
            //             }
            //         }else{
            //             $fail('Please Select Valid State');
            //         }
                    
            //     },
            // ],
            "phone"=>'required|numeric|digits:10',
            "website" => 'required|url',
            // "logo"=>"required|image|mimes:jpg,png|max:100|dimensions:width=300,height=300",
            "logo"=>"required|image|mimes:jpg,png",
            // "lat"=>"required",
            // "lng"=>"required"
        // ],[
        //     "logo.dimensions"=> ' The Image dimensions must be exactly 300x300 pixels' ,
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } 
        
        try {

    
         
                    DB::beginTransaction();
    
                    $save = new TrustedPartners();
                    $save->category = $request->category;
                    $save->company_name = $request->company_name;
                    $save->created_by = Auth::id();
                    $save->city = $request->city;
                    $save->state = $request->state;
                    $save->zip_code = $request->zip_code;
                    $save->phone = $request->phone;
                    $save->website = $request->website;
                    $save->lat = $request->lat;
                    $save->lng = $request->lng;
                    
                    if ($request->logo) {
                        $res = $this->UploadImage->saveMedia($request->logo,Auth::id());
                        $save->logo = $res;
                    }
                    
                    
                    $save->save();
                    DB::commit();
        
                    return response()->json(['message' => "Trusted Partner Added Successfully", 'status' => 'success'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function trustedPartners(Request $request){
        $currentDate = now()->format('Y-m-d');
        $current_user = Auth::user();
        if($current_user->stripe_customer_id){
            
            $stripe_res = $this->StripeService->getAllCardsByCustomerId($current_user->stripe_customer_id);
            if($stripe_res['res'] == false){
                return back()->with('error',$stripe_res['msg']);
            }
            $cards = $stripe_res['cards'];
        }else{
            $cards = null;
        }
        
        $getTrustedPartners = TrustedPartners::where('created_by',Auth::id())->orderBy('id','desc')->paginate(10);
        $getFeaturedCompanyPrice = FeaturedCompanyPrice::all(); 
        return view('advertiser.trustedPartners',compact('getTrustedPartners','currentDate','cards','getFeaturedCompanyPrice'));
    }
    
    public function destroyPartner(Request $request,String $id){
    //   Find the Partner Details

        $partnerDetails = TrustedPartners::find($id);
        if(file_exists($partnerDetails->logo)){
            unlink($partnerDetails->logo);
        }
        
        $deleteStatus = $partnerDetails->delete();
        if($deleteStatus == 1){
            return response()->json(['message' => "Trusted Partner Details Deleted Successfully", 'status' => 'success'], 200);
        }else{
            return response()->json(['message' => "Something Went Wrong", 'status' => 'error'], 500);
        }
        
    }
    
    public function editPartner(Request $request,$id){
        $partnerDetails = TrustedPartners::find($id);
        return view('advertiser.editPartner',compact('partnerDetails'));
    }
    
    public function updatePartner(Request $request){
        
        $validator = Validator::make($request->all(), [
            "category" => 'required',
            "company_name" => 'required|max:100',
            "city"=>'required',
            "state"=>'required',
            "zip_code"=>'required',
            "phone"=>'required|numeric|digits:10',
            "website" => 'required|url',
            "logo"=>"image|mimes:jpg,png",
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {

            // find the partner Details 
            $prtnr = TrustedPartners::find($request->id);
            
            $prtnr->category = $request->category;
            $prtnr->company_name = $request->company_name;
            $prtnr->created_by = Auth::id();
            $prtnr->city = $request->city;
            $prtnr->state = $request->state;
            $prtnr->zip_code = $request->zip_code;
            $prtnr->phone = $request->phone;
            $prtnr->website = $request->website;
            $prtnr->lat = $request->lat;
            $prtnr->lng = $request->lng;
            
            if($request->logo){
                if(file_exists($prtnr->logo)){
                    unlink($prtnr->logo);
                }
                $res = $this->UploadImage->saveMedia($request->logo,Auth::id());
                $prtnr->logo = $res;
            }    
            $prtnr->save();
        
            return response()->json(['message' => "Trusted Partner Updated Successfully", 'status' => 'success'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function storeFeaturePartnerPayment(Request $request){
        // return response()->json($request->all());
         $validator = Validator::make($request->all(), [
            'partner' => 'required',
            "amount" => 'required',
            "card_number" => 'required',
            "card"=>'required',
            "featurePlanId"=>'required'
        ]);
    
        if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            DB::beginTransaction();
            $current_user = Auth::user();
            $stripe_res = $this->StripeService->stripePaymentIntent($request->amount, $request->card, $current_user->stripe_customer_id);
            if($stripe_res['res'] == false){  
                 return response()->json(['message' => $stripe_res['msg'], 'status' => 'failed', 'data' => []], 500);
            }
            $payment_intent_id = $stripe_res['payment_intent_id'];
            $charge_id = $stripe_res['charge_id'];
            
            
            $featureCompanyPrice = FeaturedCompanyPrice::find($request->featurePlanId);
            
            $today = date('Y-m-d');
            $renewalDate = date('Y-m-d', strtotime('+'.$featureCompanyPrice->month.' month', strtotime($today)));

            $data = new SubscribedPartner;
            $data->trusted_partner_id = $request->partner;
            $data->user_id = $current_user->id;
            $data->payment_indent_id = $payment_intent_id;
            $data->charge_id = $charge_id;
            $data->source = $request->card;
            $data->source_type = 'card';
            $data->type = 'debit';
            $data->subscription_type= $request->featurePlanId;
            $data->amount = $request->amount;
            $data->save();
            
            
            // update the feature_partner status and renewal_date
            $update = TrustedPartners::where('id', $request->partner)->update(['featured_partner' => '1','renewal_date' => $renewalDate,'featured_company_price_id'=>$request->featurePlanId]);
            
            
            DB::commit();
    
            return response()->json(['message' => "Payment has been done successfully", 'status' => 'success', 'data' => $data], 200);
        } catch (\Exception $e) {
            
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }
    
    
    public function cancelSubscription(Request $request){
        $validator = Validator::make($request->all(), [
            "partnerId" => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } 
        
         try {

            
            $prtnr = TrustedPartners::find($request->partnerId);
          
            if($prtnr->cancel_status == 0 && $prtnr->featured_partner == 1){
                
                TrustedPartners::where('id', $request->partnerId)->update(['cancel_status' => '1','cancel_at' =>date('Y-m-d h:i:s') ]);
                
                return response()->json(['message'=>"Your Cancellation Request Saved Successfully",'status'=>"success"],200);
            }else{
                return response()->json(['message'=>"Your Subscription Cancellation Request is Pending",'status'=>'error'],200);   
            }
            
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function ViewAllOrders(){
        $currentDate = now()->format('Y-m-d');
        $current_user = Auth::user();
        
        $orders = StickerPurchase::with('products')->where('user_id',Auth::id())->orderBy('id','desc')->paginate(5);
        return view('advertiser.orders',compact('orders'));
    }
    
    public function invoice(Request $request){
        $id = $request->id;
        $invoice = StickerPurchase::with('products','user','address')->find($id);
        return view('advertiser.invoice',compact('invoice'));
    }
    
    
    
}