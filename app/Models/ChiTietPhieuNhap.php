<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ChiTietPhieuNhap extends Model
{
    protected $table = 'CT_PHIEUNHAP';
    public $incrementing = false; // PK là (MAPN, MASANPHAM)
    public $timestamps = false;


    protected $fillable = [
        'MAPN', 'MASANPHAM', 'SOLUONG', 'DONGIA'
    ];


    protected $casts = [
        'SOLUONG' => 'int',
        'DONGIA' => 'float',
    ];


    public function phieuNhap(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PhieuNhap::class, 'MAPN', 'MAPN');
    }


    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SanPham::class, 'MASANPHAM', 'MASANPHAM');
    }
}