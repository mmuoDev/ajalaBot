<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    //
    public $table = 'conversations';

    protected $fillable = [
        'user_session_id',
        'sessions'
    ];
}
