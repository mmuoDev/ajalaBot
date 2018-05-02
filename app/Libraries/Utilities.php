<?php
namespace App\Libraries;

use App\Booking;
use App\Conversation;
use App\Mail\notifyNewUsers;
use App\RequestTour;
use App\Wishlist;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class Utilities{
    public static function updatePhoneNumber($senderId, $phone){
        //
            $query = DB::select("select * from conversations where user_session_id = '$senderId' order by id DESC LIMIT 1");
            $conversation = $query[0]->sessions;
            //dd($conversation);

            if(isset($conversation)){
                //get request_type
                $sessions = json_decode($conversation, true);
                //$data = isset($sessions['request_type'])?$sessions['request_type']:"";
                if(array_key_exists("request_type",  $sessions)){
                    $data = $sessions['request_type'];
                    if(isset($data)){
                        if($data == "book_tour"){
                            //get tour_id
                            $tour_id = $sessions['tour_id'];
                            DB::table('bookings')
                                ->where([
                                    'user_session_id' => $senderId,
                                    'travel_id' => $tour_id
                                ])->update([
                                    'phone' => $phone
                                ]);
                            return true;
                        }else{
                            return false;
                        }
                    }
                }

            }
    }
    public static function check_number($number) {
        /*
        111 = input is not a digit
        110 = not starting from 080, 070, 081, 090
        120 = not 11 characters
        200 = its okay
        0 = empty input
        */
        //Lets really know if the input is not empty, which if it is, return false
        if(!$number) {
            return false;
        }
        //Checking if its really numerics
        elseif(!is_numeric($number)) {
            return 111;
        }
        //Checking if number starts with 080, 090, 070 and 081
        elseif(!preg_match('/^080/', $number) and !preg_match('/^070/', $number) and !preg_match('/^090/', $number) and !preg_match('/^081/', $number)) {
            return 110;
        }
        //Check if the length is 11 digits
        elseif(strlen($number)!==11) {
            return 120;
        }
        //Every requirements are made
        else {
            return 200;
        }
    }
    public static function sendMsg($output, $sender_id){
        $response = [
            'recipient' => ['id' => $sender_id],
            'message' => ['text' => $output]
        ];
        Self::sendMessage($response);
    }
    public static function addToWishlist($sender_id, $travel_id){
        $count = Wishlist::where(['user_session_id' => $sender_id, 'travel_id' => $travel_id])->count();
        if($count > 0){
            //already added
            return 1;
        }elseif ($count == 0){
                $create = Wishlist::create([
                    'user_session_id' => $sender_id,
                        'travel_id' => $travel_id,
                        'status_id' => 1 //pending
                    ]);
                if($create){
                    return 2; //added
                }else{
                    return 3; //error
                }
        }

    }
    public static function addToBookings($sender_id, $travel_id, $name){
        $count = Booking::where(['user_session_id' => $sender_id, 'travel_id' => $travel_id])->count();
        if($count > 0){
            return 1; //already present
        }elseif ($count == 0){
            $booking = Booking::create([
                'user_session_id' => $sender_id,
                'travel_id' => $travel_id,
                'status_id' => 1, //pending phone number entry
                'name' => $name
            ]);
            if($booking){
                return 2;
            }else{
                return 3;
            }
        }
    }
    public static function defaultResponse($senderId){
        $output = [
            "text" => "What do you want to do? ;)",
            "quick_replies" => [
                [
                    "content_type" => "text",
                    "title" => "Find Trips",
                    "payload" => "FIND_TRIPS",
                    "image_url" => "https://images-na.ssl-images-amazon.com/images/I/41d-kZxsuIL._SY450_.jpg"
                ],
                [
                    "content_type" => "text",
                    "title" => "Find Tours",
                    "payload" => "FIND_TOURS",
                    "image_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Flag_of_Libya_%281977-2011%29.svg/2000px-Flag_of_Libya_%281977-2011%29.svg.png"
                ],
                [
                    "content_type" => "text",
                    "title" => "Hang Out",
                    "payload" => "HANG_OUT",
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
    public static function manageSessions($sender_id, $data){
        date_default_timezone_set("Africa/Lagos"); //set time zone
        $jsonData = $data;
        //
        $new_data = json_decode($data, true);
        $new_question = $new_data['question'];
        //check if record already exist for this sender
        $data = Conversation::where('user_session_id', $sender_id)->first();
        if(isset($data)){
            //dd('true');
            //do a count and check
            $count = $data->count();
            if($count > 0){
                //get last message
                $data = DB::select("select * from conversations where user_session_id = '$sender_id' order by created_at DESC LIMIT 1");
                $created_at = $data[0]->created_at;
                $sessions = $data[0]->sessions;
                $array = json_decode($sessions, true);
                //$last_question = isset($array['question'])?$array['question']:"";
                if(array_key_exists('question', $array)){
                    $last_question = $array['question'];
                    $last_time = strtotime($created_at);
                    $current_time = strtotime(date('Y-m-d H:i:s'));
                    $diff_time = $current_time - $last_time;
                    if(($last_question == $new_question) && ($diff_time < 60)){
                        //repeating same question in less than a minute.
                        return 0;
                    }else{
                        //insert new record
                        Conversation::create([
                            'user_session_id' => $sender_id,
                            'sessions' => $jsonData
                        ]);
                        return 1;
                    }
                }else{
                    Conversation::create([
                        'user_session_id' => $sender_id,
                        'sessions' => $jsonData
                    ]);
                    return 1;
                }
            }
        }else{
            //dd('false');
            //no records, insert record
            Conversation::create([
                'user_session_id' => $sender_id,
                'sessions' => $jsonData
            ]);
            return 1;
        }
    }
    public static function confirmRequestTour($senderId){
        $query = DB::select("select * from conversations where user_session_id = '$senderId' order by id DESC LIMIT 1");
        $conversation = $query[0]->sessions;
        //dd($conversation);

        if(isset($conversation)){
            //get request_type
            $sessions = json_decode($conversation, true);
            //$data = isset($sessions['request_type'])?$sessions['request_type']:"";
            if(array_key_exists("request_type",  $sessions)){
                $data = $sessions['request_type'];
                if(isset($data)){
                    if($data == "request_tour"){
                        return true;
                    }else{
                        return false;
                    }
                }
            }

        }
    }
    public static function confirmBookingRequest($senderId){
        $query = DB::select("select * from conversations where user_session_id = '$senderId' order by id DESC LIMIT 1");
        $conversation = $query[0]->sessions;
        if(isset($conversation)){
            //get request_type
            $sessions = json_decode($conversation, true);
            //$data = isset($sessions['request_type'])?$sessions['request_type']:"";
            if(array_key_exists("request_type",  $sessions)){
                $data = $sessions['request_type'];
                if(isset($data)){
                    if($data == "book_tour"){
                        return true;
                    }else{
                        return false;
                    }
                }
            }

        }
    }
    public static function logRequest($sender_id, $session){
        Conversation::create([
            'user_session_id' => $sender_id,
            'sessions' => $session
        ]);
    }
    public static function getTourRequest($senderId){
        $query = DB::select("select * from conversations where user_session_id = '$senderId' order by id DESC LIMIT 1");
        $conversation = $query[0]->sessions;
        $sessions = json_decode($conversation, true);
        if(array_key_exists('request_message', $sessions)){
            $message = $sessions['request_message'];
            //insert into request_tours
            $create = RequestTour::create([
                'user_session_id' => $senderId,
                'request' => $message,
                'status_id' => 1
            ]);
            if($create){
                return true;
            }else{
                return false;
            }
        }
    }
    public static function getTours(){
        $tours = DB::select("select t.header as header, t.id as tour_id, t.img_url as file_name from travels as t
        where t.category_id = 2 and t.status_id = 2 and t.deleted_at IS NULL");
        return $tours;
    }

    public static function getAllTours(){
        $tours = DB::select("select t.* from travels as t
        where t.category_id = 2 and t.status_id = 2 and t.deleted_at IS NULL");
        return $tours;
    }
    public static function getGreetings(){
        $array = ['hey', 'hey man', 'hi', 'hello', 'how far', 'how far?'];
        return $array;
    }
    public static function checkingOnYou(){
        $array = ['how’s it going?', 'How are you doing?', 'What’s up?', 'What’s new?', 'What’s going on?',
            'How’s everything?', 'how are things?', 'how’s life?', 'how’s your day going?', 'how’s your day?',
            'it’s been a while', 'how do you do?', 'whazzup?', 'sup?', 'sup', 'how are you'];
        return $array;
    }
    public static function getSalutations(){
        $array = ['good morning', 'good afternoon', 'good evening'];
        return $array;

    }
    public static function notifyNewUsers($email, $content){
        Mail::to($email)->send(new notifyNewUsers($content));
    }

    public static function generateFolderRef()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        } else {
            return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        }
    }

    public static function getTravelFiles($id){
        $files = DB::select("select t.*, tf.uri as file_uri, tf.original_file_name from travels as t, travel_files as tf where 
        t.id = tf.travel_id and t.id = '$id'");
        return $files;
    }

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