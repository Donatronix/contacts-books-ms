<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPhone extends Model
{
    use HasFactory;

    /**
     *
     */
    const TYPE_CELL = 'cell';
    const TYPE_WORK = 'work';
    const TYPE_HOME = 'home';
    const TYPE_OTHER = 'other';

    /**
     * @var string
     */
    protected $table = 'contact_phones';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
