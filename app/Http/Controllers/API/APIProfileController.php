<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use App\Models\HotelEmployeeDoc;
use App\Models\HotelOwnerDoc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class APIProfileController extends Controller
{
    /**
 * @OA\Get(
 *     path="/get-profile-details",
 *     summary="Get Hotel or HotelEmployee details based on user type",
 *     description="Returns hotel details if the user is of type 4, or hotel employee details if the user is of type 5. Unauthorized otherwise.",
 *     operationId="getDetails",
 *     tags={"Profile"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized response",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="error", type="string", example="Unauthorized")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */
    public function getProfileDetails(Request $request) {
        try{
            $userId = Auth::user()->id;

            if(Auth::user()->user_type_id == 4) {
               $data = Hotel::with('police_station', 'ownerDocuments', 'state', 'city')->where('user_id', $userId)->first();

                    $data = [
                        'user_id' => $data->user_id,
                        'name' => $data->hotel_name,
                        'owner_name' => $data->owner_name,
                        'email' => $data->email,
                        'contact_number' => $data->contact_number,
                        'owner_contact_number' => $data->owner_contact_number,
                        'aadhar_number' => $data->aadhar_number,
                        'pan_number' => $data->pan_number,
                        'license_number' => $data->license_number,
                        'address' => $data->address,
                        'state_id' => $data->state_id,
                        'city_id' => $data->city_id,
                        'state_name' => $data->state->name,
                        'city_name' => $data->city->name,
                        'pincode' => $data->pincode,
                        'documents' => $data->ownerDocuments,
                    ];
                

                return response()->json(['data' => $data, 'status' => 'success']);
            } else if (Auth::user()->user_type_id == 5) {
                $data = HotelEmployee::with( 'state', 'city', 'employeeDocuments')->where('user_id', $userId)->first();
                $data = [
                    'user_id' => $data->user_id,
                    'name' => $data->employee_name,
                    'email' => $data->email,
                    'contact_number' => $data->contact_number,
                    'aadhar_number' => $data->aadhar_number,
                    'pan_number' => $data->pan_number,
                    'address' => $data->address,
                    'state_id' => $data->state_id,
                    'city_id' => $data->city_id,
                    'state_name' => $data->state->name,
                    'city_name' => $data->city->name,
                    'pincode' => $data->pincode,
                    'documents' => $data->employeeDocuments
                ];
                return response()->json(['data' => $data, 'status' => 'success']);
            } else {
                return response()->json(['error' => 'Unauthorized', 'status' => 'error']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 'error']);
        }
    }


    /**
 * @OA\Post(
 *     path="/update-profile",
 *     tags={"Profile"},
 *     summary="Update hotel owner or hotel employee profile",
 *     security={{"bearerAuth":{}}},
 *     description="Updates the profile information of a hotel owner (user_type_id = 4) or a hotel employee (user_type_id = 5).",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "contact_number", "aadhar_number", "pan_number", "address", "state_id", "city_id", "pincode"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="owner_name", type="string", example="John Owner", description="Required for hotel owners"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="contact_number", type="string", example="9876543210"),
 *             @OA\Property(property="owner_contact_number", type="string", example="9876543211", description="Required for hotel owners"),
 *             @OA\Property(property="aadhar_number", type="string", example="123456789012"),
 *             @OA\Property(property="pan_number", type="string", example="ABCDE1234F"),
 *             @OA\Property(property="license_number", type="string", example="LIC123456", description="Required for hotel owners"),
 *             @OA\Property(property="address", type="string", example="123 Main St"),
 *             @OA\Property(property="state_id", type="integer", example=1),
 *             @OA\Property(property="city_id", type="integer", example=10),
 *             @OA\Property(property="pincode", type="string", example="400001"),
 *             @OA\Property(
 *                 property="document",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="document_id", type="integer", example=1),
 *                     @OA\Property(property="document_path", type="string", example="path/to/document.pdf")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Profile updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Hotel or Employee not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Hotel not found")
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
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken."))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="error", type="string", example="Exception message")
 *         )
 *     )
 * )
 */


   public function updateProfile(Request $request)
{
    try {
        $userID = Auth::id();
        $userType = Auth::user()->user_type_id;

        if ($userType == 4) {
            // ===================== HOTEL OWNER ===================== //
            $hotel = Hotel::where('user_id', $userID)->first();
            if (!$hotel) {
                return response()->json(['status' => 'error', 'message' => 'Hotel not found']);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'owner_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $hotel->user_id,
                'contact_number' => 'required|numeric|digits:10|unique:hotels,contact_number,' . $hotel->id,
                'owner_contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $hotel->user_id,
                'aadhar_number' => 'required|numeric|digits:12',
                'pan_number' => 'required|string|max:10',
                'license_number' => 'nullable|string|max:255|unique:hotels,license_number,' . $hotel->id,
                'address' => 'required|string',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'pincode' => 'required|numeric|digits:6',
            ]);

            $originalData = $hotel->toArray();

            // === Update fields ===
            $hotel->hotel_name = $request->name;
            $hotel->owner_name = $request->owner_name;
            $hotel->email = $request->email;
            $hotel->contact_number = $request->contact_number;
            $hotel->owner_contact_number = $request->owner_contact_number;
            $hotel->aadhar_number = $request->aadhar_number;
            $hotel->pan_number = $request->pan_number;
            $hotel->license_number = $request->license_number;
            $hotel->address = $request->address;
            $hotel->state_id = $request->state_id;
            $hotel->city_id = $request->city_id;
            $hotel->pincode = $request->pincode;
            $hotel->status = 0;
            $hotel->save();

            Auth::user()->update(['status' => 0]);

            $changes = $this->detectChanges($originalData, $hotel->toArray());

            $updatedDocumentIds = $this->handleDocuments($request, $hotel->id, 'hotel_owner_documents', HotelOwnerDoc::class, 'hotel_id');

            // === Update user table ===
            User::find($hotel->user_id)?->update([
                'name' => $hotel->owner_name,
                'email' => $hotel->email,
                'phone' => $hotel->contact_number,
            ]);

            $changeLog = $this->generateChangeLog($changes, $updatedDocumentIds);

            activiyLog('Hotel ' . ucfirst($hotel->hotel_name) . ' updated by ' . ucfirst(Auth::user()->name) . '. Updated fields: ' . $changeLog);

            return response()->json(['status' => 'success', 'message' => 'Profile updated successfully']);
        }

        elseif ($userType == 5) {
            // ===================== HOTEL EMPLOYEE ===================== //
            $employee = HotelEmployee::where('user_id', $userID)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'Employee not found']);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $employee->user_id,
                'contact_number' => 'required|numeric|digits:10|unique:users,phone,' . $employee->user_id,
                'aadhar_number' => 'required|numeric|digits:12|unique:hotel_employees,aadhar_number,' . $employee->id,
                'pan_number' => 'required|string|max:10|unique:hotel_employees,pan_number,' . $employee->id,
                'address' => 'required|string',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'pincode' => 'required|numeric|digits:6',
            ]);

            $originalData = $employee->toArray();

            $inputData = [
                'employee_name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'aadhar_number' => $request->aadhar_number,
                'pan_number' => $request->pan_number,
                'address' => $request->address,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'pincode' => $request->pincode,
            ];
            $employee->update($inputData);

            $changes = $this->detectChanges($originalData, $employee->toArray());

            $updatedDocumentIds = $this->handleDocuments($request, $employee->id, 'hotel_employee_documents', HotelEmployeeDoc::class, 'hotel_employee_id');

            User::find($employee->user_id)?->update([
                'name' => $employee->employee_name,
                'email' => $employee->email,
                'phone' => $employee->contact_number,
            ]);

            $changeLog = $this->generateChangeLog($changes, $updatedDocumentIds);

            activiyLog('Hotel Employee ' . ucfirst($employee->employee_name) . ' updated by ' . ucfirst(Auth::user()->name) . '. Updated fields: ' . $changeLog);

            return response()->json(['status' => 'success', 'message' => 'Profile updated successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid user type']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'error' => $e->getMessage()]);
    }
}

