<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaCungCap extends Model
{
    protected $table = 'NHACUNGCAP';
    protected $primaryKey = 'MANHACUNGCAP';
    public $incrementing = false;     // PK dạng varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MANHACUNGCAP',
        'TENNHACUNGCAP',
        'DIACHI',
        'SODIENTHOAI',
        'EMAIL',
        // thêm các trường khác nếu có
    ];

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class, 'MANHACUNGCAP', 'MANHACUNGCAP');
    }
}
