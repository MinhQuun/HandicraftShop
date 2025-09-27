<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiaChiGiaoHangSeeder extends Seeder
{
    public function run(): void
    {
        // mapping: email -> địa chỉ
        $addresses = [
            'nttt@gmail.com' => '33 Phan Huy Ích, P. 15, Q. Tân Bình, TP.HCM',
            'tml@gmail.com'  => 'Ấp 5, xã Tân Tây, Huyện Gò Công Đông, Tỉnh Tiền Giang',
            'nptd@gmail.com' => '16 Núi Cấm 1, xã Vĩnh Thái, Thành Phố Nha Trang, Tỉnh Khánh Hòa',
        ];

        foreach ($addresses as $email => $addr) {
            // tìm khách hàng theo email
            $kh = DB::table('KHACHHANG')->where('EMAIL', $email)->first();

            if ($kh) {
                // chèn địa chỉ nếu chưa có
                DB::table('DIACHI_GIAOHANG')->updateOrInsert(
                    ['MAKHACHHANG' => $kh->MAKHACHHANG, 'DIACHI' => $addr],
                    [] // không cần update thêm gì
                );
            }
        }
    }
}
