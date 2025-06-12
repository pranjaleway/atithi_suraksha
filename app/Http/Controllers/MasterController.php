<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\UserAccess;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function menus(Request $request)
    {
        if (!hasPermission('Menus', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            $data = Menu::where('parent_id', null)->orderBy('order', 'asc')->get();
            $canAdd = hasPermission('Menus', 'add');
            $canEdit = hasPermission('Menus', 'edit');
            $canDelete = hasPermission('Menus', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.menus');
    }

    public function storeMenu(Request $request)
{
    $parent_id = $request->parent_id ? base64_decode($request->parent_id) : null;
    $parent_id = !empty($parent_id) ? (int) $parent_id : null;

    $validatedData = $request->validate([
        'name' => [
            'required',
            'string',
            Rule::unique('menus')->where(function ($query) use ($parent_id) {
                return $query->where('parent_id', $parent_id)
                             ->whereNull('deleted_at');
            }),
        ],
        'icon' => 'nullable|string',
        'visible_at_web' => 'nullable|boolean',
        'visible_at_app' => 'nullable|boolean',
    ]);

    $validatedData['order'] = Menu::max('order') + 1;
    $validatedData['parent_id'] = $parent_id;

    $data = Menu::create($validatedData);

    return response()->json([
        'data' => $data, 
        'status' => 'success', 
        'message' => 'Menu created successfully'
    ]);
}


    public function editMenu($id)
    {
        $data = Menu::find($id);
        return response()->json(['data' => $data]);
    }

    public function updateMenu(Request $request)
{
    $menu = Menu::findOrFail($request->id);
    $parent_id = $menu->parent_id; // use existing parent_id

    $validatedData = $request->validate([
        'name' => [
            'required',
            'string',
            Rule::unique('menus', 'name')
                ->where(function ($query) use ($parent_id) {
                    return $query->where('parent_id', $parent_id)
                                 ->whereNull('deleted_at');
                })
                ->ignore($menu->id),
        ],
        'icon' => 'nullable|string',
        'visible_at_web' => 'nullable|boolean',
        'visible_at_app' => 'nullable|boolean',
    ]);

    $menu->update($validatedData);

    return response()->json([
        'data' => $menu,
        'status' => 'success',
        'message' => 'Menu updated successfully'
    ]);
}

    public function changeMenuStatus(Request $request)
    {
        $menu = Menu::find($request->id);

        if ($menu) {
            // Toggle the status
            $newStatus = $menu->status == 1 ? 0 : 1;
            $menu->update(['status' => $newStatus]);

            return response()->json(['status' => 'success', 'message' => 'Menu status updated']);
        }

        return response()->json(['status' => 'error', 'message' => 'Menu not found'], 404);
    }

    public function deleteMenu(Request $request)
    {
        $data = Menu::find($request->id);
        $data->delete();
        return response()->json(['data' => $data, 'status' => 'success', 'message' => 'Menu deleted successfully']);
    }

    public function updateMenuOrder(Request $request)
    {
        $data = $request->all();

        foreach ($data as $order) {
            Menu::where('id', $order['id'])->update(['order' => $order['new_position']]);
        }

        return response()->json(['status' => 'success', 'message' => 'Order updated successfully']);
    }

    public function subMenus(Request $request, $id)
    {
        if (!hasPermission('Menus', 'view')) {
            abort(403, 'Unauthorized');
        }

        $id = base64_decode($id);
        if ($request->ajax()) {
            $data = Menu::where('parent_id', $id)->orderBy('order', 'asc')->get();
            $canAdd = hasPermission('Menus', 'add');
            $canEdit = hasPermission('Menus', 'edit');
            $canDelete = hasPermission('Menus', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.sub-menus');

    }

    public function userType(Request $request)
    {
        if (!hasPermission('User Type', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            if(Auth::user()->role == 0){
                $data = UserType::all();
            } else{
                $data = UserType::where('user_type', '!=', 'Admin')->get();
            }
            
            $canAdd = hasPermission('User Type', 'add');
            $canEdit = hasPermission('User Type', 'edit');
            $canDelete = hasPermission('User Type', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.user-type');
    }

    public function storeUserType(Request $request)
    {
        $existingUserType = UserType::withTrashed()->where('user_type', $request['user_type'])->first();

    if ($existingUserType) {
        if ($existingUserType->trashed()) {
            $existingUserType->restore();

            return response()->json([
                'data' => $existingUserType,
                'status' => 'success',
                'message' => 'User Type restored successfully'
            ]);
        }
    }
        $validatedData = $request->validate([
            'user_type' => 'required|string|unique:user_types,user_type',
        ]);
        $data = UserType::create($validatedData);
        return response()->json(['data' => $data, 'status' => 'success', 'message' => 'User Type created successfully']);
    }

    public function editUserType($id)
    {
        $data = UserType::find($id);
        return response()->json(['data' => $data]);
    }

    public function updateUserType(Request $request)
    {
        $validatedData = $request->validate([
            'user_type' => 'required|string|unique:user_types,user_type,' . $request->id,
        ]);
        $data = UserType::find($request->id);
        $data->update($validatedData);
        return response()->json(['data' => $data, 'status' => 'success', 'message' => 'User Type updated successfully']);
    }

    public function changeUserTypeStatus(Request $request)
    {
        $userType = UserType::find($request->id);
        if ($userType) {
            $newStatus = $userType->status == 1 ? 0 : 1;
            $userType->update(['status' => $newStatus]);
            return response()->json(['status' => 'success', 'message' => 'User Type status updated']);
        }
        return response()->json(['status' => 'error', 'message' => 'User Type not found'], 404);
    }

    public function deleteUserType(Request $request)
    {
        $data = UserType::find($request->id);
        $data->delete();
        return response()->json(['data' => $data, 'status' => 'success', 'message' => 'User Type deleted successfully']);
    }

    private function buildMenuTree($menus, $parentId = null, $level = 0)
{
    $result = [];

    foreach ($menus->where('parent_id', $parentId) as $menu) {
        $menu->level = $level;
        $result[] = $menu;

        // Recursively get children
        $children = $this->buildMenuTree($menus, $menu->id, $level + 1);
        $result = array_merge($result, $children);
    }

    return $result;
}


    public function userAccess(Request $request, $id)
    {
        if (!hasPermission('User Type', 'add')) {
            abort(403, 'Unauthorized');
        }

        $id = base64_decode($id);

        if ($request->ajax()) {
            // Get all user access for this user type, keyed by menu_id for fast lookup
            $userAccessData = UserAccess::where('user_type_id', $id)->get()->keyBy('menu_id');
    
            // Get only menus which are assigned (present in UserAccess)
            $menuIds = $userAccessData->keys(); // Only menus that exist in UserAccess
            $menus = Menu::select('id', 'name', 'parent_id')
                ->whereIn('id', $menuIds)
                ->get();
    
            $menuTree = $this->buildMenuTree($menus); // Still use hierarchy if needed
    
            $data = collect($menuTree)->map(function ($menu) use ($userAccessData) {
                $access = $userAccessData[$menu->id] ?? null;
    
                return [
                    'id' => $access->id ?? null,
                    'menu' => [
                        'name' => str_repeat('- ', $menu->level) . $menu->name,
                        'level' => $menu->level,
                    ],
                    'view' => $access->view ?? 0,
                    'add' => $access->add ?? 0,
                    'edit' => $access->edit ?? 0,
                    'delete' => $access->delete ?? 0,
                ];
            });
    
            return response()->json([
                'data' => $data,
                'canAdd' => hasPermission('User Access', 'add'),
                'canEdit' => hasPermission('User Access', 'edit'),
                'canDelete' => hasPermission('User Access', 'delete'),
            ]);
        }
        

        $assignedMenuIds = UserAccess::where('user_type_id', $id)
            ->pluck('menu_id')
            ->toArray();

        $menus = Menu::where('status', 1) 
            ->whereNotIn('id', $assignedMenuIds) 
            ->with([
                'subMenus' => function ($query) use ($assignedMenuIds) {
                    $query->where('status', 1)
                        ->whereNotIn('id', $assignedMenuIds);
                }
            ])
            ->get();


        return view('master.user-access', compact('id', 'menus'));
    }


    public function storeUserAccess(Request $request)
    {
        $validatedData = $request->validate([
            'user_type_id' => 'required',
            'menu_id' => 'required|array',
            'menu_id.*' => 'exists:menus,id',
        ]);

        $user_type_id = base64_decode($request->user_type_id);

        // Get existing menu access for the user type
        $existingAccess = UserAccess::where('user_type_id', $user_type_id)
            ->pluck('menu_id')
            ->toArray();

        $userAccessData = [];
        $selectedMenus = Menu::whereIn('id', $validatedData['menu_id'])
            ->where('status', 1) // Only active menus
            ->get();

        foreach ($selectedMenus as $menu) {
            // Collect parent menu IDs recursively
            $menuHierarchy = $this->getParentMenus($menu);

            foreach ($menuHierarchy as $menuId) {
                if (!in_array($menuId, $existingAccess)) {
                    $userAccessData[] = [
                        'user_type_id' => $user_type_id,
                        'menu_id' => $menuId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $existingAccess[] = $menuId; // Prevent duplicate addition
                }
            }
        }

        // Insert the new records into the database
        if (!empty($userAccessData)) {
            UserAccess::insert($userAccessData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User Access updated successfully',
        ]);
    }

    /**
     * Recursively get all parent menus of a given menu
     */
    private function getParentMenus($menu)
    {
        $menuIds = [$menu->id];

        while ($menu->parent_id) {
            $menu = Menu::find($menu->parent_id);
            if ($menu) {
                $menuIds[] = $menu->id;
            } else {
                break;
            }
        }

        return array_reverse($menuIds);
    }

    public function updateAccessPermission(Request $request, $id)
    {
        UserAccess::where('id', $request->input('id'))
            ->update([
                $request->input('colName') => $request->input('isChecked'),
            ]);
    }

    public function deleteUserAccess(Request $request)
    {
        UserAccess::where('id', $request->id)->delete();

        return response()->json(['status' => 'success', 'message' => 'User Access deleted successfully']);

    }
}
