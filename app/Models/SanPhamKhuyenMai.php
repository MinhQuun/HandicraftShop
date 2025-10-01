<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPhamKhuyenMai extends Model
{
    use HasFactory;

    protected $table = 'SANPHAM_KHUYENMAI';

    protected $primaryKey = ['MASANPHAM', 'MAKHUYENMAI'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'MASANPHAM',
        'MAKHUYENMAI',
    ];
}