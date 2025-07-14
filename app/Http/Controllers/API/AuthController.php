<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\TransferEntry;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * @OA\Get(
     *     path="/dashboard",
     *     tags={"Authentication"},
     *     summary="Get dashboard statistics",
     *     description="Retrieves dashboard statistics based on the authenticated user's role. Hotel owners see their hotel's statistics, while hotel employees see statistics for their assigned hotel.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with dashboard statistics",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema( 
     *                     @OA\Property(property="totalEmployees", type="integer", example=15),
     *                     @OA\Property(property="totalBooking", type="integer", example=100),
     *                     @OA\Property(property="totalTransferPendingBookings", type="integer", example=5),
     *                     @OA\Property(property="todayTransferredBookings", type="integer", example=10)
     *                 ),
     *                 @OA\Schema( 
     *                     @OA\Property(property="totalBooking", type="integer", example=20),
     *                     @OA\Property(property="totalTransferPendingBookings", type="integer", example=2),
     *                     @OA\Property(property="totalTransferredBookings", type="integer", example=15),
     *                     @OA\Property(property="todayTransferredBookings", type="integer", example=4)
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function dashboard(Request $request)
    {
        try {
            $user = Auth::user();
            $userType = $user->user_type_id;

            switch ($userType) {
                case 4: // Hotel
                    $hotelID = Hotel::where('user_id', $user->id)->value('id');

                    $totalEmployees = HotelEmployee::where('hotel_id', $hotelID)->count();
                    $totalBooking = $this->countBookings($hotelID);
                    $totalTransferPendingBookings = $this->countPendingTransfers($hotelID);
                    $todayTransferredBookings = $this->getTodayTransferredBookings([$hotelID]);
                    $graphData = $this->generateHotelGraphData($hotelID);

                    return response()->json([
                        'totalEmployees' => $totalEmployees,
                        'totalBooking' => $totalBooking,
                        'totalTransferPendingBookings' => $totalTransferPendingBookings,
                        'todayTransferredBookings' => $todayTransferredBookings,
                        'graphData' => $graphData
                    ]);

                case 5: // Hotel Employee
                    $hotelEmployeeID = HotelEmployee::where('user_id', $user->id)->value('id');
                    $hotelID = HotelEmployee::where('id', $hotelEmployeeID)->value('hotel_id');

                    $totalBooking = $this->countBookings($hotelID, $hotelEmployeeID);
                    $totalTransferPendingBookings = $this->countPendingTransfers($hotelID, $hotelEmployeeID);
                    $totalTransferredBookings = $this->countDistinctTransfers([$hotelID], $hotelEmployeeID);
                    $todayTransferredBookings = $this->getTodayTransferredBookings([$hotelID], $hotelEmployeeID);
                    $graphData = $this->generateHotelGraphData($hotelID, $hotelEmployeeID);
                    return response()->json([
                        'totalBooking' => $totalBooking,
                        'totalTransferPendingBookings' => $totalTransferPendingBookings,
                        'totalTransferredBookings' => $totalTransferredBookings,
                        'todayTransferredBookings' => $todayTransferredBookings,
                        'graphData' => $graphData
                    ]);

                default:
                    return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function getTodayTransferredBookings(array $hotelIDs = [], $hotelEmployeeID = null)
    {
        $query = TransferEntry::whereDate('transfer_date', Carbon::today());

        if (!empty($hotelIDs)) {
            $query->whereIn('hotel_id', $hotelIDs);
        }

        if ($hotelEmployeeID) {
            $query->where('hotel_employee_id', $hotelEmployeeID);
        }

        return $query->count();
    }

    private function countDistinctTransfers(array $hotelIDs = [], $hotelEmployeeID = null)
    {
        $query = TransferEntry::whereIn('hotel_id', $hotelIDs);

        if ($hotelEmployeeID) {
            $query->where('hotel_employee_id', $hotelEmployeeID);
        }

        return $query->count(DB::raw('DISTINCT hotel_id, transfer_date'));
    }

    private function countBookings($hotelID, $hotelEmployeeID = null)
    {
        $query = HotelBooking::where('hotel_id', $hotelID)->whereNull('parent_id');

        if ($hotelEmployeeID) {
            $query->where('hotel_employee_id', $hotelEmployeeID);
        }

        return $query->count();
    }

    private function countPendingTransfers($hotelID, $hotelEmployeeID = null)
    {
        $query = HotelBooking::where('hotel_id', $hotelID)
            ->whereNull('transfer_date')
            ->where('status', 0);

        if ($hotelEmployeeID) {
            $query->where('hotel_employee_id', $hotelEmployeeID);
        }

        return $query->count();
    }

    private function generateHotelGraphData($hotelID, $hotelEmployeeID = null)
    {
        $dates = CarbonPeriod::create(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $labels = [];
        $dailyBookings = [];
        $dailyTransfers = [];

        foreach ($dates as $date) {
            $labels[] = $date->format('d M');

            $bookingQuery = HotelBooking::where('hotel_id', $hotelID)->whereDate('created_at', $date);
            $transferQuery = HotelBooking::where('hotel_id', $hotelID)->whereDate('transfer_date', $date);

            if ($hotelEmployeeID) {
                $bookingQuery->where('hotel_employee_id', $hotelEmployeeID);
                $transferQuery->where('hotel_employee_id', $hotelEmployeeID);
            }

            $dailyBookings[] = $bookingQuery->count();
            $dailyTransfers[] = $transferQuery->count();
        }

        return [
            'labels' => $labels,
            'dailyBookings' => $dailyBookings,
            'dailyTransfers' => $dailyTransfers
        ];
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
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            activiyLog(ucfirst($user->name) . ' logged out');
            $user->currentAccessToken()->delete();

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
