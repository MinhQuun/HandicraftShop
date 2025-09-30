<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CTPhieuXuat extends Model
{
    protected $table = 'CT_PHIEUXUAT';
    protected $primaryKey = ['MAPHIEUXUAT', 'MASANPHAM'];
    public $incrementing = false;
    protected $fillable = ['MAPHIEUXUAT', 'MASANPHAM', 'SOLUONG', 'DONGIA'];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MASANPHAM', 'MASANPHAM');
    }
}