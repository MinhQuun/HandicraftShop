<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HinhThucTTSeeder extends Seeder
{
    public function run(): void
    {
        // MATT <= 10 ký tự theo schema
        $rows = [
            ['MATT' => 'COD',   'LOAITT' => 'Thanh toán khi nhận hàng (COD)'],
            ['MATT' => 'BANK',  'LOAITT' => 'Chuyển khoản ngân hàng'],
            ['MATT' => 'MOMO',  'LOAITT' => 'Ví MoMo'],
            ['MATT' => 'ZLPAY', 'LOAITT' => 'ZaloPay'],
            ['MATT' => 'VNPAY', 'LOAITT' => 'VNPay QR/ATM/iBanking'],
            ['MATT' => 'CARD',  'LOAITT' => 'Thẻ quốc tế (Visa/MasterCard)'],
        ];

        foreach ($rows as $r) {
            DB::table('HINHTHUCTT')->updateOrInsert(['MATT' => $r['MATT']], ['LOAITT' => $r['LOAITT']]);
        }
    }
}
