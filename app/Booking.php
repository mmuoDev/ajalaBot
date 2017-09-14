<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    public $table = 'bookings';

    protected $fillable = [
        'sender_id',
        'book_id',
        'email',
        'name'
    ];
}
