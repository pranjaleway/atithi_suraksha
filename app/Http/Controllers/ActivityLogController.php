<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
                $query = ActivityLog::orderBy('id', 'desc');
            } else if ($user->user_type_id == 4) {
                $userIds = User::whereIn('user_type_id', [4, 5])->pluck('id');

                $query = ActivityLog::whereIn('user_id', $userIds)
                    ->orderBy('id', 'desc');
            } else {
                $query = ActivityLog::where('user_id', $user->id)
                    ->orderBy('id', 'desc');
            }


            // Check for date range filter
            if ($request->has('date_range') && !empty($request->date_range)) {
                $dates = explode(' to ', $request->date_range);

                if (count($dates) === 2) {
                    $startDate = $dates[0]; // already in Y-m-d format
                    $endDate = $dates[1];

                    // Apply date range filter (assuming "date" is a DATE or DATETIME column)
                    $query->whereDate('date', '>=', $startDate)
                        ->whereDate('date', '<=', $endDate);
                }
            }

            $data = $query->get();
            $canDelete = hasPermission('activity-log', 'delete');

            return response()->json(['data' => $data, 'canDelete' => $canDelete]);
        }

        return view('activity-log');
    }



    public function deleteActivityLog(Request $request)
    { {
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
}
