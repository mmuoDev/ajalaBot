<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\ManageSession;
use Mail;


class BotController extends Controller
{
    //
    public function test(){
        $trips = DB::select("select * from trips");
        var_dump($trips);exit;
    }
    public function bot(Request $request){

        $data = $request->all();
        $senderId = $data['entry'][0]['messaging'][0]['sender']['id']; //sender facebook id
        $messageText = isset($data['entry'][0]['messaging'][0]['message']['text'])?$data['entry'][0]['messaging'][0]['message']['text']:"";//text that user sent
        $postback = isset($data['entry'][0]['messaging'][0]['postback'])?$data['entry'][0]['messaging'][0]['postback']:""; //postback the user sent

        if (!empty($postback)) { //Return only if there are payloads
            $payloads = $data['entry'][0]['messaging'][0]['postback']['payload'];
            $this->sendMessage($senderId, $payloads);
        } else if(!empty($messageText)){
            $this->sendMessage($senderId, $messageText);
        }
        //return ['senderId' => $senderId, 'message' => $messageText]; //else return text

    }
    public function sendMessage($senderId, $message){
        //$response = null;
        $userUrl = "https://graph.facebook.com/v2.6/$senderId?fields=first_name,last_name,profile_pic&access_token=".env('ACCESS_TOKEN');
        $file = file_get_contents($userUrl);
        $output = json_decode($file);
        $firstname = $output->first_name;
        $lastname = $output->last_name;
        $name = $lastname.' '.$firstname;

        $trip = null;
        if($message == 'hi') {
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $message]
            ];

        }
        else if ($message == "GET_STARTED_PAYLOAD"){
            $output = ["attachment" => [
                "type" => "template",
                "payload" => [
                    "template_type" => "button",
                    "text" => "What do you want to do next? ;)",
                    "buttons" => [
                        [
                            "type" => "postback",
                            "title" => "Find Trips",
                            "payload" => "FIND_TRIPS"
                        ],
                        [
                            "type" => "postback",
                            "title" => "Request A Trip ",
                            "payload" => "REQUEST_A_TRIP"
                        ]
                    ]
                ]
            ]];
            // $_SESSION['output'] = $output;
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => $output
            ];
            //break;
        }else if($message == "FIND_TRIPS"){
            $trips = DB::select("select * from trips");
            foreach($trips as $trip) { //Loop through all trips
                $res[] = array(
                    //[[
                        "title" => $trip->title,
                        "image_url" => "http://www.renewventurestravel.com.ng/img/Tours/Abuja/1.jpg",
                        "buttons" => [
                            [
                                "type" => "postback",
                                "title" => "Details",
                                "payload" => $trip->id
                            ]
                        ]);
                    //]];

            }
                $output = ["attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "list",
                        "elements" => $res
                    ]]];
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
        }
        $tripDetails = DB::select("select * from trips");
        foreach($tripDetails as $tripDetail) {
            $tripId = $tripDetail->id;
            $booking_ends = date('F jS Y', strtotime($tripDetail->booking_ends));
            $tour_date = date('F jS Y', strtotime($tripDetail->trip_date));
            if($message == $tripId){
                $output = ["attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "button",
                        "text" => $tripDetail->title.' <3 B-)'.PHP_EOL.PHP_EOL.'Single Price:'.number_format($tripDetail->single_price).PHP_EOL.
                            'Couple Price:'.number_format($tripDetail->couple_price).PHP_EOL.PHP_EOL.$tripDetail->description.PHP_EOL.PHP_EOL.
                            'Booking ends:'.$booking_ends.' :('.PHP_EOL.'Trip Date:'.$tour_date.' :D',
                        //urlencode("here is my text.\n and this is a new line \n another new line");
                        //"text" => urlencode("here is my text.\n and this is a new line \n another new line");
                        "buttons" => [
                            [
                                "type" => "postback",
                                "title" => "Book Now",
                                "payload" => $tripDetail->book_id
                            ],
                            [
                                "type" => "postback",
                                "title" => "Add to Wishlist",
                                "payload" => "FIND_TRIPS"
                            ]
                        ]
                    ]
                ]];
                // $_SESSION['output'] = $output;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
            }
        }
        /**
         * Book trips and send emails
         */
        $bookTrips = DB::select("select * from trips");
        foreach ($bookTrips as $bookTrip){
            $email_request = "Please provide me with your email ;)";
            $bookid = $bookTrip->book_id;
            if($message == $bookid) {
                $session = session(['output' => $email_request]);
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $email_request]
                ];
                $create = ManageSession::create([
                    'output' => 'enter email',
                    'sender_id' => $senderId
                ]);
            }else {
                $count = DB::select("select * from sessions where output = 'enter email' and sender_id = '$senderId'");
                //$get = session()->pull('output', 'default');
                if (isset($message) && count($count) > 0) {
                    if (filter_var($message, FILTER_VALIDATE_EMAIL)){
                        //Send mail and update output column === null and insert into bookings table

                        /**
                         * Send mail to the use to confirm booking
                         */
                        $data = array(
                            'name' => $name,
                            'trip' => $bookTrip->title
                        );

                        Mail::send('emails.welcome', $data, function ($send) use ($message) {
                            $send->from('ajalabot.ng@gmail.com', 'AjalaBot Support');
                            $send->cc('ajalabot.ng@gmail.com')->subject('Booking Confirmation');
                            $send->to($message)->subject('Booking Confirmation');

                        });
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => 'Booking confirmed! An email has been sent to you.']
                        ];

                    }else{
                        //Update output column, request email and re-validate
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => "Email not valid, Try again"]
                        ];
                        $update = DB::table('sessions')
                                    ->where('sender_id', $senderId)
                                    ->update(['output' => 'Email not valid, Try again']);
                    }

                }
                $up = DB::select("select * from sessions where output = 'Email not valid, Try again' and sender_id = '$senderId'");
                if (isset($message) && count($up) > 0) {
                    if (filter_var($message, FILTER_VALIDATE_EMAIL)){

                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => "Email is valid"]
                        ];
                        //Send mail and update output column === null
                    }else{
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => "Email not valid, Try again"]
                        ];
                        //Update output column, request email and re-validate
                        $update = DB::table('sessions')
                            ->where('sender_id', $senderId)
                            ->update(['output' => 'Email not valid, Try again']);
                    }
                }
                //}
            }

        }
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.env("ACCESS_TOKEN");
        $ch = curl_init($url);
        /* curl setting to send a json post data */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //if ($message != "") {
        curl_exec($ch); // user will get the message
        //}
        curl_close($ch);

    }
}
