<?php


namespace App\Models;

use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Work extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'company',
        'department',
        'post',
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
