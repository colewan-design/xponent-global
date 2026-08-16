<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'category',
        'title',
        'description',
        'file',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];
}
