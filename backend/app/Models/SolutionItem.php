<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolutionItem extends Model
{
    protected $fillable = [
        'solution_category_id',
        'title',
        'description',
        'image',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SolutionCategory::class, 'solution_category_id');
    }
}
