<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Google_Client;



class NotificationController extends Controller
{
    public function index()
    {
        return view('pushNotification');
    }

    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();

        $data = [
            "message" => [
                "token" => $firebaseToken,
                "notification" => [
                    "title" => $request->title,
                    "body" => $request->body,
                ]
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return back()->with('success', 'Notificacion enviada.');
    }

    public function getAccessToken()
    {
        // Ruta al archivo JSON de credenciales de Firebase Admin
        $jsonKeyFilePath = public_path('test-notificaciones-b99ef-firebase-adminsdk-780y6-dd0c4b0ff9.json');

        // Crea un cliente de Google
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setAuthConfig($jsonKeyFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase');

        // Obtiene el token de acceso
        $accessToken = $client->fetchAccessTokenWithAssertion();

        return $accessToken['access_token'];
    }

        // Puedes crear una ruta en routes/web.php para acceder a esta función
        public function showToken()
        {
            $token = $this->getAccessToken();
            return view('token', ['token' => $token]);
        }

}
