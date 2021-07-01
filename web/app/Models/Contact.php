<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UuidTrait;
    use OwnerTrait;

    public $type = '';
    public $images = [];

    /**
     * @var string[]
     */
    protected $appends = [
        'display_name'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'surname',
        'prefix',
        'suffix',
        'nickname',
        'note',
        'avatar',
        'birthday',
        'is_favorite'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

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

    public function scopeFavorites($query)
    {

    }

    public function scopeRecentlyAdded($query)
    {

    }

    /**
     * Make display_name attribute
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] = $this->first_name . ' ' . $this->last_name;
    }

    public function getImagesFromRemote($query, $uuid)
    {
        $images = [];
        $query->id = '';
        $client = new Client(['base_uri' => env('FILES_MICROSERVICE_HOST')]);
        $entityWithId = '?entity=contact&contact_id=' . $query->id;
        $contactImages = $client->request('GET', env('API_FILES', '/v1') . '/files' . $entityWithId);
        $contactImages = json_decode($contactImages->getBody(), JSON_OBJECT_AS_ARRAY);
        foreach ($contactImages['data'] as $image) {
            $images[] = $image['attributes']['path'];
        }
        $this->images = $images;

        if ($includes = $query->get('include')) {
            foreach (explode(',', $includes) as $include) {
                if (method_exists($this, $include) && $this->{$include}() instanceof Relation) {
                    $this->{$include};
                }
            }
        }

        return $this->toArray();
    }
}
