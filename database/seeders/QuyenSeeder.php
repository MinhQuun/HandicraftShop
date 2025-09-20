<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuyenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('QUYEN')->insertOrIgnore([
            ['MAQUYEN' => 'Q01', 'TENQUYEN' => 'admin'],
            ['MAQUYEN' => 'Q02', 'TENQUYEN' => 'nhanvien'],
            ['MAQUYEN' => 'Q03', 'TENQUYEN' => 'khachhang'],
            ]);
    }
}
