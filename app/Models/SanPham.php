<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * SQL expression: giá sau khuyến mại (ưu tiên mức giá thấp nhất trong các KM PRODUCT đang hoạt động).
     */
    public static function effectivePriceExpression(?string $tableAlias = null): string
    {
        $table = $tableAlias ?: 'SANPHAM';

        return "COALESCE((
            SELECT MIN(
                CASE
                    WHEN km.LOAIKHUYENMAI LIKE '%\\%%' THEN {$table}.GIABAN * (1 - km.GIAMGIA / 100)
                    WHEN km.LOAIKHUYENMAI LIKE '%fixed%' THEN {$table}.GIABAN - km.GIAMGIA
                    ELSE {$table}.GIABAN
                END
            )
            FROM SANPHAM_KHUYENMAI AS spkm
            JOIN KHUYENMAI AS km ON km.MAKHUYENMAI = spkm.MAKHUYENMAI
            WHERE spkm.MASANPHAM = {$table}.MASANPHAM
              AND km.PHAMVI = 'PRODUCT'
              AND km.NGAYBATDAU <= NOW()
              AND km.NGAYKETTHUC >= NOW()
        ), {$table}.GIABAN)";
    }

    /**
     * SQL expression: phần trăm giảm giá tương đương của KM PRODUCT đang áp dụng.
     */
    public static function discountPercentExpression(?string $tableAlias = null): string
    {
        $table = $tableAlias ?: 'SANPHAM';

        return "COALESCE((
            SELECT MAX(
                CASE
                    WHEN km.LOAIKHUYENMAI LIKE '%\\%%' THEN km.GIAMGIA
                    WHEN km.LOAIKHUYENMAI LIKE '%fixed%' AND {$table}.GIABAN > 0 THEN ROUND(100 * km.GIAMGIA / {$table}.GIABAN, 2)
                    ELSE 0
                END
            )
            FROM SANPHAM_KHUYENMAI AS spkm
            JOIN KHUYENMAI AS km ON km.MAKHUYENMAI = spkm.MAKHUYENMAI
            WHERE spkm.MASANPHAM = {$table}.MASANPHAM
              AND km.PHAMVI = 'PRODUCT'
              AND km.NGAYBATDAU <= NOW()
              AND km.NGAYKETTHUC >= NOW()
        ), 0)";
    }

    public function loai(){ return $this->belongsTo(Loai::class, 'MALOAI', 'MALOAI'); }
    public function nhaCungCap(){ return $this->belongsTo(NhaCungCap::class, 'MANHACUNGCAP', 'MANHACUNGCAP'); }

    public function danhGias(){ return $this->hasMany(DanhGia::class, 'MASANPHAM', 'MASANPHAM'); }

    public function khuyenmais()
    {
        return $this->belongsToMany(KhuyenMai::class, 'SANPHAM_KHUYENMAI', 'MASANPHAM', 'MAKHUYENMAI');
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

    /**
     * Bổ sung field giá sau KM và % giảm vào query để tái sử dụng cho lọc/sắp xếp.
     */
    public function scopeWithPricing(Builder $q): Builder
    {
        $table       = $q->getModel()->getTable();
        $priceSql    = self::effectivePriceExpression($table);
        $discountSql = self::discountPercentExpression($table);

        return $q->select("{$table}.*")
            ->selectRaw("{$priceSql} as gia_sau_km_calculated")
            ->selectRaw("{$discountSql} as discount_percent_calculated");
    }

    /** KM đang hiệu lực của SP, sắp xếp theo ưu tiên */
    public function activePromotions()
    {
        return $this->khuyenmais()->where('PHAMVI','PRODUCT')->where(function($q){
            $q->where('NGAYBATDAU','<=', now())->where('NGAYKETTHUC','>=', now());
        })->orderByDesc('UUTIEN');
    }

    /** Giá sau khuyến mại (lấy KM ưu tiên cao nhất) */
    public function getGiaSauKmAttribute(): float
    {
        if (array_key_exists('gia_sau_km_calculated', $this->attributes)) {
            return (float) $this->attributes['gia_sau_km_calculated'];
        }

        $price = (float) $this->GIABAN;
        $promo = $this->activePromotions()->first();
        if (!$promo) return $price;

        if ($promo->LOAIKHUYENMAI === 'Giảm %') {
            return max(0, round($price * (1 - (float)$promo->GIAMGIA / 100)));
        }
        if ($promo->LOAIKHUYENMAI === 'Giảm fixed') {
            return max(0, $price - (float)$promo->GIAMGIA);
        }
        // Flash Sale hay loại khác: tự chỉnh
        return $price;
    }

    /**
     * Tỷ lệ giảm giá (0-100).
     */
    public function getDiscountPercentAttribute(): float
    {
        if (array_key_exists('discount_percent_calculated', $this->attributes)) {
            return (float) $this->attributes['discount_percent_calculated'];
        }

        $orig = (float) $this->GIABAN;
        if ($orig <= 0) {
            return 0;
        }

        $sale = $this->gia_sau_km;
        return max(0, round(100 * max(0, $orig - $sale) / $orig));
    }

    /** URL ảnh */
    public function getImageUrlAttribute(): string
    {
        $img = trim((string) ($this->HINHANH ?? ''));
        return $img !== '' ? asset('assets/images/' . $img) : asset('HinhAnh/LOGO/Logo.jpg');
    }
}
