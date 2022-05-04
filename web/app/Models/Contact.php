<?php

namespace App\Models;

use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\Sorting;
use Sumra\SDK\Traits\UuidTrait;
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
 *         property="works",
 *         type="array",
 *         description="Contacts phones / Msisdns data in JSON",
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="company",
 *                 type="string",
 *                 description="Company",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="department",
 *                 type="string",
 *                 description="Department",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="post",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="addresses",
 *         type="array",
 *         description="Contacts phones / Msisdns data in JSON",
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="country",
 *                 type="string",
 *                 description="country",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="address_string1",
 *                 type="string",
 *                 description="Department",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="address_string2",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="city",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="provinces",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="postcode",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="po_box",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 description="Post",
 *                 example=""
 *             ),
 *             @OA\Property(
 *                 property="is_default",
 *                 type="boolean",
 *                 description="Post",
 *                 example=""
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="sites",
 *         type="array",
 *         description="Sites",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="url",
 *                 type="string",
 *                 description="Site url",
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
 *         property="chats",
 *         type="array",
 *         description="Sites",
 *
 *         @OA\Items(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="chat",
 *                 type="string",
 *                 description="Site url",
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
 *         property="relations",
 *         type="array",
 *         description="Working relations with anybody",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(
 *                 property="value",
 *                 type="string",
 *                 description="Value of relations",
 *                 example="Jhonny Michael"
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 description="Type of relations",
 *                 enum={"other", "spouse", "child", "mother", "father", "parent", "brother", "sister", "friend", "relative", "manager", "assistant", "referred_by", "partner", "domestic_partner"}
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
        return $this->hasMany(Phone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'contact_category');
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
