<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurants extends Model
{
    protected $table = "restaurants";

    protected $fillable = ['id', 'name', 'logo','detail_link','postal_code','created_at', 'updated_at'];

}
