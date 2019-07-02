<?php

namespace App\Http\Controllers;

use App\Models\PharmacyMrConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function showAllUsers()
    {
        return response()->json(User::all());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'user_mobile' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        $data = $request->all();
        $userModel = new User();
        $user = $userModel->create($data);

        return response()->json($user);
    }

    public function sendPushNotification($title, $messageBody, $message, $imageUrl, $urlNeedsToOpen, $sendType)
    {

        $res = array();

        $res['data']['title'] = isset($title) ? $title : '';
        $res['data']['is_background'] = true;
        $res['data']['message'] = isset($messageBody) ? $messageBody : '';
        $res['data']['imageUrl'] = isset($imageUrl) ? $imageUrl : '';
        $res['data']['urlNeedsToOpen'] = isset($urlNeedsToOpen) ? $urlNeedsToOpen : '';
        $res['data']['clickType'] = 2;
        $res['data']['timestamp'] = date('Y-m-d H:i:s');
        $res['data']['notificationData'] = $message;


        $firebaseApiKey = 'AIzaSyDbGByXy5gnQOCrAmJH1dG9heDq5U4peZk';

        $firebaseIds = array(
            'eKPcwtO1nHg:APA91bHZV0bKpTkE-iWGKMGQOkJVdSlhsqcNKfOf8Tsvn1qhvGdASloAsUEVko8l4xOQnIG9dCfrDdsMxV1GAMO0h7o8B759BqgGiyarLndADaSf_Mrj_hwN61xVHQmsk7N4PYOVkDNj'
        );


        if ($sendType == 1) { // Send to individual ids

            $fields = array(
                'registration_ids' => $firebaseIds,
                'data' => $res
            );

        } else { // Send to all

            $fields = array(
                'to' => '/topics/global',
                'data' => $res
            );

        }

        $headers = array(
            'Authorization: key=' . $firebaseApiKey,
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            $result = curl_error($ch);
        }

        // Close connection
        curl_close($ch);

        return $result;
    }

    public function verifyUser(Request $request)
    {
        $this->validate($request, [
            'verification_pin' => 'required',
            'user_mobile' => 'required',
        ]);

        $user = DB::table('users')
            ->where('user_mobile', $request->user_mobile)
            ->where('verification_pin', $request->verification_pin)
            ->first();

        return response()->json($user);

    }

    public function getVerificationCode(Request $request)
    {
        $this->validate($request, [
            'user_mobile' => 'required',
        ]);

        $user = DB::table('users')
            ->where('user_mobile', $request->user_mobile)
            ->value('verification_pin');

        return response()->json($user);

    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function mrConnection(Request $request)
    {
       $phamracy_mr_connection = PharmacyMrConnection::create($request->all());

        return response()->json(['success' => true, 'data' => $phamracy_mr_connection]);

    }


}
