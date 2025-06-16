<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Document;
use App\Models\Menu;
use App\Models\State;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    // Menu CRUD
    public function menus(Request $request)
    {
        if (!hasPermission('menu', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            $data = Menu::where('parent_id', null)->orderBy('order', 'asc')->get();
            $canAdd = hasPermission('menu', 'add');
            $canEdit = hasPermission('menu', 'edit');
            $canDelete = hasPermission('menu', 'delete');
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
        $validatedData['slug'] = Str::slug($validatedData['name']);

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
        if (!hasPermission('menu', 'view')) {
            abort(403, 'Unauthorized');
        }

        $id = base64_decode($id);
        if ($request->ajax()) {
            $data = Menu::where('parent_id', $id)->orderBy('order', 'asc')->get();
            $canAdd = hasPermission('menu', 'add');
            $canEdit = hasPermission('menu', 'edit');
            $canDelete = hasPermission('menu', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.sub-menus');

    }

    // User Type CRUD
    public function userType(Request $request)
    {
        if (!hasPermission('user-type', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            $data = UserType::whereNot('id', 1)->get();

            $canAdd = hasPermission('user-type', 'add');
            $canEdit = hasPermission('user-type', 'edit');
            $canDelete = hasPermission('user-type', 'delete');
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


    //User Access
    public function userAccess(Request $request, $id)
    {
        if (!hasPermission('user-type', 'add')) {
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
                'canAdd' => hasPermission('user-type', 'add'),
                'canEdit' => hasPermission('user-type', 'edit'),
                'canDelete' => hasPermission('user-type', 'delete'),
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

    //State CRUD

    public function states(Request $request)
    {
        if (!hasPermission('states', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($request->ajax()) {
            $data = State::orderBy('name', 'asc')->get();
            $canAdd = hasPermission('states', 'add');
            $canEdit = hasPermission('states', 'edit');
            $canDelete = hasPermission('states', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.states');
    }

    public function storeState(Request $request)
    {
        $existingState = State::withTrashed()->where('name', $request['name'])->first();

        if ($existingState) {
            if ($existingState->trashed()) {
                $existingState->restore();

                return response()->json([
                    'data' => $existingState,
                    'status' => 'success',
                    'message' => 'User Type restored successfully'
                ]);
            }
        }

        $validatedData = $request->validate([
            'name' => 'required|string|unique:states,name',
        ]);

        $validatedData['country_id'] = 1;

        State::create($validatedData);
        return response()->json(['status' => 'success', 'message' => 'State created successfully']);
    }

    public function editState(Request $request)
    {
        $data = State::find($request->id);
        return response()->json(['data' => $data]);
    }

    public function updateState(Request $request)
    {
        $existingState = State::withTrashed()->where('name', $request['name'])->first();

        if ($existingState) {
            if ($existingState->trashed()) {
                $existingState->restore();

                return response()->json([
                    'data' => $existingState,
                    'status' => 'success',
                    'message' => 'User Type restored successfully'
                ]);
            }
        }
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'required|unique:states,name,' . $request->id,
        ]);
        State::where('id', $request->id)->update($validatedData);
        return response()->json(['status' => 'success', 'message' => 'State updated successfully']);
    }

    public function deleteState(Request $request)
    {
        State::where('id', $request->id)->delete();
        return response()->json(['status' => 'success', 'message' => 'State deleted successfully']);
    }

    public function changeStateStatus(Request $request)
    {
        $state = State::find($request->id);

        if ($state) {
            // Toggle the status
            $newStatus = $state->status == 1 ? 0 : 1;
            $state->update(['status' => $newStatus]);

            return response()->json(['status' => 'success', 'message' => 'State status updated']);
        }

        return response()->json(['status' => 'error', 'message' => 'Menu not found'], 404);
    }

    public function cities(Request $request)
    {
        if (!hasPermission('cities', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($request->ajax()) {
            $data = City::select('cities.*')
                ->join('states', 'cities.state_id', '=', 'states.id')
                ->where('states.status', 1)
                ->orderBy('states.name', 'asc')
                ->orderBy('cities.name', 'asc')
                ->with('state:id,name')
                ->get();

            $canAdd = hasPermission('cities', 'add');
            $canEdit = hasPermission('cities', 'edit');
            $canDelete = hasPermission('cities', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        $states = State::where('status', 1)->orderBy('name', 'asc')->get();
        return view('master.cities', compact('states'));
    }

    public function storeCity(Request $request)
    {
        $existingCity = City::withTrashed()->where('name', $request['name'])->first();

        if ($existingCity) {
            if ($existingCity->trashed()) {
                $existingCity->restore();

                return response()->json([
                    'data' => $existingCity,
                    'status' => 'success',
                    'message' => 'User Type restored successfully'
                ]);
            }
        }

        $validatedData = $request->validate([
            'name' => 'required|string|unique:cities,name',
            'state_id' => 'required',
        ]);

        $validatedData['country_id'] = 1;

        City::create($validatedData);
        return response()->json(['status' => 'success', 'message' => 'City created successfully']);
    }

    public function editCity(Request $request)
    {
        $data = City::find($request->id);
        return response()->json(['data' => $data]);
    }

    public function updateCity(Request $request)
    {
        $existingCity = City::withTrashed()->where('name', $request['name'])->first();

        if ($existingCity) {
            if ($existingCity->trashed()) {
                $existingCity->restore();

                return response()->json([
                    'data' => $existingCity,
                    'status' => 'success',
                    'message' => 'User Type restored successfully'
                ]);
            }
        }
        $validatedData = $request->validate([
            'name' => 'required|unique:cities,name,' . $request->id,
            'state_id' => 'required',
        ]);
        City::where('id', $request->id)->update($validatedData);
        return response()->json(['status' => 'success', 'message' => 'City updated successfully']);
    }

    public function deleteCity(Request $request)
    {
        City::where('id', $request->id)->delete();
        return response()->json(['status' => 'success', 'message' => 'City deleted successfully']);
    }

    public function changeCityStatus(Request $request)
    {
        $city = City::find($request->id);

        if ($city) {
            // Toggle the status
            $newStatus = $city->status == 1 ? 0 : 1;
            $city->update(['status' => $newStatus]);

            return response()->json(['status' => 'success', 'message' => 'City status updated']);
        }

        return response()->json(['status' => 'error', 'message' => 'Menu not found'], 404);
    }

    public function getCitiesByState(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)->get();
        return response()->json(['data' => $cities]);
    }

    public function documents(Request $request){
        if(!hasPermission('document', 'view')) {
            abort(403, 'Unauthorized');
        }

        if($request->ajax()){
            $data = Document::all();
            $canAdd = hasPermission('document', 'add');
            $canEdit = hasPermission('document', 'edit');
            $canDelete = hasPermission('document', 'delete');
            return response()->json(['data' => $data, 'canAdd' => $canAdd, 'canEdit' => $canEdit, 'canDelete' => $canDelete]);
        }
        return view('master.documents');
    }

    public function storeDocument(Request $request){
        $existingDocument = Document::withTrashed()->where('name', $request['name'])->first();

    if ($existingDocument) {
        if ($existingDocument->trashed()) {
            $existingDocument->restore();

            return response()->json([
                'data' => $existingDocument,
                'status' => 'success',
                'message' => 'Document restored successfully'
            ]);
        }
    }
    $validatedData = $request->validate([
        'name' => 'required|string|unique:documents,name',
    ]);


    $data = Document::create($validatedData);

    return response()->json([
        'data' => $data,
        'status' => 'success',
        'message' => 'Document created successfully'
    ]);
    }

    public function changeDocumentStatus(Request $request){
        $document = Document::find($request->id);

        if ($document) {
            // Toggle the status
            $newStatus = $document->status == 1 ? 0 : 1;
            $document->update(['status' => $newStatus]);

            // Return the updated status
            return response()->json(['status' => 'success', 'message' => 'Document status updated']);
        }
        return response()->json(['status' => 'error', 'message' => 'Document not found'], 404);
    }

    public function editDocument(Request $request){
        $data = Document::find($request->id);
        return response()->json(['data' => $data]);
    }

    public function updateDocument(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|unique:documents,name,' . $request->id,
        ]);
        $data = Document::find($request->id);
        $data->update($validatedData);
        return response()->json(['data' => $data, 'status' => 'success', 'message' => 'Document updated successfully']);
    }

    public function deleteDocument(Request $request){
        Document::where('id', $request->id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Document deleted successfully']);
    }
}
