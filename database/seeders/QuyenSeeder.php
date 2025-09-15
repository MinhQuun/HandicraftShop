<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuyenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('QUYEN')->insertOrIgnore([
            ['MAQUYEN' => 1, 'TENQUYEN' => 'admin'],
            ['MAQUYEN' => 2, 'TENQUYEN' => 'nhanvien'],
            ['MAQUYEN' => 3, 'TENQUYEN' => 'khachhang'],
        ]);
    }
}
