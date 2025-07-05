<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\HotelEmployeeDoc;
use App\Models\RoomNumber;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class APIHotelController extends Controller
{
    /**
 * @OA\Get(
 *     path="/get-rooms",
 *     tags={"Hotels"},
 *     summary="Get rooms for the authenticated hotel",
 *     security={{"bearerAuth":{}}},
 *     description="Retrieves a paginated list of room numbers for the authenticated hotel along with permission flags.",
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with paginated rooms and permissions",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="data", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="room_number", type="string", example="101"),
 *                         @OA\Property(property="room_type", type="string", example="Deluxe"),
 *                         @OA\Property(property="status", type="string", example="available")
 *                     )
 *                 ),
 *                 @OA\Property(property="last_page", type="integer", example=5),
 *                 @OA\Property(property="total", type="integer", example=50)
 *             ),
 *             @OA\Property(property="canAdd", type="boolean", example=true),
 *             @OA\Property(property="canEdit", type="boolean", example=true),
 *             @OA\Property(property="canDelete", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
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

    public function getRooms(Request $request)
    {
        try{
        if (!hasPermission('room-master', 'view')) {
            abort(403, 'Unauthorized');
        }
       
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
            $data = RoomNumber::where('hotel_id', $hotelId)
                ->select('id', 'room_number', 'room_type', 'status')
                ->paginate(10);

            $canAdd = hasPermission('room-master', 'add');
            $canEdit = hasPermission('room-master', 'edit');
            $canDelete = hasPermission('room-master', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
 * @OA\Post(
 *     path="/add-room",
 *     tags={"Hotels"},
 *     summary="Add a new room for the authenticated hotel",
 *     security={{"bearerAuth":{}}},
 *     description="Creates a new room for the authenticated hotel's account. If the room number was previously soft-deleted, it will be restored.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"room_number", "room_type"},
 *             @OA\Property(property="room_number", type="string", example="101"),
 *             @OA\Property(property="room_type", type="string", enum={"AC", "NON-AC"}, example="AC")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Room created or restored successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Room Number created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="room_number", type="string", example="101"),
 *                 @OA\Property(property="room_type", type="string", example="AC"),
 *                 @OA\Property(property="hotel_id", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", example="2024-01-01T12:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", example="2024-01-01T12:00:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or room number already exists",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Room Number already exists for this hotel.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */


    public function addRoom(Request $request){
        try{
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');

        // Check if room exists (including soft-deleted), scoped by hotel_id
        $existingRoom = RoomNumber::withTrashed()
            ->where('room_number', $request['room_number'])
            ->where('hotel_id', $hotelId)
            ->first();

        if ($existingRoom) {
            if ($existingRoom->trashed()) {
                // Restore soft-deleted room
                $existingRoom->restore();

                return response()->json([
                    'data' => $existingRoom,
                    'status' => 'success',
                    'message' => 'Room Number restored successfully'
                ]);
            }

            // Active room already exists
            return response()->json([
                'status' => 'error',
                'message' => 'Room Number already exists for this hotel.'
            ], 422);
        }

        // Validate room_number as unique for this hotel_id
        $validatedData = $request->validate([
            'room_number' => [
                'required',
                'string',
                Rule::unique('room_numbers', 'room_number')->where(fn($query) => $query->where('hotel_id', $hotelId)),
            ],
            'room_type' => 'required|in:AC,NON-AC',
        ]);

        $validatedData['hotel_id'] = $hotelId;

        $data = RoomNumber::create($validatedData);

        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => 'Room Number created successfully'
        ]);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
 * @OA\Get(
 *     path="/get-employees",
 *     tags={"Hotels"},
 *     summary="Get employees for the authenticated hotel",
 *     description="Retrieves the list of all employees associated with the hotel of the authenticated user.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with employee data",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="employee_name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *                     @OA\Property(property="contact_number", type="string", example="9876543210"),
 *                     @OA\Property(property="aadhar_number", type="string", example="123456789012"),
 *                     @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
 *                     @OA\Property(property="address", type="string", example="123 Main St"),
 *                     @OA\Property(property="state_id", type="integer", example=1),
 *                     @OA\Property(property="city_id", type="integer", example=1),
 *                     @OA\Property(property="pincode", type="string", example="400001"),
 *                     @OA\Property(property="hotel_id", type="integer", example=1),
 *                     @OA\Property(property="user_id", type="integer", example=10),
 *                     @OA\Property(property="created_at", type="string", example="2024-01-01T12:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2024-01-01T12:00:00Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */


    public function getEmployees(Request $request) {
        try {
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
            $data = HotelEmployee::where('hotel_id', $hotelId)->paginate(10);
            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

   /**
 * @OA\Post(
 *     path="/add-employee",
 *     tags={"Hotels"},
 *     summary="Add a new hotel employee",
 *     description="Creates a new hotel employee and associates them with a user account.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "employee_name", "email", "contact_number", "aadhar_number",
 *                 "pan_number", "address", "state_id", "city_id", "pincode", "password", "password_confirmation"
 *             },
 *             @OA\Property(property="employee_name", type="string", example="Jane Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="employee@example.com"),
 *             @OA\Property(property="contact_number", type="string", example="9876543210"),
 *             @OA\Property(property="aadhar_number", type="string", example="123456789012"),
 *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
 *             @OA\Property(property="address", type="string", example="456 Main Street"),
 *             @OA\Property(property="state_id", type="integer", example=1),
 *             @OA\Property(property="city_id", type="integer", example=5),
 *             @OA\Property(property="pincode", type="string", example="400001"),
 *             @OA\Property(property="password", type="string", format="password", example="strongpassword"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="strongpassword")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Hotel employee created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Hotel employee created successfully"),
 *             @OA\Property(property="redirect", type="string", example="/hotel-employees")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="This email has already been taken."))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */
    public function addEmployee(Request $request) {
        try {
           $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|numeric|digits:10|unique:users,phone',
            'aadhar_number' => 'required|numeric|digits:12|unique:hotel_employees,aadhar_number',
            'pan_number' => 'required|string|max:10|unique:hotel_employees,pan_number',
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
        $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
        $request->merge(['hotel_id' => $hotelId]);

        $employee = HotelEmployee::create($request->only([
            'employee_name',
            'email',
            'contact_number',
            'aadhar_number',
            'pan_number',
            'address',
            'state_id',
            'city_id',
            'pincode',
            'hotel_id'
        ]));

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $documentId => $file) {
                $path = $file->store('hotel_employee_documents', 'public'); // stores in storage/app/public/hotel_documents

                HotelEmployeeDoc::create([
                    'hotel_employee_id' => $employee->id,
                    'document_id' => $documentId,
                    'document_path' => $path,
                ]);
            }
        }

        $user = $employee->user()->create([
            'name' => $request->employee_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 5,
            'role' => UserType::where('id', 5)->value('user_type'),
            'phone' => $request->contact_number,
        ]);

        $employee->update(['user_id' => $user->id]);

        activiyLog('Hotel employee ' . $employee->employee_name . ' created by ' . ucfirst(Auth::user()->name));

        return response()->json([
            'status' => 'success',
            'message' => 'Hotel employee created successfully',
            'redirect' => route('hotel-employees')
        ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
 * @OA\Get(
 *     path="/get-bookings",
 *     tags={"Hotels"},
 *     summary="Get hotel bookings",
 *     description="Retrieves hotel bookings for the authenticated hotel owner or employee. Hotel owners see all their hotel bookings; employees see only their own.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of hotel bookings",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="hotel_id", type="integer", example=5),
 *                     @OA\Property(property="hotel_employee_id", type="integer", example=3),
 *                     @OA\Property(property="state_id", type="integer", example=1),
 *                     @OA\Property(property="city_id", type="integer", example=10),
 *                     @OA\Property(property="check_in_date", type="string", format="date", example="2025-06-01"),
 *                     @OA\Property(property="check_out_date", type="string", format="date", example="2025-06-05"),
 *                     @OA\Property(property="status", type="string", example="confirmed"),
 *                     @OA\Property(property="hotel", type="object",
 *                         @OA\Property(property="id", type="integer", example=5),
 *                         @OA\Property(property="hotel_name", type="string", example="Grand Palace Hotel")
 *                     ),
 *                     @OA\Property(property="hotelEmployee", type="object",
 *                         @OA\Property(property="id", type="integer", example=3),
 *                         @OA\Property(property="employee_name", type="string", example="John Smith")
 *                     ),
 *                     @OA\Property(property="state", type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Maharashtra")
 *                     ),
 *                     @OA\Property(property="city", type="object",
 *                         @OA\Property(property="id", type="integer", example=10),
 *                         @OA\Property(property="name", type="string", example="Mumbai")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */


    public function getBookings(Request $request) {
        try {
            $query = HotelBooking::with(['hotel', 'hotelEmployee', 'state', 'city'])->where('parent_id', null);
           if (Auth::user()->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
                $data = $query->where('hotel_id', $hotelId)->orderBy('id', 'desc')->paginate(10);
            } else if (Auth::user()->user_type_id == 5) {
                $employeeID = HotelEmployee::where('user_id', Auth::user()->id)->value('id');
                $data = $query->where('hotel_employee_id', $employeeID)->orderBy('id', 'desc')->paginate(10);
            } else {
                $data = [];
            }

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
