<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelEmployee;
use App\Models\HotelEmployeeDoc;
use App\Models\RoomNumber;
use App\Models\State;
use App\Models\TransferEntry;
use App\Models\User;
use App\Models\UserType;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class APIHotelController extends Controller
{
    /**
     * @OA\Post(
     *     path="/get-rooms",
     *     tags={"Hotels"},
     *     summary="Get rooms for the authenticated hotel",
     *     security={{"bearerAuth":{}}},
     *     description="Retrieves a paginated list of room numbers for the authenticated hotel along with permission flags. Supports optional search by room number, room type, or filtering by specific room ID.",
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter rooms by room number or room type",
     *         @OA\Schema(type="string", example="Deluxe")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="Filter by specific room ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
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
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
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
    public function getRooms(Request $request)
    {
        try {
            if (!hasPermission('room-master', 'view')) {
                abort(403, 'Unauthorized');
            }

            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');

            $query = RoomNumber::where('hotel_id', $hotelId)
                ->select('id', 'room_number', 'room_type', 'status');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('room_number', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('room_type', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->id) {
                $query->where('id', $request->id);
            }

            $data = $query->paginate(10);

            $canAdd = hasPermission('room-master', 'add');
            $canEdit = hasPermission('room-master', 'edit');
            $canDelete = hasPermission('room-master', 'delete');

            return response()->json([
                'data' => $data,
                'canAdd' => $canAdd,
                'canEdit' => $canEdit,
                'canDelete' => $canDelete,
            ]);

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


    public function addRoom(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/delete-room",
     *     tags={"Hotels"},
     *     summary="Delete a room by ID",
     *     description="Deletes a room record from the authenticated hotel by the provided room ID.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         description="The ID of the room to be deleted",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful deletion response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Room Number deleted successfully")
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


    public function deleteRoom(Request $request)
    {
        try {
            RoomNumber::where('id', $request->id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Room Number deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/update-room",
     *     tags={"Hotels"},
     *     summary="Update a room's details",
     *     description="Updates a room's number and type for the authenticated hotel. Room numbers must be unique within the hotel.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "room_number", "room_type"},
     *             @OA\Property(property="id", type="integer", example=5, description="ID of the room to update"),
     *             @OA\Property(property="room_number", type="string", example="101", description="New room number"),
     *             @OA\Property(property="room_type", type="string", enum={"AC", "NON-AC"}, example="AC", description="Room type")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Room updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Room Number updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="room_number", type="string", example="101"),
     *                 @OA\Property(property="room_type", type="string", example="AC"),
     *                 @OA\Property(property="status", type="string", example="available"),
     *                 @OA\Property(property="hotel_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2025-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-01-02T12:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Room not found.")
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


    public function updateRoom(Request $request)
    {
        try {
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
            $data = RoomNumber::where('hotel_id', $hotelId)
                ->where('id', $request->id)
                ->first();
            if (!$data) {
                return response()->json(['status' => 'error', 'message' => 'Room not found.'], 404);
            }

            // Validate input
            $validatedData = $request->validate([
                'room_number' => [
                    'required',
                    'string',
                    Rule::unique('room_numbers', 'room_number')
                        ->where(function ($query) use ($hotelId) {
                            return $query->where('hotel_id', $hotelId);
                        })
                        ->ignore($data->id, 'id'),
                ],
                'room_type' => 'required|in:AC,NON-AC',
            ]);

            $data->update($validatedData);

            return response()->json([
                'data' => $data,
                'status' => 'success',
                'message' => 'Room Number updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }



    /**
     * @OA\Post(
     *     path="/change-room-status",
     *     tags={"Hotels"},
     *     summary="Change room status",
     *     description="Toggles the status of a room between active (1) and inactive (0).",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=5, description="ID of the room to change status")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Room status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Room status updated")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Room not found")
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

    public function changeRoomStatus(Request $request)
    {
        $room = RoomNumber::find($request->id);

        if ($room) {
            // Toggle the status
            $newStatus = $room->status == 1 ? 0 : 1;
            $room->update(['status' => $newStatus]);

            // Return the updated status
            return response()->json(['status' => 'success', 'message' => 'Room status updated']);
        }
        return response()->json(['status' => 'error', 'message' => 'Room not found'], 404);
    }

    /**
     * @OA\Post(
     *     path="/get-employees",
     *     tags={"Hotels"},
     *     summary="Get employees for the authenticated hotel",
     *     description="Retrieves a paginated list of employees associated with the hotel of the authenticated user. Supports search by employee name, contact number, and filter by employee ID.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter employees by name or contact number",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="Filter employees by specific employee ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with paginated employee data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="employee_name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="contact_number", type="string", example="9876543210"),
     *                         @OA\Property(property="aadhar_number", type="string", example="123456789012"),
     *                         @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
     *                         @OA\Property(property="address", type="string", example="123 Main St"),
     *                         @OA\Property(property="state_id", type="integer", example=1),
     *                         @OA\Property(property="city_id", type="integer", example=1),
     *                         @OA\Property(property="pincode", type="string", example="400001"),
     *                         @OA\Property(property="hotel_id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=10),
     *                         @OA\Property(property="created_at", type="string", example="2024-01-01T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2024-01-01T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function getEmployees(Request $request)
    {
        try {
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');

            $query = HotelEmployee::where('hotel_id', $hotelId);

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('employee_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }

            $data = $query->paginate(10);

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
     *             @OA\Property(property="message", type="string", example="Hotel employee created successfully")
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
    public function addEmployee(Request $request)
    {
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
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/update-employee",
     *     summary="Update an existing hotel employee",
     *     description="Updates hotel employee details and optionally document IDs (documents should be uploaded separately).",
     *     operationId="updateEmployee",
     *     tags={"Hotels"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "employee_name", "email", "contact_number", "aadhar_number", "pan_number", "address", "state_id", "city_id", "pincode"},
     *             @OA\Property(property="id", type="integer", example=1, description="Hotel employee ID"),
     *             @OA\Property(property="employee_name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="contact_number", type="string", example="9876543210"),
     *             @OA\Property(property="aadhar_number", type="string", example="123412341234"),
     *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="state_id", type="integer", example=5),
     *             @OA\Property(property="city_id", type="integer", example=10),
     *             @OA\Property(property="pincode", type="string", example="560001"),
     *             @OA\Property(
     *                 property="documents",
     *                 type="object",
     *                 example={"1": "document1.pdf", "2": "document2.pdf"},
     *                 description="Optional: Document IDs mapped to file names or paths (if already uploaded)."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Hotel employee updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function updateEmployee(Request $request)
    {
        try {
            $employee = HotelEmployee::find($request->id);

            $request->validate([
                'employee_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $employee->user_id,
                'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $employee->user_id,
                'aadhar_number' => 'required|numeric|digits:12|unique:hotel_employees,aadhar_number,' . $request->id,
                'pan_number' => 'required|string|max:10|unique:hotel_employees,pan_number,' . $request->id,
                'address' => 'required|string',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'pincode' => 'required|numeric|digits:6',
            ], [
                'email.unique' => 'This email has already been taken.',
                'contact_number.unique' => 'This contact number has already been taken.',
                'city_id.exists' => 'The selected city is invalid.',
                'state_id.exists' => 'The selected state is invalid.',
            ]);

            // Track changes before update
            $excludedKeys = ['_token', 'document'];
            $originalData = $employee->toArray();
            $inputData = $request->except($excludedKeys);
            $changes = array_diff_assoc($inputData, $originalData);

            // Replace state and city ids with names for log
            if (isset($changes['state_id'])) {
                $state = State::find($changes['state_id']);
                if ($state) {
                    unset($changes['state_id']);
                    $changes['state_name'] = $state->name;
                }
            }
            if (isset($changes['city_id'])) {
                $city = City::find($changes['city_id']);
                if ($city) {
                    unset($changes['city_id']);
                    $changes['city_name'] = $city->name;
                }
            }

            // Perform update
            $employee->update($inputData);

            // Handle documents
            $updatedDocumentIds = [];
            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $documentId => $file) {
                    $existingDocument = HotelEmployeeDoc::where('hotel_employee_id', $employee->id)
                        ->where('document_id', $documentId)
                        ->first();

                    if ($existingDocument) {
                        Storage::disk('public')->delete($existingDocument->document_path);
                        $existingDocument->delete();
                    }

                    $path = $file->store('hotel_employee_documents', 'public');

                    HotelEmployeeDoc::create([
                        'hotel_employee_id' => $employee->id,
                        'document_id' => $documentId,
                        'document_path' => $path,
                    ]);

                    $updatedDocumentIds[] = $documentId;
                }
            }

            // Update associated user
            $user = User::find($employee->user_id);
            if ($user) {
                $user->update([
                    'name' => $employee->employee_name,
                    'email' => $employee->email,
                    'phone' => $employee->contact_number,
                ]);
            }

            // Prepare readable field changes
            $updatedChanges = implode(', ', array_map(function ($key) use ($changes) {
                $readableKey = ucwords(str_replace('_', ' ', $key));
                return $readableKey . ': ' . (isset($changes[$key]) ? $changes[$key] : 'NULL');
            }, array_keys($changes)));

            // Add document names to activity log
            $documentNames = Document::pluck('name', 'id')->toArray();

            if (!empty($updatedDocumentIds)) {
                $documentList = collect($updatedDocumentIds)
                    ->map(fn($id) => $documentNames[$id] ?? "Document ID $id")
                    ->implode(', ');
                $updatedChanges .= ($updatedChanges ? ', ' : '') . 'Updated Documents: ' . $documentList;
            }

            // Activity log
            activiyLog('Hotel Employee ' . ucfirst($employee->employee_name) . ' updated by ' . ucfirst(Auth::user()->name) . '. Updated fields: ' . $updatedChanges);

            return response()->json([
                'status' => 'success',
                'message' => 'Hotel employee updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/delete-employee",
     *     tags={"Hotels"},
     *     summary="Delete a hotel employee",
     *     description="Deletes a hotel employee, their associated user account, and their documents.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID of the hotel employee to delete")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Hotel employee deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Hotel employee deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function deleteEmployee(Request $request)
    {
        try {
            $employee = HotelEmployee::find($request->id);
            if ($employee->user_id) {
                User::find($employee->user_id)->delete();
                $employee->employeeDocuments()->delete();
                $employee->delete();
            }
            activiyLog('Hotel employee ' . $employee->employee_name . ' deleted by ' . ucfirst(Auth::user()->name));
            return response()->json([
                'status' => 'success',
                'message' => 'Hotel employee deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/change-employee-status",
     *     tags={"Hotels"},
     *     summary="Change hotel employee status",
     *     description="Toggles the status of a hotel employee between active and inactive. Also updates the associated user's status.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID of the hotel employee whose status will be toggled")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Hotel employee status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Hotel employee status updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Hotel employee not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Hotel not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function changeEmployeeStatus(Request $request)
    {
        $employee = HotelEmployee::find($request->id);
        if ($employee) {
            $newStatus = $employee->status == 1 ? 0 : 1;
            $employee->update(['status' => $newStatus]);
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                $user->update(['status' => $newStatus]);
            }
            activiyLog('Hotel employee ' . $employee->employee_name . ' status changed to ' . ($newStatus == 1 ? 'Active' : 'Inactive') . ' by ' . ucfirst(Auth::user()->name));
            return response()->json(['status' => 'success', 'message' => 'Hotel employee status updated successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Hotel not found'], 404);
    }

    /**
     * @OA\Post(
     *     path="/get-bookings",
     *     tags={"Hotels"},
     *     summary="Get hotel bookings",
     *     description="Retrieves hotel bookings for the authenticated hotel owner or employee. Hotel owners see all their hotel bookings; employees see only their own. Supports search and date range filtering.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter bookings by guest name, contact number, or room number",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering bookings (format: Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2025-06-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         required=false,
     *         description="End date for filtering bookings (format: Y-m-d)",
     *         @OA\Schema(type="string", format="date", example="2025-06-30")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of hotel bookings",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="hotel_id", type="integer", example=5),
     *                         @OA\Property(property="hotel_employee_id", type="integer", example=3),
     *                         @OA\Property(property="state_id", type="integer", example=1),
     *                         @OA\Property(property="city_id", type="integer", example=10),
     *                         @OA\Property(property="check_in_date", type="string", format="date", example="2025-06-01"),
     *                         @OA\Property(property="check_out_date", type="string", format="date", example="2025-06-05"),
     *                         @OA\Property(property="status", type="string", example="confirmed"),
     *                         @OA\Property(property="hotel", type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="hotel_name", type="string", example="Grand Palace Hotel")
     *                         ),
     *                         @OA\Property(property="hotelEmployee", type="object",
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="employee_name", type="string", example="John Smith")
     *                         ),
     *                         @OA\Property(property="state", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Maharashtra")
     *                         ),
     *                         @OA\Property(property="city", type="object",
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="name", type="string", example="Mumbai")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *
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

    public function getBookings(Request $request)
    {
        try {
            $query = HotelBooking::with(['hotel:id,hotel_name,user_id', 'hotelEmployee:id,employee_name,user_id', 'state:id,name', 'city:id,name'])
                ->whereNull('parent_id');

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('guest_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('room_number', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $from = Carbon::parse($request->from_date)->startOfDay();
                $to = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$from, $to]);
            }

            if (Auth::user()->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
                $query->where('hotel_id', $hotelId);
            } else if (Auth::user()->user_type_id == 5) {
                $employeeID = HotelEmployee::where('user_id', Auth::user()->id)->value('id');
                $query->where('hotel_employee_id', $employeeID);
            } else {
                return response()->json(['status' => 'success', 'data' => []]);
            }

            $data = $query->orderBy('id', 'desc')->paginate(10);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/get-members",
     *     tags={"Hotels"},
     *     summary="Get booking members by parent booking ID",
     *     description="Retrieves a paginated list of hotel booking members (child bookings) associated with the provided parent booking ID. Supports search by guest name, contact number, and room number.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         required=true,
     *         description="The parent booking ID to retrieve members for",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter members by guest name, contact number, or room number",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of booking members",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="parent_id", type="integer", example=15),
     *                         @OA\Property(property="guest_name", type="string", example="John Doe"),
     *                         @OA\Property(property="contact_number", type="string", example="9876543210"),
     *                         @OA\Property(property="room_number", type="string", example="102"),
     *                         @OA\Property(property="status", type="string", example="confirmed"),
     *                         @OA\Property(property="check_in_date", type="string", format="date", example="2025-06-01"),
     *                         @OA\Property(property="check_out_date", type="string", format="date", example="2025-06-05"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-01T10:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-01T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=30)
     *             ),
     *             @OA\Property(property="canAdd", type="boolean", example=true),
     *             @OA\Property(property="canEdit", type="boolean", example=true),
     *             @OA\Property(property="canDelete", type="boolean", example=true)
     *         )
     *     ),
     *
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
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */


    public function getMembers(Request $request)
    {
        if (!hasPermission('bookings', 'view')) {
            abort(403, 'Unauthorized');
        }

        $id = $request->parent_id;

        $query = HotelBooking::where('parent_id', $id);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('guest_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('room_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        $data = $query->paginate(10);

        $canAdd = hasPermission('bookings', 'add');
        $canEdit = hasPermission('bookings', 'edit');
        $canDelete = hasPermission('bookings', 'delete');

        return response()->json([
            'data' => $data,
            'canAdd' => $canAdd,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete
        ]);
    }

    /**
     * @OA\Post(
     *     path="/get-visitors",
     *     tags={"Hotels"},
     *     summary="Get visitors for a hotel booking",
     *     description="Retrieves a paginated list of visitors associated with a specific hotel booking. Supports optional search by visitor name or contact number.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         description="The booking ID to retrieve visitors for",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter visitors by name or contact number",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with paginated visitors and permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="visitor_name", type="string", example="John Doe"),
     *                         @OA\Property(property="contact_number", type="string", example="9876543210"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-10T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-10T12:00:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             ),
     *             @OA\Property(property="canAdd", type="boolean", example=true),
     *             @OA\Property(property="canEdit", type="boolean", example=true),
     *             @OA\Property(property="canDelete", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
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

    public function getVisitors(Request $request)
    {
        if (!hasPermission('bookings', 'view')) {
            abort(403, 'Unauthorized');
        }

        $id = $request->id;

        $query = Visitor::where('booking_id', $id);

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('visitor_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate(10);

        $canAdd = hasPermission('bookings', 'add');
        $canEdit = hasPermission('bookings', 'edit');
        $canDelete = hasPermission('bookings', 'delete');

        return response()->json([
            'data' => $data,
            'canAdd' => $canAdd,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }





    /**
     * @OA\Post(
     *     path="/get-transfer-entries",
     *     operationId="getTransferEntries",
     *     tags={"Transfer Entries"},
     *     summary="Get paginated transfer entries with filtering",
     *     description="Retrieve transfer entries for the authenticated user, with optional filters for date range and hotel. Groups results by hotel and transfer date.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering transfer entries (format: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         required=false,
     *         description="End date for filtering transfer entries (format: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="hotel_id", type="integer"),
     *                     @OA\Property(property="transfer_date", type="string", format="date"),
     *                     @OA\Property(property="hotel", type="object"),
     *                     @OA\Property(property="hotelEmployee", type="object"),
     *                     @OA\Property(property="transfer_types", type="array", @OA\Items(type="string"))
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="canAdd", type="boolean"),
     *             @OA\Property(property="canEdit", type="boolean"),
     *             @OA\Property(property="canDelete", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */


    public function getTransferEntries(Request $request)
    {
        try {
            if (!hasPermission('transfer-entries', 'view')) {
                abort(403, 'Unauthorized');
            }

            $user = Auth::user();
            $userId = $user->id;
            $userType = $user->user_type_id;

            $query = TransferEntry::with(['hotel:id,hotel_name,user_id', 'hotelEmployee:id,employee_name,user_id']);

            switch ($userType) {
                case 4:
                    $hotelIds = Hotel::where('user_id', $userId)->pluck('id');
                    $query->whereIn('hotel_id', $hotelIds);
                    break;

                case 5:
                    $employeeId = HotelEmployee::where('user_id', $userId)->value('id');
                    if ($employeeId) {
                        $query->where('hotel_employee_id', $employeeId);
                    }
                    break;
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $from = Carbon::parse($request->from_date)->startOfDay();
                $to = Carbon::parse($request->to_date)->endOfDay();
                $query->whereBetween('transfer_date', [$from, $to]);
            }

            // Paginate before grouping
            $entries = $query->paginate(10);

            // Group the paginated results
            $grouped = collect($entries->items())->groupBy(function ($item) {
                return $item->hotel_id . '|' . $item->transfer_date;
            });

            $result = $grouped->map(function ($items, $key) {
                $first = $items->first();
                [$hotelId, $transferDate] = explode('|', $key);
                return [
                    'id' => $first->id,
                    'hotel_id' => $hotelId,
                    'transfer_date' => $transferDate,
                    'hotel' => $first->hotel,
                    'hotelEmployee' => $first->hotelEmployee,
                    'transfer_types' => $items->pluck('transfer_type')->unique()->values(),
                ];
            })->values();

            return response()->json([
                'data' => $result,
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'total' => $entries->total(),
                'per_page' => $entries->perPage(),
                'canAdd' => hasPermission('transfer-entries', 'add'),
                'canEdit' => hasPermission('transfer-entries', 'edit'),
                'canDelete' => hasPermission('transfer-entries', 'delete'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


}
