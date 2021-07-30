<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;
    use OwnerTrait;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'user_id'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
        'user_id'
    ];

    /**
     * @return string[]
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'string|required|min:2|max:50'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }
}
