<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolutionCategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'sort_order',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SolutionItem::class)->orderBy('sort_order');
    }
}
