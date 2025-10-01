<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SanPham extends Model
{
    protected $table = 'SANPHAM';
    protected $primaryKey = 'MASANPHAM';
    public $incrementing = false;        // PK dạng varchar
    protected $keyType = 'string';
    public $timestamps = false;

    // KHÓA TỒN: không cho mass-assign SOLUONGTON
    protected $fillable = [
        'MASANPHAM','TENSANPHAM','HINHANH','GIABAN','MOTA',
        'MALOAI','MAKHUYENMAI','MANHACUNGCAP'
    ];
    protected $guarded = ['SOLUONGTON'];

    protected $casts = [
        'GIABAN'      => 'float',
        'SOLUONGTON'  => 'int',
    ];

    /** Quan hệ */
    public function loai()
    {
        return $this->belongsTo(Loai::class, 'MALOAI', 'MALOAI');
    }

    public function nhaCungCap()
    {
        return $this->belongsTo(NhaCungCap::class, 'MANHACUNGCAP', 'MANHACUNGCAP');
    }

    /** ===== Scopes tiện dụng ===== */

    // Tìm theo tên/ mã (được group để tránh "or" rơi ra ngoài)
    public function scopeSearch(Builder $q, ?string $s): Builder
    {
        if (!$s) return $q;
        return $q->where(function ($x) use ($s) {
            $x->where('TENSANPHAM', 'like', "%{$s}%")
                ->orWhere('MASANPHAM', 'like', "%{$s}%");
        });
    }

    // Lọc theo MALOAI
    public function scopeOfType(Builder $q, ?string $maLoai): Builder
    {
        return $maLoai ? $q->where('MALOAI', $maLoai) : $q;
    }

    // Lọc theo MADANHMUC (thông qua bảng LOAI)
    public function scopeOfCategory(Builder $q, $maDanhMuc): Builder
    {
        if (!$maDanhMuc) return $q;
        return $q->whereHas('loai', fn ($w) => $w->where('MADANHMUC', $maDanhMuc));
    }

    // Lọc theo nhà cung cấp
    public function scopeOfSupplier(Builder $q, $maNcc): Builder
    {
        return $maNcc ? $q->where('MANHACUNGCAP', $maNcc) : $q;
    }

    /** URL ảnh tiện dùng (nếu muốn) */
    public function getImageUrlAttribute(): string
    {
        $img = trim((string) ($this->HINHANH ?? ''));
        return $img !== '' ? asset('assets/images/' . $img)
                            : asset('HinhAnh/LOGO/Logo.jpg');
    }

    public function danhGias()
    {
        return $this->hasMany(\App\Models\DanhGia::class, 'MASANPHAM', 'MASANPHAM');
    }
    public function khuyenmais()
    {
        return $this->belongsToMany(KhuyenMai::class, 'SANPHAM_KHUYENMAI', 'MASANPHAM', 'MAKHUYENMAI');
    }
}