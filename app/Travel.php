<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Travel extends Model
{
    //
    use SoftDeletes;

    public $table = 'travels';

    protected $fillable = [
      'header',
      'user_id',
      'category_id',
      'start_date',
      'end_date',
      'details',
      'deadline',
      'single_price',
      'couple_price',
      'status_id',
      'uri',
        'img_url'
    ];
}
