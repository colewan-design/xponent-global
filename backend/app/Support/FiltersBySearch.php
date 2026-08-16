<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersBySearch
{
    /**
     * @param  string[]  $columns
     */
    protected function applySearch(Builder $query, Request $request, array $columns): Builder
    {
        $term = $request->query('search');

        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($columns, $term) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
