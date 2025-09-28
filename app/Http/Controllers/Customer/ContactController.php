<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ContactController extends Controller
{
    protected string $table = 'LIENHE';

    public function show()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','min:2','max:120'],
            'email'   => ['required','email:rfc,dns','max:255'],
            'message' => ['required','string','min:6','max:5000'],
        ], [
            'name.required'    => 'Vui lòng nhập họ tên.',
            'email.required'   => 'Vui lòng nhập email.',
            'email.email'      => 'Email không đúng định dạng.',
            'message.required' => 'Vui lòng nhập nội dung.',
        ]);

        if (!DB::getSchemaBuilder()->hasTable($this->table)) {
            return back()->with('error', 'Chưa thiết lập bảng LIENHE. Vui lòng tạo bảng trước.');
        }

        DB::table($this->table)->insert([
            'NAME'        => $data['name'],
            'EMAIL'       => $data['email'],
            'MESSAGE'     => $data['message'],
            'STATUS'      => 'NEW',
            'CREATED_AT'  => Carbon::now(),
            'UPDATED_AT'  => Carbon::now(),
        ]);

        return back()->with('success', 'Đã gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất.');
    }
}