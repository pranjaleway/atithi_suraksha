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
use App\Models\UploadedEntry;
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

             $query->orderBy('id', 'desc');

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

            activiyLog(ucfirst(Auth::user()->name) . ' added room number: ' . $data->room_number);

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
            $room = RoomNumber::where('id', $request->id);
            activiyLog(ucfirst(Auth::user()->name) . ' deleted room number ' . $room->room_number);
            $room->delete();
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

            $changes = array_diff_assoc($validatedData, $data->toArray());
            $data->update($validatedData);

            $updatedChanges = implode(', ', array_map(function ($key) use ($changes) {
                $readableKey = ucwords(str_replace('_', ' ', $key));
                return $readableKey . ': ' . (isset($changes[$key]) ? $changes[$key] : 'NULL');
            }, array_keys($changes)));

            activiyLog(ucfirst(Auth::user()->name) . ' updated room changes: ' . $updatedChanges);

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

            activiyLog('Room ' . $room->room_number . ' status changed to ' . ($newStatus == 1 ? 'Active' : 'Inactive') . ' by ' . ucfirst(Auth::user()->name));

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

            $query = HotelEmployee::with('state:id,name', 'city:id,name', 'employeeDocuments')->where('hotel_id', $hotelId);

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

             $query->orderBy('id', 'desc');

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
     *     description="Retrieves hotel bookings for the authenticated hotel owner or employee. Hotel owners see all their hotel bookings; employees see only their own. Supports search, date range filtering, and specific booking or transfer date lookups.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1, description="Specific booking ID to retrieve"),
     *             @OA\Property(property="search", type="string", example="John", description="Search guest name, contact number, or room number"),
     *             @OA\Property(property="from_date", type="string", format="date", example="2025-06-01", description="Start date (Y-m-d)"),
     *             @OA\Property(property="to_date", type="string", format="date", example="2025-06-30", description="End date (Y-m-d)"),
     *             @OA\Property(property="hotel_id", type="integer", example=4, description="Hotel ID used with transfer date filter"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-07-01", description="Transfer date used with hotel_id")
     *         )
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
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="guest_name", type="string", example="John Doe"),
     *                         @OA\Property(property="contact_number", type="string", example="9876543210"),
     *                         @OA\Property(property="check_in", type="string", format="date", example="2025-06-01"),
     *                         @OA\Property(property="check_out", type="string", format="date", example="2025-06-05"),
     *                         @OA\Property(property="room_number", type="string", example="101,102"),
     *                         @OA\Property(property="status", type="integer", example=1),
     *                         @OA\Property(property="transfer_date", type="string", format="date-time", example="2025-07-01T10:00:00Z"),
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
     *                 )
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

            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }

            if ($request->filled('hotel_id') && $request->filled('date')) {
                $entry = TransferEntry::with(['hotel', 'hotelEmployee'])
                    ->where('transfer_date', $request->date)
                    ->where('transfer_type', 'manual')
                    ->first();
                $query = HotelBooking::with(['hotel', 'hotelEmployee', 'state', 'city'])->where('parent_id', null)
                    ->whereDate('transfer_date', $entry->transfer_date);
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
 *     path="/add-booking",
 *     tags={"Hotels"},
 *     summary="Create a hotel booking with guest and member information",
 *     operationId="storeHotelBooking",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "guestFullName", "contactNumber", "gender", "age",
 *                 "aadharNumber", "checkIn", "checkOut", "room", "state_id", "city_id",
 *                 "pincode", "address", "idProof"
 *             },
 *             @OA\Property(property="guestFullName", type="string", example="Ankit"),
 *             @OA\Property(property="contactNumber", type="string", example="9988654433"),
 *             @OA\Property(property="email", type="string", format="email", example="ankit@example.com"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
 *             @OA\Property(property="age", type="integer", example=23),
 *             @OA\Property(property="guestNo", type="integer", example=3),
 *             @OA\Property(property="maleCount", type="integer", example=1),
 *             @OA\Property(property="femaleCount", type="integer", example=1),
 *             @OA\Property(property="childCount", type="integer", example=1),
 *             @OA\Property(property="aadharNumber", type="string", example="983475934593"),
 *             @OA\Property(property="checkIn", type="string", format="date-time", example="2025-07-14T11:43:00.000Z"),
 *             @OA\Property(property="checkOut", type="string", format="date-time", example="2025-07-16T11:43:00.000Z"),
 *             @OA\Property(
 *                 property="room",
 *                 type="string",
 *                 example="1,2,3",
 *             ),
 *             @OA\Property(property="state_id", type="integer", example=1),
 *             @OA\Property(property="city_id", type="integer", example=2),
 *             @OA\Property(property="pincode", type="integer", example=456010),
 *             @OA\Property(property="address", type="string", example="test"),
 *             @OA\Property(property="addMembers", type="boolean", example=true),
 *             @OA\Property(property="idProof", type="string", format="binary", description="ID proof file (base64 or file URL)"),
 *             @OA\Property(
 *                 property="members",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"guestFullName", "contactNumber", "gender", "age", "aadharNumber", "idProof"},
 *                     @OA\Property(property="guestFullName", type="string", example="guest 2"),
 *                     @OA\Property(property="contactNumber", type="string", example="93845793475"),
 *                     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female"),
 *                     @OA\Property(property="age", type="integer", example=22),
 *                     @OA\Property(property="aadharNumber", type="string", example="234234234234"),
 *                     @OA\Property(property="sameAsAboveAddress", type="boolean", example=true),
 *                     @OA\Property(property="state_id", type="integer", example=2),
 *                     @OA\Property(property="city_id", type="integer", example=126),
 *                     @OA\Property(property="pincode", type="integer", example=456050),
 *                     @OA\Property(property="address", type="string", example="test 2"),
 *                     @OA\Property(property="idProof", type="string", format="binary", description="Member ID proof (base64 or file reference)")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Hotel booking added successfully"),
 *             @OA\Property(property="redirect", type="string", example="/bookings")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */

    private function generateBookingId($cityId, $policeStationId, $hotelId)
    {
        $datePart = now()->format('Ymd'); // Example: 20250703

        // Match all booking IDs ending with today's date + sequence
        $todayBookings = HotelBooking::where('booking_id', 'like', "%{$datePart}%")
            ->pluck('booking_id');

        $maxSequence = 0;

        foreach ($todayBookings as $bookingId) {
            // Get the last 3 characters as sequence
            $sequence = (int) substr($bookingId, -3);
            if ($sequence > $maxSequence) {
                $maxSequence = $sequence;
            }
        }

        $nextSequence = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);

        // Build final booking ID
        return "{$cityId}{$policeStationId}{$hotelId}{$datePart}{$nextSequence}";
    }


    public function addBooking(Request $request)
{
    $mainGuest = [
        'guest_name' => $request->guestFullName,
        'contact_number' => $request->contactNumber,
        'email' => $request->email,
        'gender' => $request->gender,
        'age' => $request->age,
        'no_of_guest' => $request->guestNo,
        'no_of_male' => $request->maleCount,
        'no_of_female' => $request->femaleCount,
        'no_of_children' => $request->childCount,
        'aadhar_number' => $request->aadharNumber,
        'check_in' => Carbon::parse($request->checkIn)->format('Y-m-d\TH:i'),
        'check_out' => Carbon::parse($request->checkOut)->format('Y-m-d\TH:i'),
        'room_number_id' => $request->room,
        'state_id' => $request->state_id,
        'city_id' => $request->city_id,
        'pincode' => $request->pincode,
        'address' => $request->address,
        'id_proof_path' => $request->file('idProof'),
        'same_address' => null, 
    ];

    $members = $request->input('members', []);
    $guestList = [$mainGuest];

    foreach ($members as $index => $member) {
        $sameAddress = filter_var($member['sameAsAboveAddress'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $guestList[] = [
            'guest_name' => $member['guestFullName'],
            'contact_number' => $member['contactNumber'] ?? null,
            'gender' => $member['gender'],
            'age' => $member['age'],
            'aadhar_number' => $member['aadharNumber'],
            'email' => null,
            'check_in' => null,
            'check_out' => null,
            'room_number_id' => null,
            'no_of_guest' => null,
            'no_of_male' => null,
            'no_of_female' => null,
            'no_of_children' => null,
            'same_address' => $sameAddress ? 'true' : 'false',
            'address' => $member['address'] ?? null,
            'state_id' => $member['state_id'] ?? null,
            'city_id' => $member['city_id'] ?? null,
            'pincode' => $member['pincode'] ?? null,
            'id_proof_path' => $request->file("members.$index.idProof"),
        ];
    }

    $request->merge(['guests' => $guestList]);

    $this->validateBookingData($request);

    $guests = $request->guests;
    $firstGuest = $guests[0];

    $totalGuests = (int) ($firstGuest['no_of_male'] ?? 0)
        + (int) ($firstGuest['no_of_female'] ?? 0)
        + (int) ($firstGuest['no_of_children'] ?? 0);

    if ((int) $firstGuest['no_of_guest'] !== $totalGuests) {
        return response()->json([
            'status' => 'error',
            'errors' => [
                "guests.0.no_of_guest" => ["Total guests must match the sum of male, female, and children."]
            ]
        ], 422);
    }

    $user = Auth::user();
    $hotelId = $hotelEmployeeId = null;

    if ($user->user_type_id == 4) {
        $hotelId = Hotel::where('user_id', $user->id)->value('id');
    } elseif ($user->user_type_id == 5) {
        $employee = HotelEmployee::where('user_id', $user->id)->first();
        $hotelId = $employee->hotel_id ?? null;
        $hotelEmployeeId = $employee->id ?? null;
    }

    $cityId = $firstGuest['city_id'];
    $policeStationId = Hotel::where('id', $hotelId)->value('police_station_id');
    $bookingId = $this->generateBookingId($cityId, $policeStationId, $hotelId);
    $savedIds = [];

    foreach ($guests as $index => $guestData) {
        $sameAddress = filter_var($guestData['same_address'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($index > 0 && $sameAddress) {
            $guestData['address'] = $firstGuest['address'];
            $guestData['state_id'] = $firstGuest['state_id'];
            $guestData['city_id'] = $firstGuest['city_id'];
            $guestData['pincode'] = $firstGuest['pincode'];
        }

        if ($index > 0) {
            $guestData['check_in'] = $firstGuest['check_in'];
            $guestData['check_out'] = $firstGuest['check_out'];
            $guestData['room_number_id'] = $firstGuest['room_number_id'];
        }

        $roomNumbers = [];
        if (!empty($guestData['room_number_id'])) {
            $roomIds = explode(',', $guestData['room_number_id']);
            $roomNumbers = RoomNumber::whereIn('id', $roomIds)->pluck('room_number')->toArray();
        }

        $filePath = null;
        if (!empty($guestData['id_proof_path'])) {
            $filePath = $guestData['id_proof_path']->store('booking/id_proofs', 'public');
        }

        $booking = HotelBooking::create([
            'hotel_id' => $hotelId,
            'hotel_employee_id' => $hotelEmployeeId,
            'booking_id' => $bookingId,
            'guest_name' => $guestData['guest_name'],
            'contact_number' => $guestData['contact_number'] ?? $firstGuest['contact_number'],
            'email' => $guestData['email'] ?? null,
            'aadhar_number' => $guestData['aadhar_number'],
            'age' => $guestData['age'],
            'gender' => $guestData['gender'],
            'no_of_guest' => $guestData['no_of_guest'] ?? null,
            'no_of_male' => $guestData['no_of_male'] ?? null,
            'no_of_female' => $guestData['no_of_female'] ?? null,
            'no_of_children' => $guestData['no_of_children'] ?? null,
            'check_in' => $guestData['check_in'],
            'check_out' => $guestData['check_out'],
            'room_number_id' => $guestData['room_number_id'],
            'room_number' => implode(',', $roomNumbers),
            'address' => $guestData['address'],
            'state_id' => $guestData['state_id'],
            'city_id' => $guestData['city_id'],
            'pincode' => $guestData['pincode'],
            'id_proof_path' => $filePath,
            'parent_id' => $index === 0 ? null : ($savedIds[0] ?? null),
        ]);

        $savedIds[] = $booking->id;
    }

    activiyLog('New booking added by ' . ucfirst($user->name));

    return response()->json([
        'status' => 'success',
        'message' => 'Hotel booking added successfully',
    ]);
}


private function validateBookingData(Request $request)
{
    $guests = $request->input('guests');

    $rules = ['guests' => 'required|array|min:1'];
    $messages = ['guests.required' => 'At least one guest is required.'];

    foreach ($guests as $index => $guest) {
        $prefix = "guests.$index.";

        $rules += [
            $prefix . 'guest_name' => 'required|string|max:255',
            $prefix . 'aadhar_number' => 'required|numeric|digits:12',
            $prefix . 'email' => 'nullable|email|max:255',
            $prefix . 'id_proof_path' => 'required|file|mimes:jpeg,jpg,png,pdf',
            $prefix . 'same_address' => 'nullable|in:true,false,1,0',
            $prefix . 'age' => 'required|integer|min:0',
            $prefix . 'gender' => 'required|in:male,female,other',
        ];

        if ($index === 0) {
            $rules += [
                $prefix . 'check_in' => 'required|date_format:Y-m-d\TH:i',
                $prefix . 'check_out' => 'required|date_format:Y-m-d\TH:i|after_or_equal:' . $prefix . 'check_in',
                $prefix . 'contact_number' => 'required|numeric|digits:10',
                $prefix . 'no_of_guest' => 'required|integer|min:1',
                $prefix . 'no_of_male' => 'nullable|integer|min:1',
                $prefix . 'no_of_female' => 'nullable|integer|min:1',
                $prefix . 'no_of_children' => 'nullable|integer|min:0',
                $prefix . 'address' => 'required|string|max:500',
                $prefix . 'state_id' => 'required|integer|exists:states,id',
                $prefix . 'city_id' => 'required|integer|exists:cities,id',
                $prefix . 'pincode' => 'required|numeric|digits:6',
                $prefix . 'room_number_id' => 'required',
            ];
        } else {
            $rules += [
                $prefix . 'check_in' => 'nullable|date_format:Y-m-d\TH:i',
                $prefix . 'check_out' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:' . $prefix . 'check_in',
                $prefix . 'contact_number' => 'nullable',
                $prefix . 'no_of_guest' => 'nullable',
                $prefix . 'no_of_male' => 'nullable',
                $prefix . 'no_of_female' => 'nullable',
                $prefix . 'no_of_children' => 'nullable',
                $prefix . 'room_number_id' => 'nullable',
            ];

            // ✅ Fix: cast same_address before checking (handles "true"/"false" correctly)
            $sameAddress = filter_var($guest['same_address'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!$sameAddress) {
                $rules += [
                    $prefix . 'address' => 'required|string|max:500',
                    $prefix . 'state_id' => 'required|integer|exists:states,id',
                    $prefix . 'city_id' => 'required|integer|exists:cities,id',
                    $prefix . 'pincode' => 'required|numeric|digits:6',
                ];
                $messages += [
                    $prefix . 'address.required' => 'Please enter the address.',
                    $prefix . 'state_id.required' => 'Please select a state.',
                    $prefix . 'city_id.required' => 'Please select a city.',
                    $prefix . 'pincode.required' => 'Please enter the pincode.',
                ];
            }
        }

        // ✅ Shared custom messages
        $messages += [
            $prefix . 'guest_name.required' => 'Please enter the guest name.',
            $prefix . 'guest_name.max' => 'Guest name may not exceed 255 characters.',
            $prefix . 'aadhar_number.required' => 'Please enter the Aadhar number.',
            $prefix . 'aadhar_number.numeric' => 'Aadhar number must be numeric.',
            $prefix . 'aadhar_number.digits' => 'Aadhar number must be exactly 12 digits.',
            $prefix . 'email.email' => 'Please enter a valid email address.',
            $prefix . 'id_proof_path.required' => 'Please upload the ID proof.',
            $prefix . 'id_proof_path.mimes' => 'ID proof must be a jpeg, jpg, png, or pdf file.',
            $prefix . 'age.required' => 'Please enter the age.',
            $prefix . 'age.integer' => 'Age must be a valid number.',
            $prefix . 'age.min' => 'Age must be a positive number.',
            $prefix . 'gender.required' => 'Please select the gender.',
            $prefix . 'gender.in' => 'Please select a valid gender.',
        ];

        if ($index === 0) {
            $messages += [
                $prefix . 'check_in.required' => 'Please enter the check-in date.',
                $prefix . 'check_in.date_format' => 'Check-in date must be in Y-m-d\TH:i format.',
                $prefix . 'check_out.required' => 'Please enter the check-out date.',
                $prefix . 'check_out.date_format' => 'Check-out date must be in Y-m-d\TH:i format.',
                $prefix . 'check_out.after_or_equal' => 'Check-out must be same or after check-in.',
                $prefix . 'contact_number.required' => 'Please enter contact number.',
                $prefix . 'contact_number.numeric' => 'Contact number must be numeric.',
                $prefix . 'contact_number.digits' => 'Contact number must be 10 digits.',
                $prefix . 'no_of_guest.required' => 'Please enter number of guests.',
                $prefix . 'no_of_guest.integer' => 'Number of guests must be a valid number.',
                $prefix . 'no_of_guest.min' => 'At least one guest is required.',
                $prefix . 'address.required' => 'Please enter the address.',
                $prefix . 'state_id.required' => 'Please select the state.',
                $prefix . 'city_id.required' => 'Please select the city.',
                $prefix . 'pincode.required' => 'Please enter the pincode.',
                $prefix . 'pincode.numeric' => 'Pincode must be numeric.',
                $prefix . 'pincode.digits' => 'Pincode must be 6 digits.',
                $prefix . 'no_of_male.integer' => 'Number of males must be a valid number.',
                $prefix . 'no_of_male.min' => 'At least one male is required.',
                $prefix . 'no_of_female.integer' => 'Number of females must be a valid number.',
                $prefix . 'no_of_female.min' => 'At least one female is required.',
                $prefix . 'no_of_children.integer' => 'Number of children must be a valid number.',
                $prefix . 'same_address.in' => 'Please select a valid option.',
                $prefix . 'room_number_id.required' => 'Please enter the room number.',
            ];
        }
    }

    // ✅ Run validation
    $request->validate($rules, $messages);
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

        $data = $query->orderBy('id', 'desc')->paginate(10);

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
     *     path="/add-member",
     *     tags={"Hotels"},
     *     summary="Add a new member to a booking",
     *     description="Creates a new hotel booking member under an existing booking. Requires authentication.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"parent_id", "guest_name", "check_in", "age", "gender", "room_number_id", "contact_number", "aadhar_number", "address", "state_id", "city_id", "pincode", "id_proof_path"},
     *             @OA\Property(property="parent_id", type="integer", example=1, description="Parent booking ID"),
     *             @OA\Property(property="guest_name", type="string", example="John Doe"),
     *             @OA\Property(property="check_in", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="check_out", type="string", format="date", nullable=true, example="2025-06-05"),
     *             @OA\Property(property="age", type="integer", example=30),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
     *             @OA\Property(property="room_number_id", type="string", example="1,2", description="Comma-separated room number IDs"),
     *             @OA\Property(property="contact_number", type="string", example="9876543210"),
     *             @OA\Property(property="aadhar_number", type="string", example="123456789012"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com", nullable=true),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="state_id", type="integer", example=1),
     *             @OA\Property(property="city_id", type="integer", example=10),
     *             @OA\Property(property="pincode", type="string", example="400001"),
     *             @OA\Property(property="id_proof_path", type="string", example="data:application/pdf;base64,JVBERi0xLjQKJ...")  
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Member added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Member added successfully")
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

    public function addMember(Request $request)
    {
        try {
            $request->validate([
                'parent_id' => 'required',
                'guest_name' => 'required',
                'check_in' => 'required|date',
                'check_out' => 'nullable|date',
                'age' => 'required|numeric',
                'gender' => 'required|in:male,female,other',
                'room_number_id' => 'required',
                'contact_number' => 'required|numeric|digits:10',
                'aadhar_number' => 'required|numeric|digits:12',
                'email' => 'nullable|email',
                'address' => 'required',
                'state_id' => 'required',
                'city_id' => 'required',
                'pincode' => 'required|numeric|digits:6',
                'id_proof_path' => 'required|file|mimes:jpeg,png,jpg,pdf',
            ]);

            $guestData = $request->all();

            $roomNumberIds = is_array($guestData['room_number_id'])
                ? explode(',', $guestData['room_number_id'][0])
                : explode(',', $guestData['room_number_id']);
            $roomNumberStr = implode(',', $roomNumberIds);

            $roomNumbers = RoomNumber::whereIn('id', $roomNumberIds)->pluck('room_number')->toArray();

            $guestData['room_number'] = implode(',', $roomNumbers);

            // Handle file if uploaded
            $file = $request->file('id_proof_path');
            $filePath = $file->store('booking/id_proofs', 'public');

            $user = Auth::user();
            $hotelId = null;
            $hotel_employee_id = null;

            if ($user->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', $user->id)->value('id');
            } elseif ($user->user_type_id == 5) {
                $employee = HotelEmployee::where('user_id', $user->id)->first();
                if ($employee) {
                    $hotelId = $employee->hotel_id;
                    $hotel_employee_id = $employee->id;
                }
            }

            $parentId = $guestData['parent_id'];
            $booking = HotelBooking::find($parentId);
            $bookingId = $booking->booking_id;

            $booking = HotelBooking::create([
                'hotel_id' => $hotelId,
                'booking_id' => $bookingId,
                'hotel_employee_id' => $hotel_employee_id,
                'guest_name' => $guestData['guest_name'],
                'check_in' => $guestData['check_in'],
                'check_out' => $guestData['check_out'],
                'room_number' => $guestData['room_number'],
                'room_number_id' => $roomNumberStr,
                'contact_number' => $guestData['contact_number'],
                'aadhar_number' => $guestData['aadhar_number'],
                'email' => $guestData['email'],
                'address' => $guestData['address'],
                'state_id' => $guestData['state_id'],
                'city_id' => $guestData['city_id'],
                'pincode' => $guestData['pincode'],
                'id_proof_path' => $filePath,
                'parent_id' => $guestData['parent_id'],
                'age' => $guestData['age'],
                'gender' => $guestData['gender'],
            ]);

            if ($booking) {
                activiyLog('Member added by ' . ucfirst(Auth::user()->name));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Member added successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/delete-member",
     *     tags={"Hotels"},
     *     summary="Delete a hotel booking member",
     *     description="Deletes a member from a hotel booking by their booking ID. Requires authentication.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=5, description="The ID of the member booking record to delete")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Member deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Member deleted successfully")
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


    public function deleteMember(Request $request)
    {
        try {
            $member = HotelBooking::find($request->id);
            if ($member) {
                $member->delete();
                activiyLog('Member deleted by ' . ucfirst(Auth::user()->name));
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Member deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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
     *         name="booking_id",
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

        $bookingId = $request->booking_id;

        $query = Visitor::where('booking_id', $bookingId);

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('visitor_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('id')) {
            $query->where('id', $request->id);
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
     *     path="/add-visitor",
     *     tags={"Hotels"},
     *     summary="Add a new visitor",
     *     description="Adds a new visitor associated with a booking. Supports providing visitor details and an optional ID proof path as a string (e.g., file path).",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id", "visitor_name", "aadhar_number", "contact_number", "age", "gender", "address", "state_id", "city_id", "pincode", "entry_time"},
     *             @OA\Property(property="booking_id", type="integer", example=1, description="Booking ID to associate the visitor with"),
     *             @OA\Property(property="visitor_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="aadhar_number", type="string", example="123456789012"),
     *             @OA\Property(property="contact_number", type="string", example="9876543210"),
     *             @OA\Property(property="age", type="integer", example=30),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female"),
     *             @OA\Property(property="id_proof_path", type="string", example="booking/visitors_id_proofs/abc123.pdf", description="Optional path of the ID proof document"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="state_id", type="integer", example=1),
     *             @OA\Property(property="city_id", type="integer", example=10),
     *             @OA\Property(property="pincode", type="string", example="400001"),
     *             @OA\Property(property="entry_time", type="string", format="date-time", example="2025-07-10T12:00:00", description="Entry time in Y-m-d\\TH:i:s format")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Visitor added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Visitor added successfully.")
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

    public function addVisitor(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|exists:hotel_bookings,id',
                'visitor_name' => 'required|string|max:255',
                'aadhar_number' => 'required|string|max:255',
                'contact_number' => 'required|string|max:255',
                'age' => 'required|integer|min:0',
                'gender' => 'required|in:male,female,other',
                'id_proof_path' => 'nullable|file|mimes:jpeg,jpg,png,pdf',
                'address' => 'required|string|max:255',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'pincode' => 'required|string|max:255',
                'entry_time' => 'required|date_format:Y-m-d\TH:i',
            ]);

            $visitor = Visitor::create($validated);

            if ($request->hasFile('id_proof_path')) {
                $visitor->id_proof_path = $request->file('id_proof_path')->store('booking/visitors_id_proofs', 'public');
                $visitor->save();
            }
            activiyLog('Visitor ' . $visitor->visitor_name . ' added by ' . ucfirst(Auth::user()->name));
            return response()->json(['status' => 'success', 'message' => 'Visitor added successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/delete-visitor",
     *     tags={"Hotels"},
     *     summary="Delete a visitor",
     *     description="Deletes a visitor by its ID. Logs the action performed by the authenticated user.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID of the visitor to be deleted")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Visitor deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Visitor deleted successfully.")
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



    public function deleteVisitor(Request $request)
    {
        try {
            $visitor = Visitor::find($request->id);
            $visitor->delete();
            activiyLog('Visitor ' . $visitor->visitor_name . ' deleted by ' . ucfirst(Auth::user()->name));
            return response()->json(['status' => 'success', 'message' => 'Visitor deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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
            $entries = $query->orderBy('id', 'desc')->paginate(10);

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

    /**
     * @OA\Post(
     *     path="/get-remaining-transfer-bookings",
     *     tags={"Transfer Entries"},
     *     summary="Get remaining transfer entries",
     *     description="Retrieves all hotel bookings for the authenticated hotel that have a status of 0 and a check-in date up to and including the day after tomorrow.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with a list of pending transfer bookings",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="guest_name", type="string", example="John Doe"),
     *                     @OA\Property(property="check_in", type="string", format="date-time", example="2025-07-12T14:00:00"),
     *                     @OA\Property(property="check_out", type="string", format="date-time", example="2025-07-14T11:00:00"),
     *                     @OA\Property(property="status", type="integer", example=0),
     *                     @OA\Property(property="room_number", type="string", example="101,102")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function getRemainingTransferBookings(Request $request)
    {
        try {
            $dayAfterTomorrow = Carbon::tomorrow()->addDay(); // Day after tomorrow's date

            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');

            $bookings = HotelBooking::where('hotel_id', $hotelId)->where('status', 0)
                ->whereNull('parent_id')
                ->whereDate('check_in', '<=', $dayAfterTomorrow)
                ->orderBy('id', 'desc') 
                ->get();

            return response()->json(['status' => true, 'data' => $bookings]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/add-transfer-bookings",
     *     tags={"Transfer Entries"},
     *     summary="Transfer hotel bookings",
     *     description="Marks the selected hotel bookings as transferred and creates a transfer entry for the authenticated hotel or employee.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_ids"},
     *             @OA\Property(
     *                 property="booking_ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of booking IDs to transfer"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Transfer entries saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transfer entries saved successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The booking_ids field is required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function addTransferBookings(Request $request)
    {
        try {

            $validated = $request->validate([
                'booking_ids' => 'required|array',
                'booking_ids.*' => 'integer|exists:hotel_bookings,id',
            ]);

            $user = Auth::user();
            $hotelId = null;
            $hotel_employee_id = null;

            if ($user->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', $user->id)->value('id');
            } elseif ($user->user_type_id == 5) {
                $employee = HotelEmployee::where('user_id', $user->id)->first();
                if ($employee) {
                    $hotelId = $employee->hotel_id;
                    $hotel_employee_id = $employee->id;
                }
            }
            foreach ($validated['booking_ids'] as $id) {
                $booking = HotelBooking::find($id);
                if ($booking) {
                    $booking->status = 1;
                    $booking->transfer_date = now();
                    $booking->save();
                }
            }
            $transferEntries = TransferEntry::create([
                'hotel_id' => $hotelId,
                'hotel_employee_id' => $hotel_employee_id,
                'transfer_date' => now(),
                'transfer_type' => 'manual',
            ]);

            // Log the action
            activiyLog('Manual Entries Transferred by ' . ucfirst(Auth::user()->name));

            // Return JSON response
            return response()->json([
                'status' => 'success',
                'message' => 'Transfer entries saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/upload-register",
     *     tags={"Transfer Entries"},
     *     summary="Upload register file paths",
     *     description="Saves the file paths of uploaded register entries and marks them as transferred. This expects file paths (strings), not binary uploads.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file_path"},
     *             @OA\Property(
     *                 property="file_path",
     *                 type="array",
     *                 description="Array of file paths (as strings) for the uploaded files",
     *                 @OA\Items(
     *                     type="string",
     *                     example="uploaded_entries/abc123.pdf"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="File paths saved and marked as transferred successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="File uploaded and marked as transferred successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Please provide at least one file path.")
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
    public function uploadRegister(Request $request)
    {
        try {
            $validated = $request->validate([
                'file_path' => 'required|array',
                'file_path.*' => 'file|mimes:jpeg,png,jpg,pdf',
            ], [
                'file_path.required' => 'Please select at least one file.',
                'file_path.*.file' => 'Please select a valid file.',
                'file_path.*.mimes' => 'Please select a file with one of the following extensions: jpeg, png, jpg, pdf.',
            ]);

            $user = Auth::user();
            $hotelId = null;
            $hotel_employee_id = null;

            if ($user->user_type_id == 4) {
                $hotelId = Hotel::where('user_id', $user->id)->value('id');
            } elseif ($user->user_type_id == 5) {
                $employee = HotelEmployee::where('user_id', $user->id)->first();
                if ($employee) {
                    $hotelId = $employee->hotel_id;
                    $hotel_employee_id = $employee->id;
                }
            }

            $createdUploadIds = [];

            // Upload and save each file
            foreach ($request->file('file_path') as $file) {
                $filePath = $file->store('uploaded_entries', 'public');
                $uploaded = UploadedEntry::create([
                    'hotel_id' => $hotelId,
                    'hotel_employee_id' => $hotel_employee_id,
                    'file_path' => $filePath,
                    'status' => 1,
                    'transfer_date' => now(),
                ]);

                // Collect the uploaded entry ID
                $createdUploadIds[] = $uploaded->id;
            }

            // Update those entries and create transfer entry
            if (!empty($createdUploadIds)) {

                TransferEntry::create([
                    'hotel_id' => $hotelId,
                    'hotel_employee_id' => $hotel_employee_id,
                    'transfer_date' => now(),
                    'transfer_type' => 'uploaded',
                ]);
            }

            activiyLog('Entries uploaded by ' . ucfirst($user->name));

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded and marked as transferred successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
 * @OA\Post(
 *     path="/get-uploaded-registers",
 *     tags={"Hotels"},
 *     summary="Get uploaded register entries",
 *     description="Fetches uploaded register entries for the authenticated hotel or hotel employee. If 'hotel_id' and 'date' are provided, it filters based on a matching uploaded transfer entry.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             @OA\Property(property="hotel_id", type="integer", example=2, description="ID of the hotel (required with date to filter specific entry)"),
 *             @OA\Property(property="date", type="string", format="date", example="2025-07-15", description="Date of the transfer to filter uploaded register")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of uploaded register entries",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="hotel_id", type="integer", example=5),
 *                     @OA\Property(property="hotel_employee_id", type="integer", example=3),
 *                     @OA\Property(property="file_path", type="string", example="uploaded_entries/file123.pdf"),
 *                     @OA\Property(property="status", type="integer", example=1),
 *                     @OA\Property(property="transfer_date", type="string", format="date", example="2025-07-15"),
 *                     @OA\Property(property="hotel", type="object",
 *                         @OA\Property(property="id", type="integer", example=5),
 *                         @OA\Property(property="hotel_name", type="string", example="Grand Palace Hotel")
 *                     ),
 *                     @OA\Property(property="hotelEmployee", type="object",
 *                         @OA\Property(property="id", type="integer", example=3),
 *                         @OA\Property(property="employee_name", type="string", example="John Smith")
 *                     )
 *                 )
 *             ),
 *             @OA\Property(property="canAdd", type="boolean", example=true),
 *             @OA\Property(property="canEdit", type="boolean", example=true),
 *             @OA\Property(property="canDelete", type="boolean", example=false)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Transfer entry not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Transfer entry not found")
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

    public function getUploadedRegisters(Request $request)
    {
        if (!hasPermission('uploaded-entries', 'view')) {
            abort(403, 'Unauthorized');
        }
        $id = $request->hotel_id;
        $date = $request->date;

        if ($id && $date) {
            $entry = TransferEntry::with(['hotel', 'hotelEmployee'])
                ->where('transfer_date', $date)
                ->where('transfer_type', 'uploaded')
                ->first();

            if (!$entry) {
                return response()->json(['error' => 'Transfer entry not found'], 404);
            }

            $query = UploadedEntry::with(['hotel', 'hotelEmployee'])
                ->where('transfer_date', $entry->transfer_date);
        } else {
            $query = UploadedEntry::with(['hotel', 'hotelEmployee']);
        }

        if (Auth::user()->user_type_id == 4) {
            $hotelId = Hotel::where('user_id', Auth::user()->id)->value('id');
            $data = $query->where('hotel_id', $hotelId)
            ->orderBy('id', 'desc')
            ->get();
        } else if (Auth::user()->user_type_id == 5) {
            $employeeID = HotelEmployee::where('user_id', Auth::user()->id)->value('id');
            $data = $query->where('hotel_employee_id', $employeeID)
            ->orderBy('id', 'desc')
            ->get();
        } else {
            $data = [];
        }

        return response()->json([
            'data' => $data,
            'canAdd' => hasPermission('uploaded-entries', 'add'),
            'canEdit' => hasPermission('uploaded-entries', 'edit'),
            'canDelete' => hasPermission('uploaded-entries', 'delete'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/get-available-room-numbers",
     *     tags={"Hotels"},
     *     summary="Get available room numbers",
     *     description="Retrieves a list of available room numbers for the authenticated hotel or hotel employee based on the given check-in date. Excludes rooms already booked for the given date.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"check_in"},
     *             @OA\Property(property="check_in", type="string", format="date-time", example="2025-07-15T14:00:00", description="Check-in date and time in Y-m-d\\TH:i:s format")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with a list of available rooms",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="room_number", type="string", example="101"),
     *                     @OA\Property(property="room_type", type="string", example="Deluxe"),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error when check-in date is missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Check-in date is required")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access if user is not a hotel or hotel employee",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function getAvailableRoomNumbers(Request $request)
    {
        try {
            $checkInDateTime = Carbon::parse($request->check_in);

            if (!$checkInDateTime) {
                return response()->json(['status' => false, 'message' => 'Check-in date is required'], 422);
            }

            if (Auth::user()->user_type_id == 4) {
                $hotel_id = Hotel::where('user_id', Auth::id())->value('id');
            } elseif (Auth::user()->user_type_id == 5) {
                $hotel_id = HotelEmployee::where('user_id', Auth::id())->value('hotel_id');
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }

            $allRooms = RoomNumber::where('hotel_id', $hotel_id)->where('status', 1)->get();

            $bookings = HotelBooking::where('hotel_id', $hotel_id)
                ->where('check_in', '<=', $checkInDateTime)
                ->where('check_out', '>=', $checkInDateTime)
                ->pluck('room_number_id') // this gives comma-separated room ids
                ->toArray();

            $bookedRoomIds = collect($bookings)
                ->flatMap(function ($item) {
                    return array_map('intval', explode(',', $item));
                })
                ->unique()
                ->toArray();

            $availableRooms = $allRooms->filter(function ($room) use ($bookedRoomIds) {
                return !in_array($room->id, $bookedRoomIds);
            })->values();

            return response()->json(['status' => true, 'data' => $availableRooms]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
