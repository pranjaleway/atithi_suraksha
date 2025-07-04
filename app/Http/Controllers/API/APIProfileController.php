<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                        'hotel_name' => $data->hotel_name,
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
                    'employee_name' => $data->employee_name,
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

}
