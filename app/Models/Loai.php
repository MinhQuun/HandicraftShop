<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loai extends Model
{
    protected $table = 'LOAI';
    protected $primaryKey = 'MALOAI';
    public $incrementing = false;     // vì PK là varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MALOAI','TENLOAI','MADANHMUC'];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'MADANHMUC', 'MADANHMUC');
    }

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class, 'MALOAI', 'MALOAI');
    }
}
