<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Quyen;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','phone','password'];
    protected $hidden   = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * users.id <-> QUYEN.MAQUYEN qua QUYEN_NGUOIDUNG (KHÔNG có timestamps)
     */
    public function roles()
    {
        return $this->belongsToMany(
            Quyen::class,      // model liên kết
            'QUYEN_NGUOIDUNG', // bảng pivot
            'user_id',         // FK pivot -> users.id
            'MAQUYEN',         // FK pivot -> QUYEN.MAQUYEN
            'id',              // local key users
            'MAQUYEN'          // owner key QUYEN
        )->withPivot('user_id','MAQUYEN'); // chỉ rõ cột pivot có thật
        // ->withTimestamps(); // CHỈ bật nếu pivot có cả created_at & updated_at
    }

    /**
     * Kiểm tra role: hỗ trợ 'admin' hoặc 'Q01' (case-insensitive)
     */
    public function hasRole(string $role): bool
    {
        $needle = mb_strtolower($role);

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn($q) =>
                mb_strtolower($q->TENQUYEN) === $needle
                || mb_strtolower($q->MAQUYEN) === $needle
            );
        }

        return $this->roles()
            ->whereRaw('LOWER(TENQUYEN) = ?', [$needle])
            ->orWhereRaw('LOWER(QUYEN.MAQUYEN) = ?', [$needle])
            ->exists();
    }

    public function assignRole(string $maQuyen): void
    {
        $this->roles()->syncWithoutDetaching([$maQuyen]);
    }

    public function removeRole(string $maQuyen): void
    {
        $this->roles()->detach($maQuyen);
    }
}
