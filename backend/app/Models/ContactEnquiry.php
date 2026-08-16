<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    protected $fillable = [
        'enquiry_type',
        'region',
        'country',
        'name',
        'email',
        'company',
        'phone',
        'message',
        'status',
    ];
}
