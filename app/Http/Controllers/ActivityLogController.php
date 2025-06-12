<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;


class ActivityLogController extends Controller
{
    public function activityLog(Request $request)
    {
        if (!hasPermission('Activity Log', 'view')) {
            abort(403, 'Unauthorized');
        }
    
        if ($request->ajax()) {
            $query = ActivityLog::orderBy('id', 'desc');
    
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
            $canDelete = hasPermission('Activity Log', 'delete');
    
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
