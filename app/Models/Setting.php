<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid()->toString();
        });

        // Invalidate cache on save/update/delete
        static::saved(function () {
            Cache::forget('settings_current');
        });

        static::deleted(function () {
            Cache::forget('settings_current');
        });
    }

    /**
     * Singleton-style: get (or cache) the first settings row.
     * Cache stores raw attribute array (not object) to avoid __PHP_Incomplete_Class on unserialize.
     */
    public static function getCurrent(): ?self
    {
        $attrs = Cache::remember('settings_current', 60, function () {
            return static::first()?->attributesToArray();
        });

        if ($attrs === null) {
            return null;
        }

        /** @var self $instance */
        $instance = (new self)->newFromBuilder($attrs);

        return $instance;
    }
}