private function detectChanges(array $original, array $updated, array $exclude = ['_token', 'document', 'created_at', 'updated_at', 'status']): array
{
    $changes = [];
    foreach ($updated as $key => $newValue) {
        if (in_array($key, $exclude)) continue;
        if ((string)($original[$key] ?? '') !== (string)$newValue) {
            $changes[$key] = $newValue;
        }
    }
    return $changes;
}

private function handleDocuments($request, $ownerId, $folder, $model, $ownerKey): array
{
    $updatedDocumentIds = [];

    if ($request->has('documents') && is_array($request->documents)) {
        $documents = $request->documents;

        foreach ($documents as $document) {
            $documentId = $document['document_id'] ?? null;
            $file = $document['document_path'] ?? null;

            if (!$documentId || !$file) {
                continue; // Skip invalid entries
            }

            $existingDocument = $model::where($ownerKey, $ownerId)
                ->where('document_id', $documentId)
                ->first();

            if ($existingDocument) {
                Storage::disk('public')->delete($existingDocument->document_path);
                $existingDocument->delete();
            }

            $path = $file->store($folder, 'public');

            $model::create([
                $ownerKey => $ownerId,
                'document_id' => $documentId,
                'document_path' => $path,
            ]);

            $updatedDocumentIds[] = $documentId;
        }
    }

    return $updatedDocumentIds;
}

private function generateChangeLog(array $changes, array $updatedDocumentIds): string
{
    $log = implode(', ', array_map(function ($key) use ($changes) {
        return ucwords(str_replace('_', ' ', $key)) . ': ' . $changes[$key];
    }, array_keys($changes)));

    if (!empty($updatedDocumentIds)) {
        $documentNames = Document::pluck('name', 'id')->toArray();
        $documentList = collect($updatedDocumentIds)->map(fn($id) => $documentNames[$id] ?? "Document ID $id")->implode(', ');
        $log .= ($log ? ', ' : '') . 'Updated Documents: ' . $documentList;
    }

    return $log;
}



}