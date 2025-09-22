<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeathConfirmation;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\OneSignalTrait;
use DB;
use Illuminate\Support\Facades\Validator;
class DeathConfirmationController extends Controller
{
    use OneSignalTrait;
    public function __construct()
    {
        //
    }

    public function confirmDeceasedOLD(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'user_id' =>'required|exists:users,id',
                'status'  => 'required|in:confirmed,not_confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $authUser = Auth::user();

            $trusted = TrustedUser::where('user_id', $request->user_id)
                                    ->where('trusted_user_id', $authUser->id)
                                    ->where('status', 'accepted')
                                    ->first();

            if (!$trusted) {
                return response()->json(['message' => 'You are not an accepted trusted user'], 403);
            }

            // Save confirmation
            $confirmation = DeathConfirmation::updateOrCreate(
                ['user_id' => $request->user_id, 'trusted_user_id' => $authUser->id],
                ['status' => $request->status]
            );

            // 🔔 Notify other trusted users when someone marks as deceased (first time)
            if ($request->status === 'confirmed') {
                // check if this was the FIRST confirmation
                $alreadyConfirmed = DeathConfirmation::where('user_id', $request->user_id)
                    ->where('status', 'confirmed')
                    ->count();

                if ($alreadyConfirmed === 1) { // first confirmation
                    // get all other trusted users except the one who just confirmed
                    $otherTrusted = TrustedUser::where('user_id', $request->user_id)
                        ->where('status', 'accepted')
                        ->where('trusted_user_id', '!=', $authUser->id)
                        ->pluck('trusted_user_id');

                    $owner = User::find($request->user_id);

                    foreach ($otherTrusted as $receiverId) {
                        // 🔔 send notification
                        $this->notifyMessage($authUser,$receiverId, $owner, "deceased", deceasedUser: $owner,deceasedById: $authUser->id);
                    }
                }
            }

            // ✅ Final step: check threshold
            $confirmedCount = DeathConfirmation::where('user_id', $request->user_id)
                ->where('status', 'confirmed')
                ->count();

            $notConfirmed = DeathConfirmation::where('user_id', $request->user_id)
                                            ->where('status', 'not_confirmed')
                                            ->exists();

            if ($confirmedCount >= 2 && !$notConfirmed) {
                User::where('id', $request->user_id)->update(['is_dead' => 1]);
            }

            return response()->json([
                'message' => 'Death confirmation recorded',
                'data' => $confirmation,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => "Something Went Wrong! " . $e->getMessage(), 'status' => 'failed'], 400);
        }
    }

    public function confirmDeceased(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' =>'required|exists:users,id',
                'status'  => 'required|in:confirmed,not_confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $authUser = Auth::user();

            $trusted = TrustedUser::where('user_id', $request->user_id)
                                    ->where('trusted_user_id', $authUser->id)
                                    ->where('status', 'accepted')
                                    ->first();

            if (!$trusted) {
                return response()->json(['message' => 'You are not an accepted trusted user'], 403);
            }

            // Save confirmation
            $confirmation = DeathConfirmation::updateOrCreate(
                ['user_id' => $request->user_id, 'trusted_user_id' => $authUser->id],
                ['status' => $request->status]
            );

            // 🔔 Notify other trusted users when someone marks as deceased (first time)
            if ($request->status === 'confirmed') {
                $alreadyConfirmed = DeathConfirmation::where('user_id', $request->user_id)
                    ->where('status', 'confirmed')
                    ->count();

                if ($alreadyConfirmed === 1) { // first confirmation
                    $otherTrusted = TrustedUser::where('user_id', $request->user_id)
                        ->where('status', 'accepted')
                        ->where('trusted_user_id', '!=', $authUser->id)
                        ->pluck('trusted_user_id');

                    $owner = User::find($request->user_id);

                    foreach ($otherTrusted as $receiverId) {
                        $this->notifyMessage(
                            $authUser, 
                            $receiverId, 
                            $owner, 
                            "deceased", 
                            deceasedUser: $owner,
                            deceasedById: $authUser->id
                        );
                    }

                    $this->notifyMessage(
                        $authUser,
                        $owner->id,
                        $owner,
                        "deceased_marked",
                        deceasedUser: $owner,
                        deceasedById: $authUser->id
                    );
                }
            }

            // ✅ Final step: check threshold dynamically
            $aliveTrustedCount = TrustedUser::where('user_id', $request->user_id)
                ->where('status', 'accepted')
                ->whereHas('trustedUser', function ($q) {
                    $q->where('is_dead', 0); // skip dead trusted users
                })
                ->count();

            $requiredConfirmations = min(2, $aliveTrustedCount);

            $confirmedCount = DeathConfirmation::where('user_id', $request->user_id)
                ->where('status', 'confirmed')
                ->count();

            $notConfirmed = DeathConfirmation::where('user_id', $request->user_id)
                                            ->where('status', 'not_confirmed')
                                            ->exists();

            if ($confirmedCount >= $requiredConfirmations && !$notConfirmed) {
                User::where('id', $request->user_id)->update(['is_dead' => 1]);
            }

            return response()->json([
                'message' => 'Death confirmation recorded',
                'data' => $confirmation,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => "Something Went Wrong! " . $e->getMessage(), 'status' => 'failed'], 400);
        }
    }

    public function selfRevokeDeath(Request $request)
    {
        try {
            $authUser = Auth::user();

            // Step 1: Check if user is marked dead
            if ($authUser->is_dead == 0) {
                return response()->json([
                    'message' => 'You are already marked as alive.',
                    'status'  => 'success'
                ], 200);
            }

            // Step 2: Update user as alive
            $authUser->update([
                'is_dead' => 0,
                'passed_date' => null
            ]);

            // Step 3: Delete all confirmations related to this user
            DeathConfirmation::where('user_id', $authUser->id)->delete();

            return response()->json([
                'message' => 'Your death status has been revoked. You are now marked alive.',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! ' . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }



}
