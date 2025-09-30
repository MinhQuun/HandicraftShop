<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HinhThucTT extends Model
{
    protected $table = 'HINHTHUCTT';
    protected $primaryKey = 'MATT';
    public $incrementing = false;      // PK là varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MATT', 'LOAITT'];

    // (tuỳ chọn) Quan hệ với đơn hàng: DONHANG.MATT -> HINHTHUCTT.MATT
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'MATT', 'MATT');
    }
}
