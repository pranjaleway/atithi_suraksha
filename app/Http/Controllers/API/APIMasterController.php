<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Hotel;
use App\Models\Menu;
use App\Models\Notification;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use App\Models\State;
use App\Models\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class APIMasterController extends Controller
{
    /**
     * @OA\Get(
     *     path="/get-documents",
     *     summary="Get list of documents",
     *     description="Retrieve all documents with their ID, name, and status.",
     *     operationId="getDocuments",
     *     tags={"Common"},
     *     @OA\Response(
     *         response=200,
     *         description="List of documents retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Aadhar Card"),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )             
     *             )            
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong!")
     *         )
     *     )
     * )
     */


    public function getDocuments(Request $request)
    {
        try {
            $data = Document::where('status', 1)->orderBy('id', 'desc')->get();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/get-states",
     *     summary="Get list of states",
     *     description="Retrieve all states with their ID, name, and status.",
     *     operationId="getStates",
     *     tags={"Common"},
     *     @OA\Response(
     *         response=200,
     *         description="List of states retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Andhra Pradesh"),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong!")
     *         )
     *     )
     * )
     */


    public function getStates(Request $request)
    {
        try {
            $data = State::where('country_id', 1)->where('status', 1)->orderBy('name', 'asc')->get();
            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/get-cities",
     *     summary="Get list of cities",
     *     description="Retrieve all cities with their ID, name, state details, and status.",
     *     operationId="getCities",
     *     tags={"Common"},
     *     @OA\Parameter(
     *         name="state_id",
     *         in="query",
     *         required=false,
     *         description="Filter cities by state ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of cities retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Hyderabad"),
     *                     @OA\Property(property="state", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Andhra Pradesh")
     *                     ),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong!")
     *         )
     *     )
     * )
     */

    public function getCities(Request $request)
    {
        try {
            $data = City::where('status', 1)->where('state_id', $request->state_id)->get();
            $data = $data->sortBy('name')->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'state' => [
                        'id' => $item->state->id,
                        'state_name' => $item->state->name,
                    ],
                    'status' => $item->status
                ];
            })->values();


            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/get-menus",
     *     summary="Get list of menus",
     *     description="Retrieve all menus with their ID, name, and status.",
     *     operationId="getMenus",
     *     tags={"Common"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="List of menus retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dashboard"),
     *                     @OA\Property(property="status", type="integer", example=1)
     *                 )             
     *             )            
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong!")
     *         )
     *     )
     * )
     */

    public function getMenus(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $user_role = $user->user_type_id;

            if ($user_role == 0) {
                $menus = Menu::where('status', 1)->get();
            } else {
                $userAccess = UserAccess::where('user_type_id', $user_role)
                    ->get();

                $menuIds = $userAccess->pluck('menu_id')->toArray();

                $menus = Menu::where('status', 1)
                    ->where('visible_at_app', 1)
                    ->whereIn('id', $menuIds)
                    ->get();
            }

            $menuTree = $this->buildMenuTree($menus);

            return response()->json(['status' => true, 'data' => $menuTree], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    private function buildMenuTree($menus, $parentId = null, $level = 0)
    {
        $result = [];

        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $menu->level = $level;
            $menuData = $menu->toArray(); // Convert to array if needed

            // Recursively get children
            $menuData['children'] = $this->buildMenuTree($menus, $menu->id, $level + 1);

            $result[] = $menuData;
        }

        return $result;
    }

    /**
     * @OA\Get(
     *     path="/get-notifications",
     *     tags={"Common"},
     *     summary="Get notifications for the authenticated user",
     *     description="Retrieves a paginated list of notifications. For hotels and hotel employees, notifications from their associated Police Station or SP Office are fetched. Super Admin gets all notifications.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=2),
     *                         @OA\Property(property="message", type="string", example="New notification received."),
     *                         @OA\Property(property="created_at", type="string", example="2025-07-10T12:00:00Z"),
     *                         @OA\Property(property="updated_at", type="string", example="2025-07-10T12:00:00Z"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="John Doe")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             ),
     *             @OA\Property(property="canAdd", type="boolean", example=true),
     *             @OA\Property(property="canEdit", type="boolean", example=true),
     *             @OA\Property(property="canDelete", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
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


    public function getNotifications(Request $request)
    {
        try {
            if (!hasPermission('notifications', 'view')) {
                abort(403, 'Unauthorized');
            }

            $user = Auth::user();

            $query = Notification::with('user:id,name')->orderByDesc('id');

            if (in_array($user->user_type_id, [4, 5])) {
                $hotel = Hotel::where('user_id', $user->id)->first();
                $policeStationId = $hotel?->police_station_id;

                $spUserId = optional(PoliceStation::find($policeStationId)?->spOffice)->user_id;
                $policeUserIds = PoliceStation::where('id', $policeStationId)->pluck('user_id');

                $query->where(function ($q) use ($spUserId, $policeUserIds) {
                    if ($spUserId) {
                        $q->orWhere('user_id', $spUserId);
                    }
                    if ($policeUserIds->isNotEmpty()) {
                        $q->orWhereIn('user_id', $policeUserIds);
                    }
                });
            }

            $notifications = $query->paginate(10);

            return response()->json([
                'data' => $notifications,
                'canAdd' => hasPermission('notifications', 'add'),
                'canEdit' => hasPermission('notifications', 'edit'),
                'canDelete' => hasPermission('notifications', 'delete'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/get-police-station-by-city",
     *     summary="Get police stations by city ID",
     *     description="Returns a list of police stations filtered by the given city ID.",
     *     operationId="getPoliceStationsByCity",
     *     tags={"Common"},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="City ID to filter police stations",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="MG Road Police Station"),
     *                     @OA\Property(property="city_id", type="integer", example=1),
     *                     @OA\Property(property="address", type="string", example="123 MG Road, City"),
     *                     @OA\Property(property="contact_number", type="string", example="9876543210"),
     *                     @OA\Property(property="user_id", type="integer", example=12),
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
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */

    public function getPoliceStationByCity(Request $request)
    {
        try {
            $policeStations = PoliceStation::where('city_id', $request->id)->orderBy('id', 'desc')->get();
            return response()->json(['data' => $policeStations]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



}
