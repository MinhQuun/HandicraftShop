<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\KhachHang;

class KhachHangSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy id quyền "khachhang"
        $roleId = DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = ?', ['khachhang'])
            ->value('MAQUYEN');

        if (!$roleId) {
            $this->command->warn('⚠️ Bảng QUYEN chưa có quyền "khachhang". Chạy QuyenSeeder trước.');
            return;
        }

        // Danh sách khách mẫu
        $customers = [
            [
                'name'  => 'Nguyễn Thị Tú Trinh',
                'email' => 'nguyenthitutrinh120504@gmail.com',
                'phone' => '0564609210',
            ],
            [
                'name'  => 'Trần Minh Luân',
                'email' => 'hakachi303@gmail.com',
                'phone' => '0389137204',
            ],
            [
                'name'  => 'Nguyễn Phạm Trường Duy',
                'email' => 'nptduyc920@gmail.com',
                'phone' => '0796177075',
            ],
        ];

        foreach ($customers as $c) {
            // 1. Tạo user nếu chưa có
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name'     => $c['name'],
                    'phone'    => $c['phone'],
                    'password' => Hash::make('123456'), // mật khẩu mặc định
                ]
            );

            // 2. Gán quyền khachhang
            $user->assignRole($roleId);

            // 3. Tạo hoặc cập nhật hồ sơ KH
            $kh = KhachHang::firstOrNew(['user_id' => $user->id]);
            $kh->HOTEN        = $user->name;
            $kh->EMAIL        = $user->email;
            $kh->SODIENTHOAI  = $user->phone;
            $kh->save();
        }
    }
}
