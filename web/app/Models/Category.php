<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Off created_at and updated_at column
     *
     * @var bool
     */
    public $timestamps = false;

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
//    protected $with = [
//        'children',
//    ];

    /**
     * Reload model boot
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($category) {
            $category->children->each->delete();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_category');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeStructure($query)
    {
        return $query->with([
            'children.children'
        ]);
    }
}
