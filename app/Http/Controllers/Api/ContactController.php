<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller as Controller;
use App\Models\UserContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\FormatResponseTrait;
use DB;
use Carbon\Carbon;

class ContactController extends Controller
{
    use FormatResponseTrait;



public function index()
{
    // Paginate the UserContact data
    $auth_user = auth()->guard('api')->user();
    
    $contacts = UserContact::where('user_id', $auth_user->id)
        ->leftJoin('users', \DB::raw("RIGHT(users.phone, 10)"), '=', \DB::raw("RIGHT(user_contacts.phone, 10)"))
        ->select('user_contacts.*',
            \DB::raw("CASE WHEN RIGHT(users.phone, 10) IS NOT NULL THEN '1' ELSE '0' END as phone_exists")
        )
        ->orderBy('user_contacts.name', 'asc')
        ->paginate(20);

    // Map the contacts to add phone_exists flag and structure as required
    $contacts->getCollection()->transform(function ($contact) {
        // Add phone_exists status to the phone number array
        $phoneNumbersStatus = [
            'name' => $contact->name,
            'user_id' => $contact->user_id,
            'phone' => $contact->phone,
            'status' => $contact->phone_exists
        ];
        return $phoneNumbersStatus;
    });


    // Return the paginated response with pagination metadata
    return $this->successResponseWithPagintaion('Contacts fetched successfully', 200, $contacts);
}


    
    
    public function store(Request $request)
    {
        // Get the authenticated user
        $auth_user = auth()->guard('api')->user();
    
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',  // Validate that data is an array
            'data.*.name' => 'required|string|max:255',  // Validate each contact's full_name
            'data.*.phone' => 'required|array|min:1', // Validate each contact's phone_number
        ]);
    
        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Retrieve the contacts data
        $data = $request->data;
        
        $currentTime = Carbon::now();
        foreach ($data as $user) {
            foreach ($user['phone'] as $phone) {
                UserContact::updateOrCreate(
                    [
                        'user_id' => $auth_user->id, // Assuming $auth_user is the logged-in user
                        'phone' => $phone
                    ],
                    [
                        'name' => $user['name']
                    ]
                );
            }
        }
        
        // Check if the insertion was successful and return the appropriate respons
        return $this->successResponse('Contacts created successfully', 200);
    }
    

    public function show($id)
    {
        $contact = UserContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        return response()->json($contact);
    }

    

    public function update(Request $request, $id)
    {
        $auth_user=auth()->guard('api')->user();
        $contact = UserContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }
        if ($contact->user_id !== $auth_user->id) {
            return response()->json(['message' => 'You are not authorized to update this contact'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            //'email' => ['required', 'email', Rule::unique('contacts')->ignore($contact->id)],
            //'message' => 'required',
            'phone' => 'required|unique:contacts,phone,' . $contact->id . ',id,user_id,' . $auth_user->id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json(['message' => 'Contact updated successfully', 'contact' => $contact]);
    }

    public function destroy($id)
    {
        $contact = UserContact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }


}
