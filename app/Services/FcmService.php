<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Google\Client as GoogleClient;


class FcmService
{
    /**
     * Send FCM notification to a user
     *
     * @param int $userId
     * @param string $title
     * @param string $description
     */
    public function sendFcmNotification(int $userId, string $title, string $description)
    {
        $user = \App\Models\User::find($userId);
        $fcm = $user->fcm_token;

        if (!$fcm) {
            return false;
        }

        $projectId = config('services.fcm.project_id');

        $credentialsFilePath = base_path('storage/app/json/service-account.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return false;
        } else {
            return true;
        }
    }
}
