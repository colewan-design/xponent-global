<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = [
        'page',
        'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];
}
