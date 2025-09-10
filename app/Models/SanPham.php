<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    protected $table = 'SANPHAM';
    protected $primaryKey = 'MASANPHAM';
    public $incrementing = false;     // PK varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MASANPHAM','TENSANPHAM','HINHANH','GIABAN','SOLUONGTON','MOTA',
        'MALOAI','MAKHUYENMAI','MANHACUNGCAP'
    ];

    public function loai()
    {
        return $this->belongsTo(Loai::class, 'MALOAI', 'MALOAI');
    }

    // tiện cho tìm kiếm
    public function scopeSearch($q, $s)
    {
        if (!$s) return $q;
        return $q->where('TENSANPHAM','like',"%$s%")
                ->orWhere('MASANPHAM','like',"%$s%");
    }
}
