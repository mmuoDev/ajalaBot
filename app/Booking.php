<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    public $table = 'bookings';

    protected $fillable = [
        'user_session_id',
        'travel_id',
        'name',
        'phone',
        'status_id'
    ];
}
