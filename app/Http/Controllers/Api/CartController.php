<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carts;
use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    /**
     * Add product to cart
     */
    public function addToCart(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity'   => 'required|integer|min:1',
                'action'     => 'required|in:cart,buy_now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $userId = Auth::id();
            $actionType = $request->action;

            $product = Product::find($request->product_id);

            /*
            |--------------------------------------------------
            | Product Not Found
            |--------------------------------------------------
            */
            if (!$product) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => "Tag not found. Please select another tag"
                ], 400);
            }

            /*
            |--------------------------------------------------
            | Out of Stock
            |--------------------------------------------------
            */
            if ($product->count <= 0) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => "Tag quantity out of stock. Buy another tag"
                ], 400);
            }

            /*
            |--------------------------------------------------
            | Quantity validation
            |--------------------------------------------------
            */
            if ($request->quantity > $product->count) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => "Only {$product->count} items available in stock"
                ], 400);
            }

            /*
            |--------------------------------------------------
            | Check if item already exists in cart
            |--------------------------------------------------
            */

            $cart = Carts::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->first();

            if ($cart) {

                $newQty = $cart->quantity + $request->quantity;

                if ($newQty > $product->count) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => "Only {$product->count} items available in stock"
                    ], 400);
                }

                $cart->quantity = $newQty;
                $cart->item_price = $product->reseller_price;
                $cart->save();

            } else {

                $cart = Carts::create([
                    'user_id'    => $userId,
                    'product_id' => $product->id,
                    'item_price' => $product->reseller_price,
                    'quantity'   => $request->quantity
                ]);
            }

            /*
            |--------------------------------------------------
            | Response
            |--------------------------------------------------
            */

            $message = $actionType == 'buy_now'
                ? 'Tag Buy Now successfully'
                : 'Tag added to cart successfully';

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'data'    => [
                    'cart_id'     => $cart->id,
                    'action_type' => $actionType
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => "Something went wrong",
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Cart List
     */
    public function cartList(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'cart_id' => 'nullable|exists:carts,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $userId = Auth::id();

            if ($request->filled('cart_id')) {

                $cartItems = Carts::with('product')
                    ->where('id', $request->cart_id)
                    ->where('user_id', $userId)
                    ->get();

            } else {

                $cartItems = Carts::with('product')
                    ->where('user_id', $userId)
                    ->get();
            }

            if ($cartItems->isEmpty()) {

                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Cart is empty. Please add tags to cart'
                ], 200);
            }

            $subtotal = 0;
            $shipping_amount = 0;

            foreach ($cartItems as $item) {
                $subtotal += $item->item_price * $item->quantity;
            }

            /*
            |-----------------------------------------
            | Shipping Rule
            |-----------------------------------------
            | Free shipping above $50
            */

            if ($subtotal < 50) {
                $shipping_amount = 4.99;
            }

            $total_amount = $subtotal + $shipping_amount;

            return response()->json([
                'status'          => 'success',
                'message'         => 'Cart item list',
                'cart_items'      => $cartItems,
                'data'            => $cartItems,
                'subtotal'        => round($subtotal,2),
                'shipping_amount' => round($shipping_amount,2),
                'total_amount'    => round($total_amount,2),
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update cart quantity
     */
    public function updateCart(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'cart_id'  => 'required|exists:carts,id',
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $userId = Auth::id();

            $cart = Carts::where('id', $request->cart_id)
                        ->where('user_id', $userId)
                        ->first();

            if (!$cart) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => "Cart item not found. Please pass correct cart id"
                ], 400);
            }

            $product = Product::find($cart->product_id);

            /*
            |---------------------------------------
            | Product Deleted
            |---------------------------------------
            */
            if (!$product) {

                $cart->delete();

                return response()->json([
                    'status'  => 'failed',
                    'message' => "Product not available anymore. Item removed from cart"
                ], 400);
            }

            /*
            |---------------------------------------
            | Out of Stock
            |---------------------------------------
            */
            if ($product->count <= 0) {

                $cart->delete();

                return response()->json([
                    'status'  => 'failed',
                    'message' => "Product out of stock. Item removed from cart"
                ], 400);
            }

            /*
            |---------------------------------------
            | Quantity greater than stock
            |---------------------------------------
            */
            if ($request->quantity > $product->count) {

                return response()->json([
                    'status'  => 'failed',
                    'message' => "Only {$product->count} items available in stock"
                ], 400);
            }

            /*
            |---------------------------------------
            | Update Quantity
            |---------------------------------------
            */

            $cart->quantity = $request->quantity;
            $cart->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cart updated successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove item from cart
     */
    public function removeCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,id',
        ]);

        if ($validator->fails()) 
        {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $cart = Carts::where('id',$request->cart_id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart item not found',
                'status' => 'failed',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Tag removed from cart',
            'status' => 'success',
        ],200);
    }

    public function checkout(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'cart_id'    => 'nullable|exists:carts,id',
                'address_id' => 'required|exists:user_addresses,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $userId = Auth::id();

            /*
            |----------------------------------------
            | Validate Address
            |----------------------------------------
            */

            $user_address = UserAddress::select('id','user_id','name','phone_number','house_number','road_name','state','zip_code')
                                      ->where('id',$request->address_id)
                                      ->where('user_id',$userId)
                                      ->first();

            if(!$user_address){
                return response()->json([
                    'status'=>'failed',
                    'message'=>'Address not found'
                ],400);
            }

            if (!preg_match('/^\d{5}(-\d{4})?$/', $user_address->zip_code)) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Orders can only be placed for US addresses. Please select a valid US zip code.'
                ], 400);
            }

            /*
            |----------------------------------------
            | Get Cart Items
            |----------------------------------------
            */

            if ($request->filled('cart_id')) {

                $cartItems = Carts::with('product')
                    ->where('id', $request->cart_id)
                    ->where('user_id', $userId)
                    ->get();

                $cart_type = 'buy_now';

            } else {

                $cartItems = Carts::with('product')
                    ->where('user_id', $userId)
                    ->get();

                $cart_type = 'cart';
            }

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status'=>'failed',
                    'message'=>'Cart is empty'
                ],400);
            }

            /*
            |----------------------------------------
            | Calculate Totals
            |----------------------------------------
            */

            $subtotal = 0;
            $validItems = [];

            foreach ($cartItems as $item) {

                // product deleted
                if (!$item->product) {
                    $item->delete();
                    continue;
                }

                // stock check
                if ($item->product->count <= 0) {
                    $item->delete();
                    continue;
                }

                // quantity check
                if ($item->quantity > $item->product->count) {
                    $item->quantity = $item->product->count;
                    $item->save();
                }

                $item_total = $item->item_price * $item->quantity;

                $subtotal += $item_total;
                $product_data = [
                                  'id'=>$item->product->id,
                                  'count'=>$item->product->count,
                                  'name'=>$item->product->name,
                                  'image'=>$item->product->image,
                                  'description'=>$item->product->description,
                                  'type_of_tag'=>$item->product->type_of_tag,
                                  'tag_purpose'=>$item->product->tag_purpose,
                                  'color'=>$item->product->color,
                                ];

                $validItems[] = [
                    'cart_id'     => $item->id,
                    'product_id'  => $item->product_id,
                    'product'     => $product_data,
                    'price'       => $item->item_price,
                    'quantity'    => $item->quantity,
                    'total'       => $item_total
                ];
            }

            if ($subtotal == 0) {
                return response()->json([
                    'status'=>'failed',
                    'message'=>'All items removed due to stock issues'
                ],400);
            }

            /*
            |----------------------------------------
            | Shipping Calculation
            |----------------------------------------
            */

            $shipping_amount = 0;

            if ($subtotal < 50) {
                $shipping_amount = 4.99;
            }

            $total_amount = round($subtotal + $shipping_amount,2);

            /*
            |----------------------------------------
            | Response
            |----------------------------------------
            */

            return response()->json([
                'status'        => 'success',
                'message'       => 'Checkout summary',
                'cart_type'     => $cart_type,
                'cart_items'    => $validItems,
                'user_address'  => $user_address,
                'subtotal'      => round($subtotal,2),
                'shipping_fee'  => round($shipping_amount,2),
                'total_amount'  => $total_amount
            ],200);

        } catch (\Exception $e) {

            return response()->json([
                'status'=>'failed',
                'message'=>'Something went wrong',
                'error'=>$e->getMessage()
            ],500);
        }
    }


}