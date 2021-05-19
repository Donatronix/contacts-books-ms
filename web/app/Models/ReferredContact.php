<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferredContact extends Model
{
    protected $table = 'referred_contacts';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
