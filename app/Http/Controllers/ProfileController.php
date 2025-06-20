<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function profile()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        if(Auth::user()->user_type_id == 1){
            return view('profile.profile');
        } else if (Auth::user()->user_type_id == 2) {
            $spOffice = SpOffice::where('user_id', Auth::user()->id)->first();
            $cities = City::where('status', 1)->where('state_id', $spOffice->state_id)->orderBy('name', 'asc')->get();
            return view('profile.sp-profile', compact('states', 'cities', 'spOffice'));
        } else if (Auth::user()->user_type_id == 3) {
            $policeStation = PoliceStation::where('user_id', Auth::user()->id)->first();
            $cities = City::where('status', 1)->where('state_id', $policeStation->state_id)->orderBy('name', 'asc')->get();
            return view('profile.police-station-profile', compact('states', 'cities', 'policeStation'));
        } else if(Auth::user()->user_type_id == 4){
            $hotel = Hotel::with('ownerDocuments.document')->where('user_id', Auth::user()->id)->first();
            $cities = City::where('status', 1)->where('state_id', $hotel->state_id)->orderBy('name', 'asc')->get();
            $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
            return view('profile.hotel-profile', compact('states', 'cities', 'hotel', 'documents'));
        } else if(Auth::user()->user_type_id == 5){
            $employee = HotelEmployee::with('user', 'employeeDocuments.document')->where('user_id', Auth::user()->id)->first();
            $cities = City::where('status', 1)->where('state_id', $employee->state_id)->orderBy('name', 'asc')->get();
            $documents = Document::where('status', 1)->orderBy('name', 'asc')->get();
            return view('profile.hotel-employee-profile', compact('states', 'cities', 'employee', 'documents'));
        }
        
    }

    public function postChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('The current password is incorrect.');
                    }
                }
            ],
            'newPassword' => 'required|min:6|different:currentPassword',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update password
        $user = Auth::user();
        $user->password = Hash::make($request->newPassword);
        $user->save();

        activiyLog('Password changed by ' . ucfirst(Auth::user()->name));

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
 * @OA\Post(
 *     path="/update-admin-profile",
 *     summary="Update authenticated user's profile",
 *     tags={"Profile"},
 *     operationId="updateProfile",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Profile updated successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required."))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */


    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        activiyLog('Profile updated by ' . ucfirst(Auth::user()->name));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user
        ]);
    }
}
