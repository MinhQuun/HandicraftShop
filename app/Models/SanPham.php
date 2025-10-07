<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SanPham extends Model
{
    protected $table = 'SANPHAM';
    protected $primaryKey = 'MASANPHAM';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MASANPHAM','TENSANPHAM','HINHANH','GIABAN','MOTA',
        'MALOAI','MANHACUNGCAP' // bỏ MAKHUYENMAI để tránh mâu thuẫn mô hình
    ];
    protected $guarded = ['SOLUONGTON'];

    protected $casts = [
        'GIABAN'      => 'float',
        'SOLUONGTON'  => 'int',
    ];

    public function loai(){ return $this->belongsTo(Loai::class, 'MALOAI', 'MALOAI'); }
    public function nhaCungCap(){ return $this->belongsTo(NhaCungCap::class, 'MANHACUNGCAP', 'MANHACUNGCAP'); }

    public function danhGias(){ return $this->hasMany(DanhGia::class, 'MASANPHAM', 'MASANPHAM'); }

    public function khuyenmais()
    {
        return $this->belongsToMany(KhuyenMai::class, 'SANPHAM_KHUYENMAI', 'MASANPHAM', 'MAKHUYENMAI')
            ->withPivot([])->withTimestamps(false);
    }

    /** Scopes tiện dụng */
    public function scopeSearch(Builder $q, ?string $s): Builder
    {
        if (!$s) return $q;
        return $q->where(function ($x) use ($s) {
            $x->where('TENSANPHAM', 'like', "%{$s}%")
                ->orWhere('MASANPHAM', 'like', "%{$s}%");
        });
    }
    public function scopeOfType(Builder $q, ?string $maLoai): Builder { return $maLoai ? $q->where('MALOAI', $maLoai) : $q; }
    public function scopeOfCategory(Builder $q, $maDanhMuc): Builder
    {
        if (!$maDanhMuc) return $q;
        return $q->whereHas('loai', fn ($w) => $w->where('MADANHMUC', $maDanhMuc));
    }
    public function scopeOfSupplier(Builder $q, $maNcc): Builder { return $maNcc ? $q->where('MANHACUNGCAP', $maNcc) : $q; }

    /** KM đang hiệu lực của SP, sắp xếp theo ưu tiên */
    public function activePromotions()
    {
        return $this->khuyenmais()->where('PHAMVI','PRODUCT')->where(function($q){
            $q->where('NGAYBATDAU','<=', now())->where('NGAYKETTHUC','>=', now());
        })->orderByDesc('UUTIEN');
    }

    /** Giá sau khuyến mãi (lấy KM ưu tiên cao nhất) */
    public function getGiaSauKmAttribute(): float
    {
        $price = (float) $this->GIABAN;
        $promo = $this->activePromotions()->first();
        if (!$promo) return $price;

        if ($promo->LOAIKHUYENMAI === 'Giảm %') {
            return max(0, round($price * (1 - (float)$promo->GIAMGIA / 100)));
        }
        if ($promo->LOAIKHUYENMAI === 'Giảm fixed') {
            return max(0, $price - (float)$promo->GIAMGIA);
        }
        // Flash Sale hay loại khác: tuỳ chỉnh
        return $price;
    }

    /** URL ảnh */
    public function getImageUrlAttribute(): string
    {
        $img = trim((string) ($this->HINHANH ?? ''));
        return $img !== '' ? asset('assets/images/' . $img) : asset('HinhAnh/LOGO/Logo.jpg');
    }
}