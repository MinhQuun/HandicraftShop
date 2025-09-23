<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonHang extends Model
{
    protected $table = 'DONHANG';
    protected $primaryKey = 'MADONHANG';
    public $timestamps = false;

    protected $fillable = [
        'MAKHACHHANG','MADIACHI','NGAYGIAO','NGAYDAT','MATT',
        'GHICHU','TONGSLHANG','TONGTHANHTIEN','TRANGTHAI'
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
