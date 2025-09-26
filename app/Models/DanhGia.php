<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    protected $table = 'DANHGIA';
    public $timestamps = false; // dùng cột NGAYDANHGIA

    protected $primaryKey = 'MADANHGIA';
    protected $fillable = [
        'MAKHACHHANG',
        'MASANPHAM',
        'DIEMSO',
        'NHANXET',
        'NGAYDANHGIA',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MASANPHAM', 'MASANPHAM');
    }

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKHACHHANG', 'MAKHACHHANG');
    }
}
