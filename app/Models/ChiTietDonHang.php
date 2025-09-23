<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDonHang extends Model
{
    protected $table = 'CHITIETDONHANG';
    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = ['MADONHANG','MASANPHAM','SOLUONG','DONGIA'];

    public function donHang(): BelongsTo
    {
        return $this->belongsTo(DonHang::class, 'MADONHANG', 'MADONHANG');
    }

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'MASANPHAM', 'MASANPHAM');
    }
}
