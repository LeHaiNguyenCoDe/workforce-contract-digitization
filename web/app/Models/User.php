<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'created_by',
        'updated_by',
        'deleted_by',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Quan hệ Role (nhiều-nhiều)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Quan hệ Right trực tiếp của user (nhiều-nhiều)
     */
    public function rights(): BelongsToMany
    {
        return $this->belongsToMany(Right::class, 'user_right')
            ->withPivot('suppress')
            ->withTimestamps();
    }

    /**
     * Lịch sử đăng nhập / truy cập
     */
    public function accesses(): HasMany
    {
        return $this->hasMany(UserAccess::class);
    }

    /**
     * Lấy danh sách quyền hiệu lực (rights từ role cộng với rights gán trực tiếp,
     * trừ đi các quyền bị suppress).
     */
    public function effectiveRights(): array
    {
        $roleRights = $this->roles()
            ->with('rights:id,name')
            ->get()
            ->pluck('rights.*.name')
            ->flatten();

        $directRights = $this->rights()->pluck('name');

        $suppressed = $this->rights()
            ->wherePivot('suppress', true)
            ->pluck('name');

        return $roleRights
            ->merge($directRights)
            ->diff($suppressed)
            ->unique()
            ->values()
            ->all();
    }

    public function hasRight(string $rightName): bool
    {
        return in_array($rightName, $this->effectiveRights(), true);
    }
}
