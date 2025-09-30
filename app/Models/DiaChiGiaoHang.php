<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaChiGiaoHang extends Model
{
    protected $table = 'DIACHI_GIAOHANG';
    protected $primaryKey = 'MADIACHI';
    public $timestamps = false;
    protected $fillable = ['MAKHACHHANG', 'TENKH', 'SDT', 'DIACHI'];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }
}