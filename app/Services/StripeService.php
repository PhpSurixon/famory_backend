<?php

namespace App\Services;
require('stripe/autoload.php');
require_once(base_path() . '/vendor/stripe/stripe-php/init.php');
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\CardException;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected $stripe;
    protected $client_id;

    public function __construct()
    {
        $stripeSecret = config('app.stripe_secret');
        $this->client_id = config('app.stripe_client_id');
        $this->stripe = new StripeClient($stripeSecret);
    }

    public function createStripeCustomer($user){
        $return= array();
        try{
           $customer = $this->stripe->customers->create([
            //   'name'=> $user->first_name, 
              'email'=> $user->email, 
           ]);       
    
           $return['res'] = true;
           $return['stripe_customer_id'] = $customer->id;
           $return['msg'] = 'Customer created successfylly';
        }

        catch(\Exception $e){
          $return['res'] = false;
          $return['msg'] = $e->getMessage(); 
        }

        // dd($customer);
        return $return;
    }

    public function createConnectAccount($user){
        $return= array();
        try{
            $account = $this->stripe->accounts->create([
                'type' => 'express',
                'country' => 'US',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
            ]);
            
            $return = $account;
            $return['res']= true;
            $return['msg']= 'Account created successfully';
        }
        catch(\Exception $e){
            $return = [];
            $return['res'] = false;
            $return['customer_id'] = '';
            $return['msg'] = $e->getMessage(); 
        }
        return $return;
    }

    public function refundsAmount($payment_intent_id){
        try{
            
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $payment_intent_id
            ]);
            
            $return= array();
            $return['res']= true;
            $return['msg']= 'success';
            return $return;
        }catch(Exception $ex){
            $return= array();
            $return['res']= false;
            $return['msg']= $ex->getMessage();
            return $return;
       }
    }


    public function getAccountData($accountId){
        $return= array();
        $getAccountnumber =  $this->stripe->accounts->retrieve(
          $accountId
        );
        $return['account_id'] = $accountId;
        foreach ($getAccountnumber->external_accounts->data as $externalAccount)
        {
            $return['bank_id'] = $externalAccount->id;
            $return['rounting_number'] = $externalAccount->routing_number;
            $return['currency'] = $externalAccount->currency;
        }
        return $return;
        
    }
    
    public function retriveKycAccount($user){
        $return= array();
        try{
            $link = [];
            $kyc_account = $this->stripe->accounts->retrieve(
            $user->stripe_account_id,
              []
            );
            
            $link = $this->createAccountLink($user);
            
            $card_payments = $kyc_account->capabilities->card_payments;
            $transfer = $kyc_account->capabilities->transfers;
            if($card_payments =='inactive' || $transfer=='inactive'){
                $link['kyc_status'] = 'inactive';
            }else{
                $link['kyc_status'] = 'active';
            }
            
            $return['link'] = $link;
            $return['msg']= 'Account retrived successfully';
            $return = [
                'res' => true,
                'link' => $link,
                'msg' => 'Account retrived successfully'
            ];
        }catch(\Exception $e){
            $return['res'] = false;
            $return['customer_id'] = '';
            $return['msg'] = $e->getMessage(); 
        }
        return $return;
    }

     
    public function createAccountLink($user){
        $return= array();
        try{
            $account_link = $this->stripe->accountLinks->create([
              'account' => $user->stripe_account_id,
              'refresh_url' => 'https://messagescheduling.betaplanets.com/api/stripe_kyc_callback',
              'return_url' => 'https://messagescheduling.betaplanets.com/api/stripe_kyc_callback',
              'type' => 'account_onboarding',
            ]);
            
            $return = $account_link;
            $return['res']= true;
            $return['msg']= 'Account link created successfully';
            
        } catch(\Exception $e){
            $return['res'] = false;
            $return['customer_id'] = '';
            $return['msg'] = $e->getMessage(); 
        }
        return $return;
    }
    

    public function stripeConnectRedirect()
    {

        $AUTHORIZE_URI = 'https://messagescheduling.betaplanets.com/api/stripe_kyc_callback';

            // Show OAuth link
        $authorize_request_body = array(
            'response_type' => 'code',
            'scope' => 'read_write',
            'redirect_uri' => "https://messagescheduling.betaplanets.com/api/stripe_kyc_callback",
            'client_id' => $this->client_id,
        );

        $url = $AUTHORIZE_URI . '?' . http_build_query($authorize_request_body);
        if ($authorize_request_body) {

            return $url;
        }
    }
    
    public function addCardToCustomer($customerId, $cardToken){
        $return= array();

        if ($this->isDuplicateCard($customerId, $cardToken)) {
            $return['res'] = false;
            $return['msg'] = 'Duplicate card detected. Card not added.';
            return $return;
        }
        
        try{
            $card = $this->stripe->customers->createSource(
                  $customerId,
                  ['source' => $cardToken,
                    'metadata' => [
                        'customerId' => $customerId,
                    ],
                  ]
            );

            $return['res'] = true;
            $return['card_id'] = $card->id;
            $return['card_data'] = $card;
            $return['msg'] = 'Card created successfully';
        }catch(\Exception $e){
          $return['res'] = false;
          $return['msg'] = $e->getMessage(); 
        }

        return  $return;
    }

    public function getAllCardsByCustomerId($customerId){
        $return = array();
        try {
            // List the customer's existing card payment methods
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            $return['res'] = true;
            $return['cards'] = $paymentMethods->data;
            $return['msg'] = 'Cards retrieved successfully';
        } catch (\Exception $e) {
            $return['res'] = false;
            $return['msg'] = $e->getMessage();
        }

        return $return;
    }

    public function createStripeTransfer($amount,$account_no){
        $return= array();
        try{
            $res = $this->stripe->transfers->create([
                "amount"        => $amount*100,
                "currency"      => 'usd',
                "destination"   => $account_no,
                // "metadata"      => $data['metadata'],
            ]);
            
            
            $transfer_res = json_decode(json_encode($res, true), true);
            $return['status']= 'success';
            $return['transfer_id'] = $transfer_res['id'];
            $return['transfer_res'] = $transfer_res;
            return $return;
        }catch(Exception $ex){
            $return['status']= 'failed';
            $return['msg']= $ex->getMessage();
            return $return;
        }   
    }

    public function getAccountNumber($accountId){
        $return= array();
        $getAccountnumber =  $this->stripe->accounts->retrieve(
            $accountId
        );
        foreach ($getAccountnumber->external_accounts->data as $externalAccount)
        {
            $return = $externalAccount->last4;
        }
        return $return;
     }

    public function deleteCardFromCustomer($cardId)
    {
        $return = array();
        try {
            // Detach the card from the customer
            $this->stripe->paymentMethods->detach($cardId);

            $return['res'] = true;
            $return['msg'] = 'Card deleted successfully';
        } catch (\Exception $e) {
            $return['res'] = false;
            $return['msg'] = $e->getMessage();
        }

        return $return;
    }


    private function isDuplicateCard($customerId, $cardToken){
        try {
            // Retrieve the card details from the token
            $cardDetails = $this->stripe->tokens->retrieve($cardToken)->card;

            // List the customer's existing card payment methods
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            foreach ($paymentMethods->data as $paymentMethod) {
                if ($paymentMethod->card->fingerprint === $cardDetails->fingerprint) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function stripePaymentIntent($amount, $card_id,$stripe_customer_id){
        
        $return= array();
        try{
            $res = $this->stripe->paymentIntents->create([
                'amount' => $amount*100,
                'currency' => 'usd',
                'payment_method' => $card_id,
                'customer' => $stripe_customer_id,
                'confirm' => true, 
                'payment_method_types' => ['card'],
                'capture_method' => 'manual',
            ]);
            
            $paymentMethod = $this->stripe->paymentMethods->retrieve(
                $res->payment_method
            );
        
            $cardLastFour = $paymentMethod->card->last4;
            
            
            $return['res']= true;
            $return['msg']= 'success';
            $return['payment_intent_id']= $res->id;
            $return['charge_id']= $res->latest_charge;
            $return['amount'] = $res->amount;
            $return['card_last_four'] = $cardLastFour;
        }
        catch(\Exception $e){
            $return = array();
            $return['res'] = false;
            $return['msg'] = $e->getMessage(); 
        }
        
        return $return;
    }
    
    
    // public function createAdvertisementProducts(Request $request){
        
    //     $return= array();
    //     try{      
    //         $res = $this->stripe->products->create([
    //             'name' => 'Advertisement Price',
    //             'description' => 'Advertisement Subscription For Monthly Base',
    //         ]);
            
    //         \Log::info('Product created successfully: ', ['product_id' => $res->id]);
            
    //         $return['res'] = true;
    //         $return['data'] = $res;
    //         $return['msg'] = 'Product saved successfully';
            
    //     } catch (\Exception $e) {
    //         $return['res'] = false;
    //         $return['msg'] = $e->getMessage();
    //     }

    //     return $return;
    
    // }
    
    // public function createAdvertisementPrice(Request $request){
    //     $return= array();
    //      try{      
            
    //         $res = $this->stripe->prices->create([
    //             'currency' => 'usd',
    //             'unit_amount' => $request->amount,
    //             'recurring' => [
    //                 'interval' => 'month',
    //             ],
    //             'product' => $request->stripe_product_id,
    //         ]);
    //         $return['res'] = true;
    //         $return['data'] = $res;
    //         $return['msg'] = 'Advertisement price saved successfully';
            
    //     } catch (\Exception $e) {
    //         $return['res'] = false;
    //         $return['msg'] = $e->getMessage();
    //     }

    //     return $return;
    // }
    
    
    
    

}

