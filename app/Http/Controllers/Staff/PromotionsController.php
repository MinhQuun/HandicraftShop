<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionsController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim($request->input('q',''));
        $loai   = $request->input('loai');           // Giảm %, Giảm fixed, Flash Sale
        $phamvi = $request->input('phamvi');         // ORDER | PRODUCT
        $state  = $request->input('state');          // upcoming | active | expired

        $query = KhuyenMai::query()
            ->withCount('sanphams')
            ->when($q, function ($w) use ($q) {
                $w->where(function ($sub) use ($q) {
                    $sub->where('MAKHUYENMAI','like',"%{$q}%")
                        ->orWhere('TENKHUYENMAI','like',"%{$q}%")
                        ->orWhere('LOAIKHUYENMAI','like',"%{$q}%");
                });
            })
            ->when($loai,   fn($w)=>$w->where('LOAIKHUYENMAI',$loai))
            ->when($phamvi, fn($w)=>$w->where('PHAMVI',$phamvi))
            ->when($state === 'upcoming', fn($w)=>$w->upcoming())
            ->when($state === 'active',   fn($w)=>$w->active())
            ->when($state === 'expired',  fn($w)=>$w->expired())
            ->orderByDesc('UUTIEN')
            ->orderBy('NGAYBATDAU');

        $promotions = $query->paginate(10)->appends($request->query());

        $promotionTypes = [
            'Giảm %'     => 'Giảm %',
            'Giảm fixed' => 'Giảm số tiền',
            'Flash Sale' => 'Flash Sale',
        ];
        $scopeOptions = ['ORDER' => 'Voucher (toàn đơn)', 'PRODUCT' => 'Theo sản phẩm'];

        $products = SanPham::select('MASANPHAM','TENSANPHAM')->orderBy('TENSANPHAM')->get();
        $loais    = DB::table('LOAI')->select('MALOAI','TENLOAI','MADANHMUC')->orderBy('TENLOAI')->get();
        $danhmucs = DB::table('DANHMUCSANPHAM')->select('MADANHMUC','TENDANHMUC')->orderBy('TENDANHMUC')->get();
        $nccs     = DB::table('NHACUNGCAP')->select('MANHACUNGCAP','TENNHACUNGCAP')->orderBy('TENNHACUNGCAP')->get();

        return view('staff.promotions', compact(
            'promotions','promotionTypes','scopeOptions','q','loai','phamvi','state',
            'products','loais','danhmucs','nccs'
        ));
    }

    /** Gom danh sách MASANPHAM từ các tiêu chí lọc (SP/Loại/Danh mục/NCC) */
    protected function resolveProductIdsFromTargets(Request $request): array
    {
        $ids = collect($request->input('sanphams', []))->filter()->values();

        $maloais = (array) $request->input('maloais', []);
        $madms   = (array) $request->input('madanhmucs', []);
        $manccs  = (array) $request->input('manccs', []);

        if ($maloais || $madms || $manccs) {
            $q = SanPham::query();
            if ($maloais) $q->whereIn('MALOAI', $maloais);
            if ($madms)   $q->whereHas('loai', fn($w)=>$w->whereIn('MADANHMUC',$madms));
            if ($manccs)  $q->whereIn('MANHACUNGCAP', $manccs);

            $ids = $ids->merge($q->pluck('MASANPHAM'));
        }

        return $ids->unique()->values()->all();
    }

    /** Sinh mã KM tự động (khi PRODUCT và bạn không nhập mã) */
    protected function generatePromotionCode(): string
    {
        return 'KM-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(3));
    }

    public function store(Request $request)
    {
        $scope = $request->input('PHAMVI');

        // Base rules (mã KM sẽ kiểm tra điều kiện ở dưới để "required hoặc auto")
        $rules = [
            'TENKHUYENMAI'  => 'required|string',
            'LOAIKHUYENMAI' => 'required|string',
            'NGAYBATDAU'    => 'required|date',
            'NGAYKETTHUC'   => 'required|date|after:NGAYBATDAU',
            'GIAMGIA'       => 'required|numeric|min:0',
            'PHAMVI'        => 'required|in:ORDER,PRODUCT',
            'UUTIEN'        => 'nullable|integer|min:0',
            // điều kiện mềm / targets
            'min_order_total' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'non_stackable'   => 'nullable|boolean',
            'sanphams'        => 'array',
            'maloais'         => 'array',
            'madanhmucs'      => 'array',
            'manccs'          => 'array',
        ];

        // Voucher bắt buộc nhập mã; Product có thể để trống -> sẽ auto generate
        if ($scope === 'ORDER') {
            $rules['MAKHUYENMAI'] = 'required|string|alpha_dash|uppercase|unique:KHUYENMAI,MAKHUYENMAI';
        } else {
            $rules['MAKHUYENMAI'] = 'nullable|string|alpha_dash|uppercase|unique:KHUYENMAI,MAKHUYENMAI';
        }

        $validated = $request->validate($rules);

        // Luật theo loại giảm
        if ($validated['LOAIKHUYENMAI'] === 'Giảm %') {
            if ($validated['GIAMGIA'] <= 0 || $validated['GIAMGIA'] > 100) {
                return back()->withInput()->with('error', 'Mức giảm % phải trong khoảng 1–100.');
            }
        } else {
            if ($validated['GIAMGIA'] < 0) {
                return back()->withInput()->with('error', 'Mức giảm tiền phải ≥ 0.');
            }
        }

        // Nếu là PRODUCT mà không chọn bất kỳ tiêu chí nào -> báo lỗi
        if ($scope === 'PRODUCT') {
            $hasTargets =
                !empty($request->input('sanphams', [])) ||
                !empty($request->input('maloais', [])) ||
                !empty($request->input('madanhmucs', [])) ||
                !empty($request->input('manccs', []));
            if (!$hasTargets) {
                return back()->withInput()->with('error', 'Vui lòng chọn ít nhất 1 tiêu chí áp dụng (sản phẩm/loại/danh mục/NCC).');
            }
        }

        // Gói điều kiện mềm
        $ruleJson = [
            'non_stackable'   => (bool)$request->boolean('non_stackable'),
            'min_order_total' => $request->input('min_order_total'),
            'max_discount'    => $request->input('max_discount'),
            'targets'         => [
                'sanphams'   => array_values((array)$request->input('sanphams', [])),
                'maloais'    => array_values((array)$request->input('maloais', [])),
                'madanhmucs' => array_values((array)$request->input('madanhmucs', [])),
                'manccs'     => array_values((array)$request->input('manccs', [])),
            ]
        ];

        // Nếu PRODUCT mà không nhập mã -> tự sinh
        $code = $validated['MAKHUYENMAI'] ?? null;
        if (!$code) {
            do {
                $code = $this->generatePromotionCode();
            } while (KhuyenMai::where('MAKHUYENMAI', $code)->exists());
        }

        DB::transaction(function () use ($validated, $scope, $ruleJson, $request, $code) {
            $promotion = KhuyenMai::create([
                'MAKHUYENMAI'   => $code,
                'TENKHUYENMAI'  => $validated['TENKHUYENMAI'],
                'LOAIKHUYENMAI' => $validated['LOAIKHUYENMAI'],
                'NGAYBATDAU'    => $validated['NGAYBATDAU'],
                'NGAYKETTHUC'   => $validated['NGAYKETTHUC'],
                'GIAMGIA'       => $validated['GIAMGIA'],
                'PHAMVI'        => $validated['PHAMVI'],
                'UUTIEN'        => $request->input('UUTIEN', 10),
                'DIEUKIEN_JSON' => json_encode($ruleJson, JSON_UNESCAPED_UNICODE),
            ]);

            if ($scope === 'PRODUCT') {
                $productIds = $this->resolveProductIdsFromTargets($request);
                $promotion->sanphams()->sync($productIds);
            }
        });

        return redirect()->route('staff.promotions.index')->with('success', 'Tạo khuyến mãi thành công.');
    }

    public function update(Request $request, $id)
    {
        $promotion = KhuyenMai::findOrFail($id);

        $validated = $request->validate([
            // Không cho sửa MAKHUYENMAI (PK)
            'TENKHUYENMAI'     => 'required|string',
            'LOAIKHUYENMAI'    => 'required|string',
            'NGAYBATDAU'       => 'required|date',
            'NGAYKETTHUC'      => 'required|date|after:NGAYBATDAU',
            'GIAMGIA'          => 'required|numeric|min:0',
            'PHAMVI'           => 'required|in:ORDER,PRODUCT',
            'UUTIEN'           => 'nullable|integer|min:0',
            'min_order_total'  => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'non_stackable'    => 'nullable|boolean',
            'sanphams'         => 'array',
            'maloais'          => 'array',
            'madanhmucs'       => 'array',
            'manccs'           => 'array',
        ]);

        if ($validated['LOAIKHUYENMAI'] === 'Giảm %') {
            if ($validated['GIAMGIA'] <= 0 || $validated['GIAMGIA'] > 100) {
                return back()->withInput()->with('error', 'Mức giảm % phải trong khoảng 1–100.');
            }
        } else {
            if ($validated['GIAMGIA'] < 0) {
                return back()->withInput()->with('error', 'Mức giảm tiền phải ≥ 0.');
            }
        }

        if ($validated['PHAMVI'] === 'PRODUCT') {
            $hasTargets =
                !empty($request->input('sanphams', [])) ||
                !empty($request->input('maloais', [])) ||
                !empty($request->input('madanhmucs', [])) ||
                !empty($request->input('manccs', []));
            if (!$hasTargets) {
                return back()->withInput()->with('error', 'Vui lòng chọn ít nhất 1 tiêu chí áp dụng (sản phẩm/loại/danh mục/NCC).');
            }
        }

        $ruleJson = [
            'non_stackable'   => (bool)$request->boolean('non_stackable'),
            'min_order_total' => $request->input('min_order_total'),
            'max_discount'    => $request->input('max_discount'),
            'targets'         => [
                'sanphams'   => array_values((array)$request->input('sanphams', [])),
                'maloais'    => array_values((array)$request->input('maloais', [])),
                'madanhmucs' => array_values((array)$request->input('madanhmucs', [])),
                'manccs'     => array_values((array)$request->input('manccs', [])),
            ]
        ];

        DB::transaction(function () use ($promotion, $validated, $ruleJson, $request) {
            $promotion->update([
                'TENKHUYENMAI'  => $validated['TENKHUYENMAI'],
                'LOAIKHUYENMAI' => $validated['LOAIKHUYENMAI'],
                'NGAYBATDAU'    => $validated['NGAYBATDAU'],
                'NGAYKETTHUC'   => $validated['NGAYKETTHUC'],
                'GIAMGIA'       => $validated['GIAMGIA'],
                'PHAMVI'        => $validated['PHAMVI'],
                'UUTIEN'        => $request->input('UUTIEN', $promotion->UUTIEN),
                'DIEUKIEN_JSON' => json_encode($ruleJson, JSON_UNESCAPED_UNICODE),
            ]);

            if ($validated['PHAMVI'] === 'PRODUCT') {
                $productIds = $this->resolveProductIdsFromTargets($request);
                $promotion->sanphams()->sync($productIds);
            } else {
                $promotion->sanphams()->sync([]); // voucher: không gán SP
            }
        });

        return redirect()->route('staff.promotions.index')->with('success', 'Cập nhật khuyến mãi thành công.');
    }

    public function destroy($id)
    {
        $promotion = KhuyenMai::findOrFail($id);
        $promotion->sanphams()->sync([]); // dọn pivot
        $promotion->delete();

        return redirect()->route('staff.promotions.index')->with('success', 'Đã xoá khuyến mãi.');
    }

    /** API kiểm mã voucher: /staff/promotions/check?code=... */
    public function checkVoucher(Request $request)
    {
        $code = strtoupper(trim($request->query('code','')));
        if (!$code) return response()->json(['ok'=>false,'msg'=>'Thiếu mã.'], 422);

        $km = KhuyenMai::voucher()->active()->where('MAKHUYENMAI',$code)->first();
        if (!$km) return response()->json(['ok'=>false,'msg'=>'Mã không hợp lệ hoặc đã hết hạn.'], 404);

        $rules = json_decode($km->DIEUKIEN_JSON ?? '[]', true) ?: [];
        return response()->json([
            'ok'           => true,
            'code'         => $km->MAKHUYENMAI,
            'type'         => $km->LOAIKHUYENMAI,          // Giảm % | Giảm fixed | Flash Sale
            'value'        => (float)$km->GIAMGIA,
            'min_total'    => (float)($rules['min_order_total'] ?? 0),
            'max_discount' => (float)($rules['max_discount'] ?? 0),
            'non_stackable'=> (bool)($rules['non_stackable'] ?? false),
            'from'         => optional($km->NGAYBATDAU)->toDateString(),
            'to'           => optional($km->NGAYKETTHUC)->toDateString(),
        ]);
    }
}