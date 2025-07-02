<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelOwnerDoc;
use App\Models\Referral;
use App\Models\State;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;


class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Check if password is correct
            if (Hash::check($request->password, $user->password)) {
                // Check if account is deactivated
                if ($user->status == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is deactivated, please contact to the administrator.'
                    ], 403);
                }

                // Allow login only for active accounts
                if ($user->status == 1) {
                    Auth::login($user);
                    activiyLog(ucfirst($user->name) . ' logged in');

                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful',
                        'redirect' => route('dashboard')
                    ]);
                }
            }

            // If password is incorrect
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // If user not found
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }


    public function dashboard()
    {
        if (Auth::check()) {
            if (!hasPermission('dashboard', 'view')) {
                abort(403, 'Unauthorized');
            }
            return view('auth.dashboard');
        } else {
            return redirect()->route('login');
        }
    }


    public function logout()
    {
        activiyLog(ucfirst(Auth::user()->name) . ' logged out');
        Auth::logout();
        return redirect()->route('login');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }


    public function postForgotPassword(Request $request)
    {
        // Validate the email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        $ipAddress = $request->ip() ?? '127.0.0.1'; // Ensure IP is always set


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user details
        $user = User::where('email', $request->email)->first();

        // Send password reset link
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            $currentTime = Carbon::now('Asia/Kolkata');
            $formattedDate = $currentTime->format('m-d-Y h:i A');

            // Ensure user exists before logging
            if ($user) {
                ActivityLog::create([
                    'activity' => 'Reset password link sent to ' . $request->email,
                    'ip_address' => $ipAddress,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'date' => $formattedDate,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reset password link sent to your email.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send reset link. Try again later.'
        ], 500);
    }




    public function resetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);

    }

    public function postResetPassword(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
            'confirm-password' => 'required|same:password',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        // Attempt password reset
        $status = Password::reset(
            $request->only('email', 'password', 'confirm-password', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        // Return response based on reset status
        if ($status === Password::PASSWORD_RESET) {
            $currentTime = Carbon::now('Asia/Kolkata');
            $formattedDate = $currentTime->format('m-d-Y h:i A');

            // Log activity
            $log = [
                'activity' => ucfirst($user->name) . ' successfully reset their password.',
                'ip_address' => $request->ip(),
                'user_id' => $user->id,
                'user_name' => $user->name,
                'date' => $formattedDate,
            ];

            ActivityLog::create($log);
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
                'redirect' => route('login')
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token.'
        ], 422);
    }

    public function hotelSignup()
    {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->get();
        return view('auth.hotel-signup', compact('states', 'documents'));
    }

   /**
 * @OA\Post(
 *     path="/post-hotel-signup",
 *     tags={"Authentication"},
 *     summary="Register a new hotel",
 *     description="Handles hotel registration along with associated user and documents (in JSON).",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={
 *                     "hotel_name", "owner_name", "email", "contact_number", "owner_contact_number",
 *                     "aadhar_number", "pan_number", "license_number", "address",
 *                     "state_id", "city_id", "pincode", "password", "password_confirmation"
 *                 },
 *                 @OA\Property(property="hotel_name", type="string", example="Hotel Paradise"),
 *                 @OA\Property(property="owner_name", type="string", example="John Smith"),
 *                 @OA\Property(property="email", type="string", format="email", example="owner@example.com"),
 *                 @OA\Property(property="contact_number", type="string", example="9876543210"),
 *                 @OA\Property(property="owner_contact_number", type="string", example="9123456780"),
 *                 @OA\Property(property="aadhar_number", type="string", example="123456789012"),
 *                 @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
 *                 @OA\Property(property="license_number", type="string", example="LIC123456"),
 *                 @OA\Property(property="address", type="string", example="123 Street Name, City"),
 *                 @OA\Property(property="state_id", type="integer", example=1),
 *                 @OA\Property(property="city_id", type="integer", example=10),
 *                 @OA\Property(property="pincode", type="string", example="400001"),
 *                 @OA\Property(property="password", type="string", format="password", example="secret123"),
 *                 @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
 *                 @OA\Property(
 *                     property="documents",
 *                     type="array",
 *                     description="Array of uploaded documents in base64 or metadata",
 *                     @OA\Items(
 *                         type="object",
 *                         required={"document_id", "document_path"},
 *                         @OA\Property(property="document_id", type="integer", example=1),
 *                         @OA\Property(property="document_path", type="string", example="base64encodedstring==")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Hotel registration success",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Please wait for admin approval")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="This email has already been taken."))
 *             )
 *         )
 *     )
 * )
 */


    public function postHotelSignup(Request $request)
    {
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:hotels,contact_number',
            'owner_contact_number' => 'required|numeric|digits:10|unique:users,phone',
            'aadhar_number' => 'required|numeric|digits:12|unique:hotels,aadhar_number',
            'pan_number' => 'required|string|max:10|unique:hotels,pan_number',
            'license_number' => 'required|string|max:255|unique:hotels,license_number',
            'address' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'pincode' => 'required|numeric|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'city_id.exists' => 'The selected city is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
            'password.confirmed' => 'The confirmed password does not match.',
        ]);

        $hotels = Hotel::create($request->only([
            'hotel_name',
            'owner_name',
            'email',
            'contact_number',
            'owner_contact_number',
            'aadhar_number',
            'pan_number',
            'license_number',
            'address',
            'state_id',
            'city_id',
            'pincode'
        ]));

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $path = $file->store('hotel_owner_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelOwnerDoc::create([
                    'hotel_id' => $hotels->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = $hotels->user()->create([
            'name' => $request->owner_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 4,
            'role' => UserType::where('id', 4)->value('user_type'),
            'phone' => $request->owner_contact_number,
            'status' => 0
        ]);

        $hotels->update(['user_id' => $user->id, 'status' => 0]);

        if ($user) {
            $ipAddress = $request->ip() ?? '127.0.0.1';
            $currentTime = Carbon::now('Asia/Kolkata');
            $formattedDate = $currentTime->format('m-d-Y h:i A');
            ActivityLog::create([
                'activity' => 'New hotel ' . $hotels->hotel_name . ' has been registered.',
                'ip_address' => $ipAddress,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'date' => $formattedDate,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Please wait for admin approval',
        ]);
    }

}
