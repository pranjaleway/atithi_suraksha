<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\HotelOwnerDoc;
use App\Models\Notification;
use App\Models\PoliceStation;
use App\Models\Referral;
use App\Models\SpOffice;
use App\Models\State;
use App\Models\TransferEntry;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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


    // public function dashboard()
    // {
    //     if (!hasPermission('dashboard', 'view')) {
    //         abort(403, 'Unauthorized');
    //     }

    //     if (!Auth::check()) {
    //         return redirect()->route('login');
    //     }

    //     $userType = Auth::user()->user_type_id;

    //     if ($userType == 1) { // Super Admin
    //         $totalSPOffice = User::where('user_type_id', 2)->count();
    //         $totalPoliceStation = User::where('user_type_id', 3)->count();
    //         $hotels = Hotel::all();
    //         $totalHotel = $hotels->count();
    //         $todayTransferredBookings = TransferEntry::whereDate('transfer_date', Carbon::today())->count();

    //         $graphData = $this->generateGraphData(); // All hotels

    //         return view('auth.super-admin-dashboard', compact(
    //             'totalSPOffice',
    //             'totalPoliceStation',
    //             'totalHotel',
    //             'todayTransferredBookings',
    //             'graphData',
    //             'hotels'
    //         ));
    //     }

    //     if ($userType == 2) { // SP Office
    //         $spOfficeID = SpOffice::where('user_id', Auth::user()->id)->value('id');

    //         $policeStationIds = PoliceStation::where('sp_office_id', $spOfficeID)->pluck('id')->toArray();

    //         $hotels = Hotel::whereIn('police_station_id', $policeStationIds)->get();
    //         $hotelIDs = $hotels->pluck('id')->toArray();

    //         $totalPoliceStation = count($policeStationIds);
    //         $totalTransferredBookings = TransferEntry::whereIn('hotel_id', $hotelIDs)
    //             ->distinct('hotel_id', 'transfer_date')
    //             ->count(DB::raw('DISTINCT hotel_id, transfer_date'));


    //         $todayTransferredBookings = TransferEntry::whereDate('transfer_date', Carbon::today())
    //             ->whereIn('hotel_id', $hotelIDs)
    //             ->count();

    //         $graphData = $this->generateGraphData($hotelIDs);

    //         return view('auth.sp-office-dashboard', compact(
    //             'totalPoliceStation',
    //             'totalTransferredBookings',
    //             'todayTransferredBookings',
    //             'graphData',
    //             'hotels'
    //         ));
    //     }

    //     if ($userType == 3) { // Police Station
    //         $policeStationID = PoliceStation::where('user_id', Auth::user()->id)->value('id');

    //         $hotels = Hotel::where('police_station_id', $policeStationID)->get();
    //         $hotelIDs = $hotels->pluck('id')->toArray();

    //         $totalHotel = count($hotelIDs);
    //         $totalTransferredBookings = TransferEntry::whereIn('hotel_id', $hotelIDs)
    //             ->distinct('hotel_id', 'transfer_date')
    //             ->count(DB::raw('DISTINCT hotel_id, transfer_date'));

    //         $todayTransferredBookings = TransferEntry::whereDate('transfer_date', Carbon::today())
    //             ->whereIn('hotel_id', $hotelIDs)
    //             ->count();

    //         $graphData = $this->generateGraphData($hotelIDs);

    //         return view('auth.police-station-dashboard', compact(
    //             'totalHotel',
    //             'totalTransferredBookings',
    //             'todayTransferredBookings',
    //             'graphData',
    //             'hotels'
    //         ));
    //     }
    //     if ($userType == 4) { // Hotel
    //         $hotelID = Hotel::where('user_id', Auth::user()->id)->value('id');

    //         $totalEmployees = HotelEmployee::where('hotel_id', $hotelID)->count();
    //         $totalBooking = HotelBooking::where('hotel_id', $hotelID)
    //             ->whereNull('parent_id')
    //             ->count();
    //         $totalTransferPendingBookings = HotelBooking::where('hotel_id', $hotelID)
    //             ->whereNull('transfer_date')
    //             ->where('status', 0)
    //             ->count();

    //         $todayTransferredBookings = TransferEntry::whereDate('transfer_date', Carbon::today())
    //             ->where('hotel_id', $hotelID)
    //             ->count();

    //         // Monthly graph data
    //         $startOfMonth = Carbon::now()->startOfMonth();
    //         $endOfMonth = Carbon::now()->endOfMonth();
    //         $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

    //         $labels = [];
    //         $dailyBookings = [];
    //         $dailyTransfers = [];

    //         foreach ($dates as $date) {
    //             $labels[] = $date->format('d M');

    //             $bookingCount = HotelBooking::where('hotel_id', $hotelID)
    //                 ->whereDate('created_at', $date) // adjust if your column name differs
    //                 ->count();
    //             $dailyBookings[] = $bookingCount;

    //             $transferCount = HotelBooking::where('hotel_id', $hotelID)
    //                 ->whereDate('transfer_date', $date)
    //                 ->count();
    //             $dailyTransfers[] = $transferCount;
    //         }

    //         $graphData = [
    //             'labels' => $labels,
    //             'dailyBookings' => $dailyBookings,
    //             'dailyTransfers' => $dailyTransfers
    //         ];

    //         return view('auth.hotel-dashboard', compact(
    //             'totalEmployees',
    //             'totalBooking',
    //             'totalTransferPendingBookings',
    //             'todayTransferredBookings',
    //             'graphData'
    //         ));
    //     }

    //     if ($userType == 5) {
    //         $hotelEmployeeID = HotelEmployee::where('user_id', Auth::user()->id)->value('id');
    //         $hotelID = HotelEmployee::where('id', $hotelEmployeeID)->value('hotel_id');
    //         $totalBooking = HotelBooking::where('hotel_id', $hotelID)
    //             ->where('hotel_employee_id', $hotelEmployeeID)
    //             ->whereNull('parent_id')
    //             ->count();
    //         $totalTransferPendingBookings = HotelBooking::where('hotel_id', $hotelID)
    //             ->where('hotel_employee_id', $hotelEmployeeID)
    //             ->whereNull('transfer_date')
    //             ->where('status', 0)
    //             ->count();
    //         $totalTransferredBookings = TransferEntry::where('hotel_id', $hotelID)
    //             ->where('hotel_employee_id', $hotelEmployeeID)
    //             ->distinct('hotel_id', 'transfer_date')
    //             ->count(DB::raw('DISTINCT hotel_id, transfer_date'));
    //         $todayTransferredBookings = TransferEntry::whereDate('transfer_date', Carbon::today())
    //             ->where('hotel_id', $hotelID)
    //             ->where('hotel_employee_id', $hotelEmployeeID)
    //             ->count();
    //         // Monthly graph data
    //         $startOfMonth = Carbon::now()->startOfMonth();
    //         $endOfMonth = Carbon::now()->endOfMonth();
    //         $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

    //         $labels = [];
    //         $dailyBookings = [];
    //         $dailyTransfers = [];

    //         foreach ($dates as $date) {
    //             $labels[] = $date->format('d M');

    //             $bookingCount = HotelBooking::where('hotel_id', $hotelID)
    //                 ->where('hotel_employee_id', $hotelEmployeeID)
    //                 ->whereDate('created_at', $date) // adjust if your column name differs
    //                 ->count();
    //             $dailyBookings[] = $bookingCount;

    //             $transferCount = HotelBooking::where('hotel_id', $hotelID)
    //                 ->where('hotel_employee_id', $hotelEmployeeID)
    //                 ->whereDate('transfer_date', $date)
    //                 ->count();
    //             $dailyTransfers[] = $transferCount;
    //         }

    //         $graphData = [
    //             'labels' => $labels,
    //             'dailyBookings' => $dailyBookings,
    //             'dailyTransfers' => $dailyTransfers
    //         ];
    //         return view('auth.hotel-employee-dashboard', compact(
    //             'totalBooking',
    //             'totalTransferPendingBookings',
    //             'totalTransferredBookings',
    //             'todayTransferredBookings',
    //             'graphData'
    //         ));
    //     }



    //     return redirect()->route('dashboard');
    // }


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
     *                 @OA\Property(property="police_station_id", type="integer", example=1),
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
            'aadhar_number' => 'required|numeric|digits:12',
            'pan_number' => 'required|string|max:10',
            'license_number' => 'nullable|string|max:255|unique:hotels,license_number',
            'address' => 'required|string',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'police_station_id' => 'required|exists:police_stations,id',
            'pincode' => 'required|numeric|digits:6',
            'password' => 'required|string|min:6|confirmed',
            'document_id' => 'required',
            'document' => 'required|array',
            'document.*' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ], [
            'email.unique' => 'This email has already been taken.',
            'contact_number.unique' => 'This contact number has already been taken.',
            'city_id.exists' => 'The selected city is invalid.',
            'state_id.exists' => 'The selected state is invalid.',
            'password.confirmed' => 'The confirmed password does not match.',
            'document.*.required' => 'Please upload at least one document.',
            'document.*.file' => 'Each document must be a valid file.',
            'document.*.mimes' => 'Only JPG, JPEG, PNG, or PDF files are allowed.',
            'police_station_id.exists' => 'The selected police station is invalid.'
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
            'pincode',
            'police_station_id'
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
        $spId = PoliceStation::where('id', $request->police_station_id)->value('sp_office_id');

        Notification::create([
            'user_id' => $user->id,
            'title' => 'New Hotel Registration',
            'message' => 'New hotel ' . $hotels->hotel_name . ' has been registered.',
            'sp_id' => $spId
        ]);

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

    public function dashboard()
    {
        if (!hasPermission('dashboard', 'view')) {
            abort(403, 'Unauthorized');
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userType = $user->user_type_id;

        switch ($userType) {
            case 1: // Super Admin
                $totalSPOffice = User::where('user_type_id', 2)->count();
                $totalPoliceStation = User::where('user_type_id', 3)->count();
                $hotels = Hotel::all();
                $totalHotel = $hotels->count();
                $todayTransferredBookings = $this->getTodayTransferredBookings();
                $graphData = $this->generateGraphData();

                return view('auth.super-admin-dashboard', compact(
                    'totalSPOffice',
                    'totalPoliceStation',
                    'totalHotel',
                    'todayTransferredBookings',
                    'graphData',
                    'hotels'
                ));

            case 2: // SP Office
                $spOfficeID = SpOffice::where('user_id', $user->id)->value('id');
                $policeStationIds = PoliceStation::where('sp_office_id', $spOfficeID)->pluck('id')->toArray();
                $hotels = Hotel::whereIn('police_station_id', $policeStationIds)->get();
                $hotelIDs = $hotels->pluck('id')->toArray();

                $totalPoliceStation = count($policeStationIds);
                $totalTransferredBookings = $this->countDistinctTransfers($hotelIDs);
                $todayTransferredBookings = $this->getTodayTransferredBookings($hotelIDs);
                $graphData = $this->generateGraphData($hotelIDs);

                return view('auth.sp-office-dashboard', compact(
                    'totalPoliceStation',
                    'totalTransferredBookings',
                    'todayTransferredBookings',
                    'graphData',
                    'hotels'
                ));

            case 3: // Police Station
                $policeStationID = PoliceStation::where('user_id', $user->id)->value('id');
                $hotels = Hotel::where('police_station_id', $policeStationID)->get();
                $hotelIDs = $hotels->pluck('id')->toArray();

                $totalHotel = count($hotelIDs);
                $totalTransferredBookings = $this->countDistinctTransfers($hotelIDs);
                $todayTransferredBookings = $this->getTodayTransferredBookings($hotelIDs);
                $graphData = $this->generateGraphData($hotelIDs);

                return view('auth.police-station-dashboard', compact(
                    'totalHotel',
                    'totalTransferredBookings',
                    'todayTransferredBookings',
                    'graphData',
                    'hotels'
                ));

            case 4: // Hotel
                $hotelID = Hotel::where('user_id', $user->id)->value('id');

                $totalEmployees = HotelEmployee::where('hotel_id', $hotelID)->count();
                $totalBooking = $this->countBookings($hotelID);
                $totalTransferPendingBookings = $this->countPendingTransfers($hotelID);
                $todayTransferredBookings = $this->getTodayTransferredBookings([$hotelID]);
                $graphData = $this->generateHotelGraphData($hotelID);

                return view('auth.hotel-dashboard', compact(
                    'totalEmployees',
                    'totalBooking',
                    'totalTransferPendingBookings',
                    'todayTransferredBookings',
                    'graphData'
                ));

            case 5: // Hotel Employee
                $hotelEmployeeID = HotelEmployee::where('user_id', $user->id)->value('id');
                $hotelID = HotelEmployee::where('id', $hotelEmployeeID)->value('hotel_id');

                $totalBooking = $this->countBookings($hotelID, $hotelEmployeeID);
                $totalTransferPendingBookings = $this->countPendingTransfers($hotelID, $hotelEmployeeID);
                $totalTransferredBookings = $this->countDistinctTransfers([$hotelID], $hotelEmployeeID);
                $todayTransferredBookings = $this->getTodayTransferredBookings([$hotelID], $hotelEmployeeID);
                $graphData = $this->generateHotelGraphData($hotelID, $hotelEmployeeID);

                return view('auth.hotel-employee-dashboard', compact(
                    'totalBooking',
                    'totalTransferPendingBookings',
                    'totalTransferredBookings',
                    'todayTransferredBookings',
                    'graphData'
                ));

            default:
                return redirect()->route('dashboard');
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
            ->whereNull('parent_id')
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

    private function generateGraphData(array $hotelIds = [])
    {
        $dates = CarbonPeriod::create(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
        $labels = [];
        $bookingCounts = [];

        foreach ($dates as $date) {
            $query = TransferEntry::whereDate('transfer_date', $date);
            if (!empty($hotelIds)) {
                $query->whereIn('hotel_id', $hotelIds);
            }
            $labels[] = $date->format('d M');
            $bookingCounts[] = $query->count();
        }

        return ['labels' => $labels, 'data' => $bookingCounts];
    }



    // private function generateGraphData(array $hotelIds = [])
    // {
    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();

    //     $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);
    //     $labels = [];
    //     $bookingCounts = [];

    //     foreach ($dates as $date) {
    //         $query = TransferEntry::whereDate('transfer_date', $date);

    //         if (!empty($hotelIds)) {
    //             $query->whereIn('hotel_id', $hotelIds);
    //         }

    //         $labels[] = $date->format('d M');
    //         $bookingCounts[] = $query->count();
    //     }

    //     return [
    //         'labels' => $labels,
    //         'data' => $bookingCounts
    //     ];
    // }


    public function getFilterGraphData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();
        $dates = CarbonPeriod::create($startDate, $endDate);

        $labels = [];
        $dailyBookings = [];
        $dailyTransfers = [];
        $userType = Auth::user()->user_type_id;

        $hotelIDs = []; // For user_type 1, 2, 3
        $hotelID = null;
        $hotelEmployeeID = null;

        // Prepare hotelID(s) based on role
        if ($userType == 1) {
            $hotelIDs = Hotel::pluck('id')->toArray();
        } elseif ($userType == 2) {
            $spOfficeID = SpOffice::where('user_id', Auth::id())->value('id');
            $policeStationIds = PoliceStation::where('sp_office_id', $spOfficeID)->pluck('id')->toArray();
            $hotelIDs = Hotel::whereIn('police_station_id', $policeStationIds)->pluck('id')->toArray();
        } elseif ($userType == 3) {
            $policeStationID = PoliceStation::where('user_id', Auth::id())->value('id');
            $hotelIDs = Hotel::where('police_station_id', $policeStationID)->pluck('id')->toArray();
        } elseif ($userType == 4) {
            $hotelID = Hotel::where('user_id', Auth::id())->value('id');
        } elseif ($userType == 5) {
            $hotelEmployeeID = HotelEmployee::where('user_id', Auth::id())->value('id');
            $hotelID = HotelEmployee::where('id', $hotelEmployeeID)->value('hotel_id');
        }

        foreach ($dates as $date) {
            $labels[] = $date->format('d M');

            if (in_array($userType, [4, 5])) {
                // Hotel / Hotel Employee
                $bookingQuery = HotelBooking::where('hotel_id', $hotelID)
                    ->whereDate('created_at', $date);

                $transferQuery = HotelBooking::where('hotel_id', $hotelID)
                    ->whereDate('transfer_date', $date)
                    ->where('status', 1);

                if ($userType == 5) {
                    $bookingQuery->where('hotel_employee_id', $hotelEmployeeID);
                    $transferQuery->where('hotel_employee_id', $hotelEmployeeID);
                }

                $dailyBookings[] = $bookingQuery->count();
                $dailyTransfers[] = $transferQuery->count();
            } else {
                // Admin / SP Office / Police Station
                $transferQuery = TransferEntry::whereDate('transfer_date', $date);

                // Hotel filter from request or derived hotelIDs
                $hotelFilterId = $request->hotel_id;

                if (!empty($hotelFilterId)) {
                    $transferQuery->where('hotel_id', $hotelFilterId);
                } elseif (!empty($hotelIDs)) {
                    $transferQuery->whereIn('hotel_id', $hotelIDs);
                }

                $dailyTransfers[] = $transferQuery->count();
            }
        }

        // Response
        if (in_array($userType, [4, 5])) {
            return response()->json([
                'labels' => $labels,
                'dailyBookings' => $dailyBookings,
                'dailyTransfers' => $dailyTransfers,
            ]);
        } else {
            return response()->json([
                'labels' => $labels,
                'data' => $dailyTransfers,
            ]);
        }
    }

}