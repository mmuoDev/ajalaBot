<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManageSession extends Model
{
    //
    public $table = 'sessions';

    protected $fillable = [
        'output',
        'sender_id'
    ];
}
