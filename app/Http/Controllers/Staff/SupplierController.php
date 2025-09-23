<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /** Danh sách nhà cung cấp */
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));

        $suppliers = DB::table('NHACUNGCAP')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('TENNHACUNGCAP', 'like', "%$q%")
                        ->orWhere('DTHOAI', 'like', "%$q%")
                        ->orWhere('DIACHI', 'like', "%$q%");
            })
            ->orderBy('MANHACUNGCAP', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('staff.suppliers', compact('suppliers', 'q'));
    }

    /** Tạo mới */
    public function store(Request $request)
    {
        $request->validate([
            'TENNHACUNGCAP' => 'required|string|max:100',
            'DTHOAI'        => 'nullable|regex:/^0\d{9}$/',
            'DIACHI'        => 'nullable|string|max:255',
        ], [
            'TENNHACUNGCAP.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'DTHOAI.regex'           => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        DB::table('NHACUNGCAP')->insert([
            'TENNHACUNGCAP' => $request->TENNHACUNGCAP,
            'DTHOAI'        => $request->DTHOAI,
            'DIACHI'        => $request->DIACHI,
        ]);

        return back()->with('success', 'Thêm nhà cung cấp thành công.');
    }

    /** Cập nhật */
    public function update(Request $request, $id)
    {
        $request->validate([
            'TENNHACUNGCAP' => 'required|string|max:100',
            'DTHOAI'        => 'nullable|regex:/^0\d{9}$/',
            'DIACHI'        => 'nullable|string|max:255',
        ]);

        DB::table('NHACUNGCAP')
            ->where('MANHACUNGCAP', $id)
            ->update([
                'TENNHACUNGCAP' => $request->TENNHACUNGCAP,
                'DTHOAI'        => $request->DTHOAI,
                'DIACHI'        => $request->DIACHI,
            ]);

        return back()->with('success', 'Cập nhật nhà cung cấp thành công.');
    }

    /** Xoá */
    public function destroy($id)
    {
        DB::table('NHACUNGCAP')->where('MANHACUNGCAP', $id)->delete();

        return back()->with('success', 'Xoá nhà cung cấp thành công.');
    }
}
