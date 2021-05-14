<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait UuidTrait
 *
 * @package App\Traits\Models
 */
trait UuidTrait
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootUuidTrait(): void
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string)Str::orderedUuid();
        });
    }

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @return false
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
