<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Notification;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function notifications(Request $request)
{
    if (!hasPermission('notifications', 'view')) {
        abort(403, 'Unauthorized');
    }

    if (!$request->ajax()) {
        return view('master.notification');
    }

    $user = Auth::user();
    $query = Notification::with('user:id,name')->orderBy('id', 'desc');

    switch ($user->user_type_id) {
        case 2: // SP Office
            $spOfficeIDs = SpOffice::where('user_id', $user->id)->pluck('id');
            $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');
            $policeUserIDs = PoliceStation::whereIn('id', $policeStationIDs)->pluck('user_id');
            $query->whereIn('user_id', $policeUserIDs);
            break;

        case 3: // Police Station
            $spUserID = optional(
                SpOffice::find(optional(PoliceStation::where('user_id', $user->id)->first())->sp_office_id)
            )->user_id;
            if ($spUserID) {
                $query->where('user_id', $spUserID);
            } else {
                $query->whereNull('user_id'); // No matching SP Office
            }
            break;

        case 4: // Hotel
        case 5: // Hotel Employee
            $policeStationID = optional(Hotel::where('user_id', $user->id)->first())->police_station_id;
            $spOfficeID = optional(PoliceStation::find($policeStationID))->sp_office_id;
            $spUserID = optional(SpOffice::find($spOfficeID))->user_id;
            $policeUserID = PoliceStation::where('id', $policeStationID)->pluck('user_id');

            $query->where(function ($q) use ($spUserID, $policeUserID) {
                $q->where('user_id', $spUserID)
                  ->orWhereIn('user_id', $policeUserID);
            });
            break;

        default: // Super Admin, others
            // No user filter, fetch all notifications
            break;
    }

    $data = $query->get();

    return response()->json([
        'data' => $data,
        'canAdd' => hasPermission('notifications', 'add'),
        'canEdit' => hasPermission('notifications', 'edit'),
        'canDelete' => hasPermission('notifications', 'delete'),
    ]);
}


    public function storeNotification(Request $request) {
        $request->validate([
            'title' => 'required',
            'message' => 'required',
        ]);
        $notification = new Notification();
        $notification->title = $request->title;
        $notification->message = $request->message;
        $notification->user_id = Auth::user()->id;
        $notification->save();
        return response()->json(['message' => 'Notification added successfully', 'status' => 'success']);
    }

    public function deleteNotification(Request $request) {
        $notification = Notification::find($request->id);
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully', 'status' => 'success']);
    }
}
