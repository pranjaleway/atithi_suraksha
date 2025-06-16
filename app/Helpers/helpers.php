<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAccess;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

if (!function_exists('hasPermission')) {
    function hasPermission($menuSlug, $action = null)
{
    $user = Auth::user();

    if (!$user) {
        return false; // User not logged in
    }

    // If user is super admin, grant full permissions
    if ((int) $user->user_type_id === 1) { // Ensure user_type_id is correctly checked
        return true;
    }

    // Get the menu ID using the name
    $menu = Menu::where('slug', $menuSlug)->first();

    if (!$menu) {
        return false; // Menu not found
    }

    $query = UserAccess::where('user_type_id', $user->user_type_id)
        ->where('menu_id', $menu->id);

    // Check for a specific action (add, edit, delete)
    if ($action) {
        return $query->where($action, 1)->exists();
    }

    return $query->exists();
}

}

if(!function_exists('activiyLog')) {
    function activiyLog($activity, $customerId=null) {
        $currentTime = Carbon::now('Asia/Kolkata');
        $formattedDate = $currentTime->format('Y-m-d h:i A');

        $user = Auth::user();
        $log = new ActivityLog();
        $log->user_id = $user->id;
        $log->user_name = $user->name;
        $log->activity = $activity;
        $log->date = $formattedDate;
        $log->ip_address = Request::ip();
        $log->save();
    }
}

