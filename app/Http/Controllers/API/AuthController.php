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
     *     summary="User login (email or phone)",
     *     description="Logs in a user (only for user types 4 and 5). Users can log in using either their email address or a 10-digit phone number.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"login", "password"},
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     example="user@example.com",
     *                     description="Email or 10-digit phone number. Example: 'user@example.com' or '9876543210'."
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     example="secret123"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="1|XyzAbcTokenString"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Authenticated user details",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="phone", type="string", example="9876543210"),
     *                 @OA\Property(property="user_type_id", type="integer", example=4),
     *                 @OA\Property(property="status", type="integer", example=1)
     *             )
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
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Please enter a valid email or phone number"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="login", type="array", @OA\Items(type="string", example="The login field is required.")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
     *             )
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|min:6',
        ]);

        // Detect whether login is email or phone
        if (filter_var($request->login, FILTER_VALIDATE_EMAIL)) {
            $loginType = 'email';
        } elseif (preg_match('/^[0-9]{10}$/', $request->login)) { // strict 10-digit phone
            $loginType = 'phone';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email or phone number'
            ], 422);
        }

        // Find user by email or phone
        $user = User::where($loginType, $request->login)->first();

        if ($user && Hash::check($request->password, $user->password)) {
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
     *     description="Returns dashboard statistics based on the authenticated user's role (Hotel Owner or Hotel Employee).",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with dashboard data",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="totalEmployees", type="integer", example=12),
     *                     @OA\Property(property="totalBooking", type="integer", example=200),
     *                     @OA\Property(property="totalTransferPendingBookings", type="integer", example=5),
     *                     @OA\Property(property="todayTransferredBookings", type="integer", example=3),
     *                     @OA\Property(
     *                         property="graphData",
     *                         type="object",
     *                         @OA\Property(property="labels", type="array", @OA\Items(type="string", example="01 Jul")),
     *                         @OA\Property(property="dailyBookings", type="array", @OA\Items(type="integer", example=5)),
     *                         @OA\Property(property="dailyTransfers", type="array", @OA\Items(type="integer", example=2))
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="totalBooking", type="integer", example=50),
     *                     @OA\Property(property="totalTransferPendingBookings", type="integer", example=2),
     *                     @OA\Property(property="totalTransferredBookings", type="integer", example=20),
     *                     @OA\Property(property="todayTransferredBookings", type="integer", example=1),
     *                     @OA\Property(
     *                         property="graphData",
     *                         type="object",
     *                         @OA\Property(property="labels", type="array", @OA\Items(type="string", example="01 Jul")),
     *                         @OA\Property(property="dailyBookings", type="array", @OA\Items(type="integer", example=3)),
     *                         @OA\Property(property="dailyTransfers", type="array", @OA\Items(type="integer", example=1))
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
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


    /**
     * @OA\Post(
     *     path="/get-filter-graph-data",
     *     tags={"Authentication"},
     *     summary="Get filtered graph data for bookings and transfers",
     *     description="Retrieves daily hotel bookings and transfer counts between a specified date range for the authenticated hotel owner or employee. Returns empty data for unauthorized users.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering data (Y-m-d format). Defaults to the start of the current month.",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         description="End date for filtering data (Y-m-d format). Defaults to the end of the current month.",
     *         @OA\Schema(type="string", format="date", example="2025-07-15")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Graph data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="labels", type="array",
     *                 @OA\Items(type="string", example="01 Jul")
     *             ),
     *             @OA\Property(property="dailyBookings", type="array",
     *                 @OA\Items(type="integer", example=5)
     *             ),
     *             @OA\Property(property="dailyTransfers", type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */


    public function getFilterGraphData(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        $dates = collect(CarbonPeriod::create($startDate, $endDate)->toArray());
        $labels = $dates->map(fn($date) => $date->format('d M'))->toArray();

        $userType = Auth::user()->user_type_id;
        $hotelID = null;
        $hotelEmployeeID = null;

        if ($userType == 4) {
            $hotelID = Hotel::where('user_id', Auth::id())->value('id');
        } elseif ($userType == 5) {
            $hotelEmployee = HotelEmployee::where('user_id', Auth::id())->first();
            $hotelID = $hotelEmployee->hotel_id ?? null;
            $hotelEmployeeID = $hotelEmployee->id ?? null;
        }

        if (!in_array($userType, [4, 5])) {
            return response()->json([
                'labels' => [],
                'dailyBookings' => [],
                'dailyTransfers' => [],
            ]);
        }

        $bookingQuery = HotelBooking::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('hotel_id', $hotelID)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userType == 5) {
            $bookingQuery->where('hotel_employee_id', $hotelEmployeeID);
        }

        $bookings = $bookingQuery->groupBy('date')->pluck('total', 'date');

        $transferQuery = HotelBooking::selectRaw('DATE(transfer_date) as date, COUNT(*) as total')
            ->where('hotel_id', $hotelID)
            ->where('status', 1)
            ->whereBetween('transfer_date', [$startDate, $endDate]);

        if ($userType == 5) {
            $transferQuery->where('hotel_employee_id', $hotelEmployeeID);
        }

        $transfers = $transferQuery->groupBy('date')->pluck('total', 'date');

        $dailyBookings = [];
        $dailyTransfers = [];

        foreach ($dates as $date) {
            $day = $date->format('Y-m-d');
            $dailyBookings[] = $bookings[$day] ?? 0;
            $dailyTransfers[] = $transfers[$day] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'dailyBookings' => $dailyBookings,
            'dailyTransfers' => $dailyTransfers,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/store-device-token",
     *     summary="Update the user's device token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_token"},
     *             @OA\Property(property="device_token", type="string", example="fcm_device_token_123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device token updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Device token updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The device token field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */


    public function storeDeviceToken(Request $request)
    {
        try {
            $request->validate([
                'device_token' => 'required|string',
            ]);

            $user = Auth::user();

            $user->update([
                'device_token' => $request->device_token
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Device token updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
