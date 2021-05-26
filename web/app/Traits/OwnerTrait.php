<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait OwnerTrait
 *
 * @package App\Http\Traits
 */
trait OwnerTrait{
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeByOwner($query)
    {
        return $query->where('user_id', (int)Auth::user()->getAuthIdentifier());
    }
}
