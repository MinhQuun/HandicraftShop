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
        'TENKHUYENMAI',
        'LOAIKHUYENMAI', // 'Giảm %' | 'Giảm fixed' | 'Flash Sale'...
        'NGAYBATDAU',
        'NGAYKETTHUC',
        'GIAMGIA',
        'PHAMVI',        // 'ORDER' | 'PRODUCT'
        'DIEUKIEN_JSON', // json string/array
        'UUTIEN',
    ];

    protected $casts = [
        'NGAYBATDAU'   => 'datetime',
        'NGAYKETTHUC'  => 'datetime',
        'GIAMGIA'      => 'decimal:2',
        'DIEUKIEN_JSON'=> 'array',
    ];

    /** Quan hệ N-N tới sản phẩm (áp dụng khi PHAMVI = PRODUCT) */
    public function sanphams()
    {
        return $this->belongsToMany(SanPham::class, 'SANPHAM_KHUYENMAI', 'MAKHUYENMAI', 'MASANPHAM');
    }

    /** Scopes trạng thái và phạm vi */
    public function scopeActive($q)
    {
        $now = now();
        return $q->where('NGAYBATDAU', '<=', $now)->where('NGAYKETTHUC', '>=', $now);
    }

    public function scopeUpcoming($q)
    {
        return $q->where('NGAYBATDAU', '>', now());
    }

    public function scopeExpired($q)
    {
        return $q->where('NGAYKETTHUC', '<', now());
    }

    public function scopeVoucher($q)
    {
        return $q->where('PHAMVI', 'ORDER');
    }

    public function scopeProduct($q)
    {
        return $q->where('PHAMVI', 'PRODUCT');
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->NGAYBATDAU && $this->NGAYKETTHUC && $this->NGAYBATDAU->lte($now) && $this->NGAYKETTHUC->gte($now);
    }

    public function isVoucher(): bool { return $this->PHAMVI === 'ORDER'; }
    public function isProduct(): bool { return $this->PHAMVI === 'PRODUCT'; }
}