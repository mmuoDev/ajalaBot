<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Libraries\Utilities;
use App\Travel;
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
        $quickReply = isset($data['entry'][0]['messaging'][0]['message']['quick_reply']['payload'])?$data['entry'][0]['messaging'][0]['message']['quick_reply']['payload']:"";
        if (!empty($postback)) { //Return only if there are payloads
            $payloads = $data['entry'][0]['messaging'][0]['postback']['payload'];
            //$this->sendMessage($senderId, $payloads);
        }else if(!empty($quickReply)){
            //$this->sendMessage($senderId, $quickReply);
            }
        else if(!empty($messageText)) {
            //$this->sendMessage($senderId, $messageText);
        }

        //return ['senderId' => $senderId, 'message' => $messageText]; //else return text

    }
    public function sendMessage($senderId, $message){
//        $response = [
//            'recipient' => ['id' => $senderId],
//            'message' => ['text' => $message]
//        ];
//        Utilities::sendMessage($response);
        //$response = null;
        $userUrl = "https://graph.facebook.com/v2.6/$senderId?fields=first_name,last_name,profile_pic&access_token=".env('ACCESS_TOKEN');
        $file = file_get_contents($userUrl);
        $out = json_decode($file);
        $firstname = $out->first_name;
        $lastname = $out->last_name;
        $name = $lastname.' '.$firstname;
        //$trip = null;
        //Array of messages
        $getGreetings = Utilities::getGreetings();
        $checkingOnYou = Utilities::checkingOnYou();
        $getSaluatations = Utilities::getSalutations();
        //array ends....
        //array of current messages
        $requests = [
            "FIND_TOURS", "REQUEST_TOUR", "NO_WISH"
        ];
        if(in_array(strtolower($message), $getGreetings)) {
            //do a check
            $data = json_encode(['question' => $message]);
            $check = Utilities::manageSessions($senderId, $data);
            if($check == 0){
                //
                $output = "Ooops! ".$firstname." you already said that some seconds ago :D";
            }elseif ($check == 1){
                $output = "Hey ".$firstname."! thanks for checking up on me <3".PHP_EOL;
            }
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //default bot response
            Utilities::defaultResponse($senderId);
        }

        //
        if(in_array(strtolower($message), $checkingOnYou)) {
            //do a check
            $data = json_encode(['question' => $message]);
            $check = Utilities::manageSessions($senderId, $data);
            if($check == 0){
                //
                $output = "Ooops! ".$firstname." you already said that some seconds ago :D";
            }elseif ($check == 1){
                $output = "Hey ".$firstname."! I am doing very well! B-)".PHP_EOL;
            }
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //default bot response
            Utilities::defaultResponse($senderId);
        }
        //
        if(in_array(strtolower($message), $getSaluatations)) {
            //do a check
            $data = json_encode(['question' => $message]);
            $check = Utilities::manageSessions($senderId, $data);
            if($check == 0){
                //
                $output = "Ooops! ".$firstname." you already said that some seconds ago :D";
            }elseif ($check == 1){
                $output = "Greetings ".$firstname."! Good to hear from you!".PHP_EOL;
            }
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //default bot response
            Utilities::defaultResponse($senderId);
        }

        /**
         * Find tours
         */
        if($message == "FIND_TOURS"){
            //a short message first
            $output = $firstname.", please hold on while I search for tours to museums, national parks, history places, vineyards, etc.";
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //do a check to ensure there are tours
            $query = DB::select("select count(id)  as count from travels where category_id = 2 and status_id = 2 and deleted_at IS NULL");
            $count = ($query[0]->count);

            if($count == 0){
                //no tours, request a tour
                $output = "Unfortunately ".$firstname. ", there are no tours currently available. :(.".PHP_EOL;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $output]
                ];
                Utilities::sendMessage($response);
                //show request tour button
                $output = [
                    "text" => "Why not request for a tour?",
                    "quick_replies" => [
                        [
                            "content_type" => "text",
                            "title" => "Request for tour",
                            "payload" => "REQUEST_TOUR",
                            "image_url" => "https://images-na.ssl-images-amazon.com/images/I/41d-kZxsuIL._SY450_.jpg"
                        ]
                    ]
                ];
                // $_SESSION['output'] = $output;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
                Utilities::sendMessage($response);

            }elseif($count > 0){
                //okay. Thes are the tours I found
                $output = "Okay ".$firstname. ", these are what I found for you. 8-)".PHP_EOL. "Click on the 'Details' button of any of the tours".PHP_EOL.
                    "to see more details";
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $output]
                ];
                Utilities::sendMessage($response);
                //show all tours
                $tours = Utilities::getTours();
                //http://www.renewventurestravel.com.ng/img/Tours/Abuja/1.jpg
                //localhost:9090/uploads/files/MjAxOC0wNC0wNyAwODowMDoxMERVZ0VYR2RXc0FFdmZ2ay5qcGc=.jpg
                foreach($tours as $tour) { //Loop through all trips
                    $res[] = array(
                        //[[
                        "title" => $tour->header,
                        "image_url" => $tour->file_name, //Default tour image
                        "buttons" => [
                            [
                                "type" => "postback",
                                "title" => "Details",
//                                "payload" => "ajala"
                                "payload" => "ajala".$tour->tour_id
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

            }else{}
        }
        /**
         * Show details of a particular tour
         */
        $tourDetails = Utilities::getAllTours();
        //$book_array;
        $array = [];
        foreach($tourDetails as $tourDetail) {
            $tourId = $tourDetail->id;
            $newTripId = "ajala".$tourId; //travel details
            //get the arrays
            $bookId = "book".$tourId; //book id
            $wishId = "wish".$tourId; //wish Id
            $yes_wish_id = "yes_wish".$tourId;
            $yes_book_id = "yes_book".$tourId;

            $array[] = [$newTripId, $bookId, $wishId, $yes_wish_id, $yes_wish_id];
            //array ends
//            $travel_array[] = [$newTripId];
//            $book_array[] = [$bookId];
//            $wish_array[] = [$wishId];
//            $travel_array[] = [$newTripId];
//            $yes_wish_array[] = [$yes_wish_id];
//            $yes_book_array[] = [$yes_book_id];
            //
            $booking_ends = date('F jS Y', strtotime($tourDetail->deadline));
            $start_date = strtotime($tourDetail->start_date);
            $end_date = strtotime($tourDetail->end_date);
            if($start_date == $end_date){
                $tour_date = date('F jS Y', strtotime($tourDetail->start_date));
            }else{
                $start_date = date('F jS Y', strtotime($tourDetail->start_date));
                $end_date = date('F jS Y', strtotime($tourDetail->end_date));
                $tour_date = $start_date." to ".$end_date;
            }
            if($message == $newTripId){
                $tour_id = str_replace('ajala', '', $newTripId);
                $output = ["attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "button",
                        "text" => ucwords($tourDetail->header).' <3'.PHP_EOL.PHP_EOL.'Single Price: '.'₦'.number_format($tourDetail->single_price).PHP_EOL.
                            'Couple Price: '.'₦'.number_format($tourDetail->couple_price).PHP_EOL.PHP_EOL.$tourDetail->details.PHP_EOL.PHP_EOL.
                            'Booking ends: '.$booking_ends.' :('.PHP_EOL.'Trip Date: '.$tour_date.' :D',
                        //urlencode("here is my text.\n and this is a new line \n another new line");
                        //"text" => urlencode("here is my text.\n and this is a new line \n another new line");
                        "buttons" => [
                            [
                                "type" => "postback",
                                "title" => "Book Now",
                                "payload" => "book".$tour_id
                            ],
                            [
                                "type" => "postback",
                                "title" => "Add to Wishlist",
                                "payload" => "wish".$tour_id
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

            //Add to wishlist
            if($message == $wishId){
                $wish_id = str_replace('wish', '', $wishId);
                //log this request
                //$header = Travel::where('id', $wish_id)->first()->header;
                $output = [
                    "text" => "Are you sure to add this tour to your wishlist?",
                    "quick_replies" => [
                        [
                            "content_type" => "text",
                            "title" => "Yes",
                            "payload" => "yes_wish".$wish_id,
                            "image_url" => "https://images-na.ssl-images-amazon.com/images/I/41d-kZxsuIL._SY450_.jpg"
                        ],
                        [
                            "content_type" => "text",
                            "title" => "No",
                            "payload" => "NO_WISH",
                            "image_url" => "http://www.solidbackgrounds.com/images/2560x1440/2560x1440-blue-solid-color-background.jpg"
                        ]
                    ]
                ];
                // $_SESSION['output'] = $output;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
                Utilities::sendMessage($response);
            }
            //Add to bookings
            if($message == $bookId){
                $book_id = str_replace('book', '', $bookId);
                //log this request
                //$header = Travel::where('id', $wish_id)->first()->header;
                $output = [
                    "text" => "Are you sure you want to book this tour?",
                    "quick_replies" => [
                        [
                            "content_type" => "text",
                            "title" => "Yes",
                            "payload" => "yes_book".$book_id,
                            "image_url" => "https://images-na.ssl-images-amazon.com/images/I/41d-kZxsuIL._SY450_.jpg"
                        ],
                        [
                            "content_type" => "text",
                            "title" => "No",
                            "payload" => "NO_WISH",
                            "image_url" => "http://www.solidbackgrounds.com/images/2560x1440/2560x1440-blue-solid-color-background.jpg"
                        ]
                    ]
                ];
                // $_SESSION['output'] = $output;
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => $output
                ];
                Utilities::sendMessage($response);
            }
            if($message == $yes_wish_id){
                $tour_id = str_replace('yes_wish', '', $yes_wish_id);
                //add to wishlist
                $output = "Gimme some seconds as I add this tour to your wishlist";
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $output]
                ];
                Utilities::sendMessage($response);
                //get
                //Make a call to wishlist table
               $wishlist = Utilities::addToWishlist($senderId, $tour_id);
               if($wishlist == 1){
                    $output = "This tour has already been added to your wishlist :/";
               }elseif ($wishlist == 2){
                    $output = "Okay ". $firstname.", I have added this tour to your wishlist (y)";
               }elseif ($wishlist == 3){
                   $output = "Unfortunately, I couldn't add this tour to your wishlist. Please try again";
               }else{}
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $output]
                ];
                Utilities::sendMessage($response);
        }
            //yes to bookings
            if($message == $yes_book_id){
                $tour_id = str_replace('yes_book', '', $yes_book_id);
                //log request
                $data = json_encode(['request_type' => 'book_tour', 'tour_id' => $tour_id]);
                //
                Utilities::logRequest($senderId, $data);
                //add to wishlist
                $output = "Gimme some seconds while I book this tour";
                $response = [
                    'recipient' => ['id' => $senderId],
                    'message' => ['text' => $output]
                ];
                Utilities::sendMessage($response);
                //get
                //Add to bookings table
                $booking = Utilities::addToBookings($senderId, $tour_id, $firstname);
                if($booking == 1){
                    //already exists
                    $output = $firstname.", I have already booked this tour for you. 8|";
                    Utilities::sendMsg($output, $senderId);

                }elseif ($booking == 2){
                    //booking success
                    $output = "Okay ".$firstname.", one last thing.".PHP_EOL."I will be needing your phone number so a tour guide can call you";
                    Utilities::sendMsg($output, $senderId);

                    $output = "For example, 08188888888. It must be 11 digits. ;)";
                    Utilities::sendMsg($output, $senderId);
                }else{
                    $output = "Unfortunately, I can't book this tour now. Please try again";
                    Utilities::sendMsg($output, $senderId);
                }

            }

        }
//        $book_array[] = [$bookId];
//        $wish_array[] = [$wishId];
//        $travel_array[] = [$newTripId];
//        $yes_wish_array[] = [$yes_wish_id];
//        $yes_book_array[] = [$yes_book_id];
//        confirmBookingRequest
        $confirmBookingRequest = Utilities::confirmBookingRequest($senderId);
        if((!empty($message)) && $message == 2 && !in_array($message, $requests) &&
            !in_array($message, $array)
            && ($confirmBookingRequest == true)){
            //validate phone number
            $validate_number = Utilities::check_number($message);
            if($validate_number == 200){
                $output = "That looks correct! ;)";
                Utilities::sendMsg($output, $senderId);
                //update table with phone number
                Utilities::updatePhoneNumber($senderId, $message);
                //log request
                $data = json_encode(['request_type' => 'book_success', 'tour_id' => $tour_id]);
                //
                Utilities::logRequest($senderId, $data);
                //
                $output = "Booking was successful O:)!".PHP_EOL."
                A tour guide will call you soon ".$firstname;
                Utilities::sendMsg($output, $senderId);

                Utilities::defaultResponse($senderId);

            }else{
                $output = "This phone number is incorrect. :/".PHP_EOL."For example, 08188888888. It must be 11 digits. ;)";
                Utilities::sendMsg($output, $senderId);
            }
        }
        //no to adding to wishlist
        if($message == "NO_WISH"){
            Utilities::defaultResponse($senderId);
        }

        /**
         * Request for tours
         */
        if($message == "REQUEST_TOUR"){
            //tell me where, how many of you and date of tour
            $output = "Please tell me where you want to go, how many people are in for this tour".PHP_EOL.
            "and your desired date for this tour. E.g. Olumo Rock, 3 person and August 30, 2017 ;)";
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //log request
            $data = json_encode(['request_type' => 'request_tour']);
            Utilities::logRequest($senderId, $data);
        }
        /**
         * Confirm request
         */
        //get last request
        $confirm = Utilities::confirmRequestTour($senderId);
        if((!empty($message)) && !in_array($message, $requests) && ($confirm == true)){
            //please confirm entry
            $output = "You have entered the following...";
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            //message
            //log message
            $data = json_encode(['request_message' => $message]);
            Utilities::logRequest($senderId, $data);
            $output = $message;
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
            ];
            Utilities::sendMessage($response);
            $output = [
                "text" => "Are you okay with this?",
                "quick_replies" => [
                    [
                        "content_type" => "text",
                        "title" => "YES",
                        "payload" => "YES_TOURS",
                        "image_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Flag_of_Libya_%281977-2011%29.svg/2000px-Flag_of_Libya_%281977-2011%29.svg.png"
                    ],
                    [
                        "content_type" => "text",
                        "title" => "NO",
                        "payload" => "REQUEST_TOUR",
                        "image_url" => "https://images-na.ssl-images-amazon.com/images/I/41d-kZxsuIL._SY450_.jpg"
                    ]
                ]
            ];
            // $_SESSION['output'] = $output;
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => $output
            ];
            Utilities::sendMessage($response);
        }
        /**
         * Confirm tour request
         */
        if($message == "YES_TOURS"){
            //get message and save in tour requests
            $request = Utilities::getTourRequest($senderId);
            if($request == true){
                $output = "I have successfully submitted your request! O:)".PHP_EOL."You should get a response from me within the next 48 hours ;)";
            }else{
                $output = "Unfortunately, I could not submit your request.:'( Please try again.";
            }
            $response = [
                'recipient' => ['id' => $senderId],
                'message' => ['text' => $output]
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
