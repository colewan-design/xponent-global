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
        'latitude',
        'longitude',
        'sort_order',
    ];

    /** Cast off the decimal columns' string representation so the API emits numbers. */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
