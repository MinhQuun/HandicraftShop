<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhuyenMai extends Model
{
    use HasFactory;

    protected $table = 'KHUYENMAI';

    protected $primaryKey = 'MAKHUYENMAI';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'MAKHUYENMAI',
        'LOAIKHUYENMAI',
        'TENKHUYENMAI',
        'NGAYBATDAU',
        'NGAYKETTHUC',
        'GIAMGIA',
    ];

    protected $casts = [
        'NGAYBATDAU' => 'datetime',
        'NGAYKETTHUC' => 'datetime',
        'GIAMGIA' => 'decimal:2',
    ];
}