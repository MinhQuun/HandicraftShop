<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonHang extends Model
{
    protected $table = 'DONHANG';
    protected $primaryKey = 'MADONHANG';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'MAKHACHHANG', 'MADIACHI', 'NGAYLAP', 'MAHTTHANHTOAN',
        'GHICHU', 'TONGTIEN', 'TRANGTHAI'
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietDonHang::class, 'MADONHANG', 'MADONHANG');
    }
}