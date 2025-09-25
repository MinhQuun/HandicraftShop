<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim($request->get('q', ''));   // tìm theo tên sp / tên loại / NCC
        $loai = $request->get('loai');          // MALOAI filter

        $products = DB::table('SANPHAM as s')
            ->leftJoin('LOAI as l', 'l.MALOAI', '=', 's.MALOAI')
            ->leftJoin('NHACUNGCAP as n', 'n.MANHACUNGCAP', '=', 's.MANHACUNGCAP')
            ->when($q, function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($x) use ($like) {
                    $x->where('s.TENSANPHAM', 'like', $like)   // tên sản phẩm
                      ->orWhere('l.TENLOAI', 'like', $like)    // tên loại
                      ->orWhere('n.TENNHACUNGCAP', 'like', $like); // tên NCC
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
            ->orderBy('s.MASANPHAM', 'asc')
            ->paginate(8)
            ->withQueryString();

        $categories = DB::table('LOAI')
            ->select('MALOAI','TENLOAI')
            ->orderBy('TENLOAI')
            ->get();

        $suppliers = DB::table('NHACUNGCAP')
            ->select('MANHACUNGCAP','TENNHACUNGCAP')
            ->orderBy('TENNHACUNGCAP')
            ->get();

        return view('staff.products', [
            'products'   => $products,
            'categories' => $categories,
            'suppliers'  => $suppliers,
            'q'          => $q,
            'loai'       => $loai,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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

        // === Sinh mã tự động ===
        $row = DB::selectOne("
            SELECT MAX(CAST(SUBSTRING(MASANPHAM, 3) AS UNSIGNED)) AS last_num
            FROM SANPHAM
            WHERE MASANPHAM REGEXP '^SP[0-9]+$'
        ");
        $next = (int)($row->last_num ?? 0) + 1;
        $data['MASANPHAM'] = 'SP'.str_pad($next, 3, '0', STR_PAD_LEFT);

        // === Lưu ảnh vào public/assets/images ===
        if ($request->hasFile('HINHANH')) {
            $file = $request->file('HINHANH');
            $name = $this->makePrettyFileName($file);
            $file->move(public_path('assets/images'), $name);
            $data['HINHANH'] = $name;
        }

        DB::table('SANPHAM')->insert($data);

        return redirect()->route('staff.products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(Request $request, $id)
    {
        $row = DB::table('SANPHAM')->where('MASANPHAM', $id)->first();
        if (!$row) return back()->with('error','Không tìm thấy sản phẩm.');

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
            $file = $request->file('HINHANH');
            $name = $this->makePrettyFileName($file);
            $file->move(public_path('assets/images'), $name);
            $data['HINHANH'] = $name;

            // Xóa ảnh cũ
            if (!empty($row->HINHANH)) {
                $oldPath = public_path('assets/images/'.$row->HINHANH);
                if (file_exists($oldPath)) @unlink($oldPath);
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
            $oldPath = public_path('assets/images/'.$row->HINHANH);
            if (file_exists($oldPath)) @unlink($oldPath);
        }

        DB::table('SANPHAM')->where('MASANPHAM', $id)->delete();

        return redirect()->route('staff.products.index')->with('success', 'Đã xoá sản phẩm.');
    }

    private function makePrettyFileName($file, $dir = null)
    {
        $dir = $dir ?: public_path('assets/images');
        $ext  = strtolower($file->getClientOriginalExtension()); // jpg, png...
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); 
        $slug = Str::slug($base, '-'); 
        if ($slug === '') $slug = 'image';

        $slug = Str::limit($slug, 60, '');

        $name = $slug.'.'.$ext;
        $i = 2;
        while (file_exists($dir.DIRECTORY_SEPARATOR.$name)) {
            $name = $slug.'-'.$i.'.'.$ext; 
            $i++;
        }
        return $name;
    }
}
