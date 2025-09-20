<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // B1: Đảm bảo có đủ 3 quyền
        // ==============================
        $roles = [
            'Q01' => 'admin',
            'Q02' => 'nhanvien',
            'Q03' => 'khachhang',
        ];


        foreach ($roles as $maQuyen => $tenQuyen) {
            DB::table('QUYEN')->updateOrInsert(
                ['MAQUYEN' => $maQuyen],
                ['TENQUYEN' => $tenQuyen]
            );
        }

        // ==============================
        // B2: Tạo các user mặc định
        // ==============================
        $users = [
            [
                'email' => 'quan@gmail.com',
                'name'  => 'Quân',
                'phone' => '0123456789',
                'password' => Hash::make('123456'),
                'role'  => 'Q01'
            ],
            [
                'email' => 'doan@gmail.com',
                'name'  => 'Đoan',
                'phone' => '0987654321',
                'password' => Hash::make('123456'),
                'role'  => 'Q02'
            ],
            [
                'email' => 'vy@gmail.com',
                'name'  => 'Vy',
                'phone' => '0987654322',
                'password' => Hash::make('123456'),
                'role'  => 'Q02'
            ],
            [
                'email' => 'yen@gmail.com',
                'name'  => 'Yến',
                'phone' => '0987654323',
                'password' => Hash::make('123456'),
                'role'  => 'Q02'
            ],
            [
                'email' => 'khachhang@example.com',
                'name'  => 'KhachHang',
                'phone' => '0911222333',
                'password' => Hash::make('123456'),
                'role'  => 'Q03'
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'phone' => $u['phone'], 'password' => $u['password']]
            );

            DB::table('QUYEN_NGUOIDUNG')->updateOrInsert(
                ['user_id' => $user->id, 'MAQUYEN' => $u['role']],
                []
            );
        }
    }
}
