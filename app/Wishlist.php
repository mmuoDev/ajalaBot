<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    //
    public $table = 'wishlists';

    protected $fillable = [
        'user_session_id',
        'travel_id',
        'status_id'
    ];
}
