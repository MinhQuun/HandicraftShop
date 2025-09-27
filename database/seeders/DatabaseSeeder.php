<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $this->call([
        // ImportSqlSeeder::class,
        // ]);
        $this->call(QuyenSeeder::class);
        $this->call([UserSeeder::class,]);
        
        $this->call([
            KhachHangSeeder::class,
        ]);
        
        $this->call([
            DiaChiGiaoHangSeeder::class,
        ]);
        
        $this->call([
            PhieuNhapXuatSeeder::class,
        ]);
    }
}
