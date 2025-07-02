<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


/**
 * @OA\Info(
 *     title="Atithi Suraksha API",
 *     version="v1",
 * )
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 */
class AuthController extends Controller
{

    /**
 * @OA\Post(
 *     path="/login",
 *     tags={"Authentication"},
 *     summary="User login",
 *     description="Logs in a user (only for user types 4 and 5).",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"email", "password"},
 *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *                 @OA\Property(property="password", type="string", format="password", example="secret123")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Login successful"),
 *             @OA\Property(property="redirect", type="string", example="/dashboard")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid credentials")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Unauthorized user type or deactivated account",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You do not have permission to login via the app.")
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
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
 *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
 *             )
 *         )
 *     )
 * )
 */

    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user) {
        // Check if password is correct
        if (Hash::check($request->password, $user->password)) {
            // Allow login only for user_type_id 4 or 5
            if (!in_array($user->user_type_id, [4, 5])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to login via the app.'
                ], 403);
            }

            // Check if account is deactivated
            if ($user->status == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is deactivated, please contact the administrator.'
                ], 403);
            }

            // Allow login
            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;
            activiyLog(ucfirst($user->name) . ' logged in');

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'data' => $user
            ]);
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

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Forgot Password",
     *     description="Send a password reset link to the provided email address.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Reset password link sent successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong!"),
     *             @OA\Property(property="error", type="string", example="Server error details")
     *         )
     *     )
     * )
     */

    public function forgotPassword(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email address not found.',
                'status' => 'error',
            ], 404);
        }

        // Send reset link
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Reset password link sent successfully',
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'message' => 'Reset link could not be sent',
                'status' => 'error',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong!',
            'error' => $e->getMessage(),
            'status' => 'error',
        ], 500);
    }
}

 /**
     * @OA\Post(
     *     path="/reset-password",
     *     summary="Reset Password",
     *     description="Reset the user's password using the provided token.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},    
     *             @OA\Property(property="token", type="string", example="token"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong!"),
     *             @OA\Property(property="error", type="string", example="Server error details")
     *         )
     *     )
     * )
     **/


    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));
                    $user->save();
                }
            );
            return $status === Password::PASSWORD_RESET
                ? response()->json(['message' => 'Password reset successfully'])
                : response()->json(['message' => 'Reset password link sent successfully'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ]);
        }
    }

 /**
     * @OA\Post(
     *     path="/logout",
     *     summary="User Logout",
     *     description="Logs out the authenticated user by deleting the current access token.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No active session found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No active session found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not authenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred during logout: error details here.")
     *         )
     *     )
     * )
     */



public function logout(Request $request)
    {
        try {
            activiyLog(ucfirst($request->user()->name).' logged out');
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
