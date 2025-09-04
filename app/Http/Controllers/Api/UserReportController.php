<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;

class UserReportController extends Controller
{
    public function storeReport(Request $request)
    {
        try {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'reported_user_id' => 'required|exists:users,id',
                'reason' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $reportedUser = User::findOrFail($request->reported_user_id);

            //Prevent self-report
            if ($reportedUser->id === $authUser->id) {
                return response()->json([
                    'message' => "You cannot report yourself",
                    'status' => 'failed'
                ], 400);
            }

            //Already reported check
            $exists = UserReport::where('reporter_id', $authUser->id)
                ->where('reported_user_id', $reportedUser->id)
                ->first();

            if ($exists) {
                return response()->json([
                    'message' => "You have already reported this user",
                    'status' => 'failed'
                ], 400);
            }

            //Create report
            $report = UserReport::create([
                'reporter_id'      => $authUser->id,
                'reported_user_id' => $reportedUser->id,
                'reason'           => $request->reason,
                'description'      => $request->description,
            ]);

            //Success response
            return response()->json([
                'message' => "You reported {$reportedUser->first_name} successfully",
                'status'  => 'success',
                'data'    => $report
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }

}
