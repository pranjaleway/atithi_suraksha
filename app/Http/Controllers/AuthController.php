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

        // Ensure user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            // Allow only roles 0, 1, 2
            // Super admin, admin, employee
            // if (in_array($user->role, [0, 1,2 ])) {
            Auth::login($user);
            activiyLog(ucfirst($user->name) . ' logged in');
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('dashboard')
            ]);
            // } else {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized Access'
            //     ], 401);
            // }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
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

    public function hotelSignup() {
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        $documents = Document::where('status', 1)->get();
        return view('auth.hotel-signup', compact('states', 'documents'));
    }

    public function postHotelSignup(Request $request) {
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

        return response()->json([
            'status' => 'success',
            'message' => 'Please wait for admin approval',
        ]);
    }

}
