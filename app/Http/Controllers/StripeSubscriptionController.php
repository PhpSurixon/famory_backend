<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\AdsPrice;
use App\Models\TransactionHistory;
use App\Models\Advertisement;
use App\Mail\AdsRenewalReminder;


class StripeSubscriptionController extends Controller
{
    public function adSubscriptionRenewal(Request $request)
    {
        return "check";
        // try {
        //     $getRenewalDates = Advertisement::where('renew_date', '!=', null)->get();
        //     $getCurrentDate = date('Y-m-d');
    
        //     foreach ($getRenewalDates as $getRenewalDate) {
        //         $renewalDate = date('Y-m-d', strtotime($getRenewalDate->renew_date));
        //         $fiveDaysBeforeRenewal = date('Y-m-d', strtotime('-5 days', strtotime($renewalDate)));
                
        //         // Send reminder email 5 days before renewal
        //         if ($getCurrentDate === $fiveDaysBeforeRenewal && !$getRenewalDate->reminder_email_sent) {
        //             $user = User::find($getRenewalDate->user_id);
        //             \Mail::to($user->email)->send(new AdsRenewalReminder($getRenewalDate, $user));
        //             $getRenewalDate->update(['reminder_email_sent' => 1]);
        //         }
    
        //         // Check if today is the renewal date
        //         if ($getCurrentDate === $renewalDate) {
        //             if ($getRenewalDate->cancel_status == '0') {
        //                 // Get price from database
        //                 $getPrice = AdsPrice::first();
        //                 if (!$getPrice) {
        //                     \Log::info("Price not found");
        //                     continue;
        //                 }
    
        //                 $userId = $getRenewalDate->user_id;
        //                 $current_user = User::find($userId);
    
        //                 // Call StripeService to create an invoice with a custom price
        //                 $stripe_res = $this->StripeService->createCustomSubscription($current_user->stripe_customer_id, $getPrice->price);
    
        //                 if ($stripe_res['res'] == false) {
        //                     // Handle failure: Reset renewal date
        //                     Advertisement::where('id', $getRenewalDate->id)->update(['renew_date' => null]);
        //                     continue;
        //                 }
    
        //                 // Update renewal date based on subscription success
        //                 $renewalDate = date('Y-m-d', strtotime('+' . $getPrice->day . ' month'));
    
        //                 // Save transaction details and update advertisement
        //                 $data = new TransactionHistory;
        //                 $data->user_id = $current_user->id;
        //                 $data->ads_id = $getRenewalDate->id;
        //                 $data->source = 'stripe';
        //                 $data->source_type = 'subscription';
        //                 $data->type = 'debit';
        //                 $data->amount = $getPrice->price;
        //                 $data->save();
    
        //                 $advertisement = Advertisement::find($getRenewalDate->id);
        //                 $advertisement->renew_date = $renewalDate;
        //                 $advertisement->save();
                        
        //                 // Send payment confirmation email
        //                 \Mail::to($current_user->email)->send(new RenewalAdPaymentProcess($advertisement, $current_user, $data));
    
        //                 \Log::info("Subscription successfully created for ad: " . $getRenewalDate->id);
        //             }
        //         }
        //     }
    
        //     return response()->json(['message' => 'Cron run successfully'], 200);
    
        // } catch (\Exception $e) {
        //     return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        // }
    }

}
