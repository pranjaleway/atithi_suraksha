<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelEmployee;
use App\Models\Notification;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;

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
        $currentUserId = $user->id;
        $query = Notification::with('user:id,name')->latest('id')
            ->where(function ($q) use ($user) {
                $q->whereNull('deleted_by')
                    ->orWhereRaw("FIND_IN_SET(?, deleted_by) = 0", [$user->id]);
            });

        switch ($user->user_type_id) {
            case 2: // SP Office
                $spOfficeIDs = SpOffice::where('user_id', $user->id)->pluck('id');
                $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');

                $policeUserIDs = PoliceStation::whereIn('id', $policeStationIDs)->pluck('user_id');
                $hotelUserIDs = Hotel::whereIn('police_station_id', $policeStationIDs)->pluck('user_id');

                $query->where(function ($q) use ($policeUserIDs, $hotelUserIDs, $spOfficeIDs) {
                    $q->whereIn('user_id', $policeUserIDs)
                        ->orWhereIn('user_id', $hotelUserIDs)
                        ->orWhereIn('sp_id', $spOfficeIDs);
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
                            $q->orWhereIn('user_id', $hotelUserIDs)->whereNull('sp_id');
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

        $notifications = $query->get()->map(function ($notification) use ($currentUserId) {
            $readBy = $notification->read_by ? explode(',', $notification->read_by) : [];

            return [
                'id'        => $notification->id,
                'is_read'      => in_array($currentUserId, $readBy),
                'title'     => $notification->title,
                'message'   => $notification->message,
                'image'     => $notification->image,
                'created'   => $notification->created_at->format('Y-m-d H:i:s'),
                'user_id'   => $notification->user_id,
                'user'      => $notification->user,
                'status'    => $notification->status,
            ];
        });

        return response()->json([
            'data' => $notifications,
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
        $notification->user_id = Auth::id();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('notifications', 'public');
            $notification->image = $imagePath;
        }

        $notification->save();

        // 🔔 Send Push Notification to Hotel and Hotel Employees
        $this->sendNotificationToHotels($notification);

        return response()->json(['message' => 'Notification added successfully', 'status' => 'success']);
    }


    protected function sendNotificationToHotels($notification)
    {
        $sender = Auth::user();

        if (in_array($sender->user_type_id, [4, 5])) {
            return;
        }

        $hotelUserIDs = collect();
        $hotelIDs = collect(); // for fetching hotel employees

        switch ($sender->user_type_id) {
            case 2: // SP Office
                $spOfficeIDs = SpOffice::where('user_id', $sender->id)->pluck('id');
                $policeStationIDs = PoliceStation::whereIn('sp_office_id', $spOfficeIDs)->pluck('id');
                $hotels = Hotel::whereIn('police_station_id', $policeStationIDs)->get(['id', 'user_id']);
                $hotelUserIDs = $hotels->pluck('user_id');
                $hotelIDs = $hotels->pluck('id');
                break;

            case 3: // Police Station
                $policeStation = PoliceStation::where('user_id', $sender->id)->first();
                if ($policeStation) {
                    $hotels = Hotel::where('police_station_id', $policeStation->id)->get(['id', 'user_id']);
                    $hotelUserIDs = $hotels->pluck('user_id');
                    $hotelIDs = $hotels->pluck('id');
                }
                break;

            case 4: // Hotel
                $hotel = Hotel::where('user_id', $sender->id)->first();
                if ($hotel) {
                    $hotelUserIDs = collect([$sender->id]); // send to self
                    $hotelIDs = collect([$hotel->id]);       // send to own hotel employees
                }
                break;

            default: // Super Admin or others
                $hotels = Hotel::all(['id', 'user_id']);
                $hotelUserIDs = $hotels->pluck('user_id');
                $hotelIDs = $hotels->pluck('id');
                break;
        }

        // Get hotel employee user IDs from HotelEmployee model
        $employeeUserIDs = HotelEmployee::whereIn('hotel_id', $hotelIDs)
            ->pluck('user_id');

        // Merge and deduplicate
        $targetUserIDs = $hotelUserIDs->merge($employeeUserIDs)->unique();

        // Get device tokens for users with device_token
        $deviceTokens = User::whereIn('id', $targetUserIDs)
            ->whereNotNull('device_token')
            ->pluck('device_token');

        foreach ($deviceTokens as $deviceToken) {
            $this->send_push_notification($deviceToken, [
                'title' => $notification->title,
                'body' => $notification->message,
                'image' => $notification->image
            ]);
        }
    }




    public function deleteNotification(Request $request)
    {
        $notification = Notification::find($request->id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found', 'status' => 'error'], 404);
        }

        $currentUserId = Auth::id();

        $deletedBy = $notification->deleted_by
            ? explode(',', $notification->deleted_by)
            : [];

        if (!in_array($currentUserId, $deletedBy)) {
            $deletedBy[] = $currentUserId;
            $notification->deleted_by = implode(',', $deletedBy);
            $notification->save();
        }
        return response()->json(['message' => 'Notification deleted successfully', 'status' => 'success']);
    }


    public function send_push_notification($device_token, $notification)
    {
        $serviceAccountPath = storage_path('app/firebase/service-account.json');
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);

        $guzzle = new Client();
        $httpHandler = new Guzzle6HttpHandler($guzzle);
        $token = $credentials->fetchAuthToken($httpHandler);
        $accessToken = $token['access_token'];

        $projectId = json_decode(file_get_contents($serviceAccountPath), true)['project_id'];

        $notificationPayload = [
            'title' => $notification['title'] ?? 'Default Title',
            'body' => $notification['body'] ?? 'Default Body',
        ];

        if (!empty($notification['image'])) {
            $notificationPayload['image'] = $notification['image'];
        }

        $extraNotificationData = [
            'message' => json_encode($notificationPayload),
            'moredata' => 'dd',
        ];

        $payload = [
            'message' => [
                'token' => $device_token,
                'notification' => $notificationPayload,
                'data' => $extraNotificationData,
                'android' => ['priority' => 'high'],
                'apns' => ['payload' => ['aps' => ['mutable-content' => 1]]],
                'webpush' => ['headers' => ['Urgency' => 'high']],
            ],
        ];

        try {
            $guzzle->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }
    }

    public function alerts(Request $request)
    {
        if (!hasPermission('notifications', 'view')) {
            abort(403, 'Unauthorized');
        }
        if ($request->ajax()) {
            $data = Notification::with('user:id,name')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

            return response()->json([
                'data' => $data,
                'canAdd' => hasPermission('notifications', 'add'),
                'canEdit' => hasPermission('notifications', 'edit'),
                'canDelete' => hasPermission('notifications', 'delete'),
            ]);
        }
        return view('master.alerts');
    }

    public function deleteAlert(Request $request)
    {
        $notification = Notification::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found or unauthorized',
                'status' => 'error'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
            'status' => 'success'
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        try {
            $notification = Notification::find($request->id);
            if (!$notification) {
                return response()->json(['message' => 'Notification not found', 'status' => 'error'], 404);
            }
            $currentUserId = Auth::id();

            $readBy = $notification->read_by
                ? explode(',', $notification->read_by)
                : [];

            if (!in_array($currentUserId, $readBy)) {
                $readBy[] = $currentUserId;
                $notification->read_by = implode(',', $readBy);
                $notification->save();
            }
            return response()->json(['message' => 'Notification marked as read', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 500);
        }
    }
}
