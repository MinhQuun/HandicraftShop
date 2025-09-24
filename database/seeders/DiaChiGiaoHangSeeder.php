<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiaChiGiaoHangSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy toàn bộ khách hàng hiện có
        $customers = DB::table('KHACHHANG')->select('MAKHACHHANG','HOTEN')->get();

        $samples1 = [
            '140 Lê Trọng Tấn, P. Tây Thạnh, Q. Tân Phú, TP.HCM',
            '12 Nguyễn Huệ, P. Bến Nghé, Q.1, TP.HCM',
            '88 Cách Mạng Tháng 8, Q.10, TP.HCM',
        ];
        $samples2 = [
            '21 Quốc Lộ 1A, Thủ Đức, TP.HCM',
            '35 Võ Văn Ngân, TP. Thủ Đức',
            '10 Phạm Văn Đồng, TP. Thủ Đức',
        ];

        foreach ($customers as $i => $kh) {
            // Mỗi khách ít nhất 1 địa chỉ…
            DB::table('DIACHI_GIAOHANG')->insert([
                'MAKHACHHANG' => $kh->MAKHACHHANG,
                'DIACHI'      => $samples1[$i % count($samples1)],
            ]);

            // …và ngẫu nhiên 1 địa chỉ thứ 2
            if ($i % 2 === 0) {
                DB::table('DIACHI_GIAOHANG')->insert([
                    'MAKHACHHANG' => $kh->MAKHACHHANG,
                    'DIACHI'      => $samples2[$i % count($samples2)],
                ]);
            }
        }
    }
}
