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

        // Load view for non-AJAX requests
        if (!$request->ajax()) {
            return view('master.notification');
        }

        $user = Auth::user();
        $query = Notification::with('user:id,name')->orderBy('id', 'desc');

        switch ($user->user_type_id) {
            case 2: //  SP Office
                $spOfficeIDs = SpOffice::where('user_id', $user->id)->pluck('id');
                $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');

                $policeUserIDs = PoliceStation::whereIn('id', $policeStationIDs)->pluck('user_id');
                $hotelUserIDs = Hotel::whereIn('police_station_id', $policeStationIDs)->pluck('user_id');

                $query->where(function ($q) use ($policeUserIDs, $hotelUserIDs) {
                    $q->whereIn('user_id', $policeUserIDs)
                        ->orWhereIn('user_id', $hotelUserIDs);
                });
                break;

            case 3: //  Police Station
                $policeStation = PoliceStation::where('user_id', $user->id)->first();
                if ($policeStation) {
                    $spUserID = optional(SpOffice::find($policeStation->sp_office_id))->user_id;
                    $hotelUserIDs = Hotel::where('police_station_id', $policeStation->id)->pluck('user_id');

                    $query->where(function ($q) use ($spUserID, $hotelUserIDs) {
                        if ($spUserID) {
                            $q->where('user_id', $spUserID);
                        }
                        if ($hotelUserIDs->isNotEmpty()) {
                            $q->orWhereIn('user_id', $hotelUserIDs);
                        }
                    });
                }
                break;

            case 4: //  Hotel
            case 5: //  Hotel Employee
                $hotel = Hotel::where('user_id', $user->id)->first();
                if ($hotel) {
                    $policeStation = PoliceStation::find($hotel->police_station_id);
                    $spUserID = optional(SpOffice::find(optional($policeStation)->sp_office_id))->user_id;
                    $policeUserID = optional($policeStation)->user_id;

                    $query->where(function ($q) use ($spUserID, $policeUserID) {
                        if ($spUserID) {
                            $q->where('user_id', $spUserID);
                        }
                        if ($policeUserID) {
                            $q->orWhere('user_id', $policeUserID);
                        }
                    });
                }
                break;

            default: // Super Admin & Others
                // No filter - fetch all notifications
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



    public function storeNotification(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'message' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        $notification = new Notification();
        $notification->title = $request->title;
        $notification->message = $request->message;
        $notification->user_id = Auth::user()->id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('notifications', 'public');
            $notification->image = $imagePath;
        }
        $notification->save();
        return response()->json(['message' => 'Notification added successfully', 'status' => 'success']);
    }

    public function deleteNotification(Request $request)
    {
        $notification = Notification::find($request->id);
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully', 'status' => 'success']);
    }
}
