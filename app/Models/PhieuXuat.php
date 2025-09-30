<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuXuat extends Model
{
    protected $table = 'PHIEUXUAT';
    protected $primaryKey = 'MAPHIEUXUAT';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'MAKHACHHANG', 'MADIACHI', 'NGAYXUAT', 'TONGSL', 'TRANGTHAI'
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(CTPhieuXuat::class, 'MAPX', 'MAPHIEUXUAT');
    }

    public function diaChi(): BelongsTo
    {
        return $this->belongsTo(DiaChiGiaoHang::class, 'MADIACHI', 'MADIACHI');
    }
}