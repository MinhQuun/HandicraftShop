<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;

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

    /** Tất cả sản phẩm (all_product.php cũ) */
    public function allProducts(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);

        $sp = SanPham::orderBy('MASANPHAM', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return view('pages.all_product', compact('sp', 'page', 'perPage'));
    }

    /** Theo Danh mục: dm=? (MADANHMUC) -> giống category.php cũ */
    public function byCategory(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);
        $dm = (int) $r->query('dm');

        $sp = SanPham::query()
            ->whereHas('loai', fn($q) => $q->where('MADANHMUC', $dm))
            ->orderBy('MASANPHAM', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return view('pages.category', compact('sp', 'dm', 'page', 'perPage'));
    }

    /** Theo Loại: /loai/{maLoai} -> giống type.php cũ */
    public function byType(Request $r, string $maLoai)
    {
        [$page, $perPage] = $this->pageParams($r);

        $sp = SanPham::where('MALOAI', $maLoai)
            ->orderBy('MASANPHAM', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return view('pages.type', compact('sp', 'maLoai', 'page', 'perPage'));
    }

    /** Search theo tên sp -> giống search.php cũ */
    public function search(Request $r)
    {
        [$page, $perPage] = $this->pageParams($r);
        $q = (string) $r->query('q', '');

        $sp = SanPham::where('TENSANPHAM', 'like', '%' . $q . '%')
            ->orderBy('MASANPHAM', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        return view('pages.search', compact('sp', 'q', 'page', 'perPage'));
    }

    /** Chi tiết sản phẩm (nếu cần) */
    public function detail(string $id)
    {
        $p = \App\Models\SanPham::with(['nhaCungCap', 'loai'])
            ->where('MASANPHAM', $id)
            ->firstOrFail();

        $related = \App\Models\SanPham::where('MALOAI', $p->MALOAI)
            ->where('MASANPHAM', '!=', $id)->take(8)->get();

        return view('pages.detail', compact('p', 'related'));
    }
}
