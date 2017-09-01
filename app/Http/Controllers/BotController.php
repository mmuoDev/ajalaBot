<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BotController extends Controller
{
    //
    public function bot(Request $request){

        $data = $request->all();
        $senderId = $data['entry'][0]['messaging'][0]['sender']['id']; //sender facebook id
        $messageText = $data['entry'][0]['messaging'][0]['message']['text'];//text that user sent
        //$postback = isset($input['entry'][0]['messaging'][0]['postback'])?$input['entry'][0]['messaging'][0]['postback']:""; //postback

        if(!empty($messageText)){
            $this->sendMessage($senderId, $messageText);
        }
    }
    public function sendMessage($senderId, $messageText){
        $response = [
            'recipient' => ['id' => $senderId],
            'message' => ['text' => $messageText]
        ];
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.env("ACCESS_TOKEN");
        $ch = curl_init($url);
        /* curl setting to send a json post data */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //if ($messageText != "") {
            curl_exec($ch); // user will get the message
        //}
        curl_close($ch);

    }
}
