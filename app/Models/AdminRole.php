<?php

namespace App\Models;

use Database\Factories\AdminRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    /** @use HasFactory<AdminRoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $role): void {
            if (blank($role->slug)) {
                $role->slug = slug_from($role->name, 'role');
            }
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'admin_permission_role',
            'admin_role_id',
            'admin_permission_id',
        );
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'admin_role_id');
    }
}
