<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    protected $fillable = [
        'label',
        'address',
        'city',
        'country',
        'sort_order',
    ];
}
