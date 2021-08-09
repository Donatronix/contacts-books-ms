<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use App\Traits\Sorting;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Contact Model
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Contact",
 *
 *     @OA\Property(
 *         property="prefix_name",
 *         type="string",
 *         description="Prefix of contact name",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         description="First name in string",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="middle_name",
 *         type="string",
 *         description="Display name data in string",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="Display name data in string",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="suffix_name",
 *         type="string",
 *         description="Display name data in string",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="display_name",
 *         type="string",
 *         description="Display name data in string",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="nickname",
 *         type="string",
 *         description="Nickname of contact",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="birthday",
 *         type="date",
 *         description="Birthday date of contact",
 *         example="1984-10-25"
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         type="string",
 *         description="Photo body in base64 format",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="note",
 *         type="string",
 *         description="Contact note",
 *         example=""
 *     ),
 *     @OA\Property(
 *         property="phones",
 *         type="array",
 *         description="Contacts phones / Msisdns data in JSON",
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="phone",
 *                 type="string",
 *                 description="Phone number of contact",
 *                 example="(555)-777-1234"
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 description="Phone type (home, work, cell, etc)",
 *                 enum={"home", "work", "cell", "other", "main", "homefax", "workfax", "googlevoice", "pager"}
 *             ),
 *             @OA\Property(
 *                 property="is_default",
 *                 type="boolean",
 *                 description="Phone by default. Accept 1, 0, true, false",
 *                 example="false"
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="emails",
 *         type="array",
 *         description="Contacts emails",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="email",
 *                 type="string",
 *                 description="Email of contact",
 *                 example="test@tes.com"
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 description="Email type (home, work, etc)",
 *                 enum={"home", "work", "other", "main"}
 *             ),
 *             @OA\Property(
 *                 property="is_default",
 *                 type="boolean",
 *                 description="Email by default. Accept 1, 0, true, false",
 *                 example="true"
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="is_favorite",
 *         type="boolean",
 *         description="Need shared contacts data (1, 0, true, false)",
 *         example="false"
 *     )
 * )
 */
class Contact extends Model
{
    use HasFactory;
    use OwnerTrait;
    use SoftDeletes;
    use Sorting;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $appends = [
        'display_name'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_favorite' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'prefix_name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix_name',
        'write_as_name',
        'nickname',
        'birthday',
        'note',
        'is_favorite',
        'user_id',
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'write_as_name',
        'created_at',
        'updated_at',
        'deleted_at',
        'pivot'
    ];

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'prefix_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'suffix_name' => 'nullable|string',
            'display_name' => 'nullable|string',
            'avatar' => 'nullable|string',
            'nickname' => 'nullable|string',
            'birthday' => 'nullable|string',
            'note' => 'nullable|string',
            'phones' => 'required|array',
            'emails' => 'nullable|array'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones(): HasMany
    {
        return $this->hasMany(ContactPhone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails(): HasMany
    {
        return $this->hasMany(ContactEmail::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'contact_category');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function relations(): BelongsToMany
    {
        return $this->belongsToMany(Relation::class);
    }

    /**
     * Make display_name attribute
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $displayName = $this->write_as_name;
        if (empty($displayName)) {
            $displayName = sprintf(
                "%s %s %s %s %s",
                $this->prefix_name,
                $this->first_name,
                $this->middle_name,
                $this->last_name,
                $this->suffix_name
            );

            $displayName = trim(Str::replace('  ', ' ', $displayName));
        }

        return $this->attributes['display_name'] = $displayName;
    }
}
