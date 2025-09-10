<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    protected $table = 'DANHMUCSANPHAM';
    protected $primaryKey = 'MADANHMUC';
    public $timestamps = false;

    protected $fillable = ['TENDANHMUC'];

    public function loais()
    {
        return $this->hasMany(Loai::class, 'MADANHMUC', 'MADANHMUC');
    }
}
