<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:100'],
            'email'   => ['required','email','max:150'],
            'message' => ['required','string','max:2000'],
        ]);

        // TODO: tuỳ bạn — lưu DB, gửi email, gửi Telegram, v.v.
        // Ví dụ lưu log tạm:
        // \Log::info('Contact form', $data);

        return back()->with('success', 'Cảm ơn bạn! Chúng tôi đã nhận được thông tin và sẽ phản hồi sớm nhất.');
    }
}
