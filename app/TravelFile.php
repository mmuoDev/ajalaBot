<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelFile extends Model
{
    //
    public $table = 'travel_files';

    protected $fillable = [
        'travel_id',
        'original_file_name',
        'uri',
        'new_name'
    ];
}
