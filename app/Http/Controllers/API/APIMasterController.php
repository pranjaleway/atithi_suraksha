<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Menu;
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
            $data = Document::where('status', 1)->get();
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
            $data = State::where('country_id', 1)->where('status', 1)->get();
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
            $data = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'state' => [
                        'id' => $item->state->id,
                        'state_name' => $item->state->name,
                    ],
                    'status' => $item->status
                ];
            });

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


}
