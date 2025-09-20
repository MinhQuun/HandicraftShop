<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// nhớ tạo App\Models\Quyen (bên dưới)
use App\Models\Quyen;

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
     * Quan hệ nhiều-nhiều: users (id) <-> QUYEN (MAQUYEN) qua QUYEN_NGUOIDUNG
     */
    public function roles()
    {
        return $this->belongsToMany(
            Quyen::class,            // model liên kết
            'QUYEN_NGUOIDUNG',       // bảng pivot
            'user_id',               // FK pivot -> users.id
            'MAQUYEN',               // FK pivot -> QUYEN.MAQUYEN
            'id',                    // local key của users
            'MAQUYEN'                // owner key của QUYEN
        )->withTimestamps(false);
    }

    /**
     * Kiểm tra user có quyền: chấp nhận 'admin'/'Admin' hoặc mã 'Q01'...
     * So khớp không phân biệt hoa/thường.
     */
    public function hasRole(string $role): bool
    {
        $needle = mb_strtolower($role);

        // Ưu tiên dùng collection đã load (tránh query thừa)
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(function ($q) use ($needle) {
                return mb_strtolower($q->TENQUYEN) === $needle
                    || mb_strtolower($q->MAQUYEN) === $needle;
            });
        }

        // Nếu chưa load thì query gọn
        return $this->roles()
            ->whereRaw('LOWER(TENQUYEN) = ?', [$needle])
            ->orWhereRaw('LOWER(QUYEN.MAQUYEN) = ?', [$needle])
            ->exists();
    }

    /**
     * Gán quyền cho user theo MAQUYEN (vd: 'Q01' hoặc '1')
     * - Dùng syncWithoutDetaching để tránh trùng
     */
    public function assignRole(string $maQuyen): void
    {
        $this->roles()->syncWithoutDetaching([$maQuyen]);
    }

    /**
     * Gỡ quyền theo MAQUYEN
     */
    public function removeRole(string $maQuyen): void
    {
        $this->roles()->detach($maQuyen);
    }
}
