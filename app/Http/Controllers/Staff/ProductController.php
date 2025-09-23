<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim($request->get('q', ''));   // tìm theo tên sp / tên loại
        $loai = $request->get('loai');          // MALOAI filter

        $products = DB::table('SANPHAM as s')
            ->leftJoin('LOAI as l', 'l.MALOAI', '=', 's.MALOAI')
            ->leftJoin('NHACUNGCAP as n', 'n.MANHACUNGCAP', '=', 's.MANHACUNGCAP')
            ->when($q, function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($x) use ($like) {
                    $x->where('s.TENSANPHAM', 'like', $like)   // <-- tên sản phẩm
                    ->orWhere('l.TENLOAI', 'like', $like);    // <-- tên loại
                });
            })
            ->when($loai, fn($q2) => $q2->where('s.MALOAI', $loai)) // lọc theo MALOAI
            ->select(
                's.MASANPHAM',
                's.TENSANPHAM',
                DB::raw('s.GIABAN AS GIA'),
                DB::raw('s.SOLUONGTON AS TONKHO'),
                's.HINHANH',
                's.MOTA',
                's.MALOAI',
                's.MANHACUNGCAP',
                'l.TENLOAI',
                'n.TENNHACUNGCAP'
            )
            ->orderBy('s.MASANPHAM', 'asc') // tăng dần như bạn muốn
            ->paginate(8)
            ->withQueryString();

        // Lấy danh sách loại cho combobox
        $categories = DB::table('LOAI')
            ->select('MALOAI','TENLOAI')
            ->orderBy('TENLOAI')
            ->get();

        return view('staff.products', [
            'products'   => $products,
            'categories' => $categories,   // <-- truyền đúng biến mà view đang dùng
            'q'          => $q,
            'loai'       => $loai,
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            // PK không auto → bắt buộc nhập & duy nhất
            'MASANPHAM'    => ['required','string','max:10', Rule::unique('SANPHAM','MASANPHAM')],
            'TENSANPHAM'   => ['required','string','max:255'],
            'GIABAN'       => ['required','numeric','min:0'],
            'SOLUONGTON'   => ['nullable','integer','min:0'],
            'MOTA'         => ['nullable','string','max:1000'],
            'MALOAI'       => ['nullable', Rule::exists('LOAI','MALOAI')],
            'MAKHUYENMAI'  => ['nullable', Rule::exists('KHUYENMAI','MAKHUYENMAI')],
            'MANHACUNGCAP' => ['nullable', Rule::exists('NHACUNGCAP','MANHACUNGCAP')],
            'HINHANH'      => ['nullable','image','max:4096'],
        ]);

        if (!isset($data['SOLUONGTON'])) $data['SOLUONGTON'] = 0;

        if ($request->hasFile('HINHANH')) {
            $path = $request->file('HINHANH')->store('HinhAnh', 'public');
            $data['HINHANH'] = basename($path);
        }

        DB::table('SANPHAM')->insert($data);

        return redirect()->route('staff.products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(Request $request, $id)
    {
        $row = DB::table('SANPHAM')->where('MASANPHAM', $id)->first();
        if (!$row) return back()->with('error','Không tìm thấy sản phẩm.');

        // Chấp nhận input từ form là GIABAN/SOLUONGTON (đúng schema).
        // Nếu form cũ đang gửi GIA/TONKHO thì map sang cho an toàn.
        $payload = $request->all();
        if (isset($payload['GIA']) && !isset($payload['GIABAN'])) {
            $payload['GIABAN'] = $payload['GIA'];
        }
        if (isset($payload['TONKHO']) && !isset($payload['SOLUONGTON'])) {
            $payload['SOLUONGTON'] = $payload['TONKHO'];
        }

        $data = validator($payload, [
            'TENSANPHAM'   => ['required','string','max:255'],
            'GIABAN'       => ['required','numeric','min:0'],
            'SOLUONGTON'   => ['nullable','integer','min:0'],
            'MOTA'         => ['nullable','string','max:1000'],
            'MALOAI'       => ['nullable', Rule::exists('LOAI','MALOAI')],
            'MAKHUYENMAI'  => ['nullable', Rule::exists('KHUYENMAI','MAKHUYENMAI')],
            'MANHACUNGCAP' => ['nullable', Rule::exists('NHACUNGCAP','MANHACUNGCAP')],
            'HINHANH'      => ['nullable','image','max:4096'],
        ])->validate();

        if ($request->hasFile('HINHANH')) {
            $path = $request->file('HINHANH')->store('HinhAnh', 'public');
            $data['HINHANH'] = basename($path);

            if (!empty($row->HINHANH)) {
                Storage::disk('public')->delete('HinhAnh/'.$row->HINHANH);
            }
        }

        DB::table('SANPHAM')->where('MASANPHAM', $id)->update($data);

        return redirect()->route('staff.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy($id)
    {
        $row = DB::table('SANPHAM')->where('MASANPHAM', $id)->first();
        if (!$row) return back()->with('error','Không tìm thấy sản phẩm.');

        if (!empty($row->HINHANH)) {
            Storage::disk('public')->delete('HinhAnh/'.$row->HINHANH);
        }

        DB::table('SANPHAM')->where('MASANPHAM', $id)->delete();

        return redirect()->route('staff.products.index')->with('success', 'Đã xoá sản phẩm.');
    }
}
