<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'type',
        'name',
        'logo',
        'website_url',
        'description',
        'sort_order',
    ];
}
