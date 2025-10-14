<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\Request;
use App\Models\SanPham;
use App\Models\Loai;

class ProductController extends Controller
{
    /** clamp page/per_page giống code cũ */
    private function pageParams(Request $r): array
    {
        $page    = max(1, (int) $r->query('page', 1));
        $perPage = max(1, min(48, (int) $r->query('per_page', 12)));
        return [$page, $perPage];
    }

    /** Trang chủ (nếu bạn chỉ muốn video background) */
    public function home()
    {
        return view('pages.home'); // video background + header/footer
    }

    private function priceParams(Request $r): array
    {
        $min  = $r->query('min_price');
        $max  = $r->query('max_price');
        $sort = (string) $r->query('sort', 'newest'); // newest | price_asc | price_desc

        $min = is_numeric($min) ? max(0, (int)$min) : null;
        $max = is_numeric($max) ? max(0, (int)$max) : null;
        if (!is_null($min) && !is_null($max) && $min > $max) {
            [$min, $max] = [$max, $min]; // hoán đổi nếu nhập ngược
        }
        return [$min, $max, $sort];
    }

    private function applyPriceAndSort(Request $r, Builder $query): Builder
    {
        [$min, $max, $sort] = $this->priceParams($r);

        if (!is_null($min)) $query->where('GIABAN', '>=', $min);
        if (!is_null($max)) $query->where('GIABAN', '<=', $max);

        switch ($sort) {
            case 'price_asc':  $query->orderBy('GIABAN', 'asc');  break;
            case 'price_desc': $query->orderBy('GIABAN', 'desc'); break;
            default:           $query->orderBy('MASANPHAM', 'desc');
        }
        return $query;
    }

    /** Tất cả sản phẩm  **/
    public function allProducts(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);
        $query = SanPham::query();
        $this->applyPriceAndSort($r, $query);

        $sp = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        [$min, $max, $sort] = $this->priceParams($r);

        return view('pages.all_product', compact('sp', 'page', 'perPage', 'min', 'max', 'sort'));
    }

    /** Theo Danh mục **/
    public function byCategory(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);
        $dm = (int) $r->query('dm');

        $query = SanPham::query()
            ->whereHas('loai', fn($q) => $q->where('MADANHMUC', $dm));

        $this->applyPriceAndSort($r, $query);

        $sp = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        [$min, $max, $sort] = $this->priceParams($r);

        return view('pages.category', compact('sp', 'dm', 'page', 'perPage', 'min', 'max', 'sort'));
    }

    /** Theo Loại **/
    public function byType(Request $r, string $maLoai)
    {
        [$page, $perPage] = $this->pageParams($r);

        $query = SanPham::where('MALOAI', $maLoai);
        $this->applyPriceAndSort($r, $query);

        $sp = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        [$min, $max, $sort] = $this->priceParams($r);

        return view('pages.type', compact('sp', 'maLoai', 'page', 'perPage', 'min', 'max', 'sort'));
    }

    /** Search theo tên sp  **/
    public function search(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);
        $q = (string) $r->query('q', '');

        $query = SanPham::where('TENSANPHAM', 'like', '%' . $q . '%');
        $this->applyPriceAndSort($r, $query);

        $sp = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        [$min, $max, $sort] = $this->priceParams($r);

        return view('pages.search', compact('sp', 'q', 'page', 'perPage', 'min', 'max', 'sort'));
    }

    /** Chi tiết sản phẩm (nếu cần) */
    public function detail(string $id)
    {
        $p = \App\Models\SanPham::with(['nhaCungCap', 'loai'])
            ->where('MASANPHAM', $id)
            ->firstOrFail();

        $reviews = \App\Models\DanhGia::with('khachHang')
            ->where('MASANPHAM', $id)
            ->orderByDesc('NGAYDANHGIA')
            ->paginate(5)
            ->withQueryString();

        $ratingAvg = (float) (\App\Models\DanhGia::where('MASANPHAM', $id)->avg('DIEMSO') ?? 0);
        $ratingCount = (int) \App\Models\DanhGia::where('MASANPHAM', $id)->count();

        $breakdown = \App\Models\DanhGia::where('MASANPHAM', $id)
            ->selectRaw('DIEMSO, COUNT(*) as c')
            ->groupBy('DIEMSO')
            ->pluck('c', 'DIEMSO')
            ->all();

        $related = \App\Models\SanPham::where('MALOAI', $p->MALOAI)
            ->where('MASANPHAM', '!=', $id)->take(8)->get();

        return view('pages.detail', compact(
            'p', 'related', 'reviews', 'ratingAvg', 'ratingCount', 'breakdown'
        ));
    }

    /** Danh sách sản phẩm đang khuyến mãi (scope PRODUCT) */
    public function promotions(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);

        $query = SanPham::query()
            ->whereHas('khuyenmais', function ($q) {
                $q->where('PHAMVI', 'PRODUCT')
                  ->where('NGAYBATDAU', '<=', now())
                  ->where('NGAYKETTHUC', '>=', now());
            })
            ->with('activePromotions');

        $this->applyPriceAndSort($r, $query);

        $sp = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        [$min, $max, $sort] = $this->priceParams($r);

        return view('pages.promo_products', compact('sp', 'page', 'perPage', 'min', 'max', 'sort'));
    }

}
