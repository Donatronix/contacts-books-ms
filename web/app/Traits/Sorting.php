<?php

namespace App\Traits;

trait Sorting
{
    public function scopeSort($query, $column = null, $order = null){
        $sort = request()->get('sort', null);

        return $query->when(
            !is_null($sort),

            function ($query) use ($sort) {
                return $query->orderBy($sort['by'], $sort['order'] ?? 'asc');
            },

            function ($query)  use ($column, $order) {
                return $query->orderBy($column ?? 'created_at', $order ?? 'desc');
            }
        );
    }
}
