<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Referral;
use App\Models\User;
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
            return view('auth.dashboard');
        } else {
            return redirect()->route('login');
        }
    }


    public function logout()
    {
        activiyLog(ucfirst(Auth::user()->name).' logged out');
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
    
}
