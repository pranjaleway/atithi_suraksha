<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
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
        $query = Notification::with('user:id,name')->orderBy('id', 'desc');

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
        // Assuming user_type_id: 4 = Hotel, 5 = Hotel Employee
        $users = User::whereIn('user_type_id', [4, 5])
            ->whereNotNull('device_token') // ensure token exists
            ->pluck('device_token');

        foreach ($users as $deviceToken) {
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
        $notification->delete();
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
            // Optional: You can notify dev/admin via logs, Slack, email, etc.
        }
    }
}
