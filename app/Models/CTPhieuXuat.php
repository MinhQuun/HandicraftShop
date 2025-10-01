<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CTPhieuXuat extends Model
{

    protected $table = 'CT_PHIEUXUAT';
    public $timestamps = false;
    protected $primaryKey = ['MAPX', 'MASANPHAM'];
    public $incrementing = false;
    protected $fillable = ['MAPX', 'MASANPHAM', 'SOLUONG', 'DONGIA'];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'MASANPHAM', 'MASANPHAM');
    }
}