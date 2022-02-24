<?php


namespace App\Models;

use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'country',
        'provinces',
        'city',
        'address',
        'type',
        'postcode',
        'po_box',
        'contact_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
