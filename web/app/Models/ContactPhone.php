<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class ContactPhone extends Model
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
    protected $fillable = [
        'phone',
        'phone_type',
        'is_default',
        'contact_id'
    ];

    /**
     * @return array[]
     */
    public static function rules()
    {
        return [
            'phone' => [
                'required',
                'max:15',
                //'regex:/(0)[0-9\(\)]{15}/',
                Rule::unique('contact_phones')->where(function ($query) {
                    return $query->where('contact_id', $this->request->get('contact_id'));
                })
            ]
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
