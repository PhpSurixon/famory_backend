<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessTag;
use App\Models\BusinessTagCode;
use App\Models\BusinessTagPrice;
use App\Services\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    protected $UploadImage;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
    }

    public function index()
    {
        $datas = Business::withCount([
            'tagCodes',
            // Sold = marked sold by an admin or registered by a user (a registered tag is also sold)
            'tagCodes as sold_codes_count' => function ($q) {
                $q->whereNotNull('sold_at');
            },
            'tagCodes as registered_codes_count' => function ($q) {
                $q->whereNotNull('registered_user_id');
            },
        ])->orderBy('id', 'desc')->get();
        return view('admin.Business.View', compact('datas'));
    }

    public function create()
    {
        return view('admin.Business.Create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|max:255',
            'business_type' => 'required|in:Internal,External',
            'email' => 'required|email|max:255|unique:businesses,email',
            'mobile' => 'required|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'register_date' => 'required|date',
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $business = new Business;
            $business->is_active = $request->is_active;
            $business->business_name = $request->business_name;
            $business->business_type = $request->business_type;
            $business->email = $request->email;
            $business->mobile = $request->mobile;
            $business->register_date = $request->register_date;
            if ($request->hasFile('image')) {
                $business->image = $this->UploadImage->saveMedia($request->file('image'), Auth::id());
            }
            $business->save();
            return redirect()->route('business')->with('success', 'Business Created Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $data = Business::findOrFail($id);
        return view('admin.Business.Edit', compact('data'));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|max:255',
            'business_type' => 'required|in:Internal,External',
            'email' => 'required|email|max:255|unique:businesses,email,' . $id,
            'mobile' => 'required|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'register_date' => 'required|date',
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $business = Business::findOrFail($id);
            $business->is_active = $request->is_active;
            $business->business_name = $request->business_name;
            $business->business_type = $request->business_type;
            $business->email = $request->email;
            $business->mobile = $request->mobile;
            $business->register_date = $request->register_date;
            if ($request->hasFile('image')) {
                $business->image = $this->UploadImage->saveMedia($request->file('image'), Auth::id());
            }
            $business->save();
            return redirect()->route('business')->with('success', 'Business Updated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Page to pick a Business Tag and assign its codes to this business.
     */
    public function assignTags($id)
    {
        $business = Business::findOrFail($id);

        $tags = BusinessTag::where('active', true)
            ->withCount([
                'codes as available_count' => function ($q) {
                    $q->whereNull('business_id');
                },
                'codes as assigned_count' => function ($q) use ($business) {
                    $q->where('business_id', $business->id);
                },
                'codes as sold_count' => function ($q) use ($business) {
                    $q->where('business_id', $business->id)->whereNotNull('sold_at');
                },
            ])
            ->orderBy('name')
            ->get();

        $assignedCodes = BusinessTagCode::with(['businessTag:id,name,type_of_tag,selling_price', 'registeredUser:id,first_name,last_name,email,phone'])
            ->where('business_id', $business->id)
            ->orderBy('business_tag_id')
            ->orderBy('id')
            ->get();

        // Business price saved for each tag (tag id => price)
        $prices = BusinessTagPrice::where('business_id', $business->id)->pluck('business_price', 'business_tag_id');

        return view('admin.Business.AssignTags', compact('business', 'tags', 'assignedCodes', 'prices'));
    }

    /**
     * JSON: codes of a tag that can be picked for this business
     * (not assigned to anyone, or already assigned to this business).
     */
    public function getTagCodes($id, Request $request)
    {
        $business = Business::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'business_tag_id' => 'required|exists:business_tags,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        $codes = BusinessTagCode::where('business_tag_id', $request->business_tag_id)
            ->where(function ($q) use ($business) {
                $q->whereNull('business_id')->orWhere('business_id', $business->id);
            })
            // Tags a user has registered in the app are done: they are not listed here
            // (they show under "Assigned Tag Codes" and in the Sold Report).
            ->whereNull('registered_user_id')
            ->orderBy('id')
            ->get(['id', 'reference_no', 'business_id', 'sold_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'reference_no' => $c->reference_no,
                'assigned' => $c->business_id === $business->id,
                'sold' => !is_null($c->sold_at), // sold by the shop but not registered yet: locked
            ]);

        $tag = BusinessTag::find($request->business_tag_id);
        $savedPrice = BusinessTagPrice::where('business_id', $business->id)
            ->where('business_tag_id', $tag->id)
            ->value('business_price');

        return response()->json([
            'status' => 'success',
            'codes' => $codes,
            // The tag's selling price is the default; business_price is what was saved for this business (or null).
            'selling_price' => number_format((float) $tag->selling_price, 2, '.', ''),
            'business_price' => $savedPrice !== null ? number_format((float) $savedPrice, 2, '.', '') : null,
        ]);
    }

    /**
     * Save the selection for one tag: checked codes are assigned to this
     * business, unchecked codes that were assigned to it are released.
     */
    public function saveTagCodes($id, Request $request)
    {
        $business = Business::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'business_tag_id' => 'required|exists:business_tags,id',
            'code_ids' => 'nullable|array',
            'code_ids.*' => 'integer',
            'business_price' => 'nullable|numeric|min:0|max:99999999.99',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $selected = array_map('intval', $request->input('code_ids', []));

            [$assigned, $released] = DB::transaction(function () use ($business, $request, $selected) {
                // Business price for this tag: the value sent, else the price already saved,
                // else the tag's selling price.
                $existing = BusinessTagPrice::where('business_id', $business->id)
                    ->where('business_tag_id', $request->business_tag_id)
                    ->first();

                $price = $request->filled('business_price')
                    ? round((float) $request->business_price, 2)
                    : ($existing ? (float) $existing->business_price
                                 : (float) BusinessTag::whereKey($request->business_tag_id)->value('selling_price'));

                BusinessTagPrice::updateOrCreate(
                    ['business_id' => $business->id, 'business_tag_id' => $request->business_tag_id],
                    ['business_price' => $price]
                );

                // Release codes of this tag that are assigned to this business but now unchecked,
                // so they can be assigned to another business. Sold codes are never released:
                // the shop has already sold them (and a user may have registered them).
                $released = BusinessTagCode::where('business_tag_id', $request->business_tag_id)
                    ->where('business_id', $business->id)
                    ->whereNull('sold_at')
                    ->whereNotIn('id', $selected)
                    ->update(['business_id' => null, 'assigned_at' => null]);

                // Assign checked codes; the whereNull guard leaves a code already taken by any business untouched.
                $assigned = BusinessTagCode::where('business_tag_id', $request->business_tag_id)
                    ->whereIn('id', $selected)
                    ->whereNull('business_id')
                    ->update(['business_id' => $business->id, 'assigned_at' => now()]);

                return [$assigned, $released];
            });

            return response()->json([
                'status' => 'success',
                'message' => "{$assigned} code(s) assigned, {$released} code(s) released.",
            ]);
        } catch (\Exception $e) {
            Log::error('BusinessController@saveTagCodes: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Sold report for one business: what was assigned, what the shop sold (marked by an admin
     * from the shopkeeper's report), what users registered in the app, and what is still pending.
     */
    public function soldReport($id)
    {
        $business = Business::findOrFail($id);

        $codes = BusinessTagCode::with([
                'businessTag:id,name,selling_price',
                'registeredUser:id,first_name,last_name,email,phone',
                'invoice:id,invoice_no',
            ])
            ->where('business_id', $business->id)
            ->orderBy('business_tag_id')
            ->orderBy('id')
            ->get();

        $prices = BusinessTagPrice::where('business_id', $business->id)->pluck('business_price', 'business_tag_id');

        $summary = [
            'assigned' => $codes->count(),
            'sold' => $codes->whereNotNull('sold_at')->count(),
            'registered' => $codes->whereNotNull('registered_user_id')->count(),
        ];
        $summary['pending'] = $summary['assigned'] - $summary['sold'];

        $byTag = $codes->groupBy('business_tag_id')->map(function ($group, $tagId) use ($prices) {
            $first = $group->first();
            $sold = $group->whereNotNull('sold_at')->count();

            return [
                'name' => $first->businessTag->name ?? 'Deleted Tag',
                'selling_price' => $first->businessTag->selling_price ?? null,
                'business_price' => $prices[$tagId] ?? ($first->businessTag->selling_price ?? null),
                'assigned' => $group->count(),
                'sold' => $sold,
                'registered' => $group->whereNotNull('registered_user_id')->count(),
                'pending' => $group->count() - $sold,
            ];
        });

        return view('admin.Business.SoldReport', compact('business', 'codes', 'summary', 'byTag'));
    }

    /**
     * Mark assigned codes as sold on the business's behalf (from the shopkeeper's sold report).
     */
    public function markSold($id, Request $request)
    {
        $business = Business::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code_ids' => 'required|array|min:1',
            'code_ids.*' => 'integer',
            'sold_date' => 'nullable|date|before_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        try {
            // The date the shop reported; the time of day is the moment the admin records it.
            $soldAt = $request->filled('sold_date')
                ? Carbon::parse($request->sold_date)->setTime(now()->hour, now()->minute, now()->second)
                : now();

            // Only this business's codes that are not sold yet.
            $marked = BusinessTagCode::where('business_id', $business->id)
                ->whereIn('id', array_map('intval', $request->code_ids))
                ->whereNull('sold_at')
                ->update(['sold_at' => $soldAt, 'sold_marked_by' => Auth::id()]);

            return response()->json([
                'status' => 'success',
                'message' => "{$marked} code(s) marked as sold.",
            ]);
        } catch (\Exception $e) {
            Log::error('BusinessController@markSold: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Undo a sold mark made by mistake. A tag a user has registered stays sold.
     */
    public function unmarkSold($id, Request $request)
    {
        $business = Business::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code_ids' => 'required|array|min:1',
            'code_ids.*' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        try {
            // A code on an invoice stays sold until that invoice is cancelled.
            $unmarked = BusinessTagCode::where('business_id', $business->id)
                ->whereIn('id', array_map('intval', $request->code_ids))
                ->whereNotNull('sold_at')
                ->whereNull('registered_user_id')
                ->whereNull('business_invoice_id')
                ->update(['sold_at' => null, 'sold_marked_by' => null]);

            return response()->json([
                'status' => 'success',
                'message' => "{$unmarked} code(s) set back to pending.",
            ]);
        } catch (\Exception $e) {
            Log::error('BusinessController@unmarkSold: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $business = Business::find($id);
            if (!$business) {
                return response()->json(['status' => 404, 'message' => 'Business not found.']);
            }
            $business->delete();
            return response()->json(['status' => 200, 'message' => 'Business deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Internal Server Error.']);
        }
    }
}
