<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelCategory extends Model
{
    //
    public $table = 'travel_categories';

    protected $fillable = [
        'category'
    ];
}
