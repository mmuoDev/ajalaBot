<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Libraries\Utilities;
use App\Wishlist;
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
        $out = json_decode($file);
        $firstname = $out->first_name;
        $lastname = $out->last_name;
        $name = $lastname.' '.$firstname;

        //$trip = null;
        if($message == 'hi') {
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $message]
            ];
            Utilities::sendMessage($response);
        }
        if($message == "REQUEST_A_TRIP"){ //Request a trip
            $output = ["attachment" => [
                "type" => "template",
                "payload" => [
                    "template_type" => "button",
                    "text" => "Ajala Bot also help you plan a trip.".PHP_EOL."Just tell us where you would love to go and we will".PHP_EOL.
                    "help plan the perfect getaway ;)",
                    "buttons" => [
                        [
                            "type" => "postback",
                            "title" => "Get Started",
                            "payload" => "PAYLOAD_CONTACT"
                        ]
                    ]
                ]
            ]];
            // $_SESSION['output'] = $output;
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => $output
            ];
            Utilities::sendMessage($response);
        }
        //Cancel request i.e. truncate the table based on sender Id
        if($message == 'Cancel') {

            DB::table('sessions')->where('sender_id', $senderId)->delete();
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => 'Request Cancelled. Use Menu Tab']
            ];
            Utilities::sendMessage($response);
        }
        if($message  == "PAYLOAD_CONTACT"){
            $email_request = "Please provide me with your email ;)";
            $create = ManageSession::create([
                'output' => 'contact email',
                'sender_id' => $senderId
            ]);
            if($create) {
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $email_request]
                ];
                Utilities::sendMessage($response);
                exit;
            }else{
                //echo 'error';
            }
        }
        //Foreach loop ends
        $up = DB::select("select * from sessions where output = 'contact email not valid' and sender_id = '$senderId'");
        $count = DB::select("select * from sessions where output = 'contact email' and sender_id = '$senderId'");
        $request = DB::select("select * from sessions where output = 'email provided' and sender_id = '$senderId'");
        //$get = session()->pull('output', 'default');

        //Check if the user has already booked this trip
        if ($message != '' && count($count) == 1) {
            if (filter_var($message, FILTER_VALIDATE_EMAIL)){
                //Send mail and update output column === null and insert into bookings table

                /**
                 * Send mail to the use to confirm booking
                 */
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['email' => $message,
                        'output' => 'email provided']);
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => 'Enter your request and send']
                ];
                Utilities::sendMessage($response);

            }else{
                //Update output column, request email and re-validate
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => 'contact email not valid']);

                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Email not valid, Try again. Enter 'Cancel' to cancel request"]
                ];
                Utilities::sendMessage($response);
            }

        }
        else if ($message != '' && count($up) == 1) {

            if (filter_var($message, FILTER_VALIDATE_EMAIL)){
                //Send mail and update output column === null and insert into bookings table

                /**
                 * Send mail to the use to confirm booking
                 */
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['email' => $message,
                        'output' => 'email provided']);
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => 'Enter your request and send']
                ];
                Utilities::sendMessage($response);

            }else{
                //Update output column, request email and re-validate
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => 'contact email not valid']);

                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Email not valid, Try again. Enter 'Cancel' to cancel request"]
                ];
                Utilities::sendMessage($response);
            }

        }
        else if ($message != '' && count($request) == 1) {

            $select = DB::select("select * from sessions where sender_id = '$senderId'");
            foreach ($select as $select) {
                $email = $select->email;
            }
            $details = array(
                'text' => $message,
                'email' => $email
            );
            $name = Utilities::getName($senderId);
            Mail::send('emails.contact', $details, function ($send) use ($email, $name) {
                $send->from('ajalabot.ng@gmail.com', $name);
                //$send->cc($email)->subject('Queries');
                $send->to('ajalabot.ng@gmail.com')->subject('Queries');

            });
            DB::table('sessions')->where('sender_id', $senderId)->delete();
            /**
            DB::table('sessions')
                ->where('sender_id', $senderId)
                ->update([
                    'output' => '']);
             * */
            $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Request sent, You will receive a reply soon  B-)"]
            ];
            Utilities::sendMessage($response);
                //Send Mail and update output to null




        }
        if($message == "PAYLOAD_WISHLIST"){
            //Fetch all items in wishlist for this user
            $wishlist = DB::select("select a.*,  b.* from wishlists as a, trips as b where 
                        b.wish_id = a.wish_id and sender_id = '$senderId'");
            if(count($wishlist) > 0){
                foreach($wishlist as $trip) { //Loop through all trips
                    $res[] = array(
                        //[[
                        "title" => $trip->title,
                        "image_url" => "http://www.renewventurestravel.com.ng/img/Tours/Abuja/1.jpg", //Default tour image
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
                        "template_type" => "generic",
                        "elements" => $res
                    ]]];
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
                Utilities::sendMessage($response);
            }else{
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => 'No items in your wishlist']
                ];
                Utilities::sendMessage($response);
            }
        }
        //Wishlist ends
        if ($message == "GET_STARTED_PAYLOAD"){
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
            Utilities::sendMessage($response);
            //break;
        }if($message == "FIND_TRIPS"){
            $trips = DB::select("select * from trips");
            foreach($trips as $trip) { //Loop through all trips
                $res[] = array(
                    //[[
                        "title" => $trip->title,
                        "image_url" => "http://www.renewventurestravel.com.ng/img/Tours/Abuja/1.jpg", //Default tour image
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
            Utilities::sendMessage($response);
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
                                "payload" => "$tripDetail->wish_id"
                            ]
                        ]
                    ]
                ]];
                // $_SESSION['output'] = $output;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
                Utilities::sendMessage($response);
            }

        }
        $bookTrips = DB::select("select * from trips");
        foreach ($bookTrips as $bookTrip) {
            $bookid = $bookTrip->book_id;
            $wishid = $bookTrip->wish_id;
        //$count = DB::select("select * from sessions where output = 'enter email' and sender_id = '$senderId'");
            if ($message == $bookid) {
                //Check if trip is already booked
                $check = DB::select("select * from bookings where book_id = '$bookid'");
                if(count($check) > 0){
                    //Trip already booked
                    $response = [
                        'recipient' => ['id' => $senderId],
                        'message' => ['text' => "You have already booked this trip :P"]
                    ];
                    Utilities::sendMessage($response);
                }else{ //book the trip
                    $email_request = "Please provide me with your email ;)";
                    $create = ManageSession::create([
                        'output' => 'enter email',
                        'sender_id' => $senderId
                    ]);
                    if($create) {
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => $email_request]
                        ];
                        Utilities::sendMessage($response);
                        exit;
                    }else{
                        //echo 'error';
                    }
                }

            } //
            //Do for wishlist. Add to wishlist
            else if ($message == $wishid) {
                $count = DB::select("select * from wishlists where wish_id = '$message' and sender_id = '$senderId'");
                if(count($count) > 0){
                    $response = [
                        'recipient' => ['id' => $senderId],
                        'message' => ['text' => "Trip already added to your wishlist ;)"]
                    ];
                    Utilities::sendMessage($response);
                }
                else{
                    $wish = Wishlist::create([
                        'wish_id' => $message,
                        'sender_id' => $senderId
                    ]);
                    if($wish) {
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => ['text' => "Trip added to wishlist (y) B-)"]
                        ];
                        Utilities::sendMessage($response);
                        //exit;
                    }
                }
            } //
        }
        /**
         * Book trips and send emails
         */




        //Foreach loop ends
        $up = DB::select("select * from sessions where output = 'Email not valid, Try again' and sender_id = '$senderId'");
        $count = DB::select("select * from sessions where output = 'enter email' and sender_id = '$senderId'");
        //$get = session()->pull('output', 'default');

        //Check if the user has already booked this trip
        if ($message != '' && count($count) == 1) {
            if (filter_var($message, FILTER_VALIDATE_EMAIL)){
                //Send mail and update output column === null and insert into bookings table

                /**
                 * Send mail to the use to confirm booking
                 */
                $details = array(
                    'name' => $name,
                    'trip' => $bookTrip->title
                );

                Mail::send('emails.welcome', $details, function ($send) use ($message) {
                    $send->from('ajalabot.ng@gmail.com', 'AjalaBot Support');
                    $send->cc('ajalabot.ng@gmail.com')->subject('Booking Confirmation');
                    $send->to($message)->subject('Booking Confirmation');

                });
                //Email sent
                //Add to bookings table
                Booking::create([
                    'sender_id' => $senderId,
                    'book_id' => $bookid,
                    'email' => $message,
                    'name' => $name
                ]);
                //added to bookings table
                //Update session column 'output'  ==  null
                /**
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => '']); **/
                DB::table('sessions')->where('sender_id', $senderId)->delete();
                //Column updated
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => 'Booking confirmed! An email has been sent to you. :D']
                ];
                Utilities::sendMessage($response);

            }else{
                //Update output column, request email and re-validate
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => 'Email not valid, Try again']);

                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Email not valid, Try again. Enter 'Cancel' to cancel support"]
                ];
                Utilities::sendMessage($response);
            }

        }
        else if ($message != '' && count($up) == 1) {
            if (filter_var($message, FILTER_VALIDATE_EMAIL)){
                //Send mail and update output column === null and insert into bookings table

                /**
                 * Send mail to the use to confirm booking
                 */
                $details = array(
                    'name' => $name,
                    'trip' => $bookTrip->title
                );

                Mail::send('emails.welcome', $details, function ($send) use ($message) {
                    $send->from('ajalabot.ng@gmail.com', 'AjalaBot Support');
                    $send->cc('ajalabot.ng@gmail.com')->subject('Booking Confirmation');
                    $send->to($message)->subject('Booking Confirmation');

                });
                //Email sent
                //Add to bookings table
                Booking::create([
                    'sender_id' => $senderId,
                    'book_id' => $bookid,
                    'email' => $message,
                    'name' => $name
                ]);
                //added to bookings table
                //Update session column 'output'  ==  null
                /**
                DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => '']);
                 * **/
                DB::table('sessions')->where('sender_id', $senderId)->delete();
                //Column updated
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Booking confirmed! An email has been sent to you. :D"]
                ];
                Utilities::sendMessage($response);
                //Send mail and update output column === null
            }else{
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => "Email not valid, Try again"]
                ];
                Utilities::sendMessage($response);
                //Update output column, request email and re-validate
                $update = DB::table('sessions')
                    ->where('sender_id', $senderId)
                    ->update(['output' => "Email not valid, Try again. Enter 'Cancel' to cancel request"]);
            }
        }
        /**
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.env("ACCESS_TOKEN");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch); // user will get the message
        curl_close($ch);
        **/

    }
}
