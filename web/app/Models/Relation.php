<?php

namespace App\Models;

use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relation extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'relation',
        'type',
        'contact_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
