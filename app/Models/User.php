<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Quan hệ nhiều-nhiều: User ↔ Quyền
     */
    public function roles()
    {
        return $this->belongsToMany(
            Quyen::class,          // Model liên kết
            'QUYEN_NGUOIDUNG',     // Bảng trung gian
            'user_id',             // Khóa ngoại trỏ đến users
            'MAQUYEN'              // Khóa ngoại trỏ đến quyền
        );
    }

    /**
     * Kiểm tra user có quyền cụ thể không
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('TENQUYEN', $role)->exists();
    }

    /**
     * Gán quyền cho user
     */
    public function assignRole(string $maQuyen): void
    {
        $this->roles()->attach($maQuyen);
    }

    /**
     * Xóa quyền của user
     */
    public function removeRole(string $maQuyen): void
    {
        $this->roles()->detach($maQuyen);
    }
}
