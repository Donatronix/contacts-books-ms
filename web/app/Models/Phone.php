<?php

namespace App\Models;

use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Phone extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * Off updated_at column
     */
    const UPDATED_AT = null;

    /**
     * @var string[]
     */
    protected $casts = [
        'is_default' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'value',
        'type',
        'is_default'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'pivot'
    ];

    /**
     * @return array[]
     */
    public static function validationRules(): array
    {
        return [
            'value' => [
                'required',
                'max:18',
                'regex:/^(\+)?[0-9\(\)\.\-\+]{5,17}/',
                Rule::unique('phones')->where(function ($q) {
                    return $q->where('contact_id', request()->get('contact_id'));
                })
            ],
            'is_default' => 'boolean',
            'type' => 'string|max:15'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
