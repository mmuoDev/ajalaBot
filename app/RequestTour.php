<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestTour extends Model
{
    //
    public $table = 'request_tours';

    protected $fillable = [
        'user_session_id',
        'request',
        'status_id',
        'handled_by'
    ];

}
