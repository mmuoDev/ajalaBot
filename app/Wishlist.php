<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    //
    public $table = 'wishlists';

    protected $fillable = [
        'wish_id',
        'sender_id'
    ];
}
