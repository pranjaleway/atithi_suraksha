<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Notification;
use App\Models\PoliceStation;
use App\Models\SpOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use Illuminate\Support\Facades\Log;

class APINotificationController extends Controller
{

    /**
     * @OA\Post(
     *     path="/get-notifications",
     *     tags={"Notifications"},
     *     summary="Get notifications",
     *     description="Fetches a list of notifications based on user type. Hotel owners and employees only receive notifications relevant to their associated police station or SP office. Super Admin and other roles get all notifications.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of notifications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Important Notice"),
     *                     @OA\Property(property="message", type="string", example="All hotels must submit their records by tomorrow."),
     *                     @OA\Property(property="user_id", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-16T10:30:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-16T10:30:00Z"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         description="Details of the user who created the notification",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Police Officer John")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="canAdd", type="boolean", example=true, description="Permission to add notifications"),
     *             @OA\Property(property="canEdit", type="boolean", example=true, description="Permission to edit notifications"),
     *             @OA\Property(property="canDelete", type="boolean", example=false, description="Permission to delete notifications")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */

    public function getNotifications(Request $request)
    {
        if (!hasPermission('notifications', 'view')) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        $query = Notification::with('user:id,name')->latest('id');

        if (in_array($user->user_type_id, [4, 5])) { // Hotel or Hotel Employee
            $hotel = Hotel::where('user_id', $user->id)->first(['police_station_id']);

            if ($hotel && $hotel->police_station_id) {
                $policeStation = PoliceStation::find($hotel->police_station_id, ['id', 'user_id', 'sp_office_id']);

                if ($policeStation) {
                    $spUserID = SpOffice::where('id', $policeStation->sp_office_id)->value('user_id');
                    $policeUserID = $policeStation->user_id;

                    $query->where(function ($q) use ($spUserID, $policeUserID) {
                        if ($spUserID) {
                            $q->where('user_id', $spUserID);
                        }
                        if ($policeUserID) {
                            $q->orWhere('user_id', $policeUserID);
                        }
                    });
                }
            }
        }

        return response()->json([
            'data' => $query->get(),
            'canAdd' => hasPermission('notifications', 'add'),
            'canEdit' => hasPermission('notifications', 'edit'),
            'canDelete' => hasPermission('notifications', 'delete'),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/add-notification",
     *     tags={"Notifications"},
     *     summary="Create a new notification",
     *     description="Allows authenticated users to create a new notification. The image should be sent as a URL or base64 string.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "message"},
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Important Update",
     *                 description="The title of the notification"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="All hotels must submit their records by tomorrow.",
     *                 description="The message content of the notification"
     *             ),
     *             @OA\Property(
     *                 property="image",
     *                 type="string",
     *                 nullable=true,
     *                 example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...",
     *                 description="Optional image in base64 format or a valid image URL"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Notification created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notification added successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "title": {"The title field is required."},
     *                     "message": {"The message field is required."}
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */


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


    // public function send_push_notification($device_token, $notification)
    // {
    //     $serviceAccountPath = storage_path('app/firebase/service-account.json');
    //     $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
    //     $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);

    //     $guzzle = new Client();
    //     $httpHandler = new Guzzle6HttpHandler($guzzle);
    //     $token = $credentials->fetchAuthToken($httpHandler);
    //     $accessToken = $token['access_token'];

    //     $projectId = json_decode(file_get_contents($serviceAccountPath), true)['project_id'];

    //     $notificationPayload = [
    //         'title' => $notification['title'] ?? 'Default Title',
    //         'body' => $notification['body'] ?? 'Default Body',
    //     ];

    //     $extraNotificationData = [
    //         'message' => json_encode($notificationPayload),
    //         'moredata' => 'dd',
    //     ];

    //     $payload = [
    //         'message' => [
    //             'token' => $device_token,
    //             'notification' => $notificationPayload,
    //             'data' => $extraNotificationData,
    //             'android' => ['priority' => 'high'],
    //             'apns' => ['payload' => ['aps' => ['mutable-content' => 1]]],
    //             'webpush' => ['headers' => ['Urgency' => 'high']],
    //         ],
    //     ];

    //     try {
    //         $response = $guzzle->post(
    //             "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
    //             [
    //                 'headers' => [
    //                     'Authorization' => 'Bearer ' . $accessToken,
    //                     'Content-Type' => 'application/json',
    //                 ],
    //                 'json' => $payload,
    //             ]
    //         );

    //         return json_decode((string) $response->getBody(), true);
    //     } catch (\Exception $e) {
    //         Log::error('FCM Error: ' . $e->getMessage());
    //         return [
    //             'error' => true,
    //             'message' => $e->getMessage(),
    //         ];
    //     }
    // }


}
