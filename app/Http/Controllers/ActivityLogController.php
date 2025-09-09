<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function activityLog(Request $request)
    {
        if (!hasPermission('activity-log', 'view')) {
            abort(403, 'Unauthorized');
        }

        if ($request->ajax()) {
            $user = Auth::user();

            if ($user->user_type_id == 1) {
                // Admin → see all
                $query = ActivityLog::orderBy('id', 'desc');

            } elseif ($user->user_type_id == 4) {
                // Hotel Owner → see his + employees
                $hotel = Hotel::where('user_id', $user->id)->first();

                if ($hotel) {
                    $employeeIds = HotelEmployee::where('hotel_id', $hotel->id)->pluck('user_id');
                    $userIds = collect([$user->id])->merge($employeeIds);

                    $query = ActivityLog::whereIn('user_id', $userIds)
                        ->orderBy('id', 'desc');
                } else {
                    $query = ActivityLog::where('user_id', $user->id)
                        ->orderBy('id', 'desc');
                }

            } elseif ($user->user_type_id == 5) {
                // Hotel Employee → see his hotel (owner + all employees)
                $hotelEmployee = HotelEmployee::where('user_id', $user->id)->first();

                if ($hotelEmployee) {
                    $hotel = Hotel::find($hotelEmployee->hotel_id);

                    if ($hotel) {
                        $employeeIds = HotelEmployee::where('hotel_id', $hotel->id)->pluck('user_id');
                        $userIds = collect([$hotel->user_id]) // owner
                            ->merge($employeeIds); // employees

                        $query = ActivityLog::whereIn('user_id', $userIds)
                            ->orderBy('id', 'desc');
                    } else {
                        $query = ActivityLog::where('user_id', $user->id)
                            ->orderBy('id', 'desc');
                    }
                } else {
                    $query = ActivityLog::where('user_id', $user->id)
                        ->orderBy('id', 'desc');
                }

            } else {
                // Other users → see own logs
                $query = ActivityLog::where('user_id', $user->id)
                    ->orderBy('id', 'desc');
            }

            // Date filter
            if ($request->filled('date_range')) {
                $dates = explode(' to ', $request->date_range);

                if (count($dates) === 2) {
                    $startDate = trim($dates[0]);
                    $endDate = trim($dates[1]);

                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }
            }

            $data = $query->get();
            $canDelete = hasPermission('activity-log', 'delete');

            return response()->json(['data' => $data, 'canDelete' => $canDelete]);
        }

        return view('activity-log');
    }





    public function deleteActivityLog(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:activity_logs,id'
        ]);

        ActivityLog::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Selected activity logs have been deleted successfully.'
        ]);

    }
}
