<?php
namespace App\Libraries;

class Utilities{

    public static function sendMessage($response){
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.env("ACCESS_TOKEN");
        $ch = curl_init($url);
        /* curl setting to send a json post data */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($response != "") {
        curl_exec($ch); // user will get the message
        }
        curl_close($ch);
    }

    public static function getName($senderId){
        $userUrl = "https://graph.facebook.com/v2.6/$senderId?fields=first_name,last_name,profile_pic&access_token=".env('ACCESS_TOKEN');
        $file = file_get_contents($userUrl);
        $out = json_decode($file);
        $firstname = $out->first_name;
        $lastname = $out->last_name;
        $name = $lastname.' '.$firstname;
        return $name;
    }
}