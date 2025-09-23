<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhachHang extends Model
{
    protected $table = 'KHACHHANG';
    protected $primaryKey = 'MAKHACHHANG';
    public $timestamps = false; // bảng KHACHHANG không có created_at/updated_at
    protected $fillable = ['user_id','HOTEN','SODIENTHOAI','EMAIL'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function diaChis(): HasMany
    {
        return $this->hasMany(DiaChiGiaoHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }

    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }
}
