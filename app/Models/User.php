<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    protected $fillable = [
        'id',
        'code',
        'name',
        'email',
        'email_verified_at',
        'phone',
        'password',
        'picture',
        'status',
        'blocked',
        'blocked_reason',
        'password_otp',
        'password_otp_expires',
        'timezone',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'password_otp',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_otp_expires' => 'datetime',
            'blocked' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid()->toString();
        });
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'users_roles', 'user_id', 'role_id');
    }

    public function hasRole(string $name): bool
    {
        return $this->roles()->where('name', $name)->exists();
    }

    public function hasPermission(string $name, string $method = 'GET'): bool
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('name', $name)->where('method', $method)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override can() to check hasPermission for sidebar gating.
     *
     * @param  string  $ability
     * @param  mixed  $arguments
     */
    public function can($ability, $arguments = []): bool
    {
        return $this->hasPermission($ability, 'GET');
    }
}
